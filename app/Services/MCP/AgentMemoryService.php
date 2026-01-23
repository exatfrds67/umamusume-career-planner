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

            if ($memory) {
                Log::debug('[AgentMemory] Memory retrieved', [
                    'agent_id' => $agentId,
                    'type' => $memoryType,
                    'key' => $key,
                ]);

                return $memory['value'];
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
     */
    public function getAgentMemories(): array
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
     */
    public function getEpisodes(): array
        $memories = $this->getAgentMemories($agentId, self::MEMORY_EPISODIC);

        // Sort by timestamp (most recent first)
        usort($memories, function ($a, $b) {
            $timeA = $a['value']['timestamp'] ?? '';
            $timeB = $b['value']['timestamp'] ?? '';

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

        return is_array($context) ? $context : null;
    }

    /**
     * Store agent learning/insights
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
     */
    public function getLearnings(): array
        $memories = $this->getAgentMemories($agentId, self::MEMORY_LONG_TERM);

        return array_filter($memories, function ($memory) {
            return str_starts_with($memory['key'], 'learning:');
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
     */
    public function getMemoryStatistics(): array
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
     */
    public function consolidateMemories(): array
        try {
            $shortTermMemories = $this->getAgentMemories($agentId, self::MEMORY_SHORT_TERM);
            $consolidated = [];

            foreach ($shortTermMemories as $memory) {
                // Determine if memory should be consolidated
                if ($this->shouldConsolidate($memory)) {
                    $longTermKey = "consolidated:{$memory['key']}";

                    $this->storeMemory(
                        $agentId,
                        self::MEMORY_LONG_TERM,
                        $longTermKey,
                        $memory['value']
                    );

                    $consolidated[] = $longTermKey;

                    // Optionally clear short-term memory
                    $this->clearMemory($agentId, self::MEMORY_SHORT_TERM, $memory['key']);
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

    protected function getMemoryIndex(): array
        return Cache::get("agent_memory_index:{$agentId}", []);
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

    protected function shouldConsolidate(array $memory): bool
    {
        // Consolidate if memory is important or frequently accessed
        // This is a simple heuristic - can be enhanced with ML
        return str_contains($memory['key'], 'important')
            || str_contains($memory['key'], 'learning')
            || str_contains($memory['key'], 'insight');
    }
}
