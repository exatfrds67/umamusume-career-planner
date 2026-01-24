<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Memory Service
 *
 * Manages persistent context and memory for MCP agents across sessions.
 * Provides short-term, long-term, and episodic memory capabilities.
 */
class AgentMemoryService
{
    /**
     * Memory types
     */
    public const MEMORY_SHORT_TERM = 'short_term'; // Current session

    public const MEMORY_LONG_TERM = 'long_term'; // Persistent across sessions

    public const MEMORY_EPISODIC = 'episodic'; // Specific events/interactions

    public const MEMORY_SEMANTIC = 'semantic'; // Facts and knowledge

    /**
     * Memory TTL (in seconds)
     */
    public const TTL_SHORT_TERM = 3600; // 1 hour

    public const TTL_LONG_TERM = 2592000; // 30 days

    public const TTL_EPISODIC = 604800; // 7 days

    public const TTL_SEMANTIC = 2592000; // 30 days

    /**
     * Store memory for an agent
     */
    public function storeMemory(
        string $agentId,
        string $memoryType,
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        try {
            $memoryKey = $this->buildMemoryKey($agentId, $memoryType, $key);

            // Determine TTL based on memory type
            if ($ttl === null) {
                $ttl = $this->getDefaultTTL($memoryType);
            }

            $memory = [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
                'value' => $value,
                'stored_at' => now()->toIso8601String(),
                'expires_at' => now()->addSeconds($ttl)->toIso8601String(),
            ];

            Cache::put($memoryKey, $memory, $ttl);

            // Update memory index
            $this->updateMemoryIndex($agentId, $memoryType, $key);

            Log::info('[AgentMemory] Memory stored', [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
                'ttl' => $ttl,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to store memory', [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retrieve memory for an agent
     */
    public function retrieveMemory(
        string $agentId,
        string $memoryType,
        string $key
    ): mixed {
        try {
            $memoryKey = $this->buildMemoryKey($agentId, $memoryType, $key);
            $memory = Cache::get($memoryKey);

            if (is_array($memory)) {
                Log::debug('[AgentMemory] Memory retrieved', [
                    'agent_id' => $agentId,
                    'type' => $memoryType,
                    'key' => $key,
                ]);

                return $memory['value'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to retrieve memory', [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all memories for an agent
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAgentMemories(string $agentId, ?string $memoryType = null): array
    {
        try {
            $index = $this->getMemoryIndex($agentId);
            $memories = [];

            foreach ($index as $type => $keys) {
                // Filter by memory type if specified
                if ($memoryType !== null && $type !== $memoryType) {
                    continue;
                }

                foreach ($keys as $key) {
                    $value = $this->retrieveMemory($agentId, $type, $key);
                    if ($value !== null) {
                        $memories[] = [
                            'type' => $type,
                            'key' => $key,
                            'value' => $value,
                        ];
                    }
                }
            }

            return $memories;
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to get agent memories', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Store episodic memory (specific interaction/event)
     *
     * @param  array<string, mixed>  $episodeData
     */
    public function storeEpisode(
        string $agentId,
        string $episodeId,
        array $episodeData
    ): bool {
        $episode = [
            'episode_id' => $episodeId,
            'data' => $episodeData,
            'timestamp' => now()->toIso8601String(),
        ];

        return $this->storeMemory(
            $agentId,
            self::MEMORY_EPISODIC,
            $episodeId,
            $episode
        );
    }

    /**
     * Retrieve episodic memories
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEpisodes(string $agentId, ?int $limit = null): array
    {
        $memories = $this->getAgentMemories($agentId, self::MEMORY_EPISODIC);

        // Sort by timestamp (most recent first)
        usort($memories, function (array $a, array $b): int {
            $valueA = is_array($a['value'] ?? null) ? $a['value'] : [];
            $valueB = is_array($b['value'] ?? null) ? $b['value'] : [];
            $timeA = is_string($valueA['timestamp'] ?? null) ? $valueA['timestamp'] : '';
            $timeB = is_string($valueB['timestamp'] ?? null) ? $valueB['timestamp'] : '';

            return strcmp($timeB, $timeA);
        });

        if ($limit !== null) {
            $memories = array_slice($memories, 0, $limit);
        }

        return $memories;
    }

    /**
     * Store semantic knowledge
     */
    public function storeKnowledge(
        string $agentId,
        string $topic,
        mixed $knowledge
    ): bool {
        return $this->storeMemory(
            $agentId,
            self::MEMORY_SEMANTIC,
            $topic,
            $knowledge
        );
    }

    /**
     * Retrieve semantic knowledge
     */
    public function getKnowledge(
        string $agentId,
        string $topic
    ): mixed {
        return $this->retrieveMemory(
            $agentId,
            self::MEMORY_SEMANTIC,
            $topic
        );
    }

    /**
     * Store conversation context
     *
     * @param  array<string, mixed>  $context
     */
    public function storeConversationContext(
        string $agentId,
        string $conversationId,
        array $context
    ): bool {
        return $this->storeMemory(
            $agentId,
            self::MEMORY_SHORT_TERM,
            "conversation:{$conversationId}",
            $context
        );
    }

    /**
     * Retrieve conversation context
     *
     * @return array<string, mixed>|null
     */
    public function getConversationContext(
        string $agentId,
        string $conversationId
    ): ?array {
        $context = $this->retrieveMemory(
            $agentId,
            self::MEMORY_SHORT_TERM,
            "conversation:{$conversationId}"
        );

        if (is_array($context)) {
            /** @var array<string, mixed> $context */
            return $context;
        }

        return null;
    }

    /**
     * Store agent learning/insights
     *
     * @param  array<string, mixed>  $learningData
     */
    public function storeLearning(
        string $agentId,
        string $learningKey,
        array $learningData
    ): bool {
        return $this->storeMemory(
            $agentId,
            self::MEMORY_LONG_TERM,
            "learning:{$learningKey}",
            $learningData
        );
    }

    /**
     * Retrieve agent learnings
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLearnings(?string $agentId = null): array
    {
        if ($agentId === null) {
            return [];
        }

        $memories = $this->getAgentMemories($agentId, self::MEMORY_LONG_TERM);

        return array_filter($memories, function (array $memory): bool {
            $key = isset($memory['key']) && is_string($memory['key']) ? $memory['key'] : '';

            return str_starts_with($key, 'learning:');
        });
    }

    /**
     * Clear specific memory
     */
    public function clearMemory(
        string $agentId,
        string $memoryType,
        string $key
    ): bool {
        try {
            $memoryKey = $this->buildMemoryKey($agentId, $memoryType, $key);
            Cache::forget($memoryKey);

            // Update memory index
            $this->removeFromMemoryIndex($agentId, $memoryType, $key);

            Log::info('[AgentMemory] Memory cleared', [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to clear memory', [
                'agent_id' => $agentId,
                'type' => $memoryType,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear all memories for an agent
     */
    public function clearAgentMemories(
        string $agentId,
        ?string $memoryType = null
    ): bool {
        try {
            $index = $this->getMemoryIndex($agentId);

            foreach ($index as $type => $keys) {
                // Filter by memory type if specified
                if ($memoryType !== null && $type !== $memoryType) {
                    continue;
                }

                foreach ($keys as $key) {
                    $this->clearMemory($agentId, $type, $key);
                }
            }

            // Clear the index if clearing all memories
            if ($memoryType === null) {
                Cache::forget("agent_memory_index:{$agentId}");
            }

            Log::info('[AgentMemory] Agent memories cleared', [
                'agent_id' => $agentId,
                'type' => $memoryType ?? 'all',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to clear agent memories', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get memory statistics for an agent
     *
     * @return array<string, mixed>
     */
    public function getMemoryStatistics(string $agentId): array
    {
        $index = $this->getMemoryIndex($agentId);
        $stats = [
            'agent_id' => $agentId,
            'total_memories' => 0,
            'by_type' => [],
        ];

        foreach ($index as $type => $keys) {
            $count = count($keys);
            $stats['by_type'][$type] = $count;
            $stats['total_memories'] += $count;
        }

        return $stats;
    }

    /**
     * Consolidate short-term memories to long-term
     *
     * @return array<string, mixed>
     */
    public function consolidateMemories(string $agentId): array
    {
        try {
            $shortTermMemories = $this->getAgentMemories($agentId, self::MEMORY_SHORT_TERM);
            $consolidated = [];

            foreach ($shortTermMemories as $memory) {
                // Determine if memory should be consolidated
                if ($this->shouldConsolidate($memory)) {
                    $memoryKey = isset($memory['key']) && is_string($memory['key']) ? $memory['key'] : '';
                    $longTermKey = "consolidated:{$memoryKey}";

                    $this->storeMemory(
                        $agentId,
                        self::MEMORY_LONG_TERM,
                        $longTermKey,
                        $memory['value'] ?? null
                    );

                    $consolidated[] = $longTermKey;

                    // Optionally clear short-term memory
                    $this->clearMemory($agentId, self::MEMORY_SHORT_TERM, $memoryKey);
                }
            }

            Log::info('[AgentMemory] Memories consolidated', [
                'agent_id' => $agentId,
                'consolidated_count' => count($consolidated),
            ]);

            return [
                'agent_id' => $agentId,
                'consolidated_count' => count($consolidated),
                'consolidated_keys' => $consolidated,
            ];
        } catch (\Exception $e) {
            Log::error('[AgentMemory] Failed to consolidate memories', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Protected helper methods
     */
    protected function buildMemoryKey(string $agentId, string $memoryType, string $key): string
    {
        return "agent_memory:{$agentId}:{$memoryType}:{$key}";
    }

    protected function getDefaultTTL(string $memoryType): int
    {
        return match ($memoryType) {
            self::MEMORY_SHORT_TERM => self::TTL_SHORT_TERM,
            self::MEMORY_LONG_TERM => self::TTL_LONG_TERM,
            self::MEMORY_EPISODIC => self::TTL_EPISODIC,
            self::MEMORY_SEMANTIC => self::TTL_SEMANTIC,
            default => self::TTL_SHORT_TERM,
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getMemoryIndex(string $agentId): array
    {
        $result = Cache::get("agent_memory_index:{$agentId}");

        if (is_array($result)) {
            /** @var array<string, array<int, string>> $result */
            return $result;
        }

        return [];
    }

    protected function updateMemoryIndex(string $agentId, string $memoryType, string $key): void
    {
        $index = $this->getMemoryIndex($agentId);

        if (! isset($index[$memoryType])) {
            $index[$memoryType] = [];
        }

        if (! in_array($key, $index[$memoryType])) {
            $index[$memoryType][] = $key;
        }

        Cache::put("agent_memory_index:{$agentId}", $index, self::TTL_LONG_TERM);
    }

    protected function removeFromMemoryIndex(string $agentId, string $memoryType, string $key): void
    {
        $index = $this->getMemoryIndex($agentId);

        if (isset($index[$memoryType])) {
            $index[$memoryType] = array_filter(
                $index[$memoryType],
                fn ($k) => $k !== $key
            );

            if (empty($index[$memoryType])) {
                unset($index[$memoryType]);
            }

            Cache::put("agent_memory_index:{$agentId}", $index, self::TTL_LONG_TERM);
        }
    }

    /**
     * @param  array<string, mixed>  $memory
     */
    protected function shouldConsolidate(array $memory): bool
    {
        // Consolidate if memory is important or frequently accessed
        // This is a simple heuristic - can be enhanced with ML
        $key = isset($memory['key']) && is_string($memory['key']) ? $memory['key'] : '';

        return str_contains($key, 'important')
            || str_contains($key, 'learning')
            || str_contains($key, 'insight');
    }
}
