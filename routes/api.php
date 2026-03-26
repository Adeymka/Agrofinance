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

    // Throttle "général" (lecture dashboard/indicateurs + actions métiers légères)
    Route::middleware('throttle:60,1')->group(function () {

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
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);
        Route::put('/transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

        // Module indicateurs financiers agricoles (route la plus spécifique en premier)
        Route::get('/indicateurs/activite/{id}/evolution', [IndicateurController::class, 'evolution']);
        Route::get('/indicateurs/activite/{id}', [IndicateurController::class, 'parActivite']);
        Route::get('/indicateurs/exploitation/{id}', [IndicateurController::class, 'parExploitation']);

        Route::get('/dashboard', DashboardController::class);

        // Module Rapports (hors génération PDF lourde)
        Route::get('/rapports', [RapportController::class, 'index']);
        Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger']);
    });

    // Génération PDF (opération lourde)
    Route::middleware('throttle:5,15')->group(function () {
        Route::post('/rapports/generer', [RapportController::class, 'generer']);
    });

    // Création de transactions (peut être utilisée en batch)
    Route::middleware('throttle:200,1')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
    });
});

// Callback FedaPay : redirection navigateur, sans token Sanctum
Route::get('/abonnement/callback', [AbonnementController::class, 'callback']);
