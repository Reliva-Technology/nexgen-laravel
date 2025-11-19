<?php

namespace Reliva\Nexgen;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

use Reliva\Nexgen\NexgenClient;

class NexgenServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nexgen.php',
            'nexgen'
        );

        // Register any service bindings here
        $this->app->singleton('nexgen', function ($app) {
            return new NexgenClient();
        });

        $this->app->singleton('nexgen-qr', function ($app) {
            return new NexgenQRClient();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../config/nexgen.php' => config_path('nexgen.php'),
        ], 'nexgen-config');

        // Publish migrations (if any)
        // $this->publishes([
        //     __DIR__ . '/../database/migrations' => database_path('migrations'),
        // ], 'nexgen-migrations');

        // Load package routes
        // $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load package views
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nexgen');

        // Publish views (if you want users to customize them)
        // $this->publishes([
        //     __DIR__ . '/../resources/views' => resource_path('views/vendor/nexgen'),
        // ], 'nexgen-views');

        // Load package translations
        // $this->loadTranslationsFrom(__DIR__ . '/../lang', 'nexgen');

        // Publish translations
        // $this->publishes([
        //     __DIR__ . '/../lang' => lang_path('vendor/nexgen'),
        // ], 'nexgen-translations');
    }
}

