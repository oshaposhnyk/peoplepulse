<?php

use App\Http\Middleware\HandleAppearance;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Map domain exceptions to HTTP status codes
        $exceptions->renderable(function (\Domain\Shared\Exceptions\DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'BUSINESS_RULE_VIOLATION',
                    'message' => $e->getMessage(),
                ],
            ], 422);
        });

        $exceptions->renderable(function (\Application\Exceptions\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'errors' => $e->getErrors(),
                ],
            ], 422);
        });

        $exceptions->renderable(function (\Application\Exceptions\NotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => $e->getMessage(),
                ],
            ], 404);
        });

        $exceptions->renderable(function (\Application\Exceptions\UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => $e->getMessage(),
                ],
            ], 403);
        });
    })->create();
