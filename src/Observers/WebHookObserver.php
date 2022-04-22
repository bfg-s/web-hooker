<?php

namespace Bfg\WebHooker\Observers;

use Bfg\WebHooker\Jobs\WebHookerAssociateJob;
use Bfg\WebHooker\Jobs\WebHookerSubscribeJob;
use Bfg\WebHooker\Jobs\WebHookerUnsubscribeJob;
use Bfg\WebHooker\Models\WebHook;
use Illuminate\Support\Facades\Crypt;

class WebHookObserver
{
    public function creating(WebHook $hook)
    {
        if (! $hook->settings) {

            $hook->settings = [];
        }

        if (! $hook->response) {

            $hook->response = [];
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

    public function created(WebHook $hook)
    {
        $hook->hash = Crypt::encrypt($hook->id);

        WebHookerSubscribeJob::dispatch($hook);
    }

    public function updated(WebHook $hook)
    {
        WebHookerAssociateJob::dispatch($hook);
    }

    public function deleted(WebHook $hook)
    {
        WebHookerUnsubscribeJob::dispatch($hook);
    }
}
