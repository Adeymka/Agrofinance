<?php

namespace App\Jobs;

use App\Models\Activite;
use App\Models\Rapport;
use App\Models\User;
use App\Services\RapportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateRapportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $rapportId,
        public int $activiteId,
        public int $userId
    ) {}

    public function handle(RapportService $rapportService): void
    {
        $rapport = Rapport::query()->findOrFail($this->rapportId);
        $activite = Activite::pourUtilisateur($this->userId)->findOrFail($this->activiteId);
        $user = User::query()->findOrFail($this->userId);

        $prepared = $rapportService->preparerPdfRapport($user, $activite, $rapport);

        try {
            Storage::disk('local')->makeDirectory('rapports');
            Storage::disk('local')->put($prepared['chemin'], $prepared['binary']);
        } catch (\Throwable $e) {
            Log::error('GenerateRapportPdfJob : échec Storage::put', [
                'rapport_id' => $this->rapportId,
                'chemin' => $prepared['chemin'],
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        $rapport->update(['chemin_pdf' => $prepared['chemin']]);
    }
}
