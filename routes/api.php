<?php

use App\Http\Controllers\Api\Auth\{
    InscriptionController,
    VerificationOtpController,
    PinController,
    ConnexionController,
    MeController
};
use App\Http\Controllers\Api\{
    AbonnementController,
    ActiviteController,
    DashboardController,
    ExploitationController,
    IndicateurController,
    RapportController,
    TransactionController,
};
use Illuminate\Support\Facades\Route;

// Routes publiques (sans token)
Route::prefix('auth')->group(function () {
    Route::post('/inscription',      InscriptionController::class);
    Route::post('/verification-otp', VerificationOtpController::class);
    Route::post('/renvoyer-otp',     [VerificationOtpController::class, 'renvoyer']);
    Route::post('/creer-pin',        PinController::class);
    Route::post('/connexion',        ConnexionController::class);
});

// Routes protégées (token Sanctum requis)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/deconnexion', function () {
        auth()->user()->currentAccessToken()->delete();
        return response()->json(['succes' => true, 'message' => 'Déconnecté.']);
    });
    Route::get('/me', MeController::class);
});

// Toutes ces routes nécessitent un token Sanctum valide
Route::middleware('auth:sanctum')->group(function () {

    // Module Exploitations
    Route::get('/exploitations',       [ExploitationController::class, 'index']);
    Route::post('/exploitations',      [ExploitationController::class, 'store']);
    Route::get('/exploitations/{id}',  [ExploitationController::class, 'show']);
    Route::put('/exploitations/{id}',  [ExploitationController::class, 'update']);

    // Module Activités
    Route::get('/activites',                       [ActiviteController::class, 'index']);
    Route::post('/activites',                      [ActiviteController::class, 'store']);
    Route::get('/activites/{id}',                  [ActiviteController::class, 'show']);
    Route::put('/activites/{id}',                  [ActiviteController::class, 'update']);
    Route::post('/activites/{id}/cloturer',        [ActiviteController::class, 'cloturer']);

    // Module Transactions
    Route::get('/transactions',         [TransactionController::class, 'index']);
    Route::post('/transactions',        [TransactionController::class, 'store']);
    Route::get('/transactions/{id}',    [TransactionController::class, 'show']);
    Route::put('/transactions/{id}',    [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Module Indicateurs FSA (route la plus spécifique en premier)
    Route::get('/indicateurs/activite/{id}/evolution', [IndicateurController::class, 'evolution']);
    Route::get('/indicateurs/activite/{id}', [IndicateurController::class, 'parActivite']);
    Route::get('/indicateurs/exploitation/{id}', [IndicateurController::class, 'parExploitation']);

    Route::get('/dashboard', DashboardController::class);

    // Module Rapports (route fixe avant {id})
    Route::get('/rapports', [RapportController::class, 'index']);
    Route::post('/rapports/generer', [RapportController::class, 'generer']);
    Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger']);

    // Abonnement — initiation (auth requise)
    Route::post('/abonnement/initier', [AbonnementController::class, 'initier']);
    // Sans FedaPay : finalise l’abonnement après initier (FEDAPAY_MOCK=true uniquement)
    Route::post('/abonnement/finaliser-mock', [AbonnementController::class, 'finaliserMock']);
});

// Callback FedaPay : redirection navigateur, sans token Sanctum
Route::get('/abonnement/callback', [AbonnementController::class, 'callback']);

