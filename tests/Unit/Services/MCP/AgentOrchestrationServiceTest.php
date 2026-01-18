<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MCP;

use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class AgentOrchestrationServiceTest extends TestCase
{
    protected AgentOrchestrationService $service;

    protected MCPClientService&MockObject $mcpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mcpClient = $this->createMock(MCPClientService::class);
        $this->service = new AgentOrchestrationService($this->mcpClient);

        Cache::flush();
    }

    public function test_creates_workflow_successfully(): void
    {
        $workflow = $this->service->createWorkflow(
            'test-workflow',
            AgentOrchestrationService::PATTERN_SEQUENTIAL,
            [
                ['id' => 'agent1', 'type' => 'analyzer'],
                ['id' => 'agent2', 'type' => 'optimizer'],
            ]
        );

        $this->assertArrayHasKey('id', $workflow);
        $this->assertEquals('test-workflow', $workflow['name']);
        $this->assertEquals(AgentOrchestrationService::PATTERN_SEQUENTIAL, $workflow['pattern']);
        $this->assertCount(2, $workflow['agents']);
        $this->assertEquals(AgentOrchestrationService::STATE_IDLE, $workflow['state']);
    }

    public function test_executes_sequential_workflow(): void
    {
        $this->mcpClient->method('executeAgent')
            ->willReturnOnConsecutiveCalls(
                ['result' => 'step1'],
                ['result' => 'step2']
            );

        $workflow = $this->service->createWorkflow(
            'sequential-test',
            AgentOrchestrationService::PATTERN_SEQUENTIAL,
            [
                ['id' => 'agent1', 'type' => 'step1'],
                ['id' => 'agent2', 'type' => 'step2'],
            ]
        );

        $result = $this->service->executeWorkflow($workflow['id'], ['input' => 'data']);

        $this->assertEquals(AgentOrchestrationService::PATTERN_SEQUENTIAL, $result['pattern']);
        $this->assertArrayHasKey('results', $result);
        /** @var array<int, mixed> $results */
        $results = $result['results'];
        $this->assertCount(2, $results);
    }

    public function test_executes_parallel_workflow(): void
    {
        $this->mcpClient->method('executeAgent')
            ->willReturn(['result' => 'parallel']);

        $workflow = $this->service->createWorkflow(
            'parallel-test',
            AgentOrchestrationService::PATTERN_PARALLEL,
            [
                ['id' => 'agent1', 'type' => 'worker1'],
                ['id' => 'agent2', 'type' => 'worker2'],
            ]
        );

        $result = $this->service->executeWorkflow($workflow['id'], ['input' => 'data']);

        $this->assertEquals(AgentOrchestrationService::PATTERN_PARALLEL, $result['pattern']);
        $this->assertArrayHasKey('results', $result);
        /** @var array<int, mixed> $results */
        $results = $result['results'];
        $this->assertCount(2, $results);
    }

    public function test_executes_hierarchical_workflow(): void
    {
        $this->mcpClient->method('executeAgent')
            ->willReturnOnConsecutiveCalls(
                ['coordinator_output' => 'plan'],
                ['subordinate1' => 'result1'],
                ['subordinate2' => 'result2']
            );

        $workflow = $this->service->createWorkflow(
            'hierarchical-test',
            AgentOrchestrationService::PATTERN_HIERARCHICAL,
            [
                ['id' => 'coordinator', 'type' => 'coordinator'],
                ['id' => 'worker1', 'type' => 'worker'],
                ['id' => 'worker2', 'type' => 'worker'],
            ]
        );

        $result = $this->service->executeWorkflow($workflow['id'], ['input' => 'data']);

        $this->assertEquals(AgentOrchestrationService::PATTERN_HIERARCHICAL, $result['pattern']);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('coordinator', $result['results']);
        $this->assertArrayHasKey('subordinates', $result['results']);
    }

    public function test_executes_collaborative_workflow(): void
    {
        $this->mcpClient->method('executeAgent')
            ->willReturnOnConsecutiveCalls(
                ['shared_data' => 'value1'],
                ['shared_data' => 'value2']
            );

        $workflow = $this->service->createWorkflow(
            'collaborative-test',
            AgentOrchestrationService::PATTERN_COLLABORATIVE,
            [
                ['id' => 'agent1', 'type' => 'collaborator1'],
                ['id' => 'agent2', 'type' => 'collaborator2'],
            ]
        );

        $result = $this->service->executeWorkflow($workflow['id'], ['input' => 'data']);

        $this->assertEquals(AgentOrchestrationService::PATTERN_COLLABORATIVE, $result['pattern']);
        $this->assertArrayHasKey('shared_context', $result);
    }

    public function test_creates_agent_successfully(): void
    {
        $agent = $this->service->createAgent('analyzer', ['config' => 'value']);

        $this->assertArrayHasKey('id', $agent);
        $this->assertEquals('analyzer', $agent['type']);
        $this->assertEquals(AgentOrchestrationService::STATE_IDLE, $agent['state']);
    }

    public function test_monitors_agent_performance(): void
    {
        $agent = $this->service->createAgent('test-agent');

        $monitoring = $this->service->monitorAgent($agent['id']);

        $this->assertArrayHasKey('agent_id', $monitoring);
        $this->assertArrayHasKey('metrics', $monitoring);
        $this->assertArrayHasKey('health', $monitoring);
    }

    public function test_terminates_agent_successfully(): void
    {
        $agent = $this->service->createAgent('test-agent');

        $result = $this->service->terminateAgent($agent['id']);

        $this->assertTrue($result);
    }

    public function test_generates_agent_analytics(): void
    {
        $agent = $this->service->createAgent('test-agent');

        $analytics = $this->service->getAgentAnalytics($agent['id']);

        $this->assertArrayHasKey('agent_id', $analytics);
        $this->assertArrayHasKey('total_executions', $analytics);
        $this->assertArrayHasKey('success_rate', $analytics);
        $this->assertArrayHasKey('average_execution_time', $analytics);
        $this->assertArrayHasKey('recommendations', $analytics);
    }

    public function test_throws_exception_for_unknown_workflow_pattern(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $workflow = $this->service->createWorkflow(
            'invalid-pattern',
            'unknown_pattern',
            [['id' => 'agent1', 'type' => 'test']]
        );

        $this->service->executeWorkflow($workflow['id'], []);
    }

    public function test_throws_exception_for_nonexistent_workflow(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Workflow not found');

        $this->service->executeWorkflow('nonexistent-workflow-id', []);
    }
}
