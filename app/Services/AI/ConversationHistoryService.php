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
     * @param  array{
     *     character_id?: int,
     *     conversation_id?: string,
     *     provider?: string,
     *     model?: string,
     *     date_from?: string,
     *     date_to?: string,
     *     search?: string,
     *     limit?: int,
     *     offset?: int
     * }  $filters
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

        if (isset($filters['provider'])) {
            $query->where('ai_model_used', 'like', "%{$filters['provider']}%");
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

        if (isset($filters['search'])) {
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
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $conversations = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'character_id' => $conv->character_id,
                'character_name' => $conv->character?->name ?? 'Unknown',
                'message_type' => $conv->message_type,
                'message_content' => $conv->message_content,
                'ai_model_used' => $conv->ai_model_used,
                'processing_time' => $conv->processing_time,
                'token_count' => $conv->token_count,
                'cost' => $conv->cost,
                'created_at' => $conv->created_at?->toIso8601String(),
            ])
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
        $conversations = AIConversation::with('character')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'character_id' => $conv->character_id,
                'character_name' => $conv->character?->name ?? 'Unknown',
                'message_type' => $conv->message_type,
                'message_content' => $conv->message_content,
                'ai_model_used' => $conv->ai_model_used,
                'processing_time' => $conv->processing_time,
                'token_count' => $conv->token_count,
                'cost' => $conv->cost,
                'created_at' => $conv->created_at?->toIso8601String(),
            ])
            ->toArray();

        return $conversations;
    }

    /**
     * Get conversation analytics
     *
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

        $totalTokens = $conversations->sum('token_count') ?? 0;
        $totalCost = $conversations->sum('cost') ?? 0.0;

        // Group by provider
        $byProvider = $conversations
            ->filter(fn ($c) => $c->ai_model_used !== null)
            ->groupBy(function ($conv) {
                $model = $conv->ai_model_used ?? '';
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
            })
            ->map(fn ($group) => $group->count())
            ->toArray();

        // Group by model
        $byModel = $conversations
            ->filter(fn ($c) => $c->ai_model_used !== null)
            ->groupBy('ai_model_used')
            ->map(fn ($group) => $group->count())
            ->toArray();

        // Group by character
        $byCharacter = $conversations
            ->filter(fn ($c) => $c->character_id !== null)
            ->groupBy('character_id')
            ->map(fn ($group) => $group->count())
            ->toArray();

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
     * @return array{
     *     total_tool_calls: int,
     *     most_used_tools: array<int, array{tool: string, count: int, percentage: float}>,
     *     tool_success_rate: array<string, float>,
     *     avg_tools_per_conversation: float
     * }
     */
    public function getToolUsageStatistics(array $filters = []): array
    {
        // TODO: Implement tool usage tracking
        // This requires adding tool usage tracking to the conversation model

        return [
            'total_tool_calls' => 0,
            'most_used_tools' => [],
            'tool_success_rate' => [],
            'avg_tools_per_conversation' => 0.0,
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

        return json_encode($result['conversations'], JSON_PRETTY_PRINT);
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
                $conv['id'],
                $conv['conversation_id'],
                $conv['character_name'],
                $conv['message_type'],
                $conv['ai_model_used'] ?? '',
                $conv['processing_time'] ?? '',
                $conv['token_count'] ?? 0,
                $conv['cost'] ?? '',
                $conv['created_at']
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

            return $deleted;
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
