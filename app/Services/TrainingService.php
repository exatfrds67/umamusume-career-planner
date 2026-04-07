<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use App\Services\Training\BondProgressionService;
use App\Services\Training\SkillHintService;
use App\Services\Training\SupportBonusCalculator;
use Illuminate\Support\Facades\DB;

/**
 * Training Service
 *
 * Handles training execution and recording.
 */
class TrainingService
{
    /**
     * Maximum turns per career scenario.
     */
    private const MAX_TURNS_URA = 72;

    private const MAX_TURNS_UNITY_CUP = 78;

    public function __construct(
        protected SupportBonusCalculator $bonusCalculator,
        protected BondProgressionService $bondService,
        protected SkillHintService $hintService,
        protected CharacterStateService $stateService
    ) {}

    /**
     * Execute training and record results.
     *
     * @param  array<string, int>  $actualGains
     * @return array<string, mixed>
     */
    public function executeTraining(Character $character, string $trainingType, array $actualGains): array
    {
        // Check turn limit before executing
        $maxTurns = $this->getMaxTurns($character);
        if ($character->current_turn >= $maxTurns) {
            return [
                'success' => false,
                'message' => 'Career has reached the maximum number of turns.',
                'turn_limit_reached' => true,
            ];
        }

        return DB::transaction(function () use ($character, $trainingType, $actualGains) {
            $activeDeck = $character->activeSupportDeck;

            // Calculate bonuses if deck exists
            $bonuses = $activeDeck
                ? $this->bonusCalculator->calculateBonuses($activeDeck, $trainingType)
                : $this->getEmptyBonuses();

            // Calculate energy cost
            $energyCost = $this->calculateEnergyCost($trainingType);

            // Simulate training failure based on energy level
            $failureResult = $this->simulateFailure($character, $trainingType);

            // If training failed, reduce gains significantly
            $effectiveGains = $actualGains;
            if ($failureResult['failed']) {
                $effectiveGains = $this->applyFailurePenalty($actualGains);
            }

            // Update character stats (stats + SP)
            $this->updateCharacterStats($character, $effectiveGains);

            // Consume energy (or recover for rest-type)
            if ($energyCost > 0) {
                $this->stateService->consumeEnergy($character, $energyCost);
            } elseif ($energyCost < 0) {
                $character->energy_level = min(100, (int) $character->energy_level + abs($energyCost));
                $character->save();
            }

            // Progress turn and phase
            $turnResult = $this->stateService->progressTurn($character);

            // Update career phase/turn if career exists
            $career = $character->currentCareer;
            if ($career) {
                $career->current_turn = $turnResult['turn'];
                $newPhase = $turnResult['stage'];
                if (\in_array($newPhase, ['junior', 'classic', 'senior'], true)) {
                    /** @var 'junior'|'classic'|'senior' $newPhase */
                    $career->current_phase = $newPhase;
                }
                $career->save();
            }

            // Update bond levels if deck exists
            $bondUpdates = [];
            if ($activeDeck) {
                $activeCards = \is_array($bonuses['active_cards'] ?? null) ? $bonuses['active_cards'] : [];
                $participatingCards = [];
                foreach ($activeCards as $card) {
                    if (\is_array($card) && isset($card['id']) && is_numeric($card['id'])) {
                        $participatingCards[] = (int) $card['id'];
                    }
                }
                $bondResult = $this->bondService->updateBondLevels($activeDeck, $participatingCards);
                $bondUpdatesRaw = $bondResult['updated_cards'] ?? [];
                /** @var array<int, array<string, mixed>> $bondUpdates */
                $bondUpdates = \is_array($bondUpdatesRaw) ? $bondUpdatesRaw : [];
            }

            // Record skill hints (if any)
            $skillHints = $this->processSkillHints($character, $activeDeck, $bonuses);

            // Record training session
            $session = $this->recordTrainingSession(
                $character,
                $trainingType,
                $effectiveGains,
                $bonuses,
                $bondUpdates,
                $skillHints
            );

            // Check if career should end
            $maxTurns = $this->getMaxTurns($character);
            $careerCompleted = $turnResult['turn'] >= $maxTurns;

            if ($careerCompleted && $career) {
                $career->status = 'completed';
                $career->save();

                $character->status = 'completed';
                $character->save();
            }

            return [
                'success' => true,
                'training_failed' => $failureResult['failed'],
                'failure_message' => $failureResult['message'],
                'session' => $session,
                'stat_gains' => $effectiveGains,
                'bond_updates' => $bondUpdates,
                'skill_hints' => $skillHints,
                'is_friendship' => $bonuses['is_friendship'],
                'turn_result' => $turnResult,
                'career_completed' => $careerCompleted,
            ];
        });
    }

    /**
     * Update character stats and SP.
     *
     * @param  array<string, int>  $gains
     */
    protected function updateCharacterStats(Character $character, array $gains): void
    {
        $currentStats = $character->current_stats;

        foreach ($gains as $stat => $gain) {
            if ($stat === 'sp') {
                continue;
            }
            if (isset($currentStats[$stat])) {
                $currentStats[$stat] = min(1200, $currentStats[$stat] + $gain);
            }
        }

        $spGain = $gains['sp'] ?? 0;
        $character->fill([
            'current_stats' => $currentStats,
            'available_sp' => (int) $character->available_sp + $spGain,
        ]);
        $character->save();
    }

    /**
     * Process skill hints from training.
     *
     * @param  \App\Models\SupportDeck|null  $deck
     * @param  array<string, mixed>  $bonuses
     * @return array<int, array<string, mixed>>
     */
    protected function processSkillHints(Character $character, $deck, array $bonuses): array
    {
        if (! $deck) {
            return [];
        }

        $skillHints = [];

        // Simulate hint drops (in real implementation, this would be based on game mechanics)
        // For now, we'll use a simple probability based on bond levels
        $activeCards = \is_array($bonuses['active_cards'] ?? null) ? $bonuses['active_cards'] : [];
        foreach ($activeCards as $card) {
            if (! \is_array($card)) {
                continue;
            }
            $bondLevel = isset($card['bond']) && is_numeric($card['bond']) ? (int) $card['bond'] : 0;
            $hintProbability = min(0.3, $bondLevel / 300); // Max 30% at bond 90+

            // Random hint drop
            if (mt_rand(1, 100) <= ($hintProbability * 100)) {
                // In real implementation, this would select a skill from the card's skill pool
                // For now, we'll just record that a hint was obtained
                $cardId = isset($card['id']) && is_numeric($card['id']) ? (int) $card['id'] : 0;
                $cardName = isset($card['name']) && \is_string($card['name']) ? $card['name'] : 'Unknown';
                $skillHints[] = [
                    'card_id' => $cardId,
                    'card_name' => $cardName,
                    'bond_level' => $bondLevel,
                    'hint_obtained' => true,
                ];
            }
        }

        return $skillHints;
    }

    /**
     * Record training session.
     *
     * @param  array<string, int>  $gains
     * @param  array<string, mixed>  $bonuses
     * @param  array<int, array<string, mixed>>  $bondUpdates
     * @param  array<int, array<string, mixed>>  $skillHints
     */
    protected function recordTrainingSession(
        Character $character,
        string $trainingType,
        array $gains,
        array $bonuses,
        array $bondUpdates,
        array $skillHints
    ): TrainingSession {
        $career = $this->resolveCareerForTraining($character);
        $energyCost = $this->calculateEnergyCost($trainingType);
        $energyBefore = $character->energy_level ?? 100;
        $energyAfter = max(0, $energyBefore - $energyCost);
        $totalStatPoints = array_sum($gains);

        return TrainingSession::create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'turn_number' => $character->current_turn,
            'career_phase' => $character->career_stage,
            'training_type' => $trainingType,
            'speed_gain' => $gains['speed'] ?? 0,
            'stamina_gain' => $gains['stamina'] ?? 0,
            'power_gain' => $gains['power'] ?? 0,
            'guts_gain' => $gains['guts'] ?? 0,
            'wit_gain' => $gains['wit'] ?? 0,
            'sp_gain' => $gains['sp'] ?? 0,
            'energy_cost' => $energyCost,
            'energy_before' => $energyBefore,
            'energy_after' => $energyAfter,
            'total_stat_points_gained' => $totalStatPoints,
            'participating_support_cards' => $bonuses['active_cards'],
            'skill_hints_obtained' => $skillHints,
            'training_bonuses' => [
                'support_card_bonus' => $bonuses['final_bonus'],
                'friendship_bonus' => $bonuses['is_friendship'] ? 20 : 0,
                'total_multiplier' => $bonuses['friendship_multiplier'],
            ],
            'friendship_training' => $bonuses['is_friendship'],
            'friendship_level_bonus' => $bonuses['is_friendship'] ? 20 : 0,
        ]);
    }

    protected function resolveCareerForTraining(Character $character): Career
    {
        $career = $character->currentCareer;
        if ($career instanceof Career) {
            return $career;
        }

        return Career::query()->create([
            'user_id' => $character->user_id,
            'character_id' => $character->id,
            'star_level' => 3,
            'career_name' => $character->name.' Career',
            'scenario_type' => $character->scenario_type,
            'status' => 'active',
            'current_turn' => max(1, (int) $character->current_turn),
            'current_phase' => in_array($character->career_stage, ['junior', 'classic', 'senior'], true)
                    ? $character->career_stage
                    : 'junior',
            'started_at' => now()->toDateString(),
        ]);
    }

    /**
     * Calculate energy cost for a training type.
     */
    protected function calculateEnergyCost(string $trainingType): int
    {
        return match ($trainingType) {
            'rest' => -30,
            'recreation' => -20,
            'infirmary' => -10,
            'wit' => 10,
            default => 20,
        };
    }

    /**
     * Simulate training failure based on energy level.
     *
     * @return array{failed: bool, message: string, failure_rate: int}
     */
    protected function simulateFailure(Character $character, string $trainingType): array
    {
        $energyLevel = (int) ($character->energy_level ?? 100);

        $failureRate = match (true) {
            $energyLevel >= 50 => 0,
            $energyLevel >= 30 => 20,
            $energyLevel >= 10 => 40,
            default => 60,
        };

        if ($failureRate === 0) {
            return ['failed' => false, 'message' => '', 'failure_rate' => 0];
        }

        $roll = mt_rand(1, 100);
        $failed = $roll <= $failureRate;

        return [
            'failed' => $failed,
            'message' => $failed ? 'Training failed! Stats gained were reduced.' : '',
            'failure_rate' => $failureRate,
        ];
    }

    /**
     * Apply failure penalty to gains (50% reduction on failure).
     *
     * @param  array<string, int>  $gains
     * @return array<string, int>
     */
    protected function applyFailurePenalty(array $gains): array
    {
        return array_map(
            fn (int $gain): int => (int) round($gain * 0.5),
            $gains
        );
    }

    /**
     * Get the maximum number of turns for this character's scenario.
     */
    protected function getMaxTurns(Character $character): int
    {
        return match ($character->scenario_type) {
            'unity_cup' => self::MAX_TURNS_UNITY_CUP,
            default => self::MAX_TURNS_URA,
        };
    }

    /**
     * Get empty bonuses structure.
     *
     * @return array<string, mixed>
     */
    protected function getEmptyBonuses(): array
    {
        return [
            'base_bonus' => 0,
            'friendship_multiplier' => 1.0,
            'final_bonus' => 0,
            'is_friendship' => false,
            'friendship_card_count' => 0,
            'active_cards' => [],
            'total_cards' => 0,
        ];
    }
}
