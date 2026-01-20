<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ApiPerformanceMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Performance Middleware
 *
 * Provides comprehensive API performance optimization including:
 * - Response compression (gzip/brotli)
 * - Request/response timing and monitoring
 * - Performance metrics collection
 * - Bottleneck identification
 *
 * @see Requirements: 52.3, 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */
class ApiPerformanceMiddleware
{
    /**
     * Minimum response size for compression (bytes)
     */
    protected const MIN_COMPRESSION_SIZE = 1024;

    /**
     * Slow request threshold (milliseconds)
     */
    protected const SLOW_REQUEST_THRESHOLD_MS = 1000;

    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected readonly ApiPerformanceMonitoringService $performanceService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timing
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Generate unique request ID for tracing
        $requestId = $this->generateRequestId();
        $request->headers->set('X-Request-ID', $requestId);

        // Record request start
        $this->performanceService->recordRequestStart($requestId, [
            'method' => $request->method(),
            'path' => $request->path(),
            'query_params' => $request->query(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Process request
        $response = $next($request);

        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $durationMs = ($endTime - $startTime) * 1000;
        $memoryUsed = $endMemory - $startMemory;

        // Add performance headers
        $response = $this->addPerformanceHeaders($response, $requestId, $durationMs);

        // Apply compression if applicable
        if ($this->shouldCompress($request, $response)) {
            $response = $this->compressResponse($request, $response);
        }

        // Record request completion
        $this->performanceService->recordRequestEnd($requestId, [
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'memory_bytes' => $memoryUsed,
            'response_size' => strlen($response->getContent() ?: ''),
            'compressed' => $response->headers->has('Content-Encoding'),
        ]);

        // Log slow requests
        if ($durationMs > self::SLOW_REQUEST_THRESHOLD_MS) {
            $this->logSlowRequest($request, $durationMs, $requestId);
        }

        return $response;
    }

    /**
     * Generate a unique request ID.
     */
    protected function generateRequestId(): string
    {
        return sprintf(
            '%s-%s-%s',
            date('YmdHis'),
            substr(md5((string) microtime(true)), 0, 8),
            bin2hex(random_bytes(4))
        );
    }

    /**
     * Add performance headers to response.
     */
    protected function addPerformanceHeaders(Response $response, string $requestId, float $durationMs): Response
    {
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Response-Time', sprintf('%.2fms', $durationMs));
        $response->headers->set('X-Memory-Usage', $this->formatBytes(memory_get_peak_usage(true)));

        // Add server timing header for browser DevTools
        $response->headers->set(
            'Server-Timing',
            sprintf('total;dur=%.2f;desc="Total Request Time"', $durationMs)
        );

        return $response;
    }

    /**
     * Check if response should be compressed.
     */
    protected function shouldCompress(Request $request, Response $response): bool
    {
        // Check if compression is enabled
        if (! config('api-performance.compression.enabled', true)) {
            return false;
        }

        // Check if client accepts compression
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (! str_contains($acceptEncoding, 'gzip') && ! str_contains($acceptEncoding, 'br')) {
            return false;
        }

        // Check if response is already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Check content type (only compress text-based responses)
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'application/json',
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/xml',
            'text/xml',
        ];

        $isCompressible = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $isCompressible = true;
                break;
            }
        }

        if (! $isCompressible) {
            return false;
        }

        // Check minimum size
        $content = $response->getContent();
        if ($content === false || strlen($content) < self::MIN_COMPRESSION_SIZE) {
            return false;
        }

        return true;
    }

    /**
     * Compress response content.
     */
    protected function compressResponse(Request $request, Response $response): Response
    {
        $content = $response->getContent();
        if ($content === false) {
            return $response;
        }

        $acceptEncoding = $request->header('Accept-Encoding', '');
        $originalSize = strlen($content);

        // Prefer brotli if available and supported
        if (str_contains($acceptEncoding, 'br') && function_exists('brotli_compress')) {
            $compressed = brotli_compress($content, 4);
            if ($compressed !== false && strlen($compressed) < $originalSize) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'br');
                $response->headers->set('X-Original-Size', (string) $originalSize);
                $response->headers->set('X-Compressed-Size', (string) strlen($compressed));
                $response->headers->remove('Content-Length');

                return $response;
            }
        }

        // Fall back to gzip
        if (str_contains($acceptEncoding, 'gzip')) {
            $level = (int) config('api-performance.compression.level', 6);
            $compressed = gzencode($content, $level);
            if ($compressed !== false && strlen($compressed) < $originalSize) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('X-Original-Size', (string) $originalSize);
                $response->headers->set('X-Compressed-Size', (string) strlen($compressed));
                $response->headers->remove('Content-Length');
            }
        }

        return $response;
    }

    /**
     * Log slow request for analysis.
     */
    protected function logSlowRequest(Request $request, float $durationMs, string $requestId): void
    {
        Log::warning('[ApiPerformance] Slow request detected', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'duration_ms' => round($durationMs, 2),
            'threshold_ms' => self::SLOW_REQUEST_THRESHOLD_MS,
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Format bytes to human readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
