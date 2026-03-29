<?php

namespace App\Providers;

use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Rapport;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use App\Services\OtpService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

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
    }
}
