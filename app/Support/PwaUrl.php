<?php

namespace App\Support;

/**
 * Chemins PWA (manifest, service worker) dérivés de APP_URL — pas de préfixe XAMPP en dur.
 */
final class PwaUrl
{
    public static function pathPrefix(): string
    {
        $path = parse_url((string) config('app.url'), PHP_URL_PATH);
        if ($path === null || $path === '' || $path === false) {
            return '';
        }

        return rtrim((string) $path, '/');
    }

    /** Pour start_url / scope : toujours une chaîne commençant par / et se terminant par / */
    public static function scopePath(): string
    {
        $p = self::pathPrefix();

        return $p === '' ? '/' : $p.'/';
    }

    /** Chemin absolu pour une ressource sous public/ (ex. /icons/...) */
    public static function publicPath(string $relative): string
    {
        $path = parse_url(asset($relative), PHP_URL_PATH);

        return $path !== null && $path !== false ? $path : '/'.ltrim($relative, '/');
    }
}
