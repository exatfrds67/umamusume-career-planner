<?php

declare(strict_types=1);

use App\Services\MCP\AWS\AWSAPIService;
use App\Services\MCP\AWS\AWSIntegrationService;
use App\Services\MCP\AWS\AWSKnowledgeService;
use App\Services\MCP\AWS\AWSPricingService;
use Illuminate\Support\Facades\Cache;

// uses() removed - Pest handles this automatically

beforeEach(function () {
    /** @var AWSPricingService&Mockery\MockInterface $pricingService */
    $pricingService = Mockery::mock(AWSPricingService::class);
    /** @var AWSKnowledgeService&Mockery\MockInterface $knowledgeService */
    $knowledgeService = Mockery::mock(AWSKnowledgeService::class);
    /** @var AWSAPIService&Mockery\MockInterface $apiService */
    $apiService = Mockery::mock(AWSAPIService::class);

    $this->pricingService = $pricingService;
    $this->knowledgeService = $knowledgeService;
    $this->apiService = $apiService;

    $this->service = new AWSIntegrationService(
        $pricingService,
        $knowledgeService,
        $apiService
    );
});

afterEach(function () {
    Cache::flush();
    Mockery::close();
});

describe('AWSIntegrationService', function () {
    it('checks availability of all AWS services', function () {
        $this->pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->knowledgeService->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->apiService->shouldReceive('isAvailable')->once()->andReturn(true);

        $availability = $this->service->checkAvailability();

        expect($availability)->toBeArray()
            ->and($availability)->toHaveKeys(['pricing', 'knowledge', 'api', 'overall'])
            ->and($availability['pricing'])->toBeTrue()
            ->and($availability['knowledge'])->toBeTrue()
            ->and($availability['api'])->toBeTrue()
            ->and($availability['overall'])->toBeTrue();
    });

    it('reports overall availability as true when at least one service is available', function () {
        $this->pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->knowledgeService->shouldReceive('isAvailable')->once()->andReturn(false);
        $this->apiService->shouldReceive('isAvailable')->once()->andReturn(false);

        $availability = $this->service->checkAvailability();

        expect($availability['overall'])->toBeTrue();
    });

    it('gets comprehensive cost analysis', function () {
        $usage = [
            'opus' => ['input' => 10000, 'output' => 5000],
        ];

        $historicalUsage = [
            'daily_costs' => array_fill(0, 30, 5.0),
        ];

        $this->pricingService->shouldReceive('calculateMonthlyCost')
            ->once()
            ->with($usage)
            ->andReturn([
                'estimated_cost' => 100.0,
                'breakdown' => [],
                'recommendations' => [],
                'currency' => 'USD',
            ]);

        $this->pricingService->shouldReceive('getBedrockPricing')
            ->once()
            ->andReturn([
                'opus' => ['input_cost_per_1k' => 0.005, 'output_cost_per_1k' => 0.025],
            ]);

        $this->pricingService->shouldReceive('getBudgetOptimizationRecommendations')
            ->once()
            ->with($usage)
            ->andReturn([]);

        $this->pricingService->shouldReceive('forecastCosts')
            ->once()
            ->with($historicalUsage)
            ->andReturn([
                'current_month' => 150.0,
                'next_month_forecast' => 150.0,
                'trend' => 'stable',
                'confidence' => 'high',
                'recommendations' => [],
            ]);

        $analysis = $this->service->getCostAnalysis($usage, $historicalUsage);

        expect($analysis)->toBeArray()
            ->and($analysis)->toHaveKeys(['current_costs', 'pricing_data', 'optimization_recommendations', 'forecast', 'budget_status'])
            ->and($analysis['current_costs']['estimated_cost'])->toBe(100.0)
            ->and($analysis['budget_status']['current_month'])->toBe(100.0);
    });

    it('gets comprehensive Bedrock guidance', function () {
        $this->knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'best_practices' => [],
            ]);

        $this->apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'operational',
            ]);

        $this->apiService->shouldReceive('listBedrockModels')
            ->once()
            ->andReturn([
                'region' => 'us-east-1',
                'models' => [],
            ]);

        $this->knowledgeService->shouldReceive('getArchitectureRecommendations')
            ->once()
            ->andReturn([
                'architecture_type' => 'hybrid_ai_processing',
            ]);

        $guidance = $this->service->getBedrockGuidance();

        expect($guidance)->toBeArray()
            ->and($guidance)->toHaveKeys(['best_practices', 'health_status', 'available_models', 'architecture_recommendations']);
    });

    it('gets service health dashboard', function () {
        $this->apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'operational',
            ]);

        $this->apiService->shouldReceive('checkServiceQuotas')
            ->once()
            ->with('bedrock')
            ->andReturn([
                'service' => 'bedrock',
                'quotas' => [],
                'warnings' => [],
            ]);

        $this->apiService->shouldReceive('getCloudWatchAlarms')
            ->once()
            ->with('bedrock')
            ->andReturn([
                'alarms' => [],
                'total_count' => 0,
                'active_alarms' => 0,
            ]);

        $dashboard = $this->service->getServiceHealthDashboard();

        expect($dashboard)->toBeArray()
            ->and($dashboard)->toHaveKeys(['bedrock', 'quotas', 'alarms', 'overall_status', 'recommendations'])
            ->and($dashboard['overall_status'])->toBe('healthy');
    });

    it('detects degraded status when service is not operational', function () {
        $this->apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'degraded',
            ]);

        $this->apiService->shouldReceive('checkServiceQuotas')
            ->once()
            ->andReturn([
                'service' => 'bedrock',
                'quotas' => [],
                'warnings' => [],
            ]);

        $this->apiService->shouldReceive('getCloudWatchAlarms')
            ->once()
            ->andReturn([
                'alarms' => [],
                'total_count' => 0,
                'active_alarms' => 0,
            ]);

        $dashboard = $this->service->getServiceHealthDashboard();

        expect($dashboard['overall_status'])->toBe('degraded');
    });

    it('gets optimization recommendations', function () {
        $currentUsage = [
            'opus' => ['input' => 10000, 'output' => 5000],
        ];

        $this->pricingService->shouldReceive('getBudgetOptimizationRecommendations')
            ->once()
            ->with($currentUsage)
            ->andReturn([]);

        $this->knowledgeService->shouldReceive('getArchitectureRecommendations')
            ->once()
            ->andReturn([]);

        $this->knowledgeService->shouldReceive('getSecurityBestPractices')
            ->once()
            ->with('ai_services')
            ->andReturn([
                'practices' => [],
            ]);

        $this->knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'performance_tips' => ['Tip 1', 'Tip 2'],
            ]);

        $recommendations = $this->service->getOptimizationRecommendations($currentUsage);

        expect($recommendations)->toBeArray()
            ->and($recommendations)->toHaveKeys(['cost_optimization', 'architecture_optimization', 'security_recommendations', 'performance_tips', 'priority_actions']);
    });

    it('searches documentation with related content', function () {
        $query = 'bedrock error handling';

        $this->knowledgeService->shouldReceive('searchDocumentation')
            ->once()
            ->with($query)
            ->andReturn([
                'query' => $query,
                'results' => [],
                'total_results' => 0,
                'search_time' => 0.1,
            ]);

        $this->knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
            ]);

        $this->knowledgeService->shouldReceive('getTroubleshootingGuidance')
            ->once()
            ->with($query, 'bedrock')
            ->andReturn([
                'issue' => $query,
                'solutions' => [],
            ]);

        $results = $this->service->searchDocumentation($query);

        expect($results)->toBeArray()
            ->and($results)->toHaveKeys(['query', 'results', 'related_best_practices', 'troubleshooting_guides'])
            ->and($results['query'])->toBe($query);
    });

    it('gets model comparison', function () {
        $tokenCounts = ['input' => 1000, 'output' => 1000];

        $this->pricingService->shouldReceive('comparePricing')
            ->once()
            ->with($tokenCounts)
            ->andReturn([
                'opus' => ['estimated_cost' => 0.03],
                'sonnet' => ['estimated_cost' => 0.018],
                'haiku' => ['estimated_cost' => 0.006],
            ]);

        $this->knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'model_selection' => [
                    'simple_queries' => 'haiku',
                    'balanced' => 'sonnet',
                    'complex_reasoning' => 'opus',
                ],
            ]);

        $comparison = $this->service->getModelComparison($tokenCounts);

        expect($comparison)->toBeArray()
            ->and($comparison)->toHaveKeys(['pricing_comparison', 'model_recommendations', 'best_practices', 'cost_savings_potential'])
            ->and($comparison['cost_savings_potential'])->toBe(0.024); // 0.03 - 0.006
    });

    it('clears caches', function () {
        Cache::put('test_key', 'test_value', 60);

        $this->service->clearCaches();

        expect(Cache::has('test_key'))->toBeFalse();
    });

    it('gets integration statistics', function () {
        $this->pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->knowledgeService->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->apiService->shouldReceive('isAvailable')->once()->andReturn(true);

        Cache::put('aws_integration_rate_limit', 10, 60);

        $stats = $this->service->getStatistics();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKeys(['availability', 'rate_limit_status', 'cache_stats'])
            ->and($stats['rate_limit_status']['current_count'])->toBe(10)
            ->and($stats['rate_limit_status']['remaining'])->toBe(50);
    });
});
