<?php

namespace Bfg\WebHooker;

use Bfg\WebHooker\Models\WebHook;

interface WebHookOrganizerInterface
{
    /**
     * Generate the event for hook emit
     * 
     * @param  WebHook  $hook
     * @return string
     */
    function event(WebHook $hook): string;

    /**
     * Method for remote subscribe
     *
     * To return the truth if the subscription was successful,
     * otherwise there will be a repeated request for the next iteration.
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function subscribe(WebHook $hook): bool;

    /**
     * Method for remote unsubscribe
     *
     * To return the truth if the unsubscription was successful,
     * otherwise there will be a repeated request for the next iteration.
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function unsubscribe(WebHook $hook): bool;
}
