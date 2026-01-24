<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Data Operation History Service
 *
 * Handles operation logging for all import/export/migration/backup operations,
 * history retrieval with filtering and pagination, and statistics calculation
 * for operation success rates.
 *
 * Requirements: 23.5
 */
class DataOperationHistoryService
{
    /**
     * Operation types
     */
    public const OPERATION_IMPORT = 'import';

    public const OPERATION_EXPORT = 'export';

    public const OPERATION_MIGRATION = 'migration';

    public const OPERATION_BACKUP = 'backup';

    public const OPERATION_RESTORE = 'restore';

    /**
     * Operation statuses
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Cache prefix for statistics
     */
    private const CACHE_PREFIX = 'data_operation_';

    /**
     * Cache TTL in seconds
     */
    private const CACHE_TTL = 300;

    /**
     * Log an operation start
     *
     * @param  int  $userId  User ID
     * @param  string  $operationType  Type of operation
     * @param  array<string, mixed>  $metadata  Operation metadata
     * @return string Operation ID
     */
    public function logOperationStart(int $userId, string $operationType, array $metadata = []): string
    {
        $operationId = Str::uuid()->toString();

        try {
            DB::table('ucp_system_logs')->insert([
                'log_category' => 'data_operation',
                'log_level' => 'info',
                'log_source' => 'web',
                'event_type' => "{$operationType}_started",
                'message' => ucfirst($operationType).' operation started',
                'context_data' => json_encode([
                    'operation_id' => $operationId,
                    'operation_type' => $operationType,
                    'status' => self::STATUS_IN_PROGRESS,
                    'user_id' => $userId,
                    'metadata' => $metadata,
                    'started_at' => now()->toIso8601String(),
                ]),
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Invalidate statistics cache
            $this->invalidateStatisticsCache($userId);

            Log::info('[DataOperationHistoryService] Operation started', [
                'operation_id' => $operationId,
                'operation_type' => $operationType,
                'user_id' => $userId,
            ]);

            return $operationId;
        } catch (\Exception $e) {
            Log::error('[DataOperationHistoryService] Failed to log operation start', [
                'operation_type' => $operationType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return $operationId;
        }
    }

    /**
     * Log operation progress update
     *
     * @param  string  $operationId  Operation ID
     * @param  int  $progress  Progress percentage (0-100)
     * @param  array<string, mixed>  $details  Progress details
     */
    public function logOperationProgress(string $operationId, int $progress, array $details = []): void
    {
        try {
            DB::table('ucp_system_logs')->insert([
                'log_category' => 'data_operation',
                'log_level' => 'debug',
                'log_source' => 'web',
                'event_type' => 'operation_progress',
                'message' => "Operation progress: {$progress}%",
                'context_data' => json_encode([
                    'operation_id' => $operationId,
                    'progress' => $progress,
                    'details' => $details,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('[DataOperationHistoryService] Failed to log operation progress', [
                'operation_id' => $operationId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log operation completion
     *
     * @param  string  $operationId  Operation ID
     * @param  int  $userId  User ID
     * @param  string  $operationType  Type of operation
     * @param  bool  $success  Whether operation succeeded
     * @param  array<string, mixed>  $results  Operation results
     */
    public function logOperationComplete(
        string $operationId,
        int $userId,
        string $operationType,
        bool $success,
        array $results = []
    ): void {
        $status = $success ? self::STATUS_COMPLETED : self::STATUS_FAILED;

        try {
            DB::table('ucp_system_logs')->insert([
                'log_category' => 'data_operation',
                'log_level' => $success ? 'info' : 'error',
                'log_source' => 'web',
                'event_type' => "{$operationType}_completed",
                'message' => ucfirst($operationType).' operation '.($success ? 'completed successfully' : 'failed'),
                'context_data' => json_encode([
                    'operation_id' => $operationId,
                    'operation_type' => $operationType,
                    'status' => $status,
                    'user_id' => $userId,
                    'results' => $results,
                    'completed_at' => now()->toIso8601String(),
                ]),
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Invalidate statistics cache
            $this->invalidateStatisticsCache($userId);

            Log::info('[DataOperationHistoryService] Operation completed', [
                'operation_id' => $operationId,
                'operation_type' => $operationType,
                'status' => $status,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error('[DataOperationHistoryService] Failed to log operation completion', [
                'operation_id' => $operationId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get operation history with filtering and pagination
     *
     * @param  int  $userId  User ID
     * @param  array<string, mixed>  $filters  Filter options
     * @param  int  $page  Page number
     * @param  int  $perPage  Items per page
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, int|bool>}
     */
    public function getHistory(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $query = DB::table('ucp_system_logs')
                ->where('log_category', '=', 'data_operation')
                ->where('user_id', $userId)
                ->whereIn('event_type', [
                    'import_started',
                    'import_completed',
                    'export_started',
                    'export_completed',
                    'migration_started',
                    'migration_completed',
                    'backup_started',
                    'backup_completed',
                    'restore_started',
                    'restore_completed',
                ]);

            // Apply filters
            if (! empty($filters['operation_type']) && is_string($filters['operation_type'])) {
                $query->where('event_type', 'like', $filters['operation_type'].'%');
            }

            if (! empty($filters['status'])) {
                $query->whereRaw("JSON_EXTRACT(context_data, '$.status') = ?", [$filters['status']]);
            }

            if (! empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            // Get total count
            $total = $query->count();

            // Get paginated results
            $results = $query
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // Transform results
            /** @var array<int, array<string, mixed>> $data */
            $data = $results->map(function ($row): array {
                /** @var array<string, mixed> $contextData */
                $contextData = json_decode($row->context_data, true) ?? [];
                if (! is_array($contextData)) {
                    $contextData = [];
                }

                return [
                    'id' => $row->id,
                    'operation_id' => (isset($contextData['operation_id']) ? $contextData['operation_id'] : null),
                    'operation_type' => (isset($contextData['operation_type']) && is_string($contextData['operation_type'])) ? $contextData['operation_type'] : $this->extractOperationType($row->event_type),
                    'status' => (isset($contextData['status']) && is_string($contextData['status'])) ? $contextData['status'] : 'unknown',
                    'message' => $row->message,
                    'metadata' => (isset($contextData['metadata']) && is_array($contextData['metadata'])) ? $contextData['metadata'] : [],
                    'results' => (isset($contextData['results']) && is_array($contextData['results'])) ? $contextData['results'] : [],
                    'started_at' => (isset($contextData['started_at']) ? $contextData['started_at'] : null),
                    'completed_at' => (isset($contextData['completed_at']) ? $contextData['completed_at'] : null),
                    'created_at' => $row->created_at,
                ];
            })->all();

            return [
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $perPage),
                    'has_more' => ($page * $perPage) < $total,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[DataOperationHistoryService] Failed to get history', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_more' => false,
                ],
            ];
        }
    }

    /**
     * Get recent operations for dashboard
     *
     * @param  int  $userId  User ID
     * @param  int  $limit  Number of operations to return
     * @return array<int, array<string, mixed>>
     */
    public function getRecentOperations(int $userId, int $limit = 10): array
    {
        try {
            $results = DB::table('ucp_system_logs')
                ->where('log_category', '=', 'data_operation')
                ->where('user_id', $userId)
                ->whereIn('event_type', [
                    'import_completed',
                    'export_completed',
                    'migration_completed',
                    'backup_completed',
                    'restore_completed',
                ])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            /** @var array<int, array<string, mixed>> $result */
            $result = $results->map(function ($row): array {
                /** @var array<string, mixed> $contextData */
                $contextData = json_decode($row->context_data, true) ?? [];
                if (! is_array($contextData)) {
                    $contextData = [];
                }

                return [
                    'operation_id' => (isset($contextData['operation_id']) ? $contextData['operation_id'] : null),
                    'operation_type' => (isset($contextData['operation_type']) && is_string($contextData['operation_type'])) ? $contextData['operation_type'] : $this->extractOperationType($row->event_type),
                    'status' => (isset($contextData['status']) && is_string($contextData['status'])) ? $contextData['status'] : 'unknown',
                    'message' => $row->message,
                    'results' => (isset($contextData['results']) && is_array($contextData['results'])) ? $contextData['results'] : [],
                    'completed_at' => isset($contextData['completed_at']) ? $contextData['completed_at'] : $row->created_at,
                ];
            })->all();

            return $result;
        } catch (\Exception $e) {
            Log::error('[DataOperationHistoryService] Failed to get recent operations', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get operation statistics
     *
     * @param  int  $userId  User ID
     * @param  string  $period  Time period (day, week, month, all)
     * @return array<string, mixed>
     */
    public function getStatistics(int $userId, string $period = 'month'): array
    {
        $cacheKey = self::CACHE_PREFIX."stats_{$userId}_{$period}";

        /** @var array<string, mixed> $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $period): array {
            try {
                $query = DB::table('ucp_system_logs')
                    ->where('log_category', '=', 'data_operation')
                    ->where('user_id', $userId)
                    ->whereIn('event_type', [
                        'import_completed',
                        'export_completed',
                        'migration_completed',
                        'backup_completed',
                        'restore_completed',
                    ]);

                // Apply time period filter
                if ($period !== 'all') {
                    $startDate = match ($period) {
                        'day' => now()->subDay(),
                        'week' => now()->subWeek(),
                        'month' => now()->subMonth(),
                        default => now()->subMonth(),
                    };
                    $query->where('created_at', '>=', $startDate);
                }

                $results = $query->get();

                // Calculate statistics
                $stats = [
                    'total_operations' => 0,
                    'successful_operations' => 0,
                    'failed_operations' => 0,
                    'success_rate' => 0,
                    'by_type' => [
                        'import' => ['total' => 0, 'successful' => 0, 'failed' => 0],
                        'export' => ['total' => 0, 'successful' => 0, 'failed' => 0],
                        'migration' => ['total' => 0, 'successful' => 0, 'failed' => 0],
                        'backup' => ['total' => 0, 'successful' => 0, 'failed' => 0],
                        'restore' => ['total' => 0, 'successful' => 0, 'failed' => 0],
                    ],
                    'records_processed' => 0,
                    'period' => $period,
                ];

                foreach ($results as $row) {
                    /** @var array<string, mixed> $contextData */
                    $contextData = json_decode($row->context_data, true) ?? [];
                    if (! is_array($contextData)) {
                        $contextData = [];
                    }
                    $operationType = (isset($contextData['operation_type']) && is_string($contextData['operation_type'])) ? $contextData['operation_type'] : $this->extractOperationType($row->event_type);
                    $status = (isset($contextData['status']) && is_string($contextData['status'])) ? $contextData['status'] : 'unknown';

                    $recordsCount = 0;
                    if (isset($contextData['results']) && is_array($contextData['results'])) {
                        $resultsData = $contextData['results'];
                        if (isset($resultsData['imported']) && is_numeric($resultsData['imported'])) {
                            $recordsCount = (int) $resultsData['imported'];
                        } elseif (isset($resultsData['exported']) && is_numeric($resultsData['exported'])) {
                            $recordsCount = (int) $resultsData['exported'];
                        } elseif (isset($resultsData['record_count']) && is_numeric($resultsData['record_count'])) {
                            $recordsCount = (int) $resultsData['record_count'];
                        }
                    }

                    $stats['total_operations']++;
                    $stats['records_processed'] += $recordsCount;

                    if ($status === self::STATUS_COMPLETED) {
                        $stats['successful_operations']++;
                    } else {
                        $stats['failed_operations']++;
                    }

                    if (isset($stats['by_type'][$operationType])) {
                        $stats['by_type'][$operationType]['total']++;
                        if ($status === self::STATUS_COMPLETED) {
                            $stats['by_type'][$operationType]['successful']++;
                        } else {
                            $stats['by_type'][$operationType]['failed']++;
                        }
                    }
                }

                // Calculate success rate
                if ($stats['total_operations'] > 0) {
                    $stats['success_rate'] = round(($stats['successful_operations'] / $stats['total_operations']) * 100, 1);
                }

                return $stats;
            } catch (\Exception $e) {
                Log::error('[DataOperationHistoryService] Failed to get statistics', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'total_operations' => 0,
                    'successful_operations' => 0,
                    'failed_operations' => 0,
                    'success_rate' => 0,
                    'by_type' => [],
                    'records_processed' => 0,
                    'period' => $period,
                ];
            }
        });

        return $result;
    }

    /**
     * Get ongoing operations
     *
     * @param  int  $userId  User ID
     * @return array<int, array<string, mixed>>
     */
    public function getOngoingOperations(int $userId): array
    {
        try {
            $results = DB::table('ucp_system_logs')
                ->where('log_category', '=', 'data_operation')
                ->where('user_id', $userId)
                ->whereIn('event_type', [
                    'import_started',
                    'export_started',
                    'migration_started',
                    'backup_started',
                    'restore_started',
                ])
                ->where('created_at', '>=', now()->subHours(24))
                ->orderBy('created_at', 'desc')
                ->get();

            // Filter out completed operations
            $completedIds = DB::table('ucp_system_logs')
                ->where('log_category', '=', 'data_operation')
                ->where('user_id', $userId)
                ->whereIn('event_type', [
                    'import_completed',
                    'export_completed',
                    'migration_completed',
                    'backup_completed',
                    'restore_completed',
                ])
                ->where('created_at', '>=', now()->subHours(24))
                ->pluck('context_data')
                ->map(function ($data) {
                    if (! is_string($data)) {
                        return null;
                    }
                    $decoded = json_decode($data, true);

                    return is_array($decoded) && isset($decoded['operation_id']) ? $decoded['operation_id'] : null;
                })
                ->filter()
                ->toArray();

            return $results->filter(function ($row) use ($completedIds) {
                /** @var array<string, mixed> $contextData */
                $contextData = json_decode($row->context_data, true) ?? [];
                if (! is_array($contextData)) {
                    $contextData = [];
                }
                $operationId = (isset($contextData['operation_id']) ? $contextData['operation_id'] : null);

                return $operationId && ! in_array($operationId, $completedIds);
            })->map(function ($row): array {
                /** @var array<string, mixed> $contextData */
                $contextData = json_decode($row->context_data, true) ?? [];
                if (! is_array($contextData)) {
                    $contextData = [];
                }

                return [
                    'operation_id' => (isset($contextData['operation_id']) ? $contextData['operation_id'] : null),
                    'operation_type' => (isset($contextData['operation_type']) && is_string($contextData['operation_type'])) ? $contextData['operation_type'] : $this->extractOperationType($row->event_type),
                    'status' => self::STATUS_IN_PROGRESS,
                    'message' => $row->message,
                    'metadata' => (isset($contextData['metadata']) && is_array($contextData['metadata'])) ? $contextData['metadata'] : [],
                    'started_at' => isset($contextData['started_at']) ? $contextData['started_at'] : $row->created_at,
                ];
            })->values()->all();
        } catch (\Exception $e) {
            Log::error('[DataOperationHistoryService] Failed to get ongoing operations', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract operation type from event type
     */
    private function extractOperationType(string $eventType): string
    {
        $parts = explode('_', $eventType);

        return $parts[0] ?? 'unknown';
    }

    /**
     * Invalidate statistics cache for a user
     */
    private function invalidateStatisticsCache(int $userId): void
    {
        $periods = ['day', 'week', 'month', 'all'];
        foreach ($periods as $period) {
            Cache::forget(self::CACHE_PREFIX."stats_{$userId}_{$period}");
        }
    }
}
