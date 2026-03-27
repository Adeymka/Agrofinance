<?php

namespace App\Providers;

use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use App\Services\OtpService;
use Illuminate\Pagination\Paginator;
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
    }
}
