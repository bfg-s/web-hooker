<?php

namespace Bfg\WebHooker\Models;

use Bfg\WebHooker\WebHookOrganizerAbstract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property string $wh_type
 * @property int $wh_id
 * @property WebHookOrganizerAbstract|null $organizer
 * @property mixed $event
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
        'wh_type',
        'wh_id',
        'organizer',
        'event',
        'settings',
        'hash',
        'status',
        'response',
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
        'wh_type' => 'string',
        'wh_id' => 'integer',
        'organizer' => 'string',
        'event' => 'string',
        'settings' => 'array',
        'hash' => 'string',
        'status' => 'int',
        'response' => 'json',
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
     * @var array
     */
    protected static array $defaultSettings = [];

    /**
     * @param $value
     * @return WebHookOrganizerAbstract|null
     */
    public function getOrganizerAttribute($value): ?WebHookOrganizerAbstract
    {
        return $value ? app($value) : null;
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public function getEventAttribute($value): mixed
    {
        return $value ? app($value) : null;
    }

    public function getRouteResponseAttribute()
    {
        return route('webhook.response', $this->hash);
    }

    /**
     * @param  array  $settings
     * @return void
     */
    public static function setDefaultSettings(array $settings)
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
}
