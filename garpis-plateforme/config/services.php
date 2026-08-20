<?php

return [
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('GARPIS_IA_MODELE', 'claude-sonnet-4-6'),
        'endpoint' => 'https://api.anthropic.com/v1/messages',
        'version' => '2023-06-01',
    ],
];
