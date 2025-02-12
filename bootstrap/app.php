<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([\App\Http\Middleware\HandleInertiaRequests::class]);
        $middleware->api([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, \App\Http\Middleware\AddUserInfo::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
