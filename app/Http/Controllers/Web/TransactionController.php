<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Activite, Transaction};
use App\Services\FinancialIndicatorsService;
use App\Support\TransactionCategories;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $fsa
    ) {}

    public function create(Request $request)
    {
        $uid = (int) auth()->user()->id;

        $activites = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom')
            ->get();

        $activiteSelectionnee = $request->query('activite_id');
        if ($activiteSelectionnee && ! $activites->contains(fn ($a) => (int) $a->id === (int) $activiteSelectionnee)) {
            $activiteSelectionnee = null;
        }

        $categories = TransactionCategories::tree();

        return view('transactions.create', compact(
            'activites',
            'activiteSelectionnee',
            'categories'
        ) + ['nav' => 'saisie']);
    }

    public function store(Request $request)
    {
        $depSlugs = TransactionCategories::slugsForType('depense');
        $recSlugs = TransactionCategories::slugsForType('recette');

        $rules = [
            'activite_id'      => 'required|integer|exists:activites,id',
            'type'             => 'required|in:depense,recette',
            'categorie'        => ['required', 'string', 'max:100'],
            'montant'          => 'required|numeric|min:1',
            'date_transaction' => 'required|date',
            'note'             => 'nullable|string|max:500',
            'est_imprevue'     => 'boolean',
        ];

        if ($request->type === 'depense') {
            $rules['nature'] = 'required|in:fixe,variable';
            $rules['categorie'][] = Rule::in($depSlugs);
        } else {
            $rules['categorie'][] = Rule::in($recSlugs);
        }

        $request->validate($rules);

        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', (int) auth()->user()->id);
        })->findOrFail($request->activite_id);

        Transaction::create([
            'activite_id'       => $request->activite_id,
            'type'              => $request->type,
            'nature'            => $request->type === 'recette' ? null : $request->nature,
            'categorie'         => $request->categorie,
            'montant'           => $request->montant,
            'date_transaction'  => $request->date_transaction,
            'note'              => $request->note,
            'est_imprevue'      => $request->boolean('est_imprevue'),
            'synced'            => true,
        ]);

        $activite->refresh();

        $alerteMsg = null;
        if ($activite->budget_previsionnel && (float) $activite->budget_previsionnel > 0) {
            $indicateurs = $this->fsa->calculer($activite->id);
            $ct = (float) ($indicateurs['CT'] ?? 0);
            $p  = ($ct / (float) $activite->budget_previsionnel) * 100;
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
            ->with('activite')
            ->findOrFail($id);

        $categories = TransactionCategories::tree();

        $activites = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom')
            ->get();

        return view('transactions.edit', compact(
            'transaction',
            'categories',
            'activites'
        ) + ['nav' => 'saisie']);
    }

    public function update(Request $request, int $id)
    {
        $uid = (int) auth()->user()->id;

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->findOrFail($id);

        $depSlugs = TransactionCategories::slugsForType('depense');
        $recSlugs = TransactionCategories::slugsForType('recette');

        $rules = [
            'activite_id'      => 'required|integer|exists:activites,id',
            'type'             => 'required|in:depense,recette',
            'categorie'        => ['required', 'string', 'max:100'],
            'montant'          => 'required|numeric|min:1',
            'date_transaction' => 'required|date',
            'note'             => 'nullable|string|max:500',
            'est_imprevue'     => 'boolean',
        ];

        if ($request->type === 'depense') {
            $rules['nature'] = 'required|in:fixe,variable';
            $rules['categorie'][] = Rule::in($depSlugs);
        } else {
            $rules['categorie'][] = Rule::in($recSlugs);
        }

        $request->validate($rules);

        Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', (int) auth()->user()->id);
        })->findOrFail($request->activite_id);

        $transaction->update([
            'activite_id'      => $request->activite_id,
            'type'             => $request->type,
            'nature'           => $request->type === 'recette' ? null : $request->input('nature'),
            'categorie'        => $request->categorie,
            'montant'          => $request->montant,
            'date_transaction' => $request->date_transaction,
            'note'             => $request->note,
            'est_imprevue'     => $request->boolean('est_imprevue'),
        ]);

        return redirect()->route('activites.show', $transaction->activite_id)
            ->with('success', 'Transaction modifiée.');
    }

    public function destroy(int $id)
    {
        $uid = (int) auth()->user()->id;

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->findOrFail($id);

        $activiteId = $transaction->activite_id;
        $transaction->delete();

        return redirect()->route('activites.show', $activiteId)
            ->with('success', 'Transaction supprimée.');
    }
}
