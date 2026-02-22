# MCP Connector Integration Guide

## Overview

The Model Context Protocol (MCP) allows Neuron AI agents to connect to pre-built tools and integrations without
implementing them manually. This guide explains how to configure and use MCP connectors with your agents.

## Table of Contents

- [What is MCP?](#what-is-mcp)
- [Configuration](#configuration)
- [Local MCP Servers](#local-mcp-servers)
- [Remote MCP Servers](#remote-mcp-servers)
- [Using MCP with Agents](#using-mcp-with-agents)
- [Tool Filtering](#tool-filtering)
- [Troubleshooting](#troubleshooting)

## What is MCP?

Model Context Protocol (MCP) is a standard protocol that allows AI agents to access external tools and data sources.
Instead of implementing custom tools for every data source, you can connect to MCP servers that provide pre-built tools.

### Benefits

- **Rapid Integration**: Connect to existing MCP servers without writing custom code
- **Standardized Interface**: All MCP servers follow the same protocol
- **Tool Discovery**: Automatically discover available tools from connected servers
- **Flexible Filtering**: Choose which tools to expose to your agents

### Use Cases for Uma Musume Career Planner

1. **Game Data Integration**: Connect to umapyoi.net MCP server for character and skill data
2. **Memory Management**: Use the memory server for persistent knowledge graphs
3. **File Operations**: Access file system for reading/writing game data
4. **Web Scraping**: Fetch external data from game wikis and databases

## Configuration

MCP connector configuration is located in `config/neuron.php` under the `mcp` key.

### Enable MCP Connector

Set the following environment variable in your `.env` file:

```env
NEURON_MCP_ENABLED=true
```text

### Configuration Structure

```php
'mcp' => [
    'enabled' => env('NEURON_MCP_ENABLED', false),
    'local_servers' => [...],
    'remote_servers' => [...],
    'connection' => [...],
    'global_tools' => [...],
]
```

## Local MCP Servers

Local MCP servers run as command-line processes on the same machine. They use standard input/output (stdio) for
communication.

### Configuration Example

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
            'exclude' => [],
            'only' => [],
        ],
    ],
],
```text

### Environment Variables

```env
# Enable the memory server
NEURON_MCP_MEMORY_ENABLED=true

# Enable the filesystem server
NEURON_MCP_FILESYSTEM_ENABLED=true

# Enable the fetch server
NEURON_MCP_FETCH_ENABLED=true
```

## Available Local Servers

### Memory Server

Provides knowledge graph and persistent memory capabilities.

**Tools**:

- `create_entities`: Create entities in the knowledge graph
- `create_relations`: Create relationships between entities
- `add_observations`: Add observations to entities
- `search_nodes`: Search for nodes in the graph
- `open_nodes`: Retrieve specific nodes by name

**Use Case**: Store character builds, training strategies, and race results for future reference.

#### Filesystem Server

Provides file system access for reading and writing files.

**Tools**:

- `read_file`: Read file contents
- `write_file`: Write content to a file
- `list_directory`: List directory contents
- `create_directory`: Create a new directory

**Use Case**: Save and load character configurations, export training logs.

#### Fetch Server

Provides HTTP client for fetching external data.

**Tools**:

- `fetch`: Make HTTP requests to external APIs
- `fetch_json`: Fetch and parse JSON data

**Use Case**: Retrieve game data from external APIs and wikis.

## Remote MCP Servers

Remote MCP servers are accessed via HTTP/HTTPS URLs. They support Server-Sent Events (SSE) for asynchronous
communication.

### Remote Configuration Example

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

### Environment Variables

```env
# Enable the umapyoi server
NEURON_MCP_UMAPYOI_ENABLED=true

# Set the server URL
NEURON_MCP_UMAPYOI_URL=https://api.umapyoi.net/mcp

# Set the authentication token
NEURON_MCP_UMAPYOI_TOKEN=your-api-token-here
```

## Transport Options

- **stdio**: Standard input/output (local servers only)
- **sse**: Server-Sent Events (recommended for remote servers)

SSE provides:

- Asynchronous communication
- Real-time updates
- Better error handling
- Connection keep-alive

## Using MCP with Agents

### Basic Usage

Use the `McpConnectorFactory` to create MCP connectors for your agents:

```php
use App\Neuron\Support\McpConnectorFactory;
use NeuronAI\Agent;

class TrainingAdvisorAgent extends Agent
{
    protected function mcpConnectors(): array
    {
        $connectors = [];

        // Add memory server if enabled
        if (McpConnectorFactory::isServerEnabled('memory')) {
            $connectors[] = McpConnectorFactory::make('memory');
        }

        // Add umapyoi server if enabled
        if (McpConnectorFactory::isServerEnabled('umapyoi')) {
            $connectors[] = McpConnectorFactory::make('umapyoi');
        }

        return $connectors;
    }
}
```text

### Manual Connector Creation

You can also create connectors manually:

```php
use NeuronAI\MCP\McpConnector;

// Local server
$connector = McpConnector::local()
    ->command('npx')
    ->args(['-y', '@modelcontextprotocol/server-memory'])
    ->connect();

// Remote server
$connector = McpConnector::remote()
    ->url('https://api.umapyoi.net/mcp')
    ->token(env('UMAPYOI_API_TOKEN'))
    ->transport('sse')
    ->connect();

// Use with agent
$agent->withMcpConnector($connector);
```

### Multiple Connectors

Agents can use multiple MCP connectors simultaneously:

```php
protected function mcpConnectors(): array
{
    return [
        McpConnectorFactory::make('memory'),
        McpConnectorFactory::make('fetch'),
        McpConnectorFactory::make('umapyoi'),
    ];
}
```

## Tool Filtering

Control which tools from MCP servers are available to your agents.

### Exclude Specific Tools

```php
'tools' => [
    'exclude' => ['delete_file', 'write_file'],
    'only' => [],
],
```

### Include Only Specific Tools

```php
'tools' => [
    'exclude' => [],
    'only' => ['get_character_data', 'get_skill_data'],
],
```text

### Programmatic Filtering

```php
$connector = McpConnector::remote()
    ->url('https://api.umapyoi.net/mcp')
    ->token(env('UMAPYOI_API_TOKEN'))
    ->only(['get_character_data', 'get_skill_data'])
    ->connect();
```

### Global Tool Filtering

Apply filtering rules to all MCP servers:

```php
'global_tools' => [
    'exclude' => ['delete_file', 'drop_table'],
    'only' => [],
],
```

**Note**: Server-specific rules take precedence over global rules.

## Connection Settings

Configure connection behavior for MCP servers:

```php
'connection' => [
    'timeout' => env('NEURON_MCP_TIMEOUT', 30),
    'retry_attempts' => env('NEURON_MCP_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('NEURON_MCP_RETRY_DELAY', 1000), // milliseconds
],
```

### Environment Variables

```env
# Connection timeout in seconds
NEURON_MCP_TIMEOUT=30

# Number of retry attempts on failure
NEURON_MCP_RETRY_ATTEMPTS=3

# Delay between retries in milliseconds
NEURON_MCP_RETRY_DELAY=1000
```

## Troubleshooting

### MCP Server Not Connecting

**Problem**: Agent cannot connect to MCP server

**Solutions**:

1. Check if MCP is enabled:

   ```env
   NEURON_MCP_ENABLED=true
   ```

2. Verify server is enabled:

   ```env
   NEURON_MCP_MEMORY_ENABLED=true
   ```text

3. Check server configuration:

   ```php
   dd(config('neuron.mcp.local_servers.memory'));
   ```

4. Test server availability:

   ```php
   $enabled = McpConnectorFactory::isServerEnabled('memory');
   ```text

### Command Not Found (Local Servers)

**Problem**: Local MCP server command not found

**Solutions**:

1. Install required packages:

   ```bash
   npm install -g @modelcontextprotocol/server-memory
   ```

2. Verify command path:

   ```bash
   which npx
   ```text

3. Update command in configuration:

   ```php
   'command' => '/usr/local/bin/npx',
   ```

### Authentication Errors (Remote Servers)

**Problem**: Remote server returns 401 Unauthorized

**Solutions**:

1. Verify API token is set:

   ```env
   NEURON_MCP_UMAPYOI_TOKEN=your-token-here
   ```text

2. Check token validity:

   ```bash
   curl -H "Authorization: Bearer your-token-here" https://api.umapyoi.net/mcp
   ```

3. Regenerate token if expired

### Tool Not Available

**Problem**: Expected tool is not available to agent

**Solutions**:

1. Check tool filtering configuration:

   ```php
   'tools' => [
       'exclude' => [],
       'only' => [], // Empty = all tools
   ],
   ```text

2. Verify tool name:

   ```php
   $connector = McpConnectorFactory::make('memory');
   $tools = $connector->getAvailableTools();
   dd($tools);
   ```

3. Check global tool filters:

   ```php
   dd(config('neuron.mcp.global_tools'));
   ```text

### Connection Timeout

**Problem**: MCP server connection times out

**Solutions**:

1. Increase timeout:

   ```env
   NEURON_MCP_TIMEOUT=60
   ```

2. Check network connectivity:

   ```bash
   ping api.umapyoi.net
   ```text

3. Verify server is running:

   ```bash
   curl https://api.umapyoi.net/mcp/health
   ```

## Best Practices

### Security

1. **Never commit API tokens**: Use environment variables
2. **Restrict tool access**: Use `only` filtering for production
3. **Validate inputs**: Always validate data from external sources
4. **Use HTTPS**: Only connect to secure remote servers

### Performance

1. **Cache responses**: Implement caching for frequently accessed data
2. **Limit concurrent connections**: Use `max_concurrent_calls` setting
3. **Monitor timeouts**: Adjust timeout based on server response times
4. **Use local servers**: Prefer local servers for better performance

### Reliability

1. **Implement retries**: Configure retry attempts for transient failures
2. **Handle errors gracefully**: Catch and log MCP connection errors
3. **Provide fallbacks**: Have backup data sources when MCP is unavailable
4. **Monitor health**: Regularly check MCP server health

## Examples

### Training Advisor with MCP

```php
use App\Neuron\Support\McpConnectorFactory;
use NeuronAI\Agent;

class TrainingAdvisorAgent extends Agent
{
    protected function mcpConnectors(): array
    {
        $connectors = [];

        // Use memory server for storing training history
        if (McpConnectorFactory::isServerEnabled('memory')) {
            $connectors[] = McpConnectorFactory::make('memory');
        }

        // Use umapyoi server for game data
        if (McpConnectorFactory::isServerEnabled('umapyoi')) {
            $connectors[] = McpConnectorFactory::make('umapyoi');
        }

        return $connectors;
    }

    public function instructions(): string
    {
        return "You are a training advisor for Uma Musume. "
            . "Use the memory server to recall past training decisions. "
            . "Use the umapyoi server to get current character and skill data.";
    }
}
```text

### Custom MCP Server Configuration

```php
// config/neuron.php
'remote_servers' => [
    'custom_game_data' => [
        'enabled' => env('NEURON_MCP_CUSTOM_ENABLED', false),
        'type' => 'remote',
        'url' => env('NEURON_MCP_CUSTOM_URL'),
        'token' => env('NEURON_MCP_CUSTOM_TOKEN'),
        'transport' => 'sse',
        'description' => 'Custom game data server',
        'tools' => [
            'exclude' => ['admin_tools', 'delete_data'],
            'only' => [],
        ],
    ],
],
```

## Additional Resources

- [Neuron AI Documentation](https://docs.neuron-ai.dev)
- [MCP Specification](https://modelcontextprotocol.io)
- [MCP Server Directory](https://www.pulsemcp.com/servers)
- [MCP Research Document](.kiro/specs/neuron-ai-integration/mcp-server-research.md)

## Support

For issues or questions:

1. Check the [troubleshooting section](#troubleshooting)
2. Review the [MCP research document](.kiro/specs/neuron-ai-integration/mcp-server-research.md)
3. Consult the [Neuron AI documentation](https://docs.neuron-ai.dev)
4. Open an issue in the project repository
