<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $categories = HelpCategory::query()
            ->where('actif', true)
            ->withCount('articles')
            ->orderBy('ordre')
            ->orderBy('id')
            ->get();

        $populaires = HelpArticle::query()
            ->where('actif', true)
            ->orderByDesc('vues')
            ->orderBy('id')
            ->with('categorie')
            ->take(6)
            ->get();

        return view('public.aide.index', compact('categories', 'populaires'));
    }

    public function recherche(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        $articles = HelpArticle::rechercher($q);

        return response()->json([
            'resultats' => $articles->map(fn (HelpArticle $a) => [
                'titre' => $a->titre,
                'resume' => $a->resume ?? (mb_substr(strip_tags($a->contenu), 0, 120).(mb_strlen(strip_tags($a->contenu)) > 120 ? '…' : '')),
                'url' => route('aide.article', [
                    'categorie' => $a->categorie->slug,
                    'article' => $a->slug,
                ]),
                'categorie' => $a->categorie->nom,
                'icone' => $a->categorie->icone,
            ]),
        ]);
    }

    public function categorie(string $slug): View
    {
        $categorie = HelpCategory::query()
            ->where('slug', $slug)
            ->where('actif', true)
            ->firstOrFail();

        $articles = $categorie->articles()->with('categorie')->get();

        $toutesCategories = HelpCategory::query()
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('id')
            ->get();

        return view('public.aide.categorie', compact('categorie', 'articles', 'toutesCategories'));
    }

    public function article(string $categorieSlug, string $articleSlug): View
    {
        $categorie = HelpCategory::query()
            ->where('slug', $categorieSlug)
            ->where('actif', true)
            ->firstOrFail();

        $article = HelpArticle::query()
            ->where('slug', $articleSlug)
            ->where('help_category_id', $categorie->id)
            ->where('actif', true)
            ->with(['images', 'categorie'])
            ->firstOrFail();

        $article->incrementerVues();
        $article->refresh();

        $precedent = $article->precedentDansCategorie();
        $suivant = $article->suivantDansCategorie();

        $articlesCategorie = $categorie->articles()->get();

        return view('public.aide.article', compact(
            'categorie',
            'article',
            'articlesCategorie',
            'precedent',
            'suivant'
        ));
    }
}
