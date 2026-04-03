<?php

namespace App\Providers;

use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\CooperativeService;
use App\Models\Rapport;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use App\Services\OtpService;
use App\Services\TransactionJustificatifService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AbonnementService::class);
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(FinancialIndicatorsService::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(TransactionJustificatifService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        /*
         * Sprint S1 — D1 : limiter les tentatives de connexion (PIN court) sans bloquer toute une IP partagée :
         * clé = téléphone normalisé + IP.
         */
        RateLimiter::for('auth-connexion', function (Request $request) {
            $phone = strtolower((string) $request->input('telephone', ''));

            return Limit::perMinute(10)->by(sha1($phone.'|'.$request->ip()));
        });

        Gate::define('gerer-exploitation', function (User $user, Exploitation $exploitation): bool {
            return (int) $exploitation->user_id === (int) $user->id;
        });

        Gate::define('gerer-activite', function (User $user, Activite $activite): bool {
            return Activite::pourUtilisateur((int) $user->id)->whereKey($activite->getKey())->exists();
        });

        Gate::define('gerer-transaction', function (User $user, Transaction $transaction): bool {
            return Activite::pourUtilisateur((int) $user->id)
                ->whereKey($transaction->activite_id)
                ->exists();
        });

        Gate::define('gerer-rapport', function (User $user, Rapport $rapport): bool {
            return Exploitation::query()
                ->whereKey($rapport->exploitation_id)
                ->where('user_id', $user->id)
                ->exists();
        });

        Gate::define('abonnement-actif', function (User $user): bool {
            return app(AbonnementService::class)->estActif($user);
        });

        // Contexte multi-exploitations (campagnes) : même propriétaire que le tableau de bord (coop)
        View::composer(
            ['layouts.app-mobile', 'layouts.app-desktop', 'layouts.app', 'components.sidebar'],
            function (\Illuminate\View\View $view): void {
                $user = auth()->user();
                if (! $user) {
                    $view->with('exploitationNavId', null);

                    return;
                }
                $ownerId = (int) app(CooperativeService::class)->resolveOwner($user)->id;
                $view->with('exploitationNavId', Exploitation::navigationContextIdForUser($ownerId));
            }
        );
    }
}
