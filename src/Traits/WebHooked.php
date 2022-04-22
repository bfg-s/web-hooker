<?php

namespace Bfg\WebHooker\Traits;

use Bfg\WebHooker\Models\WebHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 */
class WebHooked
{
    /**
     * @return MorphOne
     */
    public function webHook(): MorphOne
    {
        return $this->morphOne(WebHook::class, 'wh', 'wh_type', 'wh_id', 'id');
    }
}
