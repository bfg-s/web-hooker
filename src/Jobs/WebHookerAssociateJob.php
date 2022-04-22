<?php

namespace Bfg\WebHooker\Jobs;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebHookerAssociateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  WebHook|null  $hook
     */
    public function __construct(
        public ?WebHook $hook = null
    ) {
        $this->queue = config('webhooker.queue');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->hook) {

            $this->applyHook($this->hook);

        } else {

            $hooksSubscribe = WebHook::query()
                ->where('subscribe_at', '<=', now())
                ->get();

            $hooksUnsubscribe = WebHook::query()
                ->where('unsubscribe_at', '<=', now())
                ->get();

            foreach ($hooksSubscribe as $hook) {

                $this->applyHook($hook);
            }

            foreach ($hooksUnsubscribe as $hook) {

                $this->applyHook($hook);
            }
        }
    }

    /**
     * @param  WebHook  $hook
     * @return void
     */
    protected function applyHook(WebHook $hook)
    {
        if (
            $hook->status
            && $hook->unsubscribe_at
            && $hook->unsubscribe_at <= now()
        ) {
            WebHookerUnsubscribeJob::dispatch($hook);
        }

        if (
            ! $hook->status
            && $hook->subscribe_at
            && $hook->subscribe_at <= now()
        ) {
            WebHookerSubscribeJob::dispatch($hook);
        }
    }
}
