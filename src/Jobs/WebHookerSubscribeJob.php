<?php

namespace Bfg\WebHooker\Jobs;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebHookerSubscribeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  WebHook  $hook
     */
    public function __construct(
        public WebHook $hook
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
            ! $this->hook->status
            && (! $this->hook->subscribe_at || $this->hook->subscribe_at <= now())
            && $this->hook->organizer->subscribe($this->hook)
        ) {
            $this->hook->update([
                'subscribed_at' => now(),
                'status' => 1
            ]);
        }
    }
}
