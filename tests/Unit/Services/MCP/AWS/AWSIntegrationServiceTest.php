<?php

declare(strict_types=1);

use App\Services\MCP\AWS\AWSAPIService;
use App\Services\MCP\AWS\AWSIntegrationService;
use App\Services\MCP\AWS\AWSKnowledgeService;
use App\Services\MCP\AWS\AWSPricingService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

/** @var MCPClientService&Mockery\MockInterface $mcpClient */
$mcpClient = null;
/** @var AWSPricingService&Mockery\MockInterface $pricingService */
$pricingService = null;
/** @var AWSKnowledgeService&Mockery\MockInterface $knowledgeService */
$knowledgeService = null;
/** @var AWSAPIService&Mockery\MockInterface $apiService */
$apiService = null;
/** @var AWSIntegrationService $service */
$service = null;

beforeEach(function () use (&$mcpClient, &$pricingService, &$knowledgeService, &$apiService, &$service) {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var AWSPricingService&Mockery\MockInterface $pricingService */
    $pricingService = Mockery::mock(AWSPricingService::class);
    /** @var AWSKnowledgeService&Mockery\MockInterface $knowledgeService */
    $knowledgeService = Mockery::mock(AWSKnowledgeService::class);
    /** @var AWSAPIService&Mockery\MockInterface $apiService */
    $apiService = Mockery::mock(AWSAPIService::class);

    $service = new AWSIntegrationService(
        $mcpClient,
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
    it('checks availability of all AWS services', function () use (&$pricingService, &$knowledgeService, &$apiService, &$service) {
        $pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $knowledgeService->shouldReceive('isAvailable')->once()->andReturn(true);
        $apiService->shouldReceive('isAvailable')->once()->andReturn(true);

        $availability = $service->checkAvailability();

        expect($availability)->toBeArray()
            ->and($availability)->toHaveKeys(['pricing', 'knowledge', 'api', 'overall'])
            ->and($availability['pricing'])->toBeTrue()
            ->and($availability['knowledge'])->toBeTrue()
            ->and($availability['api'])->toBeTrue()
            ->and($availability['overall'])->toBeTrue();
    });

    it('reports overall availability as true when at least one service is available', function () use (&$pricingService, &$knowledgeService, &$apiService, &$service) {
        $pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $knowledgeService->shouldReceive('isAvailable')->once()->andReturn(false);
        $apiService->shouldReceive('isAvailable')->once()->andReturn(false);

        $availability = $service->checkAvailability();

        expect($availability['overall'])->toBeTrue();
    });

    it('gets comprehensive cost analysis', function () use (&$pricingService, &$service) {
        $usage = [
            'opus' => ['input' => 10000, 'output' => 5000],
        ];

        $historicalUsage = [
            'daily_costs' => array_fill(0, 30, 5.0),
        ];

        $pricingService->shouldReceive('calculateMonthlyCost')
            ->once()
            ->with($usage)
            ->andReturn([
                'estimated_cost' => 100.0,
                'breakdown' => [],
                'recommendations' => [],
                'currency' => 'USD',
            ]);

        $pricingService->shouldReceive('getBedrockPricing')
            ->once()
            ->andReturn([
                'opus' => ['input_cost_per_1k' => 0.005, 'output_cost_per_1k' => 0.025],
            ]);

        $pricingService->shouldReceive('getBudgetOptimizationRecommendations')
            ->once()
            ->with($usage)
            ->andReturn([]);

        $pricingService->shouldReceive('forecastCosts')
            ->once()
            ->with($historicalUsage)
            ->andReturn([
                'current_month' => 150.0,
                'next_month_forecast' => 150.0,
                'trend' => 'stable',
                'confidence' => 'high',
                'recommendations' => [],
            ]);

        $analysis = $service->getCostAnalysis($usage, $historicalUsage);

        expect($analysis)->toBeArray()
            ->and($analysis)->toHaveKeys(['current_costs', 'pricing_data', 'optimization_recommendations', 'forecast', 'budget_status'])
            ->and($analysis['current_costs']['estimated_cost'])->toBe(100.0)
            ->and($analysis['budget_status']['current_month'])->toBe(100.0);
    });

    it('gets comprehensive Bedrock guidance', function () use (&$knowledgeService, &$apiService, &$service) {
        $knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'best_practices' => [],
            ]);

        $apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'operational',
            ]);

        $apiService->shouldReceive('listBedrockModels')
            ->once()
            ->andReturn([
                'region' => 'us-east-1',
                'models' => [],
            ]);

        $knowledgeService->shouldReceive('getArchitectureRecommendations')
            ->once()
            ->andReturn([
                'architecture_type' => 'hybrid_ai_processing',
            ]);

        $guidance = $service->getBedrockGuidance();

        expect($guidance)->toBeArray()
            ->and($guidance)->toHaveKeys(['best_practices', 'health_status', 'available_models', 'architecture_recommendations']);
    });

    it('gets service health dashboard', function () use (&$apiService, &$service) {
        $apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'operational',
            ]);

        $apiService->shouldReceive('checkServiceQuotas')
            ->once()
            ->with('bedrock')
            ->andReturn([
                'service' => 'bedrock',
                'quotas' => [],
                'warnings' => [],
            ]);

        $apiService->shouldReceive('getCloudWatchAlarms')
            ->once()
            ->with('bedrock')
            ->andReturn([
                'alarms' => [],
                'total_count' => 0,
                'active_alarms' => 0,
            ]);

        $dashboard = $service->getServiceHealthDashboard();

        expect($dashboard)->toBeArray()
            ->and($dashboard)->toHaveKeys(['bedrock', 'quotas', 'alarms', 'overall_status', 'recommendations'])
            ->and($dashboard['overall_status'])->toBe('healthy');
    });

    it('detects degraded status when service is not operational', function () use (&$apiService, &$service) {
        $apiService->shouldReceive('getBedrockHealth')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
                'status' => 'degraded',
            ]);

        $apiService->shouldReceive('checkServiceQuotas')
            ->once()
            ->andReturn([
                'service' => 'bedrock',
                'quotas' => [],
                'warnings' => [],
            ]);

        $apiService->shouldReceive('getCloudWatchAlarms')
            ->once()
            ->andReturn([
                'alarms' => [],
                'total_count' => 0,
                'active_alarms' => 0,
            ]);

        $dashboard = $service->getServiceHealthDashboard();

        expect($dashboard['overall_status'])->toBe('degraded');
    });

    it('gets optimization recommendations', function () use (&$pricingService, &$knowledgeService, &$service) {
        $currentUsage = [
            'opus' => ['input' => 10000, 'output' => 5000],
        ];

        $pricingService->shouldReceive('getBudgetOptimizationRecommendations')
            ->once()
            ->with($currentUsage)
            ->andReturn([]);

        $knowledgeService->shouldReceive('getArchitectureRecommendations')
            ->once()
            ->andReturn([]);

        $knowledgeService->shouldReceive('getSecurityBestPractices')
            ->once()
            ->with('ai_services')
            ->andReturn([
                'practices' => [],
            ]);

        $knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'performance_tips' => ['Tip 1', 'Tip 2'],
            ]);

        $recommendations = $service->getOptimizationRecommendations($currentUsage);

        expect($recommendations)->toBeArray()
            ->and($recommendations)->toHaveKeys(['cost_optimization', 'architecture_optimization', 'security_recommendations', 'performance_tips', 'priority_actions']);
    });

    it('searches documentation with related content', function () use (&$knowledgeService, &$service) {
        $query = 'bedrock error handling';

        $knowledgeService->shouldReceive('searchDocumentation')
            ->once()
            ->with($query)
            ->andReturn([
                'query' => $query,
                'results' => [],
                'total_results' => 0,
                'search_time' => 0.1,
            ]);

        $knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'service' => 'Amazon Bedrock',
            ]);

        $knowledgeService->shouldReceive('getTroubleshootingGuidance')
            ->once()
            ->with($query, 'bedrock')
            ->andReturn([
                'issue' => $query,
                'solutions' => [],
            ]);

        $results = $service->searchDocumentation($query);

        expect($results)->toBeArray()
            ->and($results)->toHaveKeys(['query', 'results', 'related_best_practices', 'troubleshooting_guides'])
            ->and($results['query'])->toBe($query);
    });

    it('gets model comparison', function () use (&$pricingService, &$knowledgeService, &$service) {
        $tokenCounts = ['input' => 1000, 'output' => 1000];

        $pricingService->shouldReceive('comparePricing')
            ->once()
            ->with($tokenCounts)
            ->andReturn([
                'opus' => ['estimated_cost' => 0.03],
                'sonnet' => ['estimated_cost' => 0.018],
                'haiku' => ['estimated_cost' => 0.006],
            ]);

        $knowledgeService->shouldReceive('getBedrockBestPractices')
            ->once()
            ->andReturn([
                'model_selection' => [
                    'simple_queries' => 'haiku',
                    'balanced' => 'sonnet',
                    'complex_reasoning' => 'opus',
                ],
            ]);

        $comparison = $service->getModelComparison($tokenCounts);

        expect($comparison)->toBeArray()
            ->and($comparison)->toHaveKeys(['pricing_comparison', 'model_recommendations', 'best_practices', 'cost_savings_potential'])
            ->and($comparison['cost_savings_potential'])->toBe(0.024); // 0.03 - 0.006
    });

    it('clears caches', function () use (&$service) {
        Cache::put('test_key', 'test_value', 60);

        $service->clearCaches();

        expect(Cache::has('test_key'))->toBeFalse();
    });

    it('gets integration statistics', function () use (&$pricingService, &$knowledgeService, &$apiService, &$service) {
        $pricingService->shouldReceive('isAvailable')->once()->andReturn(true);
        $knowledgeService->shouldReceive('isAvailable')->once()->andReturn(true);
        $apiService->shouldReceive('isAvailable')->once()->andReturn(true);

        Cache::put('aws_integration_rate_limit', 10, 60);

        $stats = $service->getStatistics();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKeys(['availability', 'rate_limit_status', 'cache_stats'])
            ->and($stats['rate_limit_status']['current_count'])->toBe(10)
            ->and($stats['rate_limit_status']['remaining'])->toBe(50);
    });
});
