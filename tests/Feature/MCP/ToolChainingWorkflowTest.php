<?php

namespace Tests\Feature\MCP;

use App\Services\MCP\MCPClientService;
use App\Services\MCP\Tools\AWSAPIService;
use App\Services\MCP\Tools\AWSKnowledgeService;
use App\Services\MCP\Tools\AWSPricingService;
use App\Services\MCP\Tools\Context7Service;
use App\Services\MCP\Tools\FetchService;
use App\Services\MCP\Tools\ToolChainingService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

/**
 * Tool Chaining Workflow Feature Tests
 *
 * Tests complex multi-step workflows using MCP tool chaining.
 *
 * Requirements: 13.4, 56.2
 */
class ToolChainingWorkflowTest extends TestCase
{
    use DatabaseMigrations;

    protected ToolChainingService $service;

    protected MCPClientService $mcpClient;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MCPClientService&Mockery\MockInterface $mcpClient */
        $mcpClient = Mockery::mock(MCPClientService::class);
        $mcpClient->shouldReceive('isServerEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);
        $this->mcpClient = $mcpClient;

        $awsPricing = new AWSPricingService($this->mcpClient);
        $awsKnowledge = new AWSKnowledgeService($this->mcpClient);
        $awsAPI = new AWSAPIService($this->mcpClient);
        $context7 = new Context7Service($this->mcpClient);
        $fetch = new FetchService($this->mcpClient);

        $this->service = new ToolChainingService(
            $this->mcpClient,
            $awsPricing,
            $awsKnowledge,
            $awsAPI,
            $context7,
            $fetch
        );

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_simple_chain_workflow(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'getBedrockPricing',
                'params' => ['model_ids' => ['claude-3-5-sonnet']],
            ],
        ];

        $result = $this->service->executeChain($steps);

        expect($result)->toHaveKeys([
            'success',
            'results',
            'workflow',
            'total_duration',
            'context',
        ])
            ->and($result['success'])->toBeTrue()
            ->and($result['results'])->toHaveCount(1)
            ->and($result['workflow'])->toHaveCount(1)
            ->and($result['workflow'][0]['status'])->toBe('success');
    }

    public function test_execute_multi_step_chain_workflow(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'getBedrockPricing',
                'params' => ['model_ids' => ['claude-3-5-sonnet']],
            ],
            [
                'tool' => 'aws_knowledge',
                'method' => 'getBedrockOptimizations',
                'params' => [],
            ],
            [
                'tool' => 'aws_api',
                'method' => 'getBedrockStatus',
                'params' => ['region' => 'us-east-1'],
            ],
        ];

        $result = $this->service->executeChain($steps);

        expect($result['success'])->toBeTrue()
            ->and($result['results'])->toHaveCount(3)
            ->and($result['workflow'])->toHaveCount(3)
            ->and($result['total_duration'])->toBeGreaterThan(0.0);
    }

    public function test_chain_stops_on_failure_by_default(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'nonExistentMethod',
                'params' => [],
            ],
            [
                'tool' => 'aws_knowledge',
                'method' => 'getBedrockOptimizations',
                'params' => [],
            ],
        ];

        $result = $this->service->executeChain($steps);

        expect($result['workflow'])->toHaveCount(1)
            ->and($result['workflow'][0]['status'])->toBe('failed')
            ->and($result['workflow'][0])->toHaveKey('error');
    }

    public function test_chain_continues_on_failure_when_specified(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'nonExistentMethod',
                'params' => [],
                'continue_on_error' => true,
            ],
            [
                'tool' => 'aws_knowledge',
                'method' => 'getBedrockOptimizations',
                'params' => [],
            ],
        ];

        $result = $this->service->executeChain($steps);

        expect($result['workflow'])->toHaveCount(2)
            ->and($result['workflow'][0]['status'])->toBe('failed')
            ->and($result['workflow'][1]['status'])->toBe('success');
    }

    public function test_context_is_shared_across_steps(): void
    {
        $initialContext = ['user_id' => 123, 'session_id' => 'abc'];

        $steps = [
            [
                'tool' => 'context7',
                'method' => 'storeContext',
                'params' => [
                    'conversation_id' => 'test_conv',
                    'context' => $initialContext,
                ],
            ],
            [
                'tool' => 'context7',
                'method' => 'retrieveContext',
                'params' => ['conversation_id' => 'test_conv'],
            ],
        ];

        $result = $this->service->executeChain($steps, $initialContext);

        expect($result['success'])->toBeTrue()
            ->and($result['context'])->toHaveKeys(['user_id', 'session_id']);
    }

    public function test_cost_optimization_workflow(): void
    {
        $usageData = [
            'models' => [
                [
                    'model' => 'claude-3-5-sonnet',
                    'tokens' => 100000,
                    'frequency' => 100,
                ],
            ],
        ];

        $result = $this->service->createCostOptimizationWorkflow($usageData);

        expect($result['success'])->toBeTrue()
            ->and($result['results'])->toHaveCount(3)
            ->and($result['workflow'])->toHaveCount(3);
    }

    public function test_data_fetch_workflow(): void
    {
        $endpoints = ['characters', 'support-cards'];

        $result = $this->service->createDataFetchWorkflow($endpoints);

        expect($result['success'])->toBeTrue()
            ->and($result['workflow'])->toHaveCount(2);
    }

    public function test_context_aware_ai_workflow(): void
    {
        $conversationId = 'test_conversation';
        $aiRequest = ['prompt' => 'Test prompt', 'model' => 'claude-3-5-sonnet'];

        $result = $this->service->createContextAwareAIWorkflow($conversationId, $aiRequest);

        expect($result['success'])->toBeTrue()
            ->and($result['workflow'])->toHaveCount(3);
    }

    public function test_conditional_step_execution(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'getBedrockPricing',
                'params' => ['model_ids' => ['claude-3-5-sonnet']],
            ],
            [
                'tool' => 'aws_knowledge',
                'method' => 'getBedrockOptimizations',
                'params' => [],
                'condition' => 'context.execute_second == true',
            ],
        ];

        // Test with condition false (step should be skipped)
        $result = $this->service->executeChain($steps, ['execute_second' => false]);

        expect($result['workflow'])->toHaveCount(2)
            ->and($result['workflow'][0]['status'])->toBe('success')
            ->and($result['workflow'][1]['status'])->toBe('skipped');

        // Test with condition true (step should execute)
        $result2 = $this->service->executeChain($steps, ['execute_second' => true]);

        expect($result2['workflow'])->toHaveCount(2)
            ->and($result2['workflow'][0]['status'])->toBe('success')
            ->and($result2['workflow'][1]['status'])->toBe('success');
    }

    public function test_get_available_templates(): void
    {
        $templates = $this->service->getAvailableTemplates();

        expect($templates)->toBeArray();
    }

    public function test_get_service_status(): void
    {
        $status = $this->service->getStatus();

        expect($status)->toHaveKeys([
            'enabled',
            'available_tools',
            'template_count',
        ])
            ->and($status['available_tools'])->toHaveKeys([
                'aws_pricing',
                'aws_knowledge',
                'aws_api',
                'context7',
                'fetch',
            ]);
    }

    public function test_workflow_duration_tracking(): void
    {
        $steps = [
            [
                'tool' => 'aws_pricing',
                'method' => 'getBedrockPricing',
                'params' => ['model_ids' => ['claude-3-5-sonnet']],
            ],
        ];

        $result = $this->service->executeChain($steps);

        expect($result['total_duration'])->toBeGreaterThan(0.0)
            ->and($result['workflow'][0])->toHaveKey('duration')
            ->and($result['workflow'][0]['duration'])->toBeGreaterThanOrEqual(0.0);
    }

    public function test_workflow_with_empty_steps(): void
    {
        $result = $this->service->executeChain([]);

        expect($result['success'])->toBeFalse()
            ->and($result['results'])->toBeEmpty()
            ->and($result['workflow'])->toBeEmpty();
    }
}
