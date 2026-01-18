<?php

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\MCP\AgentCore\AgentCoreService $service
 */

use App\Services\MCP\AgentCore\AgentCoreService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Config::set('mcp.agentcore.enabled', true);
    Cache::flush();

    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isAgentCoreAvailable')->andReturn(true);

    $this->mcpClient = $mcpClient;

    $this->service = new AgentCoreService($this->mcpClient);
});

afterEach(function () {
    Mockery::close();
});

test('agent core service checks availability correctly', function () {
    expect($this->service->isAvailable())->toBeTrue();
});

test('agent core service deploys agent successfully', function () {
    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training sequences',
    ];

    $result = $this->service->deployAgent($config);

    expect($result)->toHaveKeys(['agent_id', 'status', 'deployment_time', 'endpoint'])
        ->and($result['status'])->toBe('deployed')
        ->and($result['agent_id'])->not->toBeEmpty();
});

test('agent core service validates agent configuration', function () {
    $invalidConfig = [
        'name' => 'Test Agent',
        // Missing required fields
    ];

    $result = $this->service->deployAgent($invalidConfig);

    expect($result['status'])->toBe('failed')
        ->and($result)->toHaveKey('error');
});

test('agent core service invokes agent successfully', function () {
    // Deploy agent first
    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $deployment = $this->service->deployAgent($config);
    $agentId = $deployment['agent_id'];

    // Invoke agent
    $input = [
        'task' => 'analyze_training',
        'character_id' => 1,
    ];

    $result = $this->service->invokeAgent($agentId, $input);

    expect($result)->toHaveKeys(['output', 'status', 'execution_time', 'tokens_used', 'cost'])
        ->and($result['status'])->toBe('success');
});

test('agent core service gets agent status', function () {
    // Deploy agent first
    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $deployment = $this->service->deployAgent($config);
    $agentId = $deployment['agent_id'];

    // Get status
    $status = $this->service->getAgentStatus($agentId);

    expect($status)->toHaveKeys(['agent_id', 'status', 'metrics', 'health'])
        ->and($status['status'])->toBe('active');
});

test('agent core service terminates agent successfully', function () {
    // Deploy agent first
    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $deployment = $this->service->deployAgent($config);
    $agentId = $deployment['agent_id'];

    // Terminate agent
    $result = $this->service->terminateAgent($agentId);

    expect($result)->toHaveKeys(['agent_id', 'status', 'cleanup_time'])
        ->and($result['status'])->toBe('terminated');
});

test('agent core service lists deployed agents', function () {
    // Deploy multiple agents
    $configs = [
        [
            'name' => 'Agent 1',
            'type' => 'training_optimizer',
            'model' => 'claude-3-5-sonnet',
            'instructions' => 'Optimize training',
        ],
        [
            'name' => 'Agent 2',
            'type' => 'race_analyzer',
            'model' => 'claude-3-5-haiku',
            'instructions' => 'Analyze races',
        ],
    ];

    foreach ($configs as $config) {
        $this->service->deployAgent($config);
    }

    // List agents
    $agents = $this->service->listAgents();

    expect($agents)->toBeArray()
        ->and(count($agents))->toBeGreaterThanOrEqual(2);
});

test('agent core service handles unavailable service gracefully', function () {
    Config::set('mcp.agentcore.enabled', false);

    $service = new AgentCoreService($this->mcpClient);

    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $result = $service->deployAgent($config);

    expect($result['status'])->toBe('unavailable')
        ->and($result)->toHaveKey('error');
});

test('agent core service estimates costs correctly', function () {
    $config = [
        'name' => 'Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $deployment = $this->service->deployAgent($config);
    $agentId = $deployment['agent_id'];

    $input = ['task' => 'analyze', 'data' => str_repeat('test ', 1000)];
    $result = $this->service->invokeAgent($agentId, $input);

    expect($result['cost'])->toBeFloat()
        ->and($result['cost'])->toBeGreaterThan(0.0);
});

test('agent core service provides status information', function () {
    $this->mcpClient->shouldReceive('getServerHealth')
        ->with('agentcore-mcp-server')
        ->andReturn(['status' => 'healthy']);

    $status = $this->service->getStatus();

    expect($status)->toHaveKeys(['enabled', 'available', 'agents_deployed', 'server_health'])
        ->and($status['enabled'])->toBeTrue();
});
