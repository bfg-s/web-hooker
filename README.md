# Extension web-hooker

## Install
```bash
composer require bfg/web-hooker
```

## Description
Compact Laravel WebHook core for simple of begin

## Usage

> Importantly! You should have queues for processing hooks.

### Publish
Migrations
```cli
php artisan vendor:publish --tag=web-hooker-migrations
```
Configs
```cli
php artisan vendor:publish --tag=web-hooker-config
```
### Migrate
A mandatory stage is to create a table in the database, 
so after the publication of migration, will launch them.
```cli
php artisan migrate
```

### Http request type

> You need to make sure that the `type.http_request` setting is included in the `config/webhooker.php` file in the `true` value.

Before usage, you should use the trait on your model there you want to use for hooks:
```php
<?php

namespace App\Models;

use Bfg\WebHooker\Traits\WebHooked;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use WebHooked;
}
```

Create your own event, which is a standard Laravel functionality.
```bash
php artisan make:event YouEvent
```

Create the organizer class by using the `Bfg\WebHooker\WebHookOrganizerInterface` interface:
```php
<?php

namespace App\WebHook\Organizers;

use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\WebHookOrganizerInterface;

class YouOrganizer implements WebHookOrganizerInterface
{
    /**
     * Generate the event for hook emit
     * @param  WebHook  $hook
     * @return string
     */
    function event(WebHook $hook): string
    {
        return YouEvent::class;
    }

    /**
     * Method for remote subscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function subscribe(WebHook $hook): bool
    {
        // Request to subscribe
        // Link for request: $hook->route_response
        return true;
    }

    /**
     * Method for remote unsubscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function unsubscribe(WebHook $hook): bool
    {
        // Request to unsubscribe
        return true;
    }
}
```
Or you can use the command for create a organizer:
```cli
php artisan make:organizer YouOrganizer
```

For get a request link for hook, you can use the `$hook->route_response` parameter.

You can now create bridges for some separate entry in the database or model.
```php
$webhook = \App\Models\User::first()->assignBridge(
    organizer: YouOrganizer::class,
    settings: []
): \Bfg\WebHooker\Models\WebHook;
```
or
```php
$webhook = \App\Models\User::assignBridgework(
    organizer: YouOrganizer::class,
    settings: []
): \Bfg\WebHooker\Models\WebHook;
```
Next you get a hook model that must be subscribed 
for data (this may be API Request for example).
You can use immediate subscription or delay.

For the immediate launch of the signature procedure, 
you can use the `subscribe` method:

```php
/** @var \Bfg\WebHooker\Models\WebHook $webhook */
$webhook->subscribe();
```
This method is attached to the job that does the signature.

And you can delay needs to subscribe:
```php
/** @var \Bfg\WebHooker\Models\WebHook $webhook */
$webhook->subscribeDelay(
    now()->addHour()
);
```
And you can add datetime when needs to unsubscribe:
```php
/** @var \Bfg\WebHooker\Models\WebHook $webhook */
$webhook->unsubscribeDelay(
    now()->addHours(2)
);
```
But for this you need to add to your schedule of association:
```php
$schedule->command('webhook:associate')->everyMinute();
```






### Open signature type

> You need to make sure that the `type.websocket_open_signature` setting is included in the `config/webhooker.php` file in the `true` value.

If you install a `beyondcode/laravel-websockets` package, 
you have the opportunity to create fast hooks that 
can take data on the socket.
What you need to use this type:

```cli
composer require beyondcode/laravel-websockets
```

Create the organizer:
```cli
php artisan make:organizer YouOrganizer
```

Create the special bridge:
```php
$webhook = \App\Models\User::assignBridgework(
    organizer: YouOrganizer::class,
    settings: []
)->setTypeWebsocketOpenSignature(): \Bfg\WebHooker\Models\WebHook;
```

Server:
```cli
php artisan websockets:serve
```
Client:
```javascript
const hookSocket = new WebSocket("ws://0.0.0.0:6001/hook/{PUSHER_APP_KEY}/{WEBHOOK_HASH}");
hookSocket.send('Any data');
```




### Open client type

> You need to make sure that the `type.websocket_open_client` setting is included in the `config/webhooker.php` file in the `true` value.

This type is designed as a client that creates local 
client connections with asynchronous TCPs (web socket servers).

Create the organizer:
```cli
php artisan make:organizer BinanceOrganizer --client
```

Carefully study how the organizer is built, 
without it the client’s server will ignore this hook

```php
<?php

namespace App\WebHook\Organizers;

use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\WebHookOrganizerAbstract;

class BinanceOrganizer extends WebHookOrganizerAbstract
{
    /**
     * Generate the event for hook emit
     * @param  WebHook  $hook
     * @return string
     */
    function event(WebHook $hook): string
    {
        return YouEvent::class;
    }

    /**
     * The websocket host for connection
     * @param  WebHook  $hook
     * @return string
     */
    function host(WebHook $hook): string
    {
        return "wss://stream.binance.com:9443/ws/btcusdt@depth";
    }
}
```
After the link to the server is indicated, 
you can customize your organizer a little:
```php
...
    /**
     * Send a message when creating the first connection
     *
     * @param  WebHook  $hook
     * @return array
     */
    public function onConnectMessage(WebHook $hook): array
    {
        return [
            'method' => 'SUBSCRIBE',
            'params' => [
                'btcusdt@aggTrade',
                'btcusdt@depth'
            ],
            'id' => $hook->id
        ];
    }
    
    /**
     * Send message when disconnect from server
     *
     * @param  WebHook  $hook
     * @return array
     */
    public function onDisconnectMessage(WebHook $hook): array
    {
        return [
            'method' => 'UNSUBSCRIBE',
            'params' => [
                'btcusdt@aggTrade',
                'btcusdt@depth'
            ],
            'id' => $hook->id
        ];
    }
    
    /**
     * Send message when add hook to client’s server
     *
     * @param  WebHook  $hook
     * @return array
     */
    public function onAddMessage(WebHook $hook): array
    {
        return [];
    }
    
    /**
     * Checks an incoming message for authenticity for this hook
     *
     * @param  WebHook  $hook
     * @param  array  $payload
     * @return bool
     */
    public function isSelfMessage(WebHook $hook, array $payload): bool
    {
        return true;
    }
``` 
In order to prepare your payload, you can declare the `preparePayload` method in your organizer:
```php
...    
    public function preparePayload(array $payload): array
    {
        return $payload;
    }
...
```


Create the special bridge:
```php
$webhook = \App\Models\User::assignBridgework(
    organizer: BinanceOrganizer::class,
    settings: []
)->setTypeWebsocketOpenClient(): \Bfg\WebHooker\Models\WebHook;
```

Run the server of client connection
```cli
php artisan webhook:open-client
```
Supervisor
```cfg
[program:webhook-open-client]
directory=/path/to/you/project
command=php artisan webhook:open-client
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/you/project/webhook-open-client.log
```
