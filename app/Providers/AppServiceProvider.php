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
     */ public function boot(): void
    {
        // ✅ Default pagination view — "Showing X to Y" nahi aayega
        Paginator::defaultView('vendor.pagination.simple-bootstrap-5');
        Paginator::useBootstrap();

        try {
            if (Schema::hasTable('settings')) {
                view()->share('settings', Setting::first());
            }
        } catch (\Exception $e) {
            // Ignore database errors during deployment
        }
    }
}
