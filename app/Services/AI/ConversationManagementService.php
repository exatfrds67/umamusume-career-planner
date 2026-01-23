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
                'user_id' => $user?->id ?? throw new \Exception('User required'),
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
                'user_id' => $user?->id ?? throw new \Exception('User required'),
                'type' => $conversationType,
            ]);

            return $conversation;
        } catch (\Exception $e) {
            Log::error('[ConversationManagement] Failed to create conversation', [
                'user_id' => $user?->id ?? throw new \Exception('User required'),
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
                    'ai_model_used' => (is_array($metadata) && isset($metadata['ai_model']) ? $metadata['ai_model'] : null),
                    'processing_time' => (is_array($metadata) && isset($metadata['processing_time']) ? $metadata['processing_time'] : null),
                    'tokens_used' => (is_array($metadata) && isset($metadata['tokens_used']) ? $metadata['tokens_used'] : null),
                    'cost_estimate' => (is_array($metadata) && isset($metadata['cost_estimate']) ? $metadata['cost_estimate'] : null),
                    'sent_at' => now(),
                    'status' => 'completed',
                ]);

                // Update conversation
                $conversation->increment('message_count');
                $conversation->update(['last_activity_at' => now()]);

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
    public function createBranch(): array
        try {
            $branchId = Str::uuid()->toString();

            // Mark parent as branch point
            $parentMessage->update([
                'is_branch_point' => true,
                'branch_metadata' => [
                    'reason' => $branchReason,
                    'alternatives' => $alternatives,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

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
     * @return Collection<int, array{id: int, type: string, content: string, sent_at: string|null, agent: array{id: string|null, type: string|null, name: string|null}, quality: array{rating: int|null, is_helpful: bool|null, feedback: string|null}, branching: array{is_branch_point: bool, branch_id: string|null, has_branches: bool}, tools?: array{used: mixed, results: mixed, count: int}}>
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
                    'content' => $message->message_content,
                    'sent_at' => $message->sent_at,
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
                    $formatted['tools'] = [
                        'used' => $message->tools_used,
                        'results' => $message->tool_results,
                        'count' => $message->tool_call_count,
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
    public function getConversationAnalytics(): array
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
    public function exportWorkflow(): array
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
            $message->update([
                'quality_rating' => $rating,
                'user_feedback' => $feedback,
                'is_helpful' => $rating >= 4,
            ]);

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
     * @return array<int, mixed>
     */
    protected function getAgentBreakdown(): array
        $breakdown = [];

        foreach ($messages as $message) {
            if ($message->agent_id) {
                $agentKey = $message->agent_id;

                if (! isset($breakdown[$agentKey])) {
                    $breakdown[$agentKey] = [
                        'agent_id' => $message->agent_id,
                        'agent_type' => $message->agent_type,
                        'agent_name' => $message->agent_name,
                        'message_count' => 0,
                        'tool_calls' => 0,
                        'avg_rating' => 0,
                        'helpful_count' => 0,
                    ];
                }

                $breakdown[$agentKey]['message_count']++;
                $breakdown[$agentKey]['tool_calls'] += $message->tool_call_count ?? 0;

                if ($message->quality_rating) {
                    $breakdown[$agentKey]['avg_rating'] += $message->quality_rating;
                }

                if ($message->is_helpful) {
                    $breakdown[$agentKey]['helpful_count']++;
                }
            }
        }

        // Calculate averages
        foreach ($breakdown as $key => $data) {
            if ((is_array($data) && isset($data['message_count']) ? $data['message_count'] : null) > 0) {
                $breakdown[$key]['avg_rating'] = round((is_array($data) && isset($data['avg_rating']) ? $data['avg_rating'] : null) / (is_array($data) && isset($data['message_count']) ? $data['message_count'] : null), 2);
                $breakdown[$key]['helpfulness_rate'] = round(((is_array($data) && isset($data['helpful_count']) ? $data['helpful_count'] : null) / (is_array($data) && isset($data['message_count']) ? $data['message_count'] : null)) * 100, 2);
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
     * @return array<string, mixed>
     */
    protected function getToolUsageStats(): array
        $toolStats = [];
        $totalToolCalls = 0;

        foreach ($messages as $message) {
            $toolsUsed = $message->tools_used;
            if ($toolsUsed && \is_array($toolsUsed)) {
                foreach ($toolsUsed as $tool) {
                    $toolName = \is_array($tool) ? ($tool['name'] ?? 'unknown') : $tool;

                    if (! isset($toolStats[$toolName])) {
                        $toolStats[$toolName] = [
                            'name' => $toolName,
                            'call_count' => 0,
                            'success_count' => 0,
                            'failure_count' => 0,
                        ];
                    }

                    $toolStats[$toolName]['call_count']++;
                    $totalToolCalls = ($totalToolCalls ?? 0) + 1;

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
     * @return array<string, mixed>
     */
    protected function getQualityMetrics(): array
        $aiMessages = $messages->where('message_type', '=', 'ai');
        $ratedMessages = $aiMessages->whereNotNull('quality_rating');

        $totalRating = $ratedMessages->sum('quality_rating');
        $helpfulCount = $aiMessages->where('is_helpful', true)->count();

        return [
            'total_ai_messages' => $aiMessages->count(),
            'rated_messages' => $ratedMessages->count(),
            'average_rating' => $ratedMessages->count() > 0 ? round($totalRating / $ratedMessages->count(), 2) : 0,
            'helpful_count' => $helpfulCount,
            'helpfulness_rate' => $aiMessages->count() > 0 ? round(($helpfulCount / $aiMessages->count()) * 100, 2) : 0,
            'rating_distribution' => $this->getRatingDistribution($ratedMessages),
        ];
    }

    /**
     * Get rating distribution
     *
     * @return array<int, int>
     */
    protected function getRatingDistribution(): array
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
     * @return array<string, mixed>
     */
    protected function getBranchingStats(): array
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
     * @return array<string, mixed>
     */
    protected function getPerformanceMetrics(): array
        $aiMessages = $messages->where('message_type', '=', 'ai')->whereNotNull('processing_time');

        return [
            'total_processing_time' => round($aiMessages->sum('processing_time'), 2),
            'avg_processing_time' => $aiMessages->count() > 0
                ? round($aiMessages->avg('processing_time'), 2)
                : 0,
            'min_processing_time' => $aiMessages->count() > 0
                ? round($aiMessages->min('processing_time'), 2)
                : 0,
            'max_processing_time' => $aiMessages->count() > 0
                ? round($aiMessages->max('processing_time'), 2)
                : 0,
            'total_tokens' => $aiMessages->sum('tokens_used'),
            'avg_tokens_per_message' => $aiMessages->count() > 0 && $aiMessages->avg('tokens_used') !== null
                ? round($aiMessages->avg('tokens_used'), 0)
                : 0,
        ];
    }

    /**
     * Get cost analysis
     *
     * @return array<string, mixed>
     */
    protected function getCostAnalysis(): array
        $aiMessages = $messages->where('message_type', '=', 'ai')->whereNotNull('cost_estimate');

        return [
            'total_cost' => round($aiMessages->sum('cost_estimate'), 6),
            'avg_cost_per_message' => $aiMessages->count() > 0
                ? round($aiMessages->avg('cost_estimate'), 6)
                : 0,
            'cost_by_agent' => $this->getCostByAgent($aiMessages),
        ];
    }

    /**
     * Get cost by agent
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getCostByAgent(): array
        $costByAgent = [];

        foreach ($messages as $message) {
            if ($message->agent_id && $message->cost_estimate) {
                $agentKey = $message->agent_id;

                if (! isset($costByAgent[$agentKey])) {
                    $costByAgent[$agentKey] = [
                        'agent_id' => $message->agent_id,
                        'agent_name' => $message->agent_name,
                        'total_cost' => 0,
                        'message_count' => 0,
                    ];
                }

                $costByAgent[$agentKey]['total_cost'] += (float) $message->cost_estimate;
                $costByAgent[$agentKey]['message_count']++;
            }
        }

        // Calculate averages
        foreach ($costByAgent as $key => $data) {
            $costByAgent[$key]['total_cost'] = round((is_array($data) && isset($data['total_cost']) ? $data['total_cost'] : null), 6);
            $costByAgent[$key]['avg_cost'] = round((is_array($data) && isset($data['total_cost']) ? $data['total_cost'] : null) / (is_array($data) && isset($data['message_count']) ? $data['message_count'] : null), 6);
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
