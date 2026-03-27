<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Exploitation;
use App\Models\User;
use App\Support\FedaPayHttpConfig;
use FedaPay\FedaPay;
use FedaPay\Transaction as FedaTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AbonnementService
{
    /**
     * Abonnement actuellement valide (essai ou payant, date non depassee).
     */
    private function abonnementEnVigueur(User $user): ?Abonnement
    {
        return Abonnement::query()
            ->where('user_id', $user->id)
            ->whereIn('statut', ['actif', 'essai'])
            ->where('date_fin', '>=', now()->startOfDay())
            ->latest()
            ->first();
    }

    /**
     * Mappe la valeur brute (FedaPay ou base) vers un plan metier homogene.
     */
    public function normaliserPlan(?string $planBrut): string
    {
        $p = $planBrut ?? '';

        return match ($p) {
            'gratuit', 'essentielle', 'pro', 'cooperative' => $p,
            'mensuel'  => 'essentielle',
            'annuel'   => 'pro',
            'essai'    => 'gratuit',
            default    => 'aucun',
        };
    }

    /**
     * Montants factures (FCFA) pour l'initiation paiement FedaPay.
     */
    public function montantFacturation(string $planFacturation): int
    {
        return match ($planFacturation) {
            'mensuel'      => 1500,
            'annuel'       => 5000,
            'cooperative'  => 8000,
            default        => 0,
        };
    }

    /**
     * Plan a persister en base apres paiement (FedaPay envoie souvent mensuel / annuel).
     */
    public function planPourBase(string $planFacturation): string
    {
        return match ($planFacturation) {
            'mensuel'      => 'essentielle',
            'annuel'       => 'pro',
            'cooperative'  => 'cooperative',
            'essai'        => 'gratuit',
            default        => $planFacturation,
        };
    }

    public function estActif(User $user): bool
    {
        return $this->abonnementEnVigueur($user) !== null;
    }

    /**
     * Plan metier normalise pour les regles (PDF, quotas). "aucun" si pas d'abonnement valide.
     */
    public function planActuel(User $user): string
    {
        $abonnement = $this->abonnementEnVigueur($user);

        return $this->normaliserPlan($abonnement?->plan);
    }

    public function peutGenererPDF(User $user): bool
    {
        return in_array($this->planActuel($user), ['essentielle', 'pro', 'cooperative'], true);
    }

    /**
     * Rapport dossier credit (PDF) — Pro ou Cooperative minimum.
     */
    public function peutGenererDossierCredit(User $user): bool
    {
        return in_array($this->planActuel($user), ['pro', 'cooperative'], true);
    }

    public function peutAvoirMultiExploitations(User $user): bool
    {
        return in_array($this->planActuel($user), ['pro', 'cooperative'], true);
    }

    /**
     * Acces generation / telechargement selon le type de rapport stocke.
     */
    public function peutAccederPdfRapport(User $user, string $typeRapport): bool
    {
        if ($typeRapport === 'dossier_credit') {
            return $this->peutGenererDossierCredit($user);
        }

        return $this->peutGenererPDF($user);
    }

    public function maxExploitations(User $user): int
    {
        return match ($this->planActuel($user)) {
            'gratuit', 'aucun', 'essentielle' => 1,
            'pro'         => 5,
            'cooperative' => PHP_INT_MAX,
            default       => 1,
        };
    }

    public function dateDebutHistorique(User $user): ?Carbon
    {
        return match ($this->planActuel($user)) {
            'gratuit', 'aucun' => now()->copy()->subMonths(6)->startOfDay(),
            default            => null,
        };
    }

    public function peutCreerExploitation(User $user): bool
    {
        $n = Exploitation::where('user_id', $user->id)->count();

        return $n < $this->maxExploitations($user);
    }

    public function messageLimiteExploitations(User $user): ?string
    {
        if ($this->peutCreerExploitation($user)) {
            return null;
        }

        $plan = $this->planActuel($user);
        $max  = $this->maxExploitations($user);

        return match ($plan) {
            'gratuit', 'aucun', 'essentielle' => 'Votre plan est limite a 1 exploitation. Passez au plan Pro pour en creer plusieurs.',
            default => "Vous avez atteint la limite de {$max} exploitations pour votre plan.",
        };
    }

    public function infos(User $user): array
    {
        $abonnement = $this->abonnementEnVigueur($user);

        $joursRestants = 0;
        if ($abonnement?->date_fin) {
            $fin   = $abonnement->date_fin->copy()->startOfDay();
            $today = now()->startOfDay();
            if ($fin->gte($today)) {
                $joursRestants = (int) $today->diffInDays($fin, false);
            }
        }

        return [
            'plan'             => $abonnement?->plan ?? 'aucun',
            'plan_metier'      => $this->planActuel($user),
            'statut'           => $abonnement?->statut ?? 'expire',
            'date_fin'         => $abonnement?->date_fin,
            'jours_restants'   => $joursRestants,
            'est_essai'        => $abonnement?->statut === 'essai',
            'peut_pdf'         => $this->peutGenererPDF($user),
            'peut_dossier'     => $this->peutGenererDossierCredit($user),
            'peut_multi'       => $this->peutAvoirMultiExploitations($user),
            'max_exploitations' => $this->maxExploitations($user),
        ];
    }

    /**
     * Active un abonnement apres paiement (idempotent sur ref_fedapay).
     * $plan peut etre mensuel / annuel (FedaPay) ou deja essentielle / pro / gratuit / cooperative.
     *
     * #18 — Duree corrigee : utilisation d'un match() clair et coherent.
     *   mensuel / annuel / cooperative = 30 jours (facturation mensuelle en FCFA).
     *   essai / gratuit = 75 jours.
     */
    public function activer(User $user, string $plan, string $refFedapay, ?float $montant = null): Abonnement
    {
        $doublon = Abonnement::where('ref_fedapay', $refFedapay)->first();
        if ($doublon) {
            return $doublon;
        }

        Abonnement::where('user_id', $user->id)
            ->whereIn('statut', ['actif', 'essai'])
            ->update(['statut' => 'expire']);

        $planDb = $this->planPourBase($plan);

        // #18 — match() explicite : toutes les durees metier documentees au meme endroit
        $dureeJours = match (true) {
            in_array($plan, ['mensuel', 'annuel', 'cooperative'], true)  => 30,
            in_array($plan, ['essai', 'gratuit'], true)                  => 75,
            in_array($planDb, ['essentielle', 'pro', 'cooperative'], true) => 30,
            $planDb === 'gratuit'                                         => 75,
            default                                                       => 30,
        };

        try {
            return Abonnement::create([
                'user_id'     => $user->id,
                'plan'        => $planDb,
                'statut'      => 'actif',
                'date_debut'  => now()->toDateString(),
                'date_fin'    => now()->addDays($dureeJours)->toDateString(),
                'montant'     => $montant ?? 0,
                'ref_fedapay' => $refFedapay,
            ]);
        } catch (QueryException $e) {
            // Race condition : si deux callbacks arrivent en meme temps, la 2e creation echoue.
            // On re-lira l'enregistrement existant.
            if ($this->isDuplicateFedapayKeyException($e)) {
                return Abonnement::where('ref_fedapay', $refFedapay)->firstOrFail();
            }

            throw $e;
        }
    }

    // ─── Factorisation FedaPay (#3, #4, #5) ──────────────────────────────────

    /**
     * Initie une transaction FedaPay et stocke le contexte dans le cache (stateless).
     * #5 — Ne plus utiliser Session::put() dans l'API ; le cache Redis suffit.
     *
     * @return array{url_paiement:string, transaction_id:string, montant:int, plan:string}
     * @throws \Throwable En cas d'echec FedaPay apres retries
     */
    public function initierPaiementFedaPay(
        User $user,
        string $plan,
        string $telephone,
        string $callbackUrl,
        bool $webMode = false
    ): array {
        $montant = $this->montantFacturation($plan);

        if (config('services.fedapay.mock')) {
            $ref = 'mock_'.Str::uuid()->toString();

            Cache::put(
                "fedapay_pending_mock:{$user->id}",
                [
                    'user_id'   => $user->id,
                    'plan'      => $plan,
                    'montant'   => $montant,
                    'telephone' => $telephone,
                    'ref'       => $ref,
                ],
                now()->addHours(24)
            );

            return [
                'mock'              => true,
                'transaction_id'    => $ref,
                'montant'           => $montant,
                'plan'              => $plan,
                'url_paiement'      => null,
                'finaliser_mock'    => rtrim(config('app.url'), '/').'/api/abonnement/finaliser-mock',
            ];
        }

        [$transaction, $token] = $this->appelerFedaPayAvecRetry(
            function () use ($user, $plan, $montant, $telephone, $callbackUrl) {
                FedaPay::setApiKey(config('services.fedapay.secret_key'));
                FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));
                FedaPayHttpConfig::appliquer();

                $transaction = FedaTransaction::create([
                    'description'  => "AgroFinance+ — Abonnement {$plan}",
                    'amount'       => $montant,
                    'currency'     => ['iso' => 'XOF'],
                    'callback_url' => $callbackUrl,
                    'customer'     => [
                        'firstname'    => $user->prenom,
                        'lastname'     => $user->nom,
                        'phone_number' => ['number' => $telephone, 'country' => 'bj'],
                    ],
                    'include' => 'customer,currency',
                ]);

                return [$transaction, $transaction->generateToken()];
            },
            'initier',
            3
        );

        $txId = (string) $transaction->id;

        // Contexte stocke dans le cache uniquement (stateless — #5)
        Cache::put(
            "fedapay_pending:{$txId}",
            ['user_id' => $user->id, 'plan' => $plan],
            now()->addHours(48)
        );

        // Fallback session uniquement pour le Web (callback peut atterrir sans cache)
        // On pourrait aussi stocker en BDD (PendingPayment) pour etre 100% stateless.
        if ($webMode) {
            Session::put([
                'fedapay_transaction_id' => $txId,
                'fedapay_plan'           => $plan,
                'fedapay_user_id'        => $user->id,
            ]);
        }

        return [
            'mock'           => false,
            'transaction_id' => $txId,
            'montant'        => $montant,
            'plan'           => $plan,
            'url_paiement'   => $token->url,
        ];
    }

    /**
     * Traite le callback FedaPay (resolution contexte + activation abonnement).
     * #3 — Factorise la logique identique entre Api\AbonnementController et Web\AbonnementController.
     *
     * @param string $transactionId  ID de la transaction FedaPay
     * @return array{statut:'approved'|'autre', user_id:int|null, plan:string, deja_traite:bool}
     * @throws \Throwable En cas d'echec FedaPay apres retries
     */
    public function traiterCallbackFedaPay(string $transactionId): array
    {
        // #14 — Cache::lock() pour prevenir la double activation en cours de race condition
        $lock = Cache::lock("fedapay_callback_lock:{$transactionId}", 30);

        if (! $lock->get()) {
            // Un autre processus traite deja ce callback : on retourne "deja_traite"
            return ['statut' => 'locked', 'user_id' => null, 'plan' => '', 'deja_traite' => true];
        }

        try {
            $transaction = $this->appelerFedaPayAvecRetry(
                function () use ($transactionId) {
                    FedaPay::setApiKey(config('services.fedapay.secret_key'));
                    FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));
                    FedaPayHttpConfig::appliquer();

                    return FedaTransaction::retrieve($transactionId);
                },
                'callback_retrieve',
                3
            );

            // Resolution du contexte : Cache d'abord, Session en fallback
            $pending = Cache::pull("fedapay_pending:{$transactionId}");
            if ($pending) {
                $planChoisi = $pending['plan'];
                $userId     = $pending['user_id'];
            } else {
                $planChoisi = session('fedapay_plan', 'mensuel');
                $userId     = session('fedapay_user_id');
            }

            if (! $userId) {
                Log::error("FedaPay callback : impossible de resoudre user pour TX {$transactionId}");

                return ['statut' => 'erreur_contexte', 'user_id' => null, 'plan' => '', 'deja_traite' => false];
            }

            $dejaTraite = false;

            if (in_array($transaction->status, ['approved', 'transferred'], true)) {
                $dejaTraite = Abonnement::where('ref_fedapay', (string) $transaction->id)->exists();

                $this->activer(
                    User::findOrFail($userId),
                    $planChoisi,
                    (string) $transaction->id,
                    (float) ($transaction->amount ?? 0)
                );

                // Nettoyage session FedaPay (Web)
                session()->forget(['fedapay_transaction_id', 'fedapay_plan', 'fedapay_user_id']);

                Log::info("Abonnement active — user {$userId} — plan {$planChoisi}");
            }

            return [
                'statut'      => $transaction->status,
                'user_id'     => $userId,
                'plan'        => $planChoisi,
                'deja_traite' => $dejaTraite,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * Retry avec backoff exponentiel autour d'un appel FedaPay.
     */
    public function appelerFedaPayAvecRetry(callable $operation, string $operationNom, int $maxTentatives = 3): mixed
    {
        $derniereErreur = null;

        for ($tentative = 0; $tentative < $maxTentatives; $tentative++) {
            try {
                return $operation();
            } catch (\Throwable $e) {
                $derniereErreur = $e;
                Log::warning("FedaPay {$operationNom}: echec externe", [
                    'tentative'     => $tentative + 1,
                    'error_class'   => $e::class,
                    'error_message' => $e->getMessage(),
                ]);

                if ($tentative < $maxTentatives - 1) {
                    $backoffSeconds = (int) pow(2, $tentative); // 1,2,4...
                    usleep($backoffSeconds * 1000000);
                }
            }
        }

        throw $derniereErreur;
    }

    private function isDuplicateFedapayKeyException(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains(strtolower($message), 'duplicate')
            || str_contains(strtolower($message), 'uq_abonnements_ref_fedapay');
    }
}
