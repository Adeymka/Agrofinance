<?php

namespace App\Http\Middleware;

use App\Services\AbonnementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierAbonnement
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('connexion');
        }

        $routesLibres = [
            'abonnement',
            'abonnement.initier',
            'abonnement.callback',
            'abonnement.finaliser-mock',
            'profil',
            'profil.update',
            'deconnexion',
        ];

        if ($request->routeIs($routesLibres)) {
            return $next($request);
        }

        if ($request->is('api/v1/abonnement/*') || $request->is('api/v1/auth/*')) {
            return $next($request);
        }

        if (! $this->abonnementService->estActif($user)) {
            $isApi = $request->expectsJson()
                || $request->is('api/*')
                || in_array('api', $request->segments(), true);

            $aDejaEteAbonne = $this->abonnementService->aHistoriqueAbonnement($user);

            if ($isApi) {
                if ($aDejaEteAbonne) {
                    return response()->json([
                        'succes' => false,
                        'message' => 'Votre abonnement ou votre essai est terminé. Renouvelez une formule pour continuer.',
                        'code' => 'ABONNEMENT_EXPIRE',
                    ], 403);
                }

                return response()->json([
                    'succes' => false,
                    'message' => 'Aucune formule active. Choisissez un plan ou un essai pour accéder à ces fonctions.',
                    'code' => 'ABONNEMENT_REQUIS',
                ], 403);
            }

            if ($aDejaEteAbonne) {
                return redirect()
                    ->route('abonnement')
                    ->with('alerte', 'Votre abonnement ou votre essai est terminé. Renouvelez une formule pour continuer.');
            }

            return redirect()
                ->route('abonnement')
                ->with('alerte', 'Pour utiliser le tableau de bord et la saisie, choisissez une formule ou un essai.');
        }

        return $next($request);
    }
}
