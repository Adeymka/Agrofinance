<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Activite, Rapport};
use App\Services\FinancialIndicatorsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs
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
     */
    public function generer(Request $request)
    {
        $request->validate([
            'activite_id'   => 'required|integer|exists:activites,id',
            'type'          => 'required|in:campagne,dossier_credit,mensuel,annuel',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
        ]);

        $userId = auth()->user()->id;

        $activite = Activite::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('exploitation')->findOrFail($request->activite_id);

        $exploitation = $activite->exploitation;
        $user         = auth()->user();

        $indicateurs = $this->indicateurs->calculer(
            $activite->id,
            $request->periode_debut,
            $request->periode_fin
        );

        $transactions = $activite->transactions()
            ->whereBetween('date_transaction', [
                $request->periode_debut,
                $request->periode_fin,
            ])
            ->orderBy('date_transaction')
            ->get();

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $exploitation->id,
            'type'            => $request->type,
            'periode_debut'   => $request->periode_debut,
            'periode_fin'     => $request->periode_fin,
            'chemin_pdf'      => '',
            'lien_token'      => $token,
            'lien_expire_le'  => now()->addHours(72),
        ]);

        $template = $request->type === 'dossier_credit'
            ? 'rapports.pdf.dossier-credit'
            : 'rapports.pdf.campagne';

        $pdf = Pdf::loadView($template, compact(
            'user', 'exploitation', 'activite',
            'rapport', 'indicateurs', 'transactions'
        ));

        $nomFichier = "rapport_{$rapport->id}_{$token}.pdf";
        $chemin     = 'rapports/'.$nomFichier;

        Storage::disk('local')->makeDirectory('rapports');
        Storage::disk('local')->put($chemin, $pdf->output());

        $rapport->update(['chemin_pdf' => $chemin]);

        return response()->json([
            'succes'  => true,
            'message' => 'Rapport généré avec succès.',
            'data'    => [
                'rapport_id'     => $rapport->id,
                'type'           => $rapport->type,
                'lien_token'     => $token,
                'lien_expire_le' => $rapport->lien_expire_le->format('d/m/Y H:i'),
                'lien_partage'   => rtrim(config('app.url'), '/').'/partage/'.$token,
                'indicateurs'    => $indicateurs,
            ],
        ], 201);
    }

    /**
     * GET /api/rapports/{id}/telecharger
     */
    public function telecharger(int $id)
    {
        $userId = auth()->user()->id;

        $rapport = Rapport::whereHas('exploitation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        if ($rapport->chemin_pdf === '' || ! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return response()->json([
                'succes'  => false,
                'message' => 'Fichier PDF introuvable.',
            ], 404);
        }

        return Storage::disk('local')->download(
            $rapport->chemin_pdf,
            "rapport_{$rapport->id}.pdf"
        );
    }
}
