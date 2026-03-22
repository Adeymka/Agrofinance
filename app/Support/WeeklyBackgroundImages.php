<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Fonds d'écran desktop : 4 images par semaine (Agro, Agro, élevage, transformation),
 * rotation des jeux d'images d'une semaine sur l'autre.
 */
class WeeklyBackgroundImages
{
    /**
     * @return array<int, string> URLs absolues ou chemins pour asset() — 4 entrées
     */
    public static function weeklySlideUrls(): array
    {
        $dir = public_path('images');
        if (! is_dir($dir)) {
            return self::fallbackFour();
        }

        $paths = [];
        foreach (['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'] as $ext) {
            $found = glob($dir.DIRECTORY_SEPARATOR.'*.'.$ext) ?: [];
            foreach ($found as $p) {
                $paths[$p] = true;
            }
        }
        $paths = array_keys($paths);

        $agro = [];
        $elevage = [];
        $transformation = [];

        foreach ($paths as $path) {
            $name = basename($path);
            $lower = mb_strtolower($name);

            if (str_contains($lower, 'elevage') || str_contains($lower, 'eleveur')) {
                $elevage[] = $name;

                continue;
            }
            if (str_contains($lower, 'transformation') || $lower === 'huile.jpg') {
                $transformation[] = $name;

                continue;
            }
            if (preg_match('/^agro/i', $name) || preg_match('/^(markus|marek)-/i', $name)) {
                $agro[] = $name;

                continue;
            }
            // Autres fichiers dans images/ : rangés en Agro (cultures générales)
            $agro[] = $name;
        }

        sort($agro, SORT_NATURAL | SORT_FLAG_CASE);
        sort($elevage, SORT_NATURAL | SORT_FLAG_CASE);
        sort($transformation, SORT_NATURAL | SORT_FLAG_CASE);

        if ($agro === []) {
            return self::fallbackFour();
        }
        if ($elevage === []) {
            $elevage = $agro;
        }
        if ($transformation === []) {
            $transformation = $agro;
        }

        $weekIndex = self::isoWeekIndex();

        $na = count($agro);
        $ne = count($elevage);
        $nt = count($transformation);

        $agro1 = $agro[$weekIndex % $na];
        $agro2 = $agro[($weekIndex + 1) % $na];
        if ($na >= 2 && $agro2 === $agro1) {
            $agro2 = $agro[($weekIndex + 1) % $na];
        }
        $el = $elevage[$weekIndex % $ne];
        $tr = $transformation[$weekIndex % $nt];

        $toUrl = static fn (string $file): string => asset('images/'.$file);

        return [
            $toUrl($agro1),
            $toUrl($agro2),
            $toUrl($el),
            $toUrl($tr),
        ];
    }

    /**
     * Index stable par semaine ISO (année × 100 + numéro de semaine).
     */
    public static function isoWeekIndex(): int
    {
        $tz = new DateTimeZone(config('app.timezone', 'UTC'));
        $now = new DateTimeImmutable('now', $tz);

        return (int) $now->format('o') * 100 + (int) $now->format('W');
    }

    /**
     * @return array<int, string>
     */
    private static function fallbackFour(): array
    {
        $u = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1920&q=80';

        return [$u, $u, $u, $u];
    }
}
