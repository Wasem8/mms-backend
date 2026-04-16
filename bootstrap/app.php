<?php

use App\Http\Middleware\Authenticate;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class, // 🔥 override
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 🔐 Unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            return ApiResponse::error('Unauthenticated.', 401);
        });

        // 🚫 Rate limit
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            return ApiResponse::error('Too many attempts. Please try again later.', 429);
        });

        // ⚠️ Validation
        $exceptions->render(function (ValidationException $e, $request) {
            return ApiResponse::error(
                'Validation error.',
                422,
                $e->errors()
            );
        });

    })->create();
