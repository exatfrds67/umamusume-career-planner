<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\Career;
use App\Models\Character;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Career State Synchronization Service
 *
 * Synchronizes career state between different subagents and ensures
 * consistent state across multi-agent workflows.
 */
class CareerStateSyncService
{
    /**
     * Sync states
     */
    public const SYNC_PENDING = 'pending';

    public const SYNC_IN_PROGRESS = 'in_progress';

    public const SYNC_COMPLETED = 'completed';

    public const SYNC_FAILED = 'failed';

    public function __construct(
        private readonly AgentContextService $contextService
    ) {}

    /**
     * Synchronize career state across all agents
     *
     * @return array<string, mixed>
     */
    public function synchronizeCareerState(Career $career): array
    {
        try {
            $syncId = $this->generateSyncId();

            Log::info('[CareerStateSync] Starting career state synchronization', [
                'sync_id' => $syncId,
                'career_id' => $career->id,
            ]);

            $this->updateSyncStatus($syncId, self::SYNC_IN_PROGRESS);

            // Build current career state
            $careerState = $this->buildCareerState($career);

            // Broadcast state to all active agents
            $broadcastResult = $this->broadcastStateToAgents($syncId, $careerState);

            // Verify synchronization
            $verificationResult = $this->verifySynchronization($syncId, $careerState);

            $this->updateSyncStatus($syncId, self::SYNC_COMPLETED);

            Log::info('[CareerStateSync] Career state synchronization completed', [
                'sync_id' => $syncId,
                'career_id' => $career->id,
                'agents_synced' => is_array($broadcastResult['agents']) ? \count($broadcastResult['agents']) : 0,
            ]);

            return [
                'sync_id' => $syncId,
                'status' => self::SYNC_COMPLETED,
                'career_id' => $career->id,
                'state' => $careerState,
                'broadcast_result' => $broadcastResult,
                'verification' => $verificationResult,
                'synced_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStateSync] Career state synchronization failed', [
                'career_id' => $career->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($syncId)) {
                $this->updateSyncStatus($syncId, self::SYNC_FAILED);
            }

            throw $e;
        }
    }

    /**
     * Synchronize character state across agents
     *
     * @return array<string, mixed>
     */
    public function synchronizeCharacterState(Character $character): array
    {
        try {
            $syncId = $this->generateSyncId();

            Log::info('[CareerStateSync] Starting character state synchronization', [
                'sync_id' => $syncId,
                'character_id' => $character->id,
            ]);

            $this->updateSyncStatus($syncId, self::SYNC_IN_PROGRESS);

            // Build current character state
            $characterState = $this->buildCharacterState($character);

            // Broadcast state to all active agents
            $broadcastResult = $this->broadcastStateToAgents($syncId, $characterState);

            // Invalidate old context
            $this->contextService->invalidateCharacterContext($character->id);

            $this->updateSyncStatus($syncId, self::SYNC_COMPLETED);

            Log::info('[CareerStateSync] Character state synchronization completed', [
                'sync_id' => $syncId,
                'character_id' => $character->id,
                'agents_synced' => is_array($broadcastResult['agents']) ? \count($broadcastResult['agents']) : 0,
            ]);

            return [
                'sync_id' => $syncId,
                'status' => self::SYNC_COMPLETED,
                'character_id' => $character->id,
                'state' => $characterState,
                'broadcast_result' => $broadcastResult,
                'synced_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStateSync] Character state synchronization failed', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($syncId)) {
                $this->updateSyncStatus($syncId, self::SYNC_FAILED);
            }

            throw $e;
        }
    }

    /**
     * Get current synchronization status
     *
     * @return array<string, mixed>|null
     */
    public function getSyncStatus(string $syncId): ?array
    {
        $result = Cache::get("sync_status:{$syncId}");

        if (! is_array($result)) {
            return null;
        }

        /** @var array<string, mixed> $typedResult */
        $typedResult = $result;

        return $typedResult;
    }

    /**
     * Subscribe agent to state updates
     *
     * @param  array<int, string>  $stateTypes
     */
    public function subscribeAgent(string $agentId, array $stateTypes = []): bool
    {
        try {
            $result = Cache::get('agent_subscriptions');
            $subscriptions = is_array($result) ? $result : [];

            $subscriptions[$agentId] = [
                'state_types' => $stateTypes,
                'subscribed_at' => now()->toIso8601String(),
            ];

            Cache::put('agent_subscriptions', $subscriptions, 3600);

            Log::info('[CareerStateSync] Agent subscribed to state updates', [
                'agent_id' => $agentId,
                'state_types' => $stateTypes,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[CareerStateSync] Failed to subscribe agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Unsubscribe agent from state updates
     */
    public function unsubscribeAgent(string $agentId): bool
    {
        try {
            $result = Cache::get('agent_subscriptions');
            $subscriptions = is_array($result) ? $result : [];

            if (isset($subscriptions[$agentId])) {
                unset($subscriptions[$agentId]);
                Cache::put('agent_subscriptions', $subscriptions, 3600);

                Log::info('[CareerStateSync] Agent unsubscribed from state updates', [
                    'agent_id' => $agentId,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[CareerStateSync] Failed to unsubscribe agent', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Notify agents of state change
     *
     * @param  array<string, mixed>  $stateData
     * @return array<string, mixed>
     */
    public function notifyStateChange(string $stateType, array $stateData): array
    {
        try {
            $result = Cache::get('agent_subscriptions');
            $subscriptions = is_array($result) ? $result : [];
            $notifiedAgents = [];

            foreach ($subscriptions as $agentId => $subscription) {
                // Check if agent is subscribed to this state type
                if (! is_array($subscription)) {
                    continue;
                }
                $stateTypes = isset($subscription['state_types']) && is_array($subscription['state_types']) ? $subscription['state_types'] : [];
                if (empty($stateTypes) || \in_array($stateType, $stateTypes)) {
                    $agentIdStr = is_string($agentId) ? $agentId : (string) $agentId;
                    $this->sendStateUpdate($agentIdStr, $stateType, $stateData);
                    $notifiedAgents[] = $agentIdStr;
                }
            }

            Log::info('[CareerStateSync] State change notified', [
                'state_type' => $stateType,
                'agents_notified' => \count($notifiedAgents),
            ]);

            return [
                'state_type' => $stateType,
                'agents_notified' => $notifiedAgents,
                'notification_count' => \count($notifiedAgents),
                'notified_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[CareerStateSync] Failed to notify state change', [
                'state_type' => $stateType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build career state snapshot
     *
     * @return array<string, mixed>
     */
    protected function buildCareerState(Career $career): array
    {
        $startDate = $career->started_at;
        $endDate = $career->completed_at;

        return [
            'career_id' => $career->id,
            'character_id' => $career->character_id,
            'scenario_type' => $career->scenario_type,
            'current_turn' => $career->current_turn ?? 0,
            'is_active' => $career->completed_at === null,
            'start_date' => $startDate instanceof \Illuminate\Support\Carbon ? $startDate->toIso8601String() : (is_string($startDate) ? $startDate : null),
            'end_date' => $endDate instanceof \Illuminate\Support\Carbon ? $endDate->toIso8601String() : (is_string($endDate) ? $endDate : null),
            'final_stats' => [
                'speed' => $career->final_speed,
                'stamina' => $career->final_stamina,
                'power' => $career->final_power,
                'guts' => $career->final_guts,
                'wit' => $career->final_wit,
            ],
            'snapshot_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build character state snapshot
     *
     * @return array<string, mixed>
     */
    protected function buildCharacterState(Character $character): array
    {
        return [
            'character_id' => $character->id,
            'name' => $character->name,
            'scenario_type' => $character->scenario_type,
            'career_stage' => $character->career_stage,
            'current_turn' => $character->current_turn,
            'stats' => [
                'speed' => $character->current_stats['speed'] ?? 0,
                'stamina' => $character->current_stats['stamina'] ?? 0,
                'power' => $character->current_stats['power'] ?? 0,
                'guts' => $character->current_stats['guts'] ?? 0,
                'wit' => $character->current_stats['wit'] ?? 0,
            ],
            'state' => [
                'energy_level' => $character->energy_level,
                'mood_status' => $character->mood_status,
                'conditions' => $character->conditions ?? [],
            ],
            'snapshot_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Broadcast state to all active agents
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function broadcastStateToAgents(string $syncId, array $state): array
    {
        $result = Cache::get('agent_subscriptions');
        $subscriptions = is_array($result) ? $result : [];
        $broadcastResults = [];

        foreach ($subscriptions as $agentId => $subscription) {
            try {
                $this->sendStateUpdate($agentId, 'sync', $state);
                $broadcastResults[$agentId] = [
                    'status' => 'success',
                    'sent_at' => now()->toIso8601String(),
                ];
            } catch (\Exception $e) {
                $broadcastResults[$agentId] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                Log::warning('[CareerStateSync] Failed to broadcast to agent', [
                    'sync_id' => $syncId,
                    'agent_id' => $agentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'sync_id' => $syncId,
            'agents' => array_keys($broadcastResults),
            'results' => $broadcastResults,
            'success_count' => \count(\array_filter($broadcastResults, fn ($r) => $r['status'] === 'success')),
            'failure_count' => \count(\array_filter($broadcastResults, fn ($r) => $r['status'] === 'failed')),
        ];
    }

    /**
     * Send state update to specific agent
     *
     * @param  array<string, mixed>  $stateData
     */
    protected function sendStateUpdate(string $agentId, string $stateType, array $stateData): void
    {
        $updateKey = "agent_state_update:{$agentId}";

        $result = Cache::get($updateKey);
        $updates = is_array($result) ? $result : [];
        $updates[] = [
            'type' => $stateType,
            'data' => $stateData,
            'received_at' => now()->toIso8601String(),
        ];

        // Keep only last 50 updates
        if (\count($updates) > 50) {
            $updates = \array_slice($updates, -50);
        }

        Cache::put($updateKey, $updates, 3600);
    }

    /**
     * Verify synchronization success
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function verifySynchronization(string $syncId, array $state): array
    {
        $subResult = Cache::get('agent_subscriptions');
        $subscriptions = is_array($subResult) ? $subResult : [];
        $verificationResults = [];

        foreach ($subscriptions as $agentId => $subscription) {
            $updateKey = "agent_state_update:{$agentId}";
            $updResult = Cache::get($updateKey);
            $updates = is_array($updResult) ? $updResult : [];

            // Check if agent received the sync
            $received = false;
            foreach ($updates as $update) {
                if (! is_array($update)) {
                    continue;
                }
                $updateType = $update['type'] ?? null;
                $updateData = isset($update['data']) && is_array($update['data']) ? $update['data'] : [];
                if ($updateType === 'sync' && isset($updateData['snapshot_at']) && $updateData['snapshot_at'] === $state['snapshot_at']) {
                    $received = true;
                    break;
                }
            }

            $verificationResults[$agentId] = [
                'received' => $received,
                'verified_at' => now()->toIso8601String(),
            ];
        }

        $successCount = \count(\array_filter($verificationResults, fn ($r) => $r['received']));
        $totalCount = \count($verificationResults);

        return [
            'sync_id' => $syncId,
            'agents_verified' => $verificationResults,
            'success_count' => $successCount,
            'total_count' => $totalCount,
            'success_rate' => $totalCount > 0 ? ($successCount / $totalCount) * 100 : 0,
        ];
    }

    /**
     * Helper methods
     */
    protected function generateSyncId(): string
    {
        return 'sync_'.Str::uuid()->toString();
    }

    protected function updateSyncStatus(string $syncId, string $status): void
    {
        $syncStatus = [
            'sync_id' => $syncId,
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put("sync_status:{$syncId}", $syncStatus, 3600);
    }
}
