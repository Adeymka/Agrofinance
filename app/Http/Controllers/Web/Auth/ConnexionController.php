<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'pin'       => 'required|string|size:4',
        ]);

        $user = User::where('telephone', $request->telephone)->first();

        if (! $user || ! $user->verifierPin($request->pin)) {
            return back()->withErrors([
                'telephone' => 'Numéro ou PIN incorrect.',
            ])->withInput(['telephone' => $request->telephone]);
        }

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
