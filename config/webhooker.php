<?php

return [
    'type' => [
        'http_request' => true,
        'websocket_open_signature' => false,
        'websocket_open_client' => false,
    ],
    'routes' => [
        'middleware' => ['web'],
        'prefix' => 'webhook',
    ],
    'queue' => null,
    'websockets' => [
        'open_signature' => false,
    ],
];
