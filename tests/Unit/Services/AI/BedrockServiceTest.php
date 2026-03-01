<?php

declare(strict_types=1);

use App\Services\AI\BedrockService;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Set AWS credentials for testing
    Config::set('aws.credentials', [
        'key' => 'test-access-key',
        'secret' => 'test-secret-key',
    ]);
    Config::set('aws.bedrock.region', 'us-east-1');
    Config::set('aws.bedrock.timeout', 30);
    Config::set('ai.bedrock.default_model', 'claude-3-5-sonnet');
    Config::set('ai.bedrock.temperature', 0.3);
    Config::set('ai.bedrock.max_tokens', 4096);
    Config::set('ai.bedrock.pricing', [
        'claude-3-5-sonnet' => ['input' => 3.0, 'output' => 15.0],
        'nova-2-lite' => ['input' => 0.00125, 'output' => 0.00125],
    ]);
});

describe('BedrockService', function () {
    describe('generate with Claude models', function () {
        it('generates AI response successfully with Claude', function () {
            // Mock the Bedrock client
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            // Create mock response body
            $responseBody = json_encode([
                'content' => [
                    ['text' => 'Test response from Claude'],
                ],
                'usage' => [
                    'input_tokens' => 50,
                    'output_tokens' => 100,
                ],
            ]);

            $mockStream = \Mockery::mock(Stream::class);
            $mockStream->shouldReceive('getContents')->andReturn($responseBody);

            $mockResult = new Result([
                'body' => $mockStream,
                'ResponseMetadata' => [
                    'RequestId' => 'test-request-id-123',
                ],
            ]);

            $mockClient->shouldReceive('invokeModel')
                ->once()
                ->with(\Mockery::on(function ($args) {
                    return $args['modelId'] === 'anthropic.claude-3-5-sonnet-20241022-v2:0'
                        && $args['contentType'] === 'application/json';
                }))
                ->andReturn($mockResult);

            // Inject mock client using reflection
            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $result = $service->generate('Test prompt');

            expect($result)->toHaveKeys(['content', 'model', 'token_count', 'confidence', 'model_version', 'request_id']);
            expect($result['content'])->toBe('Test response from Claude');
            expect($result['model'])->toBe('claude-3-5-sonnet');
            expect($result['token_count'])->toBe(150);
            expect($result['confidence'])->toBe(0.9);
            expect($result['request_id'])->toBe('test-request-id-123');
        });

        it('generates response with character context', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            $responseBody = json_encode([
                'content' => [['text' => 'Contextual response']],
                'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
            ]);
            $mockStream = \Mockery::mock(Stream::class);
            $mockStream->shouldReceive('getContents')->andReturn($responseBody);
            $mockResult = new Result(['body' => $mockStream, 'ResponseMetadata' => ['RequestId' => 'test-123']]);

            $mockClient->shouldReceive('invokeModel')
                ->once()
                ->with(\Mockery::on(function ($args) {
                    $payload = json_decode($args['body'], true);
                    $prompt = $payload['messages'][0]['content'] ?? '';

                    return str_contains($prompt, 'Character: Special Week')
                        && str_contains($prompt, 'Speed 500');
                }))
                ->andReturn($mockResult);

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $context = [
                'character' => [
                    'name' => 'Special Week',
                    'scenario_type' => 'URA Finals',
                    'speed' => 500,
                    'stamina' => 400,
                    'power' => 300,
                ],
            ];

            $result = $service->generate('What should I train?', $context);

            expect($result['content'])->toBe('Contextual response');
        });
    });

    describe('generate with Nova models', function () {
        it('generates AI response with Nova model', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            $responseBody = json_encode([
                'results' => [
                    [
                        'outputText' => 'Nova response',
                        'tokenCount' => 75,
                    ],
                ],
            ]);
            $mockStream = \Mockery::mock(Stream::class);
            $mockStream->shouldReceive('getContents')->andReturn($responseBody);
            $mockResult = new Result(['body' => $mockStream, 'ResponseMetadata' => ['RequestId' => 'nova-123']]);

            $mockClient->shouldReceive('invokeModel')
                ->once()
                ->with(\Mockery::on(function ($args) {
                    return $args['modelId'] === 'amazon.nova-lite-v1:0';
                }))
                ->andReturn($mockResult);

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $result = $service->generate('Test prompt', [], 'nova-2-lite');

            expect($result['content'])->toBe('Nova response');
            expect($result['model'])->toBe('nova-2-lite');
            expect($result['token_count'])->toBe(75);
        });

        it('builds correct payload for Nova models', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            $responseBody = json_encode(['results' => [['outputText' => 'test', 'tokenCount' => 10]]]);
            $mockStream = \Mockery::mock(Stream::class);
            $mockStream->shouldReceive('getContents')->andReturn($responseBody);
            $mockResult = new Result(['body' => $mockStream, 'ResponseMetadata' => ['RequestId' => 'test']]);

            $mockClient->shouldReceive('invokeModel')
                ->once()
                ->with(\Mockery::on(function ($args) {
                    $payload = json_decode($args['body'], true);

                    return isset($payload['inputText'])
                        && isset($payload['textGenerationConfig'])
                        && isset($payload['textGenerationConfig']['maxTokenCount']);
                }))
                ->andReturn($mockResult);

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $service->generate('Test', [], 'nova-2-pro');
        });
    });

    describe('error handling', function () {
        it('throws exception when AWS credentials missing', function () {
            Config::set('aws.credentials', []);

            $service = new BedrockService;

            $service->generate('Test prompt');
        })->throws(\RuntimeException::class, 'AWS credentials not configured');

        it('handles AWS API errors gracefully', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            $awsException = new AwsException(
                'ThrottlingException',
                \Mockery::mock(Command::class),
                ['code' => 'ThrottlingException']
            );

            $mockClient->shouldReceive('invokeModel')
                ->once()
                ->andThrow($awsException);

            Log::shouldReceive('error')->once();

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $service->generate('Test prompt');
        })->throws(\RuntimeException::class, 'Bedrock API error');

        it('handles invalid response body', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            // Mock response with string body instead of Stream object
            $mockResult = new Result(['body' => 'invalid-body']);

            $mockClient->shouldReceive('invokeModel')->andReturn($mockResult);

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            Log::shouldReceive('error')->once();

            $service->generate('Test');
        })->throws(\RuntimeException::class);

        it('handles missing content in response', function () {
            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);

            $responseBody = json_encode(['unexpected' => 'structure']);
            $mockStream = \Mockery::mock(Stream::class);
            $mockStream->shouldReceive('getContents')->andReturn($responseBody);
            $mockResult = new Result(['body' => $mockStream, 'ResponseMetadata' => ['RequestId' => 'test']]);

            $mockClient->shouldReceive('invokeModel')->andReturn($mockResult);

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            Log::shouldReceive('error')->once();

            $service->generate('Test');
        })->throws(\RuntimeException::class, 'Unable to extract content');
    });

    describe('isAvailable', function () {
        it('returns true when credentials configured', function () {
            Config::set('aws.credentials', [
                'key' => 'test-key',
                'secret' => 'test-secret',
            ]);

            Cache::shouldReceive('remember')
                ->once()
                ->andReturnUsing(function ($key, $ttl, $callback) {
                    return $callback();
                });

            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);
            $mockClient->shouldReceive('listFoundationModels')
                ->once()
                ->with(['maxResults' => 1])
                ->andReturn(new Result([]));

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $result = $service->isAvailable();

            expect($result)->toBeTrue();
        });

        it('returns false when credentials missing', function () {
            Config::set('aws.credentials', []);

            $service = new BedrockService;
            $result = $service->isAvailable();

            expect($result)->toBeFalse();
        });

        it('returns false when API call fails', function () {
            Config::set('aws.credentials', ['key' => 'test', 'secret' => 'test']);

            Cache::shouldReceive('remember')
                ->once()
                ->andReturnUsing(function ($key, $ttl, $callback) {
                    return $callback();
                });

            $mockClient = \Mockery::mock(BedrockRuntimeClient::class);
            $mockClient->shouldReceive('listFoundationModels')
                ->andThrow(new \Exception('Network error'));

            Log::shouldReceive('warning')->once();

            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setValue($service, $mockClient);

            $result = $service->isAvailable();

            expect($result)->toBeFalse();
        });
    });

    describe('model configuration', function () {
        it('maps model names to Bedrock model IDs correctly', function () {
            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('getModelId');

            expect($method->invoke($service, 'claude-3-5-sonnet'))->toBe('anthropic.claude-3-5-sonnet-20241022-v2:0');
            expect($method->invoke($service, 'claude-3-5-haiku'))->toBe('anthropic.claude-3-5-haiku-20241022-v1:0');
            expect($method->invoke($service, 'nova-2-lite'))->toBe('amazon.nova-lite-v1:0');
            expect($method->invoke($service, 'custom-model'))->toBe('custom-model');
        });

        it('returns correct model versions', function () {
            $service = new BedrockService;
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('getModelVersion');

            expect($method->invoke($service, 'claude-3-5-sonnet'))->toBe('20241022-v2');
            expect($method->invoke($service, 'nova-2-lite'))->toBe('v1');
            expect($method->invoke($service, 'unknown-model'))->toBe('unknown');
        });
    });

    describe('getStatus', function () {
        it('returns comprehensive service status', function () {
            Config::set('aws.credentials', ['key' => 'test', 'secret' => 'test']);

            Cache::shouldReceive('remember')->andReturn(true);

            $service = new BedrockService;
            $status = $service->getStatus();

            expect($status)->toHaveKeys(['available', 'healthy', 'default_model', 'pricing', 'timeout', 'temperature']);
            expect($status['default_model'])->toBe('claude-3-5-sonnet');
            expect($status['timeout'])->toBe(30);
            expect($status['temperature'])->toBe(0.3);
            expect($status['pricing'])->toHaveKey('claude-3-5-sonnet');
        });
    });

    describe('getModelPricing', function () {
        it('returns configured pricing', function () {
            $service = new BedrockService;
            $pricing = $service->getModelPricing();

            expect($pricing)->toBeArray();
            expect($pricing)->toHaveKey('claude-3-5-sonnet');
            expect($pricing['claude-3-5-sonnet'])->toHaveKey('input');
            expect($pricing['claude-3-5-sonnet'])->toHaveKey('output');
        });
    });
});
