<?php

namespace App\Support;

/**
 * Tarifs affichés et facturés (alignés sur config/tarifs_abonnement.php).
 */
final class TarifsAbonnement
{
    public static function montant(string $cleFacturation): int
    {
        $v = config('tarifs_abonnement.fcfa.'.$cleFacturation);

        return is_numeric($v) ? (int) $v : 0;
    }

    /**
     * Libellé avec espaces (ex. « 5 000 ») pour les vues Blade.
     */
    public static function libelleEspace(string $cleFacturation): string
    {
        $n = self::montant($cleFacturation);

        return $n > 0 ? number_format($n, 0, ',', ' ') : '0';
    }

    /**
     * @return array<string, int> clés mensuel / annuel / cooperative
     */
    public static function tousMontants(): array
    {
        $raw = config('tarifs_abonnement.fcfa', []);

        return is_array($raw) ? array_map('intval', $raw) : [];
    }
}
