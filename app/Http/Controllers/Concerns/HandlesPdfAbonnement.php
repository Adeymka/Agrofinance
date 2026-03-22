<?php

namespace App\Http\Controllers\Concerns;

use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait HandlesPdfAbonnement
{
    /**
     * Génération : campagne → Essentielle+ ; dossier crédit → Pro+.
     */
    protected function refuseSiPasGenerationRapport(
        AbonnementService $abonnementService,
        Request $request,
        string $typeDemande
    ): ?Response {
        $user = auth()->user();
        $typeDemande = $typeDemande === 'dossier_credit' ? 'dossier_credit' : 'campagne';

        if ($abonnementService->peutAccederPdfRapport($user, $typeDemande)) {
            return null;
        }

        $message = $typeDemande === 'dossier_credit'
            ? 'Le rapport dossier crédit nécessite le plan Pro ou Coopérative.'
            : 'La génération de rapports PDF nécessite le plan Essentielle ou supérieur.';

        $isApi = $request->expectsJson()
            || $request->is('api/*')
            || in_array('api', $request->segments(), true);

        if ($isApi) {
            return response()->json(['succes' => false, 'message' => $message], 403);
        }

        return back()->withErrors(['pdf' => $message])->with('alerte', $message);
    }

    /**
     * Téléchargement selon le type du rapport déjà généré.
     */
    protected function refuseSiPasTelechargementRapport(
        AbonnementService $abonnementService,
        Request $request,
        string $typeRapport
    ): ?Response {
        $demande = $typeRapport === 'dossier_credit' ? 'dossier_credit' : 'campagne';

        return $this->refuseSiPasGenerationRapport($abonnementService, $request, $demande);
    }
}
