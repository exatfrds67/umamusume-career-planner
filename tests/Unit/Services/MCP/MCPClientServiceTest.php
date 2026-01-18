<?php

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('mcp.enabled', true);
    Config::set('mcp.debug', false);
    Config::set('mcp.health_check_interval', 300);
    Config::set('mcp.connection_timeout', 10);
    Config::set('mcp.max_concurrent_calls', 5);

    Config::set('mcp.servers', [
        'strands-agents' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['strands-agents@latest'],
            'capabilities' => ['agent_creation', 'workflow_management', 'multi_model_support'],
        ],
        'agentcore-mcp-server' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['agentcore-mcp-server@latest'],
            'capabilities' => ['agent_deployment', 'performance_monitoring', 'scaling'],
        ],
        'test-disabled' => [
            'enabled' => false,
            'command' => 'test',
            'args' => [],
            'capabilities' => [],
        ],
    ]);

    Cache::flush();
});

describe('MCPClientService', function () {
    it('initializes with correct configuration', function () {
        $service = new MCPClientService;

        expect($service->isEnabled())->toBeTrue()
            ->and($service->getConnectionTimeout())->toBe(10)
            ->and($service->getMaxConcurrentCalls())->toBe(5);
    });

    it('returns all configured servers', function () {
        $service = new MCPClientService;
        $servers = $service->getServers();

        expect($servers)->toBeArray()
            ->and($servers)->toHaveKeys(['strands-agents', 'agentcore-mcp-server', 'test-disabled']);
    });

    it('returns specific server configuration', function () {
        $service = new MCPClientService;
        $server = $service->getServer('strands-agents');

        expect($server)->not->toBeNull();
        /** @var array{enabled: bool, command: string, capabilities: array<int, string>} $server */
        expect($server)->toBeArray()
            ->and($server['enabled'])->toBeTrue()
            ->and($server['command'])->toBe('uvx')
            ->and($server['capabilities'])->toContain('agent_creation');
    });

    it('returns null for non-existent server', function () {
        $service = new MCPClientService;
        $server = $service->getServer('non-existent');

        expect($server)->toBeNull();
    });

    it('checks if server is enabled', function () {
        $service = new MCPClientService;

        expect($service->isServerEnabled('strands-agents'))->toBeTrue()
            ->and($service->isServerEnabled('test-disabled'))->toBeFalse()
            ->and($service->isServerEnabled('non-existent'))->toBeFalse();
    });

    it('returns server capabilities', function () {
        $service = new MCPClientService;
        $capabilities = $service->getServerCapabilities('strands-agents');

        expect($capabilities)->toBeArray()
            ->and($capabilities)->toContain('agent_creation')
            ->and($capabilities)->toContain('workflow_management')
            ->and($capabilities)->toContain('multi_model_support');
    });

    it('returns empty array for non-existent server capabilities', function () {
        $service = new MCPClientService;
        $capabilities = $service->getServerCapabilities('non-existent');

        expect($capabilities)->toBeArray()
            ->and($capabilities)->toBeEmpty();
    });

    it('performs health check on all servers', function () {
        $service = new MCPClientService;
        $results = $service->healthCheck();
        /** @var array<string, array{status: string, last_check: int, consecutive_failures: int, message?: string}> $results */
        expect($results)->toBeArray()
            ->and($results)->toHaveKeys(['strands-agents', 'agentcore-mcp-server', 'test-disabled'])
            ->and($results['strands-agents']['status'])->toBe('healthy')
            ->and($results['test-disabled']['status'])->toBe('disabled');
    });

    it('caches health check results', function () {
        $service = new MCPClientService;

        // First health check
        $results1 = $service->healthCheck();

        // Second health check should use cached results
        $results2 = $service->healthCheck();

        expect($results2['strands-agents']['message'])->toBe('Using cached health status');
    });

    it('tracks server health status', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        $health = $service->getServerHealth('strands-agents');

        expect($health)->not->toBeNull();
        /** @var array{status: string, last_check: int, consecutive_failures: int} $health */
        expect($health)->toBeArray()
            ->and($health)->toHaveKeys(['status', 'last_check', 'consecutive_failures'])
            ->and($health['status'])->toBe('healthy')
            ->and($health['consecutive_failures'])->toBe(0);
    });

    it('checks if server is healthy', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        expect($service->isServerHealthy('strands-agents'))->toBeTrue()
            ->and($service->isServerHealthy('test-disabled'))->toBeFalse();
    });

    it('resets server health tracking', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        $service->resetServerHealth('strands-agents');
        $health = $service->getServerHealth('strands-agents');

        expect($health)->not->toBeNull();
        /** @var array{status: string, last_check: int, consecutive_failures: int} $health */
        expect($health['status'])->toBe('unknown')
            ->and($health['last_check'])->toBe(0)
            ->and($health['consecutive_failures'])->toBe(0);
    });

    it('checks strands-agents availability', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        expect($service->isStrandsAgentsAvailable())->toBeTrue();
    });

    it('checks agentcore availability', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        expect($service->isAgentCoreAvailable())->toBeTrue();
    });

    it('returns AI services status', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        $status = $service->getAIServicesStatus();

        expect($status)->toBeArray()
            ->and($status)->toHaveKeys(['strands_agents', 'agentcore'])
            ->and($status['strands_agents'])->toBeTrue()
            ->and($status['agentcore'])->toBeTrue();
    });

    it('returns all server health statuses', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        $allHealth = $service->getAllServerHealth();

        expect($allHealth)->toBeArray()
            ->and($allHealth)->toHaveKeys(['strands-agents', 'agentcore-mcp-server', 'test-disabled']);
    });

    it('handles disabled MCP system', function () {
        Config::set('mcp.enabled', false);
        $service = new MCPClientService;

        expect($service->isEnabled())->toBeFalse();
    });

    it('handles missing server configuration', function () {
        Config::set('mcp.servers', []);
        $service = new MCPClientService;

        $servers = $service->getServers();
        expect($servers)->toBeArray()->and($servers)->toBeEmpty();
    });

    it('handles server with missing command', function () {
        Config::set('mcp.servers', [
            'invalid-server' => [
                'enabled' => true,
                'capabilities' => [],
            ],
        ]);

        $service = new MCPClientService;
        $results = $service->healthCheck();
        /** @var array<string, array{status: string, last_check: int, consecutive_failures: int, message?: string}> $results */
        expect($results['invalid-server']['status'])->toBe('unhealthy')
            ->and($results['invalid-server']['message'])->toBe('Server command not configured');
    });
});

describe('MCPClientService Health Monitoring', function () {
    it('detects when server needs reconnection', function () {
        $service = new MCPClientService;

        // Manually set unhealthy status with old timestamp
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('serverHealth');
        $property->setAccessible(true);

        $health = $property->getValue($service);
        $health['strands-agents'] = [
            'status' => 'unhealthy',
            'last_check' => time() - 120, // 2 minutes ago
            'consecutive_failures' => 2,
        ];
        $property->setValue($service, $health);

        expect($service->needsReconnection('strands-agents'))->toBeTrue();
    });

    it('does not reconnect healthy servers', function () {
        $service = new MCPClientService;
        $service->healthCheck();

        expect($service->needsReconnection('strands-agents'))->toBeFalse();
    });

    it('tracks consecutive failures', function () {
        Config::set('mcp.servers', [
            'failing-server' => [
                'enabled' => true,
                'command' => '', // Missing command will cause failure
                'capabilities' => [],
            ],
        ]);

        $service = new MCPClientService;
        $service->healthCheck();

        $health = $service->getServerHealth('failing-server');

        expect($health)->not->toBeNull();
        /** @var array{status: string, last_check: int, consecutive_failures: int} $health */
        expect($health['status'])->toBe('unhealthy')
            ->and($health['consecutive_failures'])->toBeGreaterThan(0);
    });
});

/**
 * Validates: Requirements 56.1, 56.2
 *
 * These tests verify:
 * - MCP client configuration for AI services integration (56.1)
 * - Health monitoring and automatic reconnection capabilities (56.2)
 * - strands-agents and agentcore-mcp-server integration (56.1)
 * - Server availability checking and status tracking (56.2)
 */
