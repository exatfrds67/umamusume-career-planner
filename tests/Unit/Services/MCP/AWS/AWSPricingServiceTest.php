<?php

declare(strict_types=1);

use App\Services\MCP\AWS\AWSPricingService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

/** @var MCPClientService&Mockery\MockInterface $mcpClient */
$mcpClient = null;
/** @var AWSPricingService $service */
$service = null;

beforeEach(function () use (&$mcpClient, &$service) {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $service = new AWSPricingService($mcpClient);
});

afterEach(function () {
    Cache::flush();
    Mockery::close();
});

describe('AWSPricingService', function () {
    it('checks if service is available', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')
            ->with('awspricing')
            ->once()
            ->andReturn(true);

        $mcpClient->shouldReceive('isServerHealthy')
            ->with('awspricing')
            ->once()
            ->andReturn(true);

        expect($service->isAvailable())->toBeTrue();
    });

    it('returns false when MCP server is disabled', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')
            ->with('awspricing')
            ->once()
            ->andReturn(false);

        expect($service->isAvailable())->toBeFalse();
    });

    it('gets Bedrock pricing data', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $pricing = $service->getBedrockPricing();

        expect($pricing)->toBeArray()
            ->and($pricing)->toHaveKeys(['opus', 'sonnet', 'haiku', 'nova_lite'])
            ->and($pricing['opus'])->toHaveKeys(['model', 'input_cost_per_1k', 'output_cost_per_1k', 'currency', 'region'])
            ->and($pricing['opus']['input_cost_per_1k'])->toBe(0.005)
            ->and($pricing['opus']['output_cost_per_1k'])->toBe(0.025);
    });

    it('calculates monthly cost correctly', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $usage = [
            'opus' => [
                'input' => 10000,
                'output' => 5000,
            ],
            'sonnet' => [
                'input' => 20000,
                'output' => 10000,
            ],
        ];

        $result = $service->calculateMonthlyCost($usage);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['estimated_cost', 'breakdown', 'recommendations', 'currency'])
            ->and($result['currency'])->toBe('USD')
            ->and($result['estimated_cost'])->toBeGreaterThan(0)
            ->and($result['breakdown'])->toHaveKeys(['opus', 'sonnet']);

        // Verify opus calculation: (10000/1000 * 0.005) + (5000/1000 * 0.025) = 0.05 + 0.125 = 0.175
        expect($result['breakdown']['opus']['total_cost'])->toBe(0.175);

        // Verify sonnet calculation: (20000/1000 * 0.003) + (10000/1000 * 0.015) = 0.06 + 0.15 = 0.21
        expect($result['breakdown']['sonnet']['total_cost'])->toBe(0.21);
    });

    it('compares pricing across models', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $tokenCounts = [
            'input' => 1000,
            'output' => 1000,
        ];

        $comparison = $service->comparePricing($tokenCounts);

        expect($comparison)->toBeArray()
            ->and($comparison)->toHaveKeys(['opus', 'sonnet', 'haiku', 'nova_lite']);

        // Verify nova_lite is cheapest
        expect($comparison['nova_lite']['relative_cost'])->toBe('cheapest');

        // Verify all models have required fields
        foreach ($comparison as $model => $data) {
            expect($data)->toHaveKeys(['model', 'estimated_cost', 'cost_per_request', 'relative_cost']);
        }
    });

    it('generates budget optimization recommendations', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $currentUsage = [
            'opus' => [
                'input' => 50000,
                'output' => 25000,
                'requests' => 100,
            ],
        ];

        $recommendations = $service->getBudgetOptimizationRecommendations($currentUsage);

        expect($recommendations)->toBeArray()
            ->and($recommendations)->not->toBeEmpty();

        foreach ($recommendations as $recommendation) {
            expect($recommendation)->toHaveKeys(['type', 'priority', 'recommendation', 'potential_savings', 'implementation'])
                ->and($recommendation['type'])->toBeString()
                ->and($recommendation['priority'])->toBeIn(['high', 'medium', 'low'])
                ->and($recommendation['potential_savings'])->toBeGreaterThanOrEqual(0);
        }
    });

    it('forecasts costs based on historical usage', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $historicalUsage = [
            'daily_costs' => array_fill(0, 30, 5.0), // 30 days of $5/day
        ];

        $forecast = $service->forecastCosts($historicalUsage);

        expect($forecast)->toBeArray()
            ->and($forecast)->toHaveKeys(['current_month', 'next_month_forecast', 'trend', 'confidence', 'recommendations'])
            ->and($forecast['current_month'])->toBe(150.0) // 30 * 5
            ->and($forecast['next_month_forecast'])->toBe(150.0) // Same trend
            ->and($forecast['trend'])->toBeIn(['increasing', 'decreasing', 'insufficient_data'])
            ->and($forecast['confidence'])->toBeIn(['high', 'medium', 'low']);
    });

    it('handles insufficient data for forecasting', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $historicalUsage = [
            'daily_costs' => [5.0, 6.0], // Only 2 days
        ];

        $forecast = $service->forecastCosts($historicalUsage);

        expect($forecast['trend'])->toBe('insufficient_data')
            ->and($forecast['confidence'])->toBe('low')
            ->and($forecast['recommendations'])->toContain('Collect more usage data for accurate forecasting');
    });

    it('caches pricing data', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        // First call should cache
        $pricing1 = $service->getBedrockPricing();

        // Second call should use cache
        $pricing2 = $service->getBedrockPricing();

        expect($pricing1)->toBe($pricing2);
    });

    it('uses fallback when MCP server is unavailable', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(false);

        $pricing = $service->getBedrockPricing();

        expect($pricing)->toBeArray()
            ->and($pricing)->toHaveKeys(['opus', 'sonnet', 'haiku', 'nova_lite']);
    });

    it('generates cost recommendations for high usage', function () use (&$mcpClient, &$service) {
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

        $usage = [
            'opus' => [
                'input' => 10000000, // 10M tokens
                'output' => 5000000, // 5M tokens
            ],
        ];

        $result = $service->calculateMonthlyCost($usage);

        expect($result['recommendations'])->not->toBeEmpty()
            ->and($result['estimated_cost'])->toBeGreaterThan(50.0);
    });
});
