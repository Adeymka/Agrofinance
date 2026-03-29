<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\HandlesPdfAbonnement;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Rapport;
use App\Services\AbonnementService;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    use HandlesPdfAbonnement;

    public function __construct(
        private AbonnementService $abonnementService,
        private RapportService $rapportService
    ) {}

    /**
     * GET /api/v1/rapports
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $rapports = Rapport::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with('exploitation:id,nom')
            ->latest()
            ->get();

        return response()->json([
            'succes' => true,
            'data' => $rapports,
        ]);
    }

    /**
     * POST /api/v1/rapports/generer
     */
    public function generer(Request $request)
    {
        $request->validate([
            'activite_id' => 'required|integer|exists:activites,id',
            'type' => 'required|in:campagne,dossier_credit,mensuel,annuel',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date',
        ]);

        $typePermission = match ($request->type) {
            'dossier_credit' => 'dossier_credit',
            default => 'campagne',
        };

        $refus = $this->refuseSiPasGenerationRapport(
            $this->abonnementService,
            $request,
            $typePermission
        );
        if ($refus !== null) {
            return $refus;
        }

        $userId = auth()->user()->id;

        $activite = Activite::pourUtilisateur((int) $userId)
            ->with('exploitation')->findOrFail($request->activite_id);

        $user = auth()->user();
        $periode = $this->rapportService->resoudrePeriode(
            $activite,
            $request->input('periode_debut'),
            $request->input('periode_fin')
        );

        $generation = $this->rapportService->creerEtDispatcher(
            $user,
            $activite,
            (string) $request->type,
            $periode['debut'],
            $periode['fin']
        );
        $rapport = $generation['rapport'];
        $indicateurs = $generation['indicateurs'];
        $token = $generation['token'];

        return response()->json([
            'succes' => true,
            'message' => 'Rapport généré avec succès.',
            'data' => [
                'rapport_id' => $rapport->id,
                'type' => $rapport->type,
                'lien_token' => $token,
                'lien_expire_le' => $rapport->lien_expire_le->format('d/m/Y H:i'),
                'lien_partage' => rtrim(config('app.url'), '/').'/partage/'.$token,
                'indicateurs' => $indicateurs,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/rapports/{id}/telecharger
     */
    public function telecharger(Request $request, int $id)
    {
        $userId = auth()->user()->id;

        $rapport = Rapport::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $refus = $this->refuseSiPasTelechargementRapport(
            $this->abonnementService,
            $request,
            (string) $rapport->type
        );
        if ($refus !== null) {
            return $refus;
        }

        if ($rapport->chemin_pdf === '' || ! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return response()->json([
                'succes' => false,
                'message' => 'Fichier PDF introuvable.',
            ], 404);
        }

        return Storage::disk('local')->download(
            $rapport->chemin_pdf,
            "rapport_{$rapport->id}.pdf"
        );
    }
}
