<?php

use App\Http\Controllers\Api\AbonnementController;
use App\Http\Controllers\Api\ActiviteController;
use App\Http\Controllers\Api\Auth\ConnexionController;
use App\Http\Controllers\Api\Auth\InscriptionController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\PinController;
use App\Http\Controllers\Api\Auth\VerificationOtpController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExploitationController;
use App\Http\Controllers\Api\IndicateurController;
use App\Http\Controllers\Api\RapportController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// ─── #23 — Versioning API v1 ───────────────────────────────────────────────
// Toutes les routes sont desormais sous /api/v1/ (Route::prefix dans bootstrap/app.php
// definit le prefix /api, ce groupe ajoute /v1).
// Les anciens appels sans /v1 ne fonctionneront plus - documenter la migration dans API.md.
Route::prefix('v1')->name('api.v1.')->group(function () {

    // ─── Routes publiques (sans token) ────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/inscription',      InscriptionController::class);
        Route::post('/verification-otp', VerificationOtpController::class);
        Route::post('/renvoyer-otp',     [VerificationOtpController::class, 'renvoyer']);
        Route::post('/creer-pin',        PinController::class);
        Route::post('/connexion',        ConnexionController::class);
    });

    // ─── Auth Sanctum — sans abonnement requis ─────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/deconnexion', function () {
                auth()->user()->currentAccessToken()->delete();

                return response()->json(['succes' => true, 'message' => 'Deconnecte.']);
            });
            Route::get('/me', MeController::class);
        });

        Route::post('/abonnement/initier',       [AbonnementController::class, 'initier']);
        Route::post('/abonnement/finaliser-mock', [AbonnementController::class, 'finaliserMock']);
    });

    // ─── Routes necessitant un abonnement actif ────────────────────────
    Route::middleware(['auth:sanctum', 'subscribed'])->group(function () {

        // Throttle general (lecture + actions metiers legeres)
        Route::middleware('throttle:60,1')->group(function () {

            // Exploitations
            Route::get('/exploitations',      [ExploitationController::class, 'index']);
            Route::post('/exploitations',     [ExploitationController::class, 'store']);
            Route::get('/exploitations/{id}', [ExploitationController::class, 'show']);
            Route::put('/exploitations/{id}', [ExploitationController::class, 'update']);

            // Activites
            Route::get('/activites',                   [ActiviteController::class, 'index']);
            Route::post('/activites',                  [ActiviteController::class, 'store']);
            Route::get('/activites/{id}',              [ActiviteController::class, 'show']);
            Route::put('/activites/{id}',              [ActiviteController::class, 'update']);
            Route::post('/activites/{id}/cloturer',    [ActiviteController::class, 'cloturer']);
            Route::post('/activites/{id}/abandonner',  [ActiviteController::class, 'abandonner']);

            // Transactions
            Route::get('/transactions',       [TransactionController::class, 'index']);
            Route::get('/transactions/{id}',  [TransactionController::class, 'show']);
            Route::put('/transactions/{id}',  [TransactionController::class, 'update']);
            Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

            // Indicateurs financiers agricoles
            Route::get('/indicateurs/activite/{id}/evolution', [IndicateurController::class, 'evolution']);
            Route::get('/indicateurs/activite/{id}',           [IndicateurController::class, 'parActivite']);
            Route::get('/indicateurs/exploitation/{id}',       [IndicateurController::class, 'parExploitation']);

            // Dashboard
            Route::get('/dashboard', DashboardController::class);

            // Rapports
            Route::get('/rapports',                  [RapportController::class, 'index']);
            Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger']);
        });

        // Generation PDF (operation lourde — throttle restrictif)
        Route::middleware('throttle:5,15')->group(function () {
            Route::post('/rapports/generer', [RapportController::class, 'generer']);
        });

        // Transactions en batch
        Route::middleware('throttle:200,1')->group(function () {
            Route::post('/transactions', [TransactionController::class, 'store']);
        });
    });

    // ─── Callback FedaPay : redirection navigateur, sans token Sanctum ─
    Route::get('/abonnement/callback', [AbonnementController::class, 'callback']);

}); // fin prefix('v1')
