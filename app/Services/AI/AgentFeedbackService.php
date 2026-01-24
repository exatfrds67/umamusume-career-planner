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
     *
     * @param  array<string>  $categories
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
     *
     * @return array<string, mixed>
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
     *
     * @return array<int, array<string, mixed>>
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
     *
     * @return array<string, mixed>
     */
    public function applyLearning(string $agentId): array
    {
        try {
            // Get all feedback for this agent
            $memories = $this->memoryService->getAgentMemories($agentId, AgentMemoryService::MEMORY_LONG_TERM);
            $feedback = array_filter($memories, function (array $memory): bool {
                $key = $memory['key'] ?? '';

                return is_string($key) && str_starts_with($key, 'learning:');
            });

            // Analyze patterns - rekey array for analyzePatterns
            /** @var array<string, mixed> $feedbackForPatterns */
            $feedbackForPatterns = [];
            foreach ($feedback as $key => $value) {
                $feedbackForPatterns[(string) $key] = $value;
            }
            $patterns = $this->analyzePatterns($feedbackForPatterns);

            // Store learned patterns
            foreach ($patterns as $pattern) {
                $patternType = is_string($pattern['type'] ?? null) ? $pattern['type'] : 'unknown';
                $this->memoryService->storeLearning(
                    $agentId,
                    "pattern:{$patternType}",
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
     *
     * @return array<string, mixed>
     */
    public function getFeedbackTrends(string $agentId, string $timeframe): array
    {
        try {
            $startDate = $this->getTimeframeStart($timeframe);

            $messages = ConversationMessage::where('agent_id', $agentId)
                ->where('created_at', '>=', $startDate)
                ->whereNotNull('quality_rating')
                ->orderBy('created_at')
                ->get();

            // Group by week
            $weeklyData = $messages->groupBy(function ($message) {
                $createdAt = $message->created_at;

                return $createdAt !== null ? $createdAt->format('Y-W') : 'unknown';
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
     *
     * @param  array<string>  $agentIds
     * @return array<string, mixed>
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
     *
     * @param  array<string>  $categories
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

        $agentId = (string) $message->agent_id;

        $learningData = [
            'message_id' => $message->id,
            'rating' => $rating,
            'feedback' => $feedback,
            'categories' => $categories,
            'context' => [
                'message_type' => $message->message_type,
                'tools_used' => $message->tools_used,
                'processing_time' => $message->getAttribute('processing_time'),
                'tokens_used' => $message->tokens_used,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->memoryService->storeLearning(
            $agentId,
            "feedback:{$message->id}",
            $learningData
        );
    }

    protected function updateAgentMetrics(string $agentId, int $rating): void
    {
        $cacheKey = "agent_metrics:{$agentId}";
        /** @var array{total_ratings: int, sum_ratings: int, helpful_count: int} $metrics */
        $metrics = Cache::get($cacheKey, [
            'total_ratings' => 0,
            'sum_ratings' => 0,
            'helpful_count' => 0,
        ]);

        $totalRatings = (int) $metrics['total_ratings'];
        $sumRatings = (int) $metrics['sum_ratings'];
        $helpfulCount = (int) $metrics['helpful_count'];

        $totalRatings++;
        $sumRatings += $rating;
        if ($rating >= 4) {
            $helpfulCount++;
        }

        $metrics = [
            'total_ratings' => $totalRatings,
            'sum_ratings' => $sumRatings,
            'helpful_count' => $helpfulCount,
        ];

        Cache::put($cacheKey, $metrics, 86400); // 24 hours
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
            /** @var ConversationMessage $message */
            $rating = $message->quality_rating;
            if ($rating >= 1 && $rating <= 5) {
                $distribution[$rating]++;
            }
        }

        return $distribution;
    }

    /**
     * Get recent trend
     *
     * @param  Collection<int, ConversationMessage>  $messages
     */
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

    /**
     * Identify improvement areas
     *
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * Identify strengths
     *
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * Analyze common issues
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<string, int>
     */
    protected function analyzeCommonIssues(Collection $messages): array
    {
        $issues = [];

        foreach ($messages as $message) {
            /** @var ConversationMessage $message */
            $userFeedback = $message->user_feedback;
            if ($userFeedback !== null) {
                // Simple keyword analysis
                $feedback = strtolower((string) $userFeedback);

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

    /**
     * Analyze tool usage patterns
     *
     * @return array<int, array<string, mixed>>
     */
    protected function analyzeToolUsagePatterns(string $agentId): array
    {
        // Analyze which tools correlate with low ratings
        return [];
    }

    /**
     * Analyze response patterns
     *
     * @return array<int, array<string, mixed>>
     */
    protected function analyzeResponsePatterns(string $agentId): array
    {
        // Analyze response characteristics that correlate with ratings
        return [];
    }

    /**
     * Analyze patterns
     *
     * @param  array<string, mixed>  $feedback
     * @return array<int, array<string, mixed>>
     */
    protected function analyzePatterns(array $feedback): array
    {
        // Analyze feedback to identify patterns
        return [];
    }

    /**
     * Generate configuration updates
     *
     * @param  array<int, array<string, mixed>>  $patterns
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * Calculate overall improvement
     *
     * @param  array<int, array<string, mixed>>  $trends
     * @return array<string, mixed>
     */
    protected function calculateOverallImprovement(array $trends): array
    {
        if (count($trends) < 2) {
            return ['status' => 'insufficient_data'];
        }

        $firstEntry = $trends[0] ?? ['avg_rating' => 0];
        $first = is_numeric($firstEntry['avg_rating'] ?? null) ? (float) $firstEntry['avg_rating'] : 0.0;
        $lastEntry = $trends[count($trends) - 1] ?? ['avg_rating' => 0];
        $last = is_numeric($lastEntry['avg_rating'] ?? null) ? (float) $lastEntry['avg_rating'] : 0.0;
        $change = $last - $first;
        $percentage = $first !== 0.0 ? round(($change / $first) * 100, 2) : 0.0;

        return [
            'change' => round($change, 2),
            'percentage' => $percentage,
            'direction' => $change > 0 ? 'improving' : ($change < 0 ? 'declining' : 'stable'),
        ];
    }

    /**
     * Calculate rankings
     *
     * @param  array<string, array<string, mixed>>  $comparison
     * @return array<string, array<string, int>>
     */
    protected function calculateRankings(array $comparison): array
    {
        $rankings = [];

        // Rank by average rating
        $byRating = collect($comparison)->sortByDesc('avg_rating');
        $rank = 1;
        foreach ($byRating as $agentId => $data) {
            $rankings[$agentId]['rating_rank'] = $rank;
            $rank++;
        }

        // Rank by helpful rate
        $byHelpful = collect($comparison)->sortByDesc('helpful_rate');
        $rank = 1;
        foreach ($byHelpful as $agentId => $data) {
            $rankings[$agentId]['helpful_rank'] = $rank;
            $rank++;
        }

        return $rankings;
    }

    /**
     * Identify best performer
     *
     * @param  array<string, array<string, mixed>>  $comparison
     */
    protected function identifyBestPerformer(array $comparison): ?string
    {
        $best = collect($comparison)->sortByDesc('avg_rating')->first();

        if ($best === null) {
            return null;
        }

        $key = array_search($best, $comparison, true);

        return is_string($key) ? $key : null;
    }

    /**
     * Identify needs improvement
     *
     * @param  array<string, array<string, mixed>>  $comparison
     * @return array<int, string>
     */
    protected function identifyNeedsImprovement(array $comparison): array
    {
        return collect($comparison)
            ->filter(fn ($data) => isset($data['avg_rating']) && $data['avg_rating'] < 3.5)
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Analyze tool issues
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<int, array<string, mixed>>
     */
    protected function analyzeToolIssues(Collection $messages): array
    {
        // Analyze tool-related issues
        return [];
    }

    /**
     * Analyze successful patterns
     *
     * @param  Collection<int, ConversationMessage>  $messages
     * @return array<int, array<string, mixed>>
     */
    protected function analyzeSuccessfulPatterns(Collection $messages): array
    {
        // Analyze what makes messages successful
        return [];
    }

    /**
     * Get default performance summary
     *
     * @return array<string, mixed>
     */
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
