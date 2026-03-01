<?php

declare(strict_types=1);

use App\Services\AI\OllamaService;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery as M;

beforeEach(function () {
    // Set default config values
    Config::set('ai.ollama.default_model', 'llama3.3');
    Config::set('ai.ollama.timeout', 15);
    Config::set('ai.ollama.temperature', 0.3);
    Config::set('ai.ollama.max_tokens', 2048);
    Config::set('ai.ollama.available_models', [
        'llama3.3' => ['version' => '1.0.0'],
        'mistral' => ['version' => '0.3.0'],
    ]);

    $this->ollamaService = new OllamaService;
});

afterEach(function () {
    // Reset execution time limit to unlimited after each test.
    // OllamaService::generate() calls set_time_limit() which persists
    // across the entire PHP process and would crash subsequent tests.
    set_time_limit(0);
});

describe('OllamaService', function () {
    describe('generate', function () {
        it('generates AI response successfully', function () {
            Ollama::shouldReceive('agent')
                ->with('Umamusume Career Advisor')
                ->andReturnSelf();
            Ollama::shouldReceive('model')
                ->with('llama3.3')
                ->andReturnSelf();
            Ollama::shouldReceive('prompt')
                ->andReturnSelf();
            Ollama::shouldReceive('options')
                ->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andReturn(['response' => 'Test response from Ollama']);

            $result = $this->ollamaService->generate('Test prompt');

            expect($result)->toHaveKeys(['content', 'model', 'token_count', 'confidence', 'model_version']);
            expect($result['content'])->toBe('Test response from Ollama');
            expect($result['model'])->toBe('llama3.3');
            expect($result['confidence'])->toBe(0.8);
            expect($result['model_version'])->toBe('1.0.0');
        });

        it('generates response with custom model', function () {
            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')
                ->with('mistral')
                ->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andReturn(['response' => 'Mistral response']);

            $result = $this->ollamaService->generate('Test prompt', [], 'mistral');

            expect($result['model'])->toBe('mistral');
            expect($result['model_version'])->toBe('0.3.0');
        });

        it('builds prompt with character context', function () {
            $context = [
                'character' => [
                    'name' => 'Special Week',
                    'scenario_type' => 'URA Finals',
                    'speed' => 500,
                    'stamina' => 400,
                    'power' => 300,
                ],
            ];

            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')
                ->with(M::on(function ($prompt) {
                    return str_contains($prompt, 'Character: Special Week')
                        && str_contains($prompt, 'Scenario: URA Finals')
                        && str_contains($prompt, 'Speed 500, Stamina 400, Power 300');
                }))
                ->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andReturn(['response' => 'Contextual response']);

            $result = $this->ollamaService->generate('What should I train?', $context);

            expect($result['content'])->toBe('Contextual response');
        });

        it('throws exception on generation failure', function () {
            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andThrow(new \Exception('Connection failed'));

            Log::shouldReceive('error')->once();

            $this->ollamaService->generate('Test prompt');
        })->throws(\RuntimeException::class, 'Ollama generation failed');

        it('detects and throws on Ollama error responses', function () {
            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andReturn(['error' => 'Model not found']);

            Log::shouldReceive('error')->once();

            $this->ollamaService->generate('Test prompt');
        })->throws(\RuntimeException::class);
    });

    describe('stream', function () {
        it('streams AI response chunks', function () {
            Ollama::shouldReceive('agent')
                ->with('Umamusume Career Advisor')
                ->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('stream')
                ->with(false)
                ->andReturn(['chunk1', 'chunk2', 'chunk3']);

            $chunks = [];
            foreach ($this->ollamaService->stream('Test prompt') as $chunk) {
                $chunks[] = $chunk;
            }

            expect($chunks)->toBe(['chunk1', 'chunk2', 'chunk3']);
        });

        it('handles streaming errors', function () {
            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('options')->andReturnSelf();
            Ollama::shouldReceive('stream')
                ->andThrow(new \Exception('Stream failed'));

            Log::shouldReceive('error')->once();

            iterator_to_array($this->ollamaService->stream('Test prompt'));
        })->throws(\RuntimeException::class, 'Ollama streaming failed');
    });

    describe('isAvailable', function () {
        it('returns true when Ollama is available', function () {
            Cache::shouldReceive('remember')
                ->once()
                ->andReturnUsing(function ($key, $ttl, $callback) {
                    return $callback();
                });

            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('ask')->andReturn(['response' => 'pong']);

            $result = $this->ollamaService->isAvailable();

            expect($result)->toBeTrue();
        });

        it('returns false when Ollama is unavailable', function () {
            Cache::shouldReceive('remember')
                ->once()
                ->andReturnUsing(function ($key, $ttl, $callback) {
                    return $callback();
                });

            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('ask')
                ->andThrow(new \Exception('Connection refused'));

            Log::shouldReceive('warning')->once();

            $result = $this->ollamaService->isAvailable();

            expect($result)->toBeFalse();
        });

        it('returns cached availability status', function () {
            Cache::shouldReceive('remember')
                ->once()
                ->andReturn(true); // Return cached value without calling callback

            $result = $this->ollamaService->isAvailable();

            expect($result)->toBeTrue();
        });
    });

    describe('getStatus', function () {
        it('returns comprehensive status information', function () {
            Cache::shouldReceive('remember')
                ->andReturnUsing(function ($key, $ttl, $callback) {
                    return $callback();
                });

            Ollama::shouldReceive('agent')->andReturnSelf();
            Ollama::shouldReceive('model')->andReturnSelf();
            Ollama::shouldReceive('prompt')->andReturnSelf();
            Ollama::shouldReceive('ask')->andReturn(['response' => 'pong']);

            $status = $this->ollamaService->getStatus();

            expect($status)->toHaveKeys([
                'available',
                'healthy',
                'default_model',
                'available_models',
                'timeout',
                'temperature',
            ]);
            expect($status['default_model'])->toBe('llama3.3');
            expect($status['timeout'])->toBe(15);
            expect($status['temperature'])->toBe(0.3);
            expect($status['available_models'])->toHaveKey('llama3.3');
        });
    });

    describe('getAvailableModels', function () {
        it('returns configured models', function () {
            $models = $this->ollamaService->getAvailableModels();

            expect($models)->toBeArray();
            expect($models)->toHaveKey('llama3.3');
            expect($models)->toHaveKey('mistral');
        });
    });
});
