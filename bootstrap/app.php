<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ✅ PostTooLarge handle karo
        $middleware->replace(
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\ValidatePostSize::class
        );

        $middleware->alias([
            'auth'        => \App\Http\Middleware\Authenticate::class,
            'admin.auth'  => \App\Http\Middleware\AdminAuthenticated::class,
            'client.auth' => \App\Http\Middleware\ClientAuthenticated::class,
                'chatbot.enabled' => \App\Http\Middleware\EnsureChatbotEnabled::class,
                'whatsapp.enabled' => \App\Http\Middleware\EnsureWhatsappEnabled::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
