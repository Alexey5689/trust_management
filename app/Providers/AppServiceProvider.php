<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //if (app()->environment('production')) {
        //    URL::forceScheme('https');
        //}
        // Vite::prefetch(concurrency: 3);
        if (app()->environment('production')) {
            Vite::prefetch(concurrency: 5);  // Для production больше параллелизма
        } else {
            Vite::prefetch(concurrency: 1);  // Для development меньше
        }
    }
}
