<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class FinancialIndicatorsService
{
    private const CACHE_TTL_MINUTES = 15;

    /**
     * @param  string|null  $dateDebutMin  Plancher absolu (ex. plan gratuit : 6 derniers mois) — fusionné avec $debut
     */
    public function calculer(int $activiteId, ?string $debut = null, ?string $fin = null, ?string $dateDebutMin = null): array
    {
        $debut = $this->mergeDateDebut($debut, $dateDebutMin);
        Activite::query()->findOrFail($activiteId);

        $version = $this->getActivityCacheVersion($activiteId);
        $cacheKey = "fsa:activity:{$activiteId}:{$debut}:{$fin}:{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($activiteId, $debut, $fin) {
            return $this->calculerSansCache($activiteId, $debut, $fin);
        });
    }

    public function calculerExploitation(Exploitation|int $exploitation, ?string $dateDebutMin = null): array
    {
        $exploitationModel = $exploitation instanceof Exploitation
            ? $exploitation
            : Exploitation::with('activitesActives')->findOrFail($exploitation);

        if (! $exploitationModel->relationLoaded('activitesActives')) {
            $exploitationModel->load('activitesActives');
        }

        $version = $this->getExploitationCacheVersion($exploitationModel->id);
        $cacheKey = "fsa:exploitation:{$exploitationModel->id}:{$dateDebutMin}:{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($exploitationModel, $dateDebutMin) {
            return $this->calculerExploitationSansCache($exploitationModel, $dateDebutMin);
        });
    }

    /**
     * @param  iterable<int>  $activiteIds
     * @return array<int, array<string, mixed>>
     */
    public function calculerPourActivites(iterable $activiteIds, ?string $debut = null, ?string $fin = null, ?string $dateDebutMin = null): array
    {
        $debut = $this->mergeDateDebut($debut, $dateDebutMin);
        $ids = collect($activiteIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $query = Transaction::query()->whereIn('activite_id', $ids->all());
        if ($debut) {
            $query->where('date_transaction', '>=', $debut);
        }
        if ($fin) {
            $query->where('date_transaction', '<=', $fin);
        }

        $rows = $query
            ->select('activite_id')
            ->selectRaw("SUM(CASE WHEN type = 'recette' THEN montant ELSE 0 END) as pb")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'variable' THEN montant ELSE 0 END) as cv")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'fixe' THEN montant ELSE 0 END) as cf")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND categorie IN ('semences','engrais_mineraux','engrais_organiques','pesticides','herbicides','fongicides','vaccins','medicaments_veterinaires','aliments_animaux','eau_abreuvement','energie_transformation','emballages','matieres_premieres','produits_chimiques','carburant') THEN montant ELSE 0 END) as ci")
            ->selectRaw('COUNT(*) as nb_transactions')
            ->selectRaw("SUM(CASE WHEN type = 'depense' THEN 1 ELSE 0 END) as nb_depenses")
            ->selectRaw("SUM(CASE WHEN type = 'recette' THEN 1 ELSE 0 END) as nb_recettes")
            ->selectRaw('MAX(updated_at) as derniere_saisie')
            ->groupBy('activite_id')
            ->get()
            ->keyBy('activite_id');

        $resultats = [];
        foreach ($ids as $id) {
            $row = $rows->get($id);
            $resultats[$id] = $this->normaliserIndicateurs(
                (float) ($row->pb ?? 0),
                (float) ($row->cv ?? 0),
                (float) ($row->cf ?? 0),
                (float) ($row->ci ?? 0),
                (int) ($row->nb_transactions ?? 0),
                (int) ($row->nb_depenses ?? 0),
                (int) ($row->nb_recettes ?? 0),
                $row->derniere_saisie ?? null
            );
        }

        return $resultats;
    }

    /**
     * Évolution mensuelle sur 12 mois (pour le graphique du dashboard).
     *
     * @param  string|null  $dateDebutMin  Plancher (plans gratuits : pas de données avant cette date).
     */
    public function evolutionMensuelle(int $activiteId, ?string $dateDebutMin = null): array
    {
        $version = $this->getActivityCacheVersion($activiteId);
        $cacheKey = "fsa:evolution:{$activiteId}:{$dateDebutMin}:{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($activiteId, $dateDebutMin) {
            return $this->evolutionMensuelleSansCache($activiteId, $dateDebutMin);
        });
    }

    public function invalidateForActivity(int $activiteId): void
    {
        $this->bumpActivityCacheVersion($activiteId);

        $exploitationId = Activite::query()->whereKey($activiteId)->value('exploitation_id');
        if ($exploitationId) {
            $this->bumpExploitationCacheVersion((int) $exploitationId);
        }
    }

    private function calculerSansCache(int $activiteId, ?string $debut = null, ?string $fin = null): array
    {
        $query = Transaction::query()->where('activite_id', $activiteId);
        if ($debut) {
            $query->where('date_transaction', '>=', $debut);
        }
        if ($fin) {
            $query->where('date_transaction', '<=', $fin);
        }

        $row = $query
            ->selectRaw("SUM(CASE WHEN type = 'recette' THEN montant ELSE 0 END) as pb")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'variable' THEN montant ELSE 0 END) as cv")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'fixe' THEN montant ELSE 0 END) as cf")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND categorie IN ('semences','engrais_mineraux','engrais_organiques','pesticides','herbicides','fongicides','vaccins','medicaments_veterinaires','aliments_animaux','eau_abreuvement','energie_transformation','emballages','matieres_premieres','produits_chimiques','carburant') THEN montant ELSE 0 END) as ci")
            ->selectRaw('COUNT(*) as nb_transactions')
            ->selectRaw("SUM(CASE WHEN type = 'depense' THEN 1 ELSE 0 END) as nb_depenses")
            ->selectRaw("SUM(CASE WHEN type = 'recette' THEN 1 ELSE 0 END) as nb_recettes")
            ->selectRaw('MAX(updated_at) as derniere_saisie')
            ->first();

        return $this->normaliserIndicateurs(
            (float) ($row->pb ?? 0),
            (float) ($row->cv ?? 0),
            (float) ($row->cf ?? 0),
            (float) ($row->ci ?? 0),
            (int) ($row->nb_transactions ?? 0),
            (int) ($row->nb_depenses ?? 0),
            (int) ($row->nb_recettes ?? 0),
            $row->derniere_saisie ?? null
        );
    }

    private function calculerExploitationSansCache(Exploitation $exploitation, ?string $dateDebutMin = null): array
    {
        $activites = $exploitation->activitesActives;
        if ($activites->isEmpty()) {
            return [
                'par_activite' => [],
                'consolide' => [
                    'PB' => 0.0,
                    'CT' => 0.0,
                    'MB' => 0.0,
                    'RNE' => 0.0,
                    'RF' => 0,
                    'statut' => 'rouge',
                ],
            ];
        }

        $indicateurs = $this->calculerPourActivites($activites->pluck('id'), null, null, $dateDebutMin);

        $parActivite = [];
        foreach ($activites as $activite) {
            $parActivite[$activite->id] = array_merge(
                ['nom' => $activite->nom, 'type' => $activite->type],
                $indicateurs[$activite->id] ?? $this->normaliserIndicateurs(0, 0, 0, 0, 0, 0, 0, null)
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

    private function evolutionMensuelleSansCache(int $activiteId, ?string $dateDebutMin = null): array
    {
        Activite::query()->findOrFail($activiteId);

        $evolution = [];
        $startWindow = now()->startOfMonth()->subMonths(11)->toDateString();
        $endWindow = now()->endOfMonth()->toDateString();
        $effectiveStart = $this->mergeDateDebut($startWindow, $dateDebutMin);

        $rows = Transaction::query()
            ->where('activite_id', $activiteId)
            ->where('date_transaction', '>=', $effectiveStart)
            ->where('date_transaction', '<=', $endWindow)
            ->selectRaw("DATE_FORMAT(date_transaction, '%Y-%m') as mois_num")
            ->selectRaw("SUM(CASE WHEN type = 'recette' THEN montant ELSE 0 END) as pb")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'variable' THEN montant ELSE 0 END) as cv")
            ->selectRaw("SUM(CASE WHEN type = 'depense' AND nature = 'fixe' THEN montant ELSE 0 END) as cf")
            ->groupBy('mois_num')
            ->get()
            ->keyBy('mois_num');

        for ($i = 11; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $moisNum = $mois->format('Y-m');
            $end = $mois->copy()->endOfMonth()->toDateString();

            if ($dateDebutMin && $end < $dateDebutMin) {
                $evolution[] = ['mois' => $mois->format('M Y'), 'mois_num' => $moisNum, 'MB' => 0.0, 'RNE' => 0.0, 'PB' => 0.0, 'CT' => 0.0];
                continue;
            }

            $row = $rows->get($moisNum);
            $PB = (float) ($row->pb ?? 0);
            $CV = (float) ($row->cv ?? 0);
            $CF = (float) ($row->cf ?? 0);
            $CT = $CV + $CF;
            $MB = $PB - $CV;
            $RNE = $PB - $CT;

            $evolution[] = [
                'mois' => $mois->format('M Y'),
                'mois_num' => $moisNum,
                'MB' => round($MB, 2),
                'RNE' => round($RNE, 2),
                'PB' => round($PB, 2),
                'CT' => round($CT, 2),
            ];
        }

        return $evolution;
    }

    private function normaliserIndicateurs(
        float $PB,
        float $CV,
        float $CF,
        float $CI,
        int $nbTransactions,
        int $nbDepenses,
        int $nbRecettes,
        mixed $derniereSaisie
    ): array {
        $CT = $CV + $CF;
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
            'nb_transactions' => $nbTransactions,
            'nb_depenses' => $nbDepenses,
            'nb_recettes' => $nbRecettes,
            'derniere_saisie' => $derniereSaisie,
        ];
    }

    /**
     * Garde la borne la plus récente entre une période demandée et le plancher d’abonnement.
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

    private function getActivityCacheVersion(int $activiteId): int
    {
        $key = "fsa:activity:version:{$activiteId}";
        Cache::add($key, 1, now()->addDays(30));

        return (int) Cache::get($key, 1);
    }

    private function getExploitationCacheVersion(int $exploitationId): int
    {
        $key = "fsa:exploitation:version:{$exploitationId}";
        Cache::add($key, 1, now()->addDays(30));

        return (int) Cache::get($key, 1);
    }

    private function bumpActivityCacheVersion(int $activiteId): void
    {
        $key = "fsa:activity:version:{$activiteId}";
        Cache::add($key, 1, now()->addDays(30));
        Cache::increment($key);
    }

    private function bumpExploitationCacheVersion(int $exploitationId): void
    {
        $key = "fsa:exploitation:version:{$exploitationId}";
        Cache::add($key, 1, now()->addDays(30));
        Cache::increment($key);
    }
}
