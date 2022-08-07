<?php
namespace Bfg\WebHooker\Commands;

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
            ->where('status', 1)
            ->get();

        /** @var WebHook $item */
        foreach ($unsubscribeList as $item) {
            $item->unsubscribe();
        }

        $subscribeList = WebHook::query()
            ->where('subscribe_at', '<=', now())
            ->where('status', 0)
            ->get();

        /** @var WebHook $item */
        foreach ($subscribeList as $item) {
            $item->subscribe();
        }

        return 0;
    }
}
