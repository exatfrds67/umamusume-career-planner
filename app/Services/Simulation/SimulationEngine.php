<?php

declare(strict_types=1);

namespace App\Services\Simulation;

use App\Models\Career;

/**
 * Simulates a single training scenario for career planning.
 *
 * Takes a career configuration and runs statistical simulation
 * to predict outcomes based on training parameters.
 *
 * @see Requirements: FR-12.1
 */
class SimulationEngine
{
    /**
     * Run a single simulation scenario.
     *
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $targetStats
     * @param  array{training_focus: string, support_deck_bonus: float, scenario_type: string}  $parameters
     * @return array{final_stats: array<string, int>, total_turns: int, sp_earned: int, win_rate: float, efficiency_score: float}
     */
    public function runScenario(array $targetStats, array $parameters): array
    {
        $trainingFocus = $parameters['training_focus'] ?? 'balanced';
        $supportBonus = $parameters['support_deck_bonus'] ?? 1.0;
        $scenarioType = $parameters['scenario_type'] ?? 'ura_finale';

        $totalTurns = $scenarioType === 'unity_cup' ? 78 : 72;

        $stats = $this->simulateTraining($targetStats, $trainingFocus, $supportBonus, $totalTurns);
        $spEarned = $this->calculateSpEarned($stats, $totalTurns);
        $winRate = $this->estimateWinRate($stats, $targetStats);
        $efficiencyScore = $this->calculateEfficiency($stats, $totalTurns);

        return [
            'final_stats' => $stats,
            'total_turns' => $totalTurns,
            'sp_earned' => $spEarned,
            'win_rate' => $winRate,
            'efficiency_score' => $efficiencyScore,
        ];
    }

    /**
     * Simulate training over a number of turns.
     *
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $targetStats
     * @return array{speed: int, stamina: int, power: int, guts: int, wit: int}
     */
    protected function simulateTraining(array $targetStats, string $focus, float $supportBonus, int $turns): array
    {
        $stats = ['speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100];
        $focusMultiplier = $this->getFocusMultiplier($focus);

        for ($turn = 0; $turn < $turns; $turn++) {
            foreach ($stats as $stat => $value) {
                $baseGain = mt_rand(5, 15);
                $multiplier = $focusMultiplier[$stat] ?? 1.0;
                $gain = (int) round($baseGain * $multiplier * $supportBonus);

                $stats[$stat] = min(1200, $value + $gain);
            }
        }

        return $stats;
    }

    /**
     * Get focus multipliers for each stat based on training focus.
     *
     * @return array<string, float>
     */
    protected function getFocusMultiplier(string $focus): array
    {
        return match ($focus) {
            'speed' => ['speed' => 1.5, 'stamina' => 0.8, 'power' => 1.0, 'guts' => 0.8, 'wit' => 0.9],
            'stamina' => ['speed' => 0.8, 'stamina' => 1.5, 'power' => 0.9, 'guts' => 1.0, 'wit' => 0.8],
            'power' => ['speed' => 0.9, 'stamina' => 0.8, 'power' => 1.5, 'guts' => 1.0, 'wit' => 0.8],
            'guts' => ['speed' => 0.8, 'stamina' => 1.0, 'power' => 0.8, 'guts' => 1.5, 'wit' => 0.9],
            'wit' => ['speed' => 0.9, 'stamina' => 0.8, 'power' => 0.8, 'guts' => 0.9, 'wit' => 1.5],
            default => ['speed' => 1.0, 'stamina' => 1.0, 'power' => 1.0, 'guts' => 1.0, 'wit' => 1.0],
        };
    }

    /**
     * Calculate SP earned based on final stats and turns.
     */
    protected function calculateSpEarned(array $stats, int $turns): int
    {
        $totalStats = array_sum($stats);
        $baseSpPerTurn = 8;

        return (int) round($baseSpPerTurn * $turns + ($totalStats * 0.1));
    }

    /**
     * Estimate win rate based on how close stats are to targets.
     *
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $actual
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $target
     */
    protected function estimateWinRate(array $actual, array $target): float
    {
        $totalDeviation = 0;
        $statCount = count($target);

        foreach ($target as $stat => $targetValue) {
            if ($targetValue > 0) {
                $ratio = min(1.0, ($actual[$stat] ?? 0) / $targetValue);
                $totalDeviation += $ratio;
            } else {
                $totalDeviation += 1.0;
            }
        }

        $avgFulfillment = $totalDeviation / max(1, $statCount);

        return round(min(100.0, $avgFulfillment * 100), 2);
    }

    /**
     * Calculate training efficiency score.
     *
     * @param  array{speed: int, stamina: int, power: int, guts: int, wit: int}  $stats
     */
    protected function calculateEfficiency(array $stats, int $turns): float
    {
        $totalGain = array_sum($stats) - 500;

        return round($totalGain / max(1, $turns), 2);
    }
}
