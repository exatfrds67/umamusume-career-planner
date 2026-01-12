---
inclusion: always
---

# Memory MCP Server Guidelines

Knowledge Graph Memory Server - A basic implementation of persistent memory using a local knowledge graph. This lets Claude remember information about the user across chats.

## Core Concepts

### Entities

Entities are the primary nodes in the knowledge graph. Each entity has:

- A unique name (identifier)
- An entity type (e.g., "person", "organization", "event")
- A list of observations

**Example:**

```json
{
  "name": "John_Smith",
  "entityType": "person",
  "observations": ["Speaks fluent Spanish"]
}
```

### Relations

Relations define directed connections between entities. They are always stored in active voice and describe how entities interact or relate to each other.

**Example:**

```json
{
  "from": "John_Smith",
  "to": "Anthropic",
  "relationType": "works_at"
}
```

### Observations

Observations are discrete pieces of information about an entity. They are:

- Stored as strings
- Attached to specific entities
- Can be added or removed independently
- Should be atomic (one fact per observation)

**Example:**

```json
{
  "entityName": "John_Smith",
  "observations": [
    "Speaks fluent Spanish",
    "Graduated in 2019",
    "Prefers morning meetings"
  ]
}
```

## API

### Tools

#### create_entities

Create multiple new entities in the knowledge graph

- **Input**: entities (array of objects)
- Each object contains:
  - `name` (string): Entity identifier
  - `entityType` (string): Type classification
  - `observations` (string[]): Associated observations
- Ignores entities with existing names

#### create_relations

Create multiple new relations between entities

- **Input**: relations (array of objects)
- Each object contains:
  - `from` (string): Source entity name
  - `to` (string): Target entity name
  - `relationType` (string): Relationship type in active voice
- Skips duplicate relations

#### add_observations

Add new observations to existing entities

- **Input**: observations (array of objects)
- Each object contains:
  - `entityName` (string): Target entity
  - `contents` (string[]): New observations to add
- Returns added observations per entity
- Fails if entity doesn't exist

#### delete_entities

Remove entities and their relations

- **Input**: entityNames (string[])
- Cascading deletion of associated relations
- Silent operation if entity doesn't exist

#### delete_observations

Remove specific observations from entities

- **Input**: deletions (array of objects)
- Each object contains:
  - `entityName` (string): Target entity
  - `observations` (string[]): Observations to remove
- Silent operation if observation doesn't exist

#### delete_relations

Remove specific relations from the graph

- **Input**: relations (array of objects)
- Each object contains:
  - `from` (string): Source entity name
  - `to` (string): Target entity name
  - `relationType` (string): Relationship type
- Silent operation if relation doesn't exist

#### read_graph

Read the entire knowledge graph

- **Input**: No input required
- Returns complete graph structure with all entities and relations

#### search_nodes

Search for nodes based on query

- **Input**: query (string)
- Searches across:
  - Entity names
  - Entity types
  - Observation content
- Returns matching entities and their relations

#### open_nodes

Retrieve specific nodes by name

- **Input**: names (string[])
- Returns:
  - Requested entities
  - Relations between requested entities
- Silently skips non-existent nodes

## Usage with Claude Desktop

### Setup

Add this to your claude_desktop_config.json:

#### Docker

```json
{
  "mcpServers": {
    "memory": {
      "command": "docker",
      "args": ["run", "-i", "-v", "claude-memory:/app/dist", "--rm", "mcp/memory"]
    }
  }
}
```

#### NPX

```json
{
  "mcpServers": {
    "memory": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-memory"
      ]
    }
  }
}
```

#### NPX with Custom Setting

The server can be configured using the following environment variables:

```json
{
  "mcpServers": {
    "memory": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-memory"
      ],
      "env": {
        "MEMORY_FILE_PATH": "/path/to/custom/memory.jsonl"
      }
    }
  }
}
```

**Environment Variables:**

- `MEMORY_FILE_PATH`: Path to the memory storage JSONL file (default: memory.jsonl in the server directory)

## VS Code Installation Instructions

For quick installation, use one of the one-click installation buttons below:

For manual installation, you can configure the MCP server using one of these methods:

### Method 1: User Configuration (Recommended)

Add the configuration to your user-level MCP configuration file. Open the Command Palette (Ctrl + Shift + P) and run MCP: Open User Configuration. This will open your user mcp.json file where you can add the server configuration.

### Method 2: Workspace Configuration

Alternatively, you can add the configuration to a file called .vscode/mcp.json in your workspace. This will allow you to share the configuration with others.

For more details about MCP configuration in VS Code, see the official VS Code MCP documentation.

#### NPX (VS Code)

```json
{
  "servers": {
    "memory": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-memory"
      ]
    }
  }
}
```

#### Docker (VS Code)

```json
{
  "servers": {
    "memory": {
      "command": "docker",
      "args": [
        "run",
        "-i",
        "-v",
        "claude-memory:/app/dist",
        "--rm",
        "mcp/memory"
      ]
    }
  }
}
```

## System Prompt

The prompt for utilizing memory depends on the use case. Changing the prompt will help the model determine the frequency and types of memories created.

Here is an example prompt for chat personalization. You could use this prompt in the "Custom Instructions" field of a Claude.ai Project.

```text
Follow these steps for each interaction:

1. User Identification:
   - You should assume that you are interacting with default_user
   - If you have not identified default_user, proactively try to do so.

2. Memory Retrieval:
   - Always begin your chat by saying only "Remembering..." and retrieve all relevant information from your knowledge graph
   - Always refer to your knowledge graph as your "memory"

3. Memory
   - While conversing with the user, be attentive to any new information that falls into these categories:
     a) Basic Identity (age, gender, location, job title, education level, etc.)
     b) Behaviors (interests, habits, etc.)
     c) Preferences (communication style, preferred language, etc.)
     d) Goals (goals, targets, aspirations, etc.)
     e) Relationships (personal and professional relationships up to 3 degrees of separation)

4. Memory Update:
   - If any new information was gathered during the interaction, update your memory as follows:
     a) Create entities for recurring organizations, people, and significant events
     b) Connect them to the current entities using relations
     c) Store facts about them as observations
```

## Building

Docker:

```bash
docker build -t mcp/memory -f src/memory/Dockerfile . 
```

**For Awareness**: a prior mcp/memory volume contains an index.js file that could be overwritten by the new container. If you are using a docker volume for storage, delete the old docker volume's index.js file before starting the new container.

## License

This MCP server is licensed under the MIT License. This means you are free to use, modify, and distribute the software, subject to the terms and conditions of the MIT License. For more details, please see the LICENSE file in the project repository.

## Best Practices for Memory Management

### Entity Creation

- Use descriptive, unique names for entities
- Choose appropriate entity types that reflect the nature of the entity
- Start with basic observations and expand over time
- Avoid duplicate entities by checking existing ones first

### Relationship Modeling

- Use clear, descriptive relation types
- Maintain consistency in relationship naming
- Model relationships that are relevant for future interactions
- Consider bidirectional relationships when appropriate

### Observation Management

- Keep observations atomic and specific
- Update observations when information changes
- Remove outdated or incorrect observations
- Prioritize information that enhances personalization

### Memory Retrieval Strategy

- Always start interactions by retrieving relevant memory
- Use search functionality to find related entities
- Consider relationship traversal for comprehensive context
- Balance memory retrieval with conversation flow

## Data Persistence

The memory server stores data in JSON Lines format (.jsonl), where each line contains either an entity or relation object:

```jsonl
{"type": "entity", "name": "John_Smith", "entityType": "person", "observations": ["Software engineer"]}
{"type": "relation", "from": "John_Smith", "to": "Anthropic", "relationType": "works_at"}
```

## Security Considerations

- Memory files contain sensitive user information
- Ensure appropriate file permissions on memory storage
- Consider encryption for sensitive deployments
- Implement backup strategies for important memory data
- Be mindful of data retention policies and user privacy

## Troubleshooting

### Common Issues

- **Memory not persisting**: Check file path permissions and disk space
- **Duplicate entities**: Implement entity existence checks before creation
- **Relationship errors**: Ensure both entities exist before creating relations
- **Search not working**: Verify entity names and observation content

### Performance Optimization

- Regularly clean up outdated observations
- Limit entity proliferation for better search performance
- Use specific search queries rather than broad searches
- Consider memory file size for large deployments

## Integration with Other MCP Servers

The Memory MCP Server works well alongside other MCP servers:

- **File System servers**: Remember file preferences and locations
- **Calendar servers**: Store meeting preferences and scheduling patterns
- **Communication servers**: Remember contact preferences and communication styles
- **Task management servers**: Store workflow preferences and project contexts

## Version Compatibility

- **Current Version**: @modelcontextprotocol/server-memory@2025.8.4
- **Node.js**: Requires Node.js runtime for NPX installation
- **Docker**: Compatible with standard Docker environments
- **MCP Protocol**: Follows official MCP specification

## Additional Resources

- **Official Repository**: <https://github.com/modelcontextprotocol/servers>
- **NPM Package**: @modelcontextprotocol/server-memory
- **Documentation**: <https://modelcontextprotocol.io>
- **Community**: Model Context Protocol Discord and GitHub discussions

## Example Usage Patterns

### Personal Assistant Setup

```javascript
// Create user entity
create_entities([{
  name: "user_primary",
  entityType: "person",
  observations: ["Prefers concise responses", "Works in software development"]
}])

// Add work context
create_entities([{
  name: "current_project",
  entityType: "project",
  observations: ["Laravel application", "Due next month"]
}])

// Link user to project
create_relations([{
  from: "user_primary",
  to: "current_project",
  relationType: "working_on"
}])
```

### Team Collaboration Context

```javascript
// Create team members
create_entities([
  {name: "alice_dev", entityType: "person", observations: ["Frontend specialist"]},
  {name: "bob_pm", entityType: "person", observations: ["Project manager"]}
])

// Create team relationships
create_relations([
  {from: "alice_dev", to: "bob_pm", relationType: "reports_to"},
  {from: "user_primary", to: "alice_dev", relationType: "collaborates_with"}
])
```

This memory system enables rich, contextual interactions that improve over time as more information is gathered and stored.
