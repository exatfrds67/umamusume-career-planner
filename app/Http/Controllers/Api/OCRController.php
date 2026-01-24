<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TesseractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OCR API Controller
 *
 * Handles screenshot upload and stat extraction via OCR.
 *
 * Requirements: Task 5.1
 */
class OCRController extends Controller
{
    public function __construct(
        protected TesseractService $tesseractService
    ) {}

    /**
     * Upload and process screenshot for OCR
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'screenshot' => 'required|image|mimes:png,jpg,jpeg|max:10240', // 10MB max
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $file = $request->file('screenshot');
        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded',
            ], 400);
        }

        $result = $this->tesseractService->processScreenshot($file, $user->id ?? throw new \Exception('User required'));

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'OCR processing failed',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'extraction_id' => $result['extraction_id'],
                'stats' => $result['stats'],
                'confidence' => $result['confidence'],
                'raw_text' => $result['raw_text'],
            ],
        ]);
    }

    /**
     * Get OCR service status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'available' => $this->tesseractService->isAvailable(),
            'supported_formats' => ['png', 'jpg', 'jpeg'],
            'max_file_size' => '10MB',
        ]);
    }
}
