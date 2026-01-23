<?php

declare(strict_types=1);

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\MCP\CostManagementService&Mockery\MockInterface $costManager
 * @property App\Services\MCP\AgentRoutingService $service
 */

use App\Services\MCP\AgentRoutingService;
use App\Services\MCP\CostManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

// uses() removed - Pest handles this automatically

beforeEach(function () {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var CostManagementService&Mockery\MockInterface $costManager */
    $costManager = Mockery::mock(CostManagementService::class);

    $this->mcpClient = $mcpClient;
    $this->costManager = $costManager;
    $this->service = new AgentRoutingService($this->mcpClient, $this->costManager);

    // Clear cache before each test
    Cache::flush();
});

afterEach(function () {
    Mockery::close();
});

describe('Complexity Detection', function () {
    it('detects simple requests correctly', function () {
        $request = [
            'prompt' => 'What is the weather today?',
            'context' => [],
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('detectComplexity');
        $method->setAccessible(true);

        $complexity = $method->invoke($this->service, $request);

        expect($complexity)->toBe(AgentRoutingService::COMPLEXITY_SIMPLE);
    });

    it('detects moderate requests correctly', function () {
        $request = [
            'prompt' => 'Explain how training works in Umamusume',
            'context' => ['character' => 'test'],
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('detectComplexity');
        $method->setAccessible(true);

        $complexity = $method->invoke($this->service, $request);

        expect($complexity)->toBe(AgentRoutingService::COMPLEXITY_MODERATE);
    });

    it('detects complex requests correctly', function () {
        $request = [
            'prompt' => 'Analyze and optimize my training strategy for maximum stat gains',
            'context' => ['character' => 'test', 'stats' => []],
            'requires_rag' => true,
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('detectComplexity');
        $method->setAccessible(true);

        $complexity = $method->invoke($this->service, $request);

        expect($complexity)->toBe(AgentRoutingService::COMPLEXITY_COMPLEX);
    });

    it('detects specialized requests correctly', function () {
        $request = [
            'prompt' => 'Optimize skill build',
            'agent_type' => 'skill_optimization',
            'multi_step' => true,
        ];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('detectComplexity');
        $method->setAccessible(true);

        $complexity = $method->invoke($this->service, $request);

        expect($complexity)->toBe(AgentRoutingService::COMPLEXITY_SPECIALIZED);
    });
});

describe('Provider Selection', function () {
    it('selects Ollama for simple requests when available', function () {
        $request = ['prompt' => 'Simple question'];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => false,
                'usage_percentage' => 50.0,
            ]);

        Config::set('ai.ollama.model', 'llama3');

        $route = $this->service->routeRequest($request);

        expect($route['provider'])->toBe(AgentRoutingService::PROVIDER_OLLAMA)
            ->and($route['model'])->toBe('llama3')
            ->and($route['estimated_cost'])->toBe(0.0);
    });

    it('selects Bedrock for complex requests', function () {
        $request = [
            'prompt' => 'Analyze and optimize my training strategy',
            'requires_rag' => true,
        ];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => false,
                'usage_percentage' => 50.0,
            ]);

        $route = $this->service->routeRequest($request);

        expect($route['provider'])->toBe(AgentRoutingService::PROVIDER_BEDROCK)
            ->and($route['model'])->toContain('sonnet');
    });

    it('selects agent for specialized requests', function () {
        $request = [
            'prompt' => 'Optimize skills',
            'agent_type' => 'skill_optimization',
        ];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => false,
                'usage_percentage' => 50.0,
            ]);

        $route = $this->service->routeRequest($request);

        expect($route['provider'])->toBe(AgentRoutingService::PROVIDER_AGENT)
            ->and($route['model'])->toBe('skill_optimization');
    });

    it('falls back to Ollama when budget is exceeded', function () {
        $request = ['prompt' => 'Test question'];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => true,
                'usage_percentage' => 105.0,
            ]);

        Config::set('ai.ollama.model', 'llama3');

        $route = $this->service->routeRequest($request);

        expect($route['provider'])->toBe(AgentRoutingService::PROVIDER_OLLAMA)
            ->and($route['reason'])->toContain('Budget');
    });
});

describe('Route Caching', function () {
    it('caches routing decisions', function () {
        $request = ['prompt' => 'Test question'];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => false,
                'usage_percentage' => 50.0,
            ]);

        Config::set('ai.ollama.model', 'llama3');

        // First call
        $route1 = $this->service->routeRequest($request);

        // Second call should use cache (no additional mock expectations)
        $route2 = $this->service->routeRequest($request);

        expect($route1)->toEqual($route2);
    });
});

describe('Execution with Fallback', function () {
    it('executes request successfully with primary provider', function () {
        $request = ['prompt' => 'Test question'];

        $this->costManager->shouldReceive('checkBudgetStatus')
            ->once()
            ->andReturn([
                'is_exceeded' => false,
                'usage_percentage' => 50.0,
            ]);

        Config::set('ai.ollama.model', 'llama3');

        // Mock Ollama facade
        $ollamaMock = Mockery::mock('alias:CloudStudio\Ollama\Facades\Ollama');
        $agentMock = Mockery::mock();
        $agentMock->shouldReceive('model')->andReturnSelf();
        $agentMock->shouldReceive('prompt')->andReturnSelf();
        $agentMock->shouldReceive('options')->andReturnSelf();
        $agentMock->shouldReceive('ask')->andReturn('Test response');

        $ollamaMock->shouldReceive('agent')->andReturn($agentMock);

        $result = $this->service->executeWithFallback($request);

        expect($result['success'])->toBeTrue()
            ->and($result['fallback_used'])->toBeFalse()
            ->and($result['provider'])->toBe(AgentRoutingService::PROVIDER_OLLAMA);
    });
});

describe('Token Estimation', function () {
    it('estimates token count correctly', function () {
        $text = str_repeat('word ', 100); // ~400 characters
        $context = ['key' => 'value'];

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('estimateTokenCount');
        $method->setAccessible(true);

        $tokens = $method->invoke($this->service, $text, $context);

        expect($tokens)->toBeGreaterThan(0)
            ->and($tokens)->toBeLessThan(200); // Rough estimate
    });
});

describe('Routing Analytics', function () {
    it('returns empty analytics when no data exists', function () {
        $analytics = $this->service->getRoutingAnalytics();

        expect($analytics['total_requests'])->toBe(0)
            ->and($analytics['by_provider'])->toBeEmpty()
            ->and($analytics['avg_execution_time'])->toBeEmpty()
            ->and($analytics['success_rate'])->toBeEmpty();
    });
});

describe('Performance Thresholds', function () {
    it('checks performance acceptability correctly', function () {
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('isPerformanceAcceptable');
        $method->setAccessible(true);

        // Ollama should be fast
        $acceptable = $method->invoke($this->service, AgentRoutingService::PROVIDER_OLLAMA, 5.0);
        expect($acceptable)->toBeTrue();

        // Ollama too slow
        $notAcceptable = $method->invoke($this->service, AgentRoutingService::PROVIDER_OLLAMA, 20.0);
        expect($notAcceptable)->toBeFalse();

        // Bedrock acceptable
        $acceptable = $method->invoke($this->service, AgentRoutingService::PROVIDER_BEDROCK, 25.0);
        expect($acceptable)->toBeTrue();
    });
});
