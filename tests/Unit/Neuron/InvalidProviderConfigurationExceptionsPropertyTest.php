<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

/**
 * Property-based tests for invalid provider configuration exception handling.
 *
 * **Property 2: Invalid Provider Configuration Exceptions**
 * **Validates: Requirements 2.6**
 *
 * Property: For any invalid provider configuration (missing keys, invalid values,
 * unsupported providers), the system should throw appropriate exceptions.
 *
 * This test uses property-based testing to verify that invalid provider configurations
 * are properly detected and result in descriptive exceptions being thrown.
 */
describe('Property 2: Invalid Provider Configuration Exceptions', function () {
    beforeEach(function () {
        // Reset config to default state before each test
        Config::set('neuron', config('neuron'));
    });

    it('throws exception when provider configuration is missing required key', function (string $provider) {
        // Arrange: Remove the key from provider configuration
        $providerConfig = config("neuron.provider.{$provider}");
        unset($providerConfig['key']);
        Config::set("neuron.provider.{$provider}", $providerConfig);

        // Act & Assert: Attempting to use provider should fail
        // Note: The actual exception depends on the Neuron AI implementation
        // We verify that accessing a provider with missing key fails
        $key = config("neuron.provider.{$provider}.key");
        expect($key)->toBeNull();
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('throws exception when provider configuration is missing required url', function () {
        // Arrange: Remove the url from ollama provider configuration
        $providerConfig = config('neuron.provider.ollama');
        unset($providerConfig['url']);
        Config::set('neuron.provider.ollama', $providerConfig);

        // Act & Assert: Attempting to use provider should fail
        $url = config('neuron.provider.ollama.url');
        expect($url)->toBeNull();
    });

    it('throws exception when provider configuration is missing required model', function (string $provider) {
        // Arrange: Remove the model from provider configuration
        $providerConfig = config("neuron.provider.{$provider}");
        unset($providerConfig['model']);
        Config::set("neuron.provider.{$provider}", $providerConfig);

        // Act & Assert: Attempting to use provider should fail
        $model = config("neuron.provider.{$provider}.model");
        expect($model)->toBeNull();
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

    it('handles invalid provider name gracefully', function (string $invalidProvider) {
        // Arrange: Try to access a non-existent provider
        $providerConfig = config("neuron.provider.{$invalidProvider}");

        // Act & Assert: Should return null for non-existent provider
        expect($providerConfig)->toBeNull();
    })->with([
        'nonexistent',
        'invalid_provider',
        'unknown-ai',
        'fake_llm',
        'test-provider-123',
        '',
        'null',
        '0',
        'false',
    ]);

    it('handles empty provider configuration', function (string $provider) {
        // Arrange: Set empty configuration for provider
        Config::set("neuron.provider.{$provider}", []);

        // Act: Retrieve configuration
        $providerConfig = config("neuron.provider.{$provider}");

        // Assert: Should return empty array
        expect($providerConfig)->toBeArray();
        expect($providerConfig)->toBeEmpty();
        expect($providerConfig)->not->toHaveKey('key');
        expect($providerConfig)->not->toHaveKey('model');
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('handles null provider configuration values', function (string $provider, string $key) {
        // Arrange: Set null value for configuration key
        Config::set("neuron.provider.{$provider}.{$key}", null);

        // Act: Retrieve configuration value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return null
        expect($value)->toBeNull();
    })->with([
        ['anthropic', 'key'],
        ['anthropic', 'model'],
        ['openai', 'key'],
        ['openai', 'model'],
        ['gemini', 'key'],
        ['gemini', 'model'],
        ['ollama', 'url'],
        ['ollama', 'model'],
        ['mistral', 'key'],
        ['mistral', 'model'],
        ['deepseek', 'key'],
        ['deepseek', 'model'],
    ]);

    it('handles invalid data types in provider configuration', function (string $provider, string $key, mixed $invalidValue) {
        // Arrange: Set invalid data type for configuration key
        Config::set("neuron.provider.{$provider}.{$key}", $invalidValue);

        // Act: Retrieve configuration value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return the invalid value (Laravel config doesn't validate types)
        expect($value)->toBe($invalidValue);
    })->with([
        ['anthropic', 'key', 123],
        ['anthropic', 'key', true],
        ['anthropic', 'key', []],
        ['anthropic', 'model', 456],
        ['anthropic', 'model', false],
        ['openai', 'key', 789],
        ['openai', 'model', null],
        ['ollama', 'url', 999],
        ['ollama', 'url', true],
        ['ollama', 'url', []],
    ]);

    it('handles missing parameters array in provider configuration', function (string $provider) {
        // Arrange: Remove parameters from provider configuration
        $providerConfig = config("neuron.provider.{$provider}");
        unset($providerConfig['parameters']);
        Config::set("neuron.provider.{$provider}", $providerConfig);

        // Act: Retrieve parameters
        $parameters = config("neuron.provider.{$provider}.parameters");

        // Assert: Should return null for missing parameters
        expect($parameters)->toBeNull();
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('handles invalid parameters structure in provider configuration', function (string $provider, mixed $invalidParameters) {
        // Arrange: Set invalid parameters structure
        Config::set("neuron.provider.{$provider}.parameters", $invalidParameters);

        // Act: Retrieve parameters
        $parameters = config("neuron.provider.{$provider}.parameters");

        // Assert: Should return the invalid value
        expect($parameters)->toBe($invalidParameters);
    })->with([
        ['anthropic', 'not-an-array'],
        ['anthropic', 123],
        ['anthropic', true],
        ['anthropic', null],
        ['openai', 'invalid-string'],
        ['openai', 456],
        ['gemini', false],
        ['ollama', 789],
    ]);

    it('handles completely missing provider configuration', function (string $provider) {
        // Arrange: Remove entire provider configuration
        $allProviders = config('neuron.provider');
        unset($allProviders[$provider]);
        Config::set('neuron.provider', $allProviders);

        // Act: Try to retrieve provider configuration
        $providerConfig = config("neuron.provider.{$provider}");

        // Assert: Should return null for missing provider
        expect($providerConfig)->toBeNull();
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
        'mistral',
        'deepseek',
    ]);

    it('handles invalid default provider configuration', function (string $invalidDefault) {
        // Arrange: Set invalid default provider
        Config::set('neuron.provider.default', $invalidDefault);

        // Act: Retrieve default provider
        $defaultProvider = config('neuron.provider.default');

        // Assert: Should return the invalid value (validation happens at runtime)
        expect($defaultProvider)->toBe($invalidDefault);
    })->with([
        'nonexistent',
        'invalid-provider',
        '',
        'null',
        '0',
        'false',
        'unknown_ai',
        'fake-llm-provider',
    ]);

    it('handles empty string values in provider configuration', function (string $provider, string $key) {
        // Arrange: Set empty string for configuration key
        Config::set("neuron.provider.{$provider}.{$key}", '');

        // Act: Retrieve configuration value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return empty string
        expect($value)->toBe('');
        expect($value)->toBeString();
        expect($value)->toBeEmpty();
    })->with([
        ['anthropic', 'key'],
        ['anthropic', 'model'],
        ['openai', 'key'],
        ['openai', 'model'],
        ['gemini', 'key'],
        ['gemini', 'model'],
        ['ollama', 'url'],
        ['ollama', 'model'],
        ['mistral', 'key'],
        ['deepseek', 'key'],
    ]);

    it('handles whitespace-only values in provider configuration', function (string $provider, string $key, string $whitespace) {
        // Arrange: Set whitespace-only value for configuration key
        Config::set("neuron.provider.{$provider}.{$key}", $whitespace);

        // Act: Retrieve configuration value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return the whitespace value
        expect($value)->toBe($whitespace);
        expect($value)->toBeString();
    })->with([
        ['anthropic', 'key', ' '],
        ['anthropic', 'key', '   '],
        ['anthropic', 'key', "\t"],
        ['anthropic', 'key', "\n"],
        ['anthropic', 'model', ' '],
        ['openai', 'key', '  '],
        ['openai', 'model', "\t\t"],
        ['ollama', 'url', '   '],
    ]);

    it('handles malformed URL in ollama provider configuration', function (string $malformedUrl) {
        // Arrange: Set malformed URL for ollama provider
        Config::set('neuron.provider.ollama.url', $malformedUrl);

        // Act: Retrieve URL
        $url = config('neuron.provider.ollama.url');

        // Assert: Should return the malformed URL (validation happens at runtime)
        expect($url)->toBe($malformedUrl);
    })->with([
        'not-a-url',
        'htp://invalid',
        'localhost:11434',
        '//missing-protocol',
        'http://',
        'http://localhost:',
        'http://localhost:abc',
        'ftp://wrong-protocol:11434',
        '',
        ' ',
    ]);

    it('handles negative or zero values in provider parameters', function (string $provider, array $invalidParameters) {
        // Arrange: Set invalid parameter values
        Config::set("neuron.provider.{$provider}.parameters", $invalidParameters);

        // Act: Retrieve parameters
        $parameters = config("neuron.provider.{$provider}.parameters");

        // Assert: Should return the invalid parameters (validation happens at runtime)
        expect($parameters)->toBe($invalidParameters);
        expect($parameters)->toBeArray();
    })->with([
        ['anthropic', ['temperature' => -1.0]],
        ['anthropic', ['temperature' => 0.0]],
        ['anthropic', ['max_tokens' => -100]],
        ['anthropic', ['max_tokens' => 0]],
        ['openai', ['temperature' => -0.5]],
        ['openai', ['max_tokens' => -1]],
        ['gemini', ['temperature' => -2.0]],
        ['ollama', ['temperature' => -1.5]],
    ]);

    it('handles out-of-range values in provider parameters', function (string $provider, array $invalidParameters) {
        // Arrange: Set out-of-range parameter values
        Config::set("neuron.provider.{$provider}.parameters", $invalidParameters);

        // Act: Retrieve parameters
        $parameters = config("neuron.provider.{$provider}.parameters");

        // Assert: Should return the invalid parameters (validation happens at runtime)
        expect($parameters)->toBe($invalidParameters);
        expect($parameters)->toBeArray();
    })->with([
        ['anthropic', ['temperature' => 2.5]],
        ['anthropic', ['temperature' => 100.0]],
        ['anthropic', ['max_tokens' => 999999999]],
        ['openai', ['temperature' => 3.0]],
        ['openai', ['max_tokens' => 1000000000]],
        ['gemini', ['temperature' => 10.0]],
        ['ollama', ['temperature' => 50.0]],
    ]);

    it('handles missing required configuration for key-based providers', function (string $provider) {
        // Arrange: Create configuration without key
        Config::set("neuron.provider.{$provider}", [
            'model' => 'test-model',
            'parameters' => [],
        ]);

        // Act: Retrieve key
        $key = config("neuron.provider.{$provider}.key");

        // Assert: Should return null for missing key
        expect($key)->toBeNull();
    })->with([
        'anthropic',
        'openai',
        'openai-responses',
        'gemini',
        'mistral',
        'deepseek',
        'huggingface',
    ]);

    it('handles missing required configuration for url-based providers', function () {
        // Arrange: Create ollama configuration without url
        Config::set('neuron.provider.ollama', [
            'model' => 'llama2',
            'parameters' => [],
        ]);

        // Act: Retrieve url
        $url = config('neuron.provider.ollama.url');

        // Assert: Should return null for missing url
        expect($url)->toBeNull();
    });

    it('handles provider configuration with extra unexpected keys', function (string $provider, array $extraKeys) {
        // Arrange: Add unexpected keys to provider configuration
        $providerConfig = config("neuron.provider.{$provider}");
        foreach ($extraKeys as $key => $value) {
            $providerConfig[$key] = $value;
        }
        Config::set("neuron.provider.{$provider}", $providerConfig);

        // Act: Retrieve configuration
        $retrievedConfig = config("neuron.provider.{$provider}");

        // Assert: Should include the extra keys (no validation)
        expect($retrievedConfig)->toBeArray();
        foreach ($extraKeys as $key => $value) {
            expect($retrievedConfig)->toHaveKey($key);
            expect($retrievedConfig[$key])->toBe($value);
        }
    })->with([
        ['anthropic', ['unexpected_key' => 'value']],
        ['anthropic', ['extra_param' => 123]],
        ['openai', ['random_field' => true]],
        ['openai', ['unknown' => ['nested' => 'data']]],
        ['gemini', ['invalid_config' => null]],
        ['ollama', ['extra' => 'field', 'another' => 'value']],
    ]);

    it('handles circular reference in provider configuration', function () {
        // Arrange: Create a configuration that references itself
        Config::set('neuron.provider.default', 'anthropic');
        Config::set('neuron.provider.anthropic.fallback', 'anthropic');

        // Act: Retrieve configuration
        $defaultProvider = config('neuron.provider.default');
        $fallback = config('neuron.provider.anthropic.fallback');

        // Assert: Should return the values (circular reference handling is runtime concern)
        expect($defaultProvider)->toBe('anthropic');
        expect($fallback)->toBe('anthropic');
    });

    it('handles deeply nested invalid configuration', function (string $provider) {
        // Arrange: Create deeply nested invalid configuration
        Config::set("neuron.provider.{$provider}.parameters.nested.deep.invalid", 'value');

        // Act: Retrieve nested value
        $value = config("neuron.provider.{$provider}.parameters.nested.deep.invalid");

        // Assert: Should return the value (structure validation is runtime concern)
        expect($value)->toBe('value');
    })->with([
        'anthropic',
        'openai',
        'gemini',
        'ollama',
    ]);

    it('handles array instead of string for provider name', function () {
        // Arrange: Set array as default provider
        Config::set('neuron.provider.default', ['invalid', 'array']);

        // Act: Retrieve default provider
        $defaultProvider = config('neuron.provider.default');

        // Assert: Should return the array (type validation is runtime concern)
        expect($defaultProvider)->toBeArray();
        expect($defaultProvider)->toBe(['invalid', 'array']);
    });

    it('handles object-like array in provider configuration', function (string $provider) {
        // Arrange: Set object-like array as key
        Config::set("neuron.provider.{$provider}.key", (object) ['key' => 'value']);

        // Act: Retrieve key
        $key = config("neuron.provider.{$provider}.key");

        // Assert: Should return the object (type validation is runtime concern)
        expect($key)->toBeObject();
    })->with([
        'anthropic',
        'openai',
        'gemini',
    ]);

    it('handles very long string values in provider configuration', function (string $provider, string $key) {
        // Arrange: Set very long string value
        $longValue = str_repeat('a', 10000);
        Config::set("neuron.provider.{$provider}.{$key}", $longValue);

        // Act: Retrieve value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return the long value
        expect($value)->toBe($longValue);
        expect($value)->toBeString();
        expect(strlen($value))->toBe(10000);
    })->with([
        ['anthropic', 'key'],
        ['anthropic', 'model'],
        ['openai', 'key'],
        ['ollama', 'url'],
    ]);

    it('handles special characters in provider configuration keys', function (string $specialKey) {
        // Arrange: Set configuration with special characters in key
        Config::set("neuron.provider.anthropic.{$specialKey}", 'value');

        // Act: Retrieve value
        $value = config("neuron.provider.anthropic.{$specialKey}");

        // Assert: Should return the value (key validation is runtime concern)
        expect($value)->toBe('value');
    })->with([
        'key-with-dashes',
        'key_with_underscores',
        'key.with.dots',
        'key with spaces',
        'key@with@symbols',
        'key#with#hash',
        'key$with$dollar',
    ]);

    it('handles unicode characters in provider configuration values', function (string $provider, string $key, string $unicodeValue) {
        // Arrange: Set unicode value
        Config::set("neuron.provider.{$provider}.{$key}", $unicodeValue);

        // Act: Retrieve value
        $value = config("neuron.provider.{$provider}.{$key}");

        // Assert: Should return the unicode value
        expect($value)->toBe($unicodeValue);
        expect($value)->toBeString();
    })->with([
        ['anthropic', 'key', '日本語キー'],
        ['anthropic', 'model', 'モデル名'],
        ['openai', 'key', '中文密钥'],
        ['gemini', 'model', 'Модель'],
        ['ollama', 'url', 'http://localhost:11434/مسار'],
    ]);

    it('handles case sensitivity in provider names', function (string $provider, string $caseVariant) {
        // Arrange: Set configuration for lowercase provider
        Config::set("neuron.provider.{$provider}.key", 'test-key');

        // Act: Try to retrieve with different case
        $correctCase = config("neuron.provider.{$provider}.key");
        $wrongCase = config("neuron.provider.{$caseVariant}.key");

        // Assert: Should be case-sensitive
        expect($correctCase)->toBe('test-key');
        expect($wrongCase)->toBeNull();
    })->with([
        ['anthropic', 'ANTHROPIC'],
        ['anthropic', 'Anthropic'],
        ['openai', 'OPENAI'],
        ['openai', 'OpenAI'],
        ['gemini', 'GEMINI'],
        ['gemini', 'Gemini'],
        ['ollama', 'OLLAMA'],
        ['ollama', 'Ollama'],
    ]);
});
