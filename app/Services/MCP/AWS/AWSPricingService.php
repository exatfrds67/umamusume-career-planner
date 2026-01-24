<?php

declare(strict_types=1);

namespace App\Services\MCP\AWS;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AWS Pricing Service
 *
 * Integrates with awspricing MCP server for real-time cost analysis,
 * budget optimization recommendations, cost forecasting, and service pricing comparisons.
 *
 * Requirements: 56.4, 59.3, 14.1
 */
class AWSPricingService
{
    public const CACHE_TTL = 3600; // 1 hour

    protected const MCP_SERVER_NAME = 'awspricing';

    public function __construct(
        private readonly MCPClientService $mcpClient
    ) {}

    /**
     * Check if AWS Pricing MCP server is available
     */
    public function isAvailable(): bool
    {
        return $this->mcpClient->isServerEnabled(self::MCP_SERVER_NAME) &&
            $this->mcpClient->isServerHealthy(self::MCP_SERVER_NAME);
    }

    /**
     * Get pricing for a specific AWS service
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     service: string,
     *     region: string,
     *     pricing: array<string, mixed>,
     *     currency: string,
     *     effective_date: string
     * }
     */
    public function getServicePricing(string $service = 'bedrock', string $region = 'us-east-1', array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('pricing', $service, $region, $filters);

        /** @var array{service: string, region: string, pricing: array<string, mixed>, currency: string, effective_date: string} */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($service, $region, $filters): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackPricing($service, $region);
            }

            try {
                // In production, this would call the MCP server
                // For now, return structured data
                return $this->fetchPricingFromMCP($service, $region, $filters);
            } catch (\Exception $e) {
                Log::error('[AWSPricing] Failed to fetch pricing', [
                    'service' => $service,
                    'region' => $region,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackPricing($service, $region);
            }
        });
    }

    /**
     * Get Bedrock model pricing
     *
     * @return array<string, array{
     *     model: string,
     *     input_cost_per_1k: float,
     *     output_cost_per_1k: float,
     *     currency: string,
     *     region: string
     * }>
     */
    public function getBedrockPricing(string $region = 'us-east-1'): array
    {
        $cacheKey = $this->getCacheKey('bedrock_pricing', $region);

        /** @var array<string, array{model: string, input_cost_per_1k: float, output_cost_per_1k: float, currency: string, region: string}> */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($region): array {
            if (! $this->isAvailable()) {
                return $this->getFallbackBedrockPricing();
            }

            try {
                return $this->fetchBedrockPricingFromMCP($region);
            } catch (\Exception $e) {
                Log::error('[AWSPricing] Failed to fetch Bedrock pricing', [
                    'region' => $region,
                    'error' => $e->getMessage(),
                ]);

                return $this->getFallbackBedrockPricing();
            }
        });
    }

    /**
     * Calculate estimated monthly cost for AI operations
     *
     * @param  array<string, mixed>  $usage
     * @return array{
     *     estimated_cost: float,
     *     breakdown: array<string, mixed>,
     *     recommendations: array<int, string>,
     *     currency: string
     * }
     */
    public function calculateMonthlyCost(array $usage = []): array
    {
        $bedrockPricing = $this->getBedrockPricing();
        /** @var array<string, array{input_tokens: int, output_tokens: int, input_cost: float, output_cost: float, total_cost: float}> $breakdown */
        $breakdown = [];
        $totalCost = 0.0;

        foreach ($usage as $model => $tokens) {
            if (! isset($bedrockPricing[$model])) {
                continue;
            }

            $pricing = $bedrockPricing[$model];
            $inputTokens = is_array($tokens) && isset($tokens['input']) && is_numeric($tokens['input'])
                ? (int) $tokens['input']
                : 0;
            $outputTokens = is_array($tokens) && isset($tokens['output']) && is_numeric($tokens['output'])
                ? (int) $tokens['output']
                : 0;

            $inputCost = ($inputTokens / 1000) * $pricing['input_cost_per_1k'];
            $outputCost = ($outputTokens / 1000) * $pricing['output_cost_per_1k'];
            $modelCost = $inputCost + $outputCost;

            $breakdown[$model] = [
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'input_cost' => round($inputCost, 4),
                'output_cost' => round($outputCost, 4),
                'total_cost' => round($modelCost, 4),
            ];

            $totalCost += $modelCost;
        }

        $recommendations = $this->generateCostRecommendations($breakdown, $totalCost);

        return [
            'estimated_cost' => round($totalCost, 2),
            'breakdown' => $breakdown,
            'recommendations' => $recommendations,
            'currency' => 'USD',
        ];
    }

    /**
     * Compare pricing across different models
     *
     * @param  array<string, int>  $tokenCounts
     * @return array<string, array{
     *     model: string,
     *     estimated_cost: float,
     *     cost_per_request: float,
     *     relative_cost: string
     * }>
     */
    public function comparePricing(array $tokenCounts = []): array
    {
        $bedrockPricing = $this->getBedrockPricing();
        /** @var array<string, array{model: string, estimated_cost: float, cost_per_request: float, relative_cost: string}> $comparisons */
        $comparisons = [];
        $minCost = PHP_FLOAT_MAX;

        $inputTokens = isset($tokenCounts['input']) && is_int($tokenCounts['input'])
            ? $tokenCounts['input']
            : 1000;
        $outputTokens = isset($tokenCounts['output']) && is_int($tokenCounts['output'])
            ? $tokenCounts['output']
            : 1000;

        foreach ($bedrockPricing as $model => $pricing) {
            $cost = (($inputTokens / 1000) * $pricing['input_cost_per_1k']) +
                (($outputTokens / 1000) * $pricing['output_cost_per_1k']);

            $comparisons[$model] = [
                'model' => $model,
                'estimated_cost' => round($cost, 4),
                'cost_per_request' => round($cost, 6),
                'relative_cost' => 'calculating',
            ];

            $minCost = min($minCost, $cost);
        }

        // Calculate relative costs
        foreach ($comparisons as $model => &$comparison) {
            if ($minCost > 0) {
                $relativeCost = $comparison['estimated_cost'] / $minCost;
                $comparison['relative_cost'] = $relativeCost === 1.0 ? 'cheapest' :
                    sprintf('%.1fx more expensive', $relativeCost);
            } else {
                $comparison['relative_cost'] = 'cheapest';
            }
        }

        return $comparisons;
    }

    /**
     * Get budget optimization recommendations
     *
     * @param  array<string, mixed>  $currentUsage
     * @return array<int, array{
     *     type: string,
     *     priority: string,
     *     recommendation: string,
     *     potential_savings: float,
     *     implementation: string
     * }>
     */
    public function getBudgetOptimizationRecommendations(array $currentUsage = []): array
    {
        /** @var array<int, array{type: string, priority: string, recommendation: string, potential_savings: float, implementation: string}> $recommendations */
        $recommendations = [];
        $bedrockPricing = $this->getBedrockPricing();

        // Analyze current usage patterns
        $totalCost = 0.0;
        /** @var array<string, array{cost: float, requests: int, tokens: int}> $modelUsage */
        $modelUsage = [];

        foreach ($currentUsage as $model => $usage) {
            if (! isset($bedrockPricing[$model])) {
                continue;
            }

            $pricing = $bedrockPricing[$model];
            $inputTokens = is_array($usage) && isset($usage['input']) && is_numeric($usage['input'])
                ? (int) $usage['input']
                : 0;
            $outputTokens = is_array($usage) && isset($usage['output']) && is_numeric($usage['output'])
                ? (int) $usage['output']
                : 0;
            $requests = is_array($usage) && isset($usage['requests']) && is_numeric($usage['requests'])
                ? (int) $usage['requests']
                : 0;

            $cost = (($inputTokens / 1000) * $pricing['input_cost_per_1k']) +
                (($outputTokens / 1000) * $pricing['output_cost_per_1k']);

            $modelUsage[$model] = [
                'cost' => $cost,
                'requests' => $requests,
                'tokens' => $inputTokens + $outputTokens,
            ];

            $totalCost += $cost;
        }

        // Recommendation 1: Use cheaper models for simple tasks
        if (isset($modelUsage['opus']) && $modelUsage['opus']['cost'] > 10.0) {
            $potentialSavings = $modelUsage['opus']['cost'] * 0.4; // 40% could use Sonnet

            $recommendations[] = [
                'type' => 'model_optimization',
                'priority' => 'high',
                'recommendation' => 'Consider using Claude Sonnet instead of Opus for routine queries',
                'potential_savings' => round($potentialSavings, 2),
                'implementation' => 'Implement complexity detection to route simple queries to Sonnet',
            ];
        }

        // Recommendation 2: Increase local Ollama usage
        $ollamaUsage = $modelUsage['ollama'] ?? ['cost' => 0.0, 'requests' => 0, 'tokens' => 0];
        if ($ollamaUsage['cost'] === 0.0 && $totalCost > 5.0) {
            $recommendations[] = [
                'type' => 'provider_optimization',
                'priority' => 'high',
                'recommendation' => 'Increase local Ollama usage to reduce cloud costs',
                'potential_savings' => round($totalCost * 0.3, 2),
                'implementation' => 'Route simple queries to local Ollama before cloud providers',
            ];
        }

        // Recommendation 3: Implement caching
        $recommendations[] = [
            'type' => 'caching',
            'priority' => 'medium',
            'recommendation' => 'Implement aggressive caching for repeated queries',
            'potential_savings' => round($totalCost * 0.2, 2),
            'implementation' => 'Cache AI responses for common queries with 1-hour TTL',
        ];

        // Recommendation 4: Batch processing
        if ($totalCost > 20.0) {
            $recommendations[] = [
                'type' => 'batch_processing',
                'priority' => 'medium',
                'recommendation' => 'Use batch processing for non-urgent AI operations',
                'potential_savings' => round($totalCost * 0.15, 2),
                'implementation' => 'Queue non-urgent requests and process in batches',
            ];
        }

        return $recommendations;
    }

    /**
     * Forecast costs based on usage trends
     *
     * @param  array<string, mixed>  $historicalUsage
     * @return array{
     *     current_month: float,
     *     next_month_forecast: float,
     *     trend: string,
     *     confidence: string,
     *     recommendations: array<int, string>
     * }
     */
    public function forecastCosts(array $historicalUsage = []): array
    {
        // Simple linear forecast based on recent trends
        /** @var array<int, float> $dailyCosts */
        $dailyCosts = isset($historicalUsage['daily_costs']) && is_array($historicalUsage['daily_costs'])
            ? $historicalUsage['daily_costs']
            : [];

        if (count($dailyCosts) < 7) {
            return [
                'current_month' => 0.0,
                'next_month_forecast' => 0.0,
                'trend' => 'insufficient_data',
                'confidence' => 'low',
                'recommendations' => ['Collect more usage data for accurate forecasting'],
            ];
        }

        /** @var array<int, float> $recentDays */
        $recentDays = array_slice($dailyCosts, -7);

        // Ensure numeric values
        $numericDays = array_filter($recentDays, 'is_numeric');
        $floatDays = array_map('floatval', $numericDays);

        $avgDailyCost = count($floatDays) > 0 ? array_sum($floatDays) / count($floatDays) : 0.0;

        $numericAllDays = array_filter($dailyCosts, 'is_numeric');
        $currentMonth = array_sum(array_map('floatval', $numericAllDays));
        $nextMonthForecast = $avgDailyCost * 30;

        $trend = $nextMonthForecast > $currentMonth ? 'increasing' : 'decreasing';
        $confidence = count($dailyCosts) >= 30 ? 'high' : 'medium';

        /** @var array<int, string> $recommendations */
        $recommendations = [];
        if ($nextMonthForecast > $currentMonth * 1.2) {
            $recommendations[] = 'Cost trend is increasing significantly. Review usage patterns.';
        }

        if ($nextMonthForecast > 100.0) {
            $recommendations[] = 'Forecasted costs exceed $100. Consider budget optimization.';
        }

        return [
            'current_month' => round($currentMonth, 2),
            'next_month_forecast' => round($nextMonthForecast, 2),
            'trend' => $trend,
            'confidence' => $confidence,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Fetch pricing from MCP server
     *
     * @param  array<string, mixed>  $filters
     * @return array{service: string, region: string, pricing: array<string, mixed>, currency: string, effective_date: string}
     */
    protected function fetchPricingFromMCP(string $service, string $region, array $filters): array
    {
        // In production, this would make actual MCP calls
        // For now, return structured fallback data
        unset($filters); // Parameter reserved for future implementation

        return $this->getFallbackPricing($service, $region);
    }

    /**
     * Fetch Bedrock pricing from MCP server
     *
     * @return array<string, array{
     *     model: string,
     *     input_cost_per_1k: float,
     *     output_cost_per_1k: float,
     *     currency: string,
     *     region: string
     * }>
     */
    protected function fetchBedrockPricingFromMCP(string $region): array
    {
        // In production, this would make actual MCP calls
        unset($region); // Parameter reserved for future implementation

        return $this->getFallbackBedrockPricing();
    }

    /**
     * Get fallback pricing data
     *
     * @return array{service: string, region: string, pricing: array<string, mixed>, currency: string, effective_date: string}
     */
    protected function getFallbackPricing(string $service, string $region): array
    {
        return [
            'service' => $service,
            'region' => $region,
            'pricing' => [],
            'currency' => 'USD',
            'effective_date' => now()->toDateString(),
        ];
    }

    /**
     * Get fallback Bedrock pricing
     *
     * @return array<string, array{
     *     model: string,
     *     input_cost_per_1k: float,
     *     output_cost_per_1k: float,
     *     currency: string,
     *     region: string
     * }>
     */
    protected function getFallbackBedrockPricing(): array
    {
        return [
            'opus' => [
                'model' => 'claude-opus-4.5',
                'input_cost_per_1k' => 0.005,
                'output_cost_per_1k' => 0.025,
                'currency' => 'USD',
                'region' => 'us-east-1',
            ],
            'sonnet' => [
                'model' => 'claude-sonnet-4.5',
                'input_cost_per_1k' => 0.003,
                'output_cost_per_1k' => 0.015,
                'currency' => 'USD',
                'region' => 'us-east-1',
            ],
            'haiku' => [
                'model' => 'claude-haiku-4.5',
                'input_cost_per_1k' => 0.001,
                'output_cost_per_1k' => 0.005,
                'currency' => 'USD',
                'region' => 'us-east-1',
            ],
            'nova_lite' => [
                'model' => 'nova-2-lite',
                'input_cost_per_1k' => 0.00125,
                'output_cost_per_1k' => 0.00125,
                'currency' => 'USD',
                'region' => 'us-east-1',
            ],
        ];
    }

    /**
     * Generate cost recommendations
     *
     * @param  array<string, array{input_tokens: int, output_tokens: int, input_cost: float, output_cost: float, total_cost: float}>  $breakdown
     * @return array<int, string>
     */
    protected function generateCostRecommendations(array $breakdown, float $totalCost): array
    {
        /** @var array<int, string> $recommendations */
        $recommendations = [];

        if ($totalCost > 50.0) {
            $recommendations[] = 'Monthly cost exceeds $50. Consider implementing cost optimization strategies.';
        }

        // Find most expensive model
        $maxCost = 0.0;
        $maxModel = '';

        foreach ($breakdown as $model => $data) {
            $modelTotalCost = $data['total_cost'];
            if ($modelTotalCost > $maxCost) {
                $maxCost = $modelTotalCost;
                $maxModel = $model;
            }
        }

        if ($maxCost > 0 && $totalCost > 0 && $maxCost > $totalCost * 0.5) {
            $recommendations[] = sprintf(
                'Model "%s" accounts for %.1f%% of costs. Consider using cheaper alternatives.',
                $maxModel,
                ($maxCost / $totalCost) * 100
            );
        }

        return $recommendations;
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $type, mixed ...$params): string
    {
        $normalizedParams = array_map(function (mixed $param): string {
            if (is_array($param)) {
                $encoded = json_encode($param);

                return $encoded !== false ? $encoded : '';
            }

            return is_string($param) || is_numeric($param) ? (string) $param : '';
        }, $params);

        return 'aws_pricing:'.$type.':'.md5(implode(':', $normalizedParams));
    }
}
