<?php

namespace Bfg\WebHooker;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * Class ServiceProvider
 * @package Bfg\WebHooker
 */
class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register route settings.
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/webhooker.php',
            'webhooker'
        );
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot()
    {
        Route::middleware(config('webhooker.routes.middleware'))
            ->prefix(config('webhooker.routes.prefix'))
            ->as('webhook.')
            ->group(__DIR__ . '/routes.php');
    }
}

