<?php

use Monolog\Handler\StreamHandler;

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'deprecations' => ['channel' => null, 'trace' => false],
    'channels' => [
        'stack' => ['driver' => 'stack', 'channels' => ['stderr'], 'ignore_exceptions' => false],
        'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => env('LOG_LEVEL', 'warning')],
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'warning'),
            'handler' => StreamHandler::class,
            'with' => ['stream' => 'php://stderr'],
        ],
    ],
];
