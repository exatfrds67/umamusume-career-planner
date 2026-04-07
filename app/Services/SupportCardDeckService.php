<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Support Card Deck Service
 *
 * Handles deck validation (6 cards, unique) and bonus calculation.
 * Implements friendship bonuses and race-specific bonuses.
 *
 * Requirements: Task 3.3
 */
class SupportCardDeckService
{
    /**
     * Maximum cards allowed in a deck
     */
    protected const MAX_DECK_SIZE = 6;

    /**
     * Validate deck composition
     *
     * @param  array<int, array{card_id: int, position: int}>  $cards
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validateDeck(array $cards): array
    {
        $errors = [];
        $warnings = [];

        // Check deck size
        if (count($cards) > self::MAX_DECK_SIZE) {
            $errors[] = 'Deck cannot have more than 6 support cards.';
        }

        // Check for duplicates
        $cardIds = array_values(array_map(static fn (mixed $id): int => (int) $id, array_column($cards, 'card_id')));
        $uniqueIds = array_unique($cardIds);

        if (count($cardIds) !== count($uniqueIds)) {
            $errors[] = 'Deck cannot contain duplicate support cards.';
        }

        // Check positions are 1-6
        $positions = array_column($cards, 'position');
        foreach ($positions as $position) {
            if ($position < 1 || $position > 6) {
                $errors[] = "Invalid deck position: {$position}. Must be 1-6.";
            }
        }

        // Verify all cards exist
        $existingCards = SupportCardDefinition::whereIn('id', $cardIds)
            ->pluck('id')
            ->map(static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->toArray();
        /** @var array<int, int> $existingCards */
        $missingCards = array_diff($cardIds, $existingCards);
        if (! empty($missingCards)) {
            $errors[] = 'Some cards do not exist: '.implode(', ', array_map(static fn (mixed $id): string => (string) $id, $missingCards));
        }

        // Warning for suboptimal compositions
        if (count($cards) < self::MAX_DECK_SIZE) {
            $warnings[] = 'Deck has empty slots. Consider filling all 6 positions.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Save deck configuration
     *
     * @param  array<int, array{card_id: int, position: int, limit_break_level?: int, friendship_level?: int}>  $cards
     */
    public function saveDeck(Character $character, array $cards): bool
    {
        $validation = $this->validateDeck($cards);
        if (! $validation['valid']) {
            return false;
        }

        return DB::transaction(function () use ($character, $cards) {
            // Remove existing deck
            $character->supportCards()->delete();

            // Add new cards
            foreach ($cards as $cardData) {
                CharacterSupportCard::create([
                    'character_id' => $character->id,
                    'support_card_id' => $cardData['card_id'],
                    'deck_position' => $cardData['position'],
                    'limit_break_level' => $cardData['limit_break_level'] ?? 0,
                    'friendship_level' => $cardData['friendship_level'] ?? 0,
                ]);
            }

            return true;
        });
    }

    /**
     * Calculate total deck bonuses
     *
     * @return array{
     *     stat_bonuses: array<string, float>,
     *     friendship_bonus: float,
     *     race_bonus: float,
     *     training_bonus: float,
     *     total_multiplier: float
     * }
     */
    public function calculateDeckBonuses(Character $character): array
    {
        $character->load('supportCards.supportCard');

        $statBonuses = [
            'speed' => 0.0,
            'stamina' => 0.0,
            'power' => 0.0,
            'guts' => 0.0,
            'wit' => 0.0,
        ];

        $friendshipBonus = 0.0;
        $raceBonus = 0.0;
        $trainingBonus = 0.0;

        foreach ($character->supportCards as $deckCard) {
            $card = $deckCard->supportCard;
            if (! $card) {
                continue;
            }

            // Calculate stat bonuses based on card type
            $cardType = $card->card_type ?? 'speed';
            if (! array_key_exists($cardType, $statBonuses)) {
                $cardType = 'speed';
            }
            $limitBreak = $deckCard->limit_break_level ?? 0;

            // Base bonus per matching stat (5% + 2% per limit break level)
            $baseBonus = 0.05 + ($limitBreak * 0.02);
            $statBonuses[$cardType] += $baseBonus;

            // Friendship bonus (when friendship is at 80+)
            if (($deckCard->friendship_level ?? 0) >= 80) {
                $friendshipBonus += 0.05; // 5% per maxed friendship
            }

            // Race bonus from card effects
            $effects = $card->training_effects ?? [];
            if (isset($effects['race_bonus'])) {
                $raceBonus += is_numeric($effects['race_bonus']) ? (float) $effects['race_bonus'] : 0.0;
            }

            // Training bonus
            if (isset($effects['training_bonus'])) {
                $trainingBonus += is_numeric($effects['training_bonus']) ? (float) $effects['training_bonus'] : 0.0;
            }
        }

        $totalMultiplier = 1.0 + $friendshipBonus + $trainingBonus + array_sum($statBonuses);

        return [
            'stat_bonuses' => $statBonuses,
            'friendship_bonus' => $friendshipBonus,
            'race_bonus' => $raceBonus,
            'training_bonus' => $trainingBonus,
            'total_multiplier' => $totalMultiplier,
        ];
    }

    /**
     * Calculate synergy score for the deck
     *
     * @return float Score 0-100
     */
    public function calculateSynergyScore(Character $character): float
    {
        $character->load('supportCards.supportCard');
        $cards = $character->supportCards;

        if ($cards->isEmpty()) {
            return 0.0;
        }

        $score = 0.0;

        // Score for full deck
        if ($cards->count() === self::MAX_DECK_SIZE) {
            $score += 20.0;
        }

        // Score for type diversity
        $types = $cards->pluck('supportCard.card_type')->unique()->count();
        $score += min($types * 10.0, 30.0);

        // Score for limit breaks
        $avgLimitBreak = $cards->avg('limit_break_level') ?? 0;
        $score += $avgLimitBreak * 5.0; // Max 20 points

        // Score for friendship levels
        $avgFriendship = $cards->avg('friendship_level') ?? 0;
        $score += ($avgFriendship / 100) * 20.0;

        // Score for matching priority stats
        $priorities = $character->stat_priorities ?? [];
        if (! empty($priorities)) {
            arsort($priorities);
            $topPriority = array_key_first($priorities);

            $matchingCards = $cards->filter(function ($card) use ($topPriority) {
                return ($card->supportCard->card_type ?? '') === $topPriority;
            })->count();

            $score += min($matchingCards * 5.0, 10.0);
        }

        return min($score, 100.0);
    }

    /**
     * Get deck tier rating
     *
     * @return string S, A, B, C, D, or F
     */
    public function getDeckTier(float $synergyScore): string
    {
        return match (true) {
            $synergyScore >= 90 => 'S',
            $synergyScore >= 75 => 'A',
            $synergyScore >= 60 => 'B',
            $synergyScore >= 45 => 'C',
            $synergyScore >= 30 => 'D',
            default => 'F',
        };
    }

    /**
     * Get recommended cards based on character goals
     *
     * @return Collection<int, SupportCardDefinition>
     */
    public function getRecommendations(
        Character $character,
        ?string $focusStat = null,
        int $limit = 10
    ): Collection {
        $query = SupportCardDefinition::where('is_active', true);

        // Filter by focus stat if provided
        if ($focusStat) {
            $query->where('card_type', $focusStat);
        }

        // Exclude cards already in deck
        $existingCardIds = $character->supportCards()->pluck('support_card_id')->toArray();
        if (! empty($existingCardIds)) {
            $query->whereNotIn('id', $existingCardIds);
        }

        // Order by rarity and meta tier
        return $query
            ->orderByRaw("CASE rarity WHEN 'SSR' THEN 1 WHEN 'SR' THEN 2 WHEN 'R' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
