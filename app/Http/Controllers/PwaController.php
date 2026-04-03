<?php

namespace App\Http\Controllers;

use App\Support\PwaUrl;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): \Illuminate\Http\JsonResponse
    {
        $scope = PwaUrl::scopePath();

        $icons = [];
        foreach ([72, 96, 128, 144, 152, 192, 384, 512] as $size) {
            $icons[] = [
                'src' => PwaUrl::publicPath("icons/icon-{$size}x{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
        }

        $screenshots = [
            [
                'src' => PwaUrl::publicPath('screenshots/dashboard-mobile.png'),
                'sizes' => '390x844',
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'Tableau de bord mobile',
            ],
            [
                'src' => PwaUrl::publicPath('screenshots/dashboard-desktop.png'),
                'sizes' => '1280x800',
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'Tableau de bord desktop',
            ],
        ];

        $shortcutIcon = ['src' => PwaUrl::publicPath('icons/icon-96x96.png'), 'sizes' => '96x96'];

        $shortcuts = [
            [
                'name' => 'Saisir une transaction',
                'short_name' => 'Saisie',
                'description' => 'Ajouter rapidement une dépense ou une recette',
                'url' => parse_url(route('transactions.create', absolute: true), PHP_URL_PATH) ?? '/transactions/nouvelle',
                'icons' => [$shortcutIcon],
            ],
            [
                'name' => 'Mes activités',
                'short_name' => 'Activités',
                'description' => 'Voir toutes les activités et campagnes',
                'url' => parse_url(route('activites.index', absolute: true), PHP_URL_PATH) ?? '/activites',
                'icons' => [$shortcutIcon],
            ],
        ];

        $payload = [
            'name' => 'AgroFinance+',
            'short_name' => 'AgroFinance+',
            'description' => 'Gérez vos exploitations agricoles et calculez vos indicateurs financiers agricoles facilement.',
            'start_url' => $scope,
            'scope' => $scope,
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#0D1F0D',
            'theme_color' => '#16a34a',
            'lang' => 'fr',
            'categories' => ['finance', 'productivity', 'agriculture'],
            'icons' => $icons,
            'screenshots' => $screenshots,
            'shortcuts' => $shortcuts,
        ];

        return response()
            ->json($payload)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function serviceWorker(): Response
    {
        $base = PwaUrl::pathPrefix();
        $manifestPath = parse_url(route('pwa.manifest', absolute: true), PHP_URL_PATH) ?? '/manifest.webmanifest';
        $offlinePath = parse_url(route('offline', absolute: true), PHP_URL_PATH) ?? '/offline';

        /** @var View $view */
        $view = view('pwa.service-worker', [
            'base' => $base,
            'manifestPath' => $manifestPath,
            'offlinePath' => $offlinePath,
        ]);

        return response($view->render(), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
