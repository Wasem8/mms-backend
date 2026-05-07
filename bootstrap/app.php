<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RoleMiddleware;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'auth' => Authenticate::class, // 🔥 override
            'role' => RoleMiddleware::class,
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

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
                'data' => null,
                'pagination' => null
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
                'data' => null,
                'pagination' => null
            ], 404);
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
