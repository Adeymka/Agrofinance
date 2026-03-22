<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function accueil()
    {
        return view('public.accueil');
    }

    public function commentCaMarche()
    {
        return redirect()->route('aide.index');
    }

    public function aPropos()
    {
        return view('public.a-propos');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function envoyerContact(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'contact' => 'required|string|max:100',
            'sujet' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        // TODO Sprint suivant : envoyer email ou WhatsApp
        return back()->with('success', 'Votre message a été envoyé. Nous vous répondons sous 24h.');
    }
}
