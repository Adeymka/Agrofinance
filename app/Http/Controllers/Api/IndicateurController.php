<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

class IndicateurController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service,
        private AbonnementService $abonnementService
    ) {}

    /**
     * GET /api/v1/indicateurs/activite/{id}
     * Retourne les indicateurs financiers agricoles + statut pour une activité.
     * Paramètres optionnels : ?debut=2025-01-01&fin=2025-12-31
     */
    public function parActivite(Request $request, int $id)
    {
        $userId = auth()->user()->id;

        $activite = Activite::pourUtilisateur((int) $userId)->findOrFail($id);

        $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();

        $indicateurs = $this->service->calculer(
            $id,
            $request->debut,
            $request->fin,
            $floor
        );

        return response()->json([
            'succes' => true,
            'data' => array_merge([
                'activite_id' => $activite->id,
                'activite_nom' => $activite->nom,
                'type' => $activite->type,
                'statut_campagne' => $activite->statut,
            ], $indicateurs),
        ]);
    }

    /**
     * GET /api/v1/indicateurs/exploitation/{id}
     */
    public function parExploitation(int $id)
    {
        $user = auth()->user();
        $exploitation = Exploitation::where('user_id', $user->id)
            ->findOrFail($id);

        $floor = $this->abonnementService->dateDebutHistorique($user)?->toDateString();
        $resultat = $this->service->calculerExploitation($id, $floor);

        return response()->json([
            'succes' => true,
            'data' => array_merge([
                'exploitation_id' => $exploitation->id,
                'exploitation_nom' => $exploitation->nom,
            ], $resultat),
        ]);
    }

    /**
     * GET /api/v1/indicateurs/activite/{id}/evolution
     */
    public function evolution(int $id)
    {
        $userId = auth()->user()->id;

        $activite = Activite::pourUtilisateur((int) $userId)->findOrFail($id);

        $floor = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();
        $evolution = $this->service->evolutionMensuelle($id, $floor);

        return response()->json([
            'succes' => true,
            'data' => [
                'activite_id' => $activite->id,
                'activite_nom' => $activite->nom,
                'evolution' => $evolution,
            ],
        ]);
    }
}
