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
 * Forçage ponctuel (tests) : ?platform=mobile|desktop — effet limité à la
 * requête courante (pas de mémorisation en session).
 */
class DetectPlatform
{
    public function handle(Request $request, Closure $next): Response
    {
        session()->forget('platform_forced');

        $platform = $this->resolve($request);

        session(['platform' => $platform]);

        $layout = $platform === 'mobile' ? 'layouts.app-mobile' : 'layouts.app-desktop';

        view()->share('layout', $layout);
        view()->share('platform', $platform);

        $response = $next($request);

        // Invite le navigateur à envoyer Sec-CH-UA-Mobile (Chrome / Edge notamment)
        $response->headers->set('Accept-CH', 'Sec-CH-UA-Mobile');

        return $response;
    }

    private function resolve(Request $request): string
    {
        $forced = $request->query('platform');
        if (in_array($forced, ['mobile', 'desktop'], true)) {
            return $forced;
        }

        // Client Hint (Chrome, Edge, etc.) : plus fiable que le User-Agent seul
        // (?1 = téléphone / mode mobile, ?0 = bureau / tablette « desktop »)
        $chMobile = $request->header('Sec-CH-UA-Mobile');
        if ($chMobile !== null && $chMobile !== '') {
            $chMobile = trim($chMobile);
            if ($chMobile === '?1') {
                return 'mobile';
            }
            if ($chMobile === '?0') {
                return 'desktop';
            }
        }

        // Fallback User-Agent (navigateurs sans Client Hints, outils, proxies)
        $ua = strtolower($request->userAgent() ?? '');

        // Pas de mot-clé seul « mobile » : sous-chaîne trop courante / faux positifs
        $mobileKeywords = [
            'android', 'iphone', 'ipod', 'blackberry', 'windows phone',
            'opera mini', 'opera mobi', 'iemobile',
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
