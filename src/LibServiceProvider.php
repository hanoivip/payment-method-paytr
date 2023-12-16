<?php

namespace Hanoivip\PaymentMethodPaytr;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class LibServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/hanoivip'),
            __DIR__.'/../config' => config_path(),
        ]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom( __DIR__.'/../lang', 'hanoivip.paytr');
        $this->mergeConfigFrom( __DIR__.'/../config/paytr.php', 'paytr');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip');
    }
    
    public function register()
    {
        $this->commands([
        ]);
        $this->app->bind("PaytrPaymentMethod", PaytrMethod::class);
    }
}
