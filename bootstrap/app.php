<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - apply security headers to all requests
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // API middleware configuration
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // API middleware - enforce JSON responses
        $middleware->api(append: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Web middleware - prevent aggressive HTML caching
        $middleware->web(append: [
            \App\Http\Middleware\SetCacheHeaders::class,
            \App\Http\Middleware\PreserveLivewireFlash::class,
        ]);

        // Security headers middleware alias
        $middleware->alias([
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'api.performance' => \App\Http\Middleware\ApiPerformanceMiddleware::class,
            'tiered.rate.limit' => \App\Http\Middleware\TieredRateLimiting::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // Configure authentication redirects - redirect to welcome page instead of login
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->expectsJson()
                ? null
                : route('welcome')
        );
    })
    ->withSchedule(function (Schedule $schedule): void {
        // OCR file cleanup - runs daily at 2 AM
        $schedule->command('ocr:cleanup')
            ->daily()
            ->at('02:00')
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle rate limiting exceptions for API
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60,
                ], 429);
            }
        });

        // Handle 404 for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
        });
    })->create();
