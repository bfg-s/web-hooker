<?php

namespace Bfg\WebHooker\Traits;

use Bfg\WebHooker\Jobs\WebHookerSubscribeJob;
use Bfg\WebHooker\Jobs\WebHookerUnsubscribeJob;
use Bfg\WebHooker\Models\WebHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Bus\PendingDispatch;

/**
 * @property-read WebHook|null $webHook
 * @mixin Model
 */
trait WebHooked
{
    /**
     * @return MorphOne
     */
    public function webHook(): MorphOne
    {
        return $this->morphOne(WebHook::class, 'wh');
    }

    /**
     * @param  string  $event
     * @param  string|null  $organizer
     * @param  array  $settings
     * @return WebHook|Model
     */
    public static function assignBridgework(
        string $event,
        string|null $organizer = null,
        array $settings = [],
    ): WebHook|Model {
        return WebHook::query()->updateOrCreate([
            'wh_type' => static::class,
            'wh_id' => null,
        ], [
            'event' => $event,
            'organizer' => $organizer,
            'settings' => $settings,
        ]);
    }

    /**
     * @param  string  $event
     * @param  string|null  $organizer
     * @param  array  $settings
     * @return WebHook|Model
     */
    public function assignBridge(
        string $event,
        string|null $organizer = null,
        array $settings = [],
    ): WebHook|Model {
        return $this->webHook()->updateOrCreate([], [
            'event' => $event,
            'organizer' => $organizer,
            'settings' => $settings
        ]);
    }

    /**
     * @return bool|null
     */
    public function explodeBridge(): ?bool
    {
        return $this->webHook?->delete();
    }
}
