<?php

namespace Supravatm\CloverPayment;

use Illuminate\Support\ServiceProvider;
use Supravatm\CloverPayment\Services\CloverClient;

class CloverPaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        // $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'clover');
        $this->publishes([__DIR__ . '/../config/clover.php' => config_path('clover.php')], 'config');
        $this->publishes([__DIR__ . '/../resources/views' => resource_path('views/vendor/clover')], 'views');
        $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/clover.php',
            'clover'
        );
        $this->app->singleton(CloverClient::class, function ($app) {
            return new CloverClient($app['config']['clover']);
        });
    }
}
