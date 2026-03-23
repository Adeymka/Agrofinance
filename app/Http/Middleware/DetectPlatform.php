<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Détecte la plateforme (mobile / desktop) depuis le User-Agent
 * et partage la variable $layout avec toutes les vues Blade.
 *
 * Mobile  → layouts.app-mobile
 * Desktop → layouts.app-desktop
 *
 * L'utilisateur peut forcer la plateforme via ?platform=mobile|desktop
 * (utile pour les tests).
 */
class DetectPlatform
{
    public function handle(Request $request, Closure $next): Response
    {
        $platform = $this->resolve($request);

        session(['platform' => $platform]);

        $layout = $platform === 'mobile' ? 'layouts.app-mobile' : 'layouts.app-desktop';

        view()->share('layout', $layout);
        view()->share('platform', $platform);

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        // Forçage manuel (tests, debug) : ?platform=mobile ou ?platform=desktop
        $forced = $request->query('platform');
        if (in_array($forced, ['mobile', 'desktop'], true)) {
            session(['platform_forced' => $forced]);
            return $forced;
        }

        if (session()->has('platform_forced')) {
            return session('platform_forced');
        }

        // Détection via User-Agent
        $ua = strtolower($request->userAgent() ?? '');

        $mobileKeywords = [
            'android', 'iphone', 'ipod', 'blackberry', 'windows phone',
            'mobile', 'opera mini', 'opera mobi', 'iemobile',
            'silk', 'kindle', 'webos', 'symbian', 'nokia',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (str_contains($ua, $keyword)) {
                return 'mobile';
            }
        }

        return 'desktop';
    }
}
