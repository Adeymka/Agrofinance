<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\HandlesPdfAbonnement;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Rapport;
use App\Jobs\GenerateRapportPdfJob;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    use HandlesPdfAbonnement;

    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnementService
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
            'data' => $rapports,
        ]);
    }

    /**
     * POST /api/rapports/generer
     */
    public function generer(Request $request)
    {
        $request->validate([
            'activite_id' => 'required|integer|exists:activites,id',
            'type' => 'required|in:campagne,dossier_credit,mensuel,annuel',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
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

        $activite = Activite::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('exploitation')->findOrFail($request->activite_id);

        $exploitation = $activite->exploitation;

        $indicateurs = $this->indicateurs->calculer(
            $activite->id,
            $request->periode_debut,
            $request->periode_fin
        );

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $exploitation->id,
            'type' => $request->type,
            'periode_debut' => $request->periode_debut,
            'periode_fin' => $request->periode_fin,
            'chemin_pdf' => '',
            'lien_token' => $token,
            'lien_expire_le' => now()->addHours(72),
        ]);

        $job = new GenerateRapportPdfJob(
            $rapport->id,
            $activite->id,
            $request->type,
            $request->periode_debut,
            $request->periode_fin,
            $indicateurs
        );

        if (app()->environment(['local', 'testing'])) {
            $job->handle($this->indicateurs);
        } else {
            dispatch($job->onQueue('rapports'));
        }

        return response()->json([
            'succes' => true,
            'message' => 'Rapport en cours de génération.',
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
                'succes' => false,
                'message' => 'Rapport en cours de génération.',
            ], 425);
        }

        if (! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return response()->json([
                'succes' => false,
                'message' => 'Fichier PDF introuvable.',
            ], 404);
        }
        $contenuStocke = Storage::disk('local')->get($rapport->chemin_pdf);

        try {
            $pdfBytes = Crypt::decryptString($contenuStocke);
        } catch (\Throwable) {
            // Compatibilité avec d'anciens rapports encore non chiffrés.
            $pdfBytes = $contenuStocke;
        }

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapport_'.$rapport->id.'.pdf"',
        ]);
    }
}
