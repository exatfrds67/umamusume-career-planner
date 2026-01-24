<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OCRExtraction;
use App\Services\OCR\ParserFactory;
use App\Services\OCR\ScreenTypeDetector;
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
        /** @var string $tesseractPath */
        $tesseractPath = config('services.tesseract.path', 'tesseract');
        $this->tesseractPath = is_string($tesseractPath) ? $tesseractPath : 'tesseract';

        /** @var string $language */
        $language = config('services.tesseract.language', 'jpn+eng');
        $this->language = is_string($language) ? $language : 'jpn+eng';

        /** @var string $psm */
        $psm = config('services.tesseract.psm', '6');
        $this->psm = is_string($psm) ? $psm : '6';

        /** @var string $oem */
        $oem = config('services.tesseract.oem', '3');
        $this->oem = is_string($oem) ? $oem : '3';
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
    public function processScreenshot(\Illuminate\Http\UploadedFile $file, int $userId): array
    {
        try {
            // Validate image first
            $validation = $this->imageProcessor->validateImage($file);
            if (! $validation['valid']) {
                $error = $validation['error'] ?? 'Image validation failed';

                return $this->errorResponse($error);
            }

            // Store the uploaded file
            $path = $file->store('ocr-uploads', 'local');
            if ($path === false) {
                throw new \RuntimeException('Failed to store uploaded file');
            }

            $fullPath = Storage::disk('local')->path($path);
            if (! is_string($fullPath) || $fullPath === '') {
                throw new \RuntimeException('Failed to get file path');
            }

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
     * @param  string  $text  The OCR extracted text to process
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
            'success' => (bool) ($parseResult['success'] ?? false),
            'screen_type' => $detection['detected_type'],
            'screen_detection_confidence' => (float) ($detection['confidence'] ?? 0.0),
            'parser_used' => $parser->getScreenType(),
            'data' => is_array($parseResult['data'] ?? null) ? $parseResult['data'] : [],
            'confidence' => is_numeric($parseResult['confidence'] ?? null) ? (float) $parseResult['confidence'] : 0.0,
            'errors' => is_array($parseResult['errors'] ?? null) ? $parseResult['errors'] : [],
        ];
    }

    /**
     * Perform OCR with preprocessing
     */
    protected function performOCRWithPreprocessing(string $fullPath): string
    {
        $preprocessResult = $this->imageProcessor->preprocessForOCR($fullPath);
        $ocrImagePath = $fullPath;
        if (isset($preprocessResult['success']) && $preprocessResult['success'] === true) {
            $processedPath = $preprocessResult['processed_path'] ?? null;
            if (is_string($processedPath) && $processedPath !== '') {
                $ocrImagePath = $processedPath;
            }
        }

        $rawText = $this->performOCR($ocrImagePath);

        // Clean up processed image if it was created
        if (isset($preprocessResult['success']) && $preprocessResult['success'] === true) {
            $processedPath = $preprocessResult['processed_path'] ?? null;
            if (is_string($processedPath) && $processedPath !== '' && $processedPath !== $fullPath) {
                @unlink($processedPath);
            }
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

        if (! is_string($output)) {
            throw new \RuntimeException('Tesseract execution failed');
        }

        return trim($output);
    }

    /**
     * Check for existing extraction
     *
     * @return array{
     *     success: bool,
     *     extraction_id: int|null,
     *     screen_type: string|null,
     *     data: array<string, mixed>|null,
     *     raw_text: string|null,
     *     confidence: float,
     *     error: string|null
     * }|null
     */
    protected function checkExistingExtraction(string $imageHash): ?array
    {
        $existing = OCRExtraction::where('image_hash', $imageHash)
            ->where('status', '=', 'processed')
            ->first();

        if (! $existing) {
            return null;
        }

        $data = is_array($existing->parsed_data) ? $existing->parsed_data : null;

        return [
            'success' => true,
            'extraction_id' => $existing->id,
            'screen_type' => $existing->data_type,
            'data' => $data,
            'raw_text' => $existing->extracted_text,
            'confidence' => (is_numeric($existing->confidence_score) ? (float) $existing->confidence_score : 0.0),
            'error' => null,
        ];
    }

    /**
     * Update extraction record with results
     */
    /**
     * @param  array{
     *     data: array<string, mixed>,
     *     confidence: float,
     *     screen_type: string|null,
     *     screen_detection_confidence?: float,
     *     parser_used?: string|null,
     *     errors?: array<string>
     * }  $result
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
     * @param  string  $text  The OCR text to parse
     * @param  string|null  $detectedType  The detected screen type, if any
     * @param  float  $detectionConfidence  The detection confidence score
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
     * @param  string  $text  The OCR text to extract stats from
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
     * @param  string  $error  The error message
     * @param  int|null  $extractionId  The extraction ID if available
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

        return is_string($output) && str_contains($output, 'tesseract');
    }
}
