<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

/**
 * Screen Parser Interface
 *
 * Defines the contract for screen-specific OCR parsers.
 *
 * Requirements: Task 5.1.3, Requirement 23.3
 */
interface ScreenParserInterface
{
    /**
     * Parse OCR text and extract structured data
     *
     * @return array{
     *     success: bool,
     *     data: array<string, mixed>,
     *     confidence: float,
     *     errors: array<string>
     * }
     */
    public function parse(string $text): array;

    /**
     * Validate extracted data
     *
     * @param  array<string, mixed>  $data
     * @return array{
     *     valid: bool,
     *     errors: array<string>
     * }
     */
    public function validate(array $data): array;

    /**
     * Get the screen type this parser handles
     */
    public function getScreenType(): string;
}
