<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->redirectGuestsTo(fn () => route('connexion'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->alias([
            'auth'         => \App\Http\Middleware\Authenticate::class,
            'guest'        => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'subscribed'   => \App\Http\Middleware\VerifierAbonnement::class,
        ]);
        // Détection plateforme sur toutes les routes web (partage $layout dans les vues)
        $middleware->web(append: \App\Http\Middleware\DetectPlatform::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // XAMPP : URL souvent .../public/api/... donc is('api/*') est faux — on détecte le segment "api"
        $isApiRequest = static fn (\Illuminate\Http\Request $request): bool => $request->is('api/*')
            || in_array('api', $request->segments(), true);

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request))
                return response()->json(['succes' => false, 'message' => 'Non authentifié.'], 401);
        });
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request))
                return response()->json(['succes' => false, 'message' => 'Introuvable.'], 404);
        });
        // findOrFail() est souvent converti en NotFoundHttpException : même réponse API uniforme
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request))
                return response()->json(['succes' => false, 'message' => 'Introuvable.'], 404);
        });
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request))
                return response()->json(['succes' => false, 'errors' => $e->errors()], 422);
        });
    })->create();
