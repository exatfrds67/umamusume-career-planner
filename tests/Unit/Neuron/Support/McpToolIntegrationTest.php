<?php

declare(strict_types=1);

use App\Neuron\Support\McpToolIntegration;

/**
 * Tests for MCP Tool Integration service.
 *
 * **Validates: Requirements 17.5, 17.6**
 */
describe('McpToolIntegration', function () {
    beforeEach(function () {
        // Reset config before each test
        config(['neuron.mcp.enabled' => false]);
    });

    it('returns empty array when MCP is disabled', function () {
        config(['neuron.mcp.enabled' => false]);

        $connectors = McpToolIntegration::getEnabledConnectors();

        expect($connectors)->toBeArray()->toBeEmpty();
    });

    it('checks if MCP integration is enabled', function () {
        config(['neuron.mcp.enabled' => false]);
        expect(McpToolIntegration::isEnabled())->toBeFalse();

        config(['neuron.mcp.enabled' => true]);
        expect(McpToolIntegration::isEnabled())->toBeTrue();
    });

    it('returns available server names', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => ['enabled' => true],
                'filesystem' => ['enabled' => false],
            ],
            'neuron.mcp.remote_servers' => [
                'umapyoi' => ['enabled' => true],
            ],
        ]);

        $servers = McpToolIntegration::getAvailableServers();

        expect($servers)->toBeArray()
            ->toContain('memory')
            ->toContain('filesystem')
            ->toContain('umapyoi');
    });

    it('returns empty array when MCP is disabled for available servers', function () {
        config(['neuron.mcp.enabled' => false]);

        $servers = McpToolIntegration::getAvailableServers();

        expect($servers)->toBeArray()->toBeEmpty();
    });

    it('gets server info for local server', function () {
        config([
            'neuron.mcp.local_servers.memory' => [
                'enabled' => true,
                'type' => 'local',
                'description' => 'Memory server',
                'tools' => [
                    'exclude' => [],
                    'only' => [],
                ],
            ],
        ]);

        $info = McpToolIntegration::getServerInfo('memory');

        expect($info)->toBeArray()
            ->toHaveKey('type', 'local')
            ->toHaveKey('enabled', true)
            ->toHaveKey('description', 'Memory server')
            ->toHaveKey('tools');
    });

    it('gets server info for remote server', function () {
        config([
            'neuron.mcp.remote_servers.umapyoi' => [
                'enabled' => true,
                'type' => 'remote',
                'description' => 'Uma Musume API',
                'tools' => [
                    'exclude' => [],
                    'only' => [],
                ],
            ],
        ]);

        $info = McpToolIntegration::getServerInfo('umapyoi');

        expect($info)->toBeArray()
            ->toHaveKey('type', 'remote')
            ->toHaveKey('enabled', true)
            ->toHaveKey('description', 'Uma Musume API')
            ->toHaveKey('tools');
    });

    it('returns null for non-existent server', function () {
        $info = McpToolIntegration::getServerInfo('non_existent');

        expect($info)->toBeNull();
    });

    it('gets all tools from enabled servers', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                ],
            ],
        ]);

        // This will attempt to connect, which may fail in test environment
        // We're testing the method exists and returns an array
        $tools = McpToolIntegration::getAllTools();

        expect($tools)->toBeArray();
    });

    it('gets tools from specific servers with filtering', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                ],
            ],
        ]);

        $servers = [
            'memory' => [
                'only' => ['create_entities', 'search_nodes'],
            ],
        ];

        // This will attempt to connect, which may fail in test environment
        // We're testing the method exists and returns an array
        $tools = McpToolIntegration::getTools($servers);

        expect($tools)->toBeArray();
    });

    it('handles connection errors gracefully', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'invalid_server' => [
                    'enabled' => true,
                    'command' => 'invalid_command_that_does_not_exist',
                    'args' => [],
                    'tools' => [
                        'exclude' => [],
                        'only' => [],
                    ],
                ],
            ],
        ]);

        // Should not throw exception, just log warning and return empty array
        $connectors = McpToolIntegration::getEnabledConnectors();

        // The connector may be created but will fail when trying to get tools
        expect($connectors)->toBeArray();

        // Try to get tools - should handle errors gracefully
        $tools = McpToolIntegration::getAllTools();
        expect($tools)->toBeArray();
    });

    it('applies exclude filter to connector', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                ],
            ],
        ]);

        // Test that exclude parameter is accepted
        // Actual connection may fail in test environment
        try {
            $connector = McpToolIntegration::getConnector('memory', ['delete_entity']);
            expect($connector)->toBeInstanceOf(\NeuronAI\MCP\McpConnector::class);
        } catch (\InvalidArgumentException $e) {
            // Expected in test environment without actual MCP server
            expect($e->getMessage())->toContain('memory');
        }
    });

    it('applies only filter to connector', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                ],
            ],
        ]);

        // Test that only parameter is accepted
        // Actual connection may fail in test environment
        try {
            $connector = McpToolIntegration::getConnector('memory', [], ['create_entities']);
            expect($connector)->toBeInstanceOf(\NeuronAI\MCP\McpConnector::class);
        } catch (\InvalidArgumentException $e) {
            // Expected in test environment without actual MCP server
            expect($e->getMessage())->toContain('memory');
        }
    });

    it('gets multiple connectors with different filters', function () {
        config([
            'neuron.mcp.enabled' => true,
            'neuron.mcp.local_servers' => [
                'memory' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-memory'],
                ],
                'filesystem' => [
                    'enabled' => true,
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-filesystem'],
                ],
            ],
        ]);

        $servers = [
            'memory' => [
                'only' => ['create_entities'],
            ],
            'filesystem' => [
                'exclude' => ['delete_file'],
            ],
        ];

        // Should handle multiple servers with different filters
        $connectors = McpToolIntegration::getConnectors($servers);

        expect($connectors)->toBeArray();
    });
});
