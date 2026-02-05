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
    ->withMiddleware(function (Middleware $middleware): void {
        // 未登入時導向 Ocadmin 登入頁
        $middleware->redirectGuestsTo(fn () => '/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
