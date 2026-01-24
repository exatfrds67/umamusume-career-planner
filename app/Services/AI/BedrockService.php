<?php

namespace App\Services\AI;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * AWS Bedrock AI Service
 *
 * Manages AWS Bedrock model interactions for cloud-based AI processing.
 * Supports Claude 4.5 series, Nova 2, and other Bedrock models.
 *
 * Requirements: 13.1, 56.2
 */
class BedrockService
{
    protected BedrockRuntimeClient $client;

    protected string $defaultModel;

    protected int $timeout;

    protected float $temperature;

    protected int $maxTokens;

    /** @var array<string, mixed> */
    protected array $modelPricing;

    public function __construct()
    {
        $defaultModelConfig = Config::get('ai.bedrock.default_model', 'claude-3-5-sonnet');
        $this->defaultModel = is_scalar($defaultModelConfig) ? (string) $defaultModelConfig : 'claude-3-5-sonnet';

        $timeoutConfig = Config::get('aws.bedrock.timeout', 30);
        $this->timeout = is_numeric($timeoutConfig) ? (int) $timeoutConfig : 30;

        $temperatureConfig = Config::get('ai.bedrock.temperature', 0.3);
        $this->temperature = is_numeric($temperatureConfig) ? (float) $temperatureConfig : 0.3;

        $maxTokensConfig = Config::get('ai.bedrock.max_tokens', 4096);
        $this->maxTokens = is_numeric($maxTokensConfig) ? (int) $maxTokensConfig : 4096;

        $pricing = Config::get('ai.bedrock.pricing', []);
        $this->modelPricing = \is_array($pricing) ? $pricing : [];

        $this->initializeClient();
    }

    /**
     * Initialize Bedrock client with AWS configuration
     */
    protected function initializeClient(): void
    {
        try {
            $credentials = Config::get('aws.credentials', []);
            $accessKey = (is_array($credentials) && isset($credentials['key']) ? $credentials['key'] : null);
            $secretKey = (is_array($credentials) && isset($credentials['secret']) ? $credentials['secret'] : null);

            if (! $accessKey || ! $secretKey) {
                throw new \RuntimeException('AWS credentials not configured. Set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY environment variables.');
            }

            $this->client = new BedrockRuntimeClient([
                'region' => Config::get('aws.bedrock.region', Config::get('aws.region', 'us-east-1')),
                'version' => Config::get('aws.bedrock.version', 'latest'),
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
                'http' => [
                    'timeout' => $this->timeout,
                    'connect_timeout' => Config::get('aws.bedrock.connect_timeout', 10),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[Bedrock] Client initialization failed', [
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Bedrock client initialization failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Generate AI response using Bedrock
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     content: string,
     *     model: string,
     *     token_count: int,
     *     confidence: float,
     *     model_version: string,
     *     request_id: string
     * }
     */
    public function generate(string $prompt = '', array $context = [], ?string $model = null): array
    {
        $model = $model ?? $this->defaultModel;
        $modelId = $this->getModelId($model);

        try {
            // Build request payload
            $payload = $this->buildPayload($prompt, $context, $model);

            // Invoke Bedrock model
            $response = $this->client->invokeModel([
                'modelId' => $modelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => json_encode($payload),
            ]);

            // Parse response
            $body = $response['body'] ?? null;
            if (! \is_object($body) || ! method_exists($body, 'getContents')) {
                throw new \RuntimeException('Invalid Bedrock response body');
            }
            $responseBody = json_decode($body->getContents(), true);

            if (! \is_array($responseBody)) {
                throw new \RuntimeException('Failed to parse Bedrock response');
            }

            /** @var array<string, mixed> $responseBody */

            // Extract content based on model type
            $content = $this->extractContent($responseBody, $model);
            $tokenCount = $this->extractTokenCount($responseBody, $model);

            // Get request ID safely
            $requestId = 'unknown';
            $metadata = $response['ResponseMetadata'] ?? null;
            if (\is_array($metadata) && isset($metadata['RequestId']) && is_scalar($metadata['RequestId'])) {
                $requestId = (string) $metadata['RequestId'];
            }

            return [
                'content' => $content,
                'model' => $model,
                'token_count' => $tokenCount,
                'confidence' => 0.9, // Bedrock models have high confidence
                'model_version' => $this->getModelVersion($model),
                'request_id' => $requestId,
            ];
        } catch (AwsException $e) {
            Log::error('[Bedrock] AWS API error', [
                'error' => $e->getMessage(),
                'model' => $model,
                'aws_error_code' => $e->getAwsErrorCode(),
            ]);

            throw new \RuntimeException("Bedrock API error: {$e->getMessage()}", 0, $e);
        } catch (\Exception $e) {
            Log::error('[Bedrock] Generation failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            throw new \RuntimeException("Bedrock generation failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Build request payload for Bedrock model
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function buildPayload(string $prompt = '', array $context = [], string $model = ''): array
    {
        // Build full prompt with context
        $fullPrompt = $this->buildPromptWithContext($prompt, $context);

        // Claude models use messages API
        if (str_starts_with($model, 'claude')) {
            return [
                'anthropic_version' => 'bedrock-2023-05-31',
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $fullPrompt,
                    ],
                ],
            ];
        }

        // Nova models use different format
        if (str_starts_with($model, 'nova')) {
            return [
                'inputText' => $fullPrompt,
                'textGenerationConfig' => [
                    'maxTokenCount' => $this->maxTokens,
                    'temperature' => $this->temperature,
                    'topP' => 0.9,
                ],
            ];
        }

        // Default format
        return [
            'prompt' => $fullPrompt,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];
    }

    /**
     * Build prompt with context
     *
     * @param  array<string, mixed>  $context
     */
    protected function buildPromptWithContext(string $prompt, array $context = []): string
    {
        if (empty($context)) {
            return $prompt;
        }

        $contextStr = "Context:\n";

        // Add character context
        if (isset($context['character']) && is_array($context['character'])) {
            $char = $context['character'];
            $name = isset($char['name']) && is_string($char['name']) ? $char['name'] : 'Unknown';
            $scenario = isset($char['scenario_type']) && is_string($char['scenario_type']) ? $char['scenario_type'] : 'Unknown';
            $speed = isset($char['speed']) && is_scalar($char['speed']) ? (string) $char['speed'] : '0';
            $stamina = isset($char['stamina']) && is_scalar($char['stamina']) ? (string) $char['stamina'] : '0';
            $power = isset($char['power']) && is_scalar($char['power']) ? (string) $char['power'] : '0';

            $contextStr .= "Character: {$name}\n";
            $contextStr .= "Scenario: {$scenario}\n";
            $contextStr .= "Stats: Speed {$speed}, Stamina {$stamina}, Power {$power}\n";
        }

        // Add career context
        if (isset($context['career']) && is_array($context['career'])) {
            $career = $context['career'];
            $stage = isset($career['stage']) && is_string($career['stage']) ? $career['stage'] : 'Unknown';
            $turn = isset($career['turn']) && is_scalar($career['turn']) ? (string) $career['turn'] : '0';
            $totalTurns = isset($career['total_turns']) && is_scalar($career['total_turns']) ? (string) $career['total_turns'] : '0';

            $contextStr .= "Career Stage: {$stage}\n";
            $contextStr .= "Turn: {$turn}/{$totalTurns}\n";
        }

        // Add goals context
        if (isset($context['goals']) && is_array($context['goals'])) {
            $contextStr .= 'Goals: '.implode(', ', $context['goals'])."\n";
        }

        $contextStr .= "\nQuestion: {$prompt}";

        return $contextStr;
    }

    /**
     * Extract content from Bedrock response
     *
     * @param  array<string, mixed>  $response
     */
    protected function extractContent(array $response, string $model): string
    {
        // Claude models
        if (str_starts_with($model, 'claude')) {
            if (isset($response['content']) && is_array($response['content'])) {
                $firstContent = $response['content'][0] ?? null;
                if (\is_array($firstContent) && isset($firstContent['text']) && is_string($firstContent['text'])) {
                    return $firstContent['text'];
                }
            }
        }

        // Nova models
        if (str_starts_with($model, 'nova')) {
            if (isset($response['results']) && is_array($response['results'])) {
                $firstResult = $response['results'][0] ?? null;
                if (\is_array($firstResult) && isset($firstResult['outputText']) && is_string($firstResult['outputText'])) {
                    return $firstResult['outputText'];
                }
            }
        }

        // Default
        if (isset($response['completion'])) {
            $completion = $response['completion'];
            if (is_string($completion)) {
                return $completion;
            }
        }

        if (isset($response['text'])) {
            $text = $response['text'];
            if (is_string($text)) {
                return $text;
            }
        }

        throw new \RuntimeException('Unable to extract content from Bedrock response');
    }

    /**
     * Extract token count from Bedrock response
     *
     * @param  array<string, mixed>  $response
     */
    protected function extractTokenCount(array $response, string $model): int
    {
        // Claude models
        if (str_starts_with($model, 'claude')) {
            $inputTokens = 0;
            $outputTokens = 0;

            if (isset($response['usage']) && is_array($response['usage'])) {
                if (isset($response['usage']['input_tokens']) && is_int($response['usage']['input_tokens'])) {
                    $inputTokens = $response['usage']['input_tokens'];
                }
                if (isset($response['usage']['output_tokens']) && is_int($response['usage']['output_tokens'])) {
                    $outputTokens = $response['usage']['output_tokens'];
                }
            }

            return $inputTokens + $outputTokens;
        }

        // Nova models
        if (str_starts_with($model, 'nova')) {
            if (isset($response['results']) && is_array($response['results'])) {
                $firstResult = $response['results'][0] ?? null;
                if (\is_array($firstResult) && isset($firstResult['tokenCount']) && is_int($firstResult['tokenCount'])) {
                    return $firstResult['tokenCount'];
                }
            }

            return 0;
        }

        // Estimate if not provided
        $content = $this->extractContent($response, $model);

        return (int) (\strlen($content) / 4);
    }

    /**
     * Get Bedrock model ID
     */
    protected function getModelId(string $model): string
    {
        /** @var array<string, string> */
        $modelIds = [
            'claude-3-5-sonnet' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'claude-3-5-haiku' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
            'claude-opus-4-5' => 'anthropic.claude-opus-4-5-20250514-v1:0',
            'nova-2-lite' => 'amazon.nova-lite-v1:0',
            'nova-2-pro' => 'amazon.nova-pro-v1:0',
        ];

        return $modelIds[$model] ?? $model;
    }

    /**
     * Get model version
     */
    protected function getModelVersion(string $model): string
    {
        /** @var array<string, string> */
        $versions = [
            'claude-3-5-sonnet' => '20241022-v2',
            'claude-3-5-haiku' => '20241022-v1',
            'claude-opus-4-5' => '20250514-v1',
            'nova-2-lite' => 'v1',
            'nova-2-pro' => 'v1',
        ];

        return $versions[$model] ?? 'unknown';
    }

    /**
     * Check if Bedrock is available
     */
    public function isAvailable(): bool
    {
        try {
            // Check if AWS credentials are configured
            $credentials = Config::get('aws.credentials', []);
            $credentials = is_array($credentials) ? $credentials : [];
            $accessKey = isset($credentials['key']) && is_string($credentials['key']) ? $credentials['key'] : null;
            $secretKey = isset($credentials['secret']) && is_string($credentials['secret']) ? $credentials['secret'] : null;

            if (! $accessKey || ! $secretKey) {
                return false;
            }

            // Check cached availability
            $cacheKey = 'bedrock_availability';

            $cached = Cache::remember($cacheKey, 300, function () {
                // BedrockRuntimeClient does not expose listFoundationModels; treat configured credentials as available.
                return true;
            });

            return is_bool($cached) ? $cached : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if Bedrock is healthy
     */
    public function isHealthy(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Get model pricing
     *
     * @return array<string, mixed>
     */
    public function getModelPricing(): array
    {
        return $this->modelPricing;
    }

    /**
     * Get service status
     *
     * @return array{
     *     available: bool,
     *     healthy: bool,
     *     default_model: string,
     *     pricing: array<string, mixed>,
     *     timeout: int,
     *     temperature: float
     * }
     */
    public function getStatus(): array
    {
        return [
            'available' => $this->isAvailable(),
            'healthy' => $this->isHealthy(),
            'default_model' => $this->defaultModel,
            'pricing' => $this->modelPricing,
            'timeout' => $this->timeout,
            'temperature' => $this->temperature,
        ];
    }
}
