<?php

namespace Bfg\WebHooker\Jobs;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebHookerUnsubscribeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  WebHook  $hook
     * @param  bool  $deleting
     */
    public function __construct(
        public WebHook $hook,
        public bool $deleting = false,
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
        if (
            $this->hook->status
            && (! $this->hook->unsubscribe_at || $this->hook->unsubscribe_at <= now())
            && (! $this->hook->organizer || $this->hook->organizer?->unsubscribe($this->hook))
        ) {
            ! $this->deleting && $this->hook->update([
                'unsubscribed_at' => now(),
                'unsubscribe_at' => null,
                'status' => 0
            ]);
        }
    }
}
