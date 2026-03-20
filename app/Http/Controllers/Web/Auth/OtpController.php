<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function showForm()
    {
        if (! session('inscription_data')) {
            return redirect()->route('inscription');
        }

        return view('auth.otp', [
            'telephone' => session('inscription_data.telephone'),
        ]);
    }

    public function verify(Request $request, OtpService $otp)
    {
        $request->validate([
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
        ]);

        $data = session('inscription_data');
        if (! $data || empty($data['telephone'])) {
            return redirect()->route('inscription');
        }

        $resultat = $otp->verifier($data['telephone'], $request->code);

        if (! $resultat['succes']) {
            return back()->withErrors(['code' => $resultat['message']]);
        }

        $user = User::create([
            'nom'               => $data['nom'],
            'prenom'            => $data['prenom'],
            'telephone'         => $data['telephone'],
            'type_exploitation' => $data['type_exploitation'] ?? 'mixte',
            'pin_hash'          => null,
        ]);

        Abonnement::create([
            'user_id'    => $user->id,
            'plan'       => 'essai',
            'statut'     => 'essai',
            'date_debut' => now()->toDateString(),
            'date_fin'   => now()->addDays(75)->toDateString(),
            'montant'    => 0,
        ]);

        session(['inscription_data' => $data]);

        return redirect()->route('creer.pin');
    }

    public function renvoyer(Request $request, OtpService $otp)
    {
        $request->merge([
            'telephone' => session('inscription_data.telephone'),
        ]);

        $request->validate([
            'telephone' => 'required|string',
        ]);

        $otp->genererEtEnvoyer($request->telephone);

        return back()->with('success', 'Nouveau code envoyé.');
    }
}
