<?php

namespace Bfg\WebHooker;

use Bfg\WebHooker\Models\WebHook;

abstract class WebHookOrganizerAbstract implements WebHookOrganizerInterface
{
    /**
     * The websocket host for connection
     * @param  WebHook  $hook
     * @return string
     */
    abstract function host(WebHook $hook): string;

    /**
     * Method for remote subscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function subscribe(WebHook $hook): bool
    {
        return ! $hook->subscribe_at
            || $hook->subscribe_at >= now();
    }

    /**
     * Method for remote unsubscribe
     *
     * @param  WebHook  $hook
     * @return bool
     */
    public function unsubscribe(WebHook $hook): bool
    {
        return $hook->unsubscribe_at
            && $hook->unsubscribe_at <= now();
    }

    /**
     * Send a message when creating a connection with the server
     *
     * @param  WebHook  $hook
     * @return array
     */
    public function onConnectMessage(WebHook $hook): array
    {
        return [];
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
     * Send message when disconnect from server
     *
     * @param  WebHook  $hook
     * @return array
     */
    public function onDisconnectMessage(WebHook $hook): array
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
}
