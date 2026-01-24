<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExportGenerateRequest;
use App\Http\Requests\ExportScheduleRequest;
use App\Services\DataExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Export Controller
 *
 * Handles data export operations including generation, download, templates, and scheduling.
 * Supports JSON, CSV, and PDF formats with selective data export.
 *
 * Requirements: 23.2
 */
class ExportController extends Controller
{
    public function __construct(
        private readonly DataExportService $exportService
    ) {}

    /**
     * Show the export interface
     */
    public function index(): View
    {
        $exportTypes = DataExportService::EXPORT_TYPES;
        $supportedFormats = DataExportService::SUPPORTED_FORMATS;
        $templates = $this->exportService->getTemplates();

        return view('export.index', compact('exportTypes', 'supportedFormats', 'templates'));
    }

    /**
     * Generate export with filters
     *
     * POST /api/export/generate
     */
    public function generate(ExportGenerateRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user === null) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            /** @var \App\Models\User $user */
            $exportType = $request->input('export_type');
            $exportType = is_string($exportType) ? $exportType : 'json';
            $format = $request->input('format', 'json');
            $filters = $request->input('filters', []);
            $filters = is_array($filters) ? $filters : [];
            $saveToFile = $request->boolean('save_to_file', false);
            $userId = $user->id ?? throw new \Exception('User required');

            // Generate export data
            $result = $this->exportService->generateExport($exportType, $userId, $filters);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate export',
                    'errors' => $result['errors'],
                ], 422);
            }

            // Convert to requested format
            /** @var string $formatStr */
            $formatStr = $format;
            $content = match ($formatStr) {
                'json' => $this->exportService->toJson($result['data']),
                'csv' => $this->exportService->toCsv($result['data'], $exportType),
                'pdf' => $this->exportService->toPdf($result['data'], $exportType),
                default => $this->exportService->toJson($result['data']),
            };

            // Save to file if requested
            $fileInfo = null;
            if ($saveToFile) {
                $fileInfo = $this->exportService->saveExport($content, $formatStr, $exportType, $userId);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully exported {$result['count']} record(s)",
                'data' => [
                    'export_type' => $exportType,
                    'format' => $format,
                    'record_count' => $result['count'],
                    'content' => $saveToFile ? null : $content,
                    'file_info' => $fileInfo,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Generate failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during export',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Download generated export
     *
     * GET /api/export/download/{path}
     */
    public function download(Request $request, string $path): Response|JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $userId = $user->id ?? throw new \Exception('User required');
            $result = $this->exportService->downloadExport($path, $userId);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Download failed',
                ], 404);
            }

            return response($result['content'])
                ->header('Content-Type', $result['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="'.$result['file_name'].'"')
                ->header('Content-Length', (string) \strlen($result['content']));
        } catch (\Exception $e) {
            Log::error('[ExportController] Download failed', [
                'error' => $e->getMessage(),
                'path' => $path,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during download',
            ], 500);
        }
    }

    /**
     * Get export templates
     *
     * GET /api/export/templates
     */
    public function templates(Request $request): JsonResponse
    {
        try {
            $exportType = $request->query('type');
            $templates = $this->exportService->getTemplates();

            if ($exportType) {
                $filteredTemplates = array_filter($templates, function ($template) use ($exportType) {
                    return \in_array($exportType, $template['types'], true);
                });

                return response()->json([
                    'success' => true,
                    'data' => [
                        'type' => $exportType,
                        'templates' => $filteredTemplates,
                        'field_mapping' => $this->exportService->getFieldMapping(is_string($exportType) ? $exportType : 'character'),
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'export_types' => DataExportService::EXPORT_TYPES,
                    'supported_formats' => DataExportService::SUPPORTED_FORMATS,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Templates failed', [
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
     * Get export history
     *
     * GET /api/export/history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $userId = $user->id ?? throw new \Exception('User required');
            $limit = (int) $request->query('limit', 20);

            $history = $this->exportService->getExportHistory($userId, $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] History failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve export history',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Schedule automated export
     *
     * POST /api/export/schedule
     */
    public function schedule(ExportScheduleRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $userId = $user->id ?? throw new \Exception('User required');
            $scheduleConfig = [
                'export_type' => $request->input('export_type'),
                'format' => $request->input('format', 'json'),
                'filters' => $request->input('filters', []),
                'frequency' => $request->input('frequency', 'weekly'),
                'day_of_week' => $request->input('day_of_week'),
                'time' => $request->input('time', '00:00'),
                'email_notification' => $request->boolean('email_notification', false),
                'is_active' => true,
            ];

            $result = $this->exportService->scheduleExport($userId, $scheduleConfig);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'schedule_id' => $result['schedule_id'],
                    'config' => $scheduleConfig,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Schedule failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while scheduling export',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get scheduled exports
     *
     * GET /api/export/schedules
     */
    public function schedules(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $userId = $user->id ?? throw new \Exception('User required');
            $schedules = $this->exportService->getScheduledExports($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'schedules' => $schedules,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Get schedules failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve scheduled exports',
            ], 500);
        }
    }

    /**
     * Delete scheduled export
     *
     * DELETE /api/export/schedule/{scheduleId}
     */
    public function deleteSchedule(Request $request, string $scheduleId): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $userId = $user->id ?? throw new \Exception('User required');
            $deleted = $this->exportService->deleteScheduledExport($userId, $scheduleId);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduled export not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Scheduled export deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Delete schedule failed', [
                'error' => $e->getMessage(),
                'schedule_id' => $scheduleId,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete scheduled export',
            ], 500);
        }
    }

    /**
     * Preview export data without generating file
     *
     * POST /api/export/preview
     */
    public function preview(ExportGenerateRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \App\Models\User $user */
            $exportType = $request->input('export_type');
            $exportType = is_string($exportType) ? $exportType : 'json';
            $filters = $request->input('filters', []);
            $filters = is_array($filters) ? $filters : [];
            $userId = $user->id ?? throw new \Exception('User required');

            // Generate export data
            $result = $this->exportService->generateExport($exportType, $userId, $filters);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate preview',
                    'errors' => $result['errors'],
                ], 422);
            }

            // Return preview with limited data
            $previewData = \array_slice($result['data'], 0, 10);

            return response()->json([
                'success' => true,
                'message' => 'Preview generated successfully',
                'data' => [
                    'export_type' => $exportType,
                    'total_records' => $result['count'],
                    'preview_records' => \count($previewData),
                    'preview' => $previewData,
                    'field_mapping' => $this->exportService->getFieldMapping($exportType),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[ExportController] Preview failed', [
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
}
