<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpCategory extends Model
{
    protected $fillable = [
        'nom', 'slug', 'icone', 'description', 'ordre', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    /** Articles actifs uniquement — utilisé par le site public et withCount('articles'). */
    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'help_category_id')
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('id');
    }

    /** Tous les articles (futur back-office). */
    public function tousLesArticles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'help_category_id')
            ->orderBy('ordre')
            ->orderBy('id');
    }
}
