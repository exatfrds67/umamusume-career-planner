<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Agent Communication Service
 *
 * Manages inter-agent communication, message passing, and data sharing
 * for collaborative multi-agent workflows.
 */
class AgentCommunicationService
{
    /**
     * Message types
     */
    public const MSG_REQUEST = 'request';

    public const MSG_RESPONSE = 'response';

    public const MSG_BROADCAST = 'broadcast';

    public const MSG_NOTIFICATION = 'notification';

    /**
     * Message priorities
     */
    public const PRIORITY_LOW = 1;

    public const PRIORITY_NORMAL = 5;

    public const PRIORITY_HIGH = 10;

    /**
     * Send a message from one agent to another
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sendMessage(
        string $fromAgentId,
        string $toAgentId,
        string $type,
        array $payload,
        int $priority = self::PRIORITY_NORMAL
    ): array {
        try {
            $messageId = $this->generateMessageId();

            $message = [
                'id' => $messageId,
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'type' => $type,
                'payload' => $payload,
                'priority' => $priority,
                'status' => 'sent',
                'created_at' => now()->toIso8601String(),
            ];

            // Store message in agent's inbox
            $this->addToInbox($toAgentId, $message);

            Log::info('[AgentCommunication] Message sent', [
                'message_id' => $messageId,
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'type' => $type,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('[AgentCommunication] Failed to send message', [
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Broadcast a message to multiple agents
     *
     * @param  array<int, string>  $toAgentIds
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function broadcastMessage(
        string $fromAgentId,
        array $toAgentIds,
        array $payload
    ): array {
        $messages = [];

        foreach ($toAgentIds as $toAgentId) {
            $messages[] = $this->sendMessage(
                $fromAgentId,
                $toAgentId,
                self::MSG_BROADCAST,
                $payload,
                self::PRIORITY_NORMAL
            );
        }

        Log::info('[AgentCommunication] Broadcast sent', [
            'from' => $fromAgentId,
            'recipient_count' => count($toAgentIds),
        ]);

        return $messages;
    }

    /**
     * Receive messages for an agent
     *
     * @return array<int, array<string, mixed>>
     */
    public function receiveMessages(string $agentId, int $limit = 10): array
    {
        $inbox = $this->getInbox($agentId);

        // Sort by priority (high to low) and timestamp
        usort($inbox, function (array $a, array $b): int {
            $priorityA = isset($a['priority']) && is_numeric($a['priority']) ? (int) $a['priority'] : 0;
            $priorityB = isset($b['priority']) && is_numeric($b['priority']) ? (int) $b['priority'] : 0;
            if ($priorityA === $priorityB) {
                $createdA = isset($a['created_at']) && \is_string($a['created_at']) ? $a['created_at'] : '';
                $createdB = isset($b['created_at']) && \is_string($b['created_at']) ? $b['created_at'] : '';

                return strcmp($createdA, $createdB);
            }

            return $priorityB - $priorityA;
        });

        return array_slice($inbox, 0, $limit);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $agentId, string $messageId): bool
    {
        try {
            $inbox = $this->getInbox($agentId);

            foreach ($inbox as &$message) {
                if ($message['id'] === $messageId) {
                    $message['status'] = 'read';
                    $message['read_at'] = now()->toIso8601String();
                    break;
                }
            }

            $this->saveInbox($agentId, $inbox);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentCommunication] Failed to mark message as read', [
                'agent_id' => $agentId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Share data between agents
     */
    public function shareData(
        string $fromAgentId,
        string $toAgentId,
        string $dataKey,
        mixed $dataValue
    ): bool {
        try {
            $sharedDataKey = "shared_data:{$fromAgentId}:{$toAgentId}:{$dataKey}";

            Cache::put($sharedDataKey, [
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'key' => $dataKey,
                'value' => $dataValue,
                'shared_at' => now()->toIso8601String(),
            ], 3600);

            Log::info('[AgentCommunication] Data shared', [
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'key' => $dataKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentCommunication] Failed to share data', [
                'from' => $fromAgentId,
                'to' => $toAgentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retrieve shared data
     */
    public function getSharedData(
        string $fromAgentId,
        string $toAgentId,
        string $dataKey
    ): mixed {
        $sharedDataKey = "shared_data:{$fromAgentId}:{$toAgentId}:{$dataKey}";
        $data = Cache::get($sharedDataKey);

        return is_array($data) && isset($data['value']) ? $data['value'] : null;
    }

    /**
     * Create a shared context for collaborative agents
     *
     * @param  array<string, mixed>  $initialData
     * @return array<string, mixed>
     */
    public function createSharedContext(string $contextId, array $initialData = []): array
    {
        $context = [
            'id' => $contextId,
            'data' => $initialData,
            'participants' => [],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put("shared_context:{$contextId}", $context, 3600);

        Log::info('[AgentCommunication] Shared context created', [
            'context_id' => $contextId,
        ]);

        return $context;
    }

    /**
     * Add agent to shared context
     */
    public function joinSharedContext(string $contextId, string $agentId): bool
    {
        try {
            $context = Cache::get("shared_context:{$contextId}");

            if (! \is_array($context)) {
                throw new \RuntimeException("Shared context not found: {$contextId}");
            }

            /** @var array<int, string> $participants */
            $participants = $context['participants'] ?? [];
            if (! in_array($agentId, $participants)) {
                $context['participants'][] = $agentId;
                $context['updated_at'] = now()->toIso8601String();

                Cache::put("shared_context:{$contextId}", $context, 3600);

                Log::info('[AgentCommunication] Agent joined shared context', [
                    'context_id' => $contextId,
                    'agent_id' => $agentId,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentCommunication] Failed to join shared context', [
                'context_id' => $contextId,
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update shared context data
     *
     * @param  array<string, mixed>  $updates
     */
    public function updateSharedContext(
        string $contextId,
        string $agentId,
        array $updates
    ): bool {
        try {
            $context = Cache::get("shared_context:{$contextId}");

            if (! \is_array($context)) {
                throw new \RuntimeException("Shared context not found: {$contextId}");
            }

            /** @var array<int, string> $participants */
            $participants = $context['participants'] ?? [];
            if (! in_array($agentId, $participants)) {
                throw new \RuntimeException("Agent not in shared context: {$agentId}");
            }

            /** @var array<string, mixed> $existingData */
            $existingData = $context['data'] ?? [];
            $context['data'] = array_merge($existingData, $updates);
            $context['updated_at'] = now()->toIso8601String();
            $context['last_updated_by'] = $agentId;

            Cache::put("shared_context:{$contextId}", $context, 3600);

            Log::info('[AgentCommunication] Shared context updated', [
                'context_id' => $contextId,
                'agent_id' => $agentId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentCommunication] Failed to update shared context', [
                'context_id' => $contextId,
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get shared context
     *
     * @return array<string, mixed>|null
     */
    public function getSharedContext(string $contextId): ?array
    {
        $context = Cache::get("shared_context:{$contextId}");

        if (! \is_array($context)) {
            return null;
        }

        // Ensure string keys
        $result = [];
        foreach ($context as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }

    /**
     * Helper methods
     */
    protected function generateMessageId(): string
    {
        return 'msg_'.uniqid().'_'.Str::random(8);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getInbox(string $agentId): array
    {
        $inbox = Cache::get("agent_inbox:{$agentId}", []);

        if (! \is_array($inbox)) {
            return [];
        }

        // Ensure proper typing
        $result = [];
        foreach ($inbox as $item) {
            if (\is_array($item)) {
                /** @var array<string, mixed> $typedItem */
                $typedItem = [];
                foreach ($item as $key => $value) {
                    $typedItem[(string) $key] = $value;
                }
                $result[] = $typedItem;
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    protected function addToInbox(string $agentId, array $message): void
    {
        $inbox = $this->getInbox($agentId);
        $inbox[] = $message;

        // Keep only last 100 messages
        if (count($inbox) > 100) {
            $inbox = array_slice($inbox, -100);
        }

        $this->saveInbox($agentId, $inbox);
    }

    /**
     * @param  array<int, array<string, mixed>>  $inbox
     */
    protected function saveInbox(string $agentId, array $inbox): void
    {
        Cache::put("agent_inbox:{$agentId}", $inbox, 3600);
    }
}
