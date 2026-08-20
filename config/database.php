<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        // Sur Clever Cloud, l'add-on PostgreSQL injecte lui-meme POSTGRESQL_ADDON_*.
        // On les lit en repli : aucune variable DB_* n'est alors a saisir a la main,
        // et il n'y a plus de risque de recopier un mot de passe de travers.
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL', env('POSTGRESQL_ADDON_URI')),
            'host' => env('DB_HOST', env('POSTGRESQL_ADDON_HOST', '127.0.0.1')),
            'port' => env('DB_PORT', env('POSTGRESQL_ADDON_PORT', '5432')),
            'database' => env('DB_DATABASE', env('POSTGRESQL_ADDON_DB', 'garpis')),
            'username' => env('DB_USERNAME', env('POSTGRESQL_ADDON_USER', 'garpis')),
            'password' => env('DB_PASSWORD', env('POSTGRESQL_ADDON_PASSWORD', '')),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'garpis'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('garpis.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ],

    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],

    'redis' => [
        'client' => 'phpredis',
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];
