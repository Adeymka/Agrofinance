<?php

namespace App\Services;

use App\Models\Activite;
use Illuminate\Support\Facades\DB;

class ActiviteStatutService
{
    /**
     * @return array{ok: true, activite: Activite}|array{ok: false, reason: 'not_found'|'invalid_statut', message: string}
     */
    public function cloturer(int $activiteId, int $userId): array
    {
        return DB::transaction(function () use ($activiteId, $userId) {
            $activite = Activite::query()
                ->pourUtilisateur($userId)
                ->whereKey($activiteId)
                ->lockForUpdate()
                ->first();

            if (! $activite) {
                return ['ok' => false, 'reason' => 'not_found', 'message' => ''];
            }

            if ($activite->statut !== Activite::STATUT_EN_COURS) {
                return [
                    'ok' => false,
                    'reason' => 'invalid_statut',
                    'message' => 'Seules les campagnes en cours peuvent être clôturées.',
                ];
            }

            $activite->update([
                'statut' => Activite::STATUT_TERMINE,
                'date_fin' => now()->toDateString(),
            ]);

            return ['ok' => true, 'activite' => $activite->fresh()];
        });
    }

    /**
     * @return array{ok: true, activite: Activite}|array{ok: false, reason: 'not_found'|'invalid_statut', message: string}
     */
    public function abandonner(int $activiteId, int $userId): array
    {
        return DB::transaction(function () use ($activiteId, $userId) {
            $activite = Activite::query()
                ->pourUtilisateur($userId)
                ->whereKey($activiteId)
                ->lockForUpdate()
                ->first();

            if (! $activite) {
                return ['ok' => false, 'reason' => 'not_found', 'message' => ''];
            }

            if ($activite->statut !== Activite::STATUT_EN_COURS) {
                return [
                    'ok' => false,
                    'reason' => 'invalid_statut',
                    'message' => 'Seules les campagnes en cours peuvent être marquées comme abandonnées.',
                ];
            }

            $activite->update([
                'statut' => Activite::STATUT_ABANDONNE,
                'date_fin' => $activite->date_fin ?? now()->toDateString(),
            ]);

            return ['ok' => true, 'activite' => $activite->fresh()];
        });
    }
}
