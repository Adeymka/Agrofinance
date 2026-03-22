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

// Routes publiques (sans token)
Route::prefix('auth')->group(function () {
    Route::post('/inscription', InscriptionController::class);
    Route::post('/verification-otp', VerificationOtpController::class);
    Route::post('/renvoyer-otp', [VerificationOtpController::class, 'renvoyer']);
    Route::post('/creer-pin', PinController::class);
    Route::post('/connexion', ConnexionController::class);
});

// Auth Sanctum — renouvellement / méta (sans abonnement actif requis)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/deconnexion', function () {
            auth()->user()->currentAccessToken()->delete();

            return response()->json(['succes' => true, 'message' => 'Déconnecté.']);
        });
        Route::get('/me', MeController::class);
    });

    Route::post('/abonnement/initier', [AbonnementController::class, 'initier']);
    Route::post('/abonnement/finaliser-mock', [AbonnementController::class, 'finaliserMock']);
});

// Abonnement actif requis
Route::middleware(['auth:sanctum', 'subscribed'])->group(function () {

    // Module Exploitations
    Route::get('/exploitations', [ExploitationController::class, 'index']);
    Route::post('/exploitations', [ExploitationController::class, 'store']);
    Route::get('/exploitations/{id}', [ExploitationController::class, 'show']);
    Route::put('/exploitations/{id}', [ExploitationController::class, 'update']);

    // Module Activités
    Route::get('/activites', [ActiviteController::class, 'index']);
    Route::post('/activites', [ActiviteController::class, 'store']);
    Route::get('/activites/{id}', [ActiviteController::class, 'show']);
    Route::put('/activites/{id}', [ActiviteController::class, 'update']);
    Route::post('/activites/{id}/cloturer', [ActiviteController::class, 'cloturer']);
    Route::post('/activites/{id}/abandonner', [ActiviteController::class, 'abandonner']);

    // Module Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Module indicateurs financiers agricoles (route la plus spécifique en premier)
    Route::get('/indicateurs/activite/{id}/evolution', [IndicateurController::class, 'evolution']);
    Route::get('/indicateurs/activite/{id}', [IndicateurController::class, 'parActivite']);
    Route::get('/indicateurs/exploitation/{id}', [IndicateurController::class, 'parExploitation']);

    Route::get('/dashboard', DashboardController::class);

    // Module Rapports (route fixe avant {id})
    Route::get('/rapports', [RapportController::class, 'index']);
    Route::post('/rapports/generer', [RapportController::class, 'generer']);
    Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger']);
});

// Callback FedaPay : redirection navigateur, sans token Sanctum
Route::get('/abonnement/callback', [AbonnementController::class, 'callback']);
