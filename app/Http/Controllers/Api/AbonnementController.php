<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Api\AbonnementController — Paiement et abonnements via FedaPay.
 *
 * La logique metier FedaPay (initiation, callback, retry, lock) est desormais
 * centralisee dans AbonnementService pour eviter la duplication avec le controleur Web.
 */
class AbonnementController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    /**
     * POST /api/abonnement/initier
     * #4, #5 — Delegue a AbonnementService::initierPaiementFedaPay() ; pas de Session::put en API.
     */
    public function initier(Request $request)
    {
        $request->validate([
            'plan'      => 'required|in:mensuel,annuel,cooperative',
            'telephone' => 'required|string',
        ]);

        if (! config('services.fedapay.mock') && empty(config('services.fedapay.secret_key'))) {
            return response()->json([
                'succes'  => false,
                'message' => 'Paiement non configure : ajoutez FEDAPAY_SECRET_KEY dans .env, ou FEDAPAY_MOCK=true pour le developpement.',
            ], 503);
        }

        $user = auth()->user();

        try {
            $result = $this->abonnementService->initierPaiementFedaPay(
                $user,
                $request->plan,
                $request->telephone,
                rtrim(config('app.url'), '/').'/api/abonnement/callback',
                webMode: false      // Pas de Session::put en API (#5)
            );
        } catch (\Throwable $e) {
            Log::error('FedaPay API initier: echec appel fournisseur', [
                'error_class'   => $e::class,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'succes'  => false,
                'message' => "Erreur lors de l'initiation du paiement.",
            ], 500);
        }

        if ($result['mock'] ?? false) {
            return response()->json([
                'succes'  => true,
                'message' => 'Mode simulation (FEDAPAY_MOCK) : aucun appel FedaPay. Appelez POST /api/abonnement/finaliser-mock pour activer l\'abonnement.',
                'data'    => $result,
            ]);
        }

        return response()->json([
            'succes'  => true,
            'message' => "Transaction initiee. Redirigez l'utilisateur vers l'URL de paiement.",
            'data'    => [
                'transaction_id' => $result['transaction_id'],
                'montant'        => $result['montant'],
                'plan'           => $result['plan'],
                'url_paiement'   => $result['url_paiement'],
            ],
        ]);
    }

    /**
     * GET /api/abonnement/callback
     * #3, #14 — Delegue a AbonnementService::traiterCallbackFedaPay() avec Cache::lock().
     */
    public function callback(Request $request)
    {
        if (empty(config('services.fedapay.secret_key'))) {
            return response()->json(['succes' => false, 'message' => 'FedaPay non configure.'], 503);
        }

        $transactionId = $request->query('id')
            ?? $request->input('id')
            ?? session('fedapay_transaction_id');

        if (! $transactionId) {
            Log::warning('FedaPay API callback sans id transaction.');

            return response()->json(['succes' => false, 'message' => 'Transaction inconnue.'], 422);
        }

        try {
            $result = $this->abonnementService->traiterCallbackFedaPay($transactionId);
        } catch (\Throwable $e) {
            Log::error('FedaPay API callback: echec traitement', [
                'error_class'   => $e::class,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['succes' => false, 'message' => 'Erreur lors de la confirmation du paiement.'], 500);
        }

        if ($result['statut'] === 'erreur_contexte') {
            return response()->json(['succes' => false, 'message' => 'Contexte de paiement perdu.'], 422);
        }

        if (in_array($result['statut'], ['approved', 'transferred'], true)) {
            return response()->json([
                'succes'  => true,
                'message' => $result['deja_traite'] ? 'Deja traite.' : 'Abonnement active.',
            ]);
        }

        return response()->json(['succes' => true]);
    }

    /**
     * POST /api/abonnement/finaliser-mock
     * Uniquement lorsque FEDAPAY_MOCK=true — simule un paiement reussi.
     */
    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return response()->json([
                'succes'  => false,
                'message' => 'Non disponible : activez FEDAPAY_MOCK=true dans .env (dev uniquement).',
            ], 403);
        }

        $userId  = auth()->user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== (int) $userId) {
            return response()->json([
                'succes'  => false,
                'message' => "Aucune initiation mock en attente — appelez d'abord POST /api/abonnement/initier.",
            ], 422);
        }

        $planChoisi = $pending['plan'];
        $montant    = $pending['montant'];
        $ref        = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        $user = User::findOrFail($userId);
        $this->abonnementService->activer($user, $planChoisi, $ref, (float) $montant);

        Log::info("Abonnement mock active — user {$userId} — plan {$planChoisi}");

        return response()->json([
            'succes'  => true,
            'message' => 'Abonnement active (simulation).',
            'data'    => ['plan' => $planChoisi, 'statut' => 'actif'],
        ]);
    }
}
