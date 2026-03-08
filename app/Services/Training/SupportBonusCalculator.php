<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\SupportDeck;

/**
 * Calculate stat bonuses from support cards during training.
 */
class SupportBonusCalculator
{
    /**
     * Base bonus percentages by rarity.
     */
    private const RARITY_BONUSES = [
        'SSR' => 10,
        'SR' => 7,
        'R' => 5,
    ];

    /**
     * Per-card flat bonus percentage (+5% per card in deck, max +30% for 6 cards).
     */
    private const PER_CARD_BONUS = 5;

    /**
     * Friendship training multiplier (bond >= 80).
     */
    private const FRIENDSHIP_MULTIPLIER = 1.2;

    /**
     * Friendship training threshold.
     */
    private const FRIENDSHIP_THRESHOLD = 80;

    /**
     * Calculate bonuses for a specific training type.
     *
     * @param  string  $trainingType  (speed, stamina, power, guts, wit)
     * @return array<string, mixed>
     */
    public function calculateBonuses(SupportDeck $deck, string $trainingType): array
    {
        $cards = $deck->supportCards;

        if ($cards->isEmpty()) {
            return $this->emptyBonusResult();
        }

        $rarityBonus = 0;
        $perCardBonus = 0;
        $activeCards = [];
        $friendshipCount = 0;

        foreach ($cards as $card) {
            $bondLevel = $card->pivot->bond_level ?? 0;
            $rarity = $card->rarity ?? 'R';

            // Calculate rarity-based bonus for this card
            $baseBonus = $this->getBaseBonusForCard($rarity, $trainingType);

            // Apply limit break multiplier if available
            $limitBreakMultiplier = $this->getLimitBreakMultiplier($card);
            $cardBonus = $baseBonus * $limitBreakMultiplier;

            $rarityBonus += $cardBonus;

            // Per-card +5% bonus when specialization matches training type (or friend card)
            $cardSpecialization = $card->card_type ?? $card->specialization ?? null;
            if ($this->cardMatchesTrainingType($cardSpecialization, $trainingType)) {
                $perCardBonus += self::PER_CARD_BONUS;
            }

            // Track friendship cards
            if ($bondLevel >= self::FRIENDSHIP_THRESHOLD) {
                $friendshipCount++;
            }

            $activeCards[] = [
                'id' => $card->id,
                'name' => $card->name_en ?? $card->title_en ?? 'Unknown Card',
                'rarity' => $rarity,
                'bonus' => $cardBonus,
                'bond' => $bondLevel,
                'is_friendship' => $bondLevel >= self::FRIENDSHIP_THRESHOLD,
                'per_card_bonus' => $this->cardMatchesTrainingType($cardSpecialization, $trainingType) ? self::PER_CARD_BONUS : 0,
            ];
        }

        $totalBonus = $rarityBonus + $perCardBonus;

        // Apply friendship training multiplier if threshold met
        $isFriendship = $friendshipCount >= 3; // Need at least 3 cards at 80+ bond
        $friendshipMultiplier = $isFriendship ? self::FRIENDSHIP_MULTIPLIER : 1.0;
        $finalBonus = $totalBonus * $friendshipMultiplier;

        return [
            'base_bonus' => round($totalBonus, 2),
            'rarity_bonus' => round($rarityBonus, 2),
            'per_card_bonus' => $perCardBonus,
            'friendship_multiplier' => $friendshipMultiplier,
            'final_bonus' => round($finalBonus, 2),
            'is_friendship' => $isFriendship,
            'friendship_card_count' => $friendshipCount,
            'active_cards' => $activeCards,
            'total_cards' => count($activeCards),
        ];
    }

    /**
     * Get base bonus for a card based on rarity and training type.
     */
    private function getBaseBonusForCard(string $rarity, string $trainingType): float
    {
        return (float) (self::RARITY_BONUSES[$rarity] ?? self::RARITY_BONUSES['R']);
    }

    /**
     * Check if a card's specialization matches the training type.
     * Friend/pal cards match all training types.
     */
    private function cardMatchesTrainingType(?string $cardSpecialization, string $trainingType): bool
    {
        if ($cardSpecialization === null) {
            return false;
        }

        $normalized = strtolower($cardSpecialization);

        if ($normalized === 'friend' || $normalized === 'pal') {
            return true;
        }

        return $normalized === strtolower($trainingType);
    }

    /**
     * Get limit break multiplier for a card.
     *
     * @param  \App\Models\SupportCard  $card
     */
    private function getLimitBreakMultiplier($card): float
    {
        // Limit break stars (0-4) add 10% per star
        $limitBreak = is_numeric($card->limit_break ?? null) ? (int) $card->limit_break : 0;

        return 1.0 + ($limitBreak * 0.1);
    }

    /**
     * Return empty bonus result.
     *
     * @return array<string, mixed>
     */
    private function emptyBonusResult(): array
    {
        return [
            'base_bonus' => 0,
            'rarity_bonus' => 0,
            'per_card_bonus' => 0,
            'friendship_multiplier' => 1.0,
            'final_bonus' => 0,
            'is_friendship' => false,
            'friendship_card_count' => 0,
            'active_cards' => [],
            'total_cards' => 0,
        ];
    }

    /**
     * Apply bonuses to base stat gains.
     *
     * @param  array<string, int>  $baseGains
     * @param  array<string, mixed>  $bonuses
     * @return array<string, int>
     */
    public function applyBonusesToGains(array $baseGains, array $bonuses): array
    {
        $finalBonus = isset($bonuses['final_bonus']) && is_numeric($bonuses['final_bonus']) ? (float) $bonuses['final_bonus'] : 0.0;
        $multiplier = 1.0 + ($finalBonus / 100);

        return array_map(
            fn ($gain) => (int) round($gain * $multiplier),
            $baseGains
        );
    }
}
