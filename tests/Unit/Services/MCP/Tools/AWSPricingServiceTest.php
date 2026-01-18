<?php

namespace Tests\Unit\Services\MCP\Tools;

use App\Services\MCP\MCPClientService;
use App\Services\MCP\Tools\AWSPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * AWS Pricing Service Tests
 *
 * Tests AWS pricing integration, cost calculations, and optimization recommendations.
 *
 * Requirements: 13.4, 56.2
 */
class AWSPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MCPClientService&MockObject $mcpClient;

    protected AWSPricingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mcpClient = $this->createMock(MCPClientService::class);
        $this->service = new AWSPricingService($this->mcpClient);

        // Clear cache before each test
        Cache::flush();
    }

    public function test_service_availability_check(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(true);
        $this->mcpClient->method('isServerHealthy')->willReturn(true);

        expect($this->service->isAvailable())->toBeTrue();
    }

    public function test_service_unavailable_when_disabled(): void
    {
        Config::set('mcp.tools.aws_pricing.enabled', false);
        $service = new AWSPricingService($this->mcpClient);

        expect($service->isAvailable())->toBeFalse();
    }

    public function test_get_bedrock_pricing_returns_fallback_data(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $pricing = $this->service->getBedrockPricing(['claude-3-5-sonnet']);

        expect($pricing)->toHaveKeys(['models', 'timestamp', 'source'])
            ->and($pricing['models'])->toHaveKey('claude-3-5-sonnet')
            ->and($pricing['models']['claude-3-5-sonnet'])->toHaveKeys([
                'model_id',
                'input_price',
                'output_price',
                'currency',
                'unit',
                'region',
            ])
            ->and($pricing['source'])->toBe('fallback_config');
    }

    public function test_calculate_ai_cost_for_claude_sonnet(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $cost = $this->service->calculateAICost(
            'claude-3-5-sonnet',
            1000000, // 1M input tokens
            500000   // 500K output tokens
        );

        expect($cost)->toHaveKeys([
            'input_cost',
            'output_cost',
            'total_cost',
            'currency',
            'breakdown',
        ])
            ->and($cost['input_cost'])->toBe(3.0) // $3 per 1M tokens
            ->and($cost['output_cost'])->toBe(7.5) // $15 per 1M tokens * 0.5M
            ->and($cost['total_cost'])->toBe(10.5)
            ->and($cost['currency'])->toBe('USD');
    }

    public function test_calculate_ai_cost_for_nova_lite(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $cost = $this->service->calculateAICost(
            'nova-2-lite',
            1000, // 1K tokens
            1000  // 1K tokens
        );

        // Nova pricing is per 1K tokens, not 1M
        expect($cost['total_cost'])->toBeGreaterThan(0.0)
            ->and($cost['currency'])->toBe('USD');
    }

    public function test_calculate_ai_cost_for_unknown_model(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $cost = $this->service->calculateAICost(
            'unknown-model',
            1000000,
            500000
        );

        expect($cost)->toHaveKey('breakdown')
            ->and($cost['breakdown'])->toHaveKey('error')
            ->and($cost['total_cost'])->toBe(0.0);
    }

    public function test_get_cost_optimization_recommendations(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $usagePatterns = [
            [
                'model' => 'claude-3-5-sonnet',
                'tokens' => 100000,
                'frequency' => 100,
            ],
        ];

        $recommendations = $this->service->getCostOptimizationRecommendations($usagePatterns);

        expect($recommendations)->toHaveKeys([
            'current_cost',
            'optimized_cost',
            'savings',
            'recommendations',
        ])
            ->and($recommendations['recommendations'])->toBeArray()
            ->and($recommendations['recommendations'])->not->toBeEmpty()
            ->and($recommendations['savings'])->toBeGreaterThanOrEqual(0.0);
    }

    public function test_recommendations_include_expected_types(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        $usagePatterns = [
            [
                'model' => 'claude-3-5-sonnet',
                'tokens' => 100000,
                'frequency' => 100,
            ],
        ];

        $recommendations = $this->service->getCostOptimizationRecommendations($usagePatterns);

        expect($recommendations['recommendations'])->toBeArray()
            ->and($recommendations['recommendations'])->not->toBeEmpty();

        // Check that each recommendation has required fields
        foreach ($recommendations['recommendations'] as $rec) {
            expect($rec)->toHaveKeys(['type', 'description', 'impact', 'estimated_savings']);
        }
    }

    public function test_get_spending_analysis_returns_structure(): void
    {
        $analysis = $this->service->getSpendingAnalysis('30d');

        expect($analysis)->toHaveKeys([
            'total_cost',
            'by_model',
            'by_provider',
            'trend',
            'period',
        ])
            ->and($analysis['period'])->toBe('30d');
    }

    public function test_pricing_data_is_cached(): void
    {
        $this->mcpClient->method('isServerEnabled')->willReturn(false);

        // First call
        $pricing1 = $this->service->getBedrockPricing(['claude-3-5-sonnet']);

        // Second call should use cache
        $pricing2 = $this->service->getBedrockPricing(['claude-3-5-sonnet']);

        expect($pricing1)->toEqual($pricing2);
    }

    public function test_get_status_returns_service_info(): void
    {
        $this->mcpClient->method('isServerHealthy')->willReturn(true);

        $status = $this->service->getStatus();

        expect($status)->toHaveKeys([
            'enabled',
            'available',
            'server_healthy',
            'cache_ttl',
        ])
            ->and($status['enabled'])->toBeTrue()
            ->and($status['cache_ttl'])->toBeInt();
    }
}
