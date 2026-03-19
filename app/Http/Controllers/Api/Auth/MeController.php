<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'succes' => true,
            'data'   => [
                'id'                => $user->id,
                'nom'               => $user->nom,
                'prenom'            => $user->prenom,
                'telephone'         => $user->telephone,
                'type_exploitation' => $user->type_exploitation,
                'departement'       => $user->departement,
                'commune'           => $user->commune,
                'abonnement'        => $user->abonnementActif?->plan ?? 'aucun',
                'membre_depuis'     => $user->created_at->format('d/m/Y'),
            ],
        ]);
    }
}

