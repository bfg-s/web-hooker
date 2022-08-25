<?php

namespace Bfg\WebHooker\Controllers;

use Bfg\WebHooker\Jobs\WebHookerEmitJob;
use Bfg\WebHooker\Models\WebHook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WebHookerController
{
    public function response(string $hash, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $id = Crypt::decrypt($hash);
        } catch (\Throwable) {
            abort(404);
        }

        /** @var WebHook $hook */
        $hook = WebHook::query()
            ->where('status', 1)
            ->where('type', 'http_request')
            ->findOrFail($id);

        $hook->update([
            'response_at' => now()
        ]);

        $payload = $request->all();

        if (
            $hook->organizer
            && method_exists($hook->organizer, 'preparePayload')
        ) {
            $payload = $hook->organizer->preparePayload($payload);
        }

        WebHookerEmitJob::dispatch($hook, $payload);

        return response()->json([
            'status' => true
        ]);
    }
}
