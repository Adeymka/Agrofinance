<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifierAbonnement
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->aUnAbonnementActif()) {
            return response()->json([
                'succes'  => false,
                'message' => 'Abonnement requis pour accéder à cette fonctionnalité.',
            ], 403);
        }
        return $next($request);
    }
}

