<?php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /** @var array global middleware */
    protected $middleware = [
        // Confianza proxies
        \Illuminate\Http\Middleware\TrustProxies::class,
        // *** CORS nativo ***
        \Illuminate\Http\Middleware\HandleCors::class,
        // Mantenimiento
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    ];

    /** @var array middleware groups */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
}