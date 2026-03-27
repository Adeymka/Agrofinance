<?php

namespace App\Services;

use App\Models\HelpArticle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * HelpSearchService — Logique de recherche dans le centre d'aide.
 *
 * Extrait de HelpArticle::rechercher() (#32) pour respecter le SRP :
 * le modele Eloquent ne doit pas porter la logique de recherche complexe.
 *
 * Algorithme : FULLTEXT BOOLEAN MODE en priorite (MySQL), fallback LIKE multi-champs.
 * Cache : 5 minutes (les articles du centre d'aide changent rarement).
 */
class HelpSearchService
{
    private const CACHE_TTL_MINUTES = 5;
    private const MAX_RESULTS       = 20;
    private const MIN_QUERY_LENGTH  = 2;

    /**
     * Recherche des articles du centre d'aide.
     *
     * @param  string  $query  Terme saisi par l'utilisateur (min 2 caracteres)
     * @return Collection<int, HelpArticle>  Articles tries par pertinence
     */
    public function rechercher(string $query): Collection
    {
        $query = trim($query);

        if (strlen($query) < self::MIN_QUERY_LENGTH) {
            return new Collection;
        }

        // Cache par terme de recherche (insensible a la casse)
        $cacheKey = 'help:search:' . md5(mb_strtolower($query));

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($query) {
            return $this->rechercherSansCache($query);
        });
    }

    /**
     * Invalide le cache de recherche (a appeler lors de la modification d'articles).
     * Note : le cache peut etre invalide globalement via php artisan cache:clear.
     */
    public function invaliderCache(): void
    {
        // Le cache par terme ne peut pas etre invalide selectivement sans Redis Tags.
        // En production avec Redis, utiliser Cache::tags(['help'])->flush().
        // Pour l'instant on laisse expirer naturellement (TTL 5 min).
    }

    /**
     * Recherche sans cache : FULLTEXT puis LIKE complementaire.
     *
     * @param  string  $query  Terme propre (deja trimme)
     * @return Collection<int, HelpArticle>
     */
    private function rechercherSansCache(string $query): Collection
    {
        $boolean  = $query . '*';
        $fulltext = new Collection;

        // Etape 1 : FULLTEXT BOOLEAN MODE (performant, supporte la troncature)
        try {
            $fulltext = HelpArticle::query()
                ->whereRaw(
                    'MATCH(titre, contenu_texte, mots_cles) AGAINST(? IN BOOLEAN MODE)',
                    [$boolean]
                )
                ->where('actif', true)
                ->with('categorie')
                ->orderByRaw(
                    'MATCH(titre, contenu_texte, mots_cles) AGAINST(? IN BOOLEAN MODE) DESC',
                    [$boolean]
                )
                ->limit(self::MAX_RESULTS)
                ->get();
        } catch (\Throwable) {
            // Index FULLTEXT absent (env test, SQLite) → repli sur LIKE
            $fulltext = new Collection;
        }

        if ($fulltext->count() >= self::MAX_RESULTS) {
            return $fulltext;
        }

        // Etape 2 : LIKE complementaire pour completer sans doublons
        $pattern = '%' . HelpArticle::escapeLike($query) . '%';
        $ids     = $fulltext->pluck('id')->all();

        $likeQuery = HelpArticle::query()
            ->where('actif', true)
            ->where(function ($q) use ($pattern) {
                $q->where('titre',        'like', $pattern)
                  ->orWhere('mots_cles',  'like', $pattern)
                  ->orWhere('resume',     'like', $pattern)
                  ->orWhere('contenu_texte', 'like', $pattern);
            })
            ->with('categorie')
            ->orderByDesc('vues')
            ->orderBy('id');

        if ($ids !== []) {
            $likeQuery->whereNotIn('id', $ids);
        }

        $like = $likeQuery->limit(self::MAX_RESULTS - $fulltext->count())->get();

        return $fulltext->merge($like)->values();
    }
}
