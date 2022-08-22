<?php

namespace Bfg\WebHooker;

use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use Bfg\WebHooker\Commands\OrganizerMakeCommand;
use Bfg\WebHooker\Commands\WebHoodAssociateCommand;
use Bfg\WebHooker\Commands\WebHookOpenClientCommand;
use Bfg\WebHooker\Controllers\WebHookerController;
use Bfg\WebHooker\Controllers\WebSocketController;
use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\Observers\WebHookObserver;
use Illuminate\Http\Request;
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
        if (config('webhooker.type.http_request', false)) {

            Route::middleware(config('webhooker.routes.middleware'))
                ->prefix(config('webhooker.routes.prefix'))
                ->any('{hash}', [WebHookerController::class, 'response'])
                ->name('webhook.response');
        }

        if (
            config('webhooker.type.websocket_open_signature', false)
            && class_exists(WebSocketsRouter::class)
        ) {
            WebSocketsRouter::webSocket('/hook/{appKey}/{clientKey}', WebSocketController::class);
        }

        if (config('webhooker.type.websocket_open_client', false)) {

            $this->commands([
                WebHookOpenClientCommand::class,
            ]);
        }

        WebHook::observe(WebHookObserver::class);

        $this->commands([
            WebHoodAssociateCommand::class,
            OrganizerMakeCommand::class,
        ]);
    }
}

