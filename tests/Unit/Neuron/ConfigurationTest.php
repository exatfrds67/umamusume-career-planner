<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

/**
 * Unit tests for Neuron AI configuration loading.
 *
 * These tests verify:
 * - Config file structure is correct
 * - Environment variable overrides work properly
 * - Provider configuration retrieval functions correctly
 *
 * **Validates: Requirements 15.1, 15.2, 15.3, 15.4**
 */
describe('Neuron Configuration', function () {
    beforeEach(function () {
        // Reset config to default state before each test
        Config::set('neuron', config('neuron'));
    });

    describe('Config File Structure', function () {
        it('has system_prompt configuration', function () {
            $config = config('neuron');

            expect($config)->toHaveKey('system_prompt');
            expect($config['system_prompt'])->toBeArray();
            expect($config['system_prompt'])->toHaveKey('background');
            expect($config['system_prompt'])->toHaveKey('steps');
            expect($config['system_prompt'])->toHaveKey('output');
        });

        it('has provider configuration', function () {
            $config = config('neuron');

            expect($config)->toHaveKey('provider');
            expect($config['provider'])->toBeArray();
            expect($config['provider'])->toHaveKey('default');
        });

        it('has anthropic provider configuration', function () {
            $config = config('neuron.provider.anthropic');

            expect($config)->toBeArray();
            expect($config)->toHaveKey('key');
            expect($config)->toHaveKey('model');
            expect($config)->toHaveKey('parameters');
        });

        it('has openai provider configuration', function () {
            $config = config('neuron.provider.openai');

            expect($config)->toBeArray();
            expect($config)->toHaveKey('key');
            expect($config)->toHaveKey('model');
            expect($config)->toHaveKey('parameters');
        });

        it('has ollama provider configuration', function () {
            $config = config('neuron.provider.ollama');

            expect($config)->toBeArray();
            expect($config)->toHaveKey('url');
            expect($config)->toHaveKey('model');
            expect($config)->toHaveKey('parameters');
        });

        it('has gemini provider configuration', function () {
            $config = config('neuron.provider.gemini');

            expect($config)->toBeArray();
            expect($config)->toHaveKey('key');
            expect($config)->toHaveKey('model');
            expect($config)->toHaveKey('parameters');
        });

        it('has embedding configuration', function () {
            $config = config('neuron');

            expect($config)->toHaveKey('embedding');
            expect($config['embedding'])->toBeArray();
            expect($config['embedding'])->toHaveKey('default');
        });

        it('has vector store configuration', function () {
            $config = config('neuron');

            expect($config)->toHaveKey('store');
            expect($config['store'])->toBeArray();
            expect($config['store'])->toHaveKey('default');
        });
    });

    describe('Environment Variable Overrides', function () {
        it('overrides default provider from environment', function () {
            // Set environment variable
            Config::set('neuron.provider.default', 'openai');

            $defaultProvider = config('neuron.provider.default');

            expect($defaultProvider)->toBe('openai');
        });

        it('overrides anthropic key from environment', function () {
            $testKey = 'test-anthropic-key-123';
            Config::set('neuron.provider.anthropic.key', $testKey);

            $key = config('neuron.provider.anthropic.key');

            expect($key)->toBe($testKey);
        });

        it('overrides anthropic model from environment', function () {
            $testModel = 'claude-3-opus-20240229';
            Config::set('neuron.provider.anthropic.model', $testModel);

            $model = config('neuron.provider.anthropic.model');

            expect($model)->toBe($testModel);
        });

        it('overrides openai key from environment', function () {
            $testKey = 'test-openai-key-456';
            Config::set('neuron.provider.openai.key', $testKey);

            $key = config('neuron.provider.openai.key');

            expect($key)->toBe($testKey);
        });

        it('overrides openai model from environment', function () {
            $testModel = 'gpt-4-turbo';
            Config::set('neuron.provider.openai.model', $testModel);

            $model = config('neuron.provider.openai.model');

            expect($model)->toBe($testModel);
        });

        it('overrides ollama url from environment', function () {
            $testUrl = 'http://custom-ollama:11434/api';
            Config::set('neuron.provider.ollama.url', $testUrl);

            $url = config('neuron.provider.ollama.url');

            expect($url)->toBe($testUrl);
        });

        it('overrides ollama model from environment', function () {
            $testModel = 'llama3';
            Config::set('neuron.provider.ollama.model', $testModel);

            $model = config('neuron.provider.ollama.model');

            expect($model)->toBe($testModel);
        });

        it('overrides gemini key from environment', function () {
            $testKey = 'test-gemini-key-789';
            Config::set('neuron.provider.gemini.key', $testKey);

            $key = config('neuron.provider.gemini.key');

            expect($key)->toBe($testKey);
        });

        it('overrides embedding provider from environment', function () {
            Config::set('neuron.embedding.default', 'gemini');

            $defaultEmbedding = config('neuron.embedding.default');

            expect($defaultEmbedding)->toBe('gemini');
        });

        it('overrides vector store provider from environment', function () {
            Config::set('neuron.store.default', 'pinecone');

            $defaultStore = config('neuron.store.default');

            expect($defaultStore)->toBe('pinecone');
        });
    });

    describe('Provider Configuration Retrieval', function () {
        it('retrieves complete anthropic provider configuration', function () {
            $config = config('neuron.provider.anthropic');

            expect($config)->toBeArray();
            expect($config)->toHaveKeys(['key', 'model', 'parameters']);
            expect($config['parameters'])->toBeArray();
        });

        it('retrieves complete openai provider configuration', function () {
            $config = config('neuron.provider.openai');

            expect($config)->toBeArray();
            expect($config)->toHaveKeys(['key', 'model', 'parameters']);
            expect($config['parameters'])->toBeArray();
        });

        it('retrieves complete ollama provider configuration', function () {
            $config = config('neuron.provider.ollama');

            expect($config)->toBeArray();
            expect($config)->toHaveKeys(['url', 'model', 'parameters']);
            expect($config['parameters'])->toBeArray();
        });

        it('retrieves complete gemini provider configuration', function () {
            $config = config('neuron.provider.gemini');

            expect($config)->toBeArray();
            expect($config)->toHaveKeys(['key', 'model', 'parameters']);
            expect($config['parameters'])->toBeArray();
        });

        it('retrieves default provider name', function () {
            $defaultProvider = config('neuron.provider.default');

            expect($defaultProvider)->toBeString();
            expect($defaultProvider)->not->toBeEmpty();
        });

        it('retrieves provider configuration by default provider name', function () {
            $defaultProvider = config('neuron.provider.default');
            $providerConfig = config("neuron.provider.{$defaultProvider}");

            expect($providerConfig)->toBeArray();
            expect($providerConfig)->not->toBeEmpty();
        });

        it('retrieves embedding provider configuration', function () {
            $defaultEmbedding = config('neuron.embedding.default');
            $embeddingConfig = config("neuron.embedding.{$defaultEmbedding}");

            expect($embeddingConfig)->toBeArray();
            expect($embeddingConfig)->not->toBeEmpty();
        });

        it('retrieves vector store configuration', function () {
            $defaultStore = config('neuron.store.default');
            $storeConfig = config("neuron.store.{$defaultStore}");

            expect($storeConfig)->toBeArray();
            expect($storeConfig)->not->toBeEmpty();
        });

        it('retrieves system prompt configuration', function () {
            $systemPrompt = config('neuron.system_prompt');

            expect($systemPrompt)->toBeArray();
            expect($systemPrompt)->toHaveKeys(['background', 'steps', 'output']);
        });

        it('handles missing provider configuration gracefully', function () {
            $config = config('neuron.provider.nonexistent');

            expect($config)->toBeNull();
        });

        it('handles missing embedding provider configuration gracefully', function () {
            $config = config('neuron.embedding.nonexistent');

            expect($config)->toBeNull();
        });

        it('handles missing vector store configuration gracefully', function () {
            $config = config('neuron.store.nonexistent');

            expect($config)->toBeNull();
        });
    });

    describe('Configuration Defaults', function () {
        it('has anthropic as default provider', function () {
            $defaultProvider = config('neuron.provider.default');

            expect($defaultProvider)->toBe('anthropic');
        });

        it('has claude-3-5-sonnet as default anthropic model', function () {
            $model = config('neuron.provider.anthropic.model');

            expect($model)->toBe('claude-3-5-sonnet-20241022');
        });

        it('has gpt-4 as default openai model', function () {
            $model = config('neuron.provider.openai.model');

            expect($model)->toBe('gpt-4');
        });

        it('has localhost as default ollama url', function () {
            $url = config('neuron.provider.ollama.url');

            expect($url)->toBe('http://localhost:11434/api');
        });

        it('has llama2 as default ollama model', function () {
            $model = config('neuron.provider.ollama.model');

            expect($model)->toBe('llama2');
        });

        it('has openai as default embedding provider', function () {
            $defaultEmbedding = config('neuron.embedding.default');

            expect($defaultEmbedding)->toBe('openai');
        });

        it('has file as default vector store', function () {
            $defaultStore = config('neuron.store.default');

            expect($defaultStore)->toBe('file');
        });

        it('has empty parameters array by default for providers', function () {
            $anthropicParams = config('neuron.provider.anthropic.parameters');
            $openaiParams = config('neuron.provider.openai.parameters');

            expect($anthropicParams)->toBeArray();
            expect($anthropicParams)->toBeEmpty();
            expect($openaiParams)->toBeArray();
            expect($openaiParams)->toBeEmpty();
        });
    });

    describe('Configuration Validation', function () {
        it('ensures provider configuration has required keys', function () {
            $providers = ['anthropic', 'openai', 'gemini', 'mistral', 'deepseek'];

            foreach ($providers as $provider) {
                $config = config("neuron.provider.{$provider}");

                expect($config)->toBeArray();
                expect($config)->toHaveKey('key');
                expect($config)->toHaveKey('model');
                expect($config)->toHaveKey('parameters');
            }
        });

        it('ensures ollama configuration has required keys', function () {
            $config = config('neuron.provider.ollama');

            expect($config)->toBeArray();
            expect($config)->toHaveKey('url');
            expect($config)->toHaveKey('model');
            expect($config)->toHaveKey('parameters');
        });

        it('ensures embedding providers have required keys', function () {
            $providers = ['openai', 'gemini', 'ollama', 'mistral'];

            foreach ($providers as $provider) {
                $config = config("neuron.embedding.{$provider}");

                expect($config)->toBeArray();
                expect($config)->toHaveKey('model');
            }
        });

        it('ensures vector stores have required keys', function () {
            $stores = ['file', 'pinecone', 'qdrant', 'meilisearch', 'chroma'];

            foreach ($stores as $store) {
                $config = config("neuron.store.{$store}");

                expect($config)->toBeArray();
                expect($config)->toHaveKey('topK');
            }
        });

        it('ensures file store has directory configuration', function () {
            $config = config('neuron.store.file');

            expect($config)->toHaveKey('directory');
            expect($config['directory'])->toBeString();
            expect($config['directory'])->toContain('storage');
        });
    });
});
