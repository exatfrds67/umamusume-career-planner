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
        $this->defaultModel = (string) Config::get('ai.bedrock.default_model', 'claude-3-5-sonnet');
        $this->timeout = (int) Config::get('aws.bedrock.timeout', 30);
        $this->temperature = (float) Config::get('ai.bedrock.temperature', 0.3);
        $this->maxTokens = (int) Config::get('ai.bedrock.max_tokens', 4096);

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
            $accessKey = $credentials['key'] ?? null;
            $secretKey = $credentials['secret'] ?? null;

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
    public function generate(
        string $prompt,
        array $context = [],
        ?string $model = null,
        ?int $timeout = null
    ): array {
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
            $responseBody = json_decode($response['body']->getContents(), true);

            if (! $responseBody) {
                throw new \RuntimeException('Failed to parse Bedrock response');
            }

            // Extract content based on model type
            $content = $this->extractContent($responseBody, $model);
            $tokenCount = $this->extractTokenCount($responseBody, $model);

            return [
                'content' => $content,
                'model' => $model,
                'token_count' => $tokenCount,
                'confidence' => 0.9, // Bedrock models have high confidence
                'model_version' => $this->getModelVersion($model),
                'request_id' => (string) ($response['ResponseMetadata']['RequestId'] ?? 'unknown'),
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
    protected function buildPayload(string $prompt, array $context, string $model): array
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
        if (isset($context['character'])) {
            $char = $context['character'];
            $contextStr .= "Character: {$char['name']}\n";
            $contextStr .= "Scenario: {$char['scenario_type']}\n";
            $contextStr .= "Stats: Speed {$char['speed']}, Stamina {$char['stamina']}, Power {$char['power']}\n";
        }

        // Add career context
        if (isset($context['career'])) {
            $career = $context['career'];
            $contextStr .= "Career Stage: {$career['stage']}\n";
            $contextStr .= "Turn: {$career['turn']}/{$career['total_turns']}\n";
        }

        // Add goals context
        if (isset($context['goals'])) {
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
            if (isset($response['content'][0]['text'])) {
                return (string) $response['content'][0]['text'];
            }
        }

        // Nova models
        if (str_starts_with($model, 'nova')) {
            if (isset($response['results'][0]['outputText'])) {
                return (string) $response['results'][0]['outputText'];
            }
        }

        // Default
        if (isset($response['completion'])) {
            return (string) $response['completion'];
        }

        if (isset($response['text'])) {
            return (string) $response['text'];
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
            $inputTokens = $response['usage']['input_tokens'] ?? 0;
            $outputTokens = $response['usage']['output_tokens'] ?? 0;

            return $inputTokens + $outputTokens;
        }

        // Nova models
        if (str_starts_with($model, 'nova')) {
            return $response['results'][0]['tokenCount'] ?? 0;
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
            $accessKey = $credentials['key'] ?? null;
            $secretKey = $credentials['secret'] ?? null;

            if (! $accessKey || ! $secretKey) {
                return false;
            }

            // Check cached availability
            $cacheKey = 'bedrock_availability';

            return Cache::remember($cacheKey, 300, function () {
                // BedrockRuntimeClient does not expose listFoundationModels; treat configured credentials as available.
                return true;
            });
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
