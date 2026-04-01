<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TransactionCategories;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use App\Services\TransactionJustificatifService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnementService,
        private TransactionJustificatifService $justificatifService
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
        if (in_array((string) $request->statut_validation, [
            Transaction::STATUT_VALIDATION_EN_ATTENTE,
            Transaction::STATUT_VALIDATION_VALIDEE,
        ], true)) {
            $query->where('statut_validation', (string) $request->statut_validation);
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
            'transactions.*.client_uuid' => 'nullable|uuid',
        ]);

        foreach ($request->transactions as $i => $data) {
            $activite = Activite::pourUtilisateur((int) auth()->user()->id)
                ->with('exploitation:id,type')
                ->findOrFail($data['activite_id']);

            if ($activite->statut !== Activite::STATUT_EN_COURS) {
                return response()->json([
                    'succes' => false,
                    'message' => 'La campagne n’accepte plus de nouvelles transactions (statut : '.$activite->statut.').',
                ], 422);
            }

            $typeExploitation = $activite->exploitation?->type ?? 'cultures_vivrieres';
            $slugConnu = in_array(
                $data['categorie'],
                TransactionCategories::flatSlugsForTransactionType($typeExploitation, $data['type']),
                true
            );

            if (($data['type'] ?? '') === 'depense'
                && ! $slugConnu
                && ! TransactionCategories::estSlugChargesIntermediaires($data['categorie'])) {
                if (! array_key_exists('intrant_production', $data)) {
                    return response()->json([
                        'succes' => false,
                        'message' => 'Pour une dépense dont la catégorie n’est pas un intrant standard, indiquez « intrant_production » (true ou false) : cet achat sert-il la production de la campagne ? (transaction #'.($i + 1).')',
                    ], 422);
                }
            }
        }

        $creees = [];
        $userId = auth()->user()->id;
        $estCoop = $this->abonnementService->estPlanCooperatif(auth()->user());
        $statutValidationParDefaut = $estCoop
            ? Transaction::STATUT_VALIDATION_EN_ATTENTE
            : Transaction::STATUT_VALIDATION_VALIDEE;

        foreach ($request->transactions as $data) {
            $activite = Activite::pourUtilisateur((int) $userId)
                ->with('exploitation:id,type')
                ->findOrFail($data['activite_id']);

            if (! empty($data['client_uuid'])) {
                $existante = Transaction::query()
                    ->where('client_uuid', $data['client_uuid'])
                    ->whereHas('activite.exploitation', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->first();

                if ($existante) {
                    $creees[] = $existante;

                    continue;
                }
            }

            $typeExploitation = $activite->exploitation?->type ?? 'cultures_vivrieres';
            $slugConnu = in_array(
                $data['categorie'],
                TransactionCategories::flatSlugsForTransactionType($typeExploitation, $data['type']),
                true
            );

            $nature = null;
            $intrant = null;
            if ($data['type'] === 'depense') {
                if ($slugConnu) {
                    $nature = TransactionCategories::natureDepensePourSlug($data['categorie']);
                    $intrant = TransactionCategories::intrantProductionPourSlugDepense($data['categorie']);
                } else {
                    $nature = $data['nature'] ?? 'variable';
                    $intrant = TransactionCategories::estSlugChargesIntermediaires($data['categorie'])
                        ? null
                        : (bool) ($data['intrant_production'] ?? false);
                }
            }

            $creees[] = Transaction::create([
                'client_uuid' => $data['client_uuid'] ?? null,
                'activite_id' => $activite->id,
                'type' => $data['type'],
                'nature' => $data['type'] === 'recette' ? null : $nature,
                'categorie' => $data['categorie'],
                'intrant_production' => $intrant,
                'montant' => $data['montant'],
                'date_transaction' => $data['date_transaction'],
                'note' => $data['note'] ?? null,
                'est_imprevue' => $data['est_imprevue'] ?? false,
                'synced' => true,
                'statut_validation' => $statutValidationParDefaut,
                'validee_par_user_id' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? (int) $userId : null,
                'validee_le' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? now() : null,
            ]);
        }

        // Recalculer les indicateurs financiers agricoles pour chaque activité touchée
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
        })->with('activite.exploitation:id,type')->findOrFail($id);

        $request->validate([
            'type' => 'sometimes|in:depense,recette',
            'nature' => 'nullable|in:fixe,variable',
            'categorie' => 'sometimes|string|max:100',
            'intrant_production' => 'nullable|boolean',
            'montant' => 'sometimes|numeric|min:0',
            'date_transaction' => 'sometimes|date',
            'note' => 'nullable|string|max:500',
            'est_imprevue' => 'boolean',
        ]);

        $payload = $request->only([
            'type', 'nature', 'categorie', 'montant',
            'date_transaction', 'note', 'est_imprevue',
        ]);
        $estCoop = $this->abonnementService->estPlanCooperatif(auth()->user());
        $statutValidationParDefaut = $estCoop
            ? Transaction::STATUT_VALIDATION_EN_ATTENTE
            : Transaction::STATUT_VALIDATION_VALIDEE;

        $cat = $request->input('categorie', $transaction->categorie);
        $typeEff = $request->input('type', $transaction->type);
        $typeExploitation = $transaction->activite->exploitation?->type ?? 'cultures_vivrieres';
        $slugConnu = in_array(
            $cat,
            TransactionCategories::flatSlugsForTransactionType($typeExploitation, $typeEff),
            true
        );

        if ($typeEff === 'depense') {
            if ($slugConnu) {
                $payload['nature'] = TransactionCategories::natureDepensePourSlug($cat);
                $payload['intrant_production'] = TransactionCategories::intrantProductionPourSlugDepense($cat);
            } elseif (! TransactionCategories::estSlugChargesIntermediaires($cat)) {
                $request->validate(['intrant_production' => 'required|boolean']);
                $payload['nature'] = $request->input('nature', $transaction->nature) ?? 'variable';
                $payload['intrant_production'] = $request->boolean('intrant_production');
            } else {
                $payload['nature'] = $request->input('nature', $transaction->nature) ?? 'variable';
                $payload['intrant_production'] = null;
            }
        } else {
            $payload['nature'] = null;
            $payload['intrant_production'] = null;
        }

        $transaction->update($payload);
        $transaction->update([
            'statut_validation' => $statutValidationParDefaut,
            'validee_par_user_id' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE
                ? (int) auth()->user()->id
                : null,
            'validee_le' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE
                ? now()
                : null,
        ]);

        if ($transaction->type === 'recette') {
            $transaction->update(['nature' => null, 'intrant_production' => null]);
        }

        $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();
        $indicateurs = $this->indicateurs->calculer($transaction->activite_id, null, null, $floor);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction mise à jour.',
            'data' => $transaction->fresh(),
            'indicateurs' => $indicateurs,
        ]);
    }

    public function valider(int $id)
    {
        if (! $this->abonnementService->estPlanCooperatif(auth()->user())) {
            return response()->json([
                'succes' => false,
                'message' => 'Validation manuelle disponible uniquement en plan Coopérative.',
            ], 403);
        }

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $transaction->update([
            'statut_validation' => Transaction::STATUT_VALIDATION_VALIDEE,
            'validee_par_user_id' => (int) auth()->user()->id,
            'validee_le' => now(),
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction validée.',
            'data' => $transaction->fresh(),
        ]);
    }

    public function remettreEnAttente(int $id)
    {
        if (! $this->abonnementService->estPlanCooperatif(auth()->user())) {
            return response()->json([
                'succes' => false,
                'message' => 'Action disponible uniquement en plan Coopérative.',
            ], 403);
        }

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $transaction->update([
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validee_par_user_id' => null,
            'validee_le' => null,
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction remise en attente.',
            'data' => $transaction->fresh(),
        ]);
    }

    public function destroy(int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->with('activite')->findOrFail($id);

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            return response()->json([
                'succes' => false,
                'message' => 'Impossible de supprimer une transaction sur une campagne terminée ou abandonnée.',
            ], 422);
        }

        $activiteId = $transaction->activite_id;
        $this->justificatifService->deleteStoredIfAny($transaction);
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
