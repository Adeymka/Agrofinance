<?php

use App\Http\Controllers\Web\AbonnementController;
use App\Http\Controllers\Web\ActiviteController;
use App\Http\Controllers\Web\Auth\ConnexionController;
use App\Http\Controllers\Web\Auth\InscriptionController;
use App\Http\Controllers\Web\Auth\OtpController;
use App\Http\Controllers\Web\Auth\PinController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExploitationController;
use App\Http\Controllers\Web\HelpController;
use App\Http\Controllers\Web\ProfilController;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\RapportController;
use App\Http\Controllers\Web\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('accueil');
})->name('home');

Route::get('/accueil', [PublicController::class, 'accueil'])->name('accueil');

// Page de fallback pour le Service Worker (mode hors ligne)
Route::get('/offline', function () {
    return response()->view('public.offline', [], 200)
        ->header('Cache-Control', 'no-store');
})->name('offline');
Route::get('/comment-ca-marche', [PublicController::class, 'commentCaMarche'])->name('comment-ca-marche');
Route::get('/a-propos', [PublicController::class, 'aPropos'])->name('a-propos');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'envoyerContact'])->name('contact.envoyer');

Route::prefix('aide')->name('aide.')->group(function () {
    Route::get('/', [HelpController::class, 'index'])->name('index');
    Route::get('/recherche', [HelpController::class, 'recherche'])->name('recherche');
    Route::get('/{categorie}', [HelpController::class, 'categorie'])->name('categorie');
    Route::get('/{categorie}/{article}', [HelpController::class, 'article'])->name('article');
});

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'showForm'])->name('connexion');
    Route::post('/connexion', [ConnexionController::class, 'store'])->name('connexion.store');

    Route::get('/inscription', [InscriptionController::class, 'showForm'])->name('inscription');
    Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');

    Route::get('/verification-otp', [OtpController::class, 'showForm'])->name('verification.otp');
    Route::post('/verification-otp', [OtpController::class, 'verify'])->name('verification.otp.submit');
    Route::post('/renvoyer-otp', [OtpController::class, 'renvoyer'])->name('renvoyer.otp');

    Route::get('/creer-pin', [PinController::class, 'showForm'])->name('creer.pin');
    Route::post('/creer-pin', [PinController::class, 'store'])->name('creer.pin.store');
});

Route::get('/partage/{token}', [RapportController::class, 'partager'])
    ->name('rapports.partager');

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [ConnexionController::class, 'destroy'])->name('deconnexion');

    Route::get('/abonnement/callback', [AbonnementController::class, 'callback'])
        ->name('abonnement.callback');

    Route::get('/abonnement', [AbonnementController::class, 'index'])->name('abonnement');
    Route::post('/abonnement/initier', [AbonnementController::class, 'initier'])->name('abonnement.initier');
    Route::post('/abonnement/finaliser-mock', [AbonnementController::class, 'finaliserMock'])->name('abonnement.finaliser-mock');

    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
});

Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/exploitations/creer', [ExploitationController::class, 'create'])->name('exploitations.create');
    Route::post('/exploitations', [ExploitationController::class, 'store'])->name('exploitations.store');

    Route::get('/activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/activites/creer', [ActiviteController::class, 'create'])->name('activites.create');
    Route::post('/activites', [ActiviteController::class, 'store'])->name('activites.store');
    Route::get('/activites/{id}', [ActiviteController::class, 'show'])->whereNumber('id')->name('activites.show');
    Route::post('/activites/{id}/cloturer', [ActiviteController::class, 'cloturer'])->whereNumber('id')->name('activites.cloturer');
    Route::post('/activites/{id}/abandonner', [ActiviteController::class, 'abandonner'])->whereNumber('id')->name('activites.abandonner');

    Route::get('/transactions/nouvelle', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}/modifier', [TransactionController::class, 'edit'])->whereNumber('id')->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->whereNumber('id')->name('transactions.update');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->whereNumber('id')->name('transactions.destroy');

    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/generer', [RapportController::class, 'generer'])->name('rapports.generer');
    Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger'])->whereNumber('id')->name('rapports.telecharger');

    // #30 — Vue alertes/index.blade.php desormais routee
    Route::get('/alertes', fn () => view('alertes.index'))->name('alertes.index');
});

// Healthcheck minimal (DB/cache/storage). Utile pour observabilité sous charge.
Route::get('/health', function () {
    $checks = [
        'database' => function () {
            \Illuminate\Support\Facades\DB::select('SELECT 1');
        },
        'cache' => function () {
            \Illuminate\Support\Facades\Cache::put('health', true, 5);
        },
        'storage' => function () {
            return \Illuminate\Support\Facades\Storage::disk('local')->exists('.gitignore');
        },
    ];

    $results = [];
    foreach ($checks as $name => $check) {
        try {
            $check();
            $results[$name] = 'ok';
        } catch (\Throwable $e) {
            $results[$name] = 'fail: ' . $e->getMessage();
        }
    }

    $hasFail = collect($results)->contains(fn ($v) => is_string($v) && str_starts_with($v, 'fail'));
    $status = $hasFail ? 503 : 200;

    return response()->json($results, $status);
})->name('health');

// Métriques minimales (format Prometheus texte). Protégez avec METRICS_TOKEN en production.
Route::get('/metrics', function (Request $request) {
    $expected = config('services.metrics.token');
    if (is_string($expected) && $expected !== '') {
        $ok = hash_equals($expected, (string) $request->bearerToken())
            || hash_equals($expected, (string) $request->query('token'));
        if (! $ok) {
            abort(403);
        }
    }

    $opcache = 0;
    if (function_exists('opcache_get_status')) {
        $st = @opcache_get_status(false);
        $opcache = is_array($st) && ! empty($st['opcache_enabled']) ? 1 : 0;
    }

    $lines = [
        '# HELP agrofinance_up Processus PHP répond.',
        '# TYPE agrofinance_up gauge',
        'agrofinance_up 1',
        '',
        '# HELP php_memory_usage_bytes Mémoire résidente du processus PHP.',
        '# TYPE php_memory_usage_bytes gauge',
        'php_memory_usage_bytes '.memory_get_usage(true),
        '',
        '# HELP php_memory_peak_bytes Pic mémoire du processus PHP.',
        '# TYPE php_memory_peak_bytes gauge',
        'php_memory_peak_bytes '.memory_get_peak_usage(true),
        '',
        '# HELP php_opcache_enabled Opcache activé (1) ou non (0).',
        '# TYPE php_opcache_enabled gauge',
        'php_opcache_enabled '.$opcache,
        '',
    ];

    $body = implode("\n", $lines)."\n";

    return response($body, 200, [
        'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
    ]);
})->name('metrics');
