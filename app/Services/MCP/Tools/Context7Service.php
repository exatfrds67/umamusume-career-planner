<?php

namespace App\Services\MCP\Tools;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Context7 Service via MCP
 *
 * Integrates with context7 MCP server for advanced context management,
 * persistent conversation context, and cross-agent context sharing.
 *
 * Requirements: 13.4, 56.2, 14.1
 */
class Context7Service
{
    protected MCPClientService $mcpClient;

    protected bool $enabled;

    protected int $cacheTTL;

    protected string $serverName = 'context7';

    protected int $maxContextSize;

    protected int $contextRetentionDays;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('mcp.tools.context7.enabled', true);
        $configTTL = Config::get('mcp.tools.context7.cache_ttl', 600);
        $this->cacheTTL = is_numeric($configTTL) ? (int) $configTTL : 600;
        $configMaxSize = Config::get('mcp.tools.context7.max_context_size', 10000);
        $this->maxContextSize = is_numeric($configMaxSize) ? (int) $configMaxSize : 10000;
        $configRetention = Config::get('mcp.tools.context7.retention_days', 30);
        $this->contextRetentionDays = is_numeric($configRetention) ? (int) $configRetention : 30;
    }

    /**
     * Check if Context7 service is available
     */
    public function isAvailable(): bool
    {
        return $this->enabled &&
            $this->mcpClient->isServerEnabled($this->serverName) &&
            $this->mcpClient->isServerHealthy($this->serverName);
    }

    /**
     * Store conversation context
     *
     * @param  array<string, mixed>  $context
     * @return array{context_id: string, stored: bool, size: int, expires_at: int}
     */
    public function storeContext(string $conversationId, array $context, ?int $ttl = null): array
    {
        $ttl = $ttl ?? ($this->contextRetentionDays * 86400);

        try {
            $contextId = $this->generateContextId($conversationId);
            $contextData = $this->prepareContextData($context);

            // Store in cache
            Cache::put($contextId, $contextData, $ttl);

            if ($this->isAvailable()) {
                // Store in MCP context7 server for persistent storage
                try {
                    $this->mcpClient->callTool($this->serverName, 'store_context', [
                        'context_id' => $contextId,
                        'conversation_id' => $conversationId,
                        'context_data' => $contextData,
                        'ttl' => $ttl,
                    ]);

                    $this->debugLog('Context stored in MCP server', [
                        'context_id' => $contextId,
                        'conversation_id' => $conversationId,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('[Context7] Failed to store in MCP server, using cache only', [
                        'error' => $e->getMessage(),
                        'context_id' => $contextId,
                    ]);
                }
            }

            return [
                'context_id' => $contextId,
                'stored' => true,
                'size' => \strlen(json_encode($contextData) ?: '{}'),
                'expires_at' => time() + $ttl,
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to store context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return [
                'context_id' => '',
                'stored' => false,
                'size' => 0,
                'expires_at' => 0,
            ];
        }
    }

    /**
     * Retrieve conversation context
     *
     * @return array{context: array<string, mixed>, found: bool, age: int, source: string}
     */
    public function retrieveContext(string $conversationId): array
    {
        try {
            $contextId = $this->generateContextId($conversationId);

            // Try cache first
            $context = Cache::get($contextId);

            if ($context !== null) {
                return [
                    'context' => \is_array($context) ? $context : [],
                    'found' => true,
                    'age' => 0, // Cache doesn't track age
                    'source' => 'cache',
                ];
            }

            // Try MCP server if available
            if ($this->isAvailable()) {
                // Retrieve from MCP context7 server
                try {
                    $result = $this->mcpClient->callTool($this->serverName, 'retrieve_context', [
                        'context_id' => $contextId,
                        'conversation_id' => $conversationId,
                    ]);

                    if (isset($result['context']) && is_array($result['context'])) {
                        // Cache the retrieved context
                        Cache::put($contextId, $result['context'], $this->cacheTTL);

                        $this->debugLog('Context retrieved from MCP server', [
                            'context_id' => $contextId,
                            'conversation_id' => $conversationId,
                        ]);

                        // Ensure proper typing for context array
                        $contextData = [];
                        foreach ($result['context'] as $key => $value) {
                            $contextData[(string) $key] = $value;
                        }

                        return [
                            'context' => $contextData,
                            'found' => true,
                            'age' => isset($result['age']) && is_numeric($result['age']) ? (int) $result['age'] : 0,
                            'source' => 'mcp_server',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('[Context7] Failed to retrieve from MCP server', [
                        'error' => $e->getMessage(),
                        'context_id' => $contextId,
                    ]);
                }
            }

            return [
                'context' => [],
                'found' => false,
                'age' => 0,
                'source' => 'none',
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to retrieve context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return [
                'context' => [],
                'found' => false,
                'age' => 0,
                'source' => 'error',
            ];
        }
    }

    /**
     * Update existing context
     *
     * @param  array<string, mixed>  $updates
     * @return array{updated: bool, context_id: string, size: int}
     */
    public function updateContext(string $conversationId, array $updates, bool $merge = true): array
    {
        try {
            $contextId = $this->generateContextId($conversationId);

            // Get existing context
            $existing = $this->retrieveContext($conversationId);

            // Merge or replace
            $newContext = $merge
                ? [...$existing['context'], ...$updates]
                : $updates;

            // Store updated context
            $result = $this->storeContext($conversationId, $newContext);

            return [
                'updated' => $result['stored'],
                'context_id' => $result['context_id'],
                'size' => $result['size'],
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to update context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return [
                'updated' => false,
                'context_id' => '',
                'size' => 0,
            ];
        }
    }

    /**
     * Share context across agents
     *
     * @param  array<int, string>  $agentIds
     * @return array{shared: bool, agent_count: int, context_id: string}
     */
    public function shareContextAcrossAgents(string $conversationId, array $agentIds): array
    {
        try {
            $context = $this->retrieveContext($conversationId);

            if (! $context['found']) {
                return [
                    'shared' => false,
                    'agent_count' => 0,
                    'context_id' => '',
                ];
            }

            // Store context for each agent
            $sharedCount = 0;
            foreach ($agentIds as $agentId) {
                $agentContextId = $this->generateContextId("{$conversationId}_agent_{$agentId}");
                Cache::put($agentContextId, $context['context'], $this->cacheTTL);
                $sharedCount++;
            }

            if ($this->isAvailable()) {
                // Share via MCP context7 server
                try {
                    $this->mcpClient->callTool($this->serverName, 'share_context', [
                        'conversation_id' => $conversationId,
                        'agent_ids' => $agentIds,
                        'context_data' => $context['context'],
                    ]);

                    $this->debugLog('Context shared across agents via MCP', [
                        'conversation_id' => $conversationId,
                        'agent_count' => $sharedCount,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('[Context7] Failed to share via MCP server, using cache only', [
                        'error' => $e->getMessage(),
                        'conversation_id' => $conversationId,
                    ]);
                }
            }

            return [
                'shared' => true,
                'agent_count' => $sharedCount,
                'context_id' => $this->generateContextId($conversationId),
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to share context across agents', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
                'agent_ids' => $agentIds,
            ]);

            return [
                'shared' => false,
                'agent_count' => 0,
                'context_id' => '',
            ];
        }
    }

    /**
     * Analyze context for optimization
     *
     * @return array{size: int, token_estimate: int, optimization_suggestions: array<int, string>, can_compress: bool, estimated_savings: int}
     */
    public function analyzeContext(string $conversationId): array
    {
        try {
            $context = $this->retrieveContext($conversationId);

            if (! $context['found']) {
                return [
                    'size' => 0,
                    'token_estimate' => 0,
                    'optimization_suggestions' => [],
                    'can_compress' => false,
                    'estimated_savings' => 0,
                ];
            }

            $contextJson = json_encode($context['context']) ?: '{}';
            $size = \strlen($contextJson);
            $tokenEstimate = (int) ($size / 4); // Rough estimate

            $suggestions = [];
            $canCompress = false;
            $estimatedSavings = 0;

            // Check if context is too large
            if ($size > $this->maxContextSize) {
                $suggestions[] = 'Context exceeds maximum size, consider pruning old messages';
                $canCompress = true;
                $estimatedSavings = $size - $this->maxContextSize;
            }

            // Check for redundant data
            if ($this->hasRedundantData($context['context'])) {
                $suggestions[] = 'Detected redundant data, consider deduplication';
                $canCompress = true;
                $estimatedSavings += (int) ($size * 0.2);
            }

            // Check for old data
            if ($this->hasOldData($context['context'])) {
                $suggestions[] = 'Context contains old data, consider archiving';
                $canCompress = true;
                $estimatedSavings += (int) ($size * 0.3);
            }

            return [
                'size' => $size,
                'token_estimate' => $tokenEstimate,
                'optimization_suggestions' => $suggestions,
                'can_compress' => $canCompress,
                'estimated_savings' => $estimatedSavings,
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to analyze context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return [
                'size' => 0,
                'token_estimate' => 0,
                'optimization_suggestions' => [],
                'can_compress' => false,
                'estimated_savings' => 0,
            ];
        }
    }

    /**
     * Compress context by removing old or redundant data
     *
     * @return array{compressed: bool, original_size: int, new_size: int, savings: int}
     */
    public function compressContext(string $conversationId): array
    {
        try {
            $context = $this->retrieveContext($conversationId);

            if (! $context['found']) {
                return [
                    'compressed' => false,
                    'original_size' => 0,
                    'new_size' => 0,
                    'savings' => 0,
                ];
            }

            $originalSize = \strlen(json_encode($context['context']) ?: '{}');

            // Compress context
            $compressed = $this->performCompression($context['context']);

            // Store compressed context
            $this->storeContext($conversationId, $compressed);

            $newSize = \strlen(json_encode($compressed) ?: '{}');
            $savings = $originalSize - $newSize;

            return [
                'compressed' => true,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'savings' => $savings,
            ];
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to compress context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return [
                'compressed' => false,
                'original_size' => 0,
                'new_size' => 0,
                'savings' => 0,
            ];
        }
    }

    /**
     * Delete context
     */
    public function deleteContext(string $conversationId): bool
    {
        try {
            $contextId = $this->generateContextId($conversationId);
            Cache::forget($contextId);

            if ($this->isAvailable()) {
                // Delete from MCP context7 server
                try {
                    $this->mcpClient->callTool($this->serverName, 'delete_context', [
                        'context_id' => $contextId,
                        'conversation_id' => $conversationId,
                    ]);

                    $this->debugLog('Context deleted from MCP server', [
                        'context_id' => $contextId,
                        'conversation_id' => $conversationId,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('[Context7] Failed to delete from MCP server, cache cleared', [
                        'error' => $e->getMessage(),
                        'context_id' => $contextId,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[Context7] Failed to delete context', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return false;
        }
    }

    /**
     * Generate context ID
     */
    protected function generateContextId(string $conversationId): string
    {
        return "context7_{$conversationId}";
    }

    /**
     * Prepare context data for storage
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function prepareContextData(array $context): array
    {
        return [
            'data' => $context,
            'timestamp' => time(),
            'version' => '1.0',
        ];
    }

    /**
     * Check if context has redundant data
     *
     * @param  array<string, mixed>  $context
     */
    protected function hasRedundantData(array $context): bool
    {
        // Simple check: if context has duplicate keys or values
        $jsonContext = json_encode($context) ?: '{}';
        $parts = explode(',', $jsonContext);

        return \count($parts) > \count(array_unique($parts));
    }

    /**
     * Check if context has old data
     *
     * @param  array<string, mixed>  $context
     */
    protected function hasOldData(array $context): bool
    {
        if (isset($context['timestamp']) && is_numeric($context['timestamp'])) {
            $age = time() - (int) $context['timestamp'];

            return $age > ($this->contextRetentionDays * 86400);
        }

        return false;
    }

    /**
     * Perform context compression
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function performCompression(array $context): array
    {
        // Remove old messages (keep last 50)
        if (isset($context['messages']) && \is_array($context['messages'])) {
            $context['messages'] = \array_slice($context['messages'], -50);
        }

        // Remove redundant metadata
        unset($context['debug'], $context['internal']);

        return $context;
    }

    /**
     * Debug logging
     *
     * @param  array<string, mixed>  $context
     */
    protected function debugLog(string $message, array $context = []): void
    {
        if (Config::get('mcp.debug', false)) {
            Log::debug("[Context7] {$message}", $context);
        }
    }

    /**
     * Get service status
     *
     * @return array{
     *     enabled: bool,
     *     available: bool,
     *     server_healthy: bool,
     *     cache_ttl: int,
     *     max_context_size: int,
     *     retention_days: int
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'server_healthy' => $this->mcpClient->isServerHealthy($this->serverName),
            'cache_ttl' => $this->cacheTTL,
            'max_context_size' => $this->maxContextSize,
            'retention_days' => $this->contextRetentionDays,
        ];
    }
}
