<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Web\AbonnementController — Interface web de gestion des abonnements.
 *
 * La logique metier FedaPay (initiation, callback, retry, lock) est desormais
 * centralisee dans AbonnementService pour eviter la duplication avec le controleur API.
 */
class AbonnementController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    public function index()
    {
        $user       = Auth::user();
        $abonnement = $user->abonnementActif()->first();

        return view('abonnement.index', compact('user', 'abonnement'));
    }

    /**
     * POST /abonnement/initier
     * #4 — Delegue a AbonnementService::initierPaiementFedaPay().
     * La session est conservee en mode Web comme fallback callback.
     */
    public function initier(Request $request)
    {
        $request->validate([
            'plan'      => 'required|in:mensuel,annuel,cooperative',
            'telephone' => 'required|string',
        ]);

        if (! config('services.fedapay.mock') && empty(config('services.fedapay.secret_key'))) {
            return back()->withErrors([
                'paiement' => 'Paiement non configure : ajoutez FEDAPAY_SECRET_KEY dans .env, ou FEDAPAY_MOCK=true.',
            ]);
        }

        $user = Auth::user();

        try {
            $result = $this->abonnementService->initierPaiementFedaPay(
                $user,
                $request->plan,
                $request->telephone,
                route('abonnement.callback'),
                webMode: true       // Autorise la session fallback pour le callback Web
            );
        } catch (\Throwable $e) {
            Log::error('FedaPay Web initier: echec appel fournisseur', [
                'error_class'   => $e::class,
                'error_message' => $e->getMessage(),
            ]);

            return back()->withErrors(['paiement' => "Erreur paiement. Veuillez reessayer."]);
        }

        if ($result['mock'] ?? false) {
            return redirect()->route('abonnement')
                ->with('info', "Mode simulation : cliquez sur \u00ab Confirmer la simulation \u00bb pour activer l'abonnement.");
        }

        return redirect($result['url_paiement']);
    }

    /**
     * POST /abonnement/finaliser-mock
     */
    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return back()->withErrors(['paiement' => 'Mode mock desactive.']);
        }

        $userId  = (int) Auth::user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== $userId) {
            return back()->withErrors(['paiement' => "Aucune initiation mock en attente. Relancez \u00ab Souscrire \u00bb."]);
        }

        $planChoisi = $pending['plan'];
        $montant    = $pending['montant'];
        $ref        = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        $user = User::findOrFail($userId);
        $this->abonnementService->activer($user, $planChoisi, $ref, (float) $montant);

        Log::info("Abonnement mock active (web) — user {$userId} — plan {$planChoisi}");

        return redirect()->route('abonnement')
            ->with('success', "Abonnement {$planChoisi} active (simulation) !");
    }

    /**
     * GET /abonnement/callback
     * #3, #14 — Delegue a AbonnementService::traiterCallbackFedaPay() avec Cache::lock().
     */
    public function callback(Request $request)
    {
        if (empty(config('services.fedapay.secret_key'))) {
            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'FedaPay non configure.']);
        }

        $transactionId = $request->query('id')
            ?? $request->input('id')
            ?? session('fedapay_transaction_id');

        if (! $transactionId) {
            Log::warning('FedaPay callback Web sans id transaction.');

            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'Transaction inconnue.']);
        }

        try {
            $result = $this->abonnementService->traiterCallbackFedaPay($transactionId);
        } catch (\Throwable $e) {
            Log::error('FedaPay Web callback: echec traitement', [
                'error_class'   => $e::class,
                'error_message' => $e->getMessage(),
            ]);

            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'Erreur lors de la confirmation.']);
        }

        if ($result['statut'] === 'erreur_contexte') {
            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'Contexte de paiement perdu.']);
        }

        if (in_array($result['statut'], ['approved', 'transferred'], true)) {
            $plan = $result['plan'];

            return redirect()->route('abonnement')
                ->with('success', $result['deja_traite']
                    ? 'Paiement deja enregistre.'
                    : "Abonnement {$plan} active avec succes !");
        }

        return redirect()->route('abonnement')
            ->withErrors(['paiement' => 'Paiement non confirme.']);
    }
}
