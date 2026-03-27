<?php

namespace App\Http\Controllers\Web;

use App\Helpers\TransactionCategories;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\ExploitationCategorieSuggestionService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $fsa,
        private AbonnementService $abonnementService,
        private ExploitationCategorieSuggestionService $categorieSuggestionService
    ) {}

    public function index()
    {
        $uid = (int) auth()->user()->id;

        $transactions = Transaction::query()
            ->whereHas('activite.exploitation', fn ($q) => $q->where('user_id', $uid))
            ->with(['activite:id,nom'])
            ->orderByDesc('date_transaction')
            ->orderByDesc('id')
            ->paginate(20);

        return view('transactions.index', compact('transactions') + ['nav' => 'saisie']);
    }

    public function create(Request $request)
    {
        $uid = (int) auth()->user()->id;

        $activites = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom,type')
            ->get();

        $activiteSelectionnee = $request->query('activite_id');
        if ($activiteSelectionnee && ! $activites->contains(fn ($a) => (int) $a->id === (int) $activiteSelectionnee)) {
            $activiteSelectionnee = null;
        }

        $activitePourType = $activites->firstWhere('id', (int) ($activiteSelectionnee ?? $activites->first()?->id));
        $typeExploitation = $activitePourType?->exploitation?->type
            ?? Exploitation::where('user_id', $uid)->first()?->type
            ?? 'cultures_vivrieres';
        $categories = TransactionCategories::getByType($typeExploitation);

        $suggestionsByExploitation = $this->categorieSuggestionService->groupedForExploitationIds(
            $activites->pluck('exploitation_id')
        );
        $activiteVersExploitation = $activites->mapWithKeys(fn ($a) => [$a->id => $a->exploitation_id]);

        return view('transactions.create', compact(
            'activites',
            'activiteSelectionnee',
            'categories',
            'typeExploitation',
            'suggestionsByExploitation',
            'activiteVersExploitation'
        ) + ['nav' => 'saisie']);
    }

    public function store(Request $request)
    {
        $uid = (int) auth()->user()->id;

        $rules = [
            'activite_id' => 'required|integer|exists:activites,id',
            'type' => 'required|in:depense,recette',
            'categorie' => 'nullable|string|max:100',
            'categorie_libre' => 'nullable|string|max:100',
            'montant' => 'required|numeric|min:1',
            'date_transaction' => 'required|date',
            'note' => 'nullable|string|max:500',
            'est_imprevue' => 'boolean',
        ];

        if ($request->type === 'depense') {
            $rules['nature'] = 'required|in:fixe,variable';
        }

        $request->validate($rules);

        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', (int) auth()->user()->id);
        })->with('exploitation:id,type')->findOrFail($request->activite_id);

        if ($activite->statut !== Activite::STATUT_EN_COURS) {
            throw ValidationException::withMessages([
                'activite_id' => ['Cette campagne n’accepte plus de nouvelles transactions.'],
            ]);
        }

        $typeExploitation = $activite->exploitation?->type ?? 'cultures_vivrieres';

        $libre = trim((string) $request->input('categorie_libre', ''));
        $categorie = $libre !== '' ? $libre : trim((string) $request->input('categorie', ''));

        if ($categorie === '') {
            throw ValidationException::withMessages([
                'categorie' => ['Choisissez une catégorie ou écrivez la vôtre.'],
            ]);
        }

        if ($libre === '') {
            $allowed = TransactionCategories::flatSlugsForTransactionType($typeExploitation, $request->type);
            if (! in_array($categorie, $allowed, true)) {
                throw ValidationException::withMessages([
                    'categorie' => ['Catégorie invalide pour votre type d’exploitation.'],
                ]);
            }
        }

        Transaction::create([
            'activite_id' => $request->activite_id,
            'type' => $request->type,
            'nature' => $request->type === 'recette' ? null : $request->nature,
            'categorie' => $categorie,
            'montant' => $request->montant,
            'date_transaction' => $request->date_transaction,
            'note' => $request->note,
            'est_imprevue' => $request->boolean('est_imprevue'),
            'synced' => true,
        ]);

        $allowedFsa = TransactionCategories::flatSlugsForTransactionType($typeExploitation, $request->type);
        $this->categorieSuggestionService->recordIfCustom(
            (int) $activite->exploitation_id,
            $request->type,
            $categorie,
            $allowedFsa
        );

        $activite->refresh();

        $alerteMsg = null;
        if ($activite->budget_previsionnel && (float) $activite->budget_previsionnel > 0) {
            $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();
            $indicateurs = $this->fsa->calculer($activite->id, null, null, $floor);
            $ct = (float) ($indicateurs['CT'] ?? 0);
            $p = ($ct / (float) $activite->budget_previsionnel) * 100;
            if ($p >= 100) {
                $alerteMsg = '⚠️ Budget dépassé ! '.round($p).'% consommé.';
            } elseif ($p >= 90) {
                $alerteMsg = '⚠️ Attention : '.round($p).'% du budget consommé.';
            }
        }

        $redirect = redirect()->route('activites.show', $activite->id)
            ->with('success', 'Transaction enregistrée ✓');

        if ($alerteMsg) {
            $redirect->with('alerte', $alerteMsg);
        }

        return $redirect;
    }

    public function edit(int $id)
    {
        $uid = (int) auth()->user()->id;

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->with('activite.exploitation:id,type,nom')
            ->findOrFail($id);

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            return redirect()->route('activites.show', $transaction->activite_id)
                ->with('error', 'Les transactions d’une campagne terminée ou abandonnée ne sont plus modifiables.');
        }

        $typeExploitation = $transaction->activite->exploitation?->type ?? 'cultures_vivrieres';
        $categories = TransactionCategories::getByType($typeExploitation);

        $allowedNow = TransactionCategories::flatSlugsForTransactionType(
            $typeExploitation,
            $transaction->type
        );
        $categorieSlugDefault = in_array($transaction->categorie, $allowedNow, true)
            ? $transaction->categorie
            : '';
        $categorieLibreDefault = $categorieSlugDefault === '' ? $transaction->categorie : '';

        $categorieLibre = old('categorie_libre', $categorieLibreDefault);
        $categorieSelectionnee = (trim((string) $categorieLibre) !== '')
            ? ''
            : old('categorie', $categorieSlugDefault);

        $activites = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom')
            ->get();

        $suggestionsByExploitation = $this->categorieSuggestionService->groupedForExploitationIds(
            $activites->pluck('exploitation_id')
        );
        $activiteVersExploitation = $activites->mapWithKeys(fn ($a) => [$a->id => $a->exploitation_id]);

        return view('transactions.edit', compact(
            'transaction',
            'categories',
            'activites',
            'typeExploitation',
            'categorieSelectionnee',
            'categorieLibre',
            'suggestionsByExploitation',
            'activiteVersExploitation'
        ) + ['nav' => 'saisie']);
    }

    public function update(Request $request, int $id)
    {
        $uid = (int) auth()->user()->id;

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('activite.exploitation:id,type')->findOrFail($id);

        $rules = [
            'activite_id' => 'required|integer|exists:activites,id',
            'type' => 'required|in:depense,recette',
            'categorie' => 'nullable|string|max:100',
            'categorie_libre' => 'nullable|string|max:100',
            'montant' => 'required|numeric|min:1',
            'date_transaction' => 'required|date',
            'note' => 'nullable|string|max:500',
            'est_imprevue' => 'boolean',
        ];

        if ($request->type === 'depense') {
            $rules['nature'] = 'required|in:fixe,variable';
        }

        $request->validate($rules);

        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', (int) auth()->user()->id);
        })->with('exploitation:id,type')->findOrFail($request->activite_id);

        if ($activite->statut !== Activite::STATUT_EN_COURS) {
            throw ValidationException::withMessages([
                'activite_id' => ['Cette campagne n’accepte plus de modifications de transactions.'],
            ]);
        }

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            throw ValidationException::withMessages([
                'activite_id' => ['Cette transaction n’est plus modifiable (campagne terminée ou abandonnée).'],
            ]);
        }

        $typeExploitation = $activite->exploitation?->type ?? 'cultures_vivrieres';

        $libre = trim((string) $request->input('categorie_libre', ''));
        $categorie = $libre !== '' ? $libre : trim((string) $request->input('categorie', ''));

        if ($categorie === '') {
            throw ValidationException::withMessages([
                'categorie' => ['Choisissez une catégorie ou écrivez la vôtre.'],
            ]);
        }

        if ($libre === '') {
            $allowed = TransactionCategories::flatSlugsForTransactionType($typeExploitation, $request->type);
            if (! in_array($categorie, $allowed, true)) {
                throw ValidationException::withMessages([
                    'categorie' => ['Catégorie invalide pour votre type d’exploitation.'],
                ]);
            }
        }

        $transaction->update([
            'activite_id' => $request->activite_id,
            'type' => $request->type,
            'nature' => $request->type === 'recette' ? null : $request->input('nature'),
            'categorie' => $categorie,
            'montant' => $request->montant,
            'date_transaction' => $request->date_transaction,
            'note' => $request->note,
            'est_imprevue' => $request->boolean('est_imprevue'),
        ]);

        $allowedFsa = TransactionCategories::flatSlugsForTransactionType($typeExploitation, $request->type);
        $this->categorieSuggestionService->recordIfCustom(
            (int) $activite->exploitation_id,
            $request->type,
            $categorie,
            $allowedFsa
        );

        return redirect()->route('activites.show', $transaction->activite_id)
            ->with('success', 'Transaction modifiée.');
    }

    public function destroy(int $id)
    {
        $uid = (int) auth()->user()->id;

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('activite')->findOrFail($id);

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une transaction sur une campagne terminée ou abandonnée.');
        }

        $activiteId = $transaction->activite_id;
        $transaction->delete();

        return redirect()->route('activites.show', $activiteId)
            ->with('success', 'Transaction supprimée.');
    }
}
