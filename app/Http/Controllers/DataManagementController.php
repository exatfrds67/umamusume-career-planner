<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Services\DataExportService;
use App\Services\DataImportService;
use App\Services\DataMigrationService;
use App\Services\DataOperationHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Data Management Controller
 *
 * Provides a unified dashboard for all data management operations including
 * import, export, migration, and backup. Includes progress tracking and
 * operation history.
 *
 * Requirements: 23.5
 */
class DataManagementController extends Controller
{
    public function __construct(
        private readonly DataImportService $importService,
        private readonly DataExportService $exportService,
        private readonly DataMigrationService $migrationService,
        private readonly BackupService $backupService,
        private readonly DataOperationHistoryService $historyService
    ) {}

    /**
     * Show the unified data management hub
     */
    public function index(): View
    {
        $importTypes = DataImportService::IMPORT_TYPES;
        $exportTypes = DataExportService::EXPORT_TYPES;
        $exportFormats = DataExportService::SUPPORTED_FORMATS;
        $exportTemplates = DataExportService::EXPORT_TEMPLATES;
        $legacyFormats = DataMigrationService::LEGACY_FORMATS;

        return view('data-management.index', compact(
            'importTypes',
            'exportTypes',
            'exportFormats',
            'exportTemplates',
            'legacyFormats'
        ));
    }

    /**
     * Get dashboard data for the data management hub
     *
     * GET /api/data-management/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            // Get statistics
            $statistics = $this->historyService->getStatistics($userId, 'month');

            // Get recent operations
            $recentOperations = $this->historyService->getRecentOperations($userId, 5);

            // Get ongoing operations
            $ongoingOperations = $this->historyService->getOngoingOperations($userId);

            // Get quick stats
            $quickStats = [
                'total_characters' => \App\Models\Character::where('user_id', $userId)->count(),
                'total_careers' => \App\Models\Career::where('user_id', $userId)->count(),
                'total_backups' => $this->getBackupCount($userId),
                'last_backup' => $this->getLastBackupDate($userId),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'recent_operations' => $recentOperations,
                    'ongoing_operations' => $ongoingOperations,
                    'quick_stats' => $quickStats,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DataManagementController] Dashboard failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get operation history with filtering and pagination
     *
     * GET /api/data-management/history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 20);

            $filters = [
                'operation_type' => $request->query('operation_type'),
                'status' => $request->query('status'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ];

            // Remove null filters
            $filters = array_filter($filters);

            $history = $this->historyService->getHistory($userId, $filters, $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $history['data'],
                'pagination' => $history['pagination'],
            ]);
        } catch (\Exception $e) {
            Log::error('[DataManagementController] History failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load operation history',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get real-time status of ongoing operations
     *
     * GET /api/data-management/status
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $operationId = $request->query('operation_id');

            if ($operationId) {
                // Get specific operation status
                $status = $this->getOperationStatus($operationId, $userId);

                return response()->json([
                    'success' => true,
                    'data' => $status,
                ]);
            }

            // Get all ongoing operations
            $ongoingOperations = $this->historyService->getOngoingOperations($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'ongoing_operations' => $ongoingOperations,
                    'count' => count($ongoingOperations),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DataManagementController] Status failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get operation status',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get statistics for data operations
     *
     * GET /api/data-management/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $period = $request->query('period', 'month');

            $statistics = $this->historyService->getStatistics($userId, $period);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('[DataManagementController] Statistics failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get backup count for user
     */
    private function getBackupCount(int $userId): int
    {
        try {
            return \Illuminate\Support\Facades\DB::table('ucp_system_logs')
                ->where('log_category', 'data_operation')
                ->where('user_id', $userId)
                ->where('event_type', 'backup_completed')
                ->whereRaw("JSON_EXTRACT(context_data, '$.status') = ?", ['completed'])
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get last backup date for user
     */
    private function getLastBackupDate(int $userId): ?string
    {
        try {
            $lastBackup = \Illuminate\Support\Facades\DB::table('ucp_system_logs')
                ->where('log_category', 'data_operation')
                ->where('user_id', $userId)
                ->where('event_type', 'backup_completed')
                ->whereRaw("JSON_EXTRACT(context_data, '$.status') = ?", ['completed'])
                ->orderBy('created_at', 'desc')
                ->first();

            return $lastBackup?->created_at;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get specific operation status
     */
    private function getOperationStatus(string $operationId, int $userId): array
    {
        try {
            $logs = \Illuminate\Support\Facades\DB::table('ucp_system_logs')
                ->where('log_category', 'data_operation')
                ->where('user_id', $userId)
                ->whereRaw("JSON_EXTRACT(context_data, '$.operation_id') = ?", [$operationId])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($logs->isEmpty()) {
                return [
                    'found' => false,
                    'operation_id' => $operationId,
                ];
            }

            $latestLog = $logs->first();
            $contextData = json_decode($latestLog->context_data, true) ?? [];

            // Get progress logs
            $progressLogs = $logs->filter(function ($log) {
                return $log->event_type === 'operation_progress';
            })->map(function ($log) {
                $data = json_decode($log->context_data, true) ?? [];

                return [
                    'progress' => $data['progress'] ?? 0,
                    'details' => $data['details'] ?? [],
                    'timestamp' => $data['timestamp'] ?? $log->created_at,
                ];
            })->values()->toArray();

            return [
                'found' => true,
                'operation_id' => $operationId,
                'operation_type' => $contextData['operation_type'] ?? 'unknown',
                'status' => $contextData['status'] ?? 'unknown',
                'message' => $latestLog->message,
                'metadata' => $contextData['metadata'] ?? [],
                'results' => $contextData['results'] ?? [],
                'started_at' => $contextData['started_at'] ?? null,
                'completed_at' => $contextData['completed_at'] ?? null,
                'progress_history' => $progressLogs,
            ];
        } catch (\Exception $e) {
            Log::error('[DataManagementController] Get operation status failed', [
                'operation_id' => $operationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'found' => false,
                'operation_id' => $operationId,
                'error' => 'Failed to retrieve operation status',
            ];
        }
    }
}
