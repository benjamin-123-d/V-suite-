<?php

use App\Http\Middleware\ExigeRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Clever Cloud place un reverse proxy devant l'application et y termine
        // le TLS. Sans cela, Laravel croit parler en http : les cookies « secure »
        // ne partent pas et l'adresse IP journalisee est celle du proxy.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_AWS_ELB);

        $middleware->alias([
            'role' => ExigeRole::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('connexion'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
