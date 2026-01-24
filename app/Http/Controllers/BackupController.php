<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BackupCreateRequest;
use App\Http\Requests\BackupRestoreRequest;
use App\Http\Requests\BackupScheduleRequest;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Backup Controller
 *
 * Handles backup and restore operations including manual backups,
 * scheduled backups, and data restoration with integrity verification.
 *
 * Requirements: 23.4
 */
class BackupController extends Controller
{
    public function __construct(
        private readonly BackupService $backupService
    ) {}

    /**
     * Get the authenticated user ID.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function getUserId(Request $request): int
    {
        $user = $request->user();
        if ($user === null) {
            abort(401, 'Unauthenticated');
        }

        return $user->id ?? throw new \Exception('User required');
    }

    /**
     * Show the backup management interface
     */
    public function index(): View
    {
        $userId = (int) auth()->id();
        $backups = $this->backupService->listBackups($userId);
        $schedules = $this->backupService->getBackupSchedule($userId);
        $statistics = $this->backupService->getBackupStatistics($userId);

        return view('backup.index', compact('backups', 'schedules', 'statistics'));
    }

    /**
     * Create a manual backup
     *
     * POST /api/backup/create
     */
    public function create(BackupCreateRequest $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            $options = [
                'type' => $request->input('type', BackupService::TYPE_FULL),
                'compress' => $request->boolean('compress', true),
                'encrypt' => $request->boolean('encrypt', false),
                'encryption_key' => $request->input('encryption_key'),
                'include_types' => $request->input('include_types', ['character', 'career', 'skill', 'support_card']),
                'description' => $request->input('description', 'Manual backup'),
            ];

            $result = $this->backupService->createBackup($userId, $options);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup creation failed',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'data' => [
                    'backup_id' => $result['backup_id'],
                    'file_path' => $result['file_path'],
                    'file_size' => $result['file_size'],
                    'checksum' => $result['checksum'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Create backup failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during backup creation',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * List all backups for the authenticated user
     *
     * GET /api/backup/list
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            $filters = [
                'type' => $request->query('type'),
                'status' => $request->query('status'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'page' => (int) $request->query('page', 1),
                'per_page' => (int) $request->query('per_page', 20),
            ];

            $result = $this->backupService->listBackups($userId, array_filter($filters));

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] List backups failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backups',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get backup details
     *
     * GET /api/backup/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $backup = $this->backupService->getBackupDetails($id, $userId);

            if (! $backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $backup,
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Get backup details failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backup details',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Restore from backup
     *
     * POST /api/backup/{id}/restore
     */
    public function restore(BackupRestoreRequest $request, string $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            $options = [
                'decryption_key' => $request->input('decryption_key'),
                'overwrite_existing' => $request->boolean('overwrite_existing', false),
                'restore_types' => $request->input('restore_types'),
                'dry_run' => $request->boolean('dry_run', false),
            ];

            $result = $this->backupService->restoreFromBackup($id, $userId, $options);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restore failed',
                    'errors' => $result['errors'],
                    'warnings' => $result['warnings'] ?? [],
                ], 422);
            }

            $message = isset($result['dry_run']) && $result['dry_run']
                ? 'Dry run completed successfully'
                : 'Restore completed successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'restored_counts' => $result['restored_counts'],
                    'warnings' => $result['warnings'] ?? [],
                    'dry_run' => $result['dry_run'] ?? false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Restore failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during restore',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Delete a backup
     *
     * DELETE /api/backup/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $result = $this->backupService->deleteBackup($id, $userId);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete backup',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Delete backup failed', [
                'backup_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting backup',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Schedule automated backup
     *
     * POST /api/backup/schedule
     */
    public function schedule(BackupScheduleRequest $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            $scheduleConfig = [
                'frequency' => $request->input('frequency', BackupService::SCHEDULE_DAILY),
                'time' => $request->input('time', '02:00'),
                'type' => $request->input('type', BackupService::TYPE_FULL),
                'compress' => $request->boolean('compress', true),
                'encrypt' => $request->boolean('encrypt', false),
                'retention_days' => is_numeric($request->input('retention_days', 30))
                    ? (int) $request->input('retention_days', 30)
                    : 30,
                'include_types' => is_array($request->input('include_types'))
                    ? $request->input('include_types')
                    : ['character', 'career', 'skill', 'support_card'],
            ];

            $result = $this->backupService->scheduleBackup($userId, $scheduleConfig);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create backup schedule',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup schedule created successfully',
                'data' => [
                    'schedule_id' => $result['schedule_id'] ?? null,
                    'schedule' => $result['schedule'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Schedule backup failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating schedule',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get backup schedules
     *
     * GET /api/backup/schedule
     */
    public function getSchedules(Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $result = $this->backupService->getBackupSchedule($userId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Get schedules failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backup schedules',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Update backup schedule
     *
     * PUT /api/backup/schedule/{id}
     */
    public function updateSchedule(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);

            $updates = $request->only([
                'frequency',
                'time',
                'type',
                'compress',
                'encrypt',
                'retention_days',
                'include_types',
                'enabled',
            ]);

            $result = $this->backupService->updateBackupSchedule($id, $userId, $updates);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update schedule',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $result['schedule'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Update schedule failed', [
                'schedule_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating schedule',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Delete backup schedule
     *
     * DELETE /api/backup/schedule/{id}
     */
    public function deleteSchedule(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $result = $this->backupService->deleteBackupSchedule($id, $userId);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete schedule',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Delete schedule failed', [
                'schedule_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting schedule',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Get backup statistics
     *
     * GET /api/backup/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $statistics = $this->backupService->getBackupStatistics($userId);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Get statistics failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backup statistics',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }

    /**
     * Cleanup old backups
     *
     * POST /api/backup/cleanup
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserId($request);
            $retentionDaysInput = $request->input('retention_days', 30);
            $retentionDays = is_numeric($retentionDaysInput) ? (int) $retentionDaysInput : 30;

            $result = $this->backupService->cleanupOldBackups($userId, $retentionDays);

            return response()->json([
                'success' => $result['success'],
                'message' => "Deleted {$result['deleted']} old backup(s)",
                'data' => [
                    'deleted' => $result['deleted'],
                ],
                'errors' => $result['errors'],
            ]);
        } catch (\Exception $e) {
            Log::error('[BackupController] Cleanup failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId($request),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during cleanup',
                'errors' => [config('app.debug') ? $e->getMessage() : 'Internal server error'],
            ], 500);
        }
    }
}
