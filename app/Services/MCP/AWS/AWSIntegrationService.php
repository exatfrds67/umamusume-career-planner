<?php

declare(strict_types=1);

namespace App\Services\MCP\AWS;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AWS Integration Service
 *
 * Unified service layer for AWS MCP tools providing:
 * - Centralized access to AWS Pricing, Knowledge, and API services
 * - Integrated caching and error handling
 * - Rate limiting and retry logic
 * - Comprehensive monitoring and logging
 *
 * Requirements: 56.4, 59.3, 14.1
 */
class AWSIntegrationService
{
    protected const RATE_LIMIT_KEY = 'aws_integration_rate_limit';

    protected const MAX_REQUESTS_PER_MINUTE = 60;

    protected const RETRY_ATTEMPTS = 3;

    protected const RETRY_DELAY_MS = 1000;

    public function __construct(
        private readonly MCPClientService $mcpClient,
        private readonly AWSPricingService $pricingService,
        private readonly AWSKnowledgeService $knowledgeService,
        private readonly AWSAPIService $apiService
    ) {}

    /**
     * Check if AWS MCP services are available
     *
     * @return array{
     *     pricing: bool,
     *     knowledge: bool,
     *     api: bool,
     *     overall: bool
     * }
     */
    public function checkAvailability(): array
    {
        $availability = [
            'pricing' => $this->pricingService->isAvailable(),
            'knowledge' => $this->knowledgeService->isAvailable(),
            'api' => $this->apiService->isAvailable(),
        ];

        $availability['overall'] = $availability['pricing'] ||
            $availability['knowledge'] ||
            $availability['api'];

        return $availability;
    }

    /**
     * Get comprehensive cost analysis
     *
     * @param  array<string, mixed>  $usage
     * @return array{
     *     current_costs: array<string, mixed>,
     *     pricing_data: array<string, mixed>,
     *     optimization_recommendations: array<int, mixed>,
     *     forecast: array<string, mixed>,
     *     budget_status: array<string, mixed>
     * }
     */
    public function getCostAnalysis(array $usage, array $historicalUsage = []): array
    {
        return $this->executeWithRateLimit(function () use ($usage, $historicalUsage) {
            return $this->executeWithRetry(function () use ($usage, $historicalUsage) {
                $currentCosts = $this->pricingService->calculateMonthlyCost($usage);
                $pricingData = $this->pricingService->getBedrockPricing();
                $optimizationRecommendations = $this->pricingService->getBudgetOptimizationRecommendations($usage);
                $forecast = $this->pricingService->forecastCosts($historicalUsage);

                return [
                    'current_costs' => $currentCosts,
                    'pricing_data' => $pricingData,
                    'optimization_recommendations' => $optimizationRecommendations,
                    'forecast' => $forecast,
                    'budget_status' => [
                        'current_month' => $currentCosts['estimated_cost'],
                        'forecast_next_month' => $forecast['next_month_forecast'],
                        'trend' => $forecast['trend'],
                    ],
                ];
            });
        });
    }

    /**
     * Get comprehensive Bedrock guidance
     *
     * @return array{
     *     best_practices: array<string, mixed>,
     *     health_status: array<string, mixed>,
     *     available_models: array<string, mixed>,
     *     architecture_recommendations: array<string, mixed>
     * }
     */
    public function getBedrockGuidance(): array
    {
        return $this->executeWithRateLimit(function () {
            return $this->executeWithRetry(function () {
                $bestPractices = $this->knowledgeService->getBedrockBestPractices();
                $healthStatus = $this->apiService->getBedrockHealth();
                $availableModels = $this->apiService->listBedrockModels();

                $architectureRecommendations = $this->knowledgeService->getArchitectureRecommendations([
                    'workload_type' => 'ai_processing',
                    'expected_load' => 'medium',
                    'budget' => 'moderate',
                ]);

                return [
                    'best_practices' => $bestPractices,
                    'health_status' => $healthStatus,
                    'available_models' => $availableModels,
                    'architecture_recommendations' => $architectureRecommendations,
                ];
            });
        });
    }

    /**
     * Get service health dashboard
     *
     * @return array{
     *     bedrock: array<string, mixed>,
     *     quotas: array<string, mixed>,
     *     alarms: array<string, mixed>,
     *     overall_status: string,
     *     recommendations: array<int, string>
     * }
     */
    public function getServiceHealthDashboard(): array
    {
        return $this->executeWithRateLimit(function () {
            return $this->executeWithRetry(function () {
                $bedrockHealth = $this->apiService->getBedrockHealth();
                $quotas = $this->apiService->checkServiceQuotas('bedrock');
                $alarms = $this->apiService->getCloudWatchAlarms('bedrock');

                $overallStatus = $bedrockHealth['status'] === 'operational' ? 'healthy' : 'degraded';

                $recommendations = [];
                if ($quotas['warnings']) {
                    $recommendations[] = 'Review service quotas - some limits are approaching';
                }

                if ($alarms['active_alarms'] > 0) {
                    $recommendations[] = sprintf('Active alarms detected: %d', $alarms['active_alarms']);
                }

                return [
                    'bedrock' => $bedrockHealth,
                    'quotas' => $quotas,
                    'alarms' => $alarms,
                    'overall_status' => $overallStatus,
                    'recommendations' => $recommendations,
                ];
            });
        });
    }

    /**
     * Get comprehensive optimization recommendations
     *
     * @param  array<string, mixed>  $currentUsage
     * @param  array<string, mixed>  $requirements
     * @return array{
     *     cost_optimization: array<int, mixed>,
     *     architecture_optimization: array<string, mixed>,
     *     security_recommendations: array<string, mixed>,
     *     performance_tips: array<int, string>,
     *     priority_actions: array<int, string>
     * }
     */
    public function getOptimizationRecommendations(array $currentUsage, array $requirements = []): array
    {
        return $this->executeWithRateLimit(function () use ($currentUsage, $requirements) {
            return $this->executeWithRetry(function () use ($currentUsage, $requirements) {
                $costOptimization = $this->pricingService->getBudgetOptimizationRecommendations($currentUsage);

                $architectureOptimization = $this->knowledgeService->getArchitectureRecommendations(
                    array_merge(['workload_type' => 'ai_processing'], $requirements)
                );

                $securityRecommendations = $this->knowledgeService->getSecurityBestPractices('ai_services');

                $bedrockBestPractices = $this->knowledgeService->getBedrockBestPractices();
                $performanceTips = $bedrockBestPractices['performance_tips'] ?? [];

                // Aggregate priority actions
                $priorityActions = [];

                foreach ($costOptimization as $recommendation) {
                    if ($recommendation['priority'] === 'high') {
                        $priorityActions[] = $recommendation['recommendation'];
                    }
                }

                foreach ($securityRecommendations['practices'] as $practice) {
                    if ($practice['severity'] === 'critical') {
                        $priorityActions[] = $practice['title'];
                    }
                }

                return [
                    'cost_optimization' => $costOptimization,
                    'architecture_optimization' => $architectureOptimization,
                    'security_recommendations' => $securityRecommendations,
                    'performance_tips' => $performanceTips,
                    'priority_actions' => array_slice($priorityActions, 0, 5),
                ];
            });
        });
    }

    /**
     * Search AWS documentation across all services
     *
     * @return array{
     *     query: string,
     *     results: array<int, mixed>,
     *     related_best_practices: array<int, mixed>,
     *     troubleshooting_guides: array<int, mixed>
     * }
     */
    public function searchDocumentation(string $query): array
    {
        return $this->executeWithRateLimit(function () use ($query) {
            return $this->executeWithRetry(function () use ($query) {
                $searchResults = $this->knowledgeService->searchDocumentation($query);

                // Get related best practices if query is service-specific
                $relatedBestPractices = [];
                if (str_contains(strtolower($query), 'bedrock')) {
                    $relatedBestPractices = $this->knowledgeService->getBedrockBestPractices();
                }

                // Get troubleshooting guides if query indicates an issue
                $troubleshootingGuides = [];
                if (
                    str_contains(strtolower($query), 'error') ||
                    str_contains(strtolower($query), 'issue') ||
                    str_contains(strtolower($query), 'problem')
                ) {
                    $troubleshootingGuides = [$this->knowledgeService->getTroubleshootingGuidance($query, 'bedrock')];
                }

                return [
                    'query' => $query,
                    'results' => $searchResults['results'],
                    'related_best_practices' => $relatedBestPractices ? [$relatedBestPractices] : [],
                    'troubleshooting_guides' => $troubleshootingGuides,
                ];
            });
        });
    }

    /**
     * Get model comparison and recommendations
     *
     * @param  array<string, int>  $tokenCounts
     * @return array{
     *     pricing_comparison: array<string, mixed>,
     *     model_recommendations: array<string, mixed>,
     *     best_practices: array<string, mixed>,
     *     cost_savings_potential: float
     * }
     */
    public function getModelComparison(array $tokenCounts): array
    {
        return $this->executeWithRateLimit(function () use ($tokenCounts) {
            return $this->executeWithRetry(function () use ($tokenCounts) {
                $pricingComparison = $this->pricingService->comparePricing($tokenCounts);
                $bestPractices = $this->knowledgeService->getBedrockBestPractices();

                // Calculate cost savings potential
                $costs = array_column($pricingComparison, 'estimated_cost');
                if (empty($costs)) {
                    $minCost = 0.0;
                    $maxCost = 0.0;
                } else {
                    $minCost = min($costs);
                    $maxCost = max($costs);
                }
                $costSavingsPotential = $maxCost - $minCost;

                $modelRecommendations = $bestPractices['model_selection'] ?? [];

                return [
                    'pricing_comparison' => $pricingComparison,
                    'model_recommendations' => $modelRecommendations,
                    'best_practices' => $bestPractices,
                    'cost_savings_potential' => round($costSavingsPotential, 4),
                ];
            });
        });
    }

    /**
     * Execute operation with rate limiting
     *
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    protected function executeWithRateLimit(callable $operation): mixed
    {
        $key = self::RATE_LIMIT_KEY;
        $count = (int) Cache::get($key, 0);

        if ($count >= self::MAX_REQUESTS_PER_MINUTE) {
            Log::warning('[AWSIntegration] Rate limit exceeded', [
                'current_count' => $count,
                'limit' => self::MAX_REQUESTS_PER_MINUTE,
            ]);

            throw new \RuntimeException('AWS Integration rate limit exceeded. Please try again later.');
        }

        Cache::put($key, $count + 1, 60);

        return $operation();
    }

    /**
     * Execute operation with retry logic
     *
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    protected function executeWithRetry(callable $operation): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::RETRY_ATTEMPTS; $attempt++) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastException = $e;

                Log::warning('[AWSIntegration] Operation failed, retrying', [
                    'attempt' => $attempt,
                    'max_attempts' => self::RETRY_ATTEMPTS,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < self::RETRY_ATTEMPTS) {
                    usleep(self::RETRY_DELAY_MS * 1000 * $attempt); // Exponential backoff
                }
            }
        }

        if ($lastException === null) {
            throw new \RuntimeException('Operation failed after retries');
        }

        Log::error('[AWSIntegration] Operation failed after all retries', [
            'attempts' => self::RETRY_ATTEMPTS,
            'error' => $lastException->getMessage(),
        ]);

        throw $lastException;
    }

    /**
     * Clear all AWS integration caches
     */
    public function clearCaches(): void
    {
        $patterns = [
            'aws_pricing:*',
            'aws_knowledge:*',
            'aws_api:*',
        ];

        foreach ($patterns as $pattern) {
            Cache::flush(); // In production, use more targeted cache clearing
        }

        Log::info('[AWSIntegration] Caches cleared');
    }

    /**
     * Get integration statistics
     *
     * @return array{
     *     availability: array<string, bool>,
     *     rate_limit_status: array{
     *         current_count: int,
     *         limit: int,
     *         remaining: int
     *     },
     *     cache_stats: array<string, mixed>
     * }
     */
    public function getStatistics(): array
    {
        $availability = $this->checkAvailability();

        $rateLimitCount = (int) Cache::get(self::RATE_LIMIT_KEY, 0);

        return [
            'availability' => $availability,
            'rate_limit_status' => [
                'current_count' => $rateLimitCount,
                'limit' => self::MAX_REQUESTS_PER_MINUTE,
                'remaining' => max(0, self::MAX_REQUESTS_PER_MINUTE - $rateLimitCount),
            ],
            'cache_stats' => [
                'pricing_cache_ttl' => AWSPricingService::CACHE_TTL,
                'knowledge_cache_ttl' => AWSKnowledgeService::CACHE_TTL,
                'api_cache_ttl' => AWSAPIService::CACHE_TTL,
            ],
        ];
    }
}
