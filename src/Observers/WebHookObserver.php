<?php

namespace Bfg\WebHooker\Observers;

use Bfg\WebHooker\Models\WebHook;
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
        $hook->hash = Crypt::encrypt($hook->id);

        $hook->save();
    }

    /**
     * @param  WebHook  $hook
     * @return void
     */
    public function deleting(WebHook $hook): void
    {
        $hook->organizer?->unsubscribe($hook);
    }
}
