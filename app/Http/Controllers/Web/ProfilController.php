<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('abonnements');
        $abonnement = $user->abonnementActif()->first();

        return view('profil.index', compact('user', 'abonnement'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nom'               => 'required|string|max:100',
            'prenom'            => 'required|string|max:100',
            'type_exploitation' => 'nullable|in:cultures_vivrieres,elevage,maraichage,transformation,mixte',
            'departement'       => 'nullable|string|max:100',
            'commune'           => 'nullable|string|max:100',
        ]);

        $user = auth()->user();

        $user->update($request->only([
            'nom', 'prenom', 'type_exploitation', 'departement', 'commune',
        ]));

        if ($request->filled('pin')) {
            $request->validate([
                'pin_actuel' => 'required|digits:4',
                'pin'        => 'required|digits:4|confirmed',
            ]);

            if (! $user->verifierPin((string) $request->input('pin_actuel'))) {
                return back()->withErrors(['pin_actuel' => 'PIN actuel incorrect.'])->withInput();
            }

            $user->update(['pin_hash' => Hash::make($request->pin)]);

            return back()->with('success', 'Profil et PIN mis à jour.');
        }

        return back()->with('success', 'Profil mis à jour.');
    }
}
