<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\HandlesPdfAbonnement;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Rapport;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    use HandlesPdfAbonnement;

    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnementService,
        private RapportService $rapportService
    ) {}

    /**
     * GET /api/rapports
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
            'data'   => $rapports,
        ]);
    }

    /**
     * POST /api/rapports/generer
     * #22 — Validation alignee avec le Web : periode nullable, avec fallback sur les dates de l'activite.
     */
    public function generer(Request $request)
    {
        $request->validate([
            'activite_id'   => 'required|integer|exists:activites,id',
            'type'          => 'required|in:campagne,dossier_credit,mensuel,annuel',
            'periode_debut' => 'nullable|date',
            'periode_fin'   => 'nullable|date|after_or_equal:periode_debut',
        ]);

        $typePermission = match ($request->type) {
            'dossier_credit' => 'dossier_credit',
            default          => 'campagne',
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

        $activite = Activite::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('exploitation')->findOrFail($request->activite_id);

        // #22 — Fallback identique au Web si les dates sont absentes
        $debut = $request->periode_debut
            ?? ($activite->date_debut?->toDateString() ?? now()->startOfMonth()->toDateString());
        $fin = $request->periode_fin
            ?? ($activite->date_fin?->toDateString() ?? now()->toDateString());

        // Calcul pre-requis pour l'API (retourne les indicateurs dans la reponse)
        $indicateurs = $this->indicateurs->calculer($activite->id, $debut, $fin);

        // Delegation au service (DRY) : cree le Rapport + dispatche le Job
        $rapport = $this->rapportService->creerEtDispatcher(
            $activite,
            $request->type,
            $debut,
            $fin,
            $indicateurs
        );

        return response()->json([
            'succes'  => true,
            'message' => 'Rapport en cours de generation.',
            'data'    => [
                'rapport_id'    => $rapport->id,
                'type'          => $rapport->type,
                'lien_token'    => $rapport->lien_token,
                'lien_expire_le' => $rapport->lien_expire_le->format('d/m/Y H:i'),
                'lien_partage'  => rtrim(config('app.url'), '/').'/partage/'.$rapport->lien_token,
                'indicateurs'   => $indicateurs,
            ],
        ], 201);
    }

    /**
     * GET /api/rapports/{id}/telecharger
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

        if ($rapport->chemin_pdf === '') {
            return response()->json([
                'succes'  => false,
                'message' => 'Rapport en cours de generation.',
            ], 425);
        }

        if (! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return response()->json([
                'succes'  => false,
                'message' => 'Fichier PDF introuvable.',
            ], 404);
        }

        $contenuStocke = Storage::disk('local')->get($rapport->chemin_pdf);

        try {
            $pdfBytes = Crypt::decryptString($contenuStocke);
        } catch (\Throwable) {
            // Compatibilite avec d'anciens rapports encore non chiffres.
            $pdfBytes = $contenuStocke;
        }

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapport_'.$rapport->id.'.pdf"',
        ]);
    }
}
