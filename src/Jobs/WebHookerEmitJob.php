<?php

namespace Bfg\WebHooker\Jobs;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebHookerEmitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  WebHook  $hook
     * @param  array  $request
     */
    public function __construct(
        public WebHook $hook,
        public array $request,
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
        event(
            app($this->hook->event, [
                'hook' => $this->hook,
                'request' => $this->request
            ])
        );
    }
}
