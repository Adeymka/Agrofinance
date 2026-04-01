<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TransactionCategories;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\CooperativeMember;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\CooperativeService;
use App\Services\FinancialIndicatorsService;
use App\Services\TransactionJustificatifService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnementService,
        private CooperativeService $cooperativeService,
        private TransactionJustificatifService $justificatifService
    ) {}

    public function index(Request $request)
    {
        $ownerId = $this->ownerUserId();
        $query = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        });

        $floor = $this->abonnementService->dateDebutHistorique($this->cooperativeService->resolveOwner(auth()->user()));
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
        if (! $this->canWriteTransactions()) {
            return response()->json([
                'succes' => false,
                'message' => 'Votre rôle est en lecture seule.',
            ], 403);
        }

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
            $activite = Activite::pourUtilisateur($this->ownerUserId())
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
        $userId = $this->ownerUserId();
        $actor = auth()->user();
        $estCoop = $this->isCooperativeContext();
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
                'validation_niveau' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? 1 : 0,
                'validee_niveau1_par_user_id' => null,
                'validee_niveau1_le' => null,
                'validee_par_user_id' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? (int) $userId : null,
                'validee_le' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? now() : null,
            ]);
        }

        // Recalculer les indicateurs financiers agricoles pour chaque activité touchée
        $floor = $this->abonnementService->dateDebutHistorique($this->cooperativeService->resolveOwner($actor))?->toDateString();

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
        $ownerId = $this->ownerUserId();
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        })->findOrFail($id);

        return response()->json([
            'succes' => true,
            'data' => $transaction,
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (! $this->canWriteTransactions()) {
            return response()->json([
                'succes' => false,
                'message' => 'Votre rôle est en lecture seule.',
            ], 403);
        }

        $ownerId = $this->ownerUserId();
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
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
        $estCoop = $this->isCooperativeContext();
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
            'validation_niveau' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE ? 1 : 0,
            'validee_niveau1_par_user_id' => null,
            'validee_niveau1_le' => null,
            'validee_par_user_id' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE
                ? $this->ownerUserId()
                : null,
            'validee_le' => $statutValidationParDefaut === Transaction::STATUT_VALIDATION_VALIDEE
                ? now()
                : null,
        ]);

        if ($transaction->type === 'recette') {
            $transaction->update(['nature' => null, 'intrant_production' => null]);
        }

        $floor = $this->abonnementService->dateDebutHistorique($this->cooperativeService->resolveOwner(auth()->user()))?->toDateString();
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
        if (! $this->isCooperativeContext()) {
            return response()->json([
                'succes' => false,
                'message' => 'Validation manuelle disponible uniquement en plan Coopérative.',
            ], 403);
        }
        if (! $this->canValidateTransactions()) {
            return response()->json([
                'succes' => false,
                'message' => 'Vous n’avez pas le rôle requis pour valider.',
            ], 403);
        }

        $ownerId = $this->ownerUserId();
        $actor = auth()->user();
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        })->findOrFail($id);

        $requiresDouble = $this->cooperativeService->requiresDoubleValidation($transaction, $actor);
        $coop = $this->cooperativeService->cooperativeFor($actor);

        if (! $requiresDouble) {
            $transaction->update([
                'validation_niveau' => 1,
                'statut_validation' => Transaction::STATUT_VALIDATION_VALIDEE,
                'validee_par_user_id' => $actor->id,
                'validee_le' => now(),
            ]);
            if ($coop) {
                $this->cooperativeService->log($coop, $actor, 'transaction.validated', ['niveau' => 1], null, $transaction->id);
            }

            return response()->json([
                'succes' => true,
                'message' => 'Transaction validée.',
                'data' => $transaction->fresh(),
            ]);
        }

        if ((int) $transaction->validation_niveau === 0) {
            $transaction->update([
                'validation_niveau' => 1,
                'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
                'validee_niveau1_par_user_id' => $actor->id,
                'validee_niveau1_le' => now(),
                'validee_par_user_id' => null,
                'validee_le' => null,
            ]);
            if ($coop) {
                $this->cooperativeService->log($coop, $actor, 'transaction.validated.level1', ['double_validation' => true], null, $transaction->id);
            }

            return response()->json([
                'succes' => true,
                'message' => 'Niveau 1 validé. Validation finale requise.',
                'data' => $transaction->fresh(),
            ]);
        }

        if ((int) $transaction->validation_niveau === 1 && (int) $transaction->validee_niveau1_par_user_id === (int) $actor->id) {
            return response()->json([
                'succes' => false,
                'message' => 'Un autre validateur doit effectuer le niveau 2.',
            ], 422);
        }

        $transaction->update([
            'validation_niveau' => 2,
            'statut_validation' => Transaction::STATUT_VALIDATION_VALIDEE,
            'validee_par_user_id' => $actor->id,
            'validee_le' => now(),
        ]);
        if ($coop) {
            $this->cooperativeService->log($coop, $actor, 'transaction.validated.level2', ['double_validation' => true], null, $transaction->id);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Transaction validée.',
            'data' => $transaction->fresh(),
        ]);
    }

    public function remettreEnAttente(int $id)
    {
        if (! $this->isCooperativeContext()) {
            return response()->json([
                'succes' => false,
                'message' => 'Action disponible uniquement en plan Coopérative.',
            ], 403);
        }
        if (! $this->canValidateTransactions()) {
            return response()->json([
                'succes' => false,
                'message' => 'Vous n’avez pas le rôle requis pour modifier la validation.',
            ], 403);
        }

        $ownerId = $this->ownerUserId();
        $actor = auth()->user();
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        })->findOrFail($id);

        $transaction->update([
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validation_niveau' => 0,
            'validee_niveau1_par_user_id' => null,
            'validee_niveau1_le' => null,
            'validee_par_user_id' => null,
            'validee_le' => null,
        ]);
        $coop = $this->cooperativeService->cooperativeFor($actor);
        if ($coop) {
            $this->cooperativeService->log($coop, $actor, 'transaction.reset_to_pending', [], null, $transaction->id);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Transaction remise en attente.',
            'data' => $transaction->fresh(),
        ]);
    }

    public function destroy(int $id)
    {
        if (! $this->canWriteTransactions()) {
            return response()->json([
                'succes' => false,
                'message' => 'Votre rôle est en lecture seule.',
            ], 403);
        }

        $ownerId = $this->ownerUserId();
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
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

        $floor = $this->abonnementService->dateDebutHistorique($this->cooperativeService->resolveOwner(auth()->user()))?->toDateString();
        $indicateurs = $this->indicateurs->calculer($activiteId, null, null, $floor);

        return response()->json([
            'succes' => true,
            'message' => 'Transaction supprimée.',
            'indicateurs' => $indicateurs,
        ]);
    }

    private function ownerUserId(): int
    {
        return (int) $this->cooperativeService->resolveOwner(auth()->user())->id;
    }

    private function isCooperativeContext(): bool
    {
        $owner = $this->cooperativeService->resolveOwner(auth()->user());

        return $this->abonnementService->estPlanCooperatif($owner);
    }

    private function canValidateTransactions(): bool
    {
        $actor = auth()->user();
        if ($this->cooperativeService->resolveOwner($actor)->id === $actor->id) {
            return true;
        }

        return $this->cooperativeService->canValidateTransactions($actor);
    }

    private function canWriteTransactions(): bool
    {
        $actor = auth()->user();
        if ($this->cooperativeService->resolveOwner($actor)->id === $actor->id) {
            return true;
        }

        $role = $this->cooperativeService->roleFor($actor);

        return in_array($role, [CooperativeMember::ROLE_ADMIN, CooperativeMember::ROLE_VALIDATEUR, CooperativeMember::ROLE_SAISIE], true);
    }
}
