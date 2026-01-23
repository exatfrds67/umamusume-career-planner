<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Abstract Screen Parser
 *
 * Base class for screen-specific OCR parsers with common functionality.
 *
 * Requirements: Task 5.1.3, Requirement 23.3
 */
abstract class AbstractScreenParser implements ScreenParserInterface
{
    /**
     * Calculate confidence score based on extracted fields
     *
     * @param  array<string, mixed>  $data
     * @param  array<string>  $requiredFields
     */
    protected function calculateConfidence(array $data, array $requiredFields): float
    {
        $foundCount = 0;
        $totalFields = count($requiredFields);

        foreach ($requiredFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $foundCount = ($foundCount ?? 0) + 1;
            }
        }

        return $totalFields > 0 ? round($foundCount / $totalFields, 2) : 0.0;
    }

    /**
     * Extract numeric value from text using pattern
     */
    protected function extractNumeric(string $text, string $pattern): ?int
    {
        if (preg_match($pattern, $text, $matches)) {
            $value = (int) $matches[1];

            return $value;
        }

        return null;
    }

    /**
     * Extract text value from text using pattern
     */
    protected function extractText(string $text, string $pattern): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Validate stat value is within valid range
     */
    protected function validateStatValue(int $value): bool
    {
        return $value >= 0 && $value <= 1200;
    }

    /**
     * Validate percentage value
     */
    protected function validatePercentage(int $value): bool
    {
        return $value >= 0 && $value <= 100;
    }

    /**
     * Log parsing result
     *
     * @param  array<string, mixed>  $result
     */
    protected function logResult(array $result): void
    {
        Log::info("[{$this->getScreenType()}Parser] Parsing completed", [
            'success' => $result['success'],
            'confidence' => $result['confidence'],
            'fields_extracted' => count($result['data']),
            'errors' => $result['errors'],
        ]);
    }
}
