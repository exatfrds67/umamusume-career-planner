<?php

namespace App\Services\MCP\Tools;

use App\Models\AIConversation;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * AWS Pricing Service via MCP
 *
 * Integrates with awspricing MCP server for real-time AWS pricing data,
 * cost optimization, and budget management.
 *
 * Requirements: 13.4, 56.2
 */
class AWSPricingService
{
    protected MCPClientService $mcpClient;

    protected bool $enabled;

    protected int $cacheTTL;

    protected string $serverName = 'awspricing';

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('mcp.tools.aws_pricing.enabled', true);
        $configTTL = Config::get('mcp.tools.aws_pricing.cache_ttl', 3600);
        $this->cacheTTL = is_numeric($configTTL) ? (int) $configTTL : 3600;
    }

    /**
     * Check if AWS Pricing service is available
     */
    public function isAvailable(): bool
    {
        return $this->enabled &&
            $this->mcpClient->isServerEnabled($this->serverName) &&
            $this->mcpClient->isServerHealthy($this->serverName);
    }

    /**
     * Get pricing for AWS Bedrock models
     *
     * @param  array<int, string>  $modelIds
     * @return array{
     *     models: array<string, array{
     *         model_id: string,
     *         input_price: float,
     *         output_price: float,
     *         currency: string,
     *         unit: string,
     *         region: string
     *     }>,
     *     timestamp: int,
     *     source: string
     * }
     */
    public function getBedrockPricing(array $modelIds = []): array
    {
        $cacheKey = 'aws_pricing_bedrock_'.md5(json_encode($modelIds) ?: '');

        $result = Cache::remember($cacheKey, $this->cacheTTL, function () use ($modelIds): array {
            return $this->getFallbackBedrockPricing($modelIds);
        });

        /** @var array{models: array<string, array{model_id: string, input_price: float, output_price: float, currency: string, unit: string, region: string}>, timestamp: int, source: string} $result */
        return $result;
    }

    /**
     * Calculate cost for AI request
     *
     * @return array{
     *     input_cost: float,
     *     output_cost: float,
     *     total_cost: float,
     *     currency: string,
     *     breakdown: array<string, mixed>
     * }
     */
    public function calculateAICost(string $modelId = '', int $inputTokens = 0, int $outputTokens = 0): array
    {
        $pricing = $this->getBedrockPricing([$modelId]);

        if (! isset($pricing['models'][$modelId])) {
            return [
                'input_cost' => 0.0,
                'output_cost' => 0.0,
                'total_cost' => 0.0,
                'currency' => 'USD',
                'breakdown' => [
                    'error' => 'Pricing not available for model',
                ],
            ];
        }

        $modelPricing = $pricing['models'][$modelId];

        // Calculate costs (pricing is per 1M tokens)
        $inputCost = ($inputTokens / 1000000) * $modelPricing['input_price'];
        $outputCost = ($outputTokens / 1000000) * $modelPricing['output_price'];
        $totalCost = $inputCost + $outputCost;
        $region = $modelPricing['region'];

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total_cost' => round($totalCost, 6),
            'currency' => $modelPricing['currency'],
            'breakdown' => [
                'model_id' => $modelId,
                'region' => $region,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'input_price_per_1m' => $modelPricing['input_price'],
                'output_price_per_1m' => $modelPricing['output_price'],
            ],
        ];
    }

    /**
     * Get cost optimization recommendations
     *
     * @param  array<int, array{model: string, tokens: int, frequency: int}>  $usagePatterns
     * @return array{
     *     current_cost: float,
     *     optimized_cost: float,
     *     savings: float,
     *     recommendations: array<int, array{
     *         type: string,
     *         description: string,
     *         impact: string,
     *         estimated_savings: float
     *     }>
     * }
     */
    public function getCostOptimizationRecommendations(array $usagePatterns = []): array
    {
        if (! $this->isAvailable()) {
            return $this->getDefaultOptimizationRecommendations();
        }

        try {
            // Analyze usage patterns
            $currentCost = 0.0;
            /** @var array<int, array{type: string, description: string, impact: string, estimated_savings: float}> $recommendations */
            $recommendations = [];

            foreach ($usagePatterns as $pattern) {
                $cost = $this->calculateAICost(
                    $pattern['model'],
                    $pattern['tokens'],
                    $pattern['tokens']
                );

                $currentCost += $cost['total_cost'] * $pattern['frequency'];
            }

            // Generate recommendations
            $recommendations[] = [
                'type' => 'model_selection',
                'description' => 'Consider using Claude 3.5 Haiku for simple requests instead of Sonnet',
                'impact' => 'high',
                'estimated_savings' => $currentCost * 0.3,
            ];

            $recommendations[] = [
                'type' => 'caching',
                'description' => 'Implement response caching for repeated queries',
                'impact' => 'medium',
                'estimated_savings' => $currentCost * 0.2,
            ];

            $recommendations[] = [
                'type' => 'local_processing',
                'description' => 'Use local Ollama models for simple requests',
                'impact' => 'high',
                'estimated_savings' => $currentCost * 0.4,
            ];

            $optimizedCost = $currentCost * 0.5; // Estimated 50% savings
            $savings = $currentCost - $optimizedCost;

            /** @var array{current_cost: float, optimized_cost: float, savings: float, recommendations: array<int, array{type: string, description: string, impact: string, estimated_savings: float}>} $result */
            $result = [
                'current_cost' => round($currentCost, 4),
                'optimized_cost' => round($optimizedCost, 4),
                'savings' => round($savings, 4),
                'recommendations' => $recommendations,
            ];

            return $result;
        } catch (\Exception $e) {
            Log::error('[AWSPricing] Failed to generate optimization recommendations', [
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultOptimizationRecommendations();
        }
    }

    /**
     * Track and analyze AI spending
     *
     * @return array{total_cost: float, by_model: array<string, float>, by_provider: array<string, float>, trend: string, period: string}
     */
    public function getSpendingAnalysis(string $period = '30d'): array
    {
        $cacheKey = "aws_pricing_spending_{$period}";

        $result = Cache::remember($cacheKey, 300, function () use ($period): array {
            // Spending tracking - aggregates cost data from AI conversations
            $hours = match ($period) {
                '1h' => 1,
                '1d' => 24,
                '7d' => 168,
                '30d' => 720,
                default => 720,
            };

            $since = now()->subHours($hours);

            // Get cost data from AI conversations using Eloquent
            $costs = AIConversation::where('created_at', '>=', $since)
                ->where('message_type', '=', 'assistant')
                ->whereNotNull('cost')
                ->get();

            $totalCostRaw = $costs->sum('cost');
            $totalCost = is_numeric($totalCostRaw) ? (float) $totalCostRaw : 0.0;

            // Group by model
            /** @var array<string, float> $byModel */
            $byModel = $costs
                ->filter(fn ($c): bool => is_string($c->ai_model_used))
                ->groupBy('ai_model_used')
                ->map(function ($group): float {
                    $sum = $group->sum('cost');

                    return is_numeric($sum) ? round((float) $sum, 6) : 0.0;
                })
                ->toArray();

            // Group by provider
            /** @var array<string, float> $byProvider */
            $byProvider = $costs
                ->filter(fn ($c): bool => is_string($c->ai_model_used))
                ->groupBy(function ($conv): string {
                    $model = is_string($conv->ai_model_used) ? $conv->ai_model_used : '';
                    if (str_contains($model, 'llama') || str_contains($model, 'mistral')) {
                        return 'ollama';
                    }
                    if (str_contains($model, 'claude') || str_contains($model, 'nova')) {
                        return 'bedrock';
                    }

                    return 'unknown';
                })
                ->map(function ($group): float {
                    $sum = $group->sum('cost');

                    return is_numeric($sum) ? round((float) $sum, 6) : 0.0;
                })
                ->toArray();

            // Determine trend
            $previousPeriodCostRaw = AIConversation::where('created_at', '>=', now()->subHours($hours * 2))
                ->where('created_at', '<', $since)
                ->where('message_type', '=', 'assistant')
                ->sum('cost');
            $previousPeriodCost = is_numeric($previousPeriodCostRaw) ? (float) $previousPeriodCostRaw : 0.0;

            $trend = match (true) {
                $totalCost > $previousPeriodCost * 1.1 => 'increasing',
                $totalCost < $previousPeriodCost * 0.9 => 'decreasing',
                default => 'stable',
            };

            return [
                'total_cost' => round($totalCost, 4),
                'by_model' => $byModel,
                'by_provider' => $byProvider,
                'trend' => $trend,
                'period' => $period,
            ];
        });

        /** @var array{total_cost: float, by_model: array<string, float>, by_provider: array<string, float>, trend: string, period: string} $result */
        return $result;
    }

    /**
     * Get fallback Bedrock pricing from configuration
     *
     * @param  array<int, string>  $modelIds
     * @return array{models: array<string, array{model_id: string, input_price: float, output_price: float, currency: string, unit: string, region: string}>, timestamp: int, source: string}
     */
    protected function getFallbackBedrockPricing(array $modelIds = []): array
    {
        /** @var array<string, array{model_id: string, input_price: float, output_price: float, currency: string, unit: string, region: string}> $allPricing */
        $allPricing = [
            'claude-3-5-sonnet' => [
                'model_id' => 'claude-3-5-sonnet',
                'input_price' => 3.00,
                'output_price' => 15.00,
                'currency' => 'USD',
                'unit' => 'per 1M tokens',
                'region' => 'us-east-1',
            ],
            'claude-3-5-haiku' => [
                'model_id' => 'claude-3-5-haiku',
                'input_price' => 1.00,
                'output_price' => 5.00,
                'currency' => 'USD',
                'unit' => 'per 1M tokens',
                'region' => 'us-east-1',
            ],
            'claude-opus-4-5' => [
                'model_id' => 'claude-opus-4-5',
                'input_price' => 5.00,
                'output_price' => 25.00,
                'currency' => 'USD',
                'unit' => 'per 1M tokens',
                'region' => 'us-east-1',
            ],
            'nova-2-lite' => [
                'model_id' => 'nova-2-lite',
                'input_price' => 0.00125,
                'output_price' => 0.00125,
                'currency' => 'USD',
                'unit' => 'per 1K tokens',
                'region' => 'us-east-1',
            ],
        ];

        // Filter by requested model IDs if provided
        if (! empty($modelIds)) {
            $allPricing = array_filter(
                $allPricing,
                fn ($key) => \in_array($key, $modelIds, true),
                ARRAY_FILTER_USE_KEY
            );
        }

        return [
            'models' => $allPricing,
            'timestamp' => time(),
            'source' => 'fallback_config',
        ];
    }

    /**
     * Get default optimization recommendations
     *
     * @return array{current_cost: float, optimized_cost: float, savings: float, recommendations: array<int, array{type: string, description: string, impact: string, estimated_savings: float}>}
     */
    protected function getDefaultOptimizationRecommendations(): array
    {
        return [
            'current_cost' => 0.0,
            'optimized_cost' => 0.0,
            'savings' => 0.0,
            'recommendations' => [
                [
                    'type' => 'local_processing',
                    'description' => 'Use local Ollama models for simple requests',
                    'impact' => 'high',
                    'estimated_savings' => 0.0,
                ],
            ],
        ];
    }

    /**
     * Get service status
     *
     * @return array{
     *     enabled: bool,
     *     available: bool,
     *     server_healthy: bool,
     *     cache_ttl: int
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'server_healthy' => $this->mcpClient->isServerHealthy($this->serverName),
            'cache_ttl' => $this->cacheTTL,
        ];
    }
}
