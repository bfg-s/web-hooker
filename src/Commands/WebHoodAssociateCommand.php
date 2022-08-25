<?php
namespace Bfg\WebHooker\Commands;

use Bfg\WebHooker\Jobs\WebHookerSubscribeJob;
use Bfg\WebHooker\Jobs\WebHookerUnsubscribeJob;
use Bfg\WebHooker\Models\WebHook;
use Illuminate\Console\Command;

class WebHoodAssociateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:associate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The associate all webhooks statuses with timestamps';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $unsubscribeList = WebHook::query()
            ->where('unsubscribe_at', '<=', now())
            ->where('type', '!=', 'websocket_open_client')
            ->where('status', 1)
            ->get();

        /** @var WebHook $item */
        foreach ($unsubscribeList as $item) {

            WebHookerUnsubscribeJob::dispatch($item);
        }

        $subscribeList = WebHook::query()
            ->where('subscribe_at', '<=', now())
            ->where('type', '!=', 'websocket_open_client')
            ->where('status', 0)
            ->get();

        /** @var WebHook $item */
        foreach ($subscribeList as $item) {

            WebHookerSubscribeJob::dispatch($item);
        }

        $subscribeNullList = WebHook::query()
            ->whereNull('subscribe_at')
            ->where('status', 0)
            ->where('type', '!=', 'websocket_open_client')
            ->where('created_at', '<=', now()->subSeconds(5))
            ->get();

        /** @var WebHook $item */
        foreach ($subscribeNullList as $item) {

            WebHookerSubscribeJob::dispatch($item);
        }

        return 0;
    }
}
