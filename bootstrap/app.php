<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle Method Not Allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? null
                ], 405);
            }
        });

        // Handle Database Connection Errors
        $exceptions->render(function (\PDOException $e, $request) {
            if ($request->is('api/*') && str_contains($e->getMessage(), 'could not connect to server')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection failed',
                    'data' => config('app.debug') ? $e->getMessage() : 'Server error'
                ], 503);
            }
        });
    })->create();
