<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Activite, Exploitation};
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

class IndicateurController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service
    ) {}

    /**
     * GET /api/indicateurs/activite/{id}
     * Retourne les indicateurs FSA + statut pour une activité.
     * Paramètres optionnels : ?debut=2025-01-01&fin=2025-12-31
     */
    public function parActivite(Request $request, int $id)
    {
        $userId = auth()->user()->id;

        $activite = Activite::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $indicateurs = $this->service->calculer(
            $id,
            $request->debut,
            $request->fin
        );

        return response()->json([
            'succes' => true,
            'data'   => array_merge([
                'activite_id'       => $activite->id,
                'activite_nom'      => $activite->nom,
                'type'              => $activite->type,
                'statut_campagne'   => $activite->statut,
            ], $indicateurs),
        ]);
    }

    /**
     * GET /api/indicateurs/exploitation/{id}
     */
    public function parExploitation(int $id)
    {
        $exploitation = Exploitation::where('user_id', auth()->user()->id)
            ->findOrFail($id);

        $resultat = $this->service->calculerExploitation($id);

        return response()->json([
            'succes' => true,
            'data'   => array_merge([
                'exploitation_id'  => $exploitation->id,
                'exploitation_nom'   => $exploitation->nom,
            ], $resultat),
        ]);
    }

    /**
     * GET /api/indicateurs/activite/{id}/evolution
     */
    public function evolution(int $id)
    {
        $userId = auth()->user()->id;

        $activite = Activite::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $evolution = $this->service->evolutionMensuelle($id);

        return response()->json([
            'succes' => true,
            'data'   => [
                'activite_id'  => $activite->id,
                'activite_nom' => $activite->nom,
                'evolution'    => $evolution,
            ],
        ]);
    }
}
