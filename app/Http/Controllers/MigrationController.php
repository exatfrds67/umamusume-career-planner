<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MigrationBatchRequest;
use App\Http\Requests\MigrationConvertRequest;
use App\Http\Requests\MigrationResolveConflictRequest;
use App\Http\Requests\MigrationValidateRequest;
use App\Services\DataImportService;
use App\Services\DataMigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Migration Controller
 *
 * Handles data migration operations including legacy format conversion,
 * batch import processing, data validation, and conflict resolution.
 *
 * Requirements: 23.3, 23.4
 */
class MigrationController extends Controller
{
    public function __construct(
        private readonly DataMigrationService $migrationService
    ) {}

    /**
     * Show the migration interface
     */
    public function index(): View
    {
        $legacyFormats = DataMigrationService::LEGACY_FORMATS;
        $importTypes = DataImportService::IMPORT_TYPES;
        $conflictStrategies = [
            DataMigrationService::CONFLICT_STRATEGY_SKIP => 'Skip duplicates',
            DataMigrationService::CONFLICT_STRATEGY_OVERWRITE => 'Overwrite existing',
            DataMigrationService::CONFLICT_STRATEGY_MERGE => 'Merge data',
            DataMigrationService::CONFLICT_STRATEGY_RENAME => 'Rename and import',
        ];

        return view('migration.index', compact(
            'legacyFormats',
            'importTypes',
            'conflictStrategies'
        ));
    }

    /**
     * Convert legacy data format to current format
     *
     * POST /api/migration/convert
     */
    public function convert(MigrationConvertRequest $request): JsonResponse
    {
        try {
            $content = $request->input('content');
            $sourceFormat = $request->input('source_format', 'auto');
            $targetType = $request->input('target_type');

            // Auto-detect format if not specified
            if ($sourceFormat === 'auto') {
                $detection = $this->migrationService->detectLegacyFormat(is_string($content) ? $content : '');
                $sourceFormat = $detection['format'];

                if ($detection['confidence'] < 0.5) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not reliably detect data format',
                        'detection' => $detection,
                        'suggestion' => 'Please specify the source format manually',
                    ], 422);
                }
            }

            // Convert the data
            $contentStr = is_string($content) ? $content : '';
            $sourceFormatStr = is_string($sourceFormat) ? $sourceFormat : 'auto';
            $targetTypeStr = is_string($targetType) ? $targetType : 'character';
            $result = $this->migrationService->convertLegacyFormat(
                $contentStr,
                $sourceFormatStr,
                $targetTypeStr
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversion failed',
                    'errors' => $result['errors'],
                    'warnings' => $result['warnings'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data converted successfully',
                'data' => [
                    'converted_data' => $result['data'],
                    'source_format' => $sourceFormat,
                    'target_type' => $targetType,
                    'statistics' => $result['statistics'] ?? [],
                    'warnings' => $result['warnings'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Convert failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during conversion',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Start a batch import process
     *
     * POST /api/migration/batch
     */
    public function startBatch(MigrationBatchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $data = $request->input('data');
            $importType = $request->input('import_type');
            $userId = $user->id ?? throw new \Exception('User required');

            $conflictStrategyInput = $request->input('conflict_strategy', DataMigrationService::CONFLICT_STRATEGY_SKIP);
            $batchSizeInput = $request->input('batch_size', 50);
            $options = [
                'conflict_strategy' => is_string($conflictStrategyInput) ? $conflictStrategyInput : DataMigrationService::CONFLICT_STRATEGY_SKIP,
                'batch_size' => is_numeric($batchSizeInput) ? (int) $batchSizeInput : 50,
            ];

            /** @var array<int, array<string, mixed>> $importData */
            $importData = is_array($data) ? array_values($data) : [];
            $result = $this->migrationService->startBatchImport(
                $importData,
                is_string($importType) ? $importType : 'character',
                $userId,
                $options
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch import started',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Start batch failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start batch import',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get batch import status
     *
     * GET /api/migration/batch/{id}/status
     */
    public function getBatchStatus(Request $request, string $id): JsonResponse
    {
        try {
            $status = $this->migrationService->getBatchStatus($id);

            if (! $status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Get batch status failed', [
                'batch_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get batch status',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Process next chunk of batch import
     *
     * POST /api/migration/batch/{id}/process
     */
    public function processBatch(Request $request, string $id): JsonResponse
    {
        try {
            $result = $this->migrationService->processBatch($id);

            if (! $result['success'] && $result['status'] === 'not_found') {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found',
                ], 404);
            }

            return response()->json([
                'success' => $result['success'],
                'data' => [
                    'status' => $result['status'],
                    'progress' => $result['progress'],
                ],
                'error' => $result['error'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Process batch failed', [
                'batch_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process batch',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Cancel a batch import
     *
     * POST /api/migration/batch/{id}/cancel
     */
    public function cancelBatch(Request $request, string $id): JsonResponse
    {
        try {
            $cancelled = $this->migrationService->cancelBatch($id);

            if (! $cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not cancel batch. It may not exist or is already completed.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch import cancelled',
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Cancel batch failed', [
                'batch_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel batch',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Validate data before import
     *
     * POST /api/migration/validate
     */
    public function validateData(MigrationValidateRequest $request): JsonResponse
    {
        try {
            $data = $request->input('data');
            $importType = $request->input('import_type');

            /** @var array<int, array<string, mixed>> $validateData */
            $validateData = is_array($data) ? array_values($data) : [];
            $result = $this->migrationService->validateData(
                $validateData,
                is_string($importType) ? $importType : 'character'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => $result['valid'],
                    'summary' => $result['summary'],
                    'valid_records' => $result['records']['valid'],
                    'invalid_records' => $result['records']['invalid'],
                    'warnings' => $result['warnings'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Validate failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Resolve duplicate conflicts
     *
     * POST /api/migration/resolve-conflicts
     */
    public function resolveConflicts(MigrationResolveConflictRequest $request): JsonResponse
    {
        try {
            $batchId = $request->input('batch_id');
            $conflictIndex = $request->input('conflict_index');
            $resolution = $request->input('resolution');

            $batchIdStr = is_string($batchId) ? $batchId : '';
            $conflictIndexInt = is_numeric($conflictIndex) ? (int) $conflictIndex : 0;
            $resolutionStr = is_string($resolution) ? $resolution : '';

            $result = $this->migrationService->resolveConflictManually(
                $batchIdStr,
                $conflictIndexInt,
                $resolutionStr
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Resolve conflicts failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflict',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get pending conflicts for a batch
     *
     * GET /api/migration/batch/{id}/conflicts
     */
    public function getConflicts(Request $request, string $id): JsonResponse
    {
        try {
            $conflicts = $this->migrationService->getPendingConflicts($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_id' => $id,
                    'conflicts' => $conflicts,
                    'count' => count($conflicts),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Get conflicts failed', [
                'batch_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get conflicts',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get migration report for a batch
     *
     * GET /api/migration/batch/{id}/report
     */
    public function getReport(Request $request, string $id): JsonResponse
    {
        try {
            $report = $this->migrationService->generateMigrationReport($id);

            if (isset($report['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $report['error'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Get report failed', [
                'batch_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Detect legacy format from content
     *
     * POST /api/migration/detect-format
     */
    public function detectFormat(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1048576', // 1MB max for detection
        ]);

        try {
            $content = $request->input('content');
            $detection = $this->migrationService->detectLegacyFormat(is_string($content) ? $content : '');

            return response()->json([
                'success' => true,
                'data' => $detection,
            ]);
        } catch (\Exception $e) {
            Log::error('[MigrationController] Detect format failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Format detection failed',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get transformation rules for an import type
     *
     * GET /api/migration/transformation-rules
     */
    public function getTransformationRules(Request $request): JsonResponse
    {
        $importType = $request->query('import_type');

        if (! $importType) {
            return response()->json([
                'success' => true,
                'data' => [
                    'import_types' => DataImportService::IMPORT_TYPES,
                    'legacy_formats' => DataMigrationService::LEGACY_FORMATS,
                ],
            ]);
        }

        if (! array_key_exists($importType, DataImportService::IMPORT_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid import type',
            ], 400);
        }

        $rules = $this->migrationService->getTransformationRules($importType);

        return response()->json([
            'success' => true,
            'data' => [
                'import_type' => $importType,
                'rules' => $rules,
            ],
        ]);
    }
}
