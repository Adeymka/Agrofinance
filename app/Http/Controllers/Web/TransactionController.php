<?php

namespace App\Http\Controllers\Web;

use App\Helpers\TransactionCategories;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\CooperativeMember;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\CooperativeService;
use App\Services\ExploitationCategorieSuggestionService;
use App\Services\ExploitationCategorieDynamiqueService;
use App\Services\FinancialIndicatorsService;
use App\Services\TransactionJustificatifService;
use App\Services\TransactionSlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $fsa,
        private AbonnementService $abonnementService,
        private CooperativeService $cooperativeService,
        private ExploitationCategorieSuggestionService $categorieSuggestionService,
        private ExploitationCategorieDynamiqueService $categorieDynamiqueService,
        private TransactionJustificatifService $justificatifService
    ) {}

    public function index()
    {
        $uid = $this->ownerUserId();
        $statutValidation = (string) request()->query('statut_validation', 'all');

        $transactions = Transaction::query()
            ->whereHas('activite.exploitation', fn ($q) => $q->where('user_id', $uid))
            ->when(
                in_array($statutValidation, [Transaction::STATUT_VALIDATION_EN_ATTENTE, Transaction::STATUT_VALIDATION_VALIDEE], true),
                fn ($q) => $q->where('statut_validation', $statutValidation)
            )
            ->with(['activite:id,nom'])
            ->orderByDesc('date_transaction')
            ->orderByDesc('id')
            ->paginate(20);
        $transactions->appends(['statut_validation' => $statutValidation]);

        $isCooperative = $this->isCooperativeContext();

        $canValidateTransactions = $this->canValidateTransactions();
        $canWriteTransactions = $this->canWriteTransactions();

        return view('transactions.index', compact('transactions', 'statutValidation', 'isCooperative', 'canValidateTransactions', 'canWriteTransactions') + ['nav' => 'saisie']);
    }

    public function create(Request $request)
    {
        if (! $this->canWriteTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Votre rôle est en lecture seule.');
        }

        $uid = $this->ownerUserId();

        $activites = Activite::pourUtilisateur($uid)
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom,type')
            ->get();

        $activiteSelectionnee = $request->query('activite_id');
        if ($activiteSelectionnee && ! $activites->contains(fn ($a) => (int) $a->id === (int) $activiteSelectionnee)) {
            $activiteSelectionnee = null;
        }

        $activitePourType = $activites->firstWhere('id', (int) ($activiteSelectionnee ?? $activites->first()?->id));
        
        // Génération dynamique des catégories basées sur les activités réelles
        $exploitation = $activitePourType?->exploitation ?? Exploitation::where('user_id', $uid)->first();
        $categories = $exploitation 
            ? ExploitationCategorieDynamiqueService::genererParExploitation($exploitation)
            : TransactionCategories::getByType('cultures_vivrieres');
        
        // typeExploitation conservé pour la rétrocompatibilité des templates
        $typeExploitation = 'mixte';

        // ✅ Filtrer les suggestions UNIQUEMENT pour l'activité courante, pas toute l'exploitation
        $activiteIdPourSuggestions = (int) ($activiteSelectionnee ?? $activites->first()?->id);
        $suggestionsForCurrentActivity = $activiteIdPourSuggestions > 0
            ? $this->categorieSuggestionService->groupedForActivityId($activiteIdPourSuggestions)
            : ['depense' => [], 'recette' => []];

        // Mapper suggestions par activite_id (non par exploitation_id) pour isoler les campagnes
        $suggestionsByExploitation = $activites->mapWithKeys(fn ($a) => [
            $a->id => ($a->id === $activiteIdPourSuggestions ? $suggestionsForCurrentActivity : ['depense' => [], 'recette' => []])
        ]);
        $activiteVersExploitation = $activites->mapWithKeys(fn ($a) => [$a->id => $a->id]);  // Mappe activite_id -> activite_id au lieu de exploitation_id

        $exploitationIdPourCampagne = $activitePourType?->exploitation_id
            ?? Exploitation::where('user_id', $uid)->orderBy('id')->value('id');

        return view('transactions.create', compact(
            'activites',
            'activiteSelectionnee',
            'categories',
            'typeExploitation',
            'suggestionsByExploitation',
            'activiteVersExploitation',
            'exploitationIdPourCampagne'
        ) + [
            'nav' => 'saisie',
            'slugsCi' => TransactionCategories::slugsChargesIntermediaires(),
            'txCatMeta' => TransactionCategories::metaJsonPourCombobox($typeExploitation),
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->canWriteTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Votre rôle est en lecture seule.');
        }

        $uid = $this->ownerUserId();

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

        $librePreview = trim((string) $request->input('categorie_libre', ''));
        if ($request->type === 'depense' && $librePreview !== '') {
            $rules['nature'] = 'required|in:fixe,variable';
        }

        $request->validate(array_merge($rules, TransactionJustificatifService::validationRules()));

        $activite = Activite::pourUtilisateur($uid)
            ->with('exploitation:id,type')->findOrFail($request->activite_id);

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
            $allSlugs = TransactionSlugService::allowedSlugsForExploitation($activite->exploitation, $request->type);
            if (! in_array($categorie, $allSlugs, true)) {
                throw ValidationException::withMessages([
                    'categorie' => ['Catégorie invalide pour votre exploitation.'],
                ]);
            }
        }

        if ($request->type === 'depense' && $libre !== '' && ! TransactionCategories::estSlugChargesIntermediaires($categorie)) {
            $request->validate([
                'intrant_production' => 'required|in:0,1',
            ]);
        }

        $intrantProduction = null;
        $natureDepense = null;
        if ($request->type === 'depense') {
            if ($libre === '') {
                $natureDepense = TransactionCategories::natureDepensePourSlug($categorie);
                $intrantProduction = TransactionCategories::intrantProductionPourSlugDepense($categorie);
            } else {
                $natureDepense = $request->input('nature');
                $intrantProduction = TransactionCategories::estSlugChargesIntermediaires($categorie)
                    ? null
                    : $request->boolean('intrant_production');
            }
        }

        $estCoop = $this->isCooperativeContext();
        $validationNiveauParDefaut = 0;

        $transaction = Transaction::create([
            'activite_id' => $request->activite_id,
            'type' => $request->type,
            'nature' => $request->type === 'recette' ? null : $natureDepense,
            'categorie' => $categorie,
            'intrant_production' => $intrantProduction,
            'montant' => $request->montant,
            'date_transaction' => $request->date_transaction,
            'note' => $request->note,
            'est_imprevue' => $request->boolean('est_imprevue'),
            'synced' => true,
            'statut_validation' => $estCoop
                ? Transaction::STATUT_VALIDATION_EN_ATTENTE
                : Transaction::STATUT_VALIDATION_VALIDEE,
            'validation_niveau' => $estCoop ? $validationNiveauParDefaut : 1,
            'validee_niveau1_par_user_id' => null,
            'validee_niveau1_le' => null,
            'validee_par_user_id' => $estCoop
                ? null
                : (int) auth()->user()->id,
            'validee_le' => $estCoop
                ? null
                : now(),
        ]);

        if ($request->hasFile('justificatif')) {
            $path = $this->justificatifService->storeUploadedFile($transaction, $request->file('justificatif'));
            $transaction->update(['photo_justificatif' => $path]);
        }

        $allowedFsa = TransactionSlugService::allowedSlugsForExploitation($activite->exploitation, $request->type);
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
        if (! $this->canWriteTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Votre rôle est en lecture seule.');
        }

        $uid = $this->ownerUserId();

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })
            ->with('activite.exploitation:id,type,nom')
            ->findOrFail($id);

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            return redirect()->route('activites.show', $transaction->activite_id)
                ->with('error', 'Les transactions d’une campagne terminée ou abandonnée ne sont plus modifiables.');
        }

        // Génération dynamique des catégories
        $exploitation = $transaction->activite->exploitation;
        $categories = $exploitation
            ? ExploitationCategorieDynamiqueService::genererParExploitation($exploitation)
            : TransactionCategories::getByType('cultures_vivrieres');
        $typeExploitation = 'mixte';

        $allowedNow = TransactionSlugService::allowedSlugsForExploitation(
            $transaction->activite->exploitation,
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

        $activites = Activite::pourUtilisateur($uid)
            ->where('statut', Activite::STATUT_EN_COURS)
            ->with('exploitation:id,nom')
            ->get();

        // ✅ Filtrer les suggestions UNIQUEMENT pour l'activité courante (celle en édition)
        $suggestionsForCurrentActivity = $this->categorieSuggestionService->groupedForActivityId($transaction->activite_id);
        $suggestionsByExploitation = $activites->mapWithKeys(fn ($a) => [
            $a->id => ($a->id === $transaction->activite_id ? $suggestionsForCurrentActivity : ['depense' => [], 'recette' => []])
        ]);
        $activiteVersExploitation = $activites->mapWithKeys(fn ($a) => [$a->id => $a->id]);  // Mappe activite_id -> activite_id au lieu de exploitation_id

        $exploitationIdPourCampagne = (int) $transaction->activite->exploitation_id;

        return view('transactions.edit', compact(
            'transaction',
            'categories',
            'activites',
            'typeExploitation',
            'categorieSelectionnee',
            'categorieLibre',
            'suggestionsByExploitation',
            'activiteVersExploitation',
            'exploitationIdPourCampagne'
        ) + [
            'nav' => 'saisie',
            'slugsCi' => TransactionCategories::slugsChargesIntermediaires(),
            'txCatMeta' => TransactionCategories::metaJsonPourCombobox($typeExploitation),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (! $this->canWriteTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Votre rôle est en lecture seule.');
        }

        $uid = $this->ownerUserId();

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
            'supprimer_justificatif' => 'boolean',
        ];

        $librePreviewUpd = trim((string) $request->input('categorie_libre', ''));
        if ($request->type === 'depense' && $librePreviewUpd !== '') {
            $rules['nature'] = 'required|in:fixe,variable';
        }

        $request->validate(array_merge($rules, TransactionJustificatifService::validationRules()));

        $activite = Activite::pourUtilisateur($uid)
            ->with('exploitation:id,type')->findOrFail($request->activite_id);

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

        $libre = trim((string) $request->input('categorie_libre', ''));
        $categorie = $libre !== '' ? $libre : trim((string) $request->input('categorie', ''));

        if ($categorie === '') {
            throw ValidationException::withMessages([
                'categorie' => ['Choisissez une catégorie ou écrivez la vôtre.'],
            ]);
        }

        if ($libre === '') {
            $allowed = TransactionSlugService::allowedSlugsForExploitation($activite->exploitation, $request->type);
            if (! in_array($categorie, $allowed, true)) {
                throw ValidationException::withMessages([
                    'categorie' => ['Catégorie invalide pour votre exploitation.'],
                ]);
            }
        }

        if ($request->type === 'depense' && $libre !== '' && ! TransactionCategories::estSlugChargesIntermediaires($categorie)) {
            $request->validate([
                'intrant_production' => 'required|in:0,1',
            ]);
        }

        $intrantProduction = null;
        $natureDepense = null;
        if ($request->type === 'depense') {
            if ($libre === '') {
                $natureDepense = TransactionCategories::natureDepensePourSlug($categorie);
                $intrantProduction = TransactionCategories::intrantProductionPourSlugDepense($categorie);
            } else {
                $natureDepense = $request->input('nature');
                $intrantProduction = TransactionCategories::estSlugChargesIntermediaires($categorie)
                    ? null
                    : $request->boolean('intrant_production');
            }
        }

        $estCoop = $this->isCooperativeContext();
        $validationNiveauParDefaut = 0;

        $transaction->update([
            'activite_id' => $request->activite_id,
            'type' => $request->type,
            'nature' => $request->type === 'recette' ? null : $natureDepense,
            'categorie' => $categorie,
            'intrant_production' => $intrantProduction,
            'montant' => $request->montant,
            'date_transaction' => $request->date_transaction,
            'note' => $request->note,
            'est_imprevue' => $request->boolean('est_imprevue'),
            'statut_validation' => $estCoop
                ? Transaction::STATUT_VALIDATION_EN_ATTENTE
                : Transaction::STATUT_VALIDATION_VALIDEE,
            'validation_niveau' => $estCoop ? $validationNiveauParDefaut : 1,
            'validee_niveau1_par_user_id' => null,
            'validee_niveau1_le' => null,
            'validee_par_user_id' => $estCoop
                ? null
                : (int) auth()->user()->id,
            'validee_le' => $estCoop
                ? null
                : now(),
        ]);

        if ($request->boolean('supprimer_justificatif')) {
            $this->justificatifService->deleteStoredIfAny($transaction);
            $transaction->update(['photo_justificatif' => null]);
        } elseif ($request->hasFile('justificatif')) {
            $path = $this->justificatifService->storeUploadedFile($transaction, $request->file('justificatif'));
            $transaction->update(['photo_justificatif' => $path]);
        }

        $allowedFsa = TransactionSlugService::allowedSlugsForExploitation($activite->exploitation, $request->type);
        $this->categorieSuggestionService->recordIfCustom(
            (int) $activite->exploitation_id,
            $request->type,
            $categorie,
            $allowedFsa
        );

        return redirect()->route('activites.show', $transaction->activite_id)
            ->with('success', 'Transaction modifiée.');
    }

    /**
     * Téléchargement du justificatif (session web authentifiée).
     */
    public function telechargerJustificatif(int $id)
    {
        $uid = $this->ownerUserId();

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->findOrFail($id);

        if (empty($transaction->photo_justificatif)) {
            abort(404);
        }

        $path = $transaction->photo_justificatif;
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, 'justificatif_'.$transaction->id);
    }

    public function destroy(int $id)
    {
        if (! $this->canWriteTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Votre rôle est en lecture seule.');
        }

        $uid = $this->ownerUserId();

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('activite')->findOrFail($id);

        if ($transaction->activite->statut !== Activite::STATUT_EN_COURS) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une transaction sur une campagne terminée ou abandonnée.');
        }

        $activiteId = $transaction->activite_id;
        $this->justificatifService->deleteStoredIfAny($transaction);
        $transaction->delete();

        return redirect()->route('activites.show', $activiteId)
            ->with('success', 'Transaction supprimée.');
    }

    public function valider(int $id)
    {
        $uid = $this->ownerUserId();
        if (! $this->isCooperativeContext()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Validation manuelle disponible uniquement en plan Coopérative.');
        }
        if (! $this->canValidateTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Vous n’avez pas le rôle requis pour valider.');
        }

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('activite')->findOrFail($id);

        $actor = auth()->user();
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

            return redirect()->route('transactions.index', ['statut_validation' => request()->input('statut_validation', 'all')])
                ->with('success', 'Transaction validée.');
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

            return redirect()->route('transactions.index', ['statut_validation' => request()->input('statut_validation', 'all')])
                ->with('success', 'Niveau 1 validé. Validation finale requise.');
        }

        if ((int) $transaction->validation_niveau === 1 && (int) $transaction->validee_niveau1_par_user_id === (int) $actor->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Un autre validateur doit effectuer le niveau 2.');
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

        return redirect()->route('transactions.index', ['statut_validation' => request()->input('statut_validation', 'all')])
            ->with('success', 'Transaction validée.');
    }

    public function remettreEnAttente(int $id)
    {
        $uid = $this->ownerUserId();
        if (! $this->isCooperativeContext()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Action disponible uniquement en plan Coopérative.');
        }
        if (! $this->canValidateTransactions()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Vous n’avez pas le rôle requis pour modifier la validation.');
        }

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('activite')->findOrFail($id);

        $transaction->update([
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validation_niveau' => 0,
            'validee_niveau1_par_user_id' => null,
            'validee_niveau1_le' => null,
            'validee_par_user_id' => null,
            'validee_le' => null,
        ]);

        $actor = auth()->user();
        $coop = $this->cooperativeService->cooperativeFor($actor);
        if ($coop) {
            $this->cooperativeService->log($coop, $actor, 'transaction.reset_to_pending', [], null, $transaction->id);
        }

        return redirect()->route('transactions.index', ['statut_validation' => request()->input('statut_validation', 'all')])
            ->with('success', 'Transaction remise en attente.');
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
