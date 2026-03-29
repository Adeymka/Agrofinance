<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\TarifsAbonnement;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function accueil()
    {
        $plansAccueilTarifs = [
            [
                'nom' => 'Gratuit',
                'prix' => '0',
                'duree' => 'Essai 75 jours',
                'badge' => 'Pour démarrer',
                'color' => 'rgba(255,255,255,0.50)',
                'bg' => 'rgba(255,255,255,0.04)',
                'bd' => 'rgba(255,255,255,0.10)',
                'cta' => 'Commencer gratuitement',
                'star' => false,
                'cta_variant' => 'muted',
                'items' => [
                    'Tableau de bord & 8 indicateurs',
                    'Saisie des transactions',
                    'Historique limité (6 mois)',
                    '1 exploitation',
                    'Pas de rapport PDF',
                ],
            ],
            [
                'nom' => 'Essentielle',
                'prix' => TarifsAbonnement::libelleEspace('mensuel'),
                'duree' => 'FCFA / mois',
                'badge' => 'Le plus populaire',
                'color' => '#4ade80',
                'bg' => 'rgba(74,222,128,0.08)',
                'bd' => 'rgba(74,222,128,0.30)',
                'cta' => 'Choisir Essentielle',
                'star' => true,
                'cta_variant' => 'green',
                'items' => [
                    'Tout le plan Gratuit',
                    'Rapports PDF avancés',
                    'Partage lien microfinance',
                    '1 exploitation',
                ],
            ],
            [
                'nom' => 'Pro',
                'prix' => TarifsAbonnement::libelleEspace('annuel'),
                'duree' => 'FCFA / mois',
                'badge' => 'Pour les pros',
                'color' => '#fbbf24',
                'bg' => 'rgba(245,158,11,0.06)',
                'bd' => 'rgba(245,158,11,0.20)',
                'cta' => 'Choisir Pro',
                'star' => false,
                'cta_variant' => 'amber',
                'items' => [
                    'Tout le plan Essentielle',
                    'Jusqu’à 5 exploitations',
                    'Dossier crédit (PDF)',
                    'Historique complet',
                ],
            ],
            [
                'nom' => 'Coopérative',
                'prix' => TarifsAbonnement::libelleEspace('cooperative'),
                'duree' => 'FCFA / mois',
                'badge' => 'Groupes & coopératives',
                'color' => '#c4b5fd',
                'bg' => 'rgba(139,92,246,0.10)',
                'bd' => 'rgba(167,139,250,0.28)',
                'cta' => 'Choisir Coopérative',
                'star' => false,
                'cta_variant' => 'violet',
                'items' => [
                    'Tout le plan Pro',
                    'Exploitations illimitées',
                    'Idéal coopératives & structures',
                ],
            ],
        ];

        return view('public.accueil', compact('plansAccueilTarifs'));
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

    public function confidentialite()
    {
        return view('public.confidentialite');
    }

    public function conditionsUtilisation()
    {
        return view('public.conditions-utilisation');
    }
}
