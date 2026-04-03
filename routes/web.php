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
use App\Http\Controllers\Web\CooperativeController;
use App\Http\Controllers\Web\ProfilController;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\RapportController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\Web\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// PWA : manifest + SW dérivés de APP_URL (pas de chemins XAMPP en dur)
Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');

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
Route::get('/confidentialite', [PublicController::class, 'confidentialite'])->name('confidentialite');
Route::get('/conditions-utilisation', [PublicController::class, 'conditionsUtilisation'])->name('conditions-utilisation');

Route::prefix('aide')->name('aide.')->group(function () {
    Route::get('/', [HelpController::class, 'index'])->name('index');
    Route::get('/recherche', [HelpController::class, 'recherche'])->name('recherche');
    Route::get('/{categorie}', [HelpController::class, 'categorie'])->name('categorie');
    Route::get('/{categorie}/{article}', [HelpController::class, 'article'])->name('article');
});

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'showForm'])->name('connexion');
    Route::post('/connexion', [ConnexionController::class, 'store'])->middleware('throttle:auth-connexion')->name('connexion.store');

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
    Route::get('/cooperative/invitation/{token}', [CooperativeController::class, 'showInvitation'])->name('cooperative.invitation.show');
    Route::post('/cooperative/invitation/{token}/accepter', [CooperativeController::class, 'acceptInvitation'])->name('cooperative.invitation.accept');

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
    Route::get('/dashboard/export/consolide-entreprise-csv', [DashboardController::class, 'exporterConsolideEntrepriseCsv'])
        ->name('dashboard.export.consolide.csv');

    Route::get('/exploitations', [ExploitationController::class, 'index'])->name('exploitations.index');
    Route::get('/exploitations/creer', [ExploitationController::class, 'create'])->name('exploitations.create');
    Route::post('/exploitations', [ExploitationController::class, 'store'])->name('exploitations.store');
    Route::get('/exploitations/{id}/modifier', [ExploitationController::class, 'edit'])->whereNumber('id')->name('exploitations.edit');
    Route::put('/exploitations/{id}', [ExploitationController::class, 'update'])->whereNumber('id')->name('exploitations.update');
    Route::get('/exploitations/{id}', [ExploitationController::class, 'show'])->whereNumber('id')->name('exploitations.show');

    Route::get('/activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/activites/creer', [ActiviteController::class, 'create'])->name('activites.create');
    Route::post('/activites', [ActiviteController::class, 'store'])->name('activites.store');
    Route::get('/activites/{id}', [ActiviteController::class, 'show'])->whereNumber('id')->name('activites.show');
    Route::post('/activites/{id}/cloturer', [ActiviteController::class, 'cloturer'])->whereNumber('id')->name('activites.cloturer');
    Route::post('/activites/{id}/abandonner', [ActiviteController::class, 'abandonner'])->whereNumber('id')->name('activites.abandonner');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/nouvelle', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}/justificatif', [TransactionController::class, 'telechargerJustificatif'])->whereNumber('id')->name('transactions.justificatif');
    Route::get('/transactions/{id}/modifier', [TransactionController::class, 'edit'])->whereNumber('id')->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->whereNumber('id')->name('transactions.update');
    Route::post('/transactions/{id}/valider', [TransactionController::class, 'valider'])->whereNumber('id')->name('transactions.valider');
    Route::post('/transactions/{id}/remettre-en-attente', [TransactionController::class, 'remettreEnAttente'])->whereNumber('id')->name('transactions.remettre-en-attente');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->whereNumber('id')->name('transactions.destroy');

    Route::get('/cooperative/membres', [CooperativeController::class, 'members'])->name('cooperative.members');
    Route::post('/cooperative/membres/inviter', [CooperativeController::class, 'invite'])->name('cooperative.members.invite');
    Route::post('/cooperative/membres/{id}/role', [CooperativeController::class, 'updateRole'])->whereNumber('id')->name('cooperative.members.role');
    Route::post('/cooperative/membres/{id}/statut', [CooperativeController::class, 'toggleStatus'])->whereNumber('id')->name('cooperative.members.status');
    Route::post('/cooperative/membres/{id}/invitation/rotate', [CooperativeController::class, 'rotateInvitation'])->whereNumber('id')->name('cooperative.members.invitation.rotate');
    Route::post('/cooperative/membres/{id}/invitation/revoke', [CooperativeController::class, 'revokeInvitation'])->whereNumber('id')->name('cooperative.members.invitation.revoke');
    Route::post('/cooperative/seuil-validation', [CooperativeController::class, 'updateThreshold'])->name('cooperative.threshold.update');
    Route::get('/cooperative/audit/export.csv', [CooperativeController::class, 'exportAuditCsv'])->name('cooperative.audit.export.csv');

    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/generer', [RapportController::class, 'generer'])->name('rapports.generer');
    Route::get('/rapports/{id}/telecharger', [RapportController::class, 'telecharger'])->whereNumber('id')->name('rapports.telecharger');
});
