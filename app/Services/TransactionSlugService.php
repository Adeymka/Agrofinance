<?php

namespace App\Services;

use App\Helpers\TransactionCategories;
use App\Models\Exploitation;

/**
 * Slugs de catégories autorisés pour une exploitation — même logique Web et API.
 */
final class TransactionSlugService
{
    /**
     * Agrège les slugs de tous les types d’activités actives de l’exploitation (aligné saisie web).
     *
     * @param  'depense'|'recette'  $transactionType
     * @return list<string>
     */
    public static function allowedSlugsForExploitation(?Exploitation $exploitation, string $transactionType): array
    {
        if (! $exploitation) {
            return TransactionCategories::flatSlugsForTransactionType('cultures_vivrieres', $transactionType);
        }

        $typesActivites = $exploitation
            ->activitesActives()
            ->distinct('type')
            ->pluck('type')
            ->filter(fn ($type) => $type !== null)
            ->toArray();

        if ($typesActivites === []) {
            return TransactionCategories::flatSlugsForTransactionType('cultures_vivrieres', $transactionType);
        }

        $allSlugs = [];
        foreach ($typesActivites as $type) {
            $slugs = TransactionCategories::flatSlugsForTransactionType($type, $transactionType);
            $allSlugs = array_merge($allSlugs, $slugs);
        }

        return array_values(array_unique($allSlugs));
    }
}
