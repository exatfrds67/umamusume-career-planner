<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupportDeckService
{
    public const DECK_SIZE = 6;

    public const MAX_FRIEND_CARDS = 1;

    /**
     * Validate deck composition
     *
     * @param  array<int, array{support_card_id: int, is_friend_card: bool}>  $cards
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    public function validateDeck(array $cards): array
    {
        /** @var array<int, string> $errors */
        $errors = [];
        /** @var array<int, string> $warnings */
        $warnings = [];

        // Validate count
        if (count($cards) !== self::DECK_SIZE) {
            $errors[] = 'Deck must contain exactly '.self::DECK_SIZE.' cards';
        }

        // Count friend cards
        $friendCardCount = collect($cards)->where('is_friend_card', true)->count();
        if ($friendCardCount > self::MAX_FRIEND_CARDS) {
            $errors[] = 'Deck can only have '.self::MAX_FRIEND_CARDS.' friend card';
        }

        // Check for duplicates (excluding friend cards)
        $ownedCardIds = collect($cards)
            ->where('is_friend_card', false)
            ->pluck('support_card_id')
            ->toArray();

        if (count($ownedCardIds) !== count(array_unique($ownedCardIds))) {
            $errors[] = 'Deck cannot contain duplicate cards (except friend cards)';
        }

        // Load cards to check specialization diversity
        /** @var array<int> $cardIds */
        $cardIds = collect($cards)->pluck('support_card_id')->unique()->toArray();
        $supportCards = SupportCardDefinition::whereIn('id', $cardIds)->get();

        /** @var array<int, string> $specializations */
        $specializations = $supportCards->pluck('card_type')->toArray();
        $uniqueSpecs = count(array_unique($specializations));

        if ($uniqueSpecs < 3) {
            $warnings[] = 'Recommend at least 3 different specializations for balanced training';
        }

        // Check for excessive same-type cards
        $typeCounts = array_count_values($specializations);
        foreach ($typeCounts as $type => $count) {
            if ($count > 2 && $type !== 'friend') {
                $warnings[] = "You have {$count} {$type} cards. Consider diversifying for better coverage";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Save deck configuration for a character
     *
     * @param  array<int, array{support_card_id: int, is_friend_card: bool, limit_break_level?: int, friendship_level?: int}>  $cards
     */
    public function saveDeck(Character $character, array $cards): bool
    {
        // Validate first
        $validation = $this->validateDeck($cards);
        if (! $validation['valid']) {
            return false;
        }

        DB::beginTransaction();

        try {
            // Remove existing deck
            $character->supportCards()->delete();

            // Create new deck
            foreach ($cards as $position => $cardData) {
                CharacterSupportCard::create([
                    'character_id' => $character->id,
                    'support_card_id' => $cardData['support_card_id'],
                    'position_slot' => $position + 1,
                    'is_friend_card' => $cardData['is_friend_card'] ?? false,
                    'limit_break_level' => $cardData['limit_break_level'] ?? 0,
                    'friendship_level' => $cardData['friendship_level'] ?? 0,
                ]);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    /**
     * Get deck recommendations based on character goals
     *
     * @return Collection<int, SupportCardDefinition>
     */
    public function getRecommendations(Character $character, ?string $focusStat = null): Collection
    {
        $query = SupportCardDefinition::where('is_active', true)
            ->whereIn('meta_tier', ['S+', 'S', 'A']);

        // If focus stat specified, prioritize those cards
        if ($focusStat && in_array($focusStat, ['speed', 'stamina', 'power', 'guts', 'wit'])) {
            $query->where('card_type', $focusStat);
        }

        // Use CASE WHEN for SQLite compatibility instead of FIELD
        $cards = $query->orderBy('usage_rate', 'desc')
            ->limit(12)
            ->get();

        // Sort by tier manually for SQLite compatibility
        return $cards->sortBy(function ($card) {
            $tierOrder = ['S+' => 1, 'S' => 2, 'A' => 3, 'B' => 4, 'C' => 5];

            return $tierOrder[$card->meta_tier] ?? 99;
        })->values();
    }

    /**
     * Calculate deck tier rating
     */
    public function calculateDeckTier(Character $character): string
    {
        $deck = $character->supportCards()->with('supportCard')->get();

        if ($deck->isEmpty()) {
            return 'Unranked';
        }

        $tierScores = [
            'S+' => 5,
            'S' => 4,
            'A' => 3,
            'B' => 2,
            'C' => 1,
        ];

        $totalScore = 0;
        $cardCount = 0;

        foreach ($deck as $characterCard) {
            if ($characterCard->supportCard) {
                $tier = $characterCard->supportCard->meta_tier;
                if (is_string($tier) && array_key_exists($tier, $tierScores)) {
                    $totalScore += $tierScores[$tier];
                }
                $cardCount++;
            }
        }

        if ($cardCount === 0) {
            return 'Unranked';
        }

        $avgScore = $totalScore / $cardCount;

        if ($avgScore >= 4.5) {
            return 'S+';
        }
        if ($avgScore >= 3.5) {
            return 'S';
        }
        if ($avgScore >= 2.5) {
            return 'A';
        }
        if ($avgScore >= 1.5) {
            return 'B';
        }

        return 'C';
    }
}
