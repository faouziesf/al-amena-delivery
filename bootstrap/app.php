<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // SUPPRIMEZ cette section complètement :
    // ->withCommands([
    //     \App\Console\Kernel::class,
    // ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Middleware groups for specific roles
        $middleware->group('client', [
            'auth:sanctum',
            \App\Http\Middleware\CheckRole::class . ':CLIENT',
        ]);

        $middleware->group('deliverer', [
            'auth:sanctum',
            \App\Http\Middleware\CheckRole::class . ':DELIVERER',
        ]);

        $middleware->group('commercial', [
            'auth:sanctum',
            \App\Http\Middleware\CheckRole::class . ':COMMERCIAL',
        ]);

        $middleware->group('supervisor', [
            'auth:sanctum',
            \App\Http\Middleware\CheckRole::class . ':SUPERVISOR',
        ]);

        $middleware->group('staff', [
            'auth:sanctum',
            \App\Http\Middleware\CheckRole::class . ':COMMERCIAL,SUPERVISOR',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();