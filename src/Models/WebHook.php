<?php

namespace Bfg\WebHooker\Models;

use Bfg\WebHooker\Traits\WebHooked;
use Bfg\WebHooker\WebHookOrganizerAbstract;
use Bfg\WebHooker\WebHookOrganizerInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property string $type
 * @property string $wh_type
 * @property int $wh_id
 * @property WebHookOrganizerInterface|WebHookOrganizerAbstract|null $organizer
 * @property string|null $event
 * @property array $settings
 * @property string $hash
 * @property int $status
 * @property array $response
 * @property Carbon|null $response_at
 * @property Carbon|null $subscribe_at
 * @property Carbon|null $subscribed_at
 * @property Carbon|null $unsubscribe_at
 * @property Carbon|null $unsubscribed_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read string $route_response
 * @property-read Model|WebHooked $model
 */
class WebHook extends Model
{
    /**
     * @var string
     */
    protected $table = "webhooks";

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'wh_type',
        'wh_id',
        'organizer',
        'settings',
        'hash',
        'status',
        'response_at',
        'subscribe_at',
        'subscribed_at',
        'unsubscribe_at',
        'unsubscribed_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'type' => 'string',
        'wh_type' => 'string',
        'wh_id' => 'integer',
        'organizer' => 'string',
        'settings' => 'array',
        'hash' => 'string',
        'status' => 'int',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'response_at',
        'subscribe_at',
        'subscribed_at',
        'unsubscribe_at',
        'unsubscribed_at'
    ];

    /**
     * @var WebHookOrganizerInterface|WebHookOrganizerAbstract
     */
    protected $origanizerCache = null;

    /**
     * @var array
     */
    protected static array $defaultSettings = [];

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo('wh');
    }

    /**
     * @param $value
     * @return WebHookOrganizerInterface|WebHookOrganizerAbstract|null
     */
    public function getOrganizerAttribute($value): ?WebHookOrganizerInterface
    {
        return $this->origanizerCache = $this->origanizerCache
            ?: ($value ? app($value, ['hook' => $this, 'webHook' => $this]) : null);
    }

    /**
     * @return string
     */
    public function getRouteResponseAttribute(): string
    {
        return route('webhook.response', $this->hash);
    }

    /**
     * @return string|null
     */
    public function getEventAttribute(): ?string
    {
        return $this->organizer?->event($this);
    }

    /**
     * @param  array  $settings
     * @return void
     */
    public static function setDefaultSettings(array $settings): void
    {
        static::$defaultSettings = $settings;
    }

    /**
     * @return array
     */
    public static function getDefaultSettings(): array
    {
        return static::$defaultSettings;
    }

    /**
     * @param  array  $settings
     * @return $this
     */
    public function setSettings(array $settings): static
    {
        $this->update([
            'settings' => array_merge($this->settings, $settings)
        ]);

        return $this;
    }

    /**
     * @param  string  $key
     * @param  mixed|null  $value
     * @return $this
     */
    public function setSetting(string $key, mixed $value = null): static
    {
        return $this->setSettings([$key => $value]);
    }

    /**
     * @param  string  $class
     * @return $this
     */
    public function setOrganizer(string $class): static
    {
        $this->update([
            'organizer' => $class
        ]);

        return $this;
    }

    public function setType(string $type): static
    {
        $this->update(compact('type'));

        return $this;
    }

    public function setTypeHttpRequest(): static
    {
        return $this->setType('http_request');
    }

    public function setTypeWebsocketOpenSignature(): static
    {
        return $this->setType('websocket_open_signature');
    }

    public function setTypeWebsocketOpenClient(): static
    {
        return $this->setType('websocket_open_client');
    }

    /**
     * @param  Carbon  $datetime
     * @return $this
     */
    public function subscribeDelay(Carbon $datetime): static
    {
        $this->update([
            'subscribe_at' => $datetime
        ]);

        return $this;
    }

    /**
     * @param  Carbon  $datetime
     * @return $this
     */
    public function unsubscribeDelay(Carbon $datetime): static
    {
        $this->update([
            'unsubscribe_at' => $datetime
        ]);

        return $this;
    }
}
