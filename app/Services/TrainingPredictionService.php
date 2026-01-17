<?php

namespace App\Services;

use App\Models\Character;

class TrainingPredictionService
{
    /**
     * Calculate projected stats gain for a specific training type.
     * Based on URA Finale scenario baseline (approximate).
     *
     * @param  string  $trainingType  'speed', 'stamina', 'power', 'guts', 'wit'
     * @return array ['stats' => ['speed' => int, ...], 'energy' => int]
     */
    public function calculateGain(Character $character, string $trainingType): array
    {
        // Base gains for Facility Level 1 (Simplified for now)
        // TODO: Factor in facility levels from character data if available

        $gains = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ];

        $energyCost = 0;

        switch ($trainingType) {
            case 'speed':
                $gains['speed'] = 10;
                $gains['power'] = 5;
                $energyCost = -20;
                break;
            case 'stamina':
                $gains['stamina'] = 10;
                $gains['guts'] = 5;
                $energyCost = -20;
                break;
            case 'power':
                $gains['power'] = 10;
                $gains['stamina'] = 5;
                $energyCost = -20;
                break;
            case 'guts':
                $gains['guts'] = 10;
                $gains['speed'] = 5;
                $gains['power'] = 2; // Small bonus sometimes?
                $energyCost = -20;
                break;
            case 'wit':
                $gains['wit'] = 10;
                $gains['speed'] = 5;
                $energyCost = 5; // Wit recovers energy
                break;
            default:
                // Unknown type, no gains
                break;
        }

        // Apply Growth Rates (e.g. +20% Speed)
        $growthRates = json_decode($character->growth_rates ?? '[]', true) ?: [];
        foreach ($gains as $stat => $value) {
            if ($value > 0 && isset($growthRates[$stat])) {
                $bonus = $growthRates[$stat]; // e.g., 20 for 20%
                $multiplier = 1 + ($bonus / 100);
                $gains[$stat] = floor($value * $multiplier);
            }
        }

        // Apply Mood Multiplier
        // Great: 1.2x, Good: 1.1x, Normal: 1.0x, Bad: 0.9x, Awful: 0.8x
        $moodMultiifiers = [
            'great' => 1.2,
            'good' => 1.1,
            'normal' => 1.0,
            'bad' => 0.9,
            'awful' => 0.8,
        ];
        $mood = $character->mood_status ?? 'normal';
        $multiplier = $moodMultiifiers[$mood] ?? 1.0;

        foreach ($gains as $stat => $value) {
            if ($value > 0) {
                $gains[$stat] = floor($value * $multiplier);
            }
        }

        return [
            'stats' => $gains,
            'energy' => $energyCost,
        ];
    }

    /**
     * Calculate failure failure rate percentage based on current energy.
     *
     * @return int Failure rate 0-100
     */
    public function calculateFailureRate(Character $character, string $trainingType = 'speed'): int
    {
        // Wit training usually has lower/no failure rate for energy, but let's standardize for now.
        // If training recovers energy (Wit), failure rate should be 0 or very low (just failing to learn).
        if ($trainingType === 'wit') {
            return 0; // Simplified
        }

        $energy = $character->energy_level;

        // Formula approximation:
        // Energy >= 50: 0% risk
        // Energy < 50: Risk increases as energy drops

        if ($energy >= 50) {
            return 0;
        }

        // Linear scaling from 50 down to 0
        // 50 energy -> 0%
        // 0 energy -> 70% risk?
        // Risk = (50 - energy) * 1.4

        $risk = (50 - $energy) * 1.4;

        return min(99, max(0, (int) $risk));
    }

    /**
     * Execute the training logic.
     *
     * @return array Result data
     */
    public function executeTraining(Character $character, string $trainingType): array
    {
        $prediction = $this->calculateGain($character, $trainingType);
        $failureRate = $this->calculateFailureRate($character, $trainingType);

        $rand = \rand(1, 100);
        $isSuccess = $rand > $failureRate;

        $resultData = [
            'success' => $isSuccess,
            'failure_rate' => $failureRate,
            'training_type' => $trainingType,
            'gains' => [],
            'energy_change' => 0,
        ];

        if ($isSuccess) {
            // Apply stats
            $currentStats = $character->current_stats;
            foreach ($prediction['stats'] as $stat => $gain) {
                if ($gain > 0) {
                    $currentStats[$stat] = min(1200, ($currentStats[$stat] ?? 0) + $gain);
                    $resultData['gains'][$stat] = $gain;
                }
            }
            $character->current_stats = $currentStats;

            // Apply energy cost
            // Ensure energy doesn't go below 0 or above 100
            $energyChange = $prediction['energy'];
            $newEnergy = $character->energy_level + $energyChange;
            $character->energy_level = max(0, min(100, $newEnergy));

            $resultData['energy_change'] = $energyChange;

        } else {
            // Failure!
            // Reduced/No stats, huge mood drop, maybe condition gained.
            // Simplified: -10 energy, -1 mood, no stats.

            $character->energy_level = max(0, $character->energy_level - 10);
            $resultData['energy_change'] = -10;

            // Worsen mood
            // We need CharacterStateService helper for this ideally, or logic duplicate
            // We'll simplisticly do it here or assume controller handles it?
            // Let's do it here.
            $moods = ['awful', 'bad', 'normal', 'good', 'great'];
            $currentKey = array_search($character->mood_status, $moods);
            if ($currentKey !== false && $currentKey > 0) {
                $character->mood_status = $moods[$currentKey - 1];
                $resultData['mood_change'] = -1;
            }
        }

        $character->save();

        return $resultData;
    }
}
