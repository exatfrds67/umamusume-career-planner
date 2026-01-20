<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\ConversationMessage;
use App\Services\MCP\AgentMemoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Feedback Service
 *
 * Collects and processes user feedback to improve agent performance
 * over time through learning and optimization.
 *
 * Requirements: 13.4, 56.4
 */
class AgentFeedbackService
{
    public function __construct(
        protected AgentMemoryService $memoryService
    ) {}

    /**
     * Record feedback for an agent message
     */
    public function recordFeedback(
        ConversationMessage $message,
        int $rating,
        ?string $feedback = null,
        array $categories = []
    ): void {
        try {
            // Update message with feedback
            $message->update([
                'quality_rating' => $rating,
                'user_feedback' => $feedback,
                'is_helpful' => $rating >= 4,
            ]);

            // Store feedback for agent learning
            if ($message->agent_id) {
                $this->storeFeedbackForLearning($message, $rating, $feedback, $categories);
            }

            // Update agent performance metrics
            if ($message->agent_id !== null) {
                $this->updateAgentMetrics((string) $message->agent_id, $rating);
            }

            Log::info('[AgentFeedback] Feedback recorded', [
                'message_id' => $message->id,
                'agent_id' => $message->agent_id,
                'rating' => $rating,
            ]);
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to record feedback', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get agent performance summary
     */
    public function getAgentPerformanceSummary(string $agentId): array
    {
        try {
            $messages = ConversationMessage::where('agent_id', $agentId)
                ->whereNotNull('quality_rating')
                ->get();

            if ($messages->isEmpty()) {
                return $this->getDefaultPerformanceSummary($agentId);
            }

            return [
                'agent_id' => $agentId,
                'total_messages' => $messages->count(),
                'avg_rating' => round((float) ($messages->avg('quality_rating') ?? 0), 2),
                'helpful_rate' => round(($messages->where('is_helpful', true)->count() / $messages->count()) * 100, 2),
                'rating_distribution' => $this->getRatingDistribution($messages),
                'recent_trend' => $this->getRecentTrend($messages),
                'improvement_areas' => $this->identifyImprovementAreas($agentId),
                'strengths' => $this->identifyStrengths($agentId),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to get performance summary', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultPerformanceSummary($agentId);
        }
    }

    /**
     * Get improvement recommendations for an agent
     */
    public function getImprovementRecommendations(string $agentId): array
    {
        try {
            $lowRatedMessages = ConversationMessage::where('agent_id', $agentId)
                ->where('quality_rating', '<=', 2)
                ->whereNotNull('user_feedback')
                ->get();

            $recommendations = [];

            // Analyze common issues
            $commonIssues = $this->analyzeCommonIssues($lowRatedMessages);
            foreach ($commonIssues as $issue => $count) {
                $recommendations[] = [
                    'type' => 'issue',
                    'description' => $issue,
                    'frequency' => $count,
                    'priority' => $this->calculatePriority($count, $lowRatedMessages->count()),
                    'suggested_action' => $this->suggestAction($issue),
                ];
            }

            // Analyze tool usage patterns
            $toolRecommendations = $this->analyzeToolUsagePatterns($agentId);
            $recommendations = array_merge($recommendations, $toolRecommendations);

            // Analyze response patterns
            $responseRecommendations = $this->analyzeResponsePatterns($agentId);
            $recommendations = array_merge($recommendations, $responseRecommendations);

            Log::info('[AgentFeedback] Improvement recommendations generated', [
                'agent_id' => $agentId,
                'recommendation_count' => count($recommendations),
            ]);

            return $recommendations;
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to get recommendations', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Apply learning from feedback
     */
    public function applyLearning(string $agentId): array
    {
        try {
            // Get all feedback for this agent
            $feedback = $this->memoryService->getLearnings($agentId);

            // Analyze patterns
            $patterns = $this->analyzePatterns($feedback);

            // Store learned patterns
            foreach ($patterns as $pattern) {
                $this->memoryService->storeLearning(
                    $agentId,
                    "pattern:{$pattern['type']}",
                    $pattern
                );
            }

            // Update agent configuration based on learning
            $updates = $this->generateConfigurationUpdates($patterns);

            Log::info('[AgentFeedback] Learning applied', [
                'agent_id' => $agentId,
                'patterns_found' => count($patterns),
                'updates_generated' => count($updates),
            ]);

            return [
                'agent_id' => $agentId,
                'patterns_learned' => count($patterns),
                'configuration_updates' => $updates,
                'applied_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to apply learning', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get feedback trends over time
     */
    public function getFeedbackTrends(
        string $agentId,
        string $timeframe = '30d'
    ): array {
        try {
            $startDate = $this->getTimeframeStart($timeframe);

            $messages = ConversationMessage::where('agent_id', $agentId)
                ->where('created_at', '>=', $startDate)
                ->whereNotNull('quality_rating')
                ->orderBy('created_at')
                ->get();

            // Group by week
            $weeklyData = $messages->groupBy(function ($message) {
                return $message->created_at->format('Y-W');
            });

            $trends = [];
            foreach ($weeklyData as $week => $weekMessages) {
                $trends[] = [
                    'week' => $week,
                    'avg_rating' => round((float) ($weekMessages->avg('quality_rating') ?? 0), 2),
                    'message_count' => $weekMessages->count(),
                    'helpful_rate' => round(($weekMessages->where('is_helpful', true)->count() / $weekMessages->count()) * 100, 2),
                ];
            }

            return [
                'agent_id' => $agentId,
                'timeframe' => $timeframe,
                'trends' => $trends,
                'overall_improvement' => $this->calculateOverallImprovement($trends),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to get feedback trends', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Compare agent performance
     */
    public function compareAgents(array $agentIds): array
    {
        try {
            $comparison = [];

            foreach ($agentIds as $agentId) {
                $comparison[$agentId] = $this->getAgentPerformanceSummary($agentId);
            }

            // Add relative rankings
            $rankings = $this->calculateRankings($comparison);

            return [
                'agents' => $comparison,
                'rankings' => $rankings,
                'best_performer' => $this->identifyBestPerformer($comparison),
                'needs_improvement' => $this->identifyNeedsImprovement($comparison),
            ];
        } catch (\Exception $e) {
            Log::error('[AgentFeedback] Failed to compare agents', [
                'agent_ids' => $agentIds,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Protected helper methods
     */
    protected function storeFeedbackForLearning(
        ConversationMessage $message,
        int $rating,
        ?string $feedback,
        array $categories
    ): void {
        if ($message->agent_id === null) {
            return;
        }

        $learningData = [
            'message_id' => $message->id,
            'rating' => $rating,
            'feedback' => $feedback,
            'categories' => $categories,
            'context' => [
                'message_type' => $message->message_type,
                'tools_used' => $message->tools_used,
                'processing_time' => $message->processing_time,
                'tokens_used' => $message->tokens_used,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->memoryService->storeLearning(
            (string) $message->agent_id,
            "feedback:{$message->id}",
            $learningData
        );
    }

    protected function updateAgentMetrics(string $agentId, int $rating): void
    {
        $cacheKey = "agent_metrics:{$agentId}";
        $metrics = Cache::get($cacheKey, [
            'total_ratings' => 0,
            'sum_ratings' => 0,
            'helpful_count' => 0,
        ]);

        $metrics['total_ratings']++;
        $metrics['sum_ratings'] += $rating;
        if ($rating >= 4) {
            $metrics['helpful_count']++;
        }

        Cache::put($cacheKey, $metrics, 86400); // 24 hours
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

    protected function getRecentTrend(Collection $messages): string
    {
        $recent = $messages->sortByDesc('created_at')->take(10);
        $older = $messages->sortByDesc('created_at')->skip(10)->take(10);

        if ($older->isEmpty()) {
            return 'insufficient_data';
        }

        $recentAvg = $recent->avg('quality_rating');
        $olderAvg = $older->avg('quality_rating');

        $diff = $recentAvg - $olderAvg;

        if ($diff > 0.5) {
            return 'improving';
        } elseif ($diff < -0.5) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    protected function identifyImprovementAreas(string $agentId): array
    {
        $lowRatedMessages = ConversationMessage::where('agent_id', $agentId)
            ->where('quality_rating', '<=', 2)
            ->get();

        $areas = [];

        // Analyze tool usage in low-rated messages
        $toolIssues = $this->analyzeToolIssues($lowRatedMessages);
        if (! empty($toolIssues)) {
            $areas[] = [
                'area' => 'tool_usage',
                'description' => 'Tool usage optimization needed',
                'details' => $toolIssues,
            ];
        }

        // Analyze response time
        $avgTime = $lowRatedMessages->avg('processing_time');
        if ($avgTime > 10) {
            $areas[] = [
                'area' => 'response_time',
                'description' => 'Response time too slow',
                'avg_time' => round($avgTime, 2),
            ];
        }

        return $areas;
    }

    protected function identifyStrengths(string $agentId): array
    {
        $highRatedMessages = ConversationMessage::where('agent_id', $agentId)
            ->where('quality_rating', '>=', 4)
            ->get();

        $strengths = [];

        // Analyze what works well
        $successfulPatterns = $this->analyzeSuccessfulPatterns($highRatedMessages);
        foreach ($successfulPatterns as $pattern) {
            $strengths[] = [
                'strength' => $pattern['type'],
                'description' => $pattern['description'],
                'frequency' => $pattern['frequency'],
            ];
        }

        return $strengths;
    }

    protected function analyzeCommonIssues(Collection $messages): array
    {
        $issues = [];

        foreach ($messages as $message) {
            if ($message->user_feedback) {
                // Simple keyword analysis
                $feedback = strtolower($message->user_feedback);

                if (str_contains($feedback, 'slow') || str_contains($feedback, 'time')) {
                    $issues['slow_response'] = ($issues['slow_response'] ?? 0) + 1;
                }
                if (str_contains($feedback, 'wrong') || str_contains($feedback, 'incorrect')) {
                    $issues['incorrect_information'] = ($issues['incorrect_information'] ?? 0) + 1;
                }
                if (str_contains($feedback, 'unclear') || str_contains($feedback, 'confusing')) {
                    $issues['unclear_response'] = ($issues['unclear_response'] ?? 0) + 1;
                }
                if (str_contains($feedback, 'tool') || str_contains($feedback, 'function')) {
                    $issues['tool_usage'] = ($issues['tool_usage'] ?? 0) + 1;
                }
            }
        }

        return $issues;
    }

    protected function calculatePriority(int $count, int $total): string
    {
        $percentage = ($count / $total) * 100;

        if ($percentage > 50) {
            return 'high';
        } elseif ($percentage > 25) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    protected function suggestAction(string $issue): string
    {
        return match ($issue) {
            'slow_response' => 'Optimize processing pipeline and reduce tool calls',
            'incorrect_information' => 'Review knowledge base and improve fact-checking',
            'unclear_response' => 'Improve response formatting and clarity',
            'tool_usage' => 'Review tool selection logic and error handling',
            default => 'Review and analyze specific feedback',
        };
    }

    protected function analyzeToolUsagePatterns(string $agentId): array
    {
        // Analyze which tools correlate with low ratings
        return [];
    }

    protected function analyzeResponsePatterns(string $agentId): array
    {
        // Analyze response characteristics that correlate with ratings
        return [];
    }

    protected function analyzePatterns(array $feedback): array
    {
        // Analyze feedback to identify patterns
        return [];
    }

    protected function generateConfigurationUpdates(array $patterns): array
    {
        // Generate configuration updates based on learned patterns
        return [];
    }

    protected function getTimeframeStart(string $timeframe): \DateTime
    {
        return match ($timeframe) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(30),
        };
    }

    protected function calculateOverallImprovement(array $trends): array
    {
        if (count($trends) < 2) {
            return ['status' => 'insufficient_data'];
        }

        $first = $trends[0]['avg_rating'];
        $last = end($trends)['avg_rating'];
        $change = $last - $first;

        return [
            'change' => round($change, 2),
            'percentage' => round(($change / $first) * 100, 2),
            'direction' => $change > 0 ? 'improving' : ($change < 0 ? 'declining' : 'stable'),
        ];
    }

    protected function calculateRankings(array $comparison): array
    {
        $rankings = [];

        // Rank by average rating
        $byRating = collect($comparison)->sortByDesc('avg_rating');
        $rank = 1;
        foreach ($byRating as $agentId => $data) {
            $rankings[$agentId]['rating_rank'] = $rank++;
        }

        // Rank by helpful rate
        $byHelpful = collect($comparison)->sortByDesc('helpful_rate');
        $rank = 1;
        foreach ($byHelpful as $agentId => $data) {
            $rankings[$agentId]['helpful_rank'] = $rank++;
        }

        return $rankings;
    }

    protected function identifyBestPerformer(array $comparison): ?string
    {
        $best = collect($comparison)->sortByDesc('avg_rating')->first();

        return $best ? array_search($best, $comparison) : null;
    }

    protected function identifyNeedsImprovement(array $comparison): array
    {
        return collect($comparison)
            ->filter(fn ($data) => $data['avg_rating'] < 3.5)
            ->keys()
            ->toArray();
    }

    protected function analyzeToolIssues(Collection $messages): array
    {
        // Analyze tool-related issues
        return [];
    }

    protected function analyzeSuccessfulPatterns(Collection $messages): array
    {
        // Analyze what makes messages successful
        return [];
    }

    protected function getDefaultPerformanceSummary(string $agentId): array
    {
        return [
            'agent_id' => $agentId,
            'total_messages' => 0,
            'avg_rating' => 0,
            'helpful_rate' => 0,
            'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            'recent_trend' => 'insufficient_data',
            'improvement_areas' => [],
            'strengths' => [],
        ];
    }
}
