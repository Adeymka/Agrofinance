<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ConnexionController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'pin'       => 'required|string|size:4',
        ]);

        $user = User::where('telephone', $request->telephone)->first();

        if (!$user || !$user->verifierPin($request->pin)) {
            return response()->json([
                'succes'  => false,
                'message' => 'Numéro ou PIN incorrect.',
            ], 401);
        }

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

