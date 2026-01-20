<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Services\OCR\Parsers\CharacterStatsParser;
use App\Services\OCR\Parsers\RaceResultParser;
use App\Services\OCR\Parsers\SkillListParser;
use App\Services\OCR\Parsers\TrainingSessionParser;
use Illuminate\Support\Facades\Log;

/**
 * Data Extraction Service
 *
 * Coordinates data extraction across all screen types with comprehensive validation.
 * Provides unified interface for OCR data extraction and processing.
 *
 * Requirements: Task 5.1.4, Requirement 23.4
 */
class DataExtractionService
{
    public function __construct(
        private readonly CharacterStatsParser $statsParser,
        private readonly TrainingSessionParser $trainingParser,
        private readonly RaceResultParser $raceParser,
        private readonly SkillListParser $skillParser,
        private readonly DataValidationService $validator
    ) {}

    /**
     * Extract data from OCR text based on screen type
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function extractData(string $screenType, string $ocrText, array $options = []): array
    {
        Log::info('[DataExtractionService] Starting data extraction', [
            'screen_type' => $screenType,
            'text_length' => \strlen($ocrText),
            'options' => $options,
        ]);

        try {
            // Get appropriate parser
            $parser = $this->getParser($screenType);

            // Parse OCR text
            $parseResult = $parser->parse($ocrText);

            // Validate extracted data
            $validationResult = $this->validator->validate($screenType, $parseResult['data']);

            // Merge results
            $result = [
                'success' => $parseResult['success'] && $validationResult['valid'],
                'screen_type' => $screenType,
                'data' => $parseResult['data'],
                'confidence' => $parseResult['confidence'],
                'validation' => [
                    'valid' => $validationResult['valid'],
                    'errors' => $validationResult['errors'],
                    'warnings' => $validationResult['warnings'],
                ],
                'parse_errors' => $parseResult['errors'],
                'extracted_at' => now()->toIso8601String(),
            ];

            Log::info('[DataExtractionService] Extraction completed', [
                'screen_type' => $screenType,
                'success' => $result['success'],
                'confidence' => $result['confidence'],
                'validation_errors' => \count($validationResult['errors']),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('[DataExtractionService] Extraction failed', [
                'screen_type' => $screenType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'screen_type' => $screenType,
                'data' => [],
                'confidence' => 0.0,
                'validation' => [
                    'valid' => false,
                    'errors' => ['Extraction failed: '.$e->getMessage()],
                    'warnings' => [],
                ],
                'parse_errors' => [$e->getMessage()],
                'extracted_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Extract character stats with comprehensive validation
     *
     * @return array<string, mixed>
     */
    public function extractCharacterStats(string $ocrText): array
    {
        return $this->extractData('character_stats', $ocrText);
    }

    /**
     * Extract training session data with validation
     *
     * @return array<string, mixed>
     */
    public function extractTrainingSession(string $ocrText): array
    {
        return $this->extractData('training_session', $ocrText);
    }

    /**
     * Extract race result data with validation
     *
     * @return array<string, mixed>
     */
    public function extractRaceResult(string $ocrText): array
    {
        return $this->extractData('race_result', $ocrText);
    }

    /**
     * Extract skill list data with validation
     *
     * @return array<string, mixed>
     */
    public function extractSkillList(string $ocrText): array
    {
        return $this->extractData('skill_list', $ocrText);
    }

    /**
     * Batch extract data from multiple screenshots
     *
     * @param  array<int, array{screen_type: string, ocr_text: string}>  $screenshots
     * @return array<int, array<string, mixed>>
     */
    public function batchExtract(array $screenshots): array
    {
        $results = [];

        foreach ($screenshots as $index => $screenshot) {
            $results[$index] = $this->extractData(
                $screenshot['screen_type'],
                $screenshot['ocr_text']
            );
        }

        return $results;
    }

    /**
     * Get appropriate parser for screen type
     */
    private function getParser(string $screenType): CharacterStatsParser|TrainingSessionParser|RaceResultParser|SkillListParser
    {
        return match ($screenType) {
            'character_stats' => $this->statsParser,
            'training_session' => $this->trainingParser,
            'race_result' => $this->raceParser,
            'skill_list' => $this->skillParser,
            default => throw new \InvalidArgumentException("Unknown screen type: {$screenType}"),
        };
    }
}
