<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

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
        // Set UTF-8 encoding for MySQL connections
        DB::statement("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    }
}
