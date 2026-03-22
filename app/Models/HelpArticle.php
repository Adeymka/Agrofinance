<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class HelpArticle extends Model
{
    protected $fillable = [
        'help_category_id', 'titre', 'slug', 'resume',
        'contenu', 'contenu_texte', 'mots_cles',
        'ordre', 'actif', 'vues',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HelpArticle $article): void {
            $article->contenu_texte = strip_tags((string) $article->contenu);
        });
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HelpArticleImage::class, 'help_article_id')
            ->orderBy('ordre')
            ->orderBy('id');
    }

    public function incrementerVues(): void
    {
        $this->increment('vues');
    }

    /**
     * Recherche : FULLTEXT en priorité, puis LIKE sur les non classés (sans doublons).
     */
    public static function rechercher(string $query): Collection
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return new Collection;
        }

        $boolean = $query.'*';
        $fulltext = new Collection;

        try {
            $fulltext = static::query()
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
                ->limit(20)
                ->get();
        } catch (Throwable) {
            $fulltext = new Collection;
        }

        if ($fulltext->count() >= 20) {
            return $fulltext;
        }

        $pattern = '%'.static::escapeLike($query).'%';
        $ids = $fulltext->pluck('id')->all();

        $likeQuery = static::query()
            ->where('actif', true)
            ->where(function ($q) use ($pattern): void {
                $q->where('titre', 'like', $pattern)
                    ->orWhere('mots_cles', 'like', $pattern)
                    ->orWhere('resume', 'like', $pattern)
                    ->orWhere('contenu_texte', 'like', $pattern);
            })
            ->with('categorie')
            ->orderByDesc('vues')
            ->orderBy('id');

        if ($ids !== []) {
            $likeQuery->whereNotIn('id', $ids);
        }

        $like = $likeQuery->limit(20 - $fulltext->count())->get();

        return $fulltext->merge($like)->values();
    }

    public static function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    /**
     * Article précédent dans la catégorie (tie-break sur id).
     */
    public function precedentDansCategorie(): ?self
    {
        return static::query()
            ->where('help_category_id', $this->help_category_id)
            ->where('actif', true)
            ->where(function ($q): void {
                $q->where('ordre', '<', $this->ordre)
                    ->orWhere(function ($q2): void {
                        $q2->where('ordre', '=', $this->ordre)
                            ->where('id', '<', $this->id);
                    });
            })
            ->orderByDesc('ordre')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Article suivant dans la catégorie (tie-break sur id).
     */
    public function suivantDansCategorie(): ?self
    {
        return static::query()
            ->where('help_category_id', $this->help_category_id)
            ->where('actif', true)
            ->where(function ($q): void {
                $q->where('ordre', '>', $this->ordre)
                    ->orWhere(function ($q2): void {
                        $q2->where('ordre', '=', $this->ordre)
                            ->where('id', '>', $this->id);
                    });
            })
            ->orderBy('ordre')
            ->orderBy('id')
            ->first();
    }
}
