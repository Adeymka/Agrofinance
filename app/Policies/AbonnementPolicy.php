<?php

namespace App\Policies;

use App\Models\User;
use App\Services\AbonnementService;

/**
 * AbonnementPolicy — Controle d'acces base sur le plan d'abonnement.
 *
 * Enregistree dans AppServiceProvider via Gate::policy().
 * Permet de centraliser les verifications d'abonnement dans une Policy
 * plutot que d'injecter AbonnementService dans chaque controleur (#16).
 *
 * Utilisation :
 *   $this->authorize('genererPdf', $user);      // dans un controleur
 *   Gate::allows('genererPdf', $user);          // depuis n'importe ou
 *   @can('genererPdf', $user)                   // dans une vue Blade
 */
class AbonnementPolicy
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    /**
     * L'utilisateur peut-il generer un rapport PDF campagne/mensuel/annuel ?
     * Plan minimum : Essentielle.
     */
    public function genererPdf(User $user): bool
    {
        return $this->abonnementService->peutGenererPDF($user);
    }

    /**
     * L'utilisateur peut-il generer un dossier credit PDF ?
     * Plan minimum : Pro ou Cooperative.
     */
    public function genererDossierCredit(User $user): bool
    {
        return $this->abonnementService->peutGenererDossierCredit($user);
    }

    /**
     * L'utilisateur peut-il creer plusieurs exploitations ?
     * Plan minimum : Pro (5 max) ou Cooperative (illimite).
     */
    public function multiExploitations(User $user): bool
    {
        return $this->abonnementService->peutAvoirMultiExploitations($user);
    }

    /**
     * L'utilisateur peut-il creer une exploitation supplementaire ?
     * Verifie le quota selon le plan (max exploitations).
     */
    public function creerExploitation(User $user): bool
    {
        return $this->abonnementService->peutCreerExploitation($user);
    }

    /**
     * Acces au PDF d'un rapport selon son type.
     * Delogue vers peutAccederPdfRapport() selon 'campagne' ou 'dossier_credit'.
     *
     * @param  User    $user
     * @param  string  $typeRapport  'campagne' | 'dossier_credit' | 'mensuel' | 'annuel'
     */
    public function accederPdfRapport(User $user, string $typeRapport): bool
    {
        return $this->abonnementService->peutAccederPdfRapport($user, $typeRapport);
    }
}
