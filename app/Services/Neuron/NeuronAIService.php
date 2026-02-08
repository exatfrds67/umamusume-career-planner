<?php

declare(strict_types=1);

namespace App\Services\Neuron;

use App\Services\AI\HybridAIService;
use App\Services\AI\RecommendationParser;
use App\ValueObjects\Recommendation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Neuron AI Service
 *
 * Integrates with HybridAIService to provide AI-powered recommendations
 * for the Training Advisory System. Handles Ollama (local) and AWS Bedrock
 * (cloud) with intelligent fallback and timeout handling.
 *
 * This service acts as a bridge between the Training Advisory System and
 * the underlying AI infrastructure, providing a clean interface for
 * generating recommendations with proper error handling and fallback logic.
 *
 * Requirements: 3.10, 4.2
 */
class NeuronAIService
{
    /**
     * Create a new Neuron AI Service instance.
     *
     * @param  HybridAIService  $hybridAI  Hybrid AI service for Ollama + Bedrock
     * @param  RecommendationParser  $parser  Parser for AI responses
     */
    public function __construct(
        protected HybridAIService $hybridAI,
        protected RecommendationParser $parser
    ) {}

    /**
     * Generate AI-powered recommendation
     *
     * Routes request through HybridAIService which handles:
     * - Local Ollama inference (primary)
     * - AWS Bedrock fallback (if Ollama unavailable/slow)
     * - Timeout handling
     * - Error recovery
     *
     * @param  string  $prompt  Recommendation prompt
     * @param  array<string, mixed>  $context  Additional context for AI
     * @param  string  $expectedType  Expected recommendation type
     * @param  int|null  $timeout  Optional timeout in seconds (null = use defaults)
     * @return Recommendation Parsed recommendation object
     *
     * @throws \RuntimeException When AI service fails and no fallback available
     */
    public function generateRecommendation(
        string $prompt,
        array $context = [],
        string $expectedType = 'training_facility',
        ?int $timeout = null
    ): Recommendation {
        $startTime = microtime(true);

        try {
            // Add timeout to context if specified
            if ($timeout !== null) {
                $context['timeout'] = $timeout;
            }

            // Process request through HybridAIService
            $response = $this->hybridAI->processRequest($prompt, $context);

            // Parse AI response to Recommendation object
            $recommendation = $this->parser->parse($response, $expectedType);

            // Log successful generation
            $processingTime = microtime(true) - $startTime;
            Log::info('[NeuronAI] Recommendation generated successfully', [
                'provider' => $response['provider'] ?? 'unknown',
                'processing_time' => $processingTime,
                'confidence' => $recommendation->confidenceScore,
            ]);

            return $recommendation;
        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;

            Log::error('[NeuronAI] Recommendation generation failed', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime,
                'prompt_length' => strlen($prompt),
            ]);

            throw new \RuntimeException(
                "Failed to generate AI recommendation: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Generate multiple AI-powered recommendations
     *
     * Useful for generating ranked lists of training options or skill purchases.
     *
     * @param  string  $prompt  Recommendation prompt
     * @param  array<string, mixed>  $context  Additional context for AI
     * @param  string  $expectedType  Expected recommendation type
     * @param  int|null  $timeout  Optional timeout in seconds
     * @return array<Recommendation> Array of parsed recommendations
     *
     * @throws \RuntimeException When AI service fails
     */
    public function generateMultipleRecommendations(
        string $prompt,
        array $context = [],
        string $expectedType = 'training_facility',
        ?int $timeout = null
    ): array {
        $startTime = microtime(true);

        try {
            // Add timeout to context if specified
            if ($timeout !== null) {
                $context['timeout'] = $timeout;
            }

            // Process request through HybridAIService
            $response = $this->hybridAI->processRequest($prompt, $context);

            // Parse AI response to multiple Recommendation objects
            $recommendations = $this->parser->parseMultiple($response, $expectedType);

            // Log successful generation
            $processingTime = microtime(true) - $startTime;
            Log::info('[NeuronAI] Multiple recommendations generated successfully', [
                'provider' => $response['provider'] ?? 'unknown',
                'processing_time' => $processingTime,
                'count' => count($recommendations),
            ]);

            return $recommendations;
        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;

            Log::error('[NeuronAI] Multiple recommendations generation failed', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            throw new \RuntimeException(
                "Failed to generate AI recommendations: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Generate recommendation with fallback to parser's natural language parsing
     *
     * Attempts to parse even malformed AI responses using fallback strategies.
     * Returns null if all parsing attempts fail.
     *
     * @param  string  $prompt  Recommendation prompt
     * @param  array<string, mixed>  $context  Additional context for AI
     * @param  string  $expectedType  Expected recommendation type
     * @return Recommendation|null Parsed recommendation or null if failed
     */
    public function generateRecommendationWithFallback(
        string $prompt,
        array $context = [],
        string $expectedType = 'training_facility'
    ): ?Recommendation {
        try {
            return $this->generateRecommendation($prompt, $context, $expectedType);
        } catch (\Exception $e) {
            Log::warning('[NeuronAI] Primary generation failed, attempting fallback parsing', [
                'error' => $e->getMessage(),
            ]);

            try {
                // Try to get raw response and parse with fallback
                $response = $this->hybridAI->processRequest($prompt, $context);

                return $this->parser->parseWithFallback($response, $expectedType);
            } catch (\Exception $fallbackError) {
                Log::error('[NeuronAI] Fallback parsing also failed', [
                    'error' => $fallbackError->getMessage(),
                ]);

                return null;
            }
        }
    }

    /**
     * Check if AI service is available
     *
     * Checks if either Ollama or Bedrock is available for processing.
     *
     * @return bool True if at least one AI provider is available
     */
    public function isAvailable(): bool
    {
        try {
            // HybridAIService will check both Ollama and Bedrock availability
            // We can't directly check without making a request, so we'll
            // rely on configuration and assume availability
            $ollamaEnabled = Config::get('ai.ollama.enabled', true);
            $bedrockEnabled = Config::get('ai.bedrock.enabled', true);

            return $ollamaEnabled || $bedrockEnabled;
        } catch (\Exception $e) {
            Log::warning('[NeuronAI] Availability check failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get AI service status
     *
     * Returns detailed status information about available AI providers.
     *
     * @return array{
     *     available: bool,
     *     providers: array<string, bool>,
     *     default_timeout: int,
     *     hybrid_enabled: bool
     * }
     */
    public function getStatus(): array
    {
        $ollamaEnabled = Config::get('ai.ollama.enabled', true);
        $bedrockEnabled = Config::get('ai.bedrock.enabled', true);
        $hybridEnabled = Config::get('ai.hybrid.enabled', true);
        $defaultTimeout = Config::get('ai.ollama.timeout', 15);

        return [
            'available' => $this->isAvailable(),
            'providers' => [
                'ollama' => is_bool($ollamaEnabled) ? $ollamaEnabled : true,
                'bedrock' => is_bool($bedrockEnabled) ? $bedrockEnabled : true,
            ],
            'default_timeout' => is_int($defaultTimeout) ? $defaultTimeout : 15,
            'hybrid_enabled' => is_bool($hybridEnabled) ? $hybridEnabled : true,
        ];
    }

    /**
     * Test AI service with a simple prompt
     *
     * Useful for health checks and debugging.
     *
     * @return array{
     *     success: bool,
     *     provider: string,
     *     processing_time: float,
     *     error: string|null
     * }
     */
    public function testService(): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->hybridAI->processRequest(
                'Respond with "OK" if you can read this.',
                ['test' => true]
            );

            $processingTime = microtime(true) - $startTime;

            return [
                'success' => true,
                'provider' => $response['provider'] ?? 'unknown',
                'processing_time' => $processingTime,
                'error' => null,
            ];
        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;

            return [
                'success' => false,
                'provider' => 'none',
                'processing_time' => $processingTime,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recommended timeout for given complexity
     *
     * Returns appropriate timeout based on request complexity.
     *
     * @param  string  $complexity  Complexity level: 'simple', 'medium', 'complex'
     * @return int Timeout in seconds
     */
    public function getRecommendedTimeout(string $complexity = 'medium'): int
    {
        return match ($complexity) {
            'simple' => 10,
            'medium' => 15,
            'complex' => 30,
            default => 15,
        };
    }

    /**
     * Generate race strategy recommendation
     *
     * Analyzes character stats, race requirements, and conditions to generate
     * optimal race strategy including running style, stamina analysis, and
     * win probability assessment.
     *
     * @param  string  $prompt  Race strategy prompt
     * @param  array<string, mixed>  $context  Additional context for AI
     * @param  int|null  $timeout  Optional timeout in seconds
     * @return \App\Neuron\Responses\RaceStrategyResponse Parsed race strategy
     *
     * @throws \RuntimeException When AI service fails
     */
    public function generateRaceStrategy(
        string $prompt,
        array $context = [],
        ?int $timeout = null
    ): \App\Neuron\Responses\RaceStrategyResponse {
        $startTime = microtime(true);

        try {
            // Add timeout to context if specified
            if ($timeout !== null) {
                $context['timeout'] = $timeout;
            }

            // Add structured output schema for race strategy
            $context['structured_output'] = \App\Neuron\Responses\RaceStrategyResponse::class;

            // Process request through HybridAIService
            $response = $this->hybridAI->processRequest($prompt, $context);

            // Parse AI response to RaceStrategyResponse object
            $strategy = $this->parseRaceStrategyResponse($response);

            // Log successful generation
            $processingTime = microtime(true) - $startTime;
            Log::info('[NeuronAI] Race strategy generated successfully', [
                'provider' => $response['provider'] ?? 'unknown',
                'processing_time' => $processingTime,
                'running_style' => $strategy->recommendedRunningStyle,
            ]);

            return $strategy;
        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;

            Log::error('[NeuronAI] Race strategy generation failed', [
                'error' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            throw new \RuntimeException(
                "Failed to generate race strategy: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Parse AI response to RaceStrategyResponse object
     *
     * @param  array<string, mixed>  $response  AI response
     * @return \App\Neuron\Responses\RaceStrategyResponse Parsed strategy
     *
     * @throws \RuntimeException When parsing fails
     */
    protected function parseRaceStrategyResponse(array $response): \App\Neuron\Responses\RaceStrategyResponse
    {
        try {
            // Extract content from response
            $content = $response['content'] ?? $response['response'] ?? '';

            // Try to parse as JSON first
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content = $decoded;
                }
            }

            // If content is already a RaceStrategyResponse, return it
            if ($content instanceof \App\Neuron\Responses\RaceStrategyResponse) {
                return $content;
            }

            // Ensure content is an array for safe access
            assert(is_array($content));

            // Extract fields from parsed content with safe casting
            $runningStyleRaw = $content['recommended_running_style']
                ?? $content['recommendedRunningStyle']
                ?? $content['running_style']
                ?? 'escape';
            $runningStyle = is_string($runningStyleRaw) ? $runningStyleRaw : 'escape';

            $skillsRaw = $content['recommended_skills']
                ?? $content['recommendedSkills']
                ?? $content['skills']
                ?? [];
            $skills = is_array($skillsRaw) ? array_map(function ($skill): string {
                if (is_string($skill)) {
                    return $skill;
                }
                if (is_scalar($skill)) {
                    return (string) $skill;
                }

                return '';
            }, $skillsRaw) : [];

            $adviceRaw = $content['race_preparation_advice']
                ?? $content['racePreparationAdvice']
                ?? $content['preparation_advice']
                ?? $content['advice']
                ?? 'Prepare for the race by ensuring stats meet requirements.';
            $advice = is_string($adviceRaw) ? $adviceRaw : 'Prepare for the race by ensuring stats meet requirements.';

            $performance = $content['expected_performance']
                ?? $content['expectedPerformance']
                ?? $content['performance']
                ?? null;

            $risksRaw = $content['risk_factors']
                ?? $content['riskFactors']
                ?? $content['risks']
                ?? [];
            $risks = is_array($risksRaw) ? array_map(function ($risk): string {
                if (is_string($risk)) {
                    return $risk;
                }
                if (is_scalar($risk)) {
                    return (string) $risk;
                }

                return '';
            }, $risksRaw) : [];

            // Create and return RaceStrategyResponse
            return new \App\Neuron\Responses\RaceStrategyResponse(
                recommendedRunningStyle: $runningStyle,
                recommendedSkills: $skills,
                racePreparationAdvice: $advice,
                expectedPerformance: $performance,
                riskFactors: $risks
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to parse race strategy response: {$e->getMessage()}",
                0,
                $e
            );
        }
    }
}
