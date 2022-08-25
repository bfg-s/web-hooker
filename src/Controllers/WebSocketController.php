<?php

namespace Bfg\WebHooker\Controllers;

use BeyondCode\LaravelWebSockets\Apps\App;
use BeyondCode\LaravelWebSockets\QueryParameters;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\ConnectionsOverCapacity;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\UnknownAppKey;
use Bfg\WebHooker\Jobs\WebHookerEmitJob;
use Bfg\WebHooker\Models\WebHook;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class WebSocketController implements MessageComponentInterface
{
    /**
     * @var ChannelManager
     */
    protected ChannelManager $channelManager;

    /**
     * @param  ChannelManager  $channelManager
     */
    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    /**
     * @param  ConnectionInterface  $conn
     * @return void
     * @throws ConnectionsOverCapacity
     * @throws UnknownAppKey
     * @throws Exception
     */
    function onOpen(ConnectionInterface $conn): void
    {
        $this->verifyAppKey($conn)
            ->limitConcurrentConnections($conn)
            ->generateSocketId($conn);
    }

    /**
     * @param  ConnectionInterface  $conn
     * @return void
     */
    function onClose(ConnectionInterface $conn): void
    {
        $this->channelManager->removeFromAllChannels($conn);
    }

    /**
     * @param  ConnectionInterface  $conn
     * @param  Exception  $e
     * @return void
     */
    function onError(ConnectionInterface $conn, Exception $e): void
    {
        \Log::error($e);
    }

    /**
     * @param  ConnectionInterface  $conn
     * @param  MessageInterface  $msg
     * @return void
     * @throws UnknownAppKey
     */
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg): void
    {
        $hook = $this->getHook($conn);

        $payload = $msg->getPayload();

        if (
            (str_starts_with($payload, '{') && str_ends_with($payload, '}'))
            || (str_starts_with($payload, '[') && str_ends_with($payload, ']'))
        ) {
            $payload = json_decode($payload);
        }

        $hook->update([
            'response_at' => now()
        ]);

        $payload = (array) $payload;

        if (
            $hook->organizer
            && method_exists($hook->organizer, 'preparePayload')
        ) {
            $payload = $hook->organizer->preparePayload($payload);
        }

        WebHookerEmitJob::dispatch($hook, $payload);
    }

    /**
     * @param  ConnectionInterface  $conn
     * @return $this
     * @throws UnknownAppKey
     */
    protected function verifyAppKey(ConnectionInterface $conn): static
    {
        $appKey = QueryParameters::create($conn->httpRequest)->get('appKey');

        $this->getHook($conn);

        if (! $app = App::findByKey($appKey)) {
            throw new UnknownAppKey($appKey);
        }

        $conn->app = $app;

        return $this;
    }

    /**
     * @param  ConnectionInterface  $conn
     * @return WebHook
     * @throws UnknownAppKey
     */
    protected function getHook(ConnectionInterface $conn): WebHook
    {
        $clientKey = QueryParameters::create($conn->httpRequest)->get('clientKey');

        try {
            $id = Crypt::decrypt($clientKey);
        } catch (\Throwable) {
            throw new UnknownAppKey($clientKey);
        }

        $hook = WebHook::query()
            ->where('status', 1)
            ->where('type', 'websocket_open_signature')
            ->whereNotNull('event')
            ->find($id);

        if (! $hook) {
            throw new UnknownAppKey($clientKey);
        }

        return $hook;
    }

    /**
     * @param  ConnectionInterface  $connection
     * @return $this
     * @throws ConnectionsOverCapacity
     */
    protected function limitConcurrentConnections(ConnectionInterface $connection): static
    {
        if (! is_null($capacity = $connection->app->capacity)) {
            $connectionsCount = $this->channelManager->getConnectionCount($connection->app->id);
            if ($connectionsCount >= $capacity) {
                throw new ConnectionsOverCapacity();
            }
        }

        return $this;
    }

    /**
     * @param  ConnectionInterface  $connection
     * @return $this
     * @throws Exception
     */
    protected function generateSocketId(ConnectionInterface $connection): static
    {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));

        $connection->socketId = $socketId;

        return $this;
    }
}
