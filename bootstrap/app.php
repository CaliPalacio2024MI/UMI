<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException; 
use Illuminate\Http\Request; 

 return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'ajax' => \App\Http\Middleware\AjaxMiddleware::class,
            'spa' => \App\Http\Middleware\SpaResponseMiddleware::class,
        ]);
        
        
        // Aplicar middleware AJAX a rutas web
        // $middleware->web(append: [
        //     \App\Http\Middleware\HandleAjaxRequests::class,
        // ]);    
    })
    ->withProviders([
        \App\Providers\ViewServiceProvider::class, 
    ])

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            return redirect()->back()->withErrors([
                'limite' => 'Has enviado el formulario demasiadas veces. Espera 15 minutos e intenta de nuevo.'
            ]);
        });
    })->create();
