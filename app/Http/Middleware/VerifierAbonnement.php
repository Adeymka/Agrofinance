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

        if ($request->is('api/abonnement/*') || $request->is('api/auth/*')) {
            return $next($request);
        }

        if (! $this->abonnementService->estActif($user)) {
            $isApi = $request->expectsJson()
                || $request->is('api/*')
                || in_array('api', $request->segments(), true);

            if ($isApi) {
                return response()->json([
                    'succes' => false,
                    'message' => 'Abonnement expiré. Veuillez renouveler votre abonnement.',
                    'code' => 'ABONNEMENT_EXPIRE',
                ], 403);
            }

            return redirect()
                ->route('abonnement')
                ->with('alerte', 'Votre abonnement a expiré. Choisissez un plan pour continuer.');
        }

        return $next($request);
    }
}
