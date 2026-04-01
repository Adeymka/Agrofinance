<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\CooperativeMember;
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

        CooperativeMember::query()
            ->whereNull('user_id')
            ->where('invited_phone', $user->telephone)
            ->update([
                'user_id' => $user->id,
                'statut' => CooperativeMember::STATUT_ACTIVE,
                'joined_at' => now(),
            ]);

        $otp->genererEtEnvoyer($request->telephone);

        return response()->json([
            'succes' => true,
            'message' => app()->isLocal()
                ? 'Compte créé. Vérifiez votre code OTP dans les logs (environnement local).'
                : 'Compte créé. Un code à 6 chiffres vous a été envoyé par SMS.',
            'data' => ['user_id' => $user->id],
        ], 201);
    }
}

