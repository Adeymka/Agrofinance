<?php

namespace App\Jobs;

use App\Models\Activite;
use App\Models\Rapport;
use App\Services\FinancialIndicatorsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class GenerateRapportPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $rapportId,
        public int $activiteId,
        public string $type,
        public string $periodeDebut,
        public string $periodeFin,
        public ?array $indicateurs = null
    ) {}

    public function handle(FinancialIndicatorsService $indicateursService): void
    {
        $rapport = Rapport::find($this->rapportId);
        if (! $rapport) {
            return;
        }

        if ($rapport->chemin_pdf !== '' && Storage::disk('local')->exists($rapport->chemin_pdf)) {
            // Déjà généré (replay/dup dispatch).
            return;
        }

        $activite = Activite::whereKey($this->activiteId)
            ->with('exploitation.user')
            ->firstOrFail();

        $exploitation = $activite->exploitation;
        $user = $exploitation?->user;

        $indicateurs = $this->indicateurs
            ?? $indicateursService->calculer($this->activiteId, $this->periodeDebut, $this->periodeFin);

        $transactions = $activite->transactions()
            ->whereBetween('date_transaction', [$this->periodeDebut, $this->periodeFin])
            ->orderBy('date_transaction')
            ->get();

        $template = $this->type === 'dossier_credit'
            ? 'rapports.pdf.dossier-credit'
            : 'rapports.pdf.campagne';

        $pdf = Pdf::loadView($template, compact(
            'user', 'exploitation', 'activite',
            'rapport', 'indicateurs', 'transactions'
        ));

        $nomFichier = "rapport_{$rapport->id}_{$rapport->lien_token}.pdf";
        $chemin = 'rapports/'.$nomFichier;

        Storage::disk('local')->makeDirectory('rapports');

        $contenuBrut   = $pdf->output();
        $contenuChiffre = Crypt::encryptString($contenuBrut);

        // #20 — try/catch : evite un rapport orphelin en BDD si l'ecriture disque echoue.
        try {
            Storage::disk('local')->put($chemin, $contenuChiffre);
        } catch (\Throwable $e) {
            // Supprime l'enregistrement pour ne pas laisser un Rapport avec chemin_pdf vide
            $rapport->delete();
            throw $e; // Re-throw pour que le Job soit requeue (tries = 3)
        }

        $rapport->update(['chemin_pdf' => $chemin]);
    }
}

