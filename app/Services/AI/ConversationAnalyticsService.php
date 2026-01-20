<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Conversation Analytics Service
 *
 * Provides comprehensive analytics for conversation effectiveness,
 * agent performance, user satisfaction, and system optimization.
 *
 * Requirements: 13.4, 56.4
 */
class ConversationAnalyticsService
{
    /**
     * Get agent effectiveness metrics
     */
    public function getAgentEffectiveness(
        ?string $agentId = null,
        ?string $timeframe = null
    ): array {
        try {
            $query = ConversationMessage::query()
                ->where('message_type', 'ai')
                ->whereNotNull('agent_id');

            if ($agentId) {
                $query->where('agent_id', $agentId);
            }

            if ($timeframe) {
                $query->where('created_at', '>=', $this->getTimeframeStart($timeframe));
            }

            $messages = $query->get();

            return [
                'agent_id' => $agentId,
                'timeframe' => $timeframe,
                'total_messages' => $messages->count(),
                'response_quality' => $this->calculateResponseQuality($messages),
                'user_satisfaction' => $this->calculateUserSatisfaction($messages),
                'tool_effectiveness' => $this->calculateToolEffectiveness($messages),
                'performance_metrics' => $this->calculatePerformanceMetrics($messages),
                'improvement_trends' => $this->calculateImprovementTrends($messages),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get agent effectiveness', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get user satisfaction metrics
     */
    public function getUserSatisfaction(
        ?int $userId = null,
        ?string $conversationType = null
    ): array {
        try {
            $query = AIConversation::query();

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($conversationType) {
                $query->where('conversation_type', $conversationType);
            }

            $conversations = $query->with('messages')->get();

            return [
                'user_id' => $userId,
                'conversation_type' => $conversationType,
                'total_conversations' => $conversations->count(),
                'overall_satisfaction' => $this->calculateOverallSatisfaction($conversations),
                'satisfaction_by_type' => $this->calculateSatisfactionByType($conversations),
                'satisfaction_trends' => $this->calculateSatisfactionTrends($conversations),
                'feedback_summary' => $this->summarizeFeedback($conversations),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get user satisfaction', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get conversation completion metrics
     */
    public function getConversationCompletionMetrics(): array
    {
        try {
            $conversations = AIConversation::all();

            return [
                'total_conversations' => $conversations->count(),
                'by_status' => $this->groupByStatus($conversations),
                'completion_rate' => $this->calculateCompletionRate($conversations),
                'avg_duration' => $this->calculateAverageDuration($conversations),
                'avg_messages_per_conversation' => $conversations->avg('message_count'),
                'abandonment_analysis' => $this->analyzeAbandonment($conversations),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get completion metrics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get tool usage analytics
     */
    public function getToolUsageAnalytics(?string $timeframe = null): array
    {
        try {
            $query = ConversationMessage::query()
                ->whereNotNull('tools_used')
                ->where('tool_call_count', '>', 0);

            if ($timeframe) {
                $query->where('created_at', '>=', $this->getTimeframeStart($timeframe));
            }

            $messages = $query->get();

            return [
                'timeframe' => $timeframe,
                'total_tool_calls' => $messages->sum('tool_call_count'),
                'messages_with_tools' => $messages->count(),
                'tool_breakdown' => $this->analyzeToolBreakdown($messages),
                'tool_success_rates' => $this->calculateToolSuccessRates($messages),
                'tool_performance' => $this->analyzeToolPerformance($messages),
                'tool_combinations' => $this->analyzeToolCombinations($messages),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get tool usage analytics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get conversation branching analytics
     */
    public function getBranchingAnalytics(): array
    {
        try {
            $branchPoints = ConversationMessage::where('is_branch_point', true)->get();
            $branchedMessages = ConversationMessage::whereNotNull('branch_id')->get();

            return [
                'total_branch_points' => $branchPoints->count(),
                'total_branches' => $branchedMessages->pluck('branch_id')->unique()->count(),
                'branch_utilization' => $this->calculateBranchUtilization($branchPoints, $branchedMessages),
                'branch_depth_analysis' => $this->analyzeBranchDepth($branchedMessages),
                'branch_effectiveness' => $this->analyzeBranchEffectiveness($branchPoints),
                'popular_branch_reasons' => $this->analyzePopularBranchReasons($branchPoints),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get branching analytics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get cost analytics
     */
    public function getCostAnalytics(?string $timeframe = null): array
    {
        try {
            $query = ConversationMessage::query()
                ->whereNotNull('cost_estimate');

            if ($timeframe) {
                $query->where('created_at', '>=', $this->getTimeframeStart($timeframe));
            }

            $messages = $query->get();

            return [
                'timeframe' => $timeframe,
                'total_cost' => round($messages->sum('cost_estimate'), 6),
                'total_messages' => $messages->count(),
                'avg_cost_per_message' => $messages->count() > 0 && $messages->avg('cost_estimate') !== null
                    ? round($messages->avg('cost_estimate'), 6)
                    : 0,
                'cost_by_agent' => $this->analyzeCostByAgent($messages),
                'cost_by_model' => $this->analyzeCostByModel($messages),
                'cost_trends' => $this->analyzeCostTrends($messages),
                'cost_optimization_opportunities' => $this->identifyCostOptimizations($messages),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get cost analytics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get comprehensive dashboard metrics
     */
    public function getDashboardMetrics(): array
    {
        try {
            return [
                'overview' => [
                    'total_conversations' => AIConversation::count(),
                    'active_conversations' => AIConversation::where('status', 'active')->count(),
                    'total_messages' => ConversationMessage::count(),
                    'total_users' => AIConversation::distinct('user_id')->count(),
                ],
                'agent_performance' => $this->getAgentEffectiveness(),
                'user_satisfaction' => $this->getUserSatisfaction(),
                'tool_usage' => $this->getToolUsageAnalytics('7d'),
                'cost_summary' => $this->getCostAnalytics('30d'),
                'recent_activity' => $this->getRecentActivity(),
            ];
        } catch (\Exception $e) {
            Log::error('[ConversationAnalytics] Failed to get dashboard metrics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Protected helper methods
     */
    protected function getTimeframeStart(string $timeframe): \DateTime
    {
        return match ($timeframe) {
            '1h' => now()->subHour(),
            '24h', '1d' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDay(),
        };
    }

    protected function calculateResponseQuality(Collection $messages): array
    {
        $ratedMessages = $messages->whereNotNull('quality_rating');

        return [
            'avg_rating' => $ratedMessages->count() > 0
                ? round($ratedMessages->avg('quality_rating'), 2)
                : 0,
            'rating_distribution' => $this->getRatingDistribution($ratedMessages),
            'quality_score' => $this->calculateQualityScore($messages),
        ];
    }

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

    protected function calculateQualityScore(Collection $messages): float
    {
        $ratedMessages = $messages->whereNotNull('quality_rating');
        if ($ratedMessages->count() === 0) {
            return 0;
        }

        $avgRating = $ratedMessages->avg('quality_rating');
        $helpfulRate = $messages->where('is_helpful', true)->count() / $messages->count();

        // Weighted score: 70% rating, 30% helpfulness
        return round(($avgRating / 5 * 0.7 + $helpfulRate * 0.3) * 100, 2);
    }

    protected function calculateUserSatisfaction(Collection $messages): array
    {
        $helpfulCount = $messages->where('is_helpful', true)->count();
        $totalCount = $messages->count();

        return [
            'helpful_rate' => $totalCount > 0 ? round(($helpfulCount / $totalCount) * 100, 2) : 0,
            'unhelpful_rate' => $totalCount > 0 ? round((($totalCount - $helpfulCount) / $totalCount) * 100, 2) : 0,
            'feedback_count' => $messages->whereNotNull('user_feedback')->count(),
        ];
    }

    protected function calculateToolEffectiveness(Collection $messages): array
    {
        $messagesWithTools = $messages->where('tool_call_count', '>', 0);

        return [
            'tool_usage_rate' => $messages->count() > 0
                ? round(($messagesWithTools->count() / $messages->count()) * 100, 2)
                : 0,
            'avg_tools_per_message' => $messagesWithTools->count() > 0
                ? round($messagesWithTools->avg('tool_call_count'), 2)
                : 0,
            'total_tool_calls' => $messagesWithTools->sum('tool_call_count'),
        ];
    }

    protected function calculatePerformanceMetrics(Collection $messages): array
    {
        $messagesWithTime = $messages->whereNotNull('processing_time');

        return [
            'avg_processing_time' => $messagesWithTime->count() > 0
                ? round($messagesWithTime->avg('processing_time'), 2)
                : 0,
            'avg_tokens' => $messages->whereNotNull('tokens_used')->avg('tokens_used') ?? 0,
            'avg_cost' => $messages->whereNotNull('cost_estimate')->avg('cost_estimate') ?? 0,
        ];
    }

    protected function calculateImprovementTrends(Collection $messages): array
    {
        // Group messages by week
        $weeklyData = $messages->groupBy(function ($message) {
            return $message->created_at->format('Y-W');
        });

        $trends = [];
        foreach ($weeklyData as $week => $weekMessages) {
            $trends[] = [
                'week' => $week,
                'avg_rating' => round($weekMessages->whereNotNull('quality_rating')->avg('quality_rating'), 2),
                'helpful_rate' => $weekMessages->count() > 0
                    ? round(($weekMessages->where('is_helpful', true)->count() / $weekMessages->count()) * 100, 2)
                    : 0,
            ];
        }

        return $trends;
    }

    protected function calculateOverallSatisfaction(Collection $conversations): array
    {
        $allMessages = $conversations->flatMap->messages;
        $ratedMessages = $allMessages->whereNotNull('quality_rating');

        return [
            'avg_rating' => $ratedMessages->count() > 0
                ? round($ratedMessages->avg('quality_rating'), 2)
                : 0,
            'helpful_rate' => $allMessages->count() > 0
                ? round(($allMessages->where('is_helpful', true)->count() / $allMessages->count()) * 100, 2)
                : 0,
        ];
    }

    protected function calculateSatisfactionByType(Collection $conversations): array
    {
        $byType = [];

        foreach ($conversations->groupBy('conversation_type') as $type => $typeConversations) {
            $messages = $typeConversations->flatMap->messages;
            $ratedMessages = $messages->whereNotNull('quality_rating');

            $byType[$type] = [
                'conversation_count' => $typeConversations->count(),
                'avg_rating' => $ratedMessages->count() > 0
                    ? round($ratedMessages->avg('quality_rating'), 2)
                    : 0,
                'helpful_rate' => $messages->count() > 0
                    ? round(($messages->where('is_helpful', true)->count() / $messages->count()) * 100, 2)
                    : 0,
            ];
        }

        return $byType;
    }

    protected function calculateSatisfactionTrends(Collection $conversations): array
    {
        // Group by month
        $monthlyData = $conversations->groupBy(function ($conversation) {
            return $conversation->started_at?->format('Y-m') ?? 'unknown';
        });

        $trends = [];
        foreach ($monthlyData as $month => $monthConversations) {
            $messages = $monthConversations->flatMap->messages;
            $ratedMessages = $messages->whereNotNull('quality_rating');

            $trends[] = [
                'month' => $month,
                'conversation_count' => $monthConversations->count(),
                'avg_rating' => $ratedMessages->count() > 0
                    ? round($ratedMessages->avg('quality_rating'), 2)
                    : 0,
            ];
        }

        return $trends;
    }

    protected function summarizeFeedback(Collection $conversations): array
    {
        $allMessages = $conversations->flatMap->messages;
        $feedbackMessages = $allMessages->whereNotNull('user_feedback');

        return [
            'total_feedback' => $feedbackMessages->count(),
            'positive_feedback' => $feedbackMessages->where('is_helpful', true)->count(),
            'negative_feedback' => $feedbackMessages->where('is_helpful', false)->count(),
            'recent_feedback' => $feedbackMessages->sortByDesc('created_at')->take(10)->map(function ($message) {
                return [
                    'rating' => $message->quality_rating,
                    'feedback' => $message->user_feedback,
                    'agent' => $message->agent_name,
                    'date' => $message->created_at->format('Y-m-d'),
                ];
            })->values()->toArray(),
        ];
    }

    protected function groupByStatus(Collection $conversations): array
    {
        return $conversations->groupBy('status')->map->count()->toArray();
    }

    protected function calculateCompletionRate(Collection $conversations): float
    {
        $total = $conversations->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $conversations->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 2);
    }

    protected function calculateAverageDuration(Collection $conversations): float
    {
        $conversationsWithDuration = $conversations->filter(function ($conversation) {
            return $conversation->started_at && $conversation->ended_at;
        });

        if ($conversationsWithDuration->count() === 0) {
            return 0;
        }

        $totalMinutes = $conversationsWithDuration->sum(function ($conversation) {
            return $conversation->started_at->diffInMinutes($conversation->ended_at);
        });

        return round($totalMinutes / $conversationsWithDuration->count(), 2);
    }

    protected function analyzeAbandonment(Collection $conversations): array
    {
        $abandoned = $conversations->where('status', 'paused');

        return [
            'total_abandoned' => $abandoned->count(),
            'abandonment_rate' => $conversations->count() > 0
                ? round(($abandoned->count() / $conversations->count()) * 100, 2)
                : 0,
            'avg_messages_before_abandonment' => $abandoned->avg('message_count') ?? 0,
        ];
    }

    protected function analyzeToolBreakdown(Collection $messages): array
    {
        $toolCounts = [];

        foreach ($messages as $message) {
            if ($message->tools_used) {
                foreach ($message->tools_used as $tool) {
                    $toolName = is_array($tool) ? ($tool['name'] ?? 'unknown') : $tool;
                    $toolCounts[$toolName] = ($toolCounts[$toolName] ?? 0) + 1;
                }
            }
        }

        arsort($toolCounts);

        return $toolCounts;
    }

    protected function calculateToolSuccessRates(Collection $messages): array
    {
        // Implementation would analyze tool_results for success/failure
        return [];
    }

    protected function analyzeToolPerformance(Collection $messages): array
    {
        // Implementation would analyze processing time per tool
        return [];
    }

    protected function analyzeToolCombinations(Collection $messages): array
    {
        // Implementation would analyze which tools are commonly used together
        return [];
    }

    protected function calculateBranchUtilization(Collection $branchPoints, Collection $branchedMessages): array
    {
        return [
            'branches_per_point' => $branchPoints->count() > 0
                ? round($branchedMessages->count() / $branchPoints->count(), 2)
                : 0,
            'utilization_rate' => $branchPoints->count() > 0
                ? round(($branchedMessages->pluck('branch_id')->unique()->count() / $branchPoints->count()) * 100, 2)
                : 0,
        ];
    }

    protected function analyzeBranchDepth(Collection $messages): array
    {
        return [
            'max_depth' => $messages->max('branch_depth') ?? 0,
            'avg_depth' => round($messages->avg('branch_depth'), 2),
        ];
    }

    protected function analyzeBranchEffectiveness(Collection $branchPoints): array
    {
        // Analyze which branches led to better outcomes
        return [];
    }

    protected function analyzePopularBranchReasons(Collection $branchPoints): array
    {
        $reasons = [];

        foreach ($branchPoints as $point) {
            if ($point->branch_metadata && isset($point->branch_metadata['reason'])) {
                $reason = $point->branch_metadata['reason'];
                $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
            }
        }

        arsort($reasons);

        return $reasons;
    }

    protected function analyzeCostByAgent(Collection $messages): array
    {
        return $messages->groupBy('agent_id')->map(function ($agentMessages) {
            return [
                'total_cost' => round($agentMessages->sum('cost_estimate'), 6),
                'message_count' => $agentMessages->count(),
                'avg_cost' => round($agentMessages->avg('cost_estimate'), 6),
            ];
        })->toArray();
    }

    protected function analyzeCostByModel(Collection $messages): array
    {
        return $messages->groupBy('ai_model_used')->map(function ($modelMessages) {
            return [
                'total_cost' => round($modelMessages->sum('cost_estimate'), 6),
                'message_count' => $modelMessages->count(),
                'avg_cost' => round($modelMessages->avg('cost_estimate'), 6),
            ];
        })->toArray();
    }

    protected function analyzeCostTrends(Collection $messages): array
    {
        // Group by day
        return $messages->groupBy(function ($message) {
            return $message->created_at->format('Y-m-d');
        })->map(function ($dayMessages) {
            return round($dayMessages->sum('cost_estimate'), 6);
        })->toArray();
    }

    protected function identifyCostOptimizations(Collection $messages): array
    {
        // Identify opportunities to reduce costs
        return [
            'high_cost_agents' => $this->identifyHighCostAgents($messages),
            'inefficient_tool_usage' => $this->identifyInefficientToolUsage($messages),
            'model_optimization_suggestions' => $this->suggestModelOptimizations($messages),
        ];
    }

    protected function identifyHighCostAgents(Collection $messages): array
    {
        return $messages->groupBy('agent_id')
            ->map(function ($agentMessages) {
                return round($agentMessages->sum('cost_estimate'), 6);
            })
            ->sortDesc()
            ->take(5)
            ->toArray();
    }

    protected function identifyInefficientToolUsage(Collection $messages): array
    {
        // Identify tools with high cost but low success rate
        return [];
    }

    protected function suggestModelOptimizations(Collection $messages): array
    {
        // Suggest cheaper models for simple tasks
        return [];
    }

    protected function getRecentActivity(): array
    {
        $recentConversations = AIConversation::with('messages')
            ->orderBy('last_activity_at', 'desc')
            ->take(10)
            ->get();

        return $recentConversations->map(function ($conversation) {
            return [
                'id' => $conversation->id,
                'title' => $conversation->conversation_title,
                'type' => $conversation->conversation_type,
                'message_count' => $conversation->message_count,
                'last_activity' => $conversation->last_activity_at?->diffForHumans(),
                'status' => $conversation->status,
            ];
        })->toArray();
    }
}
