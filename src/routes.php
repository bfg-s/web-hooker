<?php

use Illuminate\Support\Facades\Route;

Route::get('response/{hash}', [\Bfg\WebHooker\Controllers\WebHookerController::class, 'response'])
    ->name('response');
