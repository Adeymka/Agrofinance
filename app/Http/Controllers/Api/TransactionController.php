<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnementService
    ) {}

    public function index(Request $request)
    {
        $query = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        });

        $floor = $this->abonnementService->dateDebutHistorique(auth()->user());
        $floorStr = $floor?->toDateString();

        // Filtres optionnels
        if ($request->activite_id) {
            $query->where('activite_id', $request->activite_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->categorie) {
            $query->where('categorie', $request->categorie);
        }
        $dateDebutEffective = $request->date_debut;
        if ($floorStr) {
            $dateDebutEffective = $dateDebutEffective
                ? max($floorStr, $dateDebutEffective)
                : $floorStr;
        }
        if ($dateDebutEffective) {
            $query->where('date_transaction', '>=', $dateDebutEffective);
        }
        if ($request->date_fin) {
            $query->where('date_transaction', '<=', $request->date_fin);
        }

        return response()->json([
            'succes' => true,
            'data' => $query->latest('date_transaction')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transactions' => 'required|array|min:1',
            'transactions.*.activite_id' => 'required|integer|exists:activites,id',
            'transactions.*.type' => 'required|in:depense,recette',
            'transactions.*.nature' => 'nullable|in:fixe,variable',
            'transactions.*.categorie' => 'required|string|max:100',
            'transactions.*.montant' => 'required|numeric|min:0',
            'transactions.*.date_transaction' => 'required|date',
            'transactions.*.note' => 'nullable|string|max:500',
            'transactions.*.est_imprevue' => 'boolean',
        ]);

        $creees = [];

        foreach ($request->transactions as $data) {
            // Sécurité : vérifier que l'activité appartient à l'utilisateur
            $activite = Activite::whereHas('exploitation', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })->findOrFail($data['activite_id']);

            $creees[] = Transaction::create([
                'activite_id' => $activite->id,
                'type' => $data['type'],
                // Les recettes n'ont pas de nature
                'nature' => $data['type'] === 'recette'
                                        ? null
                                        : ($data['nature'] ?? 'variable'),
                'categorie' => $data['categorie'],
                'montant' => $data['montant'],
                'date_transaction' => $data['date_transaction'],
                'note' => $data['note'] ?? null,
                'est_imprevue' => $data['est_imprevue'] ?? false,
                'synced' => true,
            ]);
        }

        // Recalculer les indicateurs FSA pour chaque activité touchée
        $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();

        $idsUniques = collect($creees)->pluck('activite_id')->unique();
        $indicateurs = [];
        foreach ($idsUniques as $id) {
            $indicateurs[$id] = $this->indicateurs->calculer($id, null, null, $floor);
        }

        return response()->json([
            'succes' => true,
            'transactions_synchronisees' => count($creees),
            'indicateurs' => $indicateurs,
        ], 201);
    }

    public function show(int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        return response()->json([
            'succes' => true,
            'data' => $transaction,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $request->validate([
            'type' => 'sometimes|in:depense,recette',
            'nature' => 'nullable|in:fixe,variable',
            'categorie' => 'sometimes|string|max:100',
            'montant' => 'sometimes|numeric|min:0',
            'date_transaction' => 'sometimes|date',
            'note' => 'nullable|string|max:500',
            'est_imprevue' => 'boolean',
        ]);

        $transaction->update($request->only([
            'type', 'nature', 'categorie', 'montant',
            'date_transaction', 'note', 'est_imprevue',
        ]));

        if ($transaction->type === 'recette') {
            $transaction->update(['nature' => null]);
        }

        $indicateurs = $this->indicateurs->calculer($transaction->activite_id);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction mise à jour.',
            'data' => $transaction->fresh(),
            'indicateurs' => $indicateurs,
        ]);
    }

    public function destroy(int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $activiteId = $transaction->activite_id;
        $transaction->delete();

        $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();
        $indicateurs = $this->indicateurs->calculer($activiteId, null, null, $floor);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction supprimée.',
            'indicateurs' => $indicateurs,
        ]);
    }
}
