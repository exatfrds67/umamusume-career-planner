<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\SupportDeck;
use Illuminate\Support\Facades\DB;

/**
 * Manage bond level progression for support cards.
 */
class BondProgressionService
{
    /**
     * Base bond gain per training.
     */
    private const BASE_BOND_GAIN = 5;

    /**
     * Bonus bond gain for low bond levels (< 50).
     */
    private const LOW_BOND_BONUS = 2;

    /**
     * Low bond threshold.
     */
    private const LOW_BOND_THRESHOLD = 50;

    /**
     * Maximum bond level.
     */
    private const MAX_BOND_LEVEL = 100;

    /**
     * Update bond levels for all cards in a deck after training.
     *
     * @param  array<int>  $participatingCardIds  IDs of cards that participated
     * @return array<string, mixed>
     */
    public function updateBondLevels(SupportDeck $deck, array $participatingCardIds = []): array
    {
        $updates = [];

        // If no specific cards provided, all cards in deck participate
        if (empty($participatingCardIds)) {
            $participatingCardIds = $deck->supportCards->pluck('id')->toArray();
        }

        foreach ($participatingCardIds as $cardId) {
            $card = $deck->supportCards()->wherePivot('support_card_id', $cardId)->first();

            if (! $card) {
                continue;
            }

            $currentBond = $card->pivot->bond_level ?? 0;

            // Skip if already at max
            if ($currentBond >= self::MAX_BOND_LEVEL) {
                continue;
            }

            // Calculate bond gain
            $bondGain = $this->calculateBondGain($currentBond);
            $newBond = min(self::MAX_BOND_LEVEL, $currentBond + $bondGain);

            // Update pivot table
            DB::table('support_deck_cards')
                ->where('support_deck_id', $deck->id)
                ->where('support_card_id', $cardId)
                ->update(['bond_level' => $newBond]);

            $updates[] = [
                'card_id' => $cardId,
                'card_name' => $card->name_en ?? $card->title_en ?? 'Unknown',
                'bond_before' => $currentBond,
                'bond_after' => $newBond,
                'bond_gain' => $bondGain,
                'reached_friendship' => $currentBond < 80 && $newBond >= 80,
            ];
        }

        return [
            'updated_cards' => $updates,
            'total_updated' => count($updates),
        ];
    }

    /**
     * Calculate bond gain based on current bond level.
     */
    private function calculateBondGain(int $currentBond): int
    {
        $gain = self::BASE_BOND_GAIN;

        // Bonus for low bond levels
        if ($currentBond < self::LOW_BOND_THRESHOLD) {
            $gain += self::LOW_BOND_BONUS;
        }

        return $gain;
    }

    /**
     * Set bond level for a specific card in a deck.
     */
    public function setBondLevel(SupportDeck $deck, int $cardId, int $bondLevel): bool
    {
        $bondLevel = max(0, min(self::MAX_BOND_LEVEL, $bondLevel));

        return DB::table('support_deck_cards')
            ->where('support_deck_id', $deck->id)
            ->where('support_card_id', $cardId)
            ->update(['bond_level' => $bondLevel]) > 0;
    }

    /**
     * Get bond level for a specific card in a deck.
     */
    public function getBondLevel(SupportDeck $deck, int $cardId): int
    {
        $result = DB::table('support_deck_cards')
            ->where('support_deck_id', $deck->id)
            ->where('support_card_id', $cardId)
            ->value('bond_level');

        return (int) ($result ?? 0);
    }

    /**
     * Get bond progression summary for a deck.
     *
     * @return array<string, mixed>
     */
    public function getBondSummary(SupportDeck $deck): array
    {
        $cards = $deck->supportCards;

        $totalBond = 0;
        $friendshipCards = 0;
        $maxBondCards = 0;

        foreach ($cards as $card) {
            $bondLevel = $card->pivot->bond_level ?? 0;
            $totalBond += $bondLevel;

            if ($bondLevel >= 80) {
                $friendshipCards++;
            }

            if ($bondLevel >= self::MAX_BOND_LEVEL) {
                $maxBondCards++;
            }
        }

        $cardCount = $cards->count();
        $averageBond = $cardCount > 0 ? round($totalBond / $cardCount, 1) : 0;

        return [
            'total_cards' => $cardCount,
            'average_bond' => $averageBond,
            'friendship_cards' => $friendshipCards,
            'max_bond_cards' => $maxBondCards,
            'has_friendship_training' => $friendshipCards >= 3,
        ];
    }
}
