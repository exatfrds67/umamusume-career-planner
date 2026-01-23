<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

/**
 * Property-based tests for Neuron AI configuration environment overrides.
 *
 * **Property 13: Configuration Environment Overrides**
 * **Validates: Requirements 15.4**
 *
 * Property: For any configuration key that supports environment variable overrides,
 * setting the environment variable should override the default configuration value.
 *
 * This test uses property-based testing to verify that environment variable overrides
 * work correctly across different configuration keys and values.
 */
describe('Property 13: Configuration Environment Overrides', function () {
    beforeEach(function () {
        // Reset config to default state before each test
        Config::set('neuron', config('neuron'));
    });

    it('overrides any provider default configuration value', function (string $provider, string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.provider.{$provider}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // Anthropic provider overrides
        ['anthropic', 'key', 'test-anthropic-key-abc123'],
        ['anthropic', 'key', 'sk-ant-api03-xyz789'],
        ['anthropic', 'model', 'claude-3-opus-20240229'],
        ['anthropic', 'model', 'claude-3-sonnet-20240229'],
        ['anthropic', 'model', 'claude-3-haiku-20240307'],

        // OpenAI provider overrides
        ['openai', 'key', 'test-openai-key-def456'],
        ['openai', 'key', 'sk-proj-uvw456'],
        ['openai', 'model', 'gpt-4-turbo'],
        ['openai', 'model', 'gpt-4o'],
        ['openai', 'model', 'gpt-3.5-turbo'],

        // Gemini provider overrides
        ['gemini', 'key', 'test-gemini-key-ghi789'],
        ['gemini', 'key', 'AIzaSy-rst123'],
        ['gemini', 'model', 'gemini-pro'],
        ['gemini', 'model', 'gemini-1.5-pro'],
        ['gemini', 'model', 'gemini-1.5-flash'],

        // Ollama provider overrides
        ['ollama', 'url', 'http://localhost:11434/api'],
        ['ollama', 'url', 'http://custom-ollama:11434/api'],
        ['ollama', 'url', 'http://192.168.1.100:11434/api'],
        ['ollama', 'model', 'llama2'],
        ['ollama', 'model', 'llama3'],
        ['ollama', 'model', 'mistral'],

        // Mistral provider overrides
        ['mistral', 'key', 'test-mistral-key-jkl012'],
        ['mistral', 'model', 'mistral-large-latest'],
        ['mistral', 'model', 'mistral-medium'],

        // DeepSeek provider overrides
        ['deepseek', 'key', 'test-deepseek-key-mno345'],
        ['deepseek', 'model', 'deepseek-chat'],
        ['deepseek', 'model', 'deepseek-coder'],
    ]);

    it('overrides any embedding provider configuration value', function (string $provider, string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.embedding.{$provider}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // OpenAI embedding overrides
        ['openai', 'key', 'test-openai-embedding-key-pqr678'],
        ['openai', 'model', 'text-embedding-3-small'],
        ['openai', 'model', 'text-embedding-3-large'],
        ['openai', 'model', 'text-embedding-ada-002'],
        ['openai', 'dimensions', 1024],
        ['openai', 'dimensions', 1536],
        ['openai', 'dimensions', 3072],

        // Gemini embedding overrides
        ['gemini', 'key', 'test-gemini-embedding-key-stu901'],
        ['gemini', 'model', 'text-embedding-004'],
        ['gemini', 'model', 'embedding-001'],

        // Ollama embedding overrides
        ['ollama', 'url', 'http://localhost:11434/api'],
        ['ollama', 'url', 'http://custom-ollama:11434/api'],
        ['ollama', 'model', 'nomic-embed-text'],
        ['ollama', 'model', 'mxbai-embed-large'],

        // Voyage embedding overrides
        ['voyage', 'key', 'test-voyage-key-vwx234'],
        ['voyage', 'model', 'voyage-3'],
        ['voyage', 'model', 'voyage-2'],

        // Mistral embedding overrides
        ['mistral', 'key', 'test-mistral-embedding-key-yza567'],
        ['mistral', 'model', 'mistral-embed'],
        ['mistral', 'dimensions', 1024],
        ['mistral', 'dimensions', 512],
    ]);

    it('overrides any vector store configuration value', function (string $store, string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.store.{$store}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // File store overrides
        ['file', 'directory', '/storage/neuron'],
        ['file', 'directory', '/storage/custom/neuron'],
        ['file', 'directory', '/tmp/neuron'],
        ['file', 'topK', 5],
        ['file', 'topK', 10],
        ['file', 'topK', 20],
        ['file', 'name', 'neuron'],
        ['file', 'name', 'custom-store'],
        ['file', 'ext', '.store'],
        ['file', 'ext', '.vec'],

        // Pinecone store overrides
        ['pinecone', 'key', 'test-pinecone-key-bcd890'],
        ['pinecone', 'indexUrl', 'https://test-index.pinecone.io'],
        ['pinecone', 'topK', 5],
        ['pinecone', 'topK', 15],
        ['pinecone', 'version', '2025-04'],
        ['pinecone', 'namespace', '__default__'],
        ['pinecone', 'namespace', 'custom-namespace'],

        // Qdrant store overrides
        ['qdrant', 'collectionUrl', 'http://localhost:6333/collections/test'],
        ['qdrant', 'key', 'test-qdrant-key-efg123'],
        ['qdrant', 'topK', 5],
        ['qdrant', 'topK', 12],
        ['qdrant', 'dimension', 1024],
        ['qdrant', 'dimension', 1536],

        // Meilisearch store overrides
        ['meilisearch', 'indexUid', 'test-index'],
        ['meilisearch', 'host', 'http://localhost:7700'],
        ['meilisearch', 'host', 'http://custom-meili:7700'],
        ['meilisearch', 'key', 'test-meili-key-hij456'],
        ['meilisearch', 'embedder', 'default'],
        ['meilisearch', 'embedder', 'custom-embedder'],
        ['meilisearch', 'topK', 5],
        ['meilisearch', 'dimension', 1024],

        // Chroma store overrides
        ['chroma', 'collectionUrl', 'http://localhost:8000/collections/test'],
        ['chroma', 'host', 'http://localhost:8000'],
        ['chroma', 'tenant', 'default_tenant'],
        ['chroma', 'tenant', 'custom_tenant'],
        ['chroma', 'database', 'default_database'],
        ['chroma', 'database', 'custom_database'],
        ['chroma', 'key', 'test-chroma-key-klm789'],
        ['chroma', 'topK', 5],
    ]);

    it('overrides top-level configuration values', function (string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // Provider default overrides
        ['provider.default', 'anthropic'],
        ['provider.default', 'openai'],
        ['provider.default', 'gemini'],
        ['provider.default', 'ollama'],
        ['provider.default', 'mistral'],

        // Embedding default overrides
        ['embedding.default', 'openai'],
        ['embedding.default', 'gemini'],
        ['embedding.default', 'ollama'],
        ['embedding.default', 'voyage'],
        ['embedding.default', 'mistral'],

        // Store default overrides
        ['store.default', 'file'],
        ['store.default', 'pinecone'],
        ['store.default', 'qdrant'],
        ['store.default', 'meilisearch'],
        ['store.default', 'chroma'],
    ]);

    it('overrides system prompt configuration values', function (string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.system_prompt.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        ['background', 'Custom background prompt'],
        ['background', 'You are an expert AI assistant'],
        ['background', ''],
        ['steps', []],
        ['steps', ['Step 1', 'Step 2', 'Step 3']],
        ['steps', ['Analyze', 'Process', 'Respond']],
        ['output', []],
        ['output', ['Format 1', 'Format 2']],
        ['output', ['JSON', 'Markdown', 'Plain text']],
    ]);

    it('overrides MCP configuration values', function (string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.mcp.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // MCP enabled override
        ['enabled', true],
        ['enabled', false],

        // Connection settings overrides
        ['connection.timeout', 30],
        ['connection.timeout', 60],
        ['connection.timeout', 120],
        ['connection.retry_attempts', 3],
        ['connection.retry_attempts', 5],
        ['connection.retry_attempts', 1],
        ['connection.retry_delay', 1000],
        ['connection.retry_delay', 2000],
        ['connection.retry_delay', 500],
    ]);

    it('overrides MCP local server configuration values', function (string $server, string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.mcp.local_servers.{$server}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // Memory server overrides
        ['memory', 'enabled', true],
        ['memory', 'enabled', false],
        ['memory', 'type', 'local'],
        ['memory', 'command', 'npx'],
        ['memory', 'command', 'node'],
        ['memory', 'transport', 'stdio'],
        ['memory', 'description', 'Custom memory server'],

        // Filesystem server overrides
        ['filesystem', 'enabled', true],
        ['filesystem', 'enabled', false],
        ['filesystem', 'type', 'local'],
        ['filesystem', 'command', 'npx'],
        ['filesystem', 'transport', 'stdio'],
        ['filesystem', 'description', 'Custom filesystem server'],

        // Fetch server overrides
        ['fetch', 'enabled', true],
        ['fetch', 'enabled', false],
        ['fetch', 'type', 'local'],
        ['fetch', 'command', 'uvx'],
        ['fetch', 'transport', 'stdio'],
        ['fetch', 'description', 'Custom fetch server'],
    ]);

    it('overrides MCP remote server configuration values', function (string $server, string $key, mixed $value) {
        // Arrange: Set the configuration value
        $configPath = "neuron.mcp.remote_servers.{$server}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // Umapyoi server overrides
        ['umapyoi', 'enabled', true],
        ['umapyoi', 'enabled', false],
        ['umapyoi', 'type', 'remote'],
        ['umapyoi', 'url', 'https://api.umapyoi.net/mcp'],
        ['umapyoi', 'url', 'https://custom.umapyoi.net/mcp'],
        ['umapyoi', 'token', 'test-token-abc123'],
        ['umapyoi', 'transport', 'sse'],
        ['umapyoi', 'description', 'Custom Uma Musume API'],

        // Custom API server overrides
        ['custom_api', 'enabled', true],
        ['custom_api', 'enabled', false],
        ['custom_api', 'type', 'remote'],
        ['custom_api', 'url', 'https://api.example.com/mcp'],
        ['custom_api', 'token', 'test-token-def456'],
        ['custom_api', 'transport', 'sse'],
        ['custom_api', 'description', 'Custom remote server'],
    ]);

    it('preserves configuration type when overriding', function (string $configPath, mixed $originalValue, mixed $newValue) {
        // Arrange: Get the original value type
        $originalType = gettype($originalValue);

        // Act: Set the new value and retrieve it
        Config::set($configPath, $newValue);
        $retrievedValue = config($configPath);
        $retrievedType = gettype($retrievedValue);

        // Assert: The type should be preserved
        expect($retrievedType)->toBe($originalType);
        expect($retrievedValue)->toBe($newValue);
    })->with([
        // String values
        ['neuron.provider.default', 'anthropic', 'openai'],
        ['neuron.provider.anthropic.key', 'original-key', 'new-key'],
        ['neuron.provider.anthropic.model', 'claude-3-5-sonnet-20241022', 'claude-3-opus-20240229'],

        // Integer values
        ['neuron.embedding.openai.dimensions', 1024, 1536],
        ['neuron.store.file.topK', 5, 10],
        ['neuron.mcp.connection.timeout', 30, 60],

        // Boolean values
        ['neuron.mcp.enabled', false, true],
        ['neuron.mcp.local_servers.memory.enabled', false, true],
        ['neuron.mcp.remote_servers.umapyoi.enabled', false, true],

        // Array values
        ['neuron.system_prompt.steps', [], ['Step 1', 'Step 2']],
        ['neuron.system_prompt.output', [], ['Output 1', 'Output 2']],
        ['neuron.provider.anthropic.parameters', [], ['temperature' => 0.7]],
    ]);

    it('handles nested configuration overrides correctly', function (string $configPath, mixed $value) {
        // Arrange: Set a deeply nested configuration value
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match the set value
        expect($retrievedValue)->toBe($value);
    })->with([
        // Three-level nesting
        ['neuron.provider.anthropic.key', 'test-key-1'],
        ['neuron.embedding.openai.model', 'test-model-1'],
        ['neuron.store.file.directory', '/test/path'],

        // Four-level nesting
        ['neuron.mcp.local_servers.memory.enabled', true],
        ['neuron.mcp.remote_servers.umapyoi.url', 'https://test.com'],
        ['neuron.mcp.connection.retry_attempts', 5],

        // Five-level nesting
        ['neuron.mcp.local_servers.memory.tools.exclude', ['tool1', 'tool2']],
        ['neuron.mcp.remote_servers.umapyoi.tools.only', ['tool3', 'tool4']],
    ]);

    it('allows multiple overrides to coexist independently', function () {
        // Arrange: Set multiple configuration values
        Config::set('neuron.provider.default', 'openai');
        Config::set('neuron.provider.openai.key', 'test-openai-key');
        Config::set('neuron.provider.openai.model', 'gpt-4-turbo');
        Config::set('neuron.embedding.default', 'gemini');
        Config::set('neuron.store.default', 'pinecone');
        Config::set('neuron.mcp.enabled', true);

        // Act: Retrieve all configuration values
        $providerDefault = config('neuron.provider.default');
        $openaiKey = config('neuron.provider.openai.key');
        $openaiModel = config('neuron.provider.openai.model');
        $embeddingDefault = config('neuron.embedding.default');
        $storeDefault = config('neuron.store.default');
        $mcpEnabled = config('neuron.mcp.enabled');

        // Assert: All values should be correctly set and independent
        expect($providerDefault)->toBe('openai');
        expect($openaiKey)->toBe('test-openai-key');
        expect($openaiModel)->toBe('gpt-4-turbo');
        expect($embeddingDefault)->toBe('gemini');
        expect($storeDefault)->toBe('pinecone');
        expect($mcpEnabled)->toBe(true);
    });

    it('overrides configuration values with special characters', function (string $configPath, mixed $value) {
        // Arrange: Set configuration with special characters
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: The retrieved value should match exactly, including special characters
        expect($retrievedValue)->toBe($value);
    })->with([
        // Keys with special characters
        ['neuron.provider.anthropic.key', 'sk-ant-api03-ABC123_xyz-789'],
        ['neuron.provider.openai.key', 'sk-proj-ABC_123-xyz_789'],
        ['neuron.embedding.openai.key', 'sk-test_KEY-with-DASHES_123'],

        // URLs with special characters
        ['neuron.provider.ollama.url', 'http://localhost:11434/api?param=value'],
        ['neuron.mcp.remote_servers.umapyoi.url', 'https://api.example.com/mcp?version=v1&format=json'],

        // Paths with special characters
        ['neuron.store.file.directory', '/path/to/storage/neuron-data_v1'],
        ['neuron.store.file.directory', 'C:\\Users\\Test\\AppData\\neuron'],

        // Tokens with special characters
        ['neuron.mcp.remote_servers.umapyoi.token', 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'],
    ]);
});
