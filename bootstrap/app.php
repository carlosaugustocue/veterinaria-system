<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ AGREGAR ESTA LÍNEA - Registrar el middleware de roles
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // También puedes agregar middleware global si es necesario
        // $middleware->append(\App\Http\Middleware\SomeGlobalMiddleware::class);
        
        // O middleware para grupos específicos
        // $middleware->appendToGroup('api', [
        //     \App\Http\Middleware\SomeApiMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();