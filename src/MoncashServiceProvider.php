<?php

namespace LouCov\LaravelMonCashApi;

use Illuminate\Support\ServiceProvider;

class MoncashServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if(app()->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/moncash.php' => config_path('moncash.php'),
            ], 'moncash-config');
        }
    }
}
