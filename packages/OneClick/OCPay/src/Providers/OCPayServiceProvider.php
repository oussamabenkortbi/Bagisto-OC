<?php

namespace OneClick\OCPay\Providers;

use Illuminate\Support\ServiceProvider;

class OCPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/paymentmethods.php',
            'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php',
            'core'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'ocpay');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'ocpay');
    }
}
