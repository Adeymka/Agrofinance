<?php

use App\Http\Controllers\Web\AbonnementController;
use App\Http\Controllers\Web\ActiviteController;
use App\Http\Controllers\Web\Auth\ConnexionController;
use App\Http\Controllers\Web\Auth\InscriptionController;
use App\Http\Controllers\Web\Auth\OtpController;
use App\Http\Controllers\Web\Auth\PinController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExploitationController;
use App\Http\Controllers\Web\ProfilController;
use App\Http\Controllers\Web\RapportController;
use App\Http\Controllers\Web\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('connexion');
})->name('home');

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

    Route::get('/transactions/nouvelle', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}/modifier', [TransactionController::class, 'edit'])->whereNumber('id')->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->whereNumber('id')->name('transactions.update');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->whereNumber('id')->name('transactions.destroy');

    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/generer', [RapportController::class, 'generer'])->name('rapports.generer');
    Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger'])->whereNumber('id')->name('rapports.telecharger');
});
