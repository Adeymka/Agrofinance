<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ConnexionController extends Controller
{
    public function showForm()
    {
        return view('auth.connexion');
    }

    public function store(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'pin'       => 'required|digits_between:4,6',
        ]);

        $telephoneNettoye = preg_replace('/[^0-9]/', '', (string) $request->telephone);
        $rateKey = 'web_pin_login:'.$telephoneNettoye.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $secondes = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'telephone' => "Trop de tentatives. Réessayez dans {$secondes} secondes.",
            ])->withInput(['telephone' => $request->telephone]);
        }

        $user = User::where('telephone', $request->telephone)->first();

        if (! $user || ! $user->verifierPin($request->pin)) {
            RateLimiter::hit($rateKey, 15 * 60);
            return back()->withErrors([
                'telephone' => 'Numéro ou PIN incorrect.',
            ])->withInput(['telephone' => $request->telephone]);
        }

        RateLimiter::clear($rateKey);

        Auth::login($user);
        $request->session()->regenerate();

        $user->tokens()->where('name', 'web-token')->delete();
        $token = $user->createToken('web-token')->plainTextToken;
        session(['api_token' => $token]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        $request->user()?->tokens()->where('name', 'web-token')->delete();
        $request->session()->forget('api_token');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('connexion');
    }
}
