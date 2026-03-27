<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry — Application Performance Monitoring (APM) et gestion des erreurs.
    | #10 — Integrate Sentry pour monitorer les exceptions en production.
    |
    | Installation :
    |   composer require sentry/sentry-laravel
    |   php artisan sentry:publish --dsn=<SENTRY_DSN>
    |
    | Documentation : https://docs.sentry.io/platforms/php/guides/laravel/
    |--------------------------------------------------------------------------
    */

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    /*
    | Pourcentage de transactions capturees pour le tracing des performances.
    | 1.0 = 100% (dev/staging), 0.1 = 10% recommande en production.
    */
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 1.0),

    /*
    | Taux d'echantillonnage des sessions de profiling (necessite traces_sample_rate > 0).
    */
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 1.0),

    /*
    | Intégrations automatiques Laravel : routes, queues, BDD, cache.
    */
    'send_default_pii' => false,     // Ne pas envoyer les donnees personnelles (RGPD)

    /*
    | Environnements ou Sentry doit capturer les erreurs.
    | En local on prefere les logs console via Ignition.
    */
    'environment' => env('APP_ENV', 'production'),

    /*
    | Release : utile pour trackeer les regressions entre versions.
    | Positionner via: php artisan sentry:publish ou dans le pipeline CI.
    */
    'release' => env('SENTRY_RELEASE', null),

    /*
    | Filtres : exceptions a ignorer (evite le bruit dans Sentry).
    */
    'ignore_exceptions' => [
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
    ],

];
