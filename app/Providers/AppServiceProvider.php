<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PdftkService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PdftkService::class, function ($app) {
            return new PdftkService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
