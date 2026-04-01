<?php

namespace App\Services;

use App\Models\Exploitation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @param  array<int, array<string, mixed>>  $parActivite
     * @return array{
     *     heroActiviteId: int|null,
     *     heroInd: array<string, mixed>|null,
     *     chartActiviteId: int|null,
     *     premierActiviteId: int|null
     * }
     */
    public function resoudreHeroEtGraphique(
        mixed $campagneQuery,
        Collection $activiteIds,
        array $parActivite,
        Exploitation $exploitation
    ): array {
        $firstParActiviteId = $parActivite !== []
            ? (int) array_key_first($parActivite)
            : null;

        $heroActiviteId = null;
        if ($campagneQuery !== null && $campagneQuery !== ''
            && $activiteIds->contains((int) $campagneQuery)) {
            $heroActiviteId = (int) $campagneQuery;
        } elseif ($firstParActiviteId) {
            $heroActiviteId = $firstParActiviteId;
        }

        $heroInd = ($heroActiviteId && isset($parActivite[$heroActiviteId]))
            ? $parActivite[$heroActiviteId]
            : null;

        $chartActiviteId = $heroActiviteId ?: $firstParActiviteId;

        $premierActiviteId = $chartActiviteId
            ?? ($parActivite !== [] ? array_key_first($parActivite) : $exploitation->activitesActives->first()?->id);

        return [
            'heroActiviteId' => $heroActiviteId,
            'heroInd' => $heroInd,
            'chartActiviteId' => $chartActiviteId,
            'premierActiviteId' => $premierActiviteId !== null ? (int) $premierActiviteId : null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $parActivite
     * @return array<int, array<string, mixed>>
     */
    public function construireCartesActivites(
        Exploitation $exploitation,
        array $parActivite,
        ?string $dateDebutHistorique
    ): array {
        $activitesCards = [];

        foreach ($exploitation->activitesActives as $activite) {
            $ind = $parActivite[$activite->id] ?? null;
            if (! $ind) {
                continue;
            }

            $txForStats = $activite->transactions;
            if ($dateDebutHistorique) {
                $txForStats = $txForStats->filter(
                    fn ($t) => (string) $t->date_transaction >= $dateDebutHistorique
                );
            }

            $lastTx = $txForStats->max('date_transaction');
            $daysSince = $lastTx
                ? now()->diffInDays(Carbon::parse($lastTx))
                : 999;

            $totalDep = $txForStats->where('type', 'depense')->sum('montant');
            $budget = $activite->budget_previsionnel;
            $pctBudget = ($budget && $budget > 0)
                ? min(100, round(($totalDep / $budget) * 100, 1))
                : null;

            $activitesCards[] = [
                'id' => $activite->id,
                'nom' => $activite->nom,
                'type' => $activite->type,
                'statut' => $activite->statut,
                'recettes' => $ind['PB'] ?? 0,
                'depenses' => $ind['CT'] ?? 0,
                'marge' => $ind['MB'] ?? 0,
                'statut_indicateurs' => $ind['statut'] ?? 'rouge',
                'budget_pct' => $pctBudget,
                'budget_prev' => $budget,
                'days_since' => $daysSince,
            ];
        }

        return $activitesCards;
    }

    /**
     * @param  array<int, array<string, mixed>>  $activitesCards
     * @return array{alertesBudget: array<int, array<string, mixed>>, bannerBudgetCritique: bool}
     */
    public function alertesDepuisCartes(
        array $activitesCards,
        float $seuilAlerte = 85.0,
        float $seuilCritique = 100.0
    ): array
    {
        $seuilAlerte = max(1.0, min(100.0, $seuilAlerte));
        $seuilCritique = max($seuilAlerte, min(200.0, $seuilCritique));

        $alertesBudget = array_values(array_filter($activitesCards, function (array $c) use ($seuilAlerte) {
            $pct = $c['budget_pct'] ?? null;
            $prev = $c['budget_prev'] ?? 0;

            return $prev > 0 && $pct !== null && $pct >= $seuilAlerte;
        }));
        usort($alertesBudget, fn (array $a, array $b) => ($b['budget_pct'] ?? 0) <=> ($a['budget_pct'] ?? 0));

        $bannerBudgetCritique = collect($alertesBudget)->contains(fn ($c) => ($c['budget_pct'] ?? 0) >= $seuilCritique);

        return [
            'alertesBudget' => $alertesBudget,
            'bannerBudgetCritique' => $bannerBudgetCritique,
        ];
    }
}
