<?php

namespace App\Services;

use App\Models\{Activite, Exploitation};

class FinancialIndicatorsService
{
    public function calculer(int $activiteId, ?string $debut = null, ?string $fin = null): array
    {
        $activite = Activite::with('transactions')->findOrFail($activiteId);
        $transactions = $activite->transactions;

        if ($debut) {
            $transactions = $transactions->where('date_transaction', '>=', $debut);
        }
        if ($fin) {
            $transactions = $transactions->where('date_transaction', '<=', $fin);
        }

        $depenses = $transactions->where('type', 'depense');
        $recettes = $transactions->where('type', 'recette');

        $PB = $recettes->sum('montant');
        $CV = $depenses->where('nature', 'variable')->sum('montant');
        $CF = $depenses->where('nature', 'fixe')->sum('montant');
        $CT = $CV + $CF;

        $categoriesCI = [
            'semences', 'engrais_mineraux', 'engrais_organiques',
            'pesticides', 'herbicides', 'fongicides', 'vaccins',
            'medicaments_veterinaires', 'aliments_animaux', 'eau_abreuvement',
            'energie_transformation', 'emballages', 'matieres_premieres', 'carburant',
        ];
        $CI  = $depenses->whereIn('categorie', $categoriesCI)->sum('montant');
        $VAB = $PB - $CI;
        $MB  = $PB - $CV;
        $RNE = $PB - $CT;
        $RF  = $CT > 0 ? round(($RNE / $CT) * 100, 2) : 0;

        $SR = null;
        if ($PB > 0) {
            $tauxMarge = ($PB - $CV) / $PB;
            $SR = $tauxMarge > 0 ? round($CF / $tauxMarge, 2) : null;
        } elseif ($CF > 0) {
            $SR = $CF;
        }

        return [
            'PB'              => round($PB, 2),
            'CV'              => round($CV, 2),
            'CF'              => round($CF, 2),
            'CT'              => round($CT, 2),
            'CI'              => round($CI, 2),
            'VAB'             => round($VAB, 2),
            'MB'              => round($MB, 2),
            'RNE'             => round($RNE, 2),
            'RF'              => $RF,
            'SR'              => $SR,
            'statut'          => $this->determinerStatut($PB, $MB, $RNE, $SR),
            'nb_transactions' => $transactions->count(),
            'nb_depenses'     => $depenses->count(),
            'nb_recettes'     => $recettes->count(),
            'derniere_saisie' => $transactions->max('updated_at'),
        ];
    }

    public function calculerExploitation(int $exploitationId): array
    {
        $exploitation = Exploitation::with('activitesActives.transactions')
            ->findOrFail($exploitationId);

        $parActivite = [];
        foreach ($exploitation->activitesActives as $activite) {
            $parActivite[$activite->id] = array_merge(
                ['nom' => $activite->nom, 'type' => $activite->type],
                $this->calculer($activite->id)
            );
        }

        $PBt  = collect($parActivite)->sum('PB');
        $CTt  = collect($parActivite)->sum('CT');
        $MBt  = collect($parActivite)->sum('MB');
        $RNEt = collect($parActivite)->sum('RNE');

        return [
            'par_activite' => $parActivite,
            'consolide'    => [
                'PB'     => round($PBt, 2),
                'CT'     => round($CTt, 2),
                'MB'     => round($MBt, 2),
                'RNE'    => round($RNEt, 2),
                'RF'     => $CTt > 0 ? round(($RNEt / $CTt) * 100, 2) : 0,
                'statut' => $this->determinerStatut($PBt, $MBt, $RNEt, null),
            ],
        ];
    }

    /**
     * Évolution mensuelle sur 12 mois (pour le graphique du dashboard).
     */
    public function evolutionMensuelle(int $activiteId): array
    {
        $evolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $ind = $this->calculer(
                $activiteId,
                $mois->copy()->startOfMonth()->toDateString(),
                $mois->copy()->endOfMonth()->toDateString()
            );
            $evolution[] = [
                'mois'     => $mois->format('M Y'),
                'mois_num' => $mois->format('Y-m'),
                'MB'       => $ind['MB'],
                'RNE'      => $ind['RNE'],
                'PB'       => $ind['PB'],
                'CT'       => $ind['CT'],
            ];
        }

        return $evolution;
    }

    private function determinerStatut(float $PB, float $MB, float $RNE, ?float $SR): string
    {
        if ($RNE > 0 && ($SR === null || $PB >= $SR)) {
            return 'vert';
        }
        if ($MB > 0) {
            return 'orange';
        }
        return 'rouge';
    }
}

