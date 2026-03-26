<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Exploitation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;

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
        $p = $planBrut ?? '';

        return match ($p) {
            'gratuit', 'essentielle', 'pro', 'cooperative' => $p,
            'mensuel' => 'essentielle',
            'annuel' => 'pro',
            'essai' => 'gratuit',
            default => 'aucun',
        };
    }

    /**
     * Montants facturés (FCFA) pour l’initiation paiement FedaPay.
     */
    public function montantFacturation(string $planFacturation): int
    {
        return match ($planFacturation) {
            'mensuel' => 1500,
            'annuel' => 5000,
            'cooperative' => 8000,
            default => 0,
        };
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

        return [
            'plan' => $abonnement?->plan ?? 'aucun',
            'plan_metier' => $this->planActuel($user),
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
        // Facturation mensuelle : mensuel / annuel (Pro) / coopérative = 30 j par période
        if ($plan === 'annuel') {
            $dureeJours = 30;
        } elseif ($plan === 'cooperative') {
            $dureeJours = 30;
        } elseif ($plan === 'mensuel') {
            $dureeJours = 30;
        } elseif (in_array($plan, ['essai', 'gratuit'], true)) {
            $dureeJours = 75;
        } else {
            $dureeJours = match ($planDb) {
                'gratuit' => 75,
                'pro', 'cooperative', 'essentielle' => 30,
                default => 30,
            };
        }

        try {
            return Abonnement::create([
                'user_id' => $user->id,
                'plan' => $planDb,
                'statut' => 'actif',
                'date_debut' => now()->toDateString(),
                'date_fin' => now()->addDays($dureeJours)->toDateString(),
                'montant' => $montant ?? 0,
                'ref_fedapay' => $refFedapay,
            ]);
        } catch (QueryException $e) {
            // Race condition : si deux callbacks arrivent en même temps, la 2e création échoue.
            // On re-lira l'enregistrement existant.
            if ($this->isDuplicateFedapayKeyException($e)) {
                return Abonnement::where('ref_fedapay', $refFedapay)->firstOrFail();
            }

            throw $e;
        }
    }

    private function isDuplicateFedapayKeyException(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains(strtolower($message), 'duplicate')
            || str_contains(strtolower($message), 'uq_abonnements_ref_fedapay');
    }
}
