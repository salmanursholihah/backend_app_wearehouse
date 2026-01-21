<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',

        // WAJIB supaya routes/api.php terbaca
        api: __DIR__.'/../routes/api.php',

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    // 👉 BAGIAN INI YANG MEMPERBAIKI ERROR "Target class [role] does not exist"
    ->withMiddleware(function (Middleware $middleware): void {

        // Alias middleware custom
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
