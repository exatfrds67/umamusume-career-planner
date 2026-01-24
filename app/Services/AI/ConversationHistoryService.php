<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use Illuminate\Support\Facades\Log;

/**
 * Conversation History Service
 *
 * Manages conversation storage, retrieval, and analytics with MCP tool usage tracking.
 * Provides search, filtering, and export capabilities for conversation history.
 *
 * Requirements: 56.4, 13.5
 */
class ConversationHistoryService
{
    /**
     * Get conversation history with filters
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     conversations: array<int, array<string, mixed>>,
     *     total: int,
     *     filtered: int,
     *     limit: int,
     *     offset: int
     * }
     */
    public function getConversations(array $filters = []): array
    {
        $query = AIConversation::with('character');

        // Apply filters
        if (isset($filters['character_id'])) {
            $query->where('character_id', $filters['character_id']);
        }

        if (isset($filters['conversation_id'])) {
            $query->where('conversation_id', $filters['conversation_id']);
        }

        if (isset($filters['provider']) && is_string($filters['provider'])) {
            $provider = $filters['provider'];
            $query->where('ai_model_used', 'like', "%{$provider}%");
        }

        if (isset($filters['model'])) {
            $query->where('ai_model_used', $filters['model']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('message_content', 'like', "%{$search}%")
                    ->orWhere('conversation_id', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $total = AIConversation::count();
        $filtered = $query->count();

        // Apply pagination
        $limit = isset($filters['limit']) && is_numeric($filters['limit']) ? (int) $filters['limit'] : 50;
        $offset = isset($filters['offset']) && is_numeric($filters['offset']) ? (int) $filters['offset'] : 0;

        /** @var array<int, array<string, mixed>> $conversations */
        $conversations = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'character_id' => $conv->character_id,
                'character_name' => $conv->character->name ?? 'Unknown',
                'message_type' => $conv->message_type,
                'message_content' => $conv->getAttribute('message_content'),
                'ai_model_used' => $conv->getAttribute('ai_model_used'),
                'processing_time' => $conv->getAttribute('processing_time'),
                'token_count' => $conv->getAttribute('token_count'),
                'cost' => $conv->getAttribute('cost'),
                'created_at' => $conv->created_at?->toIso8601String(),
            ])
            ->values()
            ->toArray();

        return [
            'conversations' => $conversations,
            'total' => $total,
            'filtered' => $filtered,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Get conversation by ID
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConversationById(string $conversationId): array
    {
        /** @var array<int, array<string, mixed>> $conversations */
        $conversations = AIConversation::with('character')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'character_id' => $conv->character_id,
                'character_name' => $conv->character->name ?? 'Unknown',
                'message_type' => $conv->message_type,
                'message_content' => $conv->getAttribute('message_content'),
                'ai_model_used' => $conv->getAttribute('ai_model_used'),
                'processing_time' => $conv->getAttribute('processing_time'),
                'token_count' => $conv->getAttribute('token_count'),
                'cost' => $conv->getAttribute('cost'),
                'created_at' => $conv->created_at?->toIso8601String(),
            ])
            ->values()
            ->toArray();

        return $conversations;
    }

    /**
     * Get conversation analytics
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     total_conversations: int,
     *     total_messages: int,
     *     avg_conversation_length: float,
     *     total_tokens: int,
     *     total_cost: float,
     *     by_provider: array<string, int>,
     *     by_model: array<string, int>,
     *     by_character: array<string, int>
     * }
     */
    public function getConversationAnalytics(array $filters = []): array
    {
        $query = AIConversation::query();

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $conversations = $query->get();

        $totalConversations = $conversations->unique('conversation_id')->count();
        $totalMessages = $conversations->count();
        $avgLength = $totalConversations > 0 ? $totalMessages / $totalConversations : 0;

        $tokenSum = $conversations->sum('token_count');
        $costSum = $conversations->sum('cost');
        $totalTokens = is_numeric($tokenSum) ? (int) $tokenSum : 0;
        $totalCost = is_numeric($costSum) ? (float) $costSum : 0.0;

        // Group by provider
        $byProvider = [];
        $groupedByProvider = $conversations
            ->filter(fn ($c) => $c->getAttribute('ai_model_used') !== null)
            ->groupBy(function ($conv) {
                $model = $conv->getAttribute('ai_model_used');
                $model = is_string($model) ? $model : '';
                if (str_contains($model, 'llama') || str_contains($model, 'mistral') || str_contains($model, 'qwen')) {
                    return 'ollama';
                }
                if (str_contains($model, 'claude') || str_contains($model, 'nova')) {
                    return 'bedrock';
                }
                if (str_contains($model, 'strands')) {
                    return 'mcp-strands';
                }
                if (str_contains($model, 'agentcore')) {
                    return 'mcp-agentcore';
                }

                return 'unknown';
            });
        foreach ($groupedByProvider as $provider => $group) {
            $byProvider[(string) $provider] = $group->count();
        }

        // Group by model
        $byModel = [];
        $groupedByModel = $conversations
            ->filter(fn ($c) => $c->getAttribute('ai_model_used') !== null)
            ->groupBy('ai_model_used');
        foreach ($groupedByModel as $model => $group) {
            $byModel[(string) $model] = $group->count();
        }

        // Group by character
        $byCharacter = [];
        $groupedByCharacter = $conversations
            ->filter(fn ($c) => $c->character_id !== null)
            ->groupBy('character_id');
        foreach ($groupedByCharacter as $characterId => $group) {
            $byCharacter[(string) $characterId] = $group->count();
        }

        return [
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
            'avg_conversation_length' => round($avgLength, 2),
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 6),
            'by_provider' => $byProvider,
            'by_model' => $byModel,
            'by_character' => $byCharacter,
        ];
    }

    /**
     * Get tool usage statistics
     *
     * Retrieves tool usage statistics from the MCP tool usage tracking table.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     total_tool_calls: int,
     *     most_used_tools: array<int, array{tool: string, count: int, percentage: float}>,
     *     tool_success_rate: array<string, float>,
     *     avg_tools_per_conversation: float
     * }
     */
    public function getToolUsageStatistics(array $filters = []): array
    {
        $query = \DB::table('ucp_mcp_tool_usage');

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Get total tool calls
        $totalToolCalls = $query->count();

        // Get most used tools
        $toolCounts = \DB::table('ucp_mcp_tool_usage')
            ->select('tool_name', \DB::raw('COUNT(*) as count'))
            ->groupBy('tool_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        /** @var array<int, array{tool: string, count: int, percentage: float}> $mostUsedTools */
        $mostUsedTools = [];
        foreach ($toolCounts as $row) {
            $count = is_numeric($row->count) ? (int) $row->count : 0;
            $mostUsedTools[] = [
                'tool' => is_string($row->tool_name) ? $row->tool_name : 'unknown',
                'count' => $count,
                'percentage' => $totalToolCalls > 0 ? round(($count / $totalToolCalls) * 100, 2) : 0.0,
            ];
        }

        // Get tool success rates
        $successRatesData = \DB::table('ucp_mcp_tool_usage')
            ->select(
                'tool_name',
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful')
            )
            ->groupBy('tool_name')
            ->get();

        /** @var array<string, float> $successRates */
        $successRates = [];
        foreach ($successRatesData as $row) {
            $total = is_numeric($row->total) ? (int) $row->total : 0;
            $successful = is_numeric($row->successful) ? (int) $row->successful : 0;
            $rate = $total > 0 ? ($successful / $total) * 100 : 0.0;
            $toolName = is_string($row->tool_name) ? $row->tool_name : 'unknown';
            $successRates[$toolName] = round($rate, 2);
        }

        // Calculate average tools per conversation
        $conversationCount = AIConversation::distinct('conversation_id')->count('conversation_id');
        $avgToolsPerConversation = $conversationCount > 0 ? round($totalToolCalls / $conversationCount, 2) : 0.0;

        return [
            'total_tool_calls' => $totalToolCalls,
            'most_used_tools' => $mostUsedTools,
            'tool_success_rate' => $successRates,
            'avg_tools_per_conversation' => $avgToolsPerConversation,
        ];
    }

    /**
     * Export conversations to JSON
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportToJson(array $filters = []): string
    {
        $result = $this->getConversations($filters);

        return json_encode($result['conversations'], JSON_PRETTY_PRINT) ?: '[]';
    }

    /**
     * Export conversations to CSV
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportToCsv(array $filters = []): string
    {
        $result = $this->getConversations($filters);
        $conversations = $result['conversations'];

        if (empty($conversations)) {
            return '';
        }

        // CSV header
        $csv = "ID,Conversation ID,Character,Message Type,Model,Processing Time,Tokens,Cost,Created At\n";

        // CSV rows
        foreach ($conversations as $conv) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%d,%s,%s\n",
                is_numeric($conv['id']) ? (int) $conv['id'] : 0,
                is_string($conv['conversation_id']) ? $conv['conversation_id'] : '',
                is_string($conv['character_name']) ? $conv['character_name'] : '',
                is_string($conv['message_type']) ? $conv['message_type'] : '',
                is_string($conv['ai_model_used'] ?? '') ? ($conv['ai_model_used'] ?? '') : '',
                is_scalar($conv['processing_time'] ?? '') ? (string) ($conv['processing_time'] ?? '') : '',
                is_numeric($conv['token_count'] ?? 0) ? (int) ($conv['token_count'] ?? 0) : 0,
                is_scalar($conv['cost'] ?? '') ? (string) ($conv['cost'] ?? '') : '',
                is_string($conv['created_at'] ?? '') ? ($conv['created_at'] ?? '') : ''
            );
        }

        return $csv;
    }

    /**
     * Delete old conversations
     */
    public function deleteOldConversations(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        try {
            $deleted = AIConversation::where('created_at', '<', $cutoffDate)->delete();

            Log::info('[ConversationHistory] Deleted old conversations', [
                'deleted_count' => $deleted,
                'cutoff_date' => $cutoffDate->toDateString(),
            ]);

            return is_int($deleted) ? $deleted : 0;
        } catch (\Exception $e) {
            Log::error('[ConversationHistory] Failed to delete old conversations', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get conversation statistics for dashboard
     *
     * @return array{
     *     today: int,
     *     this_week: int,
     *     this_month: int,
     *     total: int
     * }
     */
    public function getConversationStats(): array
    {
        return [
            'today' => AIConversation::whereDate('created_at', today())->distinct('conversation_id')->count('conversation_id'),
            'this_week' => AIConversation::where('created_at', '>=', now()->startOfWeek())->distinct('conversation_id')->count('conversation_id'),
            'this_month' => AIConversation::where('created_at', '>=', now()->startOfMonth())->distinct('conversation_id')->count('conversation_id'),
            'total' => AIConversation::distinct('conversation_id')->count('conversation_id'),
        ];
    }
}
