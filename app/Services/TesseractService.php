<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OCRExtraction;
use App\Services\OCR\ParserFactory;
use App\Services\OCR\ScreenTypeDetector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Tesseract OCR Service
 *
 * Parses screenshots to extract character stats using Tesseract OCR.
 * Implements regex extraction for Speed, Stamina, Power, Guts, Wit.
 * Integrates with ImageProcessingService for preprocessing.
 * Enhanced with intelligent screen type detection and specialized parsers.
 *
 * Requirements: Task 5.1.1, Task 5.1.3
 */
class TesseractService
{
    /**
     * Tesseract binary path
     */
    protected string $tesseractPath;

    /**
     * OCR language
     */
    protected string $language;

    /**
     * Page segmentation mode
     */
    protected string $psm;

    /**
     * OCR Engine mode
     */
    protected string $oem;

    /**
     * Image processing service
     */
    protected ImageProcessingService $imageProcessor;

    /**
     * Screen type detector
     */
    protected ScreenTypeDetector $screenDetector;

    /**
     * Parser factory
     */
    protected ParserFactory $parserFactory;

    /**
     * Stat regex patterns
     */
    protected const STAT_PATTERNS = [
        'speed' => '/(?:スピード|Speed|SPD)\s*[:：]?\s*(\d{2,4})/iu',
        'stamina' => '/(?:スタミナ|Stamina|STA)\s*[:：]?\s*(\d{2,4})/iu',
        'power' => '/(?:パワー|Power|POW)\s*[:：]?\s*(\d{2,4})/iu',
        'guts' => '/(?:根性|Guts|GUT)\s*[:：]?\s*(\d{2,4})/iu',
        'wit' => '/(?:賢さ|Wit|INT)\s*[:：]?\s*(\d{2,4})/iu',
    ];

    /**
     * Additional extraction patterns
     */
    protected const ADDITIONAL_PATTERNS = [
        'turn' => '/(?:ターン|Turn)\s*[:：]?\s*(\d{1,2})(?:\/(\d{1,2}))?/iu',
        'energy' => '/(?:体力|Energy|HP)\s*[:：]?\s*(\d{1,3})/iu',
        'mood' => '/(?:やる気|Mood)\s*[:：]?\s*([\p{Han}\p{Hiragana}\p{Katakana}]+|[a-zA-Z]+)/iu',
    ];

    public function __construct(ImageProcessingService $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
        $this->screenDetector = new ScreenTypeDetector;
        $this->parserFactory = new ParserFactory;

        /** @var string $tesseractPath */
        $tesseractPath = config('services.tesseract.path', 'tesseract');
        $this->tesseractPath = \is_string($tesseractPath) ? $tesseractPath : 'tesseract';

        /** @var string $language */
        $language = config('services.tesseract.language', 'jpn+eng');
        $this->language = \is_string($language) ? $language : 'jpn+eng';

        /** @var string $psm */
        $psm = config('services.tesseract.psm', '6');
        $this->psm = \is_string($psm) ? $psm : '6';

        /** @var string $oem */
        $oem = config('services.tesseract.oem', '3');
        $this->oem = \is_string($oem) ? $oem : '3';
    }

    /**
     * Process uploaded screenshot and extract stats
     *
     * @return array{
     *     success: bool,
     *     extraction_id: int|null,
     *     stats: array<string, int|null>|null,
     *     raw_text: string|null,
     *     confidence: float,
     *     error: string|null
     * }
     */
    public function processScreenshot(\Illuminate\Http\UploadedFile $file, int $userId): array
    {
        try {
            // Validate image first
            $validation = $this->imageProcessor->validateImage($file);
            if (! $validation['valid']) {
                return [
                    'success' => false,
                    'extraction_id' => null,
                    'stats' => null,
                    'raw_text' => null,
                    'confidence' => 0.0,
                    'error' => $validation['error'],
                ];
            }

            // Store the uploaded file
            $path = $file->store('ocr-uploads', 'local');
            if ($path === false) {
                throw new \RuntimeException('Failed to store uploaded file');
            }
            $fullPath = Storage::disk('local')->path($path);
            if ($fullPath === '') {
                throw new \RuntimeException('Failed to get file path');
            }

            // Calculate image hash for deduplication
            $imageHash = $this->imageProcessor->calculateImageHash($fullPath);

            // Check for existing extraction with same hash
            $existing = OCRExtraction::where('image_hash', $imageHash)
                ->where('status', '=', 'processed')
                ->first();

            if ($existing) {
                return [
                    'success' => true,
                    'extraction_id' => $existing->id,
                    'stats' => $existing->parsed_data['stats'] ?? null,
                    'raw_text' => $existing->extracted_text,
                    'confidence' => (is_numeric($existing->confidence_score) ? (float) $existing->confidence_score : 0.0),
                    'error' => null,
                ];
            }

            // Create extraction record
            $extraction = OCRExtraction::create([
                'user_id' => $userId,
                'image_path' => $path,
                'image_hash' => $imageHash,
                'data_type' => 'character_stats',
                'status' => 'processing',
            ]);

            // Preprocess image for better OCR results
            $preprocessResult = $this->imageProcessor->preprocessForOCR($fullPath);
            $ocrImagePath = $preprocessResult['success'] && $preprocessResult['processed_path'] !== null
                ? $preprocessResult['processed_path']
                : $fullPath;

            // Perform OCR
            $rawText = $this->performOCR($ocrImagePath);

            // Clean up processed image if it was created
            if ($preprocessResult['success'] && $preprocessResult['processed_path'] !== null && $preprocessResult['processed_path'] !== $fullPath) {
                @unlink($preprocessResult['processed_path']);
            }

            if (empty($rawText)) {
                $extraction->update([
                    'status' => 'failed',
                    'error_message' => 'OCR returned empty result',
                ]);

                return [
                    'success' => false,
                    'extraction_id' => $extraction->id,
                    'stats' => null,
                    'raw_text' => null,
                    'confidence' => 0.0,
                    'error' => 'OCR returned empty result',
                ];
            }

            // Parse stats from OCR text
            $stats = $this->extractStats($rawText);
            $additionalData = $this->extractAdditionalData($rawText);

            // Calculate confidence based on stats found
            $confidence = $this->calculateConfidence($stats);

            // Update extraction record
            $extraction->update([
                'extracted_text' => $rawText,
                'parsed_data' => [
                    'stats' => $stats,
                    'additional' => $additionalData,
                ],
                'confidence_score' => $confidence,
                'status' => 'processed',
                'processed_at' => now(),
                'processing_metadata' => [
                    'language' => $this->language,
                    'psm' => $this->psm,
                    'oem' => $this->oem,
                    'preprocessed' => $preprocessResult['success'],
                    'patterns_matched' => count(array_filter($stats)),
                ],
            ]);

            return [
                'success' => true,
                'extraction_id' => $extraction->id,
                'stats' => $stats,
                'raw_text' => $rawText,
                'confidence' => $confidence,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('[TesseractService] OCR processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'extraction_id' => null,
                'stats' => null,
                'raw_text' => null,
                'confidence' => 0.0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Perform OCR using Tesseract
     *
     * @param  string  $imagePath  Full path to image
     * @return string Extracted text
     */
    protected function performOCR(string $imagePath): string
    {
        // Build Tesseract command with PSM and OEM options
        $command = \sprintf(
            '%s %s stdout -l %s --psm %s --oem %s',
            escapeshellcmd($this->tesseractPath),
            escapeshellarg($imagePath),
            escapeshellarg($this->language),
            escapeshellarg($this->psm),
            escapeshellarg($this->oem)
        );

        // Execute OCR
        $output = shell_exec($command);

        if (! \is_string($output)) {
            throw new \RuntimeException('Tesseract execution failed');
        }

        return trim($output);
    }

    /**
     * Extract stats from OCR text using regex patterns
     *
     * @return array<string, int|null>
     */
    protected function extractStats(string $text): array
    {
        $stats = [];

        foreach (self::STAT_PATTERNS as $stat => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = (int) $matches[1];
                // Validate reasonable stat range (50-1200)
                if ($value >= 50 && $value <= 1200) {
                    $stats[$stat] = $value;
                } else {
                    $stats[$stat] = null;
                }
            } else {
                $stats[$stat] = null;
            }
        }

        return $stats;
    }

    /**
     * Extract additional data from OCR text
     *
     * @param  string  $text  The OCR text to extract data from
     * @return array<string, mixed>
     */
    protected function extractAdditionalData(string $text): array
    {
        $data = [];

        // Extract turn
        if (preg_match(self::ADDITIONAL_PATTERNS['turn'], $text, $matches)) {
            $data['current_turn'] = (int) $matches[1];
            if (isset($matches[2])) {
                $data['total_turns'] = (int) $matches[2];
            }
        }

        // Extract energy
        if (preg_match(self::ADDITIONAL_PATTERNS['energy'], $text, $matches)) {
            $energy = (int) $matches[1];
            if ($energy >= 0 && $energy <= 100) {
                $data['energy_level'] = $energy;
            }
        }

        // Extract mood
        if (preg_match(self::ADDITIONAL_PATTERNS['mood'], $text, $matches)) {
            $data['mood_status'] = $this->normalizeMood($matches[1]);
        }

        return $data;
    }

    /**
     * Normalize mood status to standard values
     */
    protected function normalizeMood(string $mood): string
    {
        $moodMap = [
            '絶好調' => 'great',
            '好調' => 'good',
            '普通' => 'normal',
            '不調' => 'bad',
            '絶不調' => 'awful',
        ];

        $lower = strtolower(trim($mood));

        // Check Japanese mood
        if (isset($moodMap[$mood])) {
            return $moodMap[$mood];
        }

        // Check English mood
        $englishMoods = ['great', 'good', 'normal', 'bad', 'awful'];
        if (\in_array($lower, $englishMoods, true)) {
            return $lower;
        }

        return 'normal';
    }

    /**
     * Calculate confidence score based on extracted stats
     *
     * @param  array<string, int|null>  $stats
     * @return float Score 0.0 to 1.0
     */
    protected function calculateConfidence(array $stats): float
    {
        $foundCount = \count(array_filter($stats, static fn ($value): bool => $value !== null));
        $totalStats = 5;

        return round($foundCount / $totalStats, 2);
    }

    /**
     * Check if Tesseract is available
     */
    public function isAvailable(): bool
    {
        $output = shell_exec("{$this->tesseractPath} --version 2>&1");

        return \is_string($output) && str_contains($output, 'tesseract');
    }
}
