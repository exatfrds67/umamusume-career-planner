<?php

declare(strict_types=1);

namespace App\Services\Simulation;

/**
 * Generates comparison reports from batch simulation results.
 *
 * Compares stat outcomes, win rates, SP efficiency, and
 * highlights the best scenario recommendations.
 *
 * Covers FR-12.3
 */
class ComparisonReportService
{
    /**
     * Generate a full comparison report from batch results.
     *
     * @param  array<int, array{final_stats: array<string, int>, total_turns: int, sp_earned: int, win_rate: float, efficiency_score: float}>  $results
     * @param  array<int, array{target_stats: array<string, int>, parameters: array<string, mixed>}>  $scenarios
     * @return array{stat_comparison: array<string, array<int, int>>, rankings: array<string, array<int, mixed>>, best_scenario: int, recommendations: array<int, string>, summary: array<string, mixed>}
     */
    public function generateReport(array $results, array $scenarios): array
    {
        $validResults = $this->filterValidResults($results);

        if (count($validResults) < 2) {
            return $this->emptyReport();
        }

        return [
            'stat_comparison' => $this->compareStats($validResults),
            'rankings' => $this->rankScenarios($validResults),
            'best_scenario' => $this->determineBestScenario($validResults),
            'recommendations' => $this->generateRecommendations($validResults, $scenarios),
            'summary' => $this->buildSummary($validResults),
        ];
    }

    /**
     * Compare stats across all scenarios.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, array<int, int>>
     */
    protected function compareStats(array $results): array
    {
        $comparison = [];
        $statNames = ['speed', 'stamina', 'power', 'guts', 'wit'];

        foreach ($statNames as $stat) {
            $comparison[$stat] = [];
            foreach ($results as $index => $result) {
                /** @var array<string, int> $finalStats */
                $finalStats = $result['final_stats'] ?? [];
                $comparison[$stat][$index] = $finalStats[$stat] ?? 0;
            }
        }

        return $comparison;
    }

    /**
     * Rank scenarios by various metrics.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, array<int, mixed>>
     */
    protected function rankScenarios(array $results): array
    {
        return [
            'by_win_rate' => $this->rankBy($results, 'win_rate'),
            'by_efficiency' => $this->rankBy($results, 'efficiency_score'),
            'by_sp_earned' => $this->rankBy($results, 'sp_earned'),
            'by_total_stats' => $this->rankByTotalStats($results),
        ];
    }

    /**
     * Rank results by a specific metric.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, mixed>
     */
    protected function rankBy(array $results, string $metric): array
    {
        $ranked = [];
        foreach ($results as $index => $result) {
            $ranked[$index] = $result[$metric] ?? 0;
        }

        arsort($ranked);

        return $ranked;
    }

    /**
     * Rank results by total stat sum.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, int>
     */
    protected function rankByTotalStats(array $results): array
    {
        $ranked = [];
        foreach ($results as $index => $result) {
            /** @var array<string, int> $finalStats */
            $finalStats = $result['final_stats'] ?? [];
            $ranked[$index] = array_sum($finalStats);
        }

        arsort($ranked);

        return $ranked;
    }

    /**
     * Determine the best overall scenario.
     *
     * Uses a weighted scoring: 40% win rate, 30% efficiency, 30% SP earned.
     *
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function determineBestScenario(array $results): int
    {
        $scores = [];

        /** @var array<int, float|int> $winRateCol */
        $winRateCol = array_column($results, 'win_rate');
        /** @var array<int, float|int> $efficiencyCol */
        $efficiencyCol = array_column($results, 'efficiency_score');
        /** @var array<int, float|int> $spCol */
        $spCol = array_column($results, 'sp_earned');

        $maxWinRate = (float) (max($winRateCol) ?: 1);
        $maxEfficiency = (float) (max($efficiencyCol) ?: 1);
        $maxSp = (float) (max($spCol) ?: 1);

        foreach ($results as $index => $result) {
            $normalizedWin = (float) ($result['win_rate'] ?? 0) / $maxWinRate;
            $normalizedEfficiency = (float) ($result['efficiency_score'] ?? 0) / $maxEfficiency;
            $normalizedSp = (float) ($result['sp_earned'] ?? 0) / $maxSp;

            $scores[$index] = ($normalizedWin * 0.4) + ($normalizedEfficiency * 0.3) + ($normalizedSp * 0.3);
        }

        arsort($scores);

        return (int) array_key_first($scores);
    }

    /**
     * Generate actionable recommendations.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @param  array<int, array<string, mixed>>  $scenarios
     * @return array<int, string>
     */
    protected function generateRecommendations(array $results, array $scenarios): array
    {
        $recommendations = [];
        $bestIndex = $this->determineBestScenario($results);

        foreach ($results as $index => $result) {
            $focus = (string) ($scenarios[$index]['parameters']['training_focus'] ?? 'balanced');

            if ($index === $bestIndex) {
                $winRate = (string) ($result['win_rate'] ?? 0);
                $efficiencyScore = (string) ($result['efficiency_score'] ?? 0);
                $recommendations[$index] = "Best overall scenario. Focus: {$focus}. Win rate: {$winRate}%, Efficiency: {$efficiencyScore}.";

                continue;
            }

            $winDiff = (float) ($results[$bestIndex]['win_rate'] ?? 0) - (float) ($result['win_rate'] ?? 0);
            $effDiff = (float) ($results[$bestIndex]['efficiency_score'] ?? 0) - (float) ($result['efficiency_score'] ?? 0);

            $issues = [];
            if ($winDiff > 5) {
                $issues[] = sprintf('win rate is %.1f%% lower than best', $winDiff);
            }
            if ($effDiff > 2) {
                $issues[] = sprintf('efficiency is %.1f lower than best', $effDiff);
            }

            if (empty($issues)) {
                $recommendations[$index] = "Close to optimal. Focus: {$focus}. Minor adjustments could improve results.";
            } else {
                $recommendations[$index] = "Focus: {$focus}. Areas to improve: ".implode('; ', $issues).'.';
            }
        }

        return $recommendations;
    }

    /**
     * Build overall summary statistics.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, mixed>
     */
    protected function buildSummary(array $results): array
    {
        /** @var array<int, float> $winRates */
        $winRates = array_column($results, 'win_rate');
        /** @var array<int, float> $efficiencies */
        $efficiencies = array_column($results, 'efficiency_score');
        /** @var array<int, int> $spValues */
        $spValues = array_column($results, 'sp_earned');

        return [
            'scenario_count' => count($results),
            'avg_win_rate' => round(array_sum($winRates) / count($winRates), 2),
            'max_win_rate' => max($winRates),
            'min_win_rate' => min($winRates),
            'avg_efficiency' => round(array_sum($efficiencies) / count($efficiencies), 2),
            'avg_sp_earned' => (int) round(array_sum($spValues) / count($spValues)),
            'win_rate_spread' => round(max($winRates) - min($winRates), 2),
        ];
    }

    /**
     * Filter out results that contain errors.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, array<string, mixed>>
     */
    protected function filterValidResults(array $results): array
    {
        return array_filter($results, fn (array $result): bool => ! isset($result['error']));
    }

    /**
     * Return an empty report structure.
     *
     * @return array<string, mixed>
     */
    protected function emptyReport(): array
    {
        return [
            'stat_comparison' => [],
            'rankings' => [],
            'best_scenario' => -1,
            'recommendations' => [],
            'summary' => ['scenario_count' => 0, 'error' => 'Insufficient valid results for comparison'],
        ];
    }
}
