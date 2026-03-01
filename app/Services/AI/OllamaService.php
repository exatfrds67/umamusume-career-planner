<?php

namespace App\Services\AI;

use Cloudstudio\Ollama\Facades\Ollama;
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
        $defaultModelValue = Config::get('ai.ollama.default_model', 'llama3.3');
        $this->defaultModel = is_string($defaultModelValue) ? $defaultModelValue : 'llama3.3';
        $timeoutValue = Config::get('ai.ollama.timeout', 15);
        $this->timeout = is_int($timeoutValue) ? $timeoutValue : 15;
        $temperatureValue = Config::get('ai.ollama.temperature', 0.3);
        $this->temperature = is_float($temperatureValue) || is_int($temperatureValue) ? (float) $temperatureValue : 0.3;
        $maxTokensValue = Config::get('ai.ollama.max_tokens', 2048);
        $this->maxTokens = is_int($maxTokensValue) ? $maxTokensValue : 2048;

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
    public function generate(string $prompt, array $context = [], ?string $model = null, ?int $timeout = null): array
    {
        $model = $model ?? $this->defaultModel;
        $timeout = $timeout ?? $this->timeout;

        try {
            // Enforce execution timeout for this request (applies to the blocking HTTP call to Ollama)
            if (! app()->runningUnitTests()) {
                set_time_limit($timeout);
            }

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
            $streamResult = Ollama::agent('Umamusume Career Advisor')
                ->model($model)
                ->prompt($fullPrompt)
                ->options([
                    'temperature' => $this->temperature,
                    'top_p' => 0.9,
                    'max_tokens' => $this->maxTokens,
                ])
                ->stream($callback !== null);

            // Yield chunks from stream
            if (is_iterable($streamResult)) {
                foreach ($streamResult as $chunk) {
                    yield is_string($chunk) ? $chunk : '';
                }
            }
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
        if (isset($context['character']) && is_array($context['character'])) {
            $char = $context['character'];
            $name = isset($char['name']) && is_string($char['name']) ? $char['name'] : 'Unknown';
            $scenario = isset($char['scenario_type']) && is_string($char['scenario_type']) ? $char['scenario_type'] : 'Unknown';
            $speed = isset($char['speed']) && (is_string($char['speed']) || is_int($char['speed'])) ? (string) $char['speed'] : '0';
            $stamina = isset($char['stamina']) && (is_string($char['stamina']) || is_int($char['stamina'])) ? (string) $char['stamina'] : '0';
            $power = isset($char['power']) && (is_string($char['power']) || is_int($char['power'])) ? (string) $char['power'] : '0';

            $contextStr .= "Character: {$name}\n";
            $contextStr .= "Scenario: {$scenario}\n";
            $contextStr .= "Stats: Speed {$speed}, Stamina {$stamina}, Power {$power}\n";
        }

        // Add career context
        if (isset($context['career']) && is_array($context['career'])) {
            $career = $context['career'];
            $stage = isset($career['stage']) && is_string($career['stage']) ? $career['stage'] : 'Unknown';
            $turn = isset($career['turn']) && (is_string($career['turn']) || is_int($career['turn'])) ? (string) $career['turn'] : '0';
            $totalTurns = isset($career['total_turns']) && (is_string($career['total_turns']) || is_int($career['total_turns'])) ? (string) $career['total_turns'] : '0';

            $contextStr .= "Career Stage: {$stage}\n";
            $contextStr .= "Turn: {$turn}/{$totalTurns}\n";
        }

        // Add goals context
        if (isset($context['goals']) && is_array($context['goals'])) {
            $goalsArr = [];
            foreach ($context['goals'] as $goal) {
                if (is_string($goal)) {
                    $goalsArr[] = $goal;
                }
            }
            $contextStr .= 'Goals: '.implode(', ', $goalsArr)."\n";
        }

        $contextStr .= "\nQuestion: {$prompt}";

        return $contextStr;
    }

    /**
     * Extract content from Ollama response.
     *
     * Detects error payloads returned by Ollama (e.g. model not found) and
     * throws so callers handle them as failures rather than showing raw JSON.
     *
     * @throws \RuntimeException when Ollama signals an error in the response
     */
    protected function extractContent(mixed $response): string
    {
        // Normalise to a content string first
        $content = '';

        if (\is_string($response)) {
            $content = $response;
        } elseif (\is_array($response) && isset($response['response']) && is_string($response['response'])) {
            $content = $response['response'];
        } elseif (\is_array($response) && isset($response['error']) && is_string($response['error'])) {
            // Ollama returned a top-level error object, e.g. {'error': 'model not found'}
            throw new \RuntimeException('Ollama error: '.$response['error']);
        } elseif (\is_object($response) && method_exists($response, 'getContent')) {
            $raw = $response->getContent();
            $content = is_string($raw) ? $raw : '';
        }

        // Also handle string payloads that are JSON error objects
        if ($content !== '' && str_starts_with(ltrim($content), '{')) {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded['error']) && is_string($decoded['error'])) {
                throw new \RuntimeException('Ollama error: '.$decoded['error']);
            }
        }

        return $content;
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
        if (isset($this->availableModels[$model]) && is_array($this->availableModels[$model])) {
            $modelConfig = $this->availableModels[$model];
            if (isset($modelConfig['version']) && is_string($modelConfig['version'])) {
                return $modelConfig['version'];
            }
        }

        return 'unknown';
    }

    /**
     * Check if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            // Check if Ollama service is running
            $cacheKey = 'ollama_availability';

            $result = Cache::remember($cacheKey, 60, function (): bool {
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

            return is_bool($result) ? $result : false;
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
