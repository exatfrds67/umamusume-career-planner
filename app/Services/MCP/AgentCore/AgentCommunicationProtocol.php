<?php

namespace App\Services\MCP\AgentCore;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Communication Protocol
 *
 * Provides inter-agent collaboration and data sharing capabilities
 * for coordinated multi-agent workflows.
 *
 * Requirements: 56.3, 59.3
 */
class AgentCommunicationProtocol
{
    /** @var array<string, array<int, array<string, mixed>>> */
    protected array $messageQueues = [];

    /** @var array<string, array<string, mixed>> */
    protected array $sharedMemory = [];

    protected int $messageRetentionSeconds = 3600; // 1 hour

    /**
     * Send a message from one agent to another
     *
     * @param  array<string, mixed>  $message
     * @return array{
     *     message_id: string,
     *     status: string,
     *     delivered_at: string
     * }
     */
    public function sendMessage(string $fromAgentId, string $toAgentId, array $message): array
    {
        $messageId = $this->generateMessageId();

        $envelope = [
            'message_id' => $messageId,
            'from' => $fromAgentId,
            'to' => $toAgentId,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'status' => 'delivered',
        ];

        // Add to recipient's message queue
        if (! isset($this->messageQueues[$toAgentId])) {
            $this->messageQueues[$toAgentId] = [];
        }

        $this->messageQueues[$toAgentId][] = $envelope;

        // Cache message
        Cache::put(
            "agent_message_{$messageId}",
            $envelope,
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Message sent', [
            'message_id' => $messageId,
            'from' => $fromAgentId,
            'to' => $toAgentId,
        ]);

        return [
            'message_id' => $messageId,
            'status' => 'delivered',
            'delivered_at' => $envelope['timestamp'],
        ];
    }

    /**
     * Broadcast a message to all agents
     *
     * @param  array<string, mixed>  $message
     * @return array{
     *     message_id: string,
     *     status: string,
     *     recipients: int,
     *     broadcast_at: string
     * }
     */
    public function broadcastMessage(array $message): array
    {
        $messageId = $this->generateMessageId();

        $envelope = [
            'message_id' => $messageId,
            'from' => 'system',
            'to' => 'broadcast',
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'status' => 'broadcast',
        ];

        // Add to broadcast queue
        if (! isset($this->messageQueues['broadcast'])) {
            $this->messageQueues['broadcast'] = [];
        }

        $this->messageQueues['broadcast'][] = $envelope;

        // Cache message
        Cache::put(
            "agent_broadcast_{$messageId}",
            $envelope,
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Message broadcast', [
            'message_id' => $messageId,
        ]);

        return [
            'message_id' => $messageId,
            'status' => 'broadcast',
            'recipients' => count($this->messageQueues) - 1, // Exclude broadcast queue
            'broadcast_at' => $envelope['timestamp'],
        ];
    }

    /**
     * Receive messages for an agent
     *
     * @return array<int, array<string, mixed>>
     */
    public function receiveMessages(string $agentId): array
    {
        $messages = [];

        // Get direct messages
        if (isset($this->messageQueues[$agentId])) {
            $messages = array_merge($messages, $this->messageQueues[$agentId]);
            $this->messageQueues[$agentId] = []; // Clear queue after reading
        }

        // Get broadcast messages
        if (isset($this->messageQueues['broadcast'])) {
            $messages = array_merge($messages, $this->messageQueues['broadcast']);
        }

        Log::info('[AgentCommunication] Messages received', [
            'agent_id' => $agentId,
            'count' => count($messages),
        ]);

        return $messages;
    }

    /**
     * Share data in shared memory
     *
     * @return array{
     *     key: string,
     *     status: string,
     *     stored_at: string
     * }
     */
    public function shareData(string $agentId, string $key, mixed $data): array
    {
        $fullKey = "{$agentId}:{$key}";

        $this->sharedMemory[$fullKey] = [
            'agent_id' => $agentId,
            'key' => $key,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        // Cache shared data
        Cache::put(
            "agent_shared_{$fullKey}",
            $this->sharedMemory[$fullKey],
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Data shared', [
            'agent_id' => $agentId,
            'key' => $key,
        ]);

        return [
            'key' => $fullKey,
            'status' => 'stored',
            'stored_at' => $this->sharedMemory[$fullKey]['timestamp'],
        ];
    }

    /**
     * Retrieve shared data
     *
     * @return mixed
     */
    public function retrieveData(string $agentId, string $key)
    {
        $fullKey = "{$agentId}:{$key}";

        if (isset($this->sharedMemory[$fullKey])) {
            Log::info('[AgentCommunication] Data retrieved', [
                'agent_id' => $agentId,
                'key' => $key,
            ]);

            return $this->sharedMemory[$fullKey]['data'];
        }

        // Try cache
        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get("agent_shared_{$fullKey}");
        if (is_array($cached) && isset($cached['data'])) {
            return $cached['data'];
        }

        return null;
    }

    /**
     * Request collaboration from another agent
     *
     * @param  array<string, mixed>  $request
     * @return array{
     *     request_id: string,
     *     status: string,
     *     requested_at: string
     * }
     */
    public function requestCollaboration(string $fromAgentId, string $toAgentId, array $request): array
    {
        $requestId = $this->generateMessageId();

        $collaborationRequest = [
            'request_id' => $requestId,
            'type' => 'collaboration_request',
            'from' => $fromAgentId,
            'to' => $toAgentId,
            'request' => $request,
            'status' => 'pending',
            'timestamp' => now()->toIso8601String(),
        ];

        // Send as message
        $this->sendMessage($fromAgentId, $toAgentId, $collaborationRequest);

        Log::info('[AgentCommunication] Collaboration requested', [
            'request_id' => $requestId,
            'from' => $fromAgentId,
            'to' => $toAgentId,
        ]);

        return [
            'request_id' => $requestId,
            'status' => 'pending',
            'requested_at' => $collaborationRequest['timestamp'],
        ];
    }

    /**
     * Respond to collaboration request
     *
     * @param  array<string, mixed>  $response
     * @return array{
     *     request_id: string,
     *     status: string,
     *     responded_at: string
     * }
     */
    public function respondToCollaboration(string $requestId, string $agentId, array $response): array
    {
        $collaborationResponse = [
            'request_id' => $requestId,
            'type' => 'collaboration_response',
            'from' => $agentId,
            'response' => $response,
            'status' => 'completed',
            'timestamp' => now()->toIso8601String(),
        ];

        // Cache response
        Cache::put(
            "agent_collab_response_{$requestId}",
            $collaborationResponse,
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Collaboration response sent', [
            'request_id' => $requestId,
            'agent_id' => $agentId,
        ]);

        return [
            'request_id' => $requestId,
            'status' => 'completed',
            'responded_at' => $collaborationResponse['timestamp'],
        ];
    }

    /**
     * Create a shared context for workflow
     *
     * @param  array<string, mixed>  $initialContext
     * @return array{
     *     context_id: string,
     *     status: string,
     *     created_at: string
     * }
     */
    public function createSharedContext(string $workflowId, array $initialContext = []): array
    {
        $contextId = "context_{$workflowId}";

        $context = [
            'context_id' => $contextId,
            'workflow_id' => $workflowId,
            'data' => $initialContext,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->sharedMemory[$contextId] = $context;

        // Cache context
        Cache::put(
            "agent_context_{$contextId}",
            $context,
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Shared context created', [
            'context_id' => $contextId,
            'workflow_id' => $workflowId,
        ]);

        return [
            'context_id' => $contextId,
            'status' => 'created',
            'created_at' => $context['created_at'],
        ];
    }

    /**
     * Update shared context
     *
     * @param  array<string, mixed>  $updates
     * @return array{
     *     context_id: string,
     *     status: string,
     *     updated_at: string
     * }
     */
    public function updateSharedContext(string $contextId, array $updates): array
    {
        if (! isset($this->sharedMemory[$contextId])) {
            // Try to load from cache
            /** @var array<string, mixed>|null $cached */
            $cached = Cache::get("agent_context_{$contextId}");
            if (is_array($cached)) {
                $this->sharedMemory[$contextId] = $cached;
            } else {
                throw new \RuntimeException("Context not found: {$contextId}");
            }
        }

        // Merge updates
        /** @var array<string, mixed> $existingData */
        $existingData = is_array($this->sharedMemory[$contextId]['data'])
            ? $this->sharedMemory[$contextId]['data']
            : [];
        $this->sharedMemory[$contextId]['data'] = array_merge($existingData, $updates);
        $this->sharedMemory[$contextId]['updated_at'] = now()->toIso8601String();

        // Update cache
        Cache::put(
            "agent_context_{$contextId}",
            $this->sharedMemory[$contextId],
            $this->messageRetentionSeconds
        );

        Log::info('[AgentCommunication] Shared context updated', [
            'context_id' => $contextId,
        ]);

        return [
            'context_id' => $contextId,
            'status' => 'updated',
            'updated_at' => (string) $this->sharedMemory[$contextId]['updated_at'],
        ];
    }

    /**
     * Get shared context
     *
     * @return array<string, mixed>|null
     */
    public function getSharedContext(string $contextId): ?array
    {
        if (isset($this->sharedMemory[$contextId])) {
            /** @var array<string, mixed>|null $data */
            $data = $this->sharedMemory[$contextId]['data'] ?? null;

            return is_array($data) ? $data : null;
        }

        // Try cache
        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get("agent_context_{$contextId}");
        if (is_array($cached) && isset($cached['data']) && is_array($cached['data'])) {
            /** @var array<string, mixed> $cachedData */
            $cachedData = $cached['data'];

            return $cachedData;
        }

        return null;
    }

    /**
     * Clear message queue for an agent
     */
    public function clearMessageQueue(string $agentId): void
    {
        if (isset($this->messageQueues[$agentId])) {
            unset($this->messageQueues[$agentId]);
        }

        Log::info('[AgentCommunication] Message queue cleared', [
            'agent_id' => $agentId,
        ]);
    }

    /**
     * Clear shared memory for an agent
     */
    public function clearSharedMemory(string $agentId): void
    {
        foreach ($this->sharedMemory as $key => $data) {
            if (str_starts_with($key, "{$agentId}:")) {
                unset($this->sharedMemory[$key]);
                Cache::forget("agent_shared_{$key}");
            }
        }

        Log::info('[AgentCommunication] Shared memory cleared', [
            'agent_id' => $agentId,
        ]);
    }

    /**
     * Generate unique message ID
     */
    protected function generateMessageId(): string
    {
        return 'msg_'.\Illuminate\Support\Str::uuid()->toString();
    }

    /**
     * Get communication statistics
     *
     * @return array{
     *     total_messages: int,
     *     active_queues: int,
     *     shared_data_items: int,
     *     broadcast_messages: int
     * }
     */
    public function getStatistics(): array
    {
        $totalMessages = 0;
        foreach ($this->messageQueues as $queue) {
            $totalMessages += count($queue);
        }

        return [
            'total_messages' => $totalMessages,
            'active_queues' => count($this->messageQueues),
            'shared_data_items' => count($this->sharedMemory),
            'broadcast_messages' => count($this->messageQueues['broadcast'] ?? []),
        ];
    }
}
