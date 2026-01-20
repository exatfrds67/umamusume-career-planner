<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tiered Rate Limiting Middleware
 *
 * Provides intelligent rate limiting with different tiers for:
 * - Public/unauthenticated users
 * - Authenticated standard users
 * - Premium users
 * - Admin users
 *
 * Also supports endpoint-specific rate limits.
 *
 * @see Requirements: 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */
class TieredRateLimiting
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected readonly RateLimiter $limiter
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $tier = null): Response
    {
        // Check if rate limiting is enabled
        if (! config('api-performance.rate_limiting.enabled', true)) {
            return $next($request);
        }

        // Determine the rate limit tier
        $tier = $tier ?? $this->determineTier($request);
        $tierConfig = $this->getTierConfig($tier);

        // Check for endpoint-specific limits
        $endpointConfig = $this->getEndpointConfig($request);
        if ($endpointConfig !== null) {
            $tierConfig = array_merge($tierConfig, $endpointConfig);
        }

        // Generate rate limit key
        $key = $this->resolveRequestSignature($request, $tier);

        // Check rate limits
        $limitResult = $this->checkRateLimits($key, $tierConfig);

        if (! $limitResult['allowed']) {
            return $this->buildRateLimitResponse($request, $limitResult, $tierConfig);
        }

        // Process request
        $response = $next($request);

        // Add rate limit headers
        if (config('api-performance.rate_limiting.headers.enabled', true)) {
            $response = $this->addRateLimitHeaders($response, $limitResult, $tierConfig);
        }

        return $response;
    }

    /**
     * Determine the rate limit tier for the request.
     */
    protected function determineTier(Request $request): string
    {
        $user = $request->user();

        if ($user === null) {
            return 'public';
        }

        // Check for admin role
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return 'admin';
        }

        // Check for premium status
        if (method_exists($user, 'isPremium') && $user->isPremium()) {
            return 'premium';
        }

        // Check user role attribute
        $role = $user->role ?? null;
        if ($role === 'admin') {
            return 'admin';
        }
        if ($role === 'premium') {
            return 'premium';
        }

        return 'authenticated';
    }

    /**
     * Get configuration for a tier.
     *
     * @return array{requests_per_minute: int, requests_per_hour: int, requests_per_day: int, burst_limit: int}
     */
    protected function getTierConfig(string $tier): array
    {
        $tiers = config('api-performance.rate_limiting.tiers', []);

        return $tiers[$tier] ?? $tiers['public'] ?? [
            'requests_per_minute' => 60,
            'requests_per_hour' => 500,
            'requests_per_day' => 5000,
            'burst_limit' => 10,
        ];
    }

    /**
     * Get endpoint-specific configuration.
     *
     * @return array<string, int>|null
     */
    protected function getEndpointConfig(Request $request): ?array
    {
        $path = $request->path();
        $endpointLimits = config('api-performance.rate_limiting.endpoint_limits', []);

        foreach ($endpointLimits as $pattern => $config) {
            if (fnmatch($pattern, $path)) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $tier): string
    {
        $user = $request->user();

        if ($user !== null) {
            return 'rate_limit:'.$tier.':user:'.$user->id;
        }

        return 'rate_limit:'.$tier.':ip:'.$request->ip();
    }

    /**
     * Check rate limits for the request.
     *
     * @param  array<string, int>  $config
     * @return array{allowed: bool, remaining: int, reset_at: int, limit: int, window: string}
     */
    protected function checkRateLimits(string $key, array $config): array
    {
        // Check minute limit (primary)
        $minuteKey = $key.':minute';
        $minuteLimit = $config['requests_per_minute'];
        $minuteRemaining = $this->limiter->remaining($minuteKey, $minuteLimit);

        if ($minuteRemaining <= 0) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => time() + $this->limiter->availableIn($minuteKey),
                'limit' => $minuteLimit,
                'window' => 'minute',
            ];
        }

        // Check hour limit
        $hourKey = $key.':hour';
        $hourLimit = $config['requests_per_hour'];
        $hourRemaining = $this->limiter->remaining($hourKey, $hourLimit);

        if ($hourRemaining <= 0) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => time() + $this->limiter->availableIn($hourKey),
                'limit' => $hourLimit,
                'window' => 'hour',
            ];
        }

        // Check day limit
        $dayKey = $key.':day';
        $dayLimit = $config['requests_per_day'];
        $dayRemaining = $this->limiter->remaining($dayKey, $dayLimit);

        if ($dayRemaining <= 0) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => time() + $this->limiter->availableIn($dayKey),
                'limit' => $dayLimit,
                'window' => 'day',
            ];
        }

        // Increment all counters
        $this->limiter->hit($minuteKey, 60);
        $this->limiter->hit($hourKey, 3600);
        $this->limiter->hit($dayKey, 86400);

        return [
            'allowed' => true,
            'remaining' => min($minuteRemaining - 1, $hourRemaining - 1, $dayRemaining - 1),
            'reset_at' => time() + $this->limiter->availableIn($minuteKey),
            'limit' => $minuteLimit,
            'window' => 'minute',
        ];
    }

    /**
     * Build rate limit exceeded response.
     *
     * @param  array{allowed: bool, remaining: int, reset_at: int, limit: int, window: string}  $limitResult
     * @param  array<string, int>  $config
     */
    protected function buildRateLimitResponse(Request $request, array $limitResult, array $config): Response
    {
        $retryAfter = max(1, $limitResult['reset_at'] - time());

        Log::warning('[TieredRateLimiting] Rate limit exceeded', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'path' => $request->path(),
            'window' => $limitResult['window'],
            'limit' => $limitResult['limit'],
            'retry_after' => $retryAfter,
        ]);

        $response = response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'window' => $limitResult['window'],
                'limit' => $limitResult['limit'],
                'retry_after' => $retryAfter,
            ],
        ], 429);

        // Add rate limit headers
        $response->headers->set('Retry-After', (string) $retryAfter);
        $response->headers->set(
            config('api-performance.rate_limiting.headers.limit_header', 'X-RateLimit-Limit'),
            (string) $limitResult['limit']
        );
        $response->headers->set(
            config('api-performance.rate_limiting.headers.remaining_header', 'X-RateLimit-Remaining'),
            '0'
        );
        $response->headers->set(
            config('api-performance.rate_limiting.headers.reset_header', 'X-RateLimit-Reset'),
            (string) $limitResult['reset_at']
        );

        return $response;
    }

    /**
     * Add rate limit headers to response.
     *
     * @param  array{allowed: bool, remaining: int, reset_at: int, limit: int, window: string}  $limitResult
     * @param  array<string, int>  $config
     */
    protected function addRateLimitHeaders(Response $response, array $limitResult, array $config): Response
    {
        $response->headers->set(
            config('api-performance.rate_limiting.headers.limit_header', 'X-RateLimit-Limit'),
            (string) $limitResult['limit']
        );
        $response->headers->set(
            config('api-performance.rate_limiting.headers.remaining_header', 'X-RateLimit-Remaining'),
            (string) max(0, $limitResult['remaining'])
        );
        $response->headers->set(
            config('api-performance.rate_limiting.headers.reset_header', 'X-RateLimit-Reset'),
            (string) $limitResult['reset_at']
        );

        return $response;
    }
}
