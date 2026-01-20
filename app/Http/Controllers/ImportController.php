<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ImportExecuteRequest;
use App\Http\Requests\ImportPreviewRequest;
use App\Services\DataImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Import Controller
 *
 * Handles data import operations including preview, execution, and templates.
 * Supports CSV, JSON, and text-based imports with intelligent parsing.
 *
 * Requirements: 23.1, 23.2
 */
class ImportController extends Controller
{
    public function __construct(
        private readonly DataImportService $importService
    ) {}

    /**
     * Show the import interface
     */
    public function index(): View
    {
        $importTypes = DataImportService::IMPORT_TYPES;
        $supportedFormats = DataImportService::SUPPORTED_FORMATS;

        return view('import.index', compact('importTypes', 'supportedFormats'));
    }

    /**
     * Preview import data with validation
     *
     * POST /api/import/preview
     */
    public function preview(ImportPreviewRequest $request): JsonResponse
    {
        try {
            $importType = $request->input('import_type');
            $format = $request->input('format', 'auto');

            // Handle file upload or text input
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $content = file_get_contents($file->getRealPath());
                $fileExtension = strtolower($file->getClientOriginalExtension());

                // Override format detection with file extension
                if (in_array($fileExtension, ['csv', 'json', 'txt'])) {
                    $format = $fileExtension;
                }
            } else {
                $content = $request->input('content', '');
            }

            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No content provided for import',
                    'errors' => ['Please provide file or text content to import'],
                ], 422);
            }

            // Parse the content
            $parseResult = match ($format) {
                'json' => $this->importService->parseJson($content, $importType),
                'csv' => $this->importService->parseCsv($content, $importType),
                default => $this->importService->parseText($content, $importType),
            };

            if (! $parseResult['success'] && empty($parseResult['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse import data',
                    'errors' => $parseResult['errors'],
                    'format_detected' => $parseResult['format'],
                ], 422);
            }

            // Generate preview
            $preview = $this->importService->generatePreview($parseResult['data'], $importType);

            return response()->json([
                'success' => true,
                'message' => 'Preview generated successfully',
                'data' => [
                    'preview' => $preview,
                    'format_detected' => $parseResult['format'],
                    'parse_errors' => $parseResult['errors'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ImportController] Preview failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during preview',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Execute import of validated data
     *
     * POST /api/import/execute
     */
    public function execute(ImportExecuteRequest $request): JsonResponse
    {
        try {
            $importType = $request->input('import_type');
            $data = $request->input('data');
            $userId = $request->user()->id;

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided for import',
                    'errors' => ['Please provide validated data to import'],
                ], 422);
            }

            // Execute the import
            $result = $this->importService->executeImport($data, $importType, $userId);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed',
                    'errors' => $result['errors'],
                    'imported' => $result['imported'],
                    'failed' => $result['failed'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['imported']} record(s)",
                'data' => [
                    'imported' => $result['imported'],
                    'failed' => $result['failed'],
                    'imported_ids' => $result['imported_ids'],
                    'errors' => $result['errors'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ImportController] Execute failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get import templates
     *
     * GET /api/import/templates
     */
    public function templates(Request $request): JsonResponse
    {
        try {
            $importType = $request->query('type');
            $templates = $this->importService->getTemplates();

            if ($importType && isset($templates[$importType])) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'type' => $importType,
                        'templates' => $templates[$importType],
                        'field_mapping' => $this->importService->getFieldMapping($importType),
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'import_types' => DataImportService::IMPORT_TYPES,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ImportController] Templates failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve templates',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get import history
     *
     * GET /api/import/history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $limit = $request->query('limit', 20);

            $history = $this->importService->getImportHistory($userId, (int) $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ImportController] History failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve import history',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }
}
