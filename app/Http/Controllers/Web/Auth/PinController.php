<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function showForm()
    {
        if (! session('inscription_data')) {
            return redirect()->route('connexion');
        }

        return view('auth.creer-pin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pin'              => 'required|digits:4|confirmed',
            'pin_confirmation' => 'required|digits:4',
        ]);

        $tel = session('inscription_data.telephone');
        if (! $tel) {
            return redirect()->route('inscription');
        }

        $user = User::where('telephone', $tel)->firstOrFail();
        $user->pin_hash = Hash::make($request->pin);
        $user->save();

        session()->forget('inscription_data');

        return redirect()->route('connexion')
            ->with('success', 'Compte créé avec succès. Connectez-vous.');
    }
}
