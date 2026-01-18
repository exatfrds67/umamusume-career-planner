<?php

namespace App\Services\AI;

use CloudStudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Ollama Local AI Service
 *
 * Manages local Ollama model interactions for privacy-focused AI processing.
 * Supports Llama 3.3, Mistral, and Qwen models.
 *
 * Requirements: 13.1, 56.1
 */
class OllamaService
{
    protected string $defaultModel;

    protected int $timeout;

    protected float $temperature;

    protected int $maxTokens;

    /** @var array<string, mixed> */
    protected array $availableModels;

    public function __construct()
    {
        $this->defaultModel = (string) Config::get('ai.ollama.default_model', 'llama3.3');
        $this->timeout = (int) Config::get('ai.ollama.timeout', 15);
        $this->temperature = (float) Config::get('ai.ollama.temperature', 0.3);
        $this->maxTokens = (int) Config::get('ai.ollama.max_tokens', 2048);

        $models = Config::get('ai.ollama.available_models', []);
        $this->availableModels = \is_array($models) ? $models : [];
    }

    /**
     * Generate AI response using Ollama
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     content: string,
     *     model: string,
     *     token_count: int,
     *     confidence: float,
     *     model_version: string
     * }
     */
    public function generate(
        string $prompt,
        array $context = [],
        ?string $model = null,
        ?int $timeout = null
    ): array {
        $model = $model ?? $this->defaultModel;
        $timeout = $timeout ?? $this->timeout;

        try {
            // Build full prompt with context
            $fullPrompt = $this->buildPromptWithContext($prompt, $context);

            // Generate response using Ollama
            $response = Ollama::agent('Umamusume Career Advisor')
                ->model($model)
                ->prompt($fullPrompt)
                ->options([
                    'temperature' => $this->temperature,
                    'top_p' => 0.9,
                    'max_tokens' => $this->maxTokens,
                ])
                ->ask();

            // Extract response content
            $content = $this->extractContent($response);
            $tokenCount = $this->estimateTokenCount($content);

            return [
                'content' => $content,
                'model' => $model,
                'token_count' => $tokenCount,
                'confidence' => 0.8, // Local models have good confidence
                'model_version' => $this->getModelVersion($model),
            ];
        } catch (\Exception $e) {
            Log::error('[Ollama] Generation failed', [
                'error' => $e->getMessage(),
                'model' => $model,
                'prompt_length' => \strlen($prompt),
            ]);

            throw new \RuntimeException("Ollama generation failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Stream AI response using Ollama
     *
     * @param  array<string, mixed>  $context
     * @return \Generator<string>
     */
    public function stream(
        string $prompt,
        array $context = [],
        ?string $model = null,
        ?callable $callback = null
    ): \Generator {
        $model = $model ?? $this->defaultModel;

        try {
            // Build full prompt with context
            $fullPrompt = $this->buildPromptWithContext($prompt, $context);

            // Stream response using Ollama
            return Ollama::agent('Umamusume Career Advisor')
                ->model($model)
                ->prompt($fullPrompt)
                ->options([
                    'temperature' => $this->temperature,
                    'top_p' => 0.9,
                    'max_tokens' => $this->maxTokens,
                ])
                ->stream($callback);
        } catch (\Exception $e) {
            Log::error('[Ollama] Streaming failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            throw new \RuntimeException("Ollama streaming failed: {$e->getMessage()}", 0, $e);
        }
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
     * Extract content from Ollama response
     */
    protected function extractContent(mixed $response): string
    {
        if (\is_string($response)) {
            return $response;
        }

        if (\is_array($response) && isset($response['response'])) {
            return (string) $response['response'];
        }

        if (\is_object($response) && method_exists($response, 'getContent')) {
            return (string) $response->getContent();
        }

        return (string) $response;
    }

    /**
     * Estimate token count
     */
    protected function estimateTokenCount(string $content): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) (\strlen($content) / 4);
    }

    /**
     * Get model version
     */
    protected function getModelVersion(string $model): string
    {
        return $this->availableModels[$model]['version'] ?? 'unknown';
    }

    /**
     * Check if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            // Check if Ollama service is running
            $cacheKey = 'ollama_availability';

            return Cache::remember($cacheKey, 60, function () {
                try {
                    // Try a simple health check
                    Ollama::agent('Health Check')
                        ->model($this->defaultModel)
                        ->prompt('ping')
                        ->ask();

                    return true;
                } catch (\Exception $e) {
                    Log::warning('[Ollama] Availability check failed', [
                        'error' => $e->getMessage(),
                    ]);

                    return false;
                }
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if Ollama is healthy
     */
    public function isHealthy(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Get available models
     *
     * @return array<string, mixed>
     */
    public function getAvailableModels(): array
    {
        return $this->availableModels;
    }

    /**
     * Get service status
     *
     * @return array{
     *     available: bool,
     *     healthy: bool,
     *     default_model: string,
     *     available_models: array<string, mixed>,
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
            'available_models' => $this->availableModels,
            'timeout' => $this->timeout,
            'temperature' => $this->temperature,
        ];
    }
}
