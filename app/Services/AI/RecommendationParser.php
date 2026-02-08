<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\ValueObjects\Recommendation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * AI Recommendation Parser
 *
 * Parses AI responses from NeuronAIService (Ollama + AWS Bedrock) into
 * structured Recommendation objects. Handles malformed responses and
 * validates AI output against expected schema.
 *
 * Requirements: 3.1, 3.2, 3.3
 */
class RecommendationParser
{
    /**
     * Parse AI response to Recommendation object
     *
     * @param  array<string, mixed>  $aiResponse  Response from HybridAIService
     * @param  string  $expectedType  Expected recommendation type
     *
     * @throws \InvalidArgumentException If response is malformed or invalid
     */
    public function parse(array $aiResponse, string $expectedType = RecommendationType::TRAINING_FACILITY->value): Recommendation
    {
        try {
            // Extract content from AI response
            $content = $this->extractContent($aiResponse);

            // Parse JSON from content
            $parsed = $this->parseJson($content);

            // Validate parsed data
            $this->validateRecommendation($parsed);

            // Build Recommendation object
            return $this->buildRecommendation($parsed, $expectedType);
        } catch (\Exception $e) {
            Log::error('[RecommendationParser] Failed to parse AI response', [
                'error' => $e->getMessage(),
                'response_preview' => is_string($jsonEncoded = json_encode($aiResponse)) ? substr($jsonEncoded, 0, 200) : '',
            ]);

            throw new \InvalidArgumentException(
                "Failed to parse AI recommendation: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Parse multiple recommendations from AI response
     *
     * @param  array<string, mixed>  $aiResponse
     * @return array<Recommendation>
     */
    public function parseMultiple(array $aiResponse, string $expectedType = RecommendationType::TRAINING_FACILITY->value): array
    {
        try {
            $content = $this->extractContent($aiResponse);
            $parsed = $this->parseJson($content);

            // Check if response contains multiple recommendations
            if (isset($parsed['recommendations']) && is_array($parsed['recommendations'])) {
                $recommendations = [];
                foreach ($parsed['recommendations'] as $recData) {
                    if (is_array($recData)) {
                        // Ensure array has string keys
                        /** @var array<string, mixed> $typedRecData */
                        $typedRecData = $recData;
                        $this->validateRecommendation($typedRecData);
                        $recommendations[] = $this->buildRecommendation($typedRecData, $expectedType);
                    }
                }

                return $recommendations;
            }

            // Single recommendation
            return [$this->parse($aiResponse, $expectedType)];
        } catch (\Exception $e) {
            Log::error('[RecommendationParser] Failed to parse multiple recommendations', [
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException(
                "Failed to parse AI recommendations: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Extract content string from AI response
     *
     * @param  array<string, mixed>  $aiResponse
     */
    protected function extractContent(array $aiResponse): string
    {
        if (! isset($aiResponse['content'])) {
            throw new \InvalidArgumentException('AI response missing content field');
        }

        $content = $aiResponse['content'];

        if (! is_string($content)) {
            throw new \InvalidArgumentException('AI response content must be string');
        }

        if (empty(trim($content))) {
            throw new \InvalidArgumentException('AI response content is empty');
        }

        return trim($content);
    }

    /**
     * Parse JSON from content string
     *
     * Handles various JSON formats and extracts JSON from markdown code blocks
     *
     * @return array<string, mixed>
     */
    protected function parseJson(string $content): array
    {
        // Try direct JSON parse first
        $decoded = json_decode($content, true);
        if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
            /** @var array<string, mixed> $decoded */
            return $decoded;
        }

        // Try extracting JSON from markdown code block
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                /** @var array<string, mixed> $decoded */
                return $decoded;
            }
        }

        // Try finding JSON object in text
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                /** @var array<string, mixed> $decoded */
                return $decoded;
            }
        }

        throw new \InvalidArgumentException('Unable to extract valid JSON from AI response');
    }

    /**
     * Validate recommendation data structure
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRecommendation(array $data): void
    {
        // Normalize priority to lowercase before validation
        if (isset($data['priority']) && is_string($data['priority'])) {
            $data['priority'] = strtolower(trim($data['priority']));
        }

        $validator = Validator::make($data, [
            'type' => 'sometimes|string',
            'priority' => 'required|string|in:critical,high,medium,low',
            'action' => 'required|string|min:3',
            'reasoning' => 'required|string|min:10',
            'expected_outcomes' => 'sometimes|array',
            'risks' => 'sometimes|array',
            'confidence_score' => 'sometimes|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            throw new \InvalidArgumentException(
                'Invalid recommendation structure: '.json_encode($errors)
            );
        }
    }

    /**
     * Build Recommendation value object from parsed data
     *
     * @param  array<string, mixed>  $data
     */
    protected function buildRecommendation(array $data, string $expectedType): Recommendation
    {
        // Determine recommendation type
        $type = $this->parseType($data, $expectedType);

        // Parse priority
        $priority = $this->parsePriority($data);

        // Extract action
        $action = is_string($data['action']) ? $data['action'] : '';

        // Extract reasoning
        $reasoning = is_string($data['reasoning']) ? $data['reasoning'] : '';

        // Extract expected outcomes
        /** @var array<string, mixed> $expectedOutcomes */
        $expectedOutcomes = isset($data['expected_outcomes']) && is_array($data['expected_outcomes'])
            ? $data['expected_outcomes']
            : [];

        // Extract risks - ensure array<string>
        $risks = [];
        if (isset($data['risks']) && is_array($data['risks'])) {
            foreach ($data['risks'] as $risk) {
                if (is_string($risk)) {
                    $risks[] = $risk;
                }
            }
        }

        // Extract confidence score
        $confidenceScore = null;
        if (isset($data['confidence_score'])) {
            if (is_numeric($data['confidence_score'])) {
                $confidenceScore = (float) $data['confidence_score'];
            }
        }

        return new Recommendation(
            type: $type,
            priority: $priority,
            action: $action,
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: $risks,
            confidenceScore: $confidenceScore
        );
    }

    /**
     * Parse recommendation type from data
     *
     * @param  array<string, mixed>  $data
     */
    protected function parseType(array $data, string $expectedType): RecommendationType
    {
        if (isset($data['type']) && is_string($data['type'])) {
            $typeValue = strtolower(trim($data['type']));

            // Try to match to enum
            foreach (RecommendationType::cases() as $case) {
                if ($case->value === $typeValue) {
                    return $case;
                }

                // Also try matching without underscores
                if (str_replace('_', '', $case->value) === str_replace('_', '', $typeValue)) {
                    return $case;
                }
            }
        }

        // Fall back to expected type
        foreach (RecommendationType::cases() as $case) {
            if ($case->value === $expectedType) {
                return $case;
            }
        }

        // Default to training facility
        return RecommendationType::TRAINING_FACILITY;
    }

    /**
     * Parse priority from data
     *
     * @param  array<string, mixed>  $data
     */
    protected function parsePriority(array $data): Priority
    {
        if (! isset($data['priority']) || ! is_string($data['priority'])) {
            return Priority::MEDIUM;
        }

        $priorityValue = strtolower(trim($data['priority']));

        foreach (Priority::cases() as $case) {
            if ($case->value === $priorityValue) {
                return $case;
            }
        }

        // Default to medium
        return Priority::MEDIUM;
    }

    /**
     * Attempt to parse malformed response with fallback strategies
     *
     * @param  array<string, mixed>  $aiResponse
     */
    public function parseWithFallback(array $aiResponse, string $expectedType = RecommendationType::TRAINING_FACILITY->value): ?Recommendation
    {
        try {
            return $this->parse($aiResponse, $expectedType);
        } catch (\Exception $e) {
            Log::warning('[RecommendationParser] Primary parsing failed, attempting fallback', [
                'error' => $e->getMessage(),
            ]);

            // Try extracting structured data from natural language
            try {
                return $this->parseNaturalLanguage($aiResponse, $expectedType);
            } catch (\Exception $fallbackError) {
                Log::error('[RecommendationParser] Fallback parsing also failed', [
                    'error' => $fallbackError->getMessage(),
                ]);

                return null;
            }
        }
    }

    /**
     * Parse natural language response into recommendation
     *
     * Fallback strategy for when AI doesn't return structured JSON
     *
     * @param  array<string, mixed>  $aiResponse
     */
    protected function parseNaturalLanguage(array $aiResponse, string $expectedType): Recommendation
    {
        $content = $this->extractContent($aiResponse);

        // Extract action (first sentence or line)
        $lines = explode("\n", $content);
        $action = trim($lines[0] ?? 'No action specified');

        // Use full content as reasoning
        $reasoning = $content;

        // Determine priority from keywords
        $priority = $this->inferPriority($content);

        // Extract expected outcomes if mentioned
        $expectedOutcomes = $this->extractOutcomes($content);

        // Extract risks if mentioned
        $risks = $this->extractRisks($content);

        return new Recommendation(
            type: $this->parseType([], $expectedType),
            priority: $priority,
            action: $action,
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: $risks,
            confidenceScore: 0.6 // Lower confidence for natural language parsing
        );
    }

    /**
     * Infer priority from content keywords
     */
    protected function inferPriority(string $content): Priority
    {
        $contentLower = strtolower($content);

        // Critical keywords (check first, most specific)
        $criticalKeywords = ['critical', 'urgent', 'immediately', 'must', 'emergency'];
        foreach ($criticalKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                // Make sure it's not negated
                if (! $this->isNegated($contentLower, $keyword)) {
                    return Priority::CRITICAL;
                }
            }
        }

        // High priority keywords
        $highKeywords = ['important', 'should', 'recommended', 'strongly'];
        foreach ($highKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                if (! $this->isNegated($contentLower, $keyword)) {
                    return Priority::HIGH;
                }
            }
        }

        // Low priority keywords
        $lowKeywords = ['optional', 'consider', 'might', 'could'];
        foreach ($lowKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                return Priority::LOW;
            }
        }

        return Priority::MEDIUM;
    }

    /**
     * Check if a keyword is negated in the content
     */
    protected function isNegated(string $content, string $keyword): bool
    {
        $negationWords = ['not', 'no', 'never', 'without', 'non'];

        // Find position of keyword
        $keywordPos = strpos($content, $keyword);
        if ($keywordPos === false) {
            return false;
        }

        // Check 20 characters before keyword for negation words
        $contextStart = max(0, $keywordPos - 20);
        $context = substr($content, $contextStart, $keywordPos - $contextStart);

        foreach ($negationWords as $negation) {
            if (str_contains($context, $negation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract expected outcomes from content
     *
     * @return array<string>
     */
    protected function extractOutcomes(string $content): array
    {
        $outcomes = [];

        // Look for outcome patterns
        if (preg_match_all('/(?:expect|result|gain|increase|improve)[^.]*[.]/i', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $outcomes[] = trim($match);
            }
        }

        return $outcomes;
    }

    /**
     * Extract risks from content
     *
     * @return array<string>
     */
    protected function extractRisks(string $content): array
    {
        $risks = [];

        // Look for risk patterns
        if (preg_match_all('/(?:risk|danger|warning|caution|may fail)[^.]*[.]/i', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $risks[] = trim($match);
            }
        }

        return $risks;
    }
}
