<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AI Performance Monitor
 *
 * Tracks performance metrics across all AI providers (local, MCP, direct).
 * Monitors response times, costs, success rates, and provider health.
 *
 * Requirements: 13.1, 56.2, 57.2
 */
class AIPerformanceMonitor
{
    protected const METRICS_TTL = 3600; // 1 hour

    protected const METRICS_KEY_PREFIX = 'ai_performance_';

    /**
     * Track AI request
     *
     * @param  array<string, mixed>  $response
     */
    public function trackRequest(string $provider, array $response): void
    {
        try {
            $metrics = $this->getMetrics();

            // Initialize provider metrics if not exists
            if (! isset($metrics['providers'][$provider])) {
                $metrics['providers'][$provider] = [
                    'total_requests' => 0,
                    'successful_requests' => 0,
                    'failed_requests' => 0,
                    'total_processing_time' => 0.0,
                    'total_tokens' => 0,
                    'total_cost' => 0.0,
                    'average_processing_time' => 0.0,
                    'average_tokens' => 0,
                    'average_cost' => 0.0,
                    'success_rate' => 0.0,
                ];
            }

            // Update metrics
            $providerMetrics = &$metrics['providers'][$provider];
            $providerMetrics['total_requests']++;
            $providerMetrics['successful_requests']++;
            $providerMetrics['total_processing_time'] += $response['processing_time'] ?? 0.0;
            $providerMetrics['total_tokens'] += $response['token_count'] ?? 0;
            $providerMetrics['total_cost'] += $response['cost'] ?? 0.0;

            // Calculate averages
            $totalRequests = $providerMetrics['total_requests'];
            $providerMetrics['average_processing_time'] = $providerMetrics['total_processing_time'] / $totalRequests;
            $providerMetrics['average_tokens'] = (int) ($providerMetrics['total_tokens'] / $totalRequests);
            $providerMetrics['average_cost'] = $providerMetrics['total_cost'] / $totalRequests;
            $providerMetrics['success_rate'] = $providerMetrics['successful_requests'] / $totalRequests;

            // Update global metrics
            $metrics['total_requests']++;
            $metrics['total_processing_time'] += $response['processing_time'] ?? 0.0;
            $metrics['total_tokens'] += $response['token_count'] ?? 0;
            $metrics['total_cost'] += $response['cost'] ?? 0.0;

            // Store updated metrics
            $this->storeMetrics($metrics);

            // Log performance data
            Log::debug('[AIPerformance] Request tracked', [
                'provider' => $provider,
                'processing_time' => $response['processing_time'] ?? 0.0,
                'token_count' => $response['token_count'] ?? 0,
                'cost' => $response['cost'] ?? 0.0,
            ]);
        } catch (\Exception $e) {
            Log::error('[AIPerformance] Failed to track request', [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);
        }
    }

    /**
     * Track failed request
     */
    public function trackFailure(string $provider, string $error): void
    {
        try {
            $metrics = $this->getMetrics();

            // Initialize provider metrics if not exists
            if (! isset($metrics['providers'][$provider])) {
                $metrics['providers'][$provider] = [
                    'total_requests' => 0,
                    'successful_requests' => 0,
                    'failed_requests' => 0,
                    'total_processing_time' => 0.0,
                    'total_tokens' => 0,
                    'total_cost' => 0.0,
                    'average_processing_time' => 0.0,
                    'average_tokens' => 0,
                    'average_cost' => 0.0,
                    'success_rate' => 0.0,
                ];
            }

            // Update metrics
            $providerMetrics = &$metrics['providers'][$provider];
            $providerMetrics['total_requests']++;
            $providerMetrics['failed_requests']++;

            // Calculate success rate
            $totalRequests = $providerMetrics['total_requests'];
            $providerMetrics['success_rate'] = $providerMetrics['successful_requests'] / $totalRequests;

            // Update global metrics
            $metrics['total_requests']++;
            $metrics['total_failures']++;

            // Store updated metrics
            $this->storeMetrics($metrics);

            // Log failure
            Log::warning('[AIPerformance] Request failed', [
                'provider' => $provider,
                'error' => $error,
            ]);
        } catch (\Exception $e) {
            Log::error('[AIPerformance] Failed to track failure', [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);
        }
    }

    /**
     * Get performance metrics
     *
     * @return array{
     *     total_requests: int,
     *     total_processing_time: float,
     *     total_tokens: int,
     *     total_cost: float,
     *     total_failures: int,
     *     providers: array<string, array<string, mixed>>,
     *     last_updated: string
     * }
     */
    public function getMetrics(): array
    {
        $cacheKey = self::METRICS_KEY_PREFIX.'global';

        $metrics = Cache::get($cacheKey);

        if (! $metrics || ! \is_array($metrics)) {
            $metrics = [
                'total_requests' => 0,
                'total_processing_time' => 0.0,
                'total_tokens' => 0,
                'total_cost' => 0.0,
                'total_failures' => 0,
                'providers' => [],
                'last_updated' => now()->toIso8601String(),
            ];
        }

        return $metrics;
    }

    /**
     * Store performance metrics
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function storeMetrics(array $metrics): void
    {
        $cacheKey = self::METRICS_KEY_PREFIX.'global';
        $metrics['last_updated'] = now()->toIso8601String();

        Cache::put($cacheKey, $metrics, self::METRICS_TTL);
    }

    /**
     * Get provider metrics
     *
     * @return array<string, mixed>|null
     */
    public function getProviderMetrics(string $provider): ?array
    {
        $metrics = $this->getMetrics();

        return $metrics['providers'][$provider] ?? null;
    }

    /**
     * Get provider comparison
     *
     * @return array<string, array<string, mixed>>
     */
    public function getProviderComparison(): array
    {
        $metrics = $this->getMetrics();
        $providers = $metrics['providers'] ?? [];

        $comparison = [];

        foreach ($providers as $provider => $providerMetrics) {
            $comparison[$provider] = [
                'total_requests' => $providerMetrics['total_requests'],
                'success_rate' => round($providerMetrics['success_rate'] * 100, 2),
                'average_processing_time' => round($providerMetrics['average_processing_time'], 3),
                'average_tokens' => $providerMetrics['average_tokens'],
                'average_cost' => round($providerMetrics['average_cost'], 6),
                'total_cost' => round($providerMetrics['total_cost'], 4),
            ];
        }

        return $comparison;
    }

    /**
     * Get cost summary
     *
     * @return array{
     *     total_cost: float,
     *     by_provider: array<string, float>,
     *     by_model: array<string, float>
     * }
     */
    public function getCostSummary(): array
    {
        $metrics = $this->getMetrics();
        $providers = $metrics['providers'] ?? [];

        $byProvider = [];
        foreach ($providers as $provider => $providerMetrics) {
            $byProvider[$provider] = round($providerMetrics['total_cost'], 4);
        }

        return [
            'total_cost' => round($metrics['total_cost'], 4),
            'by_provider' => $byProvider,
            'by_model' => [], // TODO: Track by model
        ];
    }

    /**
     * Reset metrics
     */
    public function resetMetrics(): void
    {
        $cacheKey = self::METRICS_KEY_PREFIX.'global';
        Cache::forget($cacheKey);

        Log::info('[AIPerformance] Metrics reset');
    }

    /**
     * Get performance report
     *
     * @return array{
     *     summary: array<string, mixed>,
     *     providers: array<string, array<string, mixed>>,
     *     cost_summary: array<string, mixed>,
     *     recommendations: array<string>
     * }
     */
    public function getPerformanceReport(): array
    {
        $metrics = $this->getMetrics();
        $comparison = $this->getProviderComparison();
        $costSummary = $this->getCostSummary();

        // Generate recommendations
        $recommendations = $this->generateRecommendations($metrics, $comparison);

        return [
            'summary' => [
                'total_requests' => $metrics['total_requests'],
                'total_cost' => round($metrics['total_cost'], 4),
                'total_failures' => $metrics['total_failures'],
                'average_processing_time' => $metrics['total_requests'] > 0
                    ? round($metrics['total_processing_time'] / $metrics['total_requests'], 3)
                    : 0.0,
                'last_updated' => $metrics['last_updated'],
            ],
            'providers' => $comparison,
            'cost_summary' => $costSummary,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Generate performance recommendations
     *
     * @param  array<string, mixed>  $metrics
     * @param  array<string, array<string, mixed>>  $comparison
     * @return array<string>
     */
    protected function generateRecommendations(array $metrics, array $comparison): array
    {
        $recommendations = [];

        // Check if Ollama is being underutilized
        if (isset($comparison['ollama']) && isset($comparison['bedrock'])) {
            $ollamaRequests = $comparison['ollama']['total_requests'];
            $bedrockRequests = $comparison['bedrock']['total_requests'];

            if ($bedrockRequests > $ollamaRequests * 2) {
                $recommendations[] = 'Consider routing more simple requests to Ollama to reduce costs';
            }
        }

        // Check for high costs
        if ($metrics['total_cost'] > 1.0) {
            $recommendations[] = 'Total AI costs are high. Review request complexity and model selection';
        }

        // Check for slow processing
        foreach ($comparison as $provider => $providerMetrics) {
            if ($providerMetrics['average_processing_time'] > 10.0) {
                $recommendations[] = "Provider '{$provider}' has slow average processing time. Consider optimization";
            }
        }

        // Check for high failure rates
        foreach ($comparison as $provider => $providerMetrics) {
            if ($providerMetrics['success_rate'] < 90.0) {
                $recommendations[] = "Provider '{$provider}' has low success rate. Check health and configuration";
            }
        }

        return $recommendations;
    }
}
