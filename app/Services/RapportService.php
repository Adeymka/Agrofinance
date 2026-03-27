<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\Rapport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportService
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs
    ) {}

    /**
     * Crée le rapport, génère le PDF et le stocke sur disque.
     *
     * @return array{rapport: Rapport, indicateurs: array, token: string}
     */
    public function creerEtDispatcher(
        User $user,
        Activite $activite,
        string $type,
        string $periodeDebut,
        string $periodeFin
    ): array {
        $exploitation = $activite->exploitation;

        $indicateurs = $this->indicateurs->calculer(
            $activite->id,
            $periodeDebut,
            $periodeFin
        );

        $transactions = $activite->transactions()
            ->whereBetween('date_transaction', [$periodeDebut, $periodeFin])
            ->orderBy('date_transaction')
            ->get();

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $exploitation->id,
            'type' => $type,
            'periode_debut' => $periodeDebut,
            'periode_fin' => $periodeFin,
            'chemin_pdf' => '',
            'lien_token' => $token,
            'lien_expire_le' => now()->addHours(72),
        ]);

        $template = $type === 'dossier_credit'
            ? 'rapports.pdf.dossier-credit'
            : 'rapports.pdf.campagne';

        $pdf = Pdf::loadView($template, compact(
            'user',
            'exploitation',
            'activite',
            'rapport',
            'indicateurs',
            'transactions'
        ));

        $nomFichier = "rapport_{$rapport->id}_{$token}.pdf";
        $chemin = 'rapports/'.$nomFichier;

        Storage::disk('local')->makeDirectory('rapports');
        Storage::disk('local')->put($chemin, $pdf->output());

        $rapport->update(['chemin_pdf' => $chemin]);

        return [
            'rapport' => $rapport->fresh(),
            'indicateurs' => $indicateurs,
            'token' => $token,
        ];
    }
}
