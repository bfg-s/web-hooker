# Extension web-hooker

## Install
```bash
composer require bfg/web-hooker
```

## Description
Compact Laravel WebHook core for simple of begin

## Usage

> Importantly! You should have queues for processing hooks.

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

namespace App\Services;

use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\WebHookOrganizerInterface;

class WebHookOrganizer implements WebHookOrganizerInterface
{
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

You can now create bridges for some separate entry in the database or model.
```php
$webhook = \App\Models\User::first()->assignBridge(
    event: YouEvent::class,
    organizer: YouOrganizer::class,
    settings: []
): \Bfg\WebHooker\Models\WebHook;
```
or
```php
$webhook = \App\Models\User::assignBridgework(
    event: YouEvent::class,
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

Use the `subscribeDelay` method for delayed subscribe:
```php
/** @var \Bfg\WebHooker\Models\WebHook $webhook */
$webhook->subscribeDelay(
    now()->addHour()
);
```
But for this you need to add to your schedule of association:
```php
$schedule->command('webhook:associate')->everyMinute();
```

