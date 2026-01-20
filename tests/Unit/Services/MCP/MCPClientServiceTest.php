<?php

declare(strict_types=1);

use App\Services\MCP\MCPClientService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;

uses(DatabaseMigrations::class);

beforeEach(function () {
    // Set up MCP configuration
    Config::set('mcp.enabled', true);
    Config::set('mcp.debug', false);
    Config::set('mcp.health_check_interval', 300);
    Config::set('mcp.connection_timeout', 10);
    Config::set('mcp.max_concurrent_calls', 5);
    Config::set('mcp.servers', [
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
        'disabled-server' => [
            'enabled' => false,
            'command' => 'some-command',
            'capabilities' => [],
        ],
    ]);

    $this->mcpClient = new MCPClientService;
});

describe('MCPClientService', function () {
    describe('isEnabled', function () {
        it('returns true when MCP is enabled', function () {
            expect($this->mcpClient->isEnabled())->toBeTrue();
        });

        it('returns false when MCP is disabled', function () {
            Config::set('mcp.enabled', false);
            $client = new MCPClientService;

            expect($client->isEnabled())->toBeFalse();
        });
    });

    describe('getServers', function () {
        it('returns all configured servers', function () {
            $servers = $this->mcpClient->getServers();

            expect($servers)->toHaveKeys(['strands-agents', 'agentcore-mcp-server', 'disabled-server']);
        });
    });

    describe('getServer', function () {
        it('returns server configuration by name', function () {
            $server = $this->mcpClient->getServer('strands-agents');

            expect($server)->toBeArray();
            expect($server['enabled'])->toBeTrue();
            expect($server['capabilities'])->toContain('agent_creation');
        });

        it('returns null for non-existent server', function () {
            $server = $this->mcpClient->getServer('non-existent');

            expect($server)->toBeNull();
        });
    });

    describe('isServerEnabled', function () {
        it('returns true for enabled server', function () {
            expect($this->mcpClient->isServerEnabled('strands-agents'))->toBeTrue();
        });

        it('returns false for disabled server', function () {
            expect($this->mcpClient->isServerEnabled('disabled-server'))->toBeFalse();
        });

        it('returns false for non-existent server', function () {
            expect($this->mcpClient->isServerEnabled('non-existent'))->toBeFalse();
        });
    });

    describe('getServerCapabilities', function () {
        it('returns capabilities for server', function () {
            $capabilities = $this->mcpClient->getServerCapabilities('strands-agents');

            expect($capabilities)->toBeArray();
            expect($capabilities)->toContain('agent_creation');
            expect($capabilities)->toContain('tool_execution');
        });

        it('returns empty array for non-existent server', function () {
            $capabilities = $this->mcpClient->getServerCapabilities('non-existent');

            expect($capabilities)->toBeEmpty();
        });
    });

    describe('healthCheck', function () {
        it('returns health status for all servers', function () {
            $results = $this->mcpClient->healthCheck();

            expect($results)->toHaveKeys(['strands-agents', 'agentcore-mcp-server', 'disabled-server']);
        });

        it('marks disabled servers as disabled', function () {
            $results = $this->mcpClient->healthCheck();

            expect($results['disabled-server']['status'])->toBe('disabled');
        });

        it('returns healthy status for properly configured servers', function () {
            $results = $this->mcpClient->healthCheck();

            expect($results['strands-agents']['status'])->toBe('healthy');
            expect($results['strands-agents'])->toHaveKey('capabilities');
        });
    });

    describe('isServerHealthy', function () {
        it('returns false for unknown server', function () {
            expect($this->mcpClient->isServerHealthy('non-existent'))->toBeFalse();
        });

        it('returns health status based on consecutive failures', function () {
            // Perform health check to initialize health tracking
            $this->mcpClient->healthCheck();

            // Server should be healthy after successful check
            expect($this->mcpClient->isServerHealthy('strands-agents'))->toBeTrue();
        });
    });

    describe('isStrandsAgentsAvailable', function () {
        it('returns true when strands-agents is enabled and healthy', function () {
            $this->mcpClient->healthCheck();

            expect($this->mcpClient->isStrandsAgentsAvailable())->toBeTrue();
        });

        it('returns false when strands-agents is disabled', function () {
            Config::set('mcp.servers.strands-agents.enabled', false);
            $client = new MCPClientService;

            expect($client->isStrandsAgentsAvailable())->toBeFalse();
        });
    });

    describe('isAgentCoreAvailable', function () {
        it('returns true when agentcore is enabled and healthy', function () {
            $this->mcpClient->healthCheck();

            expect($this->mcpClient->isAgentCoreAvailable())->toBeTrue();
        });
    });

    describe('getAIServicesStatus', function () {
        it('returns status for both AI services', function () {
            $this->mcpClient->healthCheck();

            $status = $this->mcpClient->getAIServicesStatus();

            expect($status)->toHaveKeys(['strands_agents', 'agentcore']);
            expect($status['strands_agents'])->toBeBool();
            expect($status['agentcore'])->toBeBool();
        });
    });

    describe('getAllServerHealth', function () {
        it('returns health tracking for all servers', function () {
            $this->mcpClient->healthCheck();

            $health = $this->mcpClient->getAllServerHealth();

            expect($health)->toBeArray();
            foreach ($health as $serverHealth) {
                expect($serverHealth)->toHaveKeys(['status', 'last_check', 'consecutive_failures']);
            }
        });
    });

    describe('resetServerHealth', function () {
        it('resets health tracking for a server', function () {
            $this->mcpClient->healthCheck();
            $this->mcpClient->resetServerHealth('strands-agents');

            $health = $this->mcpClient->getServerHealth('strands-agents');

            expect($health['status'])->toBe('unknown');
            expect($health['last_check'])->toBe(0);
            expect($health['consecutive_failures'])->toBe(0);
        });
    });

    describe('getConnectionTimeout', function () {
        it('returns configured connection timeout', function () {
            expect($this->mcpClient->getConnectionTimeout())->toBe(10);
        });
    });

    describe('getMaxConcurrentCalls', function () {
        it('returns configured max concurrent calls', function () {
            expect($this->mcpClient->getMaxConcurrentCalls())->toBe(5);
        });
    });

    describe('executeAgent', function () {
        it('executes agent and returns result', function () {
            $result = $this->mcpClient->executeAgent('training_optimizer', [
                'character_id' => 1,
                'training_type' => 'speed',
            ]);

            expect($result)->toHaveKeys(['status', 'agent_type', 'output']);
            expect($result['status'])->toBe('success');
            expect($result['agent_type'])->toBe('training_optimizer');
        });
    });

    describe('registerAgent', function () {
        it('registers an agent successfully', function () {
            $result = $this->mcpClient->registerAgent(
                'agent-123',
                'training_optimizer',
                ['model' => 'claude-3-5-sonnet']
            );

            expect($result)->toBeTrue();
        });
    });

    describe('unregisterAgent', function () {
        it('unregisters an agent successfully', function () {
            $result = $this->mcpClient->unregisterAgent('agent-123');

            expect($result)->toBeTrue();
        });
    });
});
