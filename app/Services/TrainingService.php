<?php

declare(strict_types=1);

namespace App\Services;

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
    public function __construct(
        protected SupportBonusCalculator $bonusCalculator,
        protected BondProgressionService $bondService,
        protected SkillHintService $hintService
    ) {}

    /**
     * Execute training and record results.
     *
     * @param  array<string, int>  $actualGains
     * @return array<string, mixed>
     */
    public function executeTraining(Character $character, string $trainingType, array $actualGains): array
    {
        return DB::transaction(function () use ($character, $trainingType, $actualGains) {
            $activeDeck = $character->activeSupportDeck;

            // Calculate bonuses if deck exists
            $bonuses = $activeDeck
                ? $this->bonusCalculator->calculateBonuses($activeDeck, $trainingType)
                : $this->getEmptyBonuses();

            // Update character stats
            $this->updateCharacterStats($character, $actualGains);

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
                $actualGains,
                $bonuses,
                $bondUpdates,
                $skillHints
            );

            return [
                'success' => true,
                'session' => $session,
                'stat_gains' => $actualGains,
                'bond_updates' => $bondUpdates,
                'skill_hints' => $skillHints,
                'is_friendship' => $bonuses['is_friendship'],
            ];
        });
    }

    /**
     * Update character stats.
     *
     * @param  array<string, int>  $gains
     */
    protected function updateCharacterStats(Character $character, array $gains): void
    {
        $currentStats = $character->current_stats;

        foreach ($gains as $stat => $gain) {
            if (isset($currentStats[$stat])) {
                $currentStats[$stat] = min(1200, $currentStats[$stat] + $gain);
            }
        }

        $character->update([
            'current_stats' => $currentStats,
            'current_turn' => $character->current_turn + 1,
        ]);
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
        $career = $character->currentCareer;

        return TrainingSession::create([
            'career_id' => $career?->id,
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
