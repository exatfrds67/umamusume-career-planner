<?php

declare(strict_types=1);

use App\Neuron\Support\McpConnectorFactory;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set up base MCP configuration
    Config::set('neuron.mcp.enabled', true);

    Config::set('neuron.mcp.local_servers', [
        'memory' => [
            'enabled' => true,
            'type' => 'local',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-memory'],
            'transport' => 'stdio',
            'tools' => [
                'exclude' => [],
                'only' => [],
            ],
        ],
        'filesystem' => [
            'enabled' => false,
            'type' => 'local',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-filesystem', storage_path('app/neuron')],
            'transport' => 'stdio',
            'tools' => [
                'exclude' => [],
                'only' => [],
            ],
        ],
    ]);

    Config::set('neuron.mcp.remote_servers', [
        'umapyoi' => [
            'enabled' => true,
            'type' => 'remote',
            'url' => 'https://api.umapyoi.net/mcp',
            'token' => 'test-token',
            'transport' => 'sse',
            'tools' => [
                'exclude' => [],
                'only' => ['get_character_data'],
            ],
        ],
        'custom_api' => [
            'enabled' => false,
            'type' => 'remote',
            'url' => 'https://api.example.com/mcp',
            'token' => 'test-token-2',
            'transport' => 'sse',
            'tools' => [
                'exclude' => [],
                'only' => [],
            ],
        ],
    ]);

    Config::set('neuron.mcp.connection', [
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1000,
    ]);
});

describe('McpConnectorFactory', function () {
    describe('getEnabledServers', function () {
        it('returns list of enabled servers', function () {
            $servers = McpConnectorFactory::getEnabledServers();

            expect($servers)->toBeArray()
                ->and($servers)->toContain('memory')
                ->and($servers)->toContain('umapyoi')
                ->and($servers)->not->toContain('filesystem')
                ->and($servers)->not->toContain('custom_api');
        });

        it('returns empty array when MCP is disabled', function () {
            Config::set('neuron.mcp.enabled', false);

            $servers = McpConnectorFactory::getEnabledServers();

            expect($servers)->toBeArray()
                ->and($servers)->toBeEmpty();
        });
    });

    describe('isServerEnabled', function () {
        it('returns true for enabled local server', function () {
            expect(McpConnectorFactory::isServerEnabled('memory'))->toBeTrue();
        });

        it('returns false for disabled local server', function () {
            expect(McpConnectorFactory::isServerEnabled('filesystem'))->toBeFalse();
        });

        it('returns true for enabled remote server', function () {
            expect(McpConnectorFactory::isServerEnabled('umapyoi'))->toBeTrue();
        });

        it('returns false for disabled remote server', function () {
            expect(McpConnectorFactory::isServerEnabled('custom_api'))->toBeFalse();
        });

        it('returns false for non-existent server', function () {
            expect(McpConnectorFactory::isServerEnabled('nonexistent'))->toBeFalse();
        });

        it('returns false when MCP is globally disabled', function () {
            Config::set('neuron.mcp.enabled', false);

            expect(McpConnectorFactory::isServerEnabled('memory'))->toBeFalse();
        });
    });

    describe('make', function () {
        it('throws exception when MCP is disabled', function () {
            Config::set('neuron.mcp.enabled', false);

            McpConnectorFactory::make('memory');
        })->throws(InvalidArgumentException::class, 'MCP connector is disabled in configuration');

        it('throws exception for disabled server', function () {
            McpConnectorFactory::make('filesystem');
        })->throws(InvalidArgumentException::class, "MCP server 'filesystem' is not configured or is disabled");

        it('throws exception for non-existent server', function () {
            McpConnectorFactory::make('nonexistent');
        })->throws(InvalidArgumentException::class, "MCP server 'nonexistent' is not configured or is disabled");
    });
});

describe('MCP Configuration Structure', function () {
    it('has valid local server configuration', function () {
        $config = config('neuron.mcp.local_servers.memory');

        expect($config)->toBeArray()
            ->and($config)->toHaveKeys(['enabled', 'type', 'command', 'args', 'transport', 'tools'])
            ->and($config['type'])->toBe('local')
            ->and($config['command'])->toBeString()
            ->and($config['args'])->toBeArray()
            ->and($config['transport'])->toBe('stdio')
            ->and($config['tools'])->toHaveKeys(['exclude', 'only']);
    });

    it('has valid remote server configuration', function () {
        $config = config('neuron.mcp.remote_servers.umapyoi');

        expect($config)->toBeArray()
            ->and($config)->toHaveKeys(['enabled', 'type', 'url', 'token', 'transport', 'tools'])
            ->and($config['type'])->toBe('remote')
            ->and($config['url'])->toBeString()
            ->and($config['token'])->toBeString()
            ->and($config['transport'])->toBe('sse')
            ->and($config['tools'])->toHaveKeys(['exclude', 'only']);
    });

    it('has valid connection settings', function () {
        $config = config('neuron.mcp.connection');

        expect($config)->toBeArray()
            ->and($config)->toHaveKeys(['timeout', 'retry_attempts', 'retry_delay'])
            ->and($config['timeout'])->toBeInt()
            ->and($config['retry_attempts'])->toBeInt()
            ->and($config['retry_delay'])->toBeInt();
    });
});

describe('MCP Configuration Validation', function () {
    it('validates local server has required command field', function () {
        $config = config('neuron.mcp.local_servers.memory');

        expect($config['command'])->not->toBeEmpty()
            ->and($config['command'])->toBeString();
    });

    it('validates local server has args array', function () {
        $config = config('neuron.mcp.local_servers.memory');

        expect($config['args'])->toBeArray();
    });

    it('validates remote server has required url field', function () {
        $config = config('neuron.mcp.remote_servers.umapyoi');

        expect($config['url'])->not->toBeEmpty()
            ->and($config['url'])->toBeString()
            ->and($config['url'])->toStartWith('https://');
    });

    it('validates remote server supports sse transport', function () {
        $config = config('neuron.mcp.remote_servers.umapyoi');

        expect($config['transport'])->toBe('sse');
    });

    it('validates tool filtering configuration', function () {
        $config = config('neuron.mcp.remote_servers.umapyoi');

        expect($config['tools'])->toBeArray()
            ->and($config['tools']['exclude'])->toBeArray()
            ->and($config['tools']['only'])->toBeArray();
    });
});
