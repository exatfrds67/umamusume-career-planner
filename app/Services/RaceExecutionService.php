<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\GameRace;
use App\Models\Race;
use Illuminate\Support\Facades\DB;

/**
 * Handles race entry and result simulation for career runs.
 */
class RaceExecutionService
{
    public function __construct(
        protected CharacterStateService $stateService
    ) {}

    /**
     * Enter a character into a race and simulate the result.
     *
     * @return array<string, mixed>
     */
    public function enterRace(Character $character, GameRace $gameRace): array
    {
        $career = $character->currentCareer;
        if (! $career) {
            return ['success' => false, 'message' => 'No active career found.'];
        }

        if ($character->current_turn >= 78) {
            return ['success' => false, 'message' => 'Career has reached the maximum number of turns.'];
        }

        return DB::transaction(function () use ($character, $gameRace, $career) {
            // Simulate race result based on character stats
            $result = $this->simulateRace($character, $gameRace);

            // Record the race
            $race = $this->recordRace($character, $career, $gameRace, $result);

            // Apply rewards
            $this->applyRaceRewards($character, $gameRace, $result);

            // Progress turn (racing consumes a turn)
            $turnResult = $this->stateService->progressTurn($character);

            // Update career
            $career->current_turn = $turnResult['turn'];
            $newPhase = $turnResult['stage'];
            if (\in_array($newPhase, ['junior', 'classic', 'senior'], true)) {
                /** @var 'junior'|'classic'|'senior' $newPhase */
                $career->current_phase = $newPhase;
            }
            $career->save();

            // Check career completion
            $careerCompleted = $turnResult['turn'] >= 78;
            if ($careerCompleted) {
                $career->status = 'completed';
                $career->save();
                $character->status = 'completed';
                $character->save();
            }

            return [
                'success' => true,
                'race' => $race,
                'result' => $result,
                'turn_result' => $turnResult,
                'career_completed' => $careerCompleted,
            ];
        });
    }

    /**
     * Simulate race result based on character stats vs race requirements.
     *
     * @return array{finish_position: int, won_race: bool, performance_rating: string}
     */
    protected function simulateRace(Character $character, GameRace $gameRace): array
    {
        $stats = is_array($character->current_stats) ? $character->current_stats : [];
        $statRequirements = is_array($gameRace->stat_requirements) ? $gameRace->stat_requirements : [];

        // Calculate a performance score based on how well stats meet requirements
        $performanceScore = $this->calculatePerformanceScore($stats, $statRequirements, $gameRace);

        // Add randomness (±15%)
        $randomFactor = mt_rand(85, 115) / 100;
        $finalScore = $performanceScore * $randomFactor;

        // Determine finish position (1-18 typical field)
        $fieldSize = 18;
        $finishPosition = match (true) {
            $finalScore >= 90 => mt_rand(1, 3),
            $finalScore >= 75 => mt_rand(1, 5),
            $finalScore >= 60 => mt_rand(3, 8),
            $finalScore >= 45 => mt_rand(5, 12),
            default => mt_rand(8, $fieldSize),
        };

        $wonRace = $finishPosition === 1;

        $performanceRating = match (true) {
            $finishPosition <= 1 => 'excellent',
            $finishPosition <= 3 => 'good',
            $finishPosition <= 5 => 'average',
            $finishPosition <= 10 => 'below_average',
            default => 'poor',
        };

        return [
            'finish_position' => $finishPosition,
            'won_race' => $wonRace,
            'performance_rating' => $performanceRating,
        ];
    }

    /**
     * Calculate performance score (0-100) based on stats vs race requirements.
     *
     * @param  array<string, mixed>  $stats
     * @param  array<string, mixed>  $requirements
     */
    protected function calculatePerformanceScore(array $stats, array $requirements, GameRace $gameRace): float
    {
        $score = 50.0; // Base score

        // Primary stat checks based on distance category
        $primaryStats = match ($gameRace->distance_category) {
            'sprint' => ['speed' => 0.4, 'power' => 0.3, 'guts' => 0.2, 'wit' => 0.1],
            'mile' => ['speed' => 0.35, 'stamina' => 0.15, 'power' => 0.25, 'guts' => 0.15, 'wit' => 0.1],
            'medium' => ['speed' => 0.25, 'stamina' => 0.25, 'power' => 0.2, 'guts' => 0.15, 'wit' => 0.15],
            'long', 'super_long' => ['stamina' => 0.35, 'speed' => 0.2, 'power' => 0.15, 'guts' => 0.2, 'wit' => 0.1],
            default => ['speed' => 0.2, 'stamina' => 0.2, 'power' => 0.2, 'guts' => 0.2, 'wit' => 0.2],
        };

        $totalStatScore = 0.0;
        foreach ($primaryStats as $stat => $weight) {
            $currentStat = isset($stats[$stat]) && is_numeric($stats[$stat]) ? (int) $stats[$stat] : 0;
            $requiredStat = isset($requirements[$stat]) && is_numeric($requirements[$stat]) ? (int) $requirements[$stat] : 300;

            // Score based on how well current stat meets the threshold
            $statRatio = $requiredStat > 0 ? min(1.5, $currentStat / $requiredStat) : 1.0;
            $totalStatScore += $statRatio * $weight * 100;
        }

        $score = $totalStatScore;

        // Grade difficulty modifier
        $gradeModifier = match ($gameRace->grade) {
            'G1' => 0.85,
            'G2' => 0.90,
            'G3' => 0.95,
            'OP' => 1.0,
            'Pre-OP' => 1.05,
            'Debut' => 1.15,
            default => 1.0,
        };

        return $score * $gradeModifier;
    }

    /**
     * Record the race result to the database.
     *
     * @param  array<string, mixed>  $result
     */
    protected function recordRace(Character $character, Career $career, GameRace $gameRace, array $result): Race
    {
        return Race::create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'race_name' => $gameRace->name_en,
            'race_internal_id' => $gameRace->slug,
            'turn_number' => $character->current_turn,
            'career_phase' => $character->career_stage,
            'race_grade' => $gameRace->grade,
            'distance_category' => $gameRace->distance_category,
            'distance_meters' => $gameRace->distance_meters,
            'surface' => $gameRace->surface,
            'field_size' => 18,
            'speed_at_race' => $character->current_stats['speed'] ?? 0,
            'stamina_at_race' => $character->current_stats['stamina'] ?? 0,
            'power_at_race' => $character->current_stats['power'] ?? 0,
            'guts_at_race' => $character->current_stats['guts'] ?? 0,
            'wit_at_race' => $character->current_stats['wit'] ?? 0,
            'finish_position' => $result['finish_position'],
            'won_race' => $result['won_race'],
            'race_result' => $result['performance_rating'],
            'fans_gained' => $result['won_race'] ? ($gameRace->fans_reward ?? 0) : (int) (($gameRace->fans_reward ?? 0) * 0.3),
            'sp_reward' => $result['won_race'] ? ($gameRace->sp_reward ?? 0) : (int) (($gameRace->sp_reward ?? 0) * 0.5),
        ]);
    }

    /**
     * Apply race rewards to character (SP, fans).
     *
     * @param  array<string, mixed>  $result
     */
    protected function applyRaceRewards(Character $character, GameRace $gameRace, array $result): void
    {
        $spReward = $result['won_race']
            ? ($gameRace->sp_reward ?? 0)
            : (int) (($gameRace->sp_reward ?? 0) * 0.5);

        $character->available_sp = (int) $character->available_sp + $spReward;
        $character->save();
    }
}
