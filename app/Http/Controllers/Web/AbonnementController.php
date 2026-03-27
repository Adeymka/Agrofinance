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

class AbonnementController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $abonnement = $user->abonnementActif()->first();

        return view('abonnement.index', compact('user', 'abonnement'));
    }

    public function initier(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:mensuel,annuel,cooperative',
            'telephone' => 'required|string',
        ]);

        $user = Auth::user();
        $callbackUrl = route('abonnement.callback');

        $resultat = $this->abonnementService->initierPaiementFedaPay(
            $user,
            (string) $request->plan,
            (string) $request->telephone,
            $callbackUrl,
            true
        );

        return match ($resultat['type']) {
            'mock' => redirect()->route('abonnement')
                ->with('info', 'Mode simulation : cliquez sur « Confirmer la simulation » pour activer l’abonnement.'),
            'config_manquante' => back()->withErrors([
                'paiement' => $resultat['message'],
            ]),
            'succes' => redirect($resultat['url_paiement']),
            'erreur' => back()->withErrors(['paiement' => $resultat['message']]),
        };
    }

    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return back()->withErrors(['paiement' => 'Mode mock désactivé.']);
        }

        $userId = (int) Auth::user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== $userId) {
            return back()->withErrors(['paiement' => 'Aucune initiation mock en attente. Relancez « Souscrire ».']);
        }

        $planChoisi = $pending['plan'];
        $montant = $pending['montant'];
        $ref = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        $user = User::findOrFail($userId);
        $this->abonnementService->activer($user, $planChoisi, $ref, (float) $montant);

        Log::info("Abonnement mock activé (web) — user {$userId} — plan {$planChoisi}");

        return redirect()->route('abonnement')
            ->with('success', "Abonnement {$planChoisi} activé (simulation) !");
    }

    public function callback(Request $request)
    {
        $resultat = $this->abonnementService->traiterCallbackFedaPay($request, true);
        if (! $resultat['succes']) {
            return redirect()->route('abonnement')
                ->withErrors(['paiement' => $resultat['message']]);
        }

        return redirect()->route('abonnement')
            ->with('success', $resultat['deja_traite'] ?? false
                ? 'Paiement déjà enregistré.'
                : 'Abonnement activé avec succès !');
    }
}
