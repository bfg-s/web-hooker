<?php

namespace Bfg\WebHooker\Traits;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read WebHook|null $webHook
 * @mixin Model
 */
trait WebHooked
{
    /**
     * @return MorphMany
     */
    public function webHooks(): MorphMany
    {
        return $this->morphMany(WebHook::class, 'wh');
    }

    /**
     * @param  string  $organizer
     * @param  array  $settings
     * @return WebHook|Model
     */
    public static function assignBridgework(
        string $organizer,
        array $settings = [],
    ): WebHook|Model {
        return WebHook::query()->updateOrCreate([
            'wh_type' => static::class,
            'wh_id' => null,
        ], [
            'organizer' => $organizer,
            'settings' => $settings,
        ]);
    }

    /**
     * @param  string  $organizer
     * @param  array  $settings
     * @return WebHook|Model
     */
    public function assignBridge(
        string $organizer,
        array $settings = [],
    ): WebHook|Model {
        return $this->webHooks()->updateOrCreate([], [
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
