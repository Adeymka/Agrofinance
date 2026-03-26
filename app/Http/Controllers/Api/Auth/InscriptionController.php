<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    public function __invoke(Request $request, OtpService $otp)
    {
        $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'required|string|max:100',
            'telephone'        => 'required|string|unique:users,telephone',
            'type_exploitation'=> 'nullable|in:cultures_vivrieres,elevage,maraichage,transformation,mixte',
            'departement'      => 'nullable|string|max:100',
            'commune'          => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'nom'               => $request->nom,
            'prenom'            => $request->prenom,
            'telephone'         => $request->telephone,
            'type_exploitation' => $request->type_exploitation ?? 'mixte',
            'departement'       => $request->departement,
            'commune'           => $request->commune,
        ]);

        $otp->genererEtEnvoyer($request->telephone);

        return response()->json([
            'succes'  => true,
            'message' => 'Compte créé. Vérifiez votre code OTP (uniquement en local si `OTP_DEBUG_LOG=true`).',
            'data'    => ['user_id' => $user->id],
        ], 201);
    }
}

