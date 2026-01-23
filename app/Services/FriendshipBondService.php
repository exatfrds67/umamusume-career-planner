<?php

namespace App\Services;

use App\Models\CharacterSupportCard;
use Illuminate\Support\Facades\Log;

/**
 * Friendship Bond Service
 * Handles friendship level tracking, rainbow training availability,
 * and bond level impact on training effectiveness and skill hints
 */
class FriendshipBondService
{
    /**
     * Friendship threshold for rainbow training (80%)
     */
    private const RAINBOW_TRAINING_THRESHOLD = 80;

    /**
     * Maximum friendship level (100%)
     */
    private const MAX_FRIENDSHIP_LEVEL = 100;

    /**
     * Minimum friendship level (0%)
     */
    private const MIN_FRIENDSHIP_LEVEL = 0;

    /**
     * Friendship points gained per training session
     */
    private const FRIENDSHIP_POINTS_PER_TRAINING = 5;

    /**
     * Friendship bonus multiplier per participant (2 participants = +2, 3 participants = +3)
     */
    private const FRIENDSHIP_BONUS_PER_PARTICIPANT = 1;

    /**
     * Skill hint provision rate increase per 10% friendship
     */
    private const SKILL_HINT_RATE_PER_10_PERCENT = 0.02; // 2% per 10% friendship

    /**
     * Update friendship level after training session
     */
    public function updateFriendshipLevel(): array
        try {
            $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);
            $oldLevel = $characterSupportCard->friendship_level;

            // Calculate new friendship level
            $newLevel = min(
                self::MAX_FRIENDSHIP_LEVEL,
                $oldLevel + $pointsGained
            );

            $characterSupportCard->friendship_level = $newLevel;
            $characterSupportCard->save();

            $rainbowUnlocked = $oldLevel < self::RAINBOW_TRAINING_THRESHOLD && $newLevel >= self::RAINBOW_TRAINING_THRESHOLD;

            Log::info('Friendship level updated', [
                'character_support_card_id' => $characterSupportCardId,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'points_gained' => $pointsGained,
                'rainbow_unlocked' => $rainbowUnlocked,
            ]);

            return [
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'points_gained' => $pointsGained,
                'rainbow_training_available' => $newLevel >= self::RAINBOW_TRAINING_THRESHOLD,
                'rainbow_unlocked' => $rainbowUnlocked,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update friendship level', [
                'error' => $e->getMessage(),
                'character_support_card_id' => $characterSupportCardId,
            ]);

            throw $e;
        }
    }

    /**
     * Check if rainbow training is available for a card
     */
    public function isRainbowTrainingAvailable(int $characterSupportCardId): bool
    {
        $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);

        return $characterSupportCard->friendship_level >= self::RAINBOW_TRAINING_THRESHOLD;
    }

    /**
     * Get all cards with rainbow training available for a character
     */
    public function getRainbowTrainingCards(): array
        $cards = CharacterSupportCard::where('character_id', $characterId)
            ->where('friendship_level', '>=', self::RAINBOW_TRAINING_THRESHOLD)
            ->with('supportCard')
            ->get();

        return $cards->map(function ($card) {
            return [
                'id' => $card->id,
                'support_card_id' => $card->support_card_id,
                'card_name' => $card->supportCard->name,
                'card_type' => $card->supportCard->card_type,
                'friendship_level' => $card->friendship_level,
                'position_slot' => $card->position_slot,
            ];
        })->toArray();
    }

    /**
     * Calculate friendship training bonus based on participant count
     */
    public function calculateFriendshipBonus(int $participantCount): int
    {
        if ($participantCount < 2) {
            return 0;
        }

        return $participantCount * self::FRIENDSHIP_BONUS_PER_PARTICIPANT;
    }

    /**
     * Calculate total training bonus with friendship multipliers
     *
     * @param  array  $participants  Array of character_support_card_ids
     */
    public function calculateTrainingBonusWithFriendship(): array
        $rainbowParticipants = [];
        $totalFriendshipBonus = 0;

        foreach ($participants as $participantId) {
            $characterSupportCard = CharacterSupportCard::find($participantId);

            if ($characterSupportCard && $characterSupportCard->friendship_level >= self::RAINBOW_TRAINING_THRESHOLD) {
                $rainbowParticipants[] = [
                    'id' => $characterSupportCard->id,
                    'card_name' => $characterSupportCard->supportCard->name ?? 'Unknown',
                    'friendship_level' => $characterSupportCard->friendship_level,
                ];
            }
        }

        $rainbowCount = count($rainbowParticipants);

        if ($rainbowCount >= 2) {
            $totalFriendshipBonus = $this->calculateFriendshipBonus($rainbowCount);
        }

        return [
            'base_bonus' => $baseBonus,
            'friendship_bonus' => $totalFriendshipBonus,
            'total_bonus' => $baseBonus + $totalFriendshipBonus,
            'rainbow_participants' => $rainbowParticipants,
            'rainbow_count' => $rainbowCount,
            'is_rainbow_training' => $rainbowCount >= 2,
        ];
    }

    /**
     * Calculate skill hint provision rate based on bond level
     */
    public function calculateSkillHintRate(int $friendshipLevel, float $baseRate = 0.10): float
    {
        // Base rate is 10% (0.10)
        // Increase by 2% per 10% friendship
        $friendshipBonus = ($friendshipLevel / 10) * self::SKILL_HINT_RATE_PER_10_PERCENT;

        return min(1.0, $baseRate + $friendshipBonus);
    }

    /**
     * Get skill hint provision rate for a specific card
     */
    public function getSkillHintProvisionRate(): array
        $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);

        $baseRate = 0.10; // 10% base rate
        $currentRate = $this->calculateSkillHintRate($characterSupportCard->friendship_level, $baseRate);

        return [
            'character_support_card_id' => $characterSupportCardId,
            'friendship_level' => $characterSupportCard->friendship_level,
            'base_rate' => $baseRate,
            'current_rate' => $currentRate,
            'rate_increase' => $currentRate - $baseRate,
            'rate_percentage' => round($currentRate * 100, 2),
        ];
    }

    /**
     * Get friendship progression for a card
     */
    public function getFriendshipProgression(): array
        $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);

        $currentLevel = $characterSupportCard->friendship_level;
        $progressToRainbow = max(0, self::RAINBOW_TRAINING_THRESHOLD - $currentLevel);
        $progressToMax = max(0, self::MAX_FRIENDSHIP_LEVEL - $currentLevel);

        return [
            'character_support_card_id' => $characterSupportCardId,
            'current_level' => $currentLevel,
            'max_level' => self::MAX_FRIENDSHIP_LEVEL,
            'rainbow_threshold' => self::RAINBOW_TRAINING_THRESHOLD,
            'is_rainbow_available' => $currentLevel >= self::RAINBOW_TRAINING_THRESHOLD,
            'is_max_level' => $currentLevel >= self::MAX_FRIENDSHIP_LEVEL,
            'progress_to_rainbow' => $progressToRainbow,
            'progress_to_max' => $progressToMax,
            'percentage' => round(($currentLevel / self::MAX_FRIENDSHIP_LEVEL) * 100, 2),
        ];
    }

    /**
     * Get friendship overview for all cards in a character's deck
     */
    public function getDeckFriendshipOverview(): array
        $cards = CharacterSupportCard::where('character_id', $characterId)
            ->with('supportCard')
            ->orderBy('position_slot')
            ->get();

        $overview = [
            'character_id' => $characterId,
            'total_cards' => $cards->count(),
            'rainbow_available_count' => 0,
            'average_friendship' => 0,
            'cards' => [],
        ];

        if ($cards->isEmpty()) {
            return $overview;
        }

        $totalFriendship = 0;
        $rainbowCount = 0;

        foreach ($cards as $card) {
            $totalFriendship = ($totalFriendship ?? 0) + $card->friendship_level;

            if ($card->friendship_level >= self::RAINBOW_TRAINING_THRESHOLD) {
                $rainbowCount = ($rainbowCount ?? 0) + 1;
            }

            $overview['cards'][] = [
                'id' => $card->id,
                'support_card_id' => $card->support_card_id,
                'card_name' => $card->supportCard->name ?? 'Unknown',
                'card_type' => $card->supportCard->card_type ?? 'Unknown',
                'position_slot' => $card->position_slot,
                'friendship_level' => $card->friendship_level,
                'is_rainbow_available' => $card->friendship_level >= self::RAINBOW_TRAINING_THRESHOLD,
                'is_friend_card' => $card->is_friend_card,
            ];
        }

        $overview['rainbow_available_count'] = $rainbowCount;
        $overview['average_friendship'] = round($totalFriendship / $cards->count(), 2);

        return $overview;
    }

    /**
     * Bulk update friendship levels for multiple cards
     *
     * @param  array  $updates  Array of ['character_support_card_id' => points_gained]
     */
    public function bulkUpdateFriendshipLevels(): array
        $results = [
            'success' => 0,
            'failed' => 0,
            'updates' => [],
            'errors' => [],
        ];

        foreach ($updates as $cardId => $pointsGained) {
            try {
                $result = $this->updateFriendshipLevel($cardId, $pointsGained);
                $results['success']++;
                $results['updates'][$cardId] = $result;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][$cardId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Reset friendship level for a card
     */
    public function resetFriendshipLevel(int $characterSupportCardId): bool
    {
        try {
            $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);
            $characterSupportCard->friendship_level = self::MIN_FRIENDSHIP_LEVEL;
            $characterSupportCard->save();

            Log::info('Friendship level reset', [
                'character_support_card_id' => $characterSupportCardId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reset friendship level', [
                'error' => $e->getMessage(),
                'character_support_card_id' => $characterSupportCardId,
            ]);

            return false;
        }
    }

    /**
     * Set friendship level directly (for testing or admin purposes)
     *
     * @throws \InvalidArgumentException
     */
    public function setFriendshipLevel(int $characterSupportCardId, int $level): bool
    {
        // Validate level first (before try-catch)
        if ($level < self::MIN_FRIENDSHIP_LEVEL || $level > self::MAX_FRIENDSHIP_LEVEL) {
            throw new \InvalidArgumentException(
                'Friendship level must be between '.self::MIN_FRIENDSHIP_LEVEL.' and '.self::MAX_FRIENDSHIP_LEVEL
            );
        }

        try {
            $characterSupportCard = CharacterSupportCard::findOrFail($characterSupportCardId);

            $characterSupportCard->friendship_level = $level;
            $characterSupportCard->save();

            Log::info('Friendship level set', [
                'character_support_card_id' => $characterSupportCardId,
                'level' => $level,
            ]);

            return true;
        } catch (\InvalidArgumentException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to set friendship level', [
                'error' => $e->getMessage(),
                'character_support_card_id' => $characterSupportCardId,
                'level' => $level,
            ]);

            return false;
        }
    }
}
