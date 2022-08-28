<?php
namespace Bfg\WebHooker\Commands;

use Bfg\WebHooker\Jobs\WebHookerEmitJob;
use Bfg\WebHooker\Models\WebHook;
use Bfg\WebHooker\WebHookOrganizerAbstract;
use Exception;
use Illuminate\Console\Command;
use Workerman\Timer;
use Workerman\Connection\AsyncTcpConnection;

class WebHookOpenClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:open-client
    {--t|timeout=5 : Update list timeout interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The webhook client based on websockets';

    /**
     * @var array
     */
    protected array $connections = [];

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $worker = new HookWorker();

        $worker->onWorkerStart = [$this, 'workerStart'];

        HookWorker::runAll();

        return 0;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function workerStart(): void
    {
        WebHook::where('type', 'websocket_open_client')
            ->whereNotNull('organizer')
            ->update(['status' => 0]);

        $this->applyConnectionList();

        Timer::add(
            (int) $this->option('timeout'),
            [$this, 'applyConnectionList']
        );

        Timer::add(
            (int) $this->option('timeout'),
            [$this, 'overdueHooks']
        );
    }

    /**
     * @throws Exception
     */
    public function applyConnectionList()
    {
        $hooks = WebHook::where('type', 'websocket_open_client')
            ->whereNotNull('organizer')
            ->where('status', 0)
            ->get();

        /** @var WebHook $hook */
        foreach ($hooks as $hook) {
            $hook->update(['status' => 2]);
            $organizer = $hook->organizer;
            if ($organizer instanceof WebHookOrganizerAbstract) {
                if ($organizer->subscribe($hook)) {
                    if (! $organizer->unsubscribe($hook)) {
                        $host = $organizer->host($hook);
                        $ssl = str_starts_with($host, 'wss://');
                        $host = str_replace('wss://', 'ws://', $host);
                        if (isset($this->connections[$host])) {
                            $ws_connection = $this->connections[$host]['connection'];
                            $this->connections[$host]['models'][$hook->id] = $hook;

                            $hook->update([
                                'status' => 1,
                                'subscribed_at' => now()
                            ]);

                            $this->info("Hook: {$hook->id}, added");

                            if ($message = $organizer->onAddMessage($hook)) {

                                $ws_connection->send(json_encode($message));
                            }

                        } else {
                            $ws_connection = new AsyncTcpConnection($host);

                            $this->connections[$host] = [
                                'connection' => $ws_connection,
                                'models' => [$hook->id => $hook],
                            ];

                            $ws_connection->transport = $ssl ? 'ssl' : 'tcp';

                            $ws_connection->onConnect = fn ($conn)
                            => $this->onConnect($this->connections[$host], $conn);

                            $ws_connection->onMessage = fn ($conn, $data)
                            => $this->onMessage($this->connections[$host], $conn, $data);

                            $ws_connection->onError = fn ($conn, $code, $msg)
                            => $this->onError($this->connections[$host], $conn, $code, $msg);

                            $ws_connection->onClose = fn ($conn)
                            => $this->onClose($this->connections[$host], $conn);

                            $ws_connection->connect();

                            $hook->update([
                                'status' => 1,
                                'subscribed_at' => now()
                            ]);
                            $this->info("Hook: {$hook->id}, opened");
                        }
                    } else {
                        $hook->update([
                            'status' => 0,
                            'unsubscribed_at' => now()
                        ]);
                    }
                }
            }
        }
    }

    public function onConnect(array $detail, $connection)
    {
        $this->info('Hook: open connection');
        /** @var WebHook $hook */
        foreach ($detail['models'] as $hook) {
            $organizer = $hook->organizer;
            if ($message = $organizer->onConnectMessage($hook)) {
                $connection->send(json_encode($message));
            }
            if ($message = $organizer->onAddMessage($hook)) {
                $connection->send(json_encode($message));
            }
        }
        //$connection->send('Hello');
        //echo "Open: \n";
    }

    public function onMessage(array $detail, $connection, $payload) {
        /** @var WebHook $hook */
        foreach ($detail['models'] as $hook) {
            if (is_string($payload)) {
                $organizer = $hook->organizer;

                $hook->update([
                    'response_at' => now()
                ]);

                if (
                    (str_starts_with($payload, '{') && str_ends_with($payload, '}'))
                    || (str_starts_with($payload, '[') && str_ends_with($payload, ']'))
                ) {
                    $payload = json_decode($payload);
                }

                $payload = (array) $payload;

                if ($organizer->isSelfMessage($hook, $payload)) {

                    if (
                        $hook->organizer
                        && method_exists($hook->organizer, 'preparePayload')
                    ) {
                        $payload = $hook->organizer->preparePayload($hook, $payload);
                    }

                    if ($payload) {

                        WebHookerEmitJob::dispatch($hook, $payload);
                    }
                }
            }
        }
    }

    public function onError(array $detail, $connection, $code, $msg) {

        /** @var WebHook $hook */
        foreach ($detail['models'] as $hook) {
            $hook->update([
                'status' => 0,
                'unsubscribed_at' => now()
            ]);
        }

        $this->error("Hook: WebSocket has error - " . $msg);

        \Log::error($msg);

        die(2);
    }

    public function onClose (array $detail, $connection) {

        /** @var WebHook $hook */
        foreach ($detail['models'] as $hook) {
            $hook->update([
                'status' => 0,
                'unsubscribed_at' => now()
            ]);
        }

        die(3);
    }

    public function overdueHooks()
    {
        foreach ($this->connections as $key => $detail) {
            /** @var WebHook $hook */
            foreach ($detail['models'] as $hookKey => $hook) {
                $hook = WebHook::find($hook->id);
                $this->connections[$key]['models'][$hookKey] = $hook;
                $organizer = $hook->organizer;
                if ($organizer->unsubscribe($hook)) {
                    $hook->update([
                        'status' => 0,
                        'unsubscribed_at' => now(),
                    ]);
                    if ($message = $organizer->onDisconnectMessage($hook)) {
                        $detail['connection']->send(json_encode($message));
                    }
                    unset($this->connections[$key]['models'][$hookKey]);
                    $this->info("Hook: {$hook->id}, closed");
                }
            }
            if (! count($detail['models'])) {
                $detail['connection']->destroy();
                unset($this->connections[$key]);
                $this->info("Hook: closed connection");
            }
        }
    }
}
