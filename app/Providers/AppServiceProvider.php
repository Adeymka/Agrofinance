<?php

namespace App\Providers;

use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use App\Services\OtpService;
use Illuminate\Pagination\Paginator;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        if ($this->app->environment('production') && config('services.fedapay.mock')) {
            throw new RuntimeException(
                'FEDAPAY_MOCK ne doit pas être activé en production (paiements simulés).'
            );
        }
    }
}
