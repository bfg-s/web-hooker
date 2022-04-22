<?php

namespace Bfg\WebHooker\Controllers;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WebHookerController
{
    public function response(string $hash, Request $request)
    {
        try {
            $id = Crypt::decrypt($hash);
        } catch (\Throwable) {
            abort(404);
        }

        /** @var WebHook $hook */
        $hook = WebHook::query()
            ->findOrFail($id);

        $hook->update([
            'response' => $request->all(),
            'response_at' => now()
        ]);
    }
}
