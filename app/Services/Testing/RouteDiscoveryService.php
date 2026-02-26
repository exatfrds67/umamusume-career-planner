<?php

declare(strict_types=1);

namespace App\Services\Testing;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Route Discovery Service
 *
 * Discovers and categorizes all application routes for comprehensive testing.
 * Routes are categorized by authentication requirements and middleware.
 */
class RouteDiscoveryService
{
    /**
     * Get all routes categorized by authentication level.
     *
     * @return array{public: array<int, array<string, mixed>>, auth: array<int, array<string, mixed>>, admin: array<int, array<string, mixed>>}
     */
    public function discoverRoutes(): array
    {
        $routes = RouteFacade::getRoutes();
        $categorized = [
            'public' => [],
            'auth' => [],
            'admin' => [],
        ];

        /** @var Route $route */
        /** @var iterable<int, Route> $routes */
        foreach ($routes as $route) {
            // Only process GET routes for browser testing
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            // Skip API routes, we're testing web UI
            if (str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            // Skip specific routes that shouldn't be tested
            if ($this->shouldSkipRoute($route)) {
                continue;
            }

            $routeData = $this->extractRouteData($route);

            // Categorize by middleware
            if ($this->hasAdminMiddleware($route)) {
                $categorized['admin'][] = $routeData;
            } elseif ($this->hasAuthMiddleware($route)) {
                $categorized['auth'][] = $routeData;
            } else {
                $categorized['public'][] = $routeData;
            }
        }

        return $categorized;
    }

    /**
     * Extract relevant route data for testing.
     *
     * @return array{
     *     uri: string,
     *     name: string|null,
     *     parameters: array<string>,
     *     middleware: array<string>,
     *     hasParameters: bool
     * }
     */
    private function extractRouteData(Route $route): array
    {
        $uri = $route->uri();
        $parameters = $this->extractParameters($uri);

        return [
            'uri' => $uri,
            'name' => $route->getName(),
            'parameters' => $parameters,
            'middleware' => $this->getMiddleware($route),
            'hasParameters' => count($parameters) > 0,
        ];
    }

    /**
     * Extract parameter names from route URI.
     *
     * @return array<string>
     */
    private function extractParameters(string $uri): array
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);

        return $matches[1];
    }

    /**
     * Get middleware for a route.
     *
     * @return array<string>
     */
    private function getMiddleware(Route $route): array
    {
        $middleware = $route->middleware();

        // Flatten if middleware is nested
        if (is_array($middleware)) {
            return array_values(array_filter(array_map(fn ($m) => is_string($m) ? $m : null, $middleware)));
        }

        return [];
    }

    /**
     * Check if route has admin middleware.
     */
    private function hasAdminMiddleware(Route $route): bool
    {
        $middleware = $this->getMiddleware($route);

        return in_array('admin', $middleware, true);
    }

    /**
     * Check if route has auth middleware.
     */
    private function hasAuthMiddleware(Route $route): bool
    {
        $middleware = $this->getMiddleware($route);

        return in_array('auth', $middleware, true);
    }

    /**
     * Determine if a route should be skipped in testing.
     */
    private function shouldSkipRoute(Route $route): bool
    {
        $uri = $route->uri();

        // Skip specific routes
        $skipPatterns = [
            'sw.js',                    // Service worker
            'manifest.json',            // PWA manifest
            'offline.html',             // Offline page
            '_ignition',                // Debug routes
            'horizon',                  // Horizon routes
            'telescope',                // Telescope routes
            'api/',                     // API routes (already checked above)
            'sanctum/',                 // Sanctum routes
            'livewire/',                // Livewire internal routes
            'test-api',                 // Test API route
            'logout',                   // POST only route
            'login',                    // Will test via auth flow
            'register',                 // Will test via auth flow
        ];

        foreach ($skipPatterns as $pattern) {
            if (str_contains($uri, $pattern)) {
                return true;
            }
        }

        // Skip routes with optional parameters (for now)
        if (str_contains($uri, '?}')) {
            return true;
        }

        return false;
    }

    /**
     * Build URL for route with test parameters.
     *
     * @param  array<string, mixed>  $routeData
     * @param  array<string, string|int>  $parameterValues  Map of parameter names to values
     */
    public function buildUrl(array $routeData, array $parameterValues = []): string
    {
        $uri = $routeData['uri'];

        // Replace parameters with test values
        foreach ($routeData['parameters'] as $param) {
            if (isset($parameterValues[$param])) {
                $uri = str_replace('{'.$param.'}', (string) $parameterValues[$param], $uri);
            }
        }

        return '/'.$uri;
    }

    /**
     * Get statistics about discovered routes.
     *
     * @param  array<string, array<int, array<string, mixed>>>  $categorized
     * @return array{total: int, public: int, auth: int, admin: int, withParameters: int}
     */
    public function getStatistics(array $categorized): array
    {
        $withParameters = 0;

        foreach (['public', 'auth', 'admin'] as $category) {
            foreach ($categorized[$category] as $route) {
                if ($route['hasParameters']) {
                    $withParameters++;
                }
            }
        }

        return [
            'total' => count($categorized['public']) + count($categorized['auth']) + count($categorized['admin']),
            'public' => count($categorized['public']),
            'auth' => count($categorized['auth']),
            'admin' => count($categorized['admin']),
            'withParameters' => $withParameters,
        ];
    }
}
