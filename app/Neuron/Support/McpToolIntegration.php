<?php

declare(strict_types=1);

namespace App\Neuron\Support;

use InvalidArgumentException;
use NeuronAI\MCP\McpConnector;

/**
 * Service for integrating MCP tools with Neuron AI agents.
 *
 * This class provides functionality to automatically discover tools from MCP servers
 * and register them with agents. Supports filtering tools using exclude() and only()
 * methods for fine-grained control over available capabilities.
 *
 * **Validates: Requirements 17.5, 17.6**
 */
class McpToolIntegration
{
    /**
     * Get MCP connectors for all enabled servers.
     *
     * Automatically discovers and connects to all enabled MCP servers
     * configured in the application.
     *
     * @return array<string, McpConnector>
     */
    public static function getEnabledConnectors(): array
    {
        $connectors = [];
        $enabledServers = McpConnectorFactory::getEnabledServers();

        foreach ($enabledServers as $serverName) {
            try {
                $connectors[$serverName] = McpConnectorFactory::make($serverName);
            } catch (InvalidArgumentException $e) {
                // Log error but continue with other servers
                logger()->warning("Failed to connect to MCP server: {$serverName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $connectors;
    }

    /**
     * Get MCP connector for a specific server with optional tool filtering.
     *
     * @param  string  $serverName  The name of the MCP server
     * @param  array<string>  $exclude  Tools to exclude from the connector
     * @param  array<string>  $only  Only include these tools (empty = all)
     *
     * @throws InvalidArgumentException If server is not configured or disabled
     */
    public static function getConnector(
        string $serverName,
        array $exclude = [],
        array $only = []
    ): McpConnector {
        // Get base connector from factory
        $connector = McpConnectorFactory::make($serverName);

        // Apply additional filtering if provided
        // Note: These will be applied on top of any config-level filtering
        if (! empty($exclude)) {
            $connector->exclude($exclude);
        }

        if (! empty($only)) {
            $connector->only($only);
        }

        return $connector;
    }

    /**
     * Get tools from a specific MCP server.
     *
     * @param  string  $serverName  The name of the MCP server
     * @param  array<string>  $exclude  Tools to exclude
     * @param  array<string>  $only  Only include these tools
     * @return array<int, mixed>
     */
    public static function getServerTools(
        string $serverName,
        array $exclude = [],
        array $only = []
    ): array {
        try {
            $connector = self::getConnector($serverName, $exclude, $only);

            return $connector->tools();
        } catch (InvalidArgumentException $e) {
            logger()->warning("Failed to get tools from MCP server: {$serverName}", [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get MCP connectors for multiple servers with filtering.
     *
     * @param  array<string, array{exclude?: array<string>, only?: array<string>}>  $servers  Server configurations
     * @return array<string, McpConnector>
     */
    public static function getConnectors(array $servers): array
    {
        $connectors = [];

        foreach ($servers as $serverName => $config) {
            try {
                $exclude = $config['exclude'] ?? [];
                $only = $config['only'] ?? [];

                $connectors[$serverName] = self::getConnector($serverName, $exclude, $only);
            } catch (InvalidArgumentException $e) {
                // Log error but continue with other servers
                logger()->warning("Failed to connect to MCP server: {$serverName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $connectors;
    }

    /**
     * Get all tools from enabled MCP servers.
     *
     * Automatically discovers tools from all enabled MCP servers.
     * Returns a flat array of all tools from all servers.
     *
     * @return array<int, mixed>
     */
    public static function getAllTools(): array
    {
        $allTools = [];
        $connectors = self::getEnabledConnectors();

        foreach ($connectors as $connector) {
            try {
                $tools = $connector->tools();
                $allTools = array_merge($allTools, $tools);
            } catch (\Exception $e) {
                logger()->warning('Failed to get tools from MCP connector', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $allTools;
    }

    /**
     * Get tools from specific MCP servers with filtering.
     *
     * @param  array<string, array{exclude?: array<string>, only?: array<string>}>  $servers  Server configurations
     * @return array<int, mixed>
     */
    public static function getTools(array $servers): array
    {
        $allTools = [];
        $connectors = self::getConnectors($servers);

        foreach ($connectors as $connector) {
            try {
                $tools = $connector->tools();
                $allTools = array_merge($allTools, $tools);
            } catch (\Exception $e) {
                logger()->warning('Failed to get tools from MCP connector', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $allTools;
    }

    /**
     * Check if MCP integration is enabled globally.
     */
    public static function isEnabled(): bool
    {
        return config('neuron.mcp.enabled', false);
    }

    /**
     * Get list of available MCP server names.
     *
     * @return array<string>
     */
    public static function getAvailableServers(): array
    {
        if (! self::isEnabled()) {
            return [];
        }

        $servers = [];

        // Get all local servers
        $localServers = config('neuron.mcp.local_servers', []);
        foreach (array_keys($localServers) as $name) {
            $servers[] = $name;
        }

        // Get all remote servers
        $remoteServers = config('neuron.mcp.remote_servers', []);
        foreach (array_keys($remoteServers) as $name) {
            $servers[] = $name;
        }

        return $servers;
    }

    /**
     * Get server configuration details.
     *
     * @return array{type: string, enabled: bool, description: string, tools: array}|null
     */
    public static function getServerInfo(string $serverName): ?array
    {
        // Check local servers
        $localConfig = config("neuron.mcp.local_servers.{$serverName}");
        if ($localConfig) {
            return [
                'type' => 'local',
                'enabled' => $localConfig['enabled'] ?? false,
                'description' => $localConfig['description'] ?? '',
                'tools' => $localConfig['tools'] ?? ['exclude' => [], 'only' => []],
            ];
        }

        // Check remote servers
        $remoteConfig = config("neuron.mcp.remote_servers.{$serverName}");
        if ($remoteConfig) {
            return [
                'type' => 'remote',
                'enabled' => $remoteConfig['enabled'] ?? false,
                'description' => $remoteConfig['description'] ?? '',
                'tools' => $remoteConfig['tools'] ?? ['exclude' => [], 'only' => []],
            ];
        }

        return null;
    }
}
