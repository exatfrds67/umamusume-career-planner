<?php

declare(strict_types=1);

namespace App\Services\Offline;

use App\Models\Career;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Offline Sync Service
 *
 * Handles synchronization of offline data, conflict detection/resolution,
 * and data integrity validation for Local-to-Account conversion.
 *
 * @see Requirements: FR-10.5, FR-10.8
 */
class OfflineSyncService
{
    /**
     * Sync a batch of offline operations for a user.
     *
     * @param  array<int, array{type: string, endpoint: string, method: string, data: array, entity_id: string|null, timestamp: int}>  $operations
     * @return array{processed: int, succeeded: int, failed: int, conflicts: array, errors: array}
     */
    public function syncBatch(User $user, array $operations): array
    {
        $results = [
            'processed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'conflicts' => [],
            'errors' => [],
        ];

        foreach ($operations as $operation) {
            $results['processed']++;

            try {
                $conflict = $this->detectConflict($user, $operation);

                if ($conflict) {
                    $results['conflicts'][] = $conflict;

                    continue;
                }

                $this->processOperation($user, $operation);
                $results['succeeded']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'operation' => $operation['type'] ?? 'unknown',
                    'entity_id' => $operation['entity_id'] ?? null,
                    'error' => $e->getMessage(),
                ];

                Log::warning('[OfflineSync] Operation failed', [
                    'user_id' => $user->id,
                    'operation' => $operation,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Detect if an operation conflicts with server-side changes.
     *
     * A conflict exists when the same record was modified on the server
     * after the offline operation was created.
     *
     * @param  array{type: string, entity_id: string|null, timestamp: int, data: array}  $operation
     * @return array{entity_id: string, type: string, server_updated_at: string, offline_timestamp: int, server_data: array, offline_data: array}|null
     */
    public function detectConflict(User $user, array $operation): ?array
    {
        if (empty($operation['entity_id'])) {
            return null;
        }

        if (($operation['type'] ?? '') === 'create') {
            return null;
        }

        $career = Career::query()
            ->where('user_id', $user->id)
            ->find($operation['entity_id']);

        if (! $career) {
            return null;
        }

        $offlineTimestamp = Carbon::createFromTimestampMs($operation['timestamp'] ?? 0);

        if ($career->updated_at && $career->updated_at->gt($offlineTimestamp)) {
            return [
                'entity_id' => (string) $career->id,
                'type' => $operation['type'] ?? 'update',
                'server_updated_at' => $career->updated_at->toIso8601String(),
                'offline_timestamp' => $operation['timestamp'] ?? 0,
                'server_data' => $career->toArray(),
                'offline_data' => $operation['data'] ?? [],
            ];
        }

        return null;
    }

    /**
     * Process a single sync operation.
     *
     * @param  array{type: string, endpoint: string, method: string, data: array, entity_id: string|null}  $operation
     */
    public function processOperation(User $user, array $operation): void
    {
        $type = $operation['type'] ?? 'unknown';

        match ($type) {
            'create' => $this->handleCreate($user, $operation['data'] ?? []),
            'update' => $this->handleUpdate($user, $operation['entity_id'] ?? '', $operation['data'] ?? []),
            'delete' => $this->handleDelete($user, $operation['entity_id'] ?? ''),
            default => throw new \InvalidArgumentException("Unknown operation type: {$type}"),
        };
    }

    /**
     * Resolve a conflict by applying the chosen strategy.
     *
     * @param  array{entity_id: string, offline_data: array}  $conflict
     * @param  string  $strategy  One of: server_wins, client_wins, merge
     * @return array{resolved: bool, strategy: string, entity_id: string}
     */
    public function resolveConflict(User $user, array $conflict, string $strategy): array
    {
        $entityId = $conflict['entity_id'];

        match ($strategy) {
            'server_wins' => null,
            'client_wins' => $this->handleUpdate($user, $entityId, $conflict['offline_data']),
            'merge' => $this->handleMerge($user, $entityId, $conflict['offline_data']),
            default => throw new \InvalidArgumentException("Unknown resolution strategy: {$strategy}"),
        };

        return [
            'resolved' => true,
            'strategy' => $strategy,
            'entity_id' => $entityId,
        ];
    }

    /**
     * Handle a create operation.
     */
    private function handleCreate(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $characterId = $data['character_id'] ?? null;

            if (! $characterId) {
                throw new \InvalidArgumentException('character_id is required for create operations');
            }

            Career::query()->create(array_merge($data, [
                'user_id' => $user->id,
            ]));
        });
    }

    /**
     * Handle an update operation.
     */
    private function handleUpdate(User $user, string $entityId, array $data): void
    {
        DB::transaction(function () use ($user, $entityId, $data) {
            $career = Career::query()
                ->where('user_id', $user->id)
                ->findOrFail($entityId);

            $career->update($data);
        });
    }

    /**
     * Handle a delete operation.
     */
    private function handleDelete(User $user, string $entityId): void
    {
        DB::transaction(function () use ($user, $entityId) {
            $career = Career::query()
                ->where('user_id', $user->id)
                ->findOrFail($entityId);

            $career->delete();
        });
    }

    /**
     * Handle a merge operation (combine offline and server data).
     * Offline data overwrites only non-null fields.
     */
    private function handleMerge(User $user, string $entityId, array $offlineData): void
    {
        DB::transaction(function () use ($user, $entityId, $offlineData) {
            $career = Career::query()
                ->where('user_id', $user->id)
                ->findOrFail($entityId);

            $filtered = array_filter($offlineData, fn ($value) => $value !== null);
            $career->update($filtered);
        });
    }
}
