<?php

namespace Bfg\WebHooker;

use Bfg\WebHooker\Models\WebHook;

interface WebHookOrganizerInterface
{
    /**
     * Generate the event for hook emit
     * @param  WebHook  $hook
     * @return string
     */
    function event(WebHook $hook): string;

    /**
     * Method for remote subscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function subscribe(WebHook $hook): bool;

    /**
     * Method for remote unsubscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function unsubscribe(WebHook $hook): bool;
}
