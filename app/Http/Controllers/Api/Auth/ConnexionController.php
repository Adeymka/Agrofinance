<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ConnexionController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'pin'       => 'required|digits_between:4,6',
        ]);

        $telephoneNettoye = preg_replace('/[^0-9]/', '', (string) $request->telephone);
        $rateKey = 'api_pin_login:'.$telephoneNettoye.':'.$request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $secondes = RateLimiter::availableIn($rateKey);

            return response()->json([
                'succes' => false,
                'message' => "Trop de tentatives. Réessayez dans {$secondes} secondes.",
            ], 429);
        }

        $user = User::where('telephone', $request->telephone)->first();

        if (!$user || !$user->verifierPin($request->pin)) {
            RateLimiter::hit($rateKey, 15 * 60);
            return response()->json([
                'succes'  => false,
                'message' => 'Numéro ou PIN incorrect.',
            ], 401);
        }

        RateLimiter::clear($rateKey);

        $user->tokens()->where('name', 'pwa-token')->delete();
        $token = $user->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'succes' => true,
            'data'   => [
                'token' => $token,
                'user'  => [
                    'id'          => $user->id,
                    'nom'         => $user->nom,
                    'prenom'      => $user->prenom,
                    'telephone'   => $user->telephone,
                    'abonnement'  => $user->abonnementActif?->plan ?? 'aucun',
                ],
            ],
        ]);
    }
}

