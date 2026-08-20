<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Clever Cloud termine le TLS en amont : sans cela, les URL generees
        // repartent en http:// et les navigateurs bloquent les ressources.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');
    }
}
