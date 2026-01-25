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
            /** @var array{total_requests: int, successful_requests: int, failed_requests: int, total_processing_time: float, total_tokens: int, total_cost: float, average_processing_time: float, average_tokens: int, average_cost: float, success_rate: float} $providerMetrics */
            $totalReqs = (int) $providerMetrics['total_requests'];
            $successfulReqs = (int) $providerMetrics['successful_requests'];
            $totalProcTime = (float) $providerMetrics['total_processing_time'];
            $totalTokensVal = (int) $providerMetrics['total_tokens'];
            $totalCostVal = (float) $providerMetrics['total_cost'];

            $totalReqs++;
            $successfulReqs++;

            $processingTime = isset($response['processing_time']) && is_numeric($response['processing_time']) ? (float) $response['processing_time'] : 0.0;
            $tokenCount = isset($response['token_count']) && is_numeric($response['token_count']) ? (int) $response['token_count'] : 0;
            $cost = isset($response['cost']) && is_numeric($response['cost']) ? (float) $response['cost'] : 0.0;

            $totalProcTime += $processingTime;
            $totalTokensVal += $tokenCount;
            $totalCostVal += $cost;

            // Calculate averages and update provider metrics
            $providerMetrics['total_requests'] = $totalReqs;
            $providerMetrics['successful_requests'] = $successfulReqs;
            $providerMetrics['total_processing_time'] = $totalProcTime;
            $providerMetrics['total_tokens'] = $totalTokensVal;
            $providerMetrics['total_cost'] = $totalCostVal;
            $providerMetrics['average_processing_time'] = $totalProcTime / $totalReqs;
            $providerMetrics['average_tokens'] = (int) ($totalTokensVal / $totalReqs);
            $providerMetrics['average_cost'] = $totalCostVal / $totalReqs;
            $providerMetrics['success_rate'] = $successfulReqs / $totalReqs;

            // Update global metrics
            $metrics['total_requests']++;
            $metrics['total_processing_time'] += $processingTime;
            $metrics['total_tokens'] += $tokenCount;
            $metrics['total_cost'] += $cost;

            // Store updated metrics
            $this->storeMetrics($metrics);

            // Log performance data
            Log::debug('[AIPerformance] Request tracked', [
                'provider' => $provider,
                'processing_time' => $processingTime,
                'token_count' => $tokenCount,
                'cost' => $cost,
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
            /** @var array{total_requests: int, successful_requests: int, failed_requests: int, total_processing_time: float, total_tokens: int, total_cost: float, average_processing_time: float, average_tokens: int, average_cost: float, success_rate: float} $providerMetrics */
            $totalReqs = (int) $providerMetrics['total_requests'];
            $successfulReqs = (int) $providerMetrics['successful_requests'];
            $failedReqs = (int) $providerMetrics['failed_requests'];

            $totalReqs++;
            $failedReqs++;

            // Update provider metrics
            $providerMetrics['total_requests'] = $totalReqs;
            $providerMetrics['failed_requests'] = $failedReqs;

            // Calculate success rate
            $providerMetrics['success_rate'] = $successfulReqs / $totalReqs;

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
            return [
                'total_requests' => 0,
                'total_processing_time' => 0.0,
                'total_tokens' => 0,
                'total_cost' => 0.0,
                'total_failures' => 0,
                'providers' => [],
                'last_updated' => now()->toIso8601String(),
            ];
        }

        // Ensure all keys exist with proper types
        $totalRequestsRaw = $metrics['total_requests'] ?? 0;
        $totalProcessingTimeRaw = $metrics['total_processing_time'] ?? 0.0;
        $totalTokensRaw = $metrics['total_tokens'] ?? 0;
        $totalCostRaw = $metrics['total_cost'] ?? 0.0;
        $totalFailuresRaw = $metrics['total_failures'] ?? 0;
        $providersRaw = $metrics['providers'] ?? [];
        $lastUpdatedRaw = $metrics['last_updated'] ?? now()->toIso8601String();

        /** @var array<string, array<string, mixed>> $providers */
        $providers = is_array($providersRaw) ? $providersRaw : [];

        return [
            'total_requests' => is_numeric($totalRequestsRaw) ? (int) $totalRequestsRaw : 0,
            'total_processing_time' => is_numeric($totalProcessingTimeRaw) ? (float) $totalProcessingTimeRaw : 0.0,
            'total_tokens' => is_numeric($totalTokensRaw) ? (int) $totalTokensRaw : 0,
            'total_cost' => is_numeric($totalCostRaw) ? (float) $totalCostRaw : 0.0,
            'total_failures' => is_numeric($totalFailuresRaw) ? (int) $totalFailuresRaw : 0,
            'providers' => $providers,
            'last_updated' => is_string($lastUpdatedRaw) ? $lastUpdatedRaw : now()->toIso8601String(),
        ];
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
        $providers = $metrics['providers'];

        $comparison = [];

        foreach ($providers as $provider => $providerMetrics) {
            $totalRequestsRaw = $providerMetrics['total_requests'] ?? 0;
            $totalRequests = is_numeric($totalRequestsRaw) ? (int) $totalRequestsRaw : 0;
            $successRate = is_numeric($providerMetrics['success_rate'] ?? null) ? (float) $providerMetrics['success_rate'] : 0.0;
            $avgProcessingTime = is_numeric($providerMetrics['average_processing_time'] ?? null) ? (float) $providerMetrics['average_processing_time'] : 0.0;
            $avgTokensRaw = $providerMetrics['average_tokens'] ?? 0;
            $avgTokens = is_numeric($avgTokensRaw) ? (int) $avgTokensRaw : 0;
            $avgCost = is_numeric($providerMetrics['average_cost'] ?? null) ? (float) $providerMetrics['average_cost'] : 0.0;
            $totalCost = is_numeric($providerMetrics['total_cost'] ?? null) ? (float) $providerMetrics['total_cost'] : 0.0;

            $comparison[$provider] = [
                'total_requests' => $totalRequests,
                'success_rate' => round($successRate * 100, 2),
                'average_processing_time' => round($avgProcessingTime, 3),
                'average_tokens' => $avgTokens,
                'average_cost' => round($avgCost, 6),
                'total_cost' => round($totalCost, 4),
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
        $providers = $metrics['providers'];

        $byProvider = [];
        foreach ($providers as $provider => $providerMetrics) {
            $totalCost = is_numeric($providerMetrics['total_cost'] ?? null) ? (float) $providerMetrics['total_cost'] : 0.0;
            $byProvider[$provider] = round($totalCost, 4);
        }

        return [
            'total_cost' => round($metrics['total_cost'], 4),
            'by_provider' => $byProvider,
            'by_model' => $this->getCostByModel(),
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
     * Get cost breakdown by model
     *
     * @return array<string, float>
     */
    protected function getCostByModel(): array
    {
        $metrics = $this->getMetrics();
        $byModel = [];

        // Extract model-specific costs from provider metrics
        foreach ($metrics['providers'] as $provider => $providerMetrics) {
            // Each provider may use different models
            // For now, aggregate by provider as model proxy
            $modelKey = match ($provider) {
                'ollama' => 'llama3.3',
                'bedrock' => 'claude-3-5-sonnet',
                'mcp-strands' => 'strands-agent',
                'mcp-agentcore' => 'agentcore-agent',
                default => $provider,
            };

            $totalCost = is_numeric($providerMetrics['total_cost'] ?? null) ? (float) $providerMetrics['total_cost'] : 0.0;
            $byModel[$modelKey] = round($totalCost, 4);
        }

        return $byModel;
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
    protected function generateRecommendations(array $metrics = [], array $comparison = []): array
    {
        $recommendations = [];

        // Check if Ollama is being underutilized
        if (isset($comparison['ollama']) && isset($comparison['bedrock'])) {
            $ollamaRequestsRaw = $comparison['ollama']['total_requests'] ?? 0;
            $ollamaRequests = is_numeric($ollamaRequestsRaw) ? (int) $ollamaRequestsRaw : 0;
            $bedrockRequestsRaw = $comparison['bedrock']['total_requests'] ?? 0;
            $bedrockRequests = is_numeric($bedrockRequestsRaw) ? (int) $bedrockRequestsRaw : 0;

            if ($bedrockRequests > $ollamaRequests * 2) {
                $recommendations[] = 'Consider routing more simple requests to Ollama to reduce costs';
            }
        }

        // Check for high costs
        $totalCost = is_numeric($metrics['total_cost'] ?? null) ? (float) $metrics['total_cost'] : 0.0;
        if ($totalCost > 1.0) {
            $recommendations[] = 'Total AI costs are high. Review request complexity and model selection';
        }

        // Check for slow processing
        foreach ($comparison as $provider => $providerMetrics) {
            $avgProcTime = is_numeric($providerMetrics['average_processing_time'] ?? null) ? (float) $providerMetrics['average_processing_time'] : 0.0;
            if ($avgProcTime > 10.0) {
                $recommendations[] = "Provider '{$provider}' has slow average processing time. Consider optimization";
            }
        }

        // Check for high failure rates
        foreach ($comparison as $provider => $providerMetrics) {
            $successRate = is_numeric($providerMetrics['success_rate'] ?? null) ? (float) $providerMetrics['success_rate'] : 100.0;
            if ($successRate < 90.0) {
                $recommendations[] = "Provider '{$provider}' has low success rate. Check health and configuration";
            }
        }

        return $recommendations;
    }
}
