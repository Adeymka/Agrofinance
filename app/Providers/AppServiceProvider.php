<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\AbonnementPolicy;
use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use App\Services\HelpSearchService;
use App\Services\OtpService;
use App\Services\RapportService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AbonnementService::class);
        $this->app->singleton(FinancialIndicatorsService::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(RapportService::class);
        $this->app->singleton(HelpSearchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Garde FedaPay Mock hors production
        if ($this->app->environment('production') && config('services.fedapay.mock')) {
            throw new RuntimeException(
                'FEDAPAY_MOCK ne doit pas etre active en production (paiements simules).'
            );
        }

        // #16 — Enregistrement des Gates d'abonnement via AbonnementPolicy
        // Utilisation : Gate::allows('genererPdf') ou @can('genererPdf') ou $this->authorize('genererPdf')
        Gate::define('genererPdf', fn (User $user) => app(AbonnementPolicy::class)->genererPdf($user));
        Gate::define('genererDossierCredit', fn (User $user) => app(AbonnementPolicy::class)->genererDossierCredit($user));
        Gate::define('multiExploitations', fn (User $user) => app(AbonnementPolicy::class)->multiExploitations($user));
        Gate::define('creerExploitation', fn (User $user) => app(AbonnementPolicy::class)->creerExploitation($user));
        Gate::define('accederPdfRapport', fn (User $user, string $type) => app(AbonnementPolicy::class)->accederPdfRapport($user, $type));
    }
}
