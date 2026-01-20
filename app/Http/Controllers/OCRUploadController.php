<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\OCRUploadRequest;
use App\Services\ImageProcessingService;
use App\Services\TesseractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * OCR Upload Controller
 *
 * Handles screenshot uploads and OCR processing requests.
 * Implements secure file upload, validation, and duplicate detection.
 *
 * Requirements: Task 5.1.2, Requirement 23.2
 */
class OCRUploadController extends Controller
{
    public function __construct(
        private readonly ImageProcessingService $imageProcessor,
        private readonly TesseractService $tesseractService
    ) {}

    /**
     * Upload and process screenshot
     */
    public function upload(OCRUploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('screenshot');
            $userId = $request->user()->id;
            $characterId = $request->input('character_id');
            $dataType = $request->input('data_type', 'character_stats');

            // Validate image using ImageProcessingService
            $validation = $this->imageProcessor->validateImage($file);
            if (! $validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image validation failed',
                    'error' => $validation['error'],
                ], 422);
            }

            // Process screenshot with OCR
            $result = $this->tesseractService->processScreenshot($file, $userId);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'OCR processing failed',
                    'error' => $result['error'],
                ], 500);
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Screenshot processed successfully',
                'data' => [
                    'extraction_id' => $result['extraction_id'],
                    'stats' => $result['stats'],
                    'confidence' => $result['confidence'],
                    'raw_text' => $result['raw_text'],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('[OCRUploadController] Upload failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during upload',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get upload status and configuration
     */
    public function status(): JsonResponse
    {
        try {
            $tesseractAvailable = $this->tesseractService->isAvailable();
            $imageProcessingAvailable = $this->imageProcessor->isAvailable();

            return response()->json([
                'success' => true,
                'data' => [
                    'ocr_available' => $tesseractAvailable,
                    'image_processing_available' => $imageProcessingAvailable,
                    'max_file_size' => config('services.image_processing.max_file_size', 10485760),
                    'max_file_size_mb' => config('services.image_processing.max_file_size', 10485760) / 1048576,
                    'allowed_formats' => config('services.image_processing.allowed_formats', ['jpg', 'jpeg', 'png', 'webp']),
                    'min_dimensions' => [
                        'width' => config('services.image_processing.min_width', 320),
                        'height' => config('services.image_processing.min_height', 240),
                    ],
                    'max_dimensions' => [
                        'width' => config('services.image_processing.max_width', 4096),
                        'height' => config('services.image_processing.max_height', 4096),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('[OCRUploadController] Status check failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Show upload page
     *
     * @return \Illuminate\View\View
     */
    public function showUploadPage()
    {
        $characters = \App\Models\Character::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('ocr.upload', compact('characters'));
    }

    /**
     * Show extraction results
     *
     * @return \Illuminate\View\View
     */
    public function showResults(int $extractionId)
    {
        $extraction = \App\Models\OCRExtraction::where('id', $extractionId)
            ->where('user_id', auth()->id())
            ->first();

        return view('ocr.results', compact('extraction'));
    }

    /**
     * Import extracted data
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(\Illuminate\Http\Request $request, int $extractionId)
    {
        try {
            $extraction = \App\Models\OCRExtraction::where('id', $extractionId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $action = $request->input('action', 'import');

            if ($action === 'save_draft') {
                // Update extraction with corrected data
                $extraction->update([
                    'parsed_data' => $request->except(['_token', 'action']),
                    'status' => 'draft',
                ]);

                return redirect()->route('ocr.upload')
                    ->with('success', 'Data saved as draft successfully');
            }

            // Import data using DataIntegrationService
            $integrationService = app(\App\Services\OCR\DataIntegrationService::class);

            $result = match ($extraction->data_type) {
                'character_stats' => $integrationService->importCharacterStats(
                    (int) $request->input('character_id'),
                    ['data' => $request->except(['_token', 'action']), 'confidence' => $extraction->confidence_score]
                ),
                'training_session' => $integrationService->importTrainingSession(
                    (int) $request->input('career_id'),
                    ['data' => $request->except(['_token', 'action']), 'confidence' => $extraction->confidence_score]
                ),
                'race_result' => $integrationService->importRaceResult(
                    (int) $request->input('career_id'),
                    ['data' => $request->except(['_token', 'action']), 'confidence' => $extraction->confidence_score]
                ),
                'skill_list' => $integrationService->importSkillList(
                    (int) $request->input('character_id'),
                    ['data' => $request->except(['_token', 'action']), 'confidence' => $extraction->confidence_score]
                ),
                default => ['success' => false, 'message' => 'Unknown screen type'],
            };

            if ($result['success']) {
                $extraction->update(['status' => 'imported']);

                return redirect()->route('ocr.upload')
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('[OCRUploadController] Import failed', [
                'error' => $e->getMessage(),
                'extraction_id' => $extractionId,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to import data: '.$e->getMessage())
                ->withInput();
        }
    }
}
