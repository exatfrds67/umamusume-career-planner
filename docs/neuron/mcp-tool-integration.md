# MCP Tool Integration with Neuron AI Agents

## Overview

This document explains how to integrate Model Context Protocol (MCP) tools with Neuron AI agents in the Uma Musume
Career Planner application. MCP allows agents to automatically discover and use tools from external servers without
implementing them manually.

## What is MCP?

Model Context Protocol (MCP) is an open standard designed by Anthropic to connect AI agents to external service
providers. It enables:

- **Automatic Tool Discovery**: Agents automatically discover tools exposed by MCP servers
- **Standardized Integration**: Connect to any MCP-compliant server using a standard protocol
- **Tool Filtering**: Fine-grained control over which tools are available to agents
- **Pre-built Tools**: Access a vast ecosystem of pre-built tools and integrations

## Configuration

### Enabling MCP

MCP integration is configured in `config/neuron.php`:

```php
'mcp' => [
    'enabled' => env('NEURON_MCP_ENABLED', false),
    // ...
]
```text

Set `NEURON_MCP_ENABLED=true` in your `.env` file to enable MCP integration.

### Configuring MCP Servers

#### Local Servers

Local MCP servers run as command-line processes on the same machine:

```php
'local_servers' => [
    'memory' => [
        'enabled' => env('NEURON_MCP_MEMORY_ENABLED', false),
        'type' => 'local',
        'command' => 'npx',
        'args' => ['-y', '@modelcontextprotocol/server-memory'],
        'transport' => 'stdio',
        'description' => 'Knowledge graph and persistent memory for agents',
        'tools' => [
            'exclude' => [],  // Tools to exclude
            'only' => [],     // Only include these tools (empty = all)
        ],
    ],
],
```text

#### Remote Servers

Remote MCP servers are accessed via HTTP/HTTPS URLs:

```php
'remote_servers' => [
    'umapyoi' => [
        'enabled' => env('NEURON_MCP_UMAPYOI_ENABLED', false),
        'type' => 'remote',
        'url' => env('NEURON_MCP_UMAPYOI_URL', 'https://api.umapyoi.net/mcp'),
        'token' => env('NEURON_MCP_UMAPYOI_TOKEN'),
        'transport' => 'sse',
        'description' => 'Uma Musume game data API',
        'tools' => [
            'exclude' => [],
            'only' => [],
        ],
    ],
],
```text

## Using MCP Tools in Agents

### Basic Integration

To integrate MCP tools with an agent, override the `mcpServers()` method in your agent class:

```php
<?php

namespace App\Neuron\Agents;

class MyAgent extends BaseAgent
{
    /**
     * Configure MCP servers for this agent.
     */
    protected function mcpServers(): array
    {
        return [
            'memory' => [],      // Include all tools from memory server
            'filesystem' => [],  // Include all tools from filesystem server
        ];
    }
}
```

### Tool Filtering with `only()`

Use the `only` filter to include specific tools:

```php
protected function mcpServers(): array
{
    return [
        'memory' => [
            'only' => ['create_entities', 'search_nodes', 'read_graph'],
        ],
    ];
}
```text

This configuration will only include the specified tools from the memory server, reducing token consumption and
preventing unwanted tool usage.

### Tool Filtering with `exclude()`

Use the `exclude` filter to remove specific tools:

```php
protected function mcpServers(): array
{
    return [
        'filesystem' => [
            'exclude' => ['delete_file', 'write_file'],
        ],
    ];
}
```text

This configuration includes all tools from the filesystem server except the dangerous delete and write operations.

### Combining Custom Tools and MCP Tools

Agents can use both custom tools and MCP tools:

```php
<?php

namespace App\Neuron\Agents;

use App\Neuron\Agents\Tools\CharacterStatsTool;

class TrainingAdvisorAgent extends BaseAgent
{
    /**
     * Register custom tools.
     */
    protected function tools(): array
    {
        return [
            new CharacterStatsTool,
        ];
    }

    /**
     * Configure MCP servers.
     */
    protected function mcpServers(): array
    {
        return [
            'memory' => [
                'only' => ['create_entities', 'search_nodes'],
            ],
        ];
    }
}
```text

The agent will have access to both the custom `CharacterStatsTool` and the filtered tools from the memory MCP server.

## MCP Tool Integration Service

The `McpToolIntegration` service provides helper methods for working with MCP tools:

### Check if MCP is Enabled

```php
use App\Neuron\Support\McpToolIntegration;

if (McpToolIntegration::isEnabled()) {
    // MCP is enabled
}
```

### Get Available Servers

```php
$servers = McpToolIntegration::getAvailableServers();
// Returns: ['memory', 'filesystem', 'fetch', 'umapyoi', ...]
```text

### Get Server Information

```php
$info = McpToolIntegration::getServerInfo('memory');
// Returns:
// [
//     'type' => 'local',
//     'enabled' => true,
//     'description' => 'Knowledge graph and persistent memory',
//     'tools' => ['exclude' => [], 'only' => []],
// ]
```text

### Get Tools from Specific Servers

```php
$tools = McpToolIntegration::getTools([
    'memory' => [
        'only' => ['create_entities', 'search_nodes'],
    ],
    'filesystem' => [
        'exclude' => ['delete_file'],
    ],
]);
```text

### Get All Tools from Enabled Servers

```php
$allTools = McpToolIntegration::getAllTools();
```

## Example: MCP Demo Agent

The `McpDemoAgent` demonstrates MCP tool integration:

```php
<?php

namespace App\Neuron\Agents;

class McpDemoAgent extends BaseAgent
{
    protected function mcpServers(): array
    {
        return [
            // Memory server - only include specific tools
            'memory' => [
                'only' => ['create_entities', 'search_nodes', 'read_graph'],
            ],

            // Filesystem server - exclude dangerous operations
            'filesystem' => [
                'exclude' => ['delete_file', 'write_file'],
            ],

            // Fetch server - include all tools (no filtering)
            'fetch' => [],
        ];
    }
}
```text

## Available MCP Servers

### Memory Server

**Package**: `@modelcontextprotocol/server-memory`

**Description**: Knowledge graph and persistent memory for agents

**Tools**:

- `create_entities` - Create entities in the knowledge graph
- `create_relations` - Create relationships between entities
- `add_observations` - Add observations to entities
- `delete_entities` - Delete entities
- `delete_observations` - Delete observations
- `delete_relations` - Delete relationships
- `read_graph` - Read the entire knowledge graph
- `search_nodes` - Search for nodes
- `open_nodes` - Open specific nodes

### Filesystem Server

**Package**: `@modelcontextprotocol/server-filesystem`

**Description**: File system access for reading and writing files

**Tools**:

- `read_file` - Read file contents
- `read_multiple_files` - Read multiple files
- `write_file` - Write to a file
- `edit_file` - Edit file contents
- `create_directory` - Create directories
- `list_directory` - List directory contents
- `move_file` - Move or rename files
- `search_files` - Search for files
- `get_file_info` - Get file metadata

### Fetch Server

**Package**: `fetch@latest` (via uvx)

**Description**: HTTP client for fetching external data

**Tools**:

- `fetch` - Fetch data from URLs
- `post` - Send POST requests
- `put` - Send PUT requests
- `delete` - Send DELETE requests

## Best Practices

### 1. Use Tool Filtering

Always use `only` or `exclude` filters to:

- Reduce token consumption
- Prevent unwanted tool usage
- Improve agent focus and accuracy
- Enhance security by limiting capabilities

### 2. Document Tool Usage

Document which MCP tools your agent uses and why:

```php
/**
 * Training Advisor Agent with MCP integration.
 *
 * MCP Tools:
 * - memory.create_entities: Store training decisions
 * - memory.search_nodes: Retrieve past training patterns
 */
class TrainingAdvisorAgent extends BaseAgent
{
    // ...
}
```text

### 3. Handle Connection Errors

MCP connections may fail. The integration handles errors gracefully:

```php
// Errors are logged but don't crash the application
$tools = McpToolIntegration::getAllTools();
// Returns empty array if all connections fail
```text

### 4. Test with MCP Disabled

Ensure your agents work even when MCP is disabled:

```php
// Agent should function with custom tools only
config(['neuron.mcp.enabled' => false]);
$agent = new MyAgent($userId);
// Agent still works with custom tools
```

### 5. Monitor Tool Usage

Use Inspector to monitor MCP tool usage:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

This allows you to see which tools agents are using and optimize accordingly.

## Troubleshooting

### MCP Server Not Connecting

1. Check if the server is enabled in config:

   ```php
   config('neuron.mcp.local_servers.memory.enabled')
   ```

1. Verify the command and args are correct:

   ```bash
   npx -y @modelcontextprotocol/server-memory
   ```text

2. Check logs for connection errors:

   ```bash
   tail -f storage/logs/laravel.log
   ```

### Tools Not Appearing

1. Verify MCP is enabled globally:

   ```env
   NEURON_MCP_ENABLED=true
   ```text

2. Check tool filtering configuration:

   ```php
   $info = McpToolIntegration::getServerInfo('memory');
   dd($info['tools']);
   ```

3. Ensure the server is in the agent's `mcpServers()` configuration

### Performance Issues

1. Use `only` filters to reduce tool count
2. Disable unused MCP servers
3. Monitor token usage with Inspector
4. Consider caching tool results

## Requirements Validation

This implementation validates the following requirements:

- **Requirement 17.5**: When MCP servers expose tools THEN the system SHALL automatically discover and register them
with agents
- **Requirement 17.6**: The System SHALL support filtering MCP tools using `exclude()` and `only()` methods

## Related Documentation

- [Neuron AI Integration Guide](./integration-guide.md)
- [Creating Custom Tools](./custom-tools.md)
- [Agent Development Guide](./agent-development.md)
- [MCP Official Documentation](https://modelcontextprotocol.io/)
- [Neuron AI MCP Connector](https://docs.neuron-ai.dev/the-basics/mcp-connector)

## References

- [Model Context Protocol Specification](https://modelcontextprotocol.io/specification)
- [MCP Server Directory](https://github.com/modelcontextprotocol/servers)
- [Neuron AI Documentation](https://docs.neuron-ai.dev/)
