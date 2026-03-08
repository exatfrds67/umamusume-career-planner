<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\SupportDeck;
use App\Services\Training\SupportBonusCalculator;

/**
 * Training Prediction Service
 *
 * Provides training predictions with support card bonuses.
 */
class TrainingPredictionService
{
    public function __construct(
        protected SupportBonusCalculator $bonusCalculator
    ) {}

    /**
     * Get training predictions for all facilities.
     *
     * @return array<string, mixed>
     */
    public function getPredictions(Character $character): array
    {
        $activeDeck = $character->activeSupportDeck;

        if (! $activeDeck) {
            return $this->getBasePredictions($character);
        }

        return $this->getPredictionsWithSupport($character, $activeDeck);
    }

    /**
     * Get prediction for a specific training facility.
     *
     * @return array<string, mixed>
     */
    public function getPredictionForFacility(Character $character, string $facility): array
    {
        $activeDeck = $character->activeSupportDeck;

        if (! $activeDeck) {
            return $this->getBasePredictionForFacility($character, $facility);
        }

        return $this->getPredictionWithSupport($character, $activeDeck, $facility);
    }

    /**
     * Get base predictions without support cards.
     *
     * @return array<string, mixed>
     */
    protected function getBasePredictions(Character $character): array
    {
        $facilities = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $predictions = [];

        foreach ($facilities as $facility) {
            $predictions[$facility] = $this->getBasePredictionForFacility($character, $facility);
        }

        return [
            'has_support_deck' => false,
            'predictions' => $predictions,
        ];
    }

    /**
     * Get predictions with support card bonuses.
     *
     * @return array<string, mixed>
     */
    protected function getPredictionsWithSupport(Character $character, SupportDeck $deck): array
    {
        $facilities = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $predictions = [];

        foreach ($facilities as $facility) {
            $predictions[$facility] = $this->getPredictionWithSupport($character, $deck, $facility);
        }

        return [
            'has_support_deck' => true,
            'deck_id' => $deck->id,
            'predictions' => $predictions,
        ];
    }

    /**
     * Get base prediction for a specific facility.
     *
     * @return array<string, mixed>
     */
    protected function getBasePredictionForFacility(Character $character, string $facility): array
    {
        $baseGains = $this->calculateBaseGains($character, $facility);

        return [
            'facility' => $facility,
            'base_gains' => $baseGains,
            'final_gains' => $baseGains,
            'support_bonus' => 0,
            'is_friendship' => false,
            'active_cards' => [],
        ];
    }

    /**
     * Get prediction with support card bonuses for a specific facility.
     *
     * @return array<string, mixed>
     */
    protected function getPredictionWithSupport(Character $character, SupportDeck $deck, string $facility): array
    {
        $baseGains = $this->calculateBaseGains($character, $facility);
        $bonuses = $this->bonusCalculator->calculateBonuses($deck, $facility);
        $finalGains = $this->bonusCalculator->applyBonusesToGains($baseGains, $bonuses);

        return [
            'facility' => $facility,
            'base_gains' => $baseGains,
            'final_gains' => $finalGains,
            'support_bonus' => $bonuses['final_bonus'],
            'is_friendship' => $bonuses['is_friendship'],
            'friendship_card_count' => $bonuses['friendship_card_count'],
            'active_cards' => $bonuses['active_cards'],
            'total_cards' => $bonuses['total_cards'],
        ];
    }

    /**
     * Calculate base stat gains for a facility.
     *
     * @return array<string, int>
     */
    protected function calculateBaseGains(Character $character, string $facility): array
    {
        // Base gains depend on facility type and character's growth rates
        $growthRates = $character->growth_rates ?? [];
        $baseMultiplier = 1.0;

        // Apply growth rate multiplier if available
        if (isset($growthRates[$facility]) && is_numeric($growthRates[$facility])) {
            $baseMultiplier = (float) $growthRates[$facility];
        }

        // Define base gains per facility (these are game mechanics)
        // SP is earned with every training session
        $facilityGains = [
            'speed' => ['speed' => 20, 'power' => 5, 'sp' => 3],
            'stamina' => ['stamina' => 20, 'guts' => 5, 'sp' => 3],
            'power' => ['power' => 20, 'speed' => 5, 'sp' => 3],
            'guts' => ['guts' => 20, 'wit' => 5, 'sp' => 3],
            'wit' => ['wit' => 20, 'stamina' => 5, 'sp' => 5],
        ];

        $gains = $facilityGains[$facility] ?? [];

        // Apply growth rate multiplier to primary stat
        if (isset($gains[$facility])) {
            $gains[$facility] = (int) round($gains[$facility] * $baseMultiplier);
        }

        return $gains;
    }

    /**
     * Get recommended training facility based on character goals.
     *
     * @return array<string, mixed>
     */
    public function getRecommendedTraining(Character $character): array
    {
        $predictions = $this->getPredictions($character);
        $goals = \is_array($character->goals) ? $character->goals : [];
        $targetStats = \is_array($goals['target_stats'] ?? null) ? $goals['target_stats'] : [];
        $currentStats = \is_array($character->current_stats) ? $character->current_stats : [];

        if (empty($targetStats)) {
            // No goals set, recommend based on lowest stat
            return $this->recommendByLowestStat($predictions, $currentStats);
        }

        // Calculate stat gaps
        $statGaps = [];
        foreach ($targetStats as $stat => $target) {
            if (! \is_string($stat) || ! is_numeric($target)) {
                continue;
            }
            $current = isset($currentStats[$stat]) && is_numeric($currentStats[$stat]) ? (int) $currentStats[$stat] : 0;
            $gap = max(0, (int) $target - $current);
            $statGaps[$stat] = $gap;
        }

        // Find facility that best addresses largest gap
        return $this->recommendByStatGaps($predictions, $statGaps);
    }

    /**
     * Recommend training based on lowest stat.
     *
     * @param  array<string, mixed>  $predictions
     * @param  array<string, int>  $currentStats
     * @return array<string, mixed>
     */
    protected function recommendByLowestStat(array $predictions, array $currentStats): array
    {
        $lowestStat = null;
        $lowestValue = PHP_INT_MAX;

        foreach ($currentStats as $stat => $value) {
            if ($value < $lowestValue) {
                $lowestValue = $value;
                $lowestStat = $stat;
            }
        }

        $recommendedFacility = $lowestStat ?? 'speed';
        $predictionsArray = \is_array($predictions['predictions'] ?? null) ? $predictions['predictions'] : [];
        $prediction = $predictionsArray[$recommendedFacility] ?? null;

        return [
            'recommended_facility' => $recommendedFacility,
            'reason' => "Lowest stat: {$lowestStat} ({$lowestValue})",
            'prediction' => $prediction,
        ];
    }

    /**
     * Recommend training based on stat gaps.
     *
     * @param  array<string, mixed>  $predictions
     * @param  array<string, int>  $statGaps
     * @return array<string, mixed>
     */
    protected function recommendByStatGaps(array $predictions, array $statGaps): array
    {
        // Find stat with largest gap
        $largestGap = 0;
        $targetStat = null;

        foreach ($statGaps as $stat => $gap) {
            if ($gap > $largestGap) {
                $largestGap = $gap;
                $targetStat = $stat;
            }
        }

        $predictionsArray = \is_array($predictions['predictions'] ?? null) ? $predictions['predictions'] : [];

        if (! $targetStat) {
            // All goals met, train lowest stat
            return [
                'recommended_facility' => 'speed',
                'reason' => 'All goals met',
                'prediction' => $predictionsArray['speed'] ?? null,
            ];
        }

        $recommendedFacility = $targetStat;
        $prediction = $predictionsArray[$recommendedFacility] ?? null;

        return [
            'recommended_facility' => $recommendedFacility,
            'reason' => "Largest gap: {$targetStat} (need {$largestGap} more)",
            'prediction' => $prediction,
        ];
    }
}
