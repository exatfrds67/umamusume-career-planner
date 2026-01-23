<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Cache::flush();

    // Configure MCP servers for integration testing
    Config::set('mcp.enabled', true);
    Config::set('mcp.debug', false);
    Config::set('mcp.health_check_interval', 300);
    Config::set('mcp.connection_timeout', 10);
    Config::set('mcp.max_concurrent_calls', 5);
    Config::set('mcp.servers', [
        'fetch' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['mcp-server-fetch'],
            'capabilities' => ['http_client', 'external_api_integration'],
        ],
        'strands-agents' => [
            'enabled' => true,
            'command' => 'npx @anthropic/strands-agents',
            'capabilities' => ['agent_creation', 'tool_execution'],
        ],
        'agentcore-mcp-server' => [
            'enabled' => true,
            'command' => 'npx @aws/agentcore-mcp-server',
            'capabilities' => ['bedrock_integration', 'agent_orchestration'],
        ],
    ]);

    $this->mcpClient = new MCPClientService;
    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
});

describe('MCP Server Integration', function () {
    describe('Server Connectivity', function () {
        it('initializes MCP client with configured servers', function () {
            $servers = $this->mcpClient->getServers();

            expect($servers)->toHaveKey('fetch')
                ->and($servers)->toHaveKey('strands-agents')
                ->and($servers)->toHaveKey('agentcore-mcp-server');
        });

        it('performs health check on all configured servers', function () {
            $healthResults = $this->mcpClient->healthCheck();

            expect($healthResults)->toBeArray()
                ->and($healthResults)->toHaveKey('fetch')
                ->and($healthResults)->toHaveKey('strands-agents')
                ->and($healthResults)->toHaveKey('agentcore-mcp-server');

            foreach ($healthResults as $serverName => $result) {
                expect($result)->toHaveKey('status')
                    ->and($result)->toHaveKey('message');
            }
        });

        it('tracks server health status over time', function () {
            // First health check
            $this->mcpClient->healthCheck();

            $health = $this->mcpClient->getAllServerHealth();

            expect($health)->toBeArray();

            foreach ($health as $serverHealth) {
                expect($serverHealth)->toHaveKey('status')
                    ->and($serverHealth)->toHaveKey('last_check')
                    ->and($serverHealth)->toHaveKey('consecutive_failures');
            }
        });

        it('resets server health tracking', function () {
            $this->mcpClient->healthCheck();
            $this->mcpClient->resetServerHealth('fetch');

            $health = $this->mcpClient->getServerHealth('fetch');

            expect($health['status'])->toBe('unknown')
                ->and($health['last_check'])->toBe(0)
                ->and($health['consecutive_failures'])->toBe(0);
        });
    });

    describe('Fetch Server Operations', function () {
        it('checks fetch server availability', function () {
            $this->mcpClient->healthCheck();

            $isAvailable = $this->mcpClient->isFetchAvailable();

            expect($isAvailable)->toBeBool();
        });

        it('retrieves fetch server configuration', function () {
            $config = $this->mcpClient->getFetchServerConfig();

            expect($config)->toBeArray()
                ->and($config)->toHaveKey('enabled')
                ->and($config)->toHaveKey('command')
                ->and($config)->toHaveKey('capabilities');
        });

        it('performs simulated HTTP GET request', function () {
            $this->mcpClient->healthCheck();

            $response = $this->mcpClient->get('https://api.example.com/test');

            expect($response)->toHaveKey('success')
                ->and($response)->toHaveKey('status')
                ->and($response)->toHaveKey('method')
                ->and($response['method'])->toBe('GET');
        });

        it('performs simulated HTTP POST request', function () {
            $this->mcpClient->healthCheck();

            $response = $this->mcpClient->post('https://api.example.com/test', [
                'data' => 'test_value',
            ]);

            expect($response)->toHaveKey('success')
                ->and($response)->toHaveKey('method')
                ->and($response['method'])->toBe('POST');
        });

        it('handles fetch with JSON decoding', function () {
            $this->mcpClient->healthCheck();

            $response = $this->mcpClient->fetchJson('https://api.example.com/test');

            expect($response)->toHaveKey('success')
                ->and($response)->toHaveKey('data')
                ->and($response['data'])->toBeArray();
        });
    });

    describe('Agent Services Integration', function () {
        it('checks strands-agents availability', function () {
            $this->mcpClient->healthCheck();

            $isAvailable = $this->mcpClient->isStrandsAgentsAvailable();

            expect($isAvailable)->toBeBool();
        });

        it('checks agentcore availability', function () {
            $this->mcpClient->healthCheck();

            $isAvailable = $this->mcpClient->isAgentCoreAvailable();

            expect($isAvailable)->toBeBool();
        });

        it('retrieves AI services status', function () {
            $this->mcpClient->healthCheck();

            $status = $this->mcpClient->getAIServicesStatus();

            expect($status)->toHaveKey('strands_agents')
                ->and($status)->toHaveKey('agentcore')
                ->and($status['strands_agents'])->toBeBool()
                ->and($status['agentcore'])->toBeBool();
        });

        it('executes agent via MCP', function () {
            $result = $this->mcpClient->executeAgent('data_fetching', [
                'resource_type' => 'character',
                'identifier' => 'test_character',
            ]);

            expect($result)->toHaveKey('status')
                ->and($result)->toHaveKey('agent_type')
                ->and($result['agent_type'])->toBe('data_fetching');
        });

        it('registers and unregisters agents', function () {
            $registered = $this->mcpClient->registerAgent(
                'test-agent-001',
                'data_validation',
                ['model' => 'claude-3-5-sonnet']
            );

            expect($registered)->toBeTrue();

            $unregistered = $this->mcpClient->unregisterAgent('test-agent-001');

            expect($unregistered)->toBeTrue();
        });
    });

    describe('MCP Tool Calls', function () {
        it('calls MCP tool on fetch server', function () {
            $this->mcpClient->healthCheck();

            $result = $this->mcpClient->callTool('fetch', 'fetch', [
                'url' => 'https://api.example.com/data',
                'method' => 'GET',
            ]);

            expect($result)->toHaveKey('success')
                ->and($result)->toHaveKey('server')
                ->and($result)->toHaveKey('tool')
                ->and($result['server'])->toBe('fetch')
                ->and($result['tool'])->toBe('fetch');
        });

        it('throws exception for disabled server', function () {
            Config::set('mcp.servers.fetch.enabled', false);
            $client = new MCPClientService;

            expect(fn () => $client->callTool('fetch', 'fetch', []))
                ->toThrow(\RuntimeException::class);
        });

        it('throws exception when MCP is disabled', function () {
            Config::set('mcp.enabled', false);
            $client = new MCPClientService;

            expect(fn () => $client->callTool('fetch', 'fetch', []))
                ->toThrow(\RuntimeException::class, 'MCP is not enabled');
        });
    });
});
