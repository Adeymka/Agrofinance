<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifierAbonnement
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->aUnAbonnementActif()) {
            $isApi = $request->expectsJson()
                || $request->is('api/*')
                || in_array('api', $request->segments(), true);

            if ($isApi) {
                return response()->json([
                    'succes'  => false,
                    'message' => 'Abonnement requis pour accéder à cette fonctionnalité.',
                ], 403);
            }

            return redirect()->route('abonnement')
                ->with('error', 'Abonnement requis pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }
}

