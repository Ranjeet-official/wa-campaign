<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        Paginator::useBootstrap();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                view()->share('settings', \App\Models\Setting::first());
            }
        } catch (\Exception $e) {
            // Ignore database errors during deployment
        }
    }
}
