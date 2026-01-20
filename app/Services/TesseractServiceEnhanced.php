<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OCRExtraction;
use App\Services\OCR\ParserFactory;
use App\Services\OCR\ScreenTypeDetector;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Enhanced Tesseract OCR Service
 *
 * Parses screenshots with intelligent screen type detection and specialized parsers.
 * Supports: character_stats, training_session, race_result, skill_list screens.
 *
 * Requirements: Task 5.1.1, Task 5.1.3, Requirement 23.3, 23.4
 */
class TesseractServiceEnhanced
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

    public function __construct(ImageProcessingService $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
        $this->screenDetector = new ScreenTypeDetector;
        $this->parserFactory = new ParserFactory;
        $this->tesseractPath = config('services.tesseract.path', 'tesseract');
        $this->language = config('services.tesseract.language', 'jpn+eng');
        $this->psm = config('services.tesseract.psm', '6');
        $this->oem = config('services.tesseract.oem', '3');
    }

    /**
     * Process uploaded screenshot with intelligent parsing
     *
     * @return array{
     *     success: bool,
     *     extraction_id: int|null,
     *     screen_type: string|null,
     *     data: array<string, mixed>|null,
     *     raw_text: string|null,
     *     confidence: float,
     *     error: string|null
     * }
     */
    public function processScreenshot(UploadedFile $file, int $userId): array
    {
        try {
            // Validate image first
            $validation = $this->imageProcessor->validateImage($file);
            if (! $validation['valid']) {
                return $this->errorResponse($validation['error']);
            }

            // Store the uploaded file
            $path = $file->store('ocr-uploads', 'local');
            $fullPath = Storage::disk('local')->path($path);

            // Calculate image hash for deduplication
            $imageHash = $this->imageProcessor->calculateImageHash($fullPath);

            // Check for existing extraction
            $existing = $this->checkExistingExtraction($imageHash);
            if ($existing) {
                return $existing;
            }

            // Create extraction record
            $extraction = OCRExtraction::create([
                'user_id' => $userId,
                'image_path' => $path,
                'image_hash' => $imageHash,
                'data_type' => 'unknown',
                'status' => 'processing',
            ]);

            // Preprocess and perform OCR
            $rawText = $this->performOCRWithPreprocessing($fullPath);

            if (empty($rawText)) {
                $extraction->update([
                    'status' => 'failed',
                    'error_message' => 'OCR returned empty result',
                ]);

                return $this->errorResponse('OCR returned empty result', $extraction->id);
            }

            // Use intelligent processing
            $intelligentResult = $this->processWithIntelligentParsing($rawText);

            // Update extraction record
            $this->updateExtraction($extraction, $rawText, $intelligentResult);

            return [
                'success' => $intelligentResult['success'],
                'extraction_id' => $extraction->id,
                'screen_type' => $intelligentResult['screen_type'],
                'data' => $intelligentResult['data'],
                'raw_text' => $rawText,
                'confidence' => $intelligentResult['confidence'],
                'error' => ! empty($intelligentResult['errors']) ? implode(', ', $intelligentResult['errors']) : null,
            ];
        } catch (\Exception $e) {
            Log::error('[TesseractServiceEnhanced] OCR processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Process OCR text with intelligent screen detection and parsing
     *
     * @return array{
     *     success: bool,
     *     screen_type: string|null,
     *     screen_detection_confidence: float,
     *     parser_used: string|null,
     *     data: array<string, mixed>,
     *     confidence: float,
     *     errors: array<string>
     * }
     */
    public function processWithIntelligentParsing(string $text): array
    {
        // Detect screen type
        $detection = $this->screenDetector->detectScreenType($text);

        if (! $detection['detected_type']) {
            return $this->fallbackParsing($text, null, 0.0);
        }

        // Get appropriate parser
        $parser = $this->parserFactory->getParser($detection['detected_type']);

        if (! $parser) {
            return $this->fallbackParsing($text, $detection['detected_type'], $detection['confidence']);
        }

        // Parse with specialized parser
        $parseResult = $parser->parse($text);

        return [
            'success' => $parseResult['success'],
            'screen_type' => $detection['detected_type'],
            'screen_detection_confidence' => $detection['confidence'],
            'parser_used' => $parser->getScreenType(),
            'data' => $parseResult['data'],
            'confidence' => $parseResult['confidence'],
            'errors' => $parseResult['errors'],
        ];
    }

    /**
     * Perform OCR with preprocessing
     */
    protected function performOCRWithPreprocessing(string $fullPath): string
    {
        $preprocessResult = $this->imageProcessor->preprocessForOCR($fullPath);
        $ocrImagePath = $preprocessResult['success'] ? $preprocessResult['processed_path'] : $fullPath;

        $rawText = $this->performOCR($ocrImagePath);

        // Clean up processed image if it was created
        if ($preprocessResult['success'] && $preprocessResult['processed_path'] !== $fullPath) {
            @unlink($preprocessResult['processed_path']);
        }

        return $rawText;
    }

    /**
     * Perform OCR using Tesseract
     */
    protected function performOCR(string $imagePath): string
    {
        $command = sprintf(
            '%s %s stdout -l %s --psm %s --oem %s',
            escapeshellcmd($this->tesseractPath),
            escapeshellarg($imagePath),
            escapeshellarg($this->language),
            escapeshellarg($this->psm),
            escapeshellarg($this->oem)
        );

        $output = shell_exec($command);

        if ($output === null) {
            throw new \RuntimeException('Tesseract execution failed');
        }

        return trim($output);
    }

    /**
     * Check for existing extraction
     *
     * @return array<string, mixed>|null
     */
    protected function checkExistingExtraction(string $imageHash): ?array
    {
        $existing = OCRExtraction::where('image_hash', $imageHash)
            ->where('status', 'processed')
            ->first();

        if (! $existing) {
            return null;
        }

        return [
            'success' => true,
            'extraction_id' => $existing->id,
            'screen_type' => $existing->data_type,
            'data' => $existing->parsed_data,
            'raw_text' => $existing->extracted_text,
            'confidence' => (float) $existing->confidence_score,
            'error' => null,
        ];
    }

    /**
     * Update extraction record with results
     */
    protected function updateExtraction(OCRExtraction $extraction, string $rawText, array $result): void
    {
        $extraction->update([
            'extracted_text' => $rawText,
            'parsed_data' => $result['data'],
            'confidence_score' => $result['confidence'],
            'data_type' => $result['screen_type'] ?? 'unknown',
            'status' => 'processed',
            'processed_at' => now(),
            'processing_metadata' => [
                'language' => $this->language,
                'psm' => $this->psm,
                'oem' => $this->oem,
                'screen_type' => $result['screen_type'],
                'screen_detection_confidence' => $result['screen_detection_confidence'] ?? 0.0,
                'parser_used' => $result['parser_used'] ?? 'fallback',
                'validation_errors' => $result['errors'] ?? [],
            ],
        ]);
    }

    /**
     * Fallback parsing when screen type detection fails
     *
     * @return array<string, mixed>
     */
    protected function fallbackParsing(string $text, ?string $detectedType, float $detectionConfidence): array
    {
        // Basic stats extraction as fallback
        $stats = $this->extractBasicStats($text);
        $confidence = $this->calculateBasicConfidence($stats);

        $errors = [];
        if (! $detectedType) {
            $errors[] = 'Could not detect screen type, using fallback parser';
        } else {
            $errors[] = "No parser available for screen type: {$detectedType}";
        }

        return [
            'success' => $confidence >= 0.4,
            'screen_type' => $detectedType,
            'screen_detection_confidence' => $detectionConfidence,
            'parser_used' => 'fallback',
            'data' => ['stats' => $stats],
            'confidence' => $confidence,
            'errors' => $errors,
        ];
    }

    /**
     * Extract basic stats (fallback method)
     *
     * @return array<string, int|null>
     */
    protected function extractBasicStats(string $text): array
    {
        $patterns = [
            'speed' => '/(?:スピード|Speed|SPD)\s*[:：]?\s*(\d{2,4})/iu',
            'stamina' => '/(?:スタミナ|Stamina|STA)\s*[:：]?\s*(\d{2,4})/iu',
            'power' => '/(?:パワー|Power|POW)\s*[:：]?\s*(\d{2,4})/iu',
            'guts' => '/(?:根性|Guts|GUT)\s*[:：]?\s*(\d{2,4})/iu',
            'wit' => '/(?:賢さ|Wit|INT)\s*[:：]?\s*(\d{2,4})/iu',
        ];

        $stats = [];
        foreach ($patterns as $stat => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = (int) $matches[1];
                if ($value >= 0 && $value <= 1200) {
                    $stats[$stat] = $value;
                }
            } else {
                $stats[$stat] = null;
            }
        }

        return $stats;
    }

    /**
     * Calculate basic confidence score
     *
     * @param  array<string, int|null>  $stats
     */
    protected function calculateBasicConfidence(array $stats): float
    {
        $foundCount = count(array_filter($stats, fn ($v) => $v !== null));

        return round($foundCount / 5, 2);
    }

    /**
     * Create error response
     *
     * @return array<string, mixed>
     */
    protected function errorResponse(string $error, ?int $extractionId = null): array
    {
        return [
            'success' => false,
            'extraction_id' => $extractionId,
            'screen_type' => null,
            'data' => null,
            'raw_text' => null,
            'confidence' => 0.0,
            'error' => $error,
        ];
    }

    /**
     * Check if Tesseract is available
     */
    public function isAvailable(): bool
    {
        $output = shell_exec("{$this->tesseractPath} --version 2>&1");

        return $output !== null && str_contains($output, 'tesseract');
    }
}
