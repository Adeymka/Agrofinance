<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AbonnementController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    /**
     * POST /api/v1/abonnement/initier
     */
    public function initier(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:mensuel,annuel,cooperative',
            'telephone' => 'required|string',
        ]);

        $user = auth()->user();
        $callbackUrl = rtrim(config('app.url'), '/').'/api/v1/abonnement/callback';

        $resultat = $this->abonnementService->initierPaiementFedaPay(
            $user,
            (string) $request->plan,
            (string) $request->telephone,
            $callbackUrl,
            false
        );

        return match ($resultat['type']) {
            'mock' => response()->json([
                'succes' => true,
                'message' => 'Mode simulation (FEDAPAY_MOCK) : aucun appel FedaPay. Appelez POST /api/v1/abonnement/finaliser-mock pour activer l’abonnement.',
                'data' => [
                    'mock' => true,
                    'transaction_id' => $resultat['ref'],
                    'montant' => $resultat['montant'],
                    'plan' => $resultat['plan'],
                    'url_paiement' => null,
                    'finaliser_mock' => 'POST '.rtrim(config('app.url'), '/').'/api/v1/abonnement/finaliser-mock',
                ],
            ]),
            'config_manquante' => response()->json([
                'succes' => false,
                'message' => $resultat['message'],
            ], 503),
            'succes' => response()->json([
                'succes' => true,
                'message' => "Transaction initiée. Redirigez l'utilisateur vers l'URL de paiement.",
                'data' => [
                    'transaction_id' => $resultat['transaction_id'],
                    'montant' => $resultat['montant'],
                    'plan' => $resultat['plan'],
                    'url_paiement' => $resultat['url_paiement'],
                ],
            ]),
            'erreur' => response()->json([
                'succes' => false,
                'message' => $resultat['message'],
            ], 500),
        };
    }

    /**
     * GET /api/v1/abonnement/callback
     * Appel public après redirection FedaPay (pas de Bearer).
     */
    public function callback(Request $request)
    {
        $resultat = $this->abonnementService->traiterCallbackFedaPay($request, false);

        return response()->json([
            'succes' => $resultat['succes'],
            'message' => $resultat['message'],
        ], (int) $resultat['http_code']);
    }

    /**
     * POST /api/v1/abonnement/finaliser-mock
     * Uniquement lorsque FEDAPAY_MOCK=true — simule un paiement réussi.
     */
    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return response()->json([
                'succes' => false,
                'message' => 'Non disponible : activez FEDAPAY_MOCK=true dans .env (dev uniquement).',
            ], 403);
        }

        $userId = auth()->user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== (int) $userId) {
            return response()->json([
                'succes' => false,
                'message' => 'Aucune initiation mock en attente — appelez d’abord POST /api/v1/abonnement/initier.',
            ], 422);
        }

        $planChoisi = $pending['plan'];
        $montant = $pending['montant'];
        $ref = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        $user = User::findOrFail($userId);
        $this->abonnementService->activer($user, $planChoisi, $ref, (float) $montant);

        Log::info("Abonnement mock activé — user {$userId} — plan {$planChoisi}");

        return response()->json([
            'succes' => true,
            'message' => 'Abonnement activé (simulation).',
            'data' => [
                'plan' => $planChoisi,
                'statut' => 'actif',
            ],
        ]);
    }
}
