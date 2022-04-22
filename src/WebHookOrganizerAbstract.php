<?php

namespace Bfg\WebHooker;

use Bfg\WebHooker\Models\WebHook;

abstract class WebHookOrganizerAbstract
{
    /**
     * Method for remote subscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    abstract public function subscribe(WebHook $hook): bool;

    /**
     * Method for remote unsubscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    abstract public function unsubscribe(WebHook $hook): bool;
}
