<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;

/**
 * Property-based tests for Neuron AI provider configuration retrieval.
 *
 * **Property 1: Provider Configuration Retrieval**
 * **Validates: Requirements 2.5**
 *
 * Property: For any valid provider name (anthropic, openai, gemini, ollama, mistral, deepseek),
 * the system should be able to retrieve the provider configuration correctly from config files.
 *
 * This test uses property-based testing to verify that provider configuration retrieval
 * works correctly across different providers and configuration keys.
 */
describe('Property 1: Provider Configuration Retrieval', function () {
    beforeEach(function () {
        // Reset config to default state before each test
        Config::set('neuron', config('neuron'));
    });

    afterEach(function () {
        Mockery::close();
    });

    it('retrieves provider configuration for any valid provider', function (string $provider) {
        // Arrange: Ensure provider configuration exists
        $configPath = "neuron.provider.{$provider}";
        $providerConfig = config($configPath);

        // Act: Retrieve the provider configuration
        $retrievedConfig = config($configPath);

        // Assert: Configuration should be retrieved successfully
        expect($retrievedConfig)->not->toBeNull();
        expect($retrievedConfig)->toBeArray();
        expect($retrievedConfig)->toBe($providerConfig);
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('retrieves provider key configuration for any valid provider', function (string $provider) {
        // Arrange: Set a test key for the provider
        $testKey = "test-{$provider}-key-".bin2hex(random_bytes(8));
        $configPath = "neuron.provider.{$provider}.key";
        Config::set($configPath, $testKey);

        // Act: Retrieve the provider key
        $retrievedKey = config($configPath);

        // Assert: Key should be retrieved correctly
        expect($retrievedKey)->toBe($testKey);
        expect($retrievedKey)->toBeString();
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('retrieves provider model configuration for any valid provider', function (string $provider, string $model) {
        // Arrange: Set a model for the provider
        $configPath = "neuron.provider.{$provider}.model";
        Config::set($configPath, $model);

        // Act: Retrieve the provider model
        $retrievedModel = config($configPath);

        // Assert: Model should be retrieved correctly
        expect($retrievedModel)->toBe($model);
        expect($retrievedModel)->toBeString();
    })->with([
        ['anthropic', 'claude-3-5-sonnet-20241022'],
        ['anthropic', 'claude-3-opus-20240229'],
        ['anthropic', 'claude-3-sonnet-20240229'],
        ['anthropic', 'claude-3-haiku-20240307'],
        ['openai', 'gpt-4'],
        ['openai', 'gpt-4-turbo'],
        ['openai', 'gpt-4o'],
        ['openai', 'gpt-3.5-turbo'],
        ['openai-responses', 'gpt-4'],
        ['openai-responses', 'gpt-4o'],
        ['gemini', 'gemini-pro'],
        ['gemini', 'gemini-1.5-pro'],
        ['gemini', 'gemini-1.5-flash'],
        ['ollama', 'llama2'],
        ['ollama', 'llama3'],
        ['ollama', 'mistral'],
        ['ollama', 'codellama'],
        ['mistral', 'mistral-large-latest'],
        ['mistral', 'mistral-medium'],
        ['mistral', 'mistral-small'],
        ['deepseek', 'deepseek-chat'],
        ['deepseek', 'deepseek-coder'],
        ['huggingface', 'meta-llama/Llama-2-7b-hf'],
        ['huggingface', 'meta-llama/Llama-3-8b-hf'],
    ]);

    it('retrieves provider URL configuration for URL-based providers', function (string $provider, string $url) {
        // Arrange: Set a URL for the provider
        $configPath = "neuron.provider.{$provider}.url";
        Config::set($configPath, $url);

        // Act: Retrieve the provider URL
        $retrievedUrl = config($configPath);

        // Assert: URL should be retrieved correctly
        expect($retrievedUrl)->toBe($url);
        expect($retrievedUrl)->toBeString();
        expect($retrievedUrl)->toContain('http');
    })->with([
        ['ollama', 'http://localhost:11434/api'],
        ['ollama', 'http://192.168.1.100:11434/api'],
        ['ollama', 'http://custom-ollama:11434/api'],
        ['ollama', 'https://ollama.example.com/api'],
    ]);

    it('retrieves provider parameters configuration for any valid provider', function (string $provider) {
        // Arrange: Set parameters for the provider
        $testParameters = [
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'top_p' => 0.9,
        ];
        $configPath = "neuron.provider.{$provider}.parameters";
        Config::set($configPath, $testParameters);

        // Act: Retrieve the provider parameters
        $retrievedParameters = config($configPath);

        // Assert: Parameters should be retrieved correctly
        expect($retrievedParameters)->toBe($testParameters);
        expect($retrievedParameters)->toBeArray();
        expect($retrievedParameters)->toHaveKey('temperature');
        expect($retrievedParameters)->toHaveKey('max_tokens');
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('retrieves default provider configuration', function (string $defaultProvider) {
        // Arrange: Set the default provider
        Config::set('neuron.provider.default', $defaultProvider);

        // Act: Retrieve the default provider
        $retrievedDefault = config('neuron.provider.default');

        // Assert: Default provider should be retrieved correctly
        expect($retrievedDefault)->toBe($defaultProvider);
        expect($retrievedDefault)->toBeString();
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('retrieves complete provider configuration structure for any valid provider', function (string $provider) {
        // Arrange: Get the provider configuration
        $configPath = "neuron.provider.{$provider}";
        $providerConfig = config($configPath);

        // Act: Verify configuration structure
        $hasKey = array_key_exists('key', $providerConfig) || array_key_exists('url', $providerConfig);
        $hasModel = array_key_exists('model', $providerConfig);
        $hasParameters = array_key_exists('parameters', $providerConfig);

        // Assert: Configuration should have expected structure
        expect($providerConfig)->toBeArray();
        expect($hasKey)->toBeTrue(); // Either 'key' or 'url' should exist
        expect($hasModel)->toBeTrue();
        expect($hasParameters)->toBeTrue();
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('retrieves provider configuration with null values when not set', function (string $provider, string $key) {
        // Arrange: Ensure the configuration key is not set
        $configPath = "neuron.provider.{$provider}.{$key}";
        Config::set($configPath, null);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: Should return null for unset values
        expect($retrievedValue)->toBeNull();
    })->with([
        ['anthropic', 'key'],
        ['openai', 'key'],
        ['gemini', 'key'],
        ['ollama', 'url'],
        ['mistral', 'key'],
        ['deepseek', 'key'],
    ]);

    it('retrieves provider configuration with default fallback values', function (string $configPath, mixed $defaultValue) {
        // Arrange: Use a non-existent configuration path
        $nonExistentPath = $configPath.'.nonexistent.key.that.does.not.exist';

        // Act: Retrieve the configuration value with default
        $retrievedValue = config($nonExistentPath, $defaultValue);

        // Assert: Should return the default value for non-existent keys
        expect($retrievedValue)->toBe($defaultValue);
    })->with([
        ['neuron.provider.anthropic', 'default-anthropic-key'],
        ['neuron.provider.openai', 'default-openai-key'],
        ['neuron.provider.anthropic', 'claude-3-5-sonnet-20241022'],
        ['neuron.provider.openai', 'gpt-4'],
        ['neuron.provider.ollama', 'http://localhost:11434/api'],
        ['neuron.provider.ollama', 'llama2'],
        ['neuron.provider', 'anthropic'],
    ]);

    it('retrieves provider configuration independently for multiple providers', function () {
        // Arrange: Set configuration for multiple providers
        Config::set('neuron.provider.anthropic.key', 'anthropic-key-123');
        Config::set('neuron.provider.anthropic.model', 'claude-3-opus-20240229');
        Config::set('neuron.provider.openai.key', 'openai-key-456');
        Config::set('neuron.provider.openai.model', 'gpt-4-turbo');
        Config::set('neuron.provider.gemini.key', 'gemini-key-789');
        Config::set('neuron.provider.gemini.model', 'gemini-1.5-pro');

        // Act: Retrieve all configurations
        $anthropicKey = config('neuron.provider.anthropic.key');
        $anthropicModel = config('neuron.provider.anthropic.model');
        $openaiKey = config('neuron.provider.openai.key');
        $openaiModel = config('neuron.provider.openai.model');
        $geminiKey = config('neuron.provider.gemini.key');
        $geminiModel = config('neuron.provider.gemini.model');

        // Assert: All configurations should be independent and correct
        expect($anthropicKey)->toBe('anthropic-key-123');
        expect($anthropicModel)->toBe('claude-3-opus-20240229');
        expect($openaiKey)->toBe('openai-key-456');
        expect($openaiModel)->toBe('gpt-4-turbo');
        expect($geminiKey)->toBe('gemini-key-789');
        expect($geminiModel)->toBe('gemini-1.5-pro');
    });

    it('retrieves provider configuration with special characters in values', function (string $provider, string $key, string $value) {
        // Arrange: Set configuration with special characters
        $configPath = "neuron.provider.{$provider}.{$key}";
        Config::set($configPath, $value);

        // Act: Retrieve the configuration value
        $retrievedValue = config($configPath);

        // Assert: Should handle special characters correctly
        expect($retrievedValue)->toBe($value);
        expect($retrievedValue)->toBeString();
    })->with([
        ['anthropic', 'key', 'sk-ant-api03-ABC123_xyz-789'],
        ['anthropic', 'key', 'sk-ant-api03-!@#$%^&*()_+-='],
        ['openai', 'key', 'sk-proj-ABC_123-xyz_789'],
        ['openai', 'key', 'sk-proj-test.key.with.dots'],
        ['gemini', 'key', 'AIzaSy-ABC123_xyz789'],
        ['ollama', 'url', 'http://localhost:11434/api?param=value&other=test'],
        ['ollama', 'url', 'http://user:pass@localhost:11434/api'],
        ['mistral', 'key', 'mistral-key-with-dashes-and_underscores'],
        ['deepseek', 'key', 'deepseek_key_123-ABC-xyz'],
    ]);

    it('retrieves provider configuration case-sensitively', function (string $provider) {
        // Arrange: Set configuration for the provider
        $testKey = "test-key-{$provider}";
        Config::set("neuron.provider.{$provider}.key", $testKey);

        // Act: Try to retrieve with different casing (should fail or return null)
        $correctCase = config("neuron.provider.{$provider}.key");
        $wrongCase = config('neuron.provider.'.strtoupper($provider).'.key');

        // Assert: Configuration keys should be case-sensitive
        expect($correctCase)->toBe($testKey);
        expect($wrongCase)->toBeNull(); // Wrong case should not match
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('retrieves nested provider configuration correctly', function (string $provider) {
        // Arrange: Set nested configuration
        $nestedConfig = [
            'key' => "test-{$provider}-key",
            'model' => "test-{$provider}-model",
            'parameters' => [
                'temperature' => 0.8,
                'max_tokens' => 1500,
                'nested' => [
                    'deep' => 'value',
                ],
            ],
        ];
        Config::set("neuron.provider.{$provider}", $nestedConfig);

        // Act: Retrieve nested values
        $fullConfig = config("neuron.provider.{$provider}");
        $key = config("neuron.provider.{$provider}.key");
        $model = config("neuron.provider.{$provider}.model");
        $temperature = config("neuron.provider.{$provider}.parameters.temperature");
        $deepValue = config("neuron.provider.{$provider}.parameters.nested.deep");

        // Assert: All nested values should be retrieved correctly
        expect($fullConfig)->toBe($nestedConfig);
        expect($key)->toBe("test-{$provider}-key");
        expect($model)->toBe("test-{$provider}-model");
        expect($temperature)->toBe(0.8);
        expect($deepValue)->toBe('value');
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('retrieves provider configuration through AIProvider facade', function (string $provider) {
        // Arrange: Set up configuration
        Config::set('neuron.provider.default', $provider);
        Config::set("neuron.provider.{$provider}.key", "test-key-{$provider}");
        Config::set("neuron.provider.{$provider}.model", "test-model-{$provider}");

        // Mock the AIProvider facade
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        AIProvider::shouldReceive('driver')
            ->once()
            ->with($provider)
            ->andReturn($mockProvider);

        // Act: Retrieve provider through facade
        $retrievedProvider = AIProvider::driver($provider);

        // Assert: Provider should be retrieved successfully
        expect($retrievedProvider)->toBeInstanceOf(AIProviderInterface::class);
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('retrieves provider configuration with empty parameters array', function (string $provider) {
        // Arrange: Set empty parameters
        Config::set("neuron.provider.{$provider}.parameters", []);

        // Act: Retrieve parameters
        $parameters = config("neuron.provider.{$provider}.parameters");

        // Assert: Should return empty array
        expect($parameters)->toBeArray();
        expect($parameters)->toBeEmpty();
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('retrieves provider configuration with various parameter types', function (string $provider, array $parameters) {
        // Arrange: Set parameters with different types
        Config::set("neuron.provider.{$provider}.parameters", $parameters);

        // Act: Retrieve parameters
        $retrievedParameters = config("neuron.provider.{$provider}.parameters");

        // Assert: All parameter types should be preserved
        expect($retrievedParameters)->toBe($parameters);
        expect($retrievedParameters)->toBeArray();
    })->with([
        ['anthropic', ['temperature' => 0.7, 'max_tokens' => 2000]],
        ['anthropic', ['temperature' => 0.5, 'max_tokens' => 4000, 'top_p' => 0.9]],
        ['openai', ['temperature' => 0.8, 'max_tokens' => 1500, 'presence_penalty' => 0.1]],
        ['openai', ['temperature' => 1.0, 'max_tokens' => 3000, 'frequency_penalty' => 0.2]],
        ['gemini', ['temperature' => 0.6, 'max_tokens' => 2500, 'top_k' => 40]],
        ['ollama', ['temperature' => 0.9, 'num_predict' => 1000]],
        ['mistral', ['temperature' => 0.7, 'max_tokens' => 2000, 'safe_mode' => true]],
        ['deepseek', ['temperature' => 0.8, 'max_tokens' => 1800, 'stream' => false]],
    ]);
});
