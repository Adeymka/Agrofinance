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

        $telephone = $this->normaliserTelephone($request->telephone);

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
            ->with('info', 'Code envoyé. En local, consultez storage/logs/laravel.log');
    }

    private function normaliserTelephone(string $telephone): string
    {
        $digits = preg_replace('/\D/', '', $telephone);

        if (str_starts_with($digits, '229') && strlen($digits) >= 11) {
            return '+'.$digits;
        }

        if (strlen($digits) === 8) {
            return '+229'.$digits;
        }

        return str_starts_with($telephone, '+') ? '+'.$digits : '+'.$digits;
    }
}
