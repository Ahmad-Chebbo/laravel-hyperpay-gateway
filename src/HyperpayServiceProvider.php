<?php

namespace AhmadShebbo\LaravelHyperpay;

use AhmadShebbo\LaravelHyperpay\Services\HyperPayResultCodeService;
use AhmadShebbo\LaravelHyperpay\Services\HyperPayService;
use Illuminate\Support\ServiceProvider;

class HyperpayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/hyperpay.php', 'hyperpay'
        );

        $this->app->singleton('hyperpay', function ($app) {
            return new HyperPayService;
        });

        $this->app->singleton('hyperpay.result', function ($app) {
            return new HyperPayResultCodeService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyperpay');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/hyperpay.php' => config_path('hyperpay.php'),
            ], 'hyperpay-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'hyperpay-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/hyperpay'),
            ], 'hyperpay-views');

            $this->commands([
                Commands\HyperPayInstallCommand::class,
                Commands\HyperPayStatusCommand::class,
            ]);
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/hyperpay.php');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'hyperpay');

        // Publish translations
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/hyperpay'),
        ], 'hyperpay-lang');
    }
}
