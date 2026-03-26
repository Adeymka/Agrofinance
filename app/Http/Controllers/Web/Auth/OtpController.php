<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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

        if (User::where('telephone', $data['telephone'])->exists()) {
            return redirect()->route('connexion')
                ->with('success', 'Ce numéro est déjà enregistré. Connectez-vous avec votre PIN.');
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
            'plan'       => 'gratuit',
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
        $telephone = session('inscription_data.telephone');
        $request->merge(['telephone' => $telephone]);

        $request->validate([
            'telephone' => 'required|string',
        ]);

        $telephoneNettoye = preg_replace('/[^0-9]/', '', (string) $telephone);
        $rateKey = 'web_otp_resend:'.$telephoneNettoye.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $secondes = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'code' => "Trop de renvois OTP. Réessayez dans {$secondes} secondes.",
            ]);
        }

        $otp->genererEtEnvoyer($request->telephone);
        RateLimiter::hit($rateKey, 15 * 60);

        return back()->with('success', 'Nouveau code envoyé.');
    }
}
