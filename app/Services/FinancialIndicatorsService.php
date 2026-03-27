<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\Exploitation;

/**
 * Calcul des indicateurs financiers agricoles (PB, coûts, marges, seuil de rentabilité, statut).
 *
 * Les transactions sont filtrées par période optionnelle et, si fourni, par un plancher
 * d’historique lié à l’abonnement ({@see mergeDateDebut}).
 */
class FinancialIndicatorsService
{
    /**
     * Calcule les indicateurs pour une activité sur une période donnée (dates incluses).
     *
     * @param  int  $activiteId  Identifiant de l’activité.
     * @param  string|null  $debut  Borne basse des transactions (`date_transaction >=`), format `Y-m-d` ou chaîne parsable.
     * @param  string|null  $fin  Borne haute des transactions (`date_transaction <=`), format `Y-m-d` ou chaîne parsable.
     * @param  string|null  $dateDebutMin  Plancher absolu imposé par l’abonnement (ex. plan gratuit : pas d’historique avant cette date). Fusionné avec {@see $debut} via la date la plus récente des deux.
     * @return array{
     *     PB: float,
     *     CV: float,
     *     CF: float,
     *     CT: float,
     *     CI: float,
     *     VAB: float,
     *     MB: float,
     *     RNE: float,
     *     RF: float,
     *     SR: float|null,
     *     statut: 'vert'|'orange'|'rouge',
     *     nb_transactions: int,
     *     nb_depenses: int,
     *     nb_recettes: int,
     *     derniere_saisie: \Carbon\CarbonInterface|string|null
     * }
     */
    public function calculer(int $activiteId, ?string $debut = null, ?string $fin = null, ?string $dateDebutMin = null): array
    {
        $debut = $this->mergeDateDebut($debut, $dateDebutMin);

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
            'energie_transformation', 'emballages', 'matieres_premieres', 'produits_chimiques', 'carburant',
        ];
        $CI = $depenses->whereIn('categorie', $categoriesCI)->sum('montant');
        $VAB = $PB - $CI;
        $MB = $PB - $CV;
        $RNE = $PB - $CT;
        $RF = $CT > 0 ? round(($RNE / $CT) * 100, 2) : 0;

        $SR = null;
        if ($PB > 0) {
            $tauxMarge = ($PB - $CV) / $PB;
            $SR = $tauxMarge > 0 ? round($CF / $tauxMarge, 2) : null;
        } elseif ($CF > 0) {
            $SR = $CF;
        }

        return [
            'PB' => round($PB, 2),
            'CV' => round($CV, 2),
            'CF' => round($CF, 2),
            'CT' => round($CT, 2),
            'CI' => round($CI, 2),
            'VAB' => round($VAB, 2),
            'MB' => round($MB, 2),
            'RNE' => round($RNE, 2),
            'RF' => $RF,
            'SR' => $SR,
            'statut' => $this->determinerStatut($PB, $MB, $RNE, $SR),
            'nb_transactions' => $transactions->count(),
            'nb_depenses' => $depenses->count(),
            'nb_recettes' => $recettes->count(),
            'derniere_saisie' => $transactions->max('updated_at'),
        ];
    }

    /**
     * Agrège les indicateurs par activité active d’une exploitation et produit une ligne consolidée.
     *
     * Chaque activité est calculée sur tout l’historique autorisé : seul {@see $dateDebutMin} limite le passé
     * (pas de fenêtre `debut`/`fin` explicite par activité).
     *
     * @param  int  $exploitationId  Identifiant de l’exploitation.
     * @param  string|null  $dateDebutMin  Plancher d’historique (abonnement), passé à {@see calculer} pour chaque activité.
     * @return array{
     *     par_activite: array<int, array{
     *         nom: string,
     *         type: string,
     *         PB: float,
     *         CV: float,
     *         CF: float,
     *         CT: float,
     *         CI: float,
     *         VAB: float,
     *         MB: float,
     *         RNE: float,
     *         RF: float,
     *         SR: float|null,
     *         statut: 'vert'|'orange'|'rouge',
     *         nb_transactions: int,
     *         nb_depenses: int,
     *         nb_recettes: int,
     *         derniere_saisie: \Carbon\CarbonInterface|string|null
     *     }>,
     *     consolide: array{
     *         PB: float,
     *         CT: float,
     *         MB: float,
     *         RNE: float,
     *         RF: float,
     *         statut: 'vert'|'orange'|'rouge'
     *     }
     * }
     */
    public function calculerExploitation(int $exploitationId, ?string $dateDebutMin = null): array
    {
        $exploitation = Exploitation::with('activitesActives.transactions')
            ->findOrFail($exploitationId);

        $parActivite = [];
        foreach ($exploitation->activitesActives as $activite) {
            $parActivite[$activite->id] = array_merge(
                ['nom' => $activite->nom, 'type' => $activite->type],
                $this->calculer($activite->id, null, null, $dateDebutMin)
            );
        }

        $PBt = collect($parActivite)->sum('PB');
        $CTt = collect($parActivite)->sum('CT');
        $MBt = collect($parActivite)->sum('MB');
        $RNEt = collect($parActivite)->sum('RNE');

        return [
            'par_activite' => $parActivite,
            'consolide' => [
                'PB' => round($PBt, 2),
                'CT' => round($CTt, 2),
                'MB' => round($MBt, 2),
                'RNE' => round($RNEt, 2),
                'RF' => $CTt > 0 ? round(($RNEt / $CTt) * 100, 2) : 0,
                'statut' => $this->determinerStatut($PBt, $MBt, $RNEt, null),
            ],
        ];
    }

    /**
     * Évolution mensuelle sur les 12 derniers mois calendaires (un point par mois, pour graphique dashboard).
     *
     * Pour tout mois entièrement antérieur à {@see $dateDebutMin}, les indicateurs sont mis à zéro
     * (pas de calcul partiel sur un mois « coupé » par le plancher).
     *
     * @param  int  $activiteId  Identifiant de l’activité.
     * @param  string|null  $dateDebutMin  Plancher d’historique (`Y-m-d` ou équivalent) ; mois entièrement avant sont à zéro.
     * @return list<array{
     *     mois: string,
     *     mois_num: string,
     *     MB: float,
     *     RNE: float,
     *     PB: float,
     *     CT: float
     * }>
     */
    public function evolutionMensuelle(int $activiteId, ?string $dateDebutMin = null): array
    {
        $evolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $start = $mois->copy()->startOfMonth()->toDateString();
            $end = $mois->copy()->endOfMonth()->toDateString();

            if ($dateDebutMin && $end < $dateDebutMin) {
                $evolution[] = [
                    'mois' => $mois->format('M Y'),
                    'mois_num' => $mois->format('Y-m'),
                    'MB' => 0.0,
                    'RNE' => 0.0,
                    'PB' => 0.0,
                    'CT' => 0.0,
                ];

                continue;
            }

            $ind = $this->calculer($activiteId, $start, $end, $dateDebutMin);
            $evolution[] = [
                'mois' => $mois->format('M Y'),
                'mois_num' => $mois->format('Y-m'),
                'MB' => $ind['MB'],
                'RNE' => $ind['RNE'],
                'PB' => $ind['PB'],
                'CT' => $ind['CT'],
            ];
        }

        return $evolution;
    }

    /**
     * Fusionne la borne basse utilisateur et le plancher d’abonnement : on retient la date la plus récente.
     *
     * Ainsi, ni une période trop ancienne ni des données avant le début d’historique autorisé ne sont prises en compte.
     *
     * @param  string|null  $debut  Borne demandée pour le calcul (inclusive), ou null pour « depuis le début autorisé ».
     * @param  string|null  $dateDebutMin  Date minimale autorisée par le plan, ou null si aucune contrainte.
     * @return string|null  Chaîne de date comparable lexicographiquement (`Y-m-d`), ou null si aucune borne effective.
     */
    private function mergeDateDebut(?string $debut, ?string $dateDebutMin): ?string
    {
        if ($dateDebutMin === null || $dateDebutMin === '') {
            return $debut;
        }
        if ($debut === null || $debut === '') {
            return $dateDebutMin;
        }

        return max($debut, $dateDebutMin);
    }

    /**
     * Déduit un code couleur métier à partir des agrégats (seuil de rentabilité optionnel pour le vert).
     *
     * Règles : vert si RNE > 0 et (pas de SR ou PB >= SR) ; sinon orange si marge brute > 0 ; sinon rouge.
     * Pour le consolidé exploitation, {@see $SR} est null (pas de SR au niveau agrégé).
     *
     * @param  float  $PB  Produit brut (chiffre d’affaires agricole).
     * @param  float  $MB  Marge brute (PB − coûts variables).
     * @param  float  $RNE  Résultat net d’exploitation (PB − coûts totaux).
     * @param  float|null  $SR  Seuil de rentabilité (coûts fixes / taux de marge sur PB), ou null si non applicable.
     * @return 'vert'|'orange'|'rouge'
     */
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
