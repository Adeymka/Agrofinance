<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Exploitation;
use App\Models\User;
use App\Support\TarifsAbonnement;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AbonnementService
{
    /**
     * Abonnement actuellement valide (essai ou payant, date non dépassée).
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
     * Mappe la valeur brute (FedaPay ou base) vers un plan métier homogène.
     */
    public function normaliserPlan(?string $planBrut): string
    {
        $p = Str::ascii(Str::lower(trim((string) ($planBrut ?? ''))));

        return match ($p) {
            'gratuit', 'essentielle', 'pro', 'cooperative' => $p,
            'mensuel' => 'essentielle',
            'annuel' => 'pro',
            'essai' => 'gratuit',
            default => 'aucun',
        };
    }

    /**
     * Plan Coopérative (validation des transactions, filtres) : aligné sur planActuel,
     * avec repli si une ligne d’abonnement valide a bien plan = cooperative en base
     * (ex. variante d’encodage) alors que la normalisation seule renverrait « aucun ».
     */
    public function estPlanCooperatif(User $user): bool
    {
        if ($this->planActuel($user) === 'cooperative') {
            return true;
        }

        return Abonnement::query()
            ->where('user_id', $user->id)
            ->whereIn('statut', ['actif', 'essai'])
            ->where('date_fin', '>=', now()->startOfDay())
            ->where('plan', 'cooperative')
            ->exists();
    }

    /**
     * Montants facturés (FCFA) pour l’initiation paiement FedaPay.
     *
     * @see TarifsAbonnement
     * @see config('tarifs_abonnement.fcfa')
     */
    public function montantFacturation(string $planFacturation): int
    {
        return TarifsAbonnement::montant($planFacturation);
    }

    /**
     * Plan à persister en base après paiement (FedaPay envoie souvent mensuel / annuel).
     */
    public function planPourBase(string $planFacturation): string
    {
        return match ($planFacturation) {
            'mensuel' => 'essentielle',
            'annuel' => 'pro',
            'cooperative' => 'cooperative',
            'essai' => 'gratuit',
            default => $planFacturation,
        };
    }

    public function estActif(User $user): bool
    {
        return $this->abonnementEnVigueur($user) !== null;
    }

    /**
     * Au moins une ligne d’abonnement en base (même expirée) — pour distinguer
     * « jamais souscrit » et « période terminée » dans les messages utilisateur.
     */
    public function aHistoriqueAbonnement(User $user): bool
    {
        return Abonnement::query()->where('user_id', $user->id)->exists();
    }

    /**
     * Plan métier normalisé pour les règles (PDF, quotas). « aucun » si pas d’abonnement valide.
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
     * Rapport dossier crédit (PDF) — Pro ou Coopérative minimum.
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
     * Accès génération / téléchargement selon le type de rapport stocké.
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
            'pro' => 5,
            'cooperative' => PHP_INT_MAX,
            default => 1,
        };
    }

    public function dateDebutHistorique(User $user): ?Carbon
    {
        return match ($this->planActuel($user)) {
            'gratuit', 'aucun' => now()->copy()->subMonths(6)->startOfDay(),
            default => null,
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
        $max = $this->maxExploitations($user);

        return match ($plan) {
            'gratuit', 'aucun' => 'Votre plan est limité à 1 exploitation. Passez au plan Pro pour en créer plusieurs.',
            'essentielle' => 'Votre plan est limité à 1 exploitation. Passez au plan Pro pour en créer plusieurs.',
            default => "Vous avez atteint la limite de {$max} exploitations pour votre plan.",
        };
    }

    public function infos(User $user): array
    {
        $abonnement = $this->abonnementEnVigueur($user);

        $joursRestants = 0;
        if ($abonnement?->date_fin) {
            $fin = $abonnement->date_fin->copy()->startOfDay();
            $today = now()->startOfDay();
            if ($fin->gte($today)) {
                $joursRestants = (int) $today->diffInDays($fin, false);
            }
        }

        $planMetier = $this->planActuel($user);
        if ($this->estPlanCooperatif($user)) {
            $planMetier = 'cooperative';
        }

        return [
            'plan' => $abonnement?->plan ?? 'aucun',
            'plan_metier' => $planMetier,
            'statut' => $abonnement?->statut ?? 'expire',
            'date_fin' => $abonnement?->date_fin,
            'jours_restants' => $joursRestants,
            'est_essai' => $abonnement?->statut === 'essai',
            'peut_pdf' => $this->peutGenererPDF($user),
            'peut_dossier' => $this->peutGenererDossierCredit($user),
            'peut_multi' => $this->peutAvoirMultiExploitations($user),
            'max_exploitations' => $this->maxExploitations($user),
        ];
    }

    /**
     * Active un abonnement après paiement (idempotent sur ref_fedapay).
     * $plan peut être mensuel / annuel (FedaPay) ou déjà essentielle / pro / gratuit / cooperative.
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
        $dureeJours = $this->dureeAbonnementEnJours($plan, $planDb);

        return Abonnement::create([
            'user_id' => $user->id,
            'plan' => $planDb,
            'statut' => 'actif',
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays($dureeJours)->toDateString(),
            'montant' => $montant ?? 0,
            'ref_fedapay' => $refFedapay,
        ]);
    }

    /**
     * Durée de validité (jours) selon le plan de facturation FedaPay / essai et, à défaut, le plan persisté en base.
     */
    private function dureeAbonnementEnJours(string $planFacturation, string $planDb): int
    {
        return match ($planFacturation) {
            'mensuel', 'annuel', 'cooperative' => 30,
            'essai', 'gratuit' => 75,
            default => match ($planDb) {
                'gratuit' => 75,
                'essentielle', 'pro', 'cooperative' => 30,
                default => 30,
            },
        };
    }

    /**
     * Initie un paiement FedaPay (mock, clé absente ou transaction réelle + cache ; session optionnelle pour le Web).
     *
     * @return array{
     *   type: 'mock',
     *   ref: string,
     *   montant: int,
     *   plan: string
     * }|array{type: 'config_manquante', message: string}|array{
     *   type: 'succes',
     *   transaction_id: mixed,
     *   montant: int,
     *   plan: string,
     *   url_paiement: string
     * }|array{type: 'erreur', message: string}
     */
    public function initierPaiementFedaPay(
        User $user,
        string $plan,
        string $telephone,
        string $callbackUrl,
        bool $persisterSession = false
    ): array
    {
        $montant = $this->montantFacturation($plan);

        if (config('services.fedapay.mock')) {
            $ref = 'mock_'.Str::uuid()->toString();

            Cache::put(
                "fedapay_pending_mock:{$user->id}",
                [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'montant' => $montant,
                    'telephone' => $telephone,
                    'ref' => $ref,
                ],
                now()->addHours(24)
            );

            return [
                'type' => 'mock',
                'ref' => $ref,
                'montant' => $montant,
                'plan' => $plan,
            ];
        }

        if (empty(config('services.fedapay.secret_key'))) {
            return [
                'type' => 'config_manquante',
                'message' => 'Paiement non configuré : ajoutez FEDAPAY_SECRET_KEY dans .env, ou FEDAPAY_MOCK=true pour le développement sans API.',
            ];
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transaction = Transaction::create([
                'description' => "AgroFinance+ — Abonnement {$plan}",
                'amount' => $montant,
                'currency' => ['iso' => 'XOF'],
                'callback_url' => $callbackUrl,
                'customer' => [
                    'firstname' => $user->prenom,
                    'lastname' => $user->nom,
                    'phone_number' => [
                        'number' => $telephone,
                        'country' => 'bj',
                    ],
                ],
                'include' => 'customer,currency',
            ]);

            $txId = (string) $transaction->id;

            Cache::put(
                "fedapay_pending:{$txId}",
                [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'montant_fcfa' => $montant,
                ],
                now()->addHours(48)
            );

            if ($persisterSession) {
                Session::put([
                    'fedapay_transaction_id' => $txId,
                    'fedapay_plan' => $plan,
                    'fedapay_user_id' => $user->id,
                ]);
            }

            $token = $transaction->generateToken();

            return [
                'type' => 'succes',
                'transaction_id' => $transaction->id,
                'montant' => $montant,
                'plan' => $plan,
                'url_paiement' => $token->url,
            ];
        } catch (\Throwable $e) {
            Log::error('FedaPay initier : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'type' => 'erreur',
                'message' => "Erreur lors de l'initiation du paiement : ".$e->getMessage(),
            ];
        }
    }

    /**
     * Traite le callback FedaPay (API/Web) et active l'abonnement si paiement confirmé.
     *
     * @return array{
     *   succes: bool,
     *   message: string,
     *   http_code: int,
     *   deja_traite?: bool
     * }
     *
     * @param  bool  $fallbackSession  true = flux Web (navigateur) ; false = API stateless (cache + paramètres uniquement).
     */
    public function traiterCallbackFedaPay(Request $request, bool $fallbackSession = false): array
    {
        if (empty(config('services.fedapay.secret_key'))) {
            return [
                'succes' => false,
                'message' => 'FedaPay non configuré.',
                'http_code' => 503,
            ];
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transactionId = $request->query('id')
                ?? $request->input('id')
                ?? ($fallbackSession ? session('fedapay_transaction_id') : null);

            if (! $transactionId) {
                Log::warning('FedaPay callback sans id transaction.');

                return [
                    'succes' => false,
                    'message' => 'Transaction inconnue.',
                    'http_code' => 422,
                ];
            }

            $txKey = (string) $transactionId;

            return Cache::lock("fedapay:callback:{$txKey}", 120)->block(30, function () use ($request, $fallbackSession, $txKey) {
                $transaction = Transaction::retrieve($txKey);

                $pending = Cache::pull("fedapay_pending:{$txKey}");
                if ($pending) {
                    $planChoisi = $pending['plan'];
                    $userId = $pending['user_id'];
                    $montantAttendu = $pending['montant_fcfa'] ?? null;
                } elseif ($fallbackSession) {
                    $planChoisi = session('fedapay_plan', 'mensuel');
                    $userId = session('fedapay_user_id');
                    $montantAttendu = $planChoisi
                        ? $this->montantFacturation((string) $planChoisi)
                        : null;
                } else {
                    $planChoisi = null;
                    $userId = null;
                    $montantAttendu = null;
                }

                if (! $userId) {
                    if (! $fallbackSession
                        && in_array($transaction->status, ['approved', 'transferred'], true)
                        && Abonnement::where('ref_fedapay', (string) $transaction->id)->exists()) {
                        return [
                            'succes' => true,
                            'message' => 'Déjà traité.',
                            'http_code' => 200,
                            'deja_traite' => true,
                        ];
                    }

                    Log::error("FedaPay callback : impossible de résoudre user pour TX {$txKey}");

                    return [
                        'succes' => false,
                        'message' => 'Contexte de paiement perdu.',
                        'http_code' => 422,
                    ];
                }

                if (! in_array($transaction->status, ['approved', 'transferred'], true)) {
                    return [
                        'succes' => false,
                        'message' => 'Paiement non confirmé.',
                        'http_code' => 422,
                    ];
                }

                $montantTx = (int) round((float) ($transaction->amount ?? 0));
                if ($montantAttendu !== null && $montantTx > 0 && $montantTx !== (int) $montantAttendu) {
                    Log::warning('FedaPay callback : montant transaction différent du montant attendu', [
                        'transaction_id' => $txKey,
                        'montant_transaction' => $montantTx,
                        'montant_attendu' => $montantAttendu,
                    ]);
                }

                $deja = Abonnement::where('ref_fedapay', (string) $transaction->id)->exists();

                $this->activer(
                    User::findOrFail((int) $userId),
                    (string) $planChoisi,
                    (string) $transaction->id,
                    (float) ($transaction->amount ?? 0)
                );

                if ($fallbackSession) {
                    session()->forget([
                        'fedapay_transaction_id',
                        'fedapay_plan',
                        'fedapay_user_id',
                    ]);
                }

                Log::info("Abonnement activé — user {$userId} — plan {$planChoisi}");

                return [
                    'succes' => true,
                    'message' => $deja ? 'Déjà traité.' : 'Abonnement activé.',
                    'http_code' => 200,
                    'deja_traite' => $deja,
                ];
            });
        } catch (LockTimeoutException $e) {
            Log::warning('FedaPay callback : verrou non obtenu à temps', ['tx' => $transactionId ?? null]);

            return [
                'succes' => false,
                'message' => 'Traitement en cours. Réessayez dans un instant.',
                'http_code' => 429,
            ];
        } catch (\Throwable $e) {
            Log::error('FedaPay callback : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'succes' => false,
                'message' => $e->getMessage(),
                'http_code' => 500,
            ];
        }
    }
}
