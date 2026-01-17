<?php

namespace App\Services;

use App\Models\Character;
use InvalidArgumentException;

class CharacterStateService
{
    /**
     * Recover energy through resting.
     *
     * @return array Result of the rest action (recovered amount, success/fail/great success)
     */
    public function rest(Character $character): array
    {
        // Simple logic for now:
        // 0-60 energy: High chance of success (fixed 50 recovery)
        // 60-80 energy: Moderate chance (fixed 30 recovery)
        // 80+ energy: Low recovery (fixed 10)
        // TODO: Implement proper RNG logic like the game (Success/Failure/Great Success)

        $currentEnergy = $character->energy_level;
        $recovery = 0;
        $resultType = 'success'; // success, failure, great_success

        $rand = \rand(1, 100);

        // Basic probability logic simulation
        if ($currentEnergy < 50) {
            // High efficiency rest
            if ($rand <= 10) {
                $recovery = 70;
                $resultType = 'great_success';
                $this->updateMood($character, 1); // Great rest improves mood
            } else {
                $recovery = 50;
            }
        } elseif ($currentEnergy < 80) {
            // Normal efficiency
            if ($rand <= 5) {
                $recovery = 50;
                $resultType = 'great_success';
                $this->updateMood($character, 1);
            } elseif ($rand > 90) {
                $recovery = 10;
                $resultType = 'failure';
                $this->updateMood($character, -1); // Bad rest worsens mood
            } else {
                $recovery = 30;
            }
        } else {
            // Diminishing returns
            $recovery = 10;
        }

        // Apply recovery
        $character->energy_level = min(100, $character->energy_level + $recovery);
        $character->save();

        return [
            'recovered' => $recovery,
            'result' => $resultType,
            'new_energy' => $character->energy_level,
            'new_mood' => $character->mood_status,
        ];
    }

    /**
     * Consume energy for training or events.
     *
     * @return bool True if successful, triggers failure effects if low energy
     */
    public function consumeEnergy(Character $character, int $amount): bool
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Energy consumption must be positive');
        }

        // Check for failure risk (simplified)
        // In real game, training fails if energy is low.
        // Here we'll just allow it but maybe calculate failure risk later.

        $character->energy_level = max(0, $character->energy_level - $amount);
        $character->save();

        return true;
    }

    /**
     * Update mood by steps (improving or worsening).
     *
     * @param  int  $step  Positive to improve, negative to worsen
     * @return string New mood status
     */
    public function updateMood(Character $character, int $step): string
    {
        $moods = ['awful', 'bad', 'normal', 'good', 'great'];
        $currentKey = array_search($character->mood_status, $moods);

        // If current mood is invalid, default to normal
        if ($currentKey === false) {
            $currentKey = 2; // normal
        }

        $newKey = max(0, min(count($moods) - 1, $currentKey + $step));
        $character->mood_status = $moods[$newKey];
        $character->save();

        return $character->mood_status;
    }

    /**
     * Advance the turn counter and handle career stage progression.
     *
     * @return array Information about the turn progression
     */
    public function progressTurn(Character $character): array
    {
        $character->current_turn++;

        // Scenario lasts 3 years (Junior, Classic, Senior) + URA
        // Year 1: Junior (Tu 1-24)
        // Year 2: Classic (Tu 25-48)
        // Year 3: Senior (Tu 49-72)
        // URA: (Tu 73+)

        $oldStage = $character->career_stage;
        $newStage = $oldStage;

        if ($character->current_turn >= 73) {
            $newStage = 'ura'; // Custom stage for finale
        } elseif ($character->current_turn >= 49) {
            $newStage = 'senior';
        } elseif ($character->current_turn >= 25) {
            $newStage = 'classic';
        } else {
            $newStage = 'junior';
        }

        if ($newStage !== $oldStage) {
            $character->career_stage = $newStage;
        }

        $character->save();

        return [
            'turn' => $character->current_turn,
            'stage' => $character->career_stage,
            'stage_changed' => $newStage !== $oldStage,
        ];
    }

    /**
     * Check and apply conditions based on state.
     *
     * @return array Added/Removed conditions
     */
    public function checkCondition(Character $character): array
    {
        $added = [];
        $removed = [];

        $conditions = json_decode($character->conditions ?? '[]', true) ?: [];

        // Example: Low energy (<20) might give "Tired" condition (simplified)
        // In real game, conditions like "Overweight" come from events.
        // Here we'll just simulate a sanity check.

        // If energy is max, remove "Tired" if present
        if ($character->energy_level > 80) {
            if (($key = array_search('tired', $conditions)) !== false) {
                unset($conditions[$key]);
                $removed[] = 'tired';
            }
        }

        // Save back
        $character->conditions = json_encode(array_values($conditions));
        $character->save();

        return ['added' => $added, 'removed' => $removed];
    }
}
