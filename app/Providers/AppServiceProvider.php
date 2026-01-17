<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Validator;
use View;
use Illuminate\Support\Facades\Blade;

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
        //
        Schema::defaultStringLength(191);
        View::share('carpeta', '/bi');
        View::share('version', '22.10.27');
        View::share('vapp', '2025.09.03.21');
        Blade::componentNamespace('App\\View\\Components\\Form', 'form');
    }
}
