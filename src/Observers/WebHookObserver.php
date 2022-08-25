<?php

namespace Bfg\WebHooker\Observers;

use Bfg\WebHooker\Jobs\WebHookerUnsubscribeJob;
use Bfg\WebHooker\Models\WebHook;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Crypt;

class WebHookObserver
{
    /**
     * @param  WebHook  $hook
     * @return void
     */
    public function creating(WebHook $hook): void
    {
        if (! $hook->settings) {

            $hook->settings = [];
        }

        foreach ($hook::getDefaultSettings() as $key => $val) {

            if (! array_key_exists($key, $hook->settings)) {

                $hook->settings[$key] = $val;
            }
        }

        if (
            $hook->wh_type
            && class_exists($hook->wh_type)
            && method_exists($hook->wh_type, 'getDefaultSettings')
        ) {
            $modelSettings = (array) call_user_func([$hook->wh_type, 'getDefaultSettings']);

            foreach ($modelSettings as $key => $val) {

                if (! array_key_exists($key, $hook->settings)) {

                    $hook->settings[$key] = $val;
                }
            }
        }
    }

    /**
     * @param  WebHook  $hook
     * @return void
     */
    public function created(WebHook $hook): void
    {
        $hook->update([
            'hash' => Crypt::encrypt($hook->id)
        ]);
    }

    /**
     * @param  WebHook  $hook
     * @return void
     */
    public function deleting(WebHook $hook): void
    {
        if ($hook->type !== 'websocket_open_client') {

            app(Dispatcher::class)
                ->dispatchNow(new WebHookerUnsubscribeJob($hook, true));
        }
    }
}
