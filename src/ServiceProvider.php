<?php

namespace Bfg\WebHooker;

use Bfg\WebHooker\Commands\WebHoodAssociateCommand;
use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\Observers\WebHookObserver;
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

        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations')
        ], 'web-hooker-migrations');

        $this->publishes([
            __DIR__ . '/../config/webhooker.php' => config_path('webhooker.php')
        ], 'web-hooker-config');
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot()
    {
        Route::middleware(config('webhooker.routes.middleware'))
            ->prefix(config('webhooker.routes.prefix'))
            ->any('{hash}', [\Bfg\WebHooker\Controllers\WebHookerController::class, 'response'])
            ->name('webhook.response');

        WebHook::observe(WebHookObserver::class);

        $this->commands([
            WebHoodAssociateCommand::class
        ]);
    }
}

