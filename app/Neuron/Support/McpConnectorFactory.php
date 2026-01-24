<?php

declare(strict_types=1);

namespace App\Neuron\Support;

use InvalidArgumentException;
use NeuronAI\MCP\McpConnector;

/**
 * Factory class for creating MCP connector instances from configuration.
 *
 * This class provides a convenient way to create MCP connectors for both
 * local and remote MCP servers based on the application configuration.
 */
class McpConnectorFactory
{
    /**
     * Create an MCP connector for a configured server.
     *
     * @param  string  $serverName  The name of the server from config
     *
     * @throws InvalidArgumentException If server is not configured or disabled
     */
    public static function make(string $serverName): McpConnector
    {
        // Check if MCP is enabled globally
        if (! config('neuron.mcp.enabled', false)) {
            throw new InvalidArgumentException('MCP connector is disabled in configuration');
        }

        // Try local servers first
        $localConfig = config("neuron.mcp.local_servers.{$serverName}");
        if (is_array($localConfig) && ($localConfig['enabled'] ?? false)) {
            return self::createLocalConnector($localConfig);
        }

        // Try remote servers
        $remoteConfig = config("neuron.mcp.remote_servers.{$serverName}");
        if (is_array($remoteConfig) && ($remoteConfig['enabled'] ?? false)) {
            return self::createRemoteConnector($remoteConfig);
        }

        throw new InvalidArgumentException("MCP server '{$serverName}' is not configured or is disabled");
    }

    /**
     * Create a local MCP connector.
     *
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidArgumentException If connector creation fails
     */
    protected static function createLocalConnector(array $config): McpConnector
    {
        try {
            // Create connector with command and args
            $connectorConfig = [
                'command' => $config['command'],
                'args' => $config['args'] ?? [],
            ];

            $connector = McpConnector::make($connectorConfig);

            // Apply tool filtering if configured
            /** @var array{exclude?: array<string>, only?: array<string>}|null $tools */
            $tools = $config['tools'] ?? null;
            if (is_array($tools) && ! empty($tools['exclude']) && is_array($tools['exclude'])) {
                $connector->exclude($tools['exclude']);
            }

            if (is_array($tools) && ! empty($tools['only']) && is_array($tools['only'])) {
                $connector->only($tools['only']);
            }

            return $connector;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Failed to create local MCP connector: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Create a remote MCP connector.
     *
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidArgumentException If connector creation fails
     */
    protected static function createRemoteConnector(array $config): McpConnector
    {
        try {
            // Create connector with URL configuration
            $connectorConfig = [
                'url' => $config['url'],
            ];

            // Add authentication token if provided
            if (! empty($config['token'])) {
                $connectorConfig['token'] = $config['token'];
            }

            // Set transport (default to SSE for remote servers)
            if (! empty($config['transport'])) {
                $connectorConfig['transport'] = $config['transport'];
            }

            $connector = McpConnector::make($connectorConfig);

            // Apply tool filtering if configured
            /** @var array{exclude?: array<string>, only?: array<string>}|null $tools */
            $tools = $config['tools'] ?? null;
            if (is_array($tools) && ! empty($tools['exclude']) && is_array($tools['exclude'])) {
                $connector->exclude($tools['exclude']);
            }

            if (is_array($tools) && ! empty($tools['only']) && is_array($tools['only'])) {
                $connector->only($tools['only']);
            }

            return $connector;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Failed to create remote MCP connector: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get all enabled MCP server names.
     *
     * @return array<string>
     */
    public static function getEnabledServers(): array
    {
        if (! config('neuron.mcp.enabled', false)) {
            return [];
        }

        $servers = [];

        // Get enabled local servers
        /** @var array<string, array<string, mixed>> $localServers */
        $localServers = config('neuron.mcp.local_servers', []);
        if (is_array($localServers)) {
            foreach ($localServers as $name => $config) {
                if (is_array($config) && ($config['enabled'] ?? false)) {
                    $servers[] = (string) $name;
                }
            }
        }

        // Get enabled remote servers
        /** @var array<string, array<string, mixed>> $remoteServers */
        $remoteServers = config('neuron.mcp.remote_servers', []);
        if (is_array($remoteServers)) {
            foreach ($remoteServers as $name => $config) {
                if (is_array($config) && ($config['enabled'] ?? false)) {
                    $servers[] = (string) $name;
                }
            }
        }

        return $servers;
    }

    /**
     * Check if a specific MCP server is enabled.
     */
    public static function isServerEnabled(string $serverName): bool
    {
        if (! config('neuron.mcp.enabled', false)) {
            return false;
        }

        $localConfig = config("neuron.mcp.local_servers.{$serverName}");
        if (is_array($localConfig) && ($localConfig['enabled'] ?? false)) {
            return true;
        }

        $remoteConfig = config("neuron.mcp.remote_servers.{$serverName}");
        if (is_array($remoteConfig) && ($remoteConfig['enabled'] ?? false)) {
            return true;
        }

        return false;
    }
}
