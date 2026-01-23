<?php

declare(strict_types=1);

use NeuronAI\Providers\HuggingFace\InferenceProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | System prompt
    |--------------------------------------------------------------------------
    |
    | You can configure a system prompt to be used by default across multiple AI Agents.
    |
    */

    'system_prompt' => [
        'background' => 'You are an expert AI assistant for the Uma Musume Pretty Derby Career Planner application, built with Neuron AI framework.',
        'steps' => [],
        'output' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | https://docs.neuron-ai.dev/the-basics/ai-provider
    |
    | Configure the default provider to use for AI generation.
    |
    */

    'provider' => [
        'default' => env('NEURON_AI_PROVIDER', 'anthropic'),

        'anthropic' => [
            'key' => env('ANTHROPIC_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            'parameters' => [],
        ],

        'openai' => [
            'key' => env('OPENAI_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'parameters' => [],
        ],

        'openai-responses' => [
            'key' => env('OPENAI_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'parameters' => [],
        ],

        'gemini' => [
            'key' => env('GEMINI_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'parameters' => [],
        ],

        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434/api'),
            'model' => env('OLLAMA_MODEL', 'llama2'),
            'parameters' => [],
        ],

        'mistral' => [
            'key' => env('MISTRAL_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-large-latest'),
            'parameters' => [],
        ],

        'deepseek' => [
            'key' => env('DEEPSEEK_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'parameters' => [],
        ],

        'huggingface' => [
            'key' => env('HUGGINGFACE_KEY'),
            'model' => env('HUGGINGFACE_MODEL', 'meta-llama/Llama-2-7b-hf'),
            'inferenceProvider' => InferenceProvider::HF_INFERENCE,
            'parameters' => [],
        ],

        /*'cohere' => [
            'key' => env('COHERE_KEY'),
            'model' => env('COHERE_MODEL', 'command-a-reasoning-08-2025'),
            'parameters' => [],
        ],*/
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Provider
    |--------------------------------------------------------------------------
    |
    | https://docs.neuron-ai.dev/rag/embeddings-provider
    |
    | Embedding provider is a fundamental component of a RAG system.
    | Here is where you can configure the embedding provider you want to connect your RAG with.
    |
    */

    'embedding' => [
        'default' => env('NEURON_EMBEDDING_PROVIDER', 'openai'),

        'openai' => [
            'key' => env('OPENAI_KEY'),
            'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'dimensions' => 1024,
        ],

        'gemini' => [
            'key' => env('GEMINI_KEY'),
            'model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
            'config' => [],
        ],

        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434/api'),
            'model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
            'parameters' => [],
        ],

        'voyage' => [
            'key' => env('VOYAGE_KEY'),
            'model' => env('VOYAGE_EMBEDDING_MODEL', 'voyage-3'),
            'dimensions' => null,
        ],

        'mistral' => [
            'baseUri' => env('MISTRAL_BASE_URI', 'https://api.mistral.ai/v1/embeddings'),
            'key' => env('MISTRAL_KEY'),
            'model' => env('MISTRAL_EMBEDDING_MODEL', 'mistral-embed'),
            'dimensions' => 1024,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Store
    |--------------------------------------------------------------------------
    |
    | https://docs.neuron-ai.dev/rag/vector-store
    |
    | Vector Store is the database for embedded pieces of contents where your RAG performs Retrieval
    | before answering a user question. Here is where you can configure the vector store you want to connect your RAG with.
    |
    */

    'store' => [
        'default' => env('NEURON_STORE_PROVIDER', 'file'),

        'file' => [
            'directory' => storage_path('neuron'),
            'topK' => 5,
            'name' => 'neuron',
            'ext' => '.store',
        ],

        'pinecone' => [
            'key' => env('PINECONE_KEY'),
            'indexUrl' => env('PINECONE_INDEX_URL'),
            'topK' => 5,
            'version' => '2025-04',
            'namespace' => '__default__',
        ],

        'qdrant' => [
            'collectionUrl' => env('QDRANT_COLLECTION_URL'),
            'key' => env('QDRANT_KEY'),
            'topK' => 5,
            'dimension' => 1024,
        ],

        'meilisearch' => [
            'indexUid' => env('MEILISEARCH_INDEX_UID'),
            'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
            'key' => env('MEILISEARCH_KEY'),
            'embedder' => 'default',
            'topK' => 5,
            'dimension' => 1024,
        ],

        'chroma' => [
            'collectionUrl' => env('CHROMA_COLLECTION'),
            'host' => env('CHROMA_HOST', 'http://localhost:7700'),
            'tenant' => env('CHROMA_TENANT', 'default_tenant'),
            'database' => env('CHROMA_DATABASE', 'default_database'),
            'key' => env('CHROMA_KEY'),
            'topK' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Connector Configuration (Optional)
    |--------------------------------------------------------------------------
    |
    | https://docs.neuron-ai.dev/mcp/connector
    |
    | Model Context Protocol (MCP) allows agents to connect to pre-built tools
    | and integrations without implementing them manually. Configure local or
    | remote MCP servers that agents can use.
    |
    | Supported transports:
    | - 'stdio': Standard input/output (default for local servers)
    | - 'sse': Server-Sent Events (for async remote connections)
    |
    */

    'mcp' => [
        /*
        |--------------------------------------------------------------------------
        | Enable MCP Connector
        |--------------------------------------------------------------------------
        |
        | Set to true to enable MCP connector integration with Neuron AI agents.
        | When disabled, agents will not attempt to connect to MCP servers.
        |
        */
        'enabled' => env('NEURON_MCP_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Local MCP Servers
        |--------------------------------------------------------------------------
        |
        | Local MCP servers run as command-line processes on the same machine.
        | Use command-style configuration with 'command' and 'args' parameters.
        |
        | Example:
        | 'filesystem' => [
        |     'enabled' => true,
        |     'type' => 'local',
        |     'command' => 'npx',
        |     'args' => ['-y', '@modelcontextprotocol/server-filesystem', storage_path('app/neuron')],
        |     'transport' => 'stdio',
        |     'tools' => [
        |         'exclude' => [],  // Tools to exclude
        |         'only' => [],     // Only include these tools (empty = all)
        |     ],
        | ],
        |
        */
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

            'filesystem' => [
                'enabled' => env('NEURON_MCP_FILESYSTEM_ENABLED', false),
                'type' => 'local',
                'command' => 'npx',
                'args' => ['-y', '@modelcontextprotocol/server-filesystem', storage_path('app/neuron')],
                'transport' => 'stdio',
                'description' => 'File system access for reading and writing files',
                'tools' => [
                    'exclude' => [],
                    'only' => [],
                ],
            ],

            'fetch' => [
                'enabled' => env('NEURON_MCP_FETCH_ENABLED', false),
                'type' => 'local',
                'command' => 'uvx',
                'args' => ['fetch@latest'],
                'transport' => 'stdio',
                'description' => 'HTTP client for fetching external data',
                'tools' => [
                    'exclude' => [],
                    'only' => [],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Remote MCP Servers
        |--------------------------------------------------------------------------
        |
        | Remote MCP servers are accessed via HTTP/HTTPS URLs.
        | Use URL-based configuration with authentication tokens.
        | Supports SSE (Server-Sent Events) transport for async connections.
        |
        | Example:
        | 'umapyoi' => [
        |     'enabled' => true,
        |     'type' => 'remote',
        |     'url' => 'https://api.umapyoi.net/mcp',
        |     'token' => env('UMAPYOI_API_TOKEN'),
        |     'transport' => 'sse',
        |     'tools' => [
        |         'exclude' => [],
        |         'only' => ['get_character_data', 'get_skill_data'],
        |     ],
        | ],
        |
        */
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

            'custom_api' => [
                'enabled' => env('NEURON_MCP_CUSTOM_API_ENABLED', false),
                'type' => 'remote',
                'url' => env('NEURON_MCP_CUSTOM_API_URL'),
                'token' => env('NEURON_MCP_CUSTOM_API_TOKEN'),
                'transport' => 'sse',
                'description' => 'Custom remote MCP server',
                'tools' => [
                    'exclude' => [],
                    'only' => [],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Connection Settings
        |--------------------------------------------------------------------------
        |
        | Configure connection timeouts and retry behavior for MCP servers.
        |
        */
        'connection' => [
            'timeout' => env('NEURON_MCP_TIMEOUT', 30),
            'retry_attempts' => env('NEURON_MCP_RETRY_ATTEMPTS', 3),
            'retry_delay' => env('NEURON_MCP_RETRY_DELAY', 1000), // milliseconds
        ],

        /*
        |--------------------------------------------------------------------------
        | Tool Filtering
        |--------------------------------------------------------------------------
        |
        | Global tool filtering rules that apply to all MCP servers.
        | Server-specific rules take precedence over global rules.
        |
        */
        'global_tools' => [
            'exclude' => [],  // Tools to exclude from all servers
            'only' => [],     // Only include these tools from all servers (empty = all)
        ],
    ],
];
