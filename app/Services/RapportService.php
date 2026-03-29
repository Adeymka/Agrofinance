<?php

namespace App\Services;

use App\Models\Activite;
use App\Models\Rapport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RapportService
{
    public function __construct(
        private FinancialIndicatorsService $indicateurs,
        private AbonnementService $abonnement
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

        $prepared = $this->preparerPdfRapport($user, $activite, $rapport);

        $this->stockerPdfLocal($prepared['chemin'], $prepared['binary']);

        $rapport->update(['chemin_pdf' => $prepared['chemin']]);

        return [
            'rapport' => $rapport->fresh(),
            'indicateurs' => $prepared['indicateurs'],
            'token' => $token,
        ];
    }

    /**
     * Génère le binaire PDF et le chemin relatif (sans écriture disque).
     *
     * @return array{chemin: string, binary: string, indicateurs: array}
     */
    public function preparerPdfRapport(User $user, Activite $activite, Rapport $rapport): array
    {
        $exploitation = $activite->exploitation;

        $periodeDebut = $rapport->periode_debut->toDateString();
        $periodeFin = $rapport->periode_fin->toDateString();

        $floor = $this->abonnement->dateDebutHistorique($user)?->toDateString();

        $indicateurs = $this->indicateurs->calculer(
            $activite->id,
            $periodeDebut,
            $periodeFin,
            $floor
        );

        $effDebut = $periodeDebut;
        if ($floor && $effDebut < $floor) {
            $effDebut = $floor;
        }

        $transactions = $activite->transactions()
            ->where('date_transaction', '>=', $effDebut)
            ->where('date_transaction', '<=', $periodeFin)
            ->orderBy('date_transaction')
            ->get();

        $plancherAbonnementPdf = $floor
            ? 'Les données du rapport ne remontent pas avant le '.Carbon::parse($floor)->format('d/m/Y').' (limite liée à votre formule d’abonnement).'
            : null;

        $template = $rapport->type === 'dossier_credit'
            ? 'rapports.pdf.dossier-credit'
            : 'rapports.pdf.campagne';

        $pdf = Pdf::loadView($template, compact(
            'user',
            'exploitation',
            'activite',
            'rapport',
            'indicateurs',
            'transactions',
            'plancherAbonnementPdf'
        ));

        $token = $rapport->lien_token ?? Str::random(40);
        $nomFichier = "rapport_{$rapport->id}_{$token}.pdf";
        $chemin = 'rapports/'.$nomFichier;

        return [
            'chemin' => $chemin,
            'binary' => $pdf->output(),
            'indicateurs' => $indicateurs,
        ];
    }

    /**
     * Harmonise la période entre API/Web avec fallback activité.
     *
     * @return array{debut: string, fin: string}
     *
     * @throws ValidationException
     */
    public function resoudrePeriode(
        Activite $activite,
        ?string $periodeDebut,
        ?string $periodeFin
    ): array {
        $debut = $periodeDebut
            ? Carbon::parse($periodeDebut)->toDateString()
            : ($activite->date_debut?->toDateString() ?? now()->startOfMonth()->toDateString());

        $fin = $periodeFin
            ? Carbon::parse($periodeFin)->toDateString()
            : ($activite->date_fin?->toDateString() ?? now()->toDateString());

        if ($fin < $debut) {
            throw ValidationException::withMessages([
                'periode_fin' => 'La période de fin doit être postérieure ou égale à la période de début.',
            ]);
        }

        return [
            'debut' => $debut,
            'fin' => $fin,
        ];
    }

    /**
     * Écrit le PDF sur le disque local (utilisé aussi par {@see GenerateRapportPdfJob} avec try/catch).
     */
    public function stockerPdfLocal(string $chemin, string $binary): void
    {
        Storage::disk('local')->makeDirectory('rapports');
        Storage::disk('local')->put($chemin, $binary);
    }
}
