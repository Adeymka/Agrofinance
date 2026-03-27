<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscriptionController extends Controller
{
    public function showForm()
    {
        return view('auth.inscription');
    }

    public function store(Request $request, OtpService $otp)
    {
        $request->validate([
            'nom'               => 'required|string|max:100',
            'prenom'            => 'required|string|max:100',
            'telephone'         => 'required|string',
            'type_exploitation' => 'required|in:cultures_vivrieres,elevage,maraichage,transformation,mixte',
        ]);

        $telephone = $otp->normaliserTelephone($request->telephone);

        $v = \Validator::make(
            ['telephone' => $telephone],
            [
                'telephone' => [
                    'required',
                    'regex:/^\+229\d{8}$/',
                    Rule::unique('users', 'telephone'),
                ],
            ],
            ['telephone.regex' => 'Le numéro doit être au format +229 suivi de 8 chiffres.']
        );

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $otp->genererEtEnvoyer($telephone);

        session([
            'inscription_data' => [
                'nom'               => $request->nom,
                'prenom'            => $request->prenom,
                'telephone'         => $telephone,
                'type_exploitation' => $request->type_exploitation,
            ],
        ]);

        return redirect()->route('verification.otp')
            ->with(
                'info',
                app()->isLocal()
                    ? 'Code envoyé. En local, consultez storage/logs/laravel.log'
                    : 'Code envoyé par SMS sur votre numéro.'
            );
    }
}
