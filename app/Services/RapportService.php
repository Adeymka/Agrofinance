<?php

namespace App\Services;

use App\Jobs\GenerateRapportPdfJob;
use App\Models\Activite;
use App\Models\Rapport;
use App\Services\FinancialIndicatorsService;
use Illuminate\Support\Str;

/**
 * RapportService — Factorisation de la creation et du dispatch des rapports PDF.
 *
 * Ce service centralise la logique commune aux Api\RapportController et
 * Web\RapportController pour eviter la duplication : validation metier,
 * creation de l'enregistrement Rapport, dispatch du Job PDF synchrone ou
 * asynchrone selon l'environnement.
 */
class RapportService
{
    public function __construct(
        private FinancialIndicatorsService $fsa
    ) {}

    /**
     * Cree l'enregistrement Rapport en base et dispatche le Job de generation PDF.
     *
     * Utilise par Api\RapportController et Web\RapportController (DRY — #2).
     * En environnement local/testing, le Job est execute de facon synchrone.
     * En production, il est dispatche sur la queue 'rapports' (Supervisor requis).
     *
     * @param  Activite     $activite    Activite cible (relation exploitation doit etre chargee)
     * @param  string       $type        'campagne' | 'dossier_credit' | 'mensuel' | 'annuel'
     * @param  string       $debut       Date de debut periode (Y-m-d)
     * @param  string       $fin         Date de fin periode (Y-m-d)
     * @param  array|null   $indicateurs Indicateurs pre-calcules (si null, calcules ici via FinancialIndicatorsService)
     * @return Rapport                   Enregistrement Rapport cree (chemin_pdf vide jusqu'a la fin du Job)
     */
    public function creerEtDispatcher(
        Activite $activite,
        string $type,
        string $debut,
        string $fin,
        ?array $indicateurs = null
    ): Rapport {
        $indicateurs ??= $this->fsa->calculer($activite->id, $debut, $fin);

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $activite->exploitation_id
                ?? $activite->exploitation->id,
            'type'           => $type,
            'periode_debut'  => $debut,
            'periode_fin'    => $fin,
            'chemin_pdf'     => '',
            'lien_token'     => $token,
            'lien_expire_le' => now()->addHours(72),
        ]);

        $job = new GenerateRapportPdfJob(
            $rapport->id,
            $activite->id,
            $type,
            $debut,
            $fin,
            $indicateurs
        );

        if (app()->environment(['local', 'testing'])) {
            $job->handle($this->fsa);
        } else {
            dispatch($job->onQueue('rapports'));
        }

        return $rapport;
    }
}
