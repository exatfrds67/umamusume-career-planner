<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Conversation Management Service
 *
 * Manages multi-agent conversation history with agent attribution,
 * tool usage tracking, conversation branching, and analytics.
 *
 * Requirements: 13.4, 56.4
 */
class ConversationManagementService
{
    /**
     * Create a new conversation
     *
     * @param  array<string, mixed>  $contextEntities
     * @param  array<string, mixed>  $aiConfiguration
     */
    public function createConversation(
        User $user,
        string $conversationType,
        ?string $title = null,
        array $contextEntities = [],
        array $aiConfiguration = []
    ): AIConversation {
        try {
            $conversation = AIConversation::create([
                'user_id' => $user->id,
                'conversation_id' => Str::uuid()->toString(),
                'conversation_type' => $conversationType,
                'conversation_title' => $title ?? $this->generateConversationTitle($conversationType),
                'context_entities' => $contextEntities,
                'status' => 'active',
                'message_count' => 0,
                'started_at' => now(),
                'last_activity_at' => now(),
                'ai_model' => $aiConfiguration['model'] ?? 'default',
                'ai_version' => $aiConfiguration['version'] ?? '1.0',
                'ai_configuration' => $aiConfiguration,
            ]);

            Log::info('[ConversationManagement] Conversation created', [
                'conversation_id' => $conversation->conversation_id,
                'user_id' => $user->id,
                'type' => $conversationType,
            ]);

            return $conversation;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to create conversation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Add a message to a conversation
     *
     * @param  array<int, mixed>  $toolsUsed
     * @param  array<string, mixed>  $toolResults
     * @param  array<string, mixed>  $metadata
     */
    public function addMessage(
        AIConversation $conversation,
        string $messageType,
        string $messageContent,
        ?string $agentId = null,
        ?string $agentType = null,
        ?string $agentName = null,
        array $toolsUsed = [],
        array $toolResults = [],
        array $metadata = []
    ): ConversationMessage {
        try {
            $message = DB::transaction(function () use (
                $conversation,
                $messageType,
                $messageContent,
                $agentId,
                $agentType,
                $agentName,
                $toolsUsed,
                $toolResults,
                $metadata
            ) {
                $message = ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $conversation->user_id,
                    'message_type' => $messageType,
                    'message_content' => $messageContent,
                    'message_metadata' => $metadata,
                    'agent_id' => $agentId,
                    'agent_type' => $agentType,
                    'agent_name' => $agentName,
                    'tools_used' => $toolsUsed,
                    'tool_results' => $toolResults,
                    'tool_call_count' => \count($toolsUsed),
                    'ai_model_used' => $metadata['ai_model'] ?? null,
                    'processing_time' => $metadata['processing_time'] ?? null,
                    'tokens_used' => $metadata['tokens_used'] ?? null,
                    'cost_estimate' => $metadata['cost_estimate'] ?? null,
                    'sent_at' => now(),
                    'status' => 'completed',
                ]);

                // Update conversation
                $conversation->increment('message_count');
                $conversation->last_activity_at = now();
                $conversation->save();

                return $message;
            });

            Log::info('[ConversationManagement] Message added', [
                'conversation_id' => $conversation->conversation_id,
                'message_id' => $message->id,
                'type' => $messageType,
                'agent' => $agentId,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to add message', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a conversation branch
     *
     * @param  array<int, mixed>  $alternatives
     * @return array<string, mixed>
     */
    public function createBranch(
        ConversationMessage $parentMessage,
        string $branchReason,
        array $alternatives = []
    ): array {
        try {
            $branchId = Str::uuid()->toString();

            // Mark parent as branch point
            $parentMessage->is_branch_point = true;
            $parentMessage->branch_metadata = [
                'reason' => $branchReason,
                'alternatives' => $alternatives,
                'created_at' => now()->toIso8601String(),
            ];
            $parentMessage->save();

            Log::info('[ConversationManagement] Branch created', [
                'parent_message_id' => $parentMessage->id,
                'branch_id' => $branchId,
                'reason' => $branchReason,
            ]);

            return [
                'branch_id' => $branchId,
                'parent_message_id' => $parentMessage->id,
                'reason' => $branchReason,
                'alternatives' => $alternatives,
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to create branch', [
                'parent_message_id' => $parentMessage->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Add a message to a branch
     *
     * @param  array<string, mixed>  $metadata
     */
    public function addBranchMessage(
        AIConversation $conversation,
        string $branchId,
        int $parentMessageId,
        string $messageType,
        string $messageContent,
        array $metadata = []
    ): ConversationMessage {
        try {
            $parentMessage = ConversationMessage::findOrFail($parentMessageId);

            $message = ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id,
                'message_type' => $messageType,
                'message_content' => $messageContent,
                'message_metadata' => $metadata,
                'parent_message_id' => $parentMessageId,
                'branch_id' => $branchId,
                'branch_depth' => $parentMessage->branch_depth + 1,
                'sent_at' => now(),
                'status' => 'completed',
            ]);

            Log::info('[ConversationManagement] Branch message added', [
                'conversation_id' => $conversation->conversation_id,
                'branch_id' => $branchId,
                'message_id' => $message->id,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to add branch message', [
                'conversation_id' => $conversation->conversation_id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get conversation history with agent attribution
     *
     * @return Collection<int, array{id: int, type: string, content: string, sent_at: string|null, agent: array{id: string|null, type: string|null, name: string|null}, quality: array{rating: int|null, is_helpful: bool|null, feedback: string|null}, branching: array{is_branch_point: bool, branch_id: string|null, has_branches: bool}, tools?: array{used: array<string, mixed>, results: array<string, mixed>|null, count: int}}>
     */
    public function getConversationHistory(
        AIConversation $conversation,
        bool $includeToolUsage = true,
        ?string $branchId = null
    ): Collection {
        try {
            $query = $conversation->messages()
                ->with(['parentMessage', 'childMessages'])
                ->where('is_visible', true)
                ->orderBy('created_at', 'asc');

            if ($branchId !== null) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->orWhereNull('branch_id');
                });
            } else {
                // Only main conversation (no branches)
                $query->whereNull('branch_id');
            }

            /** @var \Illuminate\Database\Eloquent\Collection<int, ConversationMessage> $messages */
            $messages = $query->get();

            // Format messages with agent attribution
            return $messages->map(function (ConversationMessage $message) use ($includeToolUsage) {
                $formatted = [
                    'id' => $message->id,
                    'type' => $message->message_type,
                    'content' => $message->message_content ?? '',
                    'sent_at' => $message->sent_at?->toIso8601String(),
                    'agent' => [
                        'id' => $message->agent_id,
                        'type' => $message->agent_type,
                        'name' => $message->agent_name,
                    ],
                    'quality' => [
                        'rating' => $message->quality_rating,
                        'is_helpful' => $message->is_helpful,
                        'feedback' => $message->user_feedback,
                    ],
                    'branching' => [
                        'is_branch_point' => $message->is_branch_point,
                        'branch_id' => $message->branch_id,
                        'has_branches' => $message->hasBranches(),
                    ],
                ];

                if ($includeToolUsage && $message->tools_used) {
                    $rawToolsUsed = $message->tools_used;
                    /** @var array<string, mixed> $toolsUsed */
                    $toolsUsed = is_array($rawToolsUsed) ? array_merge([], $rawToolsUsed) : [];
                    $formatted['tools'] = [
                        'used' => $toolsUsed,
                        'results' => $message->tool_results,
                        'count' => $message->tool_call_count ?? 0,
                    ];
                }

                return $formatted;
            });
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to get conversation history', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Get conversation analytics
     *
     * @return array<string, mixed>
     */
    public function getConversationAnalytics(AIConversation $conversation): array
    {
        try {
            $messages = $conversation->messages;

            $analytics = [
                'conversation_id' => $conversation->conversation_id,
                'total_messages' => $messages->count(),
                'user_messages' => $messages->where('message_type', '=', 'user')->count(),
                'ai_messages' => $messages->where('message_type', '=', 'ai')->count(),
                'agent_breakdown' => $this->getAgentBreakdown($messages),
                'tool_usage' => $this->getToolUsageStats($messages),
                'quality_metrics' => $this->getQualityMetrics($messages),
                'branching_stats' => $this->getBranchingStats($messages),
                'performance' => $this->getPerformanceMetrics($messages),
                'cost_analysis' => $this->getCostAnalysis($messages),
            ];

            return $analytics;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to get analytics', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Export conversation workflow
     *
     * @return array<string, mixed>
     */
    public function exportWorkflow(AIConversation $conversation, string $format = 'json'): array
    {
        try {
            $messages = $this->getConversationHistory($conversation, true);

            $workflow = [
                'conversation_id' => $conversation->conversation_id,
                'title' => $conversation->conversation_title,
                'type' => $conversation->conversation_type,
                'created_at' => $conversation->started_at,
                'messages' => $messages->toArray(),
                'analytics' => $this->getConversationAnalytics($conversation),
                'metadata' => [
                    'exported_at' => now()->toIso8601String(),
                    'format' => $format,
                    'version' => '1.0',
                ],
            ];

            Log::info('[ConversationManagement] Workflow exported', [
                'conversation_id' => $conversation->conversation_id,
                'format' => $format,
            ]);

            return $workflow;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to export workflow', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Add feedback to improve agent performance
     *
     * @param  array<int, mixed>  $improvementSuggestions
     */
    public function addAgentFeedback(
        ConversationMessage $message,
        int $rating,
        ?string $feedback = null,
        array $improvementSuggestions = []
    ): void {
        try {
            $message->quality_rating = $rating;
            $message->user_feedback = $feedback;
            $message->is_helpful = $rating >= 4;
            $message->save();

            // Store feedback for agent improvement
            $this->storeAgentLearning($message, $rating, $improvementSuggestions);

            Log::info('[ConversationManagement] Agent feedback added', [
                'message_id' => $message->id,
                'agent_id' => $message->agent_id,
                'rating' => $rating,
            ]);
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to add feedback', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Protected helper methods
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<int, mixed>
     */
    protected function getAgentBreakdown(Collection $messages): array
    {
        /** @var array<string, array{agent_id: mixed, agent_type: mixed, agent_name: mixed, message_count: int, tool_calls: int, avg_rating: float, helpful_count: int, helpfulness_rate?: float}> $breakdown */
        $breakdown = [];

        foreach ($messages as $message) {
            if ($message->agent_id) {
                $agentKey = (string) $message->agent_id;

                if (! isset($breakdown[$agentKey])) {
                    $breakdown[$agentKey] = [
                        'agent_id' => $message->agent_id,
                        'agent_type' => $message->agent_type,
                        'agent_name' => $message->agent_name,
                        'message_count' => 0,
                        'tool_calls' => 0,
                        'avg_rating' => 0.0,
                        'helpful_count' => 0,
                    ];
                }

                $breakdown[$agentKey]['message_count']++;
                $breakdown[$agentKey]['tool_calls'] += $message->tool_call_count ?? 0;

                if ($message->quality_rating) {
                    $breakdown[$agentKey]['avg_rating'] += (float) $message->quality_rating;
                }

                if ($message->is_helpful) {
                    $breakdown[$agentKey]['helpful_count']++;
                }
            }
        }

        // Calculate averages
        foreach ($breakdown as $key => $data) {
            $messageCount = $data['message_count'];
            if ($messageCount > 0) {
                $breakdown[$key]['avg_rating'] = round($data['avg_rating'] / $messageCount, 2);
                $breakdown[$key]['helpfulness_rate'] = round(($data['helpful_count'] / $messageCount) * 100, 2);
            }
        }

        return array_values($breakdown);
    }

    protected function generateConversationTitle(string $type): string
    {
        $titles = [
            'career_planning' => 'Career Planning Session',
            'skill_optimization' => 'Skill Optimization Discussion',
            'training_advice' => 'Training Advice',
            'race_strategy' => 'Race Strategy Planning',
            'general_help' => 'General Help',
            'debugging' => 'Debugging Session',
        ];

        return $titles[$type] ?? 'Conversation';
    }

    /**
     * Get tool usage stats
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, mixed>
     */
    protected function getToolUsageStats(Collection $messages): array
    {
        /** @var array<string, array{name: string, call_count: int, success_count: int, failure_count: int, success_rate?: float}> $toolStats */
        $toolStats = [];
        $totalToolCalls = 0;

        foreach ($messages as $message) {
            $toolsUsed = $message->tools_used;
            if ($toolsUsed && \is_array($toolsUsed)) {
                foreach ($toolsUsed as $tool) {
                    $toolName = \is_array($tool)
                        ? (is_string($tool['name'] ?? null) ? $tool['name'] : 'unknown')
                        : (is_string($tool) ? $tool : 'unknown');

                    if (! isset($toolStats[$toolName])) {
                        $toolStats[$toolName] = [
                            'name' => $toolName,
                            'call_count' => 0,
                            'success_count' => 0,
                            'failure_count' => 0,
                        ];
                    }

                    $toolStats[$toolName]['call_count']++;
                    $totalToolCalls++;

                    // Check if tool call was successful
                    $toolResults = $message->tool_results;
                    if ($toolResults && \is_array($toolResults) && isset($toolResults[$toolName])) {
                        $result = $toolResults[$toolName];
                        if (\is_array($result) && isset($result['success']) && $result['success']) {
                            $toolStats[$toolName]['success_count']++;
                        } else {
                            $toolStats[$toolName]['failure_count']++;
                        }
                    }
                }
            }
        }

        // Calculate success rates
        foreach ($toolStats as $key => $stats) {
            if ($stats['call_count'] > 0) {
                $toolStats[$key]['success_rate'] = round(($stats['success_count'] / $stats['call_count']) * 100, 2);
            }
        }

        return [
            'total_tool_calls' => $totalToolCalls,
            'unique_tools' => \count($toolStats),
            'tools' => array_values($toolStats),
        ];
    }

    /**
     * Get quality metrics
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, mixed>
     */
    protected function getQualityMetrics(Collection $messages): array
    {
        $aiMessages = $messages->where('message_type', '=', 'ai');
        $ratedMessages = $aiMessages->whereNotNull('quality_rating');

        $totalRating = $ratedMessages->sum('quality_rating');
        $helpfulCount = $aiMessages->where('is_helpful', true)->count();

        return [
            'total_ai_messages' => $aiMessages->count(),
            'rated_messages' => $ratedMessages->count(),
            'average_rating' => $ratedMessages->count() > 0 ? round((is_numeric($totalRating) ? (float) $totalRating : 0) / $ratedMessages->count(), 2) : 0,
            'helpful_count' => $helpfulCount,
            'helpfulness_rate' => $aiMessages->count() > 0 ? round(($helpfulCount / $aiMessages->count()) * 100, 2) : 0,
            'rating_distribution' => $this->getRatingDistribution($ratedMessages),
        ];
    }

    /**
     * Get rating distribution
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<int, int>
     */
    protected function getRatingDistribution(Collection $messages): array
    {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($messages as $message) {
            $rating = $message->quality_rating;
            if ($rating >= 1 && $rating <= 5) {
                $distribution[$rating]++;
            }
        }

        return $distribution;
    }

    /**
     * Get branching stats
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, mixed>
     */
    protected function getBranchingStats(Collection $messages): array
    {
        $branchPoints = $messages->where('is_branch_point', true);
        $branchedMessages = $messages->whereNotNull('branch_id');

        return [
            'total_branch_points' => $branchPoints->count(),
            'total_branches' => $branchedMessages->pluck('branch_id')->unique()->count(),
            'max_branch_depth' => $messages->max('branch_depth') ?? 0,
            'avg_branches_per_point' => $branchPoints->count() > 0
                ? round($branchedMessages->count() / $branchPoints->count(), 2)
                : 0,
        ];
    }

    /**
     * Get performance metrics
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, mixed>
     */
    protected function getPerformanceMetrics(Collection $messages): array
    {
        $aiMessages = $messages->where('message_type', '=', 'ai')->whereNotNull('processing_time');

        $sumProcessing = $aiMessages->sum('processing_time');
        $avgProcessing = $aiMessages->avg('processing_time');
        $minProcessing = $aiMessages->min('processing_time');
        $maxProcessing = $aiMessages->max('processing_time');
        $sumTokens = $aiMessages->sum('tokens_used');
        $avgTokens = $aiMessages->avg('tokens_used');

        return [
            'total_processing_time' => is_numeric($sumProcessing) ? round((float) $sumProcessing, 2) : 0,
            'avg_processing_time' => $aiMessages->count() > 0 && $avgProcessing !== null
                ? round((float) $avgProcessing, 2)
                : 0,
            'min_processing_time' => $aiMessages->count() > 0 && is_numeric($minProcessing)
                ? round((float) $minProcessing, 2)
                : 0,
            'max_processing_time' => $aiMessages->count() > 0 && is_numeric($maxProcessing)
                ? round((float) $maxProcessing, 2)
                : 0,
            'total_tokens' => is_numeric($sumTokens) ? (int) $sumTokens : 0,
            'avg_tokens_per_message' => $aiMessages->count() > 0 && $avgTokens !== null
                ? round((float) $avgTokens, 0)
                : 0,
        ];
    }

    /**
     * Get cost analysis
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, mixed>
     */
    protected function getCostAnalysis(Collection $messages): array
    {
        $aiMessages = $messages->where('message_type', '=', 'ai')->whereNotNull('cost_estimate');

        $sumCost = $aiMessages->sum('cost_estimate');
        $avgCost = $aiMessages->avg('cost_estimate');

        return [
            'total_cost' => is_numeric($sumCost) ? round((float) $sumCost, 6) : 0,
            'avg_cost_per_message' => $aiMessages->count() > 0 && $avgCost !== null
                ? round((float) $avgCost, 6)
                : 0,
            'cost_by_agent' => $this->getCostByAgent($aiMessages),
        ];
    }

    /**
     * Get cost by agent
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<int, array<string, mixed>>
     */
    protected function getCostByAgent(Collection $messages): array
    {
        /** @var array<string, array{agent_id: mixed, agent_name: mixed, total_cost: float, message_count: int, avg_cost?: float}> $costByAgent */
        $costByAgent = [];

        foreach ($messages as $message) {
            if ($message->agent_id && $message->cost_estimate) {
                $agentKey = (string) $message->agent_id;

                if (! isset($costByAgent[$agentKey])) {
                    $costByAgent[$agentKey] = [
                        'agent_id' => $message->agent_id,
                        'agent_name' => $message->agent_name,
                        'total_cost' => 0.0,
                        'message_count' => 0,
                    ];
                }

                $costByAgent[$agentKey]['total_cost'] += (float) $message->cost_estimate;
                $costByAgent[$agentKey]['message_count']++;
            }
        }

        // Calculate averages
        foreach ($costByAgent as $key => $data) {
            $costByAgent[$key]['total_cost'] = round($data['total_cost'], 6);
            $costByAgent[$key]['avg_cost'] = $data['message_count'] > 0
                ? round($data['total_cost'] / $data['message_count'], 6)
                : 0.0;
        }

        return array_values($costByAgent);
    }

    /**
     * Store agent learning
     *
     * @param  array<int, mixed>  $suggestions
     */
    protected function storeAgentLearning(
        ConversationMessage $message,
        int $rating,
        array $suggestions
    ): void {
        // This would integrate with AgentMemoryService to store learning data
        // For now, just log it
        Log::info('[ConversationManagement] Agent learning stored', [
            'agent_id' => $message->agent_id,
            'rating' => $rating,
            'suggestions' => $suggestions,
        ]);
    }
}
