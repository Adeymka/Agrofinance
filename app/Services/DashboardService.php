<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * DashboardService — Logique metier de la page tableau de bord Web.
 *
 * Extrait du DashboardController pour respecter le principe de responsabilite unique (SRP).
 * Le controleur orchestre uniquement la requete HTTP ; ce service produit les donnees metier.
 */
class DashboardService
{
    /**
     * Construit le tableau des cards d'activites pour la vue dashboard.
     *
     * @param  Exploitation  $exploitation      Exploitation selectionnee (avec activitesActives + transactions charges)
     * @param  array         $parActivite       Indicateurs calcules par activite_id (depuis FinancialIndicatorsService)
     * @param  string|null   $dateDebutHistorique  Plancher d'historique selon plan (format Y-m-d ou null)
     * @return array<int, array{id:int, nom:string, type:string, statut:string, recettes:float, depenses:float,
     *                          marge:float, statut_indicateurs:string, budget_pct:float|null,
     *                          budget_prev:float|null, days_since:int}>
     */
    public function construireActivitesCards(
        Exploitation $exploitation,
        array $parActivite,
        ?string $dateDebutHistorique
    ): array {
        $cards = [];

        foreach ($exploitation->activitesActives as $activite) {
            $ind = $parActivite[$activite->id] ?? null;
            if (! $ind) {
                continue;
            }

            // Filtrage temporel selon le plan (plan gratuit = 6 derniers mois)
            $txForStats = $activite->transactions;
            if ($dateDebutHistorique) {
                $txForStats = $txForStats->filter(
                    fn ($t) => (string) $t->date_transaction >= $dateDebutHistorique
                );
            }

            $lastTx    = $txForStats->max('date_transaction');
            $daysSince = $lastTx ? (int) now()->diffInDays(Carbon::parse($lastTx)) : 999;

            $totalDep  = $txForStats->where('type', 'depense')->sum('montant');
            $budget    = $activite->budget_previsionnel ? (float) $activite->budget_previsionnel : null;
            $pctBudget = ($budget && $budget > 0)
                ? min(100.0, round(($totalDep / $budget) * 100, 1))
                : null;

            $cards[] = [
                'id'                 => $activite->id,
                'nom'                => $activite->nom,
                'type'               => $activite->type,
                'statut'             => $activite->statut,
                'recettes'           => (float) ($ind['PB'] ?? 0),
                'depenses'           => (float) ($ind['CT'] ?? 0),
                'marge'              => (float) ($ind['MB'] ?? 0),
                'statut_indicateurs' => $ind['statut'] ?? 'rouge',
                'budget_pct'         => $pctBudget,
                'budget_prev'        => $budget,
                'days_since'         => $daysSince,
            ];
        }

        return $cards;
    }

    /**
     * Filtre et trie les alertes budget (>= 85% consomme).
     *
     * @param  array  $activitesCards  Retour de construireActivitesCards()
     * @return array  Alertes triees par budget_pct decroissant
     */
    public function extraireAlertesBudget(array $activitesCards): array
    {
        $alertes = array_values(array_filter($activitesCards, function (array $c) {
            $pct  = $c['budget_pct'] ?? null;
            $prev = $c['budget_prev'] ?? 0;

            return $prev > 0 && $pct !== null && $pct >= 85;
        }));

        usort($alertes, fn (array $a, array $b) => ($b['budget_pct'] ?? 0) <=> ($a['budget_pct'] ?? 0));

        return $alertes;
    }

    /**
     * Determine l'ID de l'activite "hero" a mettre en avant dans le dashboard.
     *
     * @param  Collection   $activiteIds       IDs des activites en cours
     * @param  array        $parActivite       Indicateurs par activite_id
     * @param  string|null  $campagneQuery     Parametre GET `?campagne=`
     * @return int|null
     */
    public function determinerHeroActiviteId(
        Collection $activiteIds,
        array $parActivite,
        ?string $campagneQuery
    ): ?int {
        $firstParActiviteId = $parActivite !== []
            ? (int) array_key_first($parActivite)
            : null;

        if ($campagneQuery !== null && $campagneQuery !== ''
            && $activiteIds->contains((int) $campagneQuery)) {
            return (int) $campagneQuery;
        }

        return $firstParActiviteId;
    }

    /**
     * Recupere les dernieres transactions d'une liste d'activites (limite 20).
     *
     * @param  Collection   $activiteIds
     * @param  string|null  $dateDebutHistorique
     * @return Collection
     */
    public function dernieresTransactions(Collection $activiteIds, ?string $dateDebutHistorique): Collection
    {
        return Transaction::query()
            ->when($activiteIds->isNotEmpty(), fn ($q) => $q->whereIn('activite_id', $activiteIds))
            ->when($activiteIds->isEmpty(),    fn ($q) => $q->whereRaw('1 = 0'))
            ->when($dateDebutHistorique,       fn ($q) => $q->where('date_transaction', '>=', $dateDebutHistorique))
            ->with('activite:id,nom')
            ->orderByDesc('date_transaction')
            ->limit(20)
            ->get();
    }
}
