<?php

namespace App\Services;

use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Support Card Meta Service
 * Handles meta tier rankings, updates, and strategic analysis
 */
class SupportCardMetaService
{
    /**
     * Cache duration for meta data (1 week)
     */
    private const CACHE_DURATION = 604800;

    /**
     * Get all support cards grouped by meta tier
     *
     * @return array<string, Collection>
     */
    public function getCardsByTier(): array
    {
        return Cache::remember('support_cards_by_tier', self::CACHE_DURATION, function () {
            return [
                'S+' => SupportCardDefinition::where('meta_tier', 'S+')
                    ->where('is_active', true)
                    ->orderBy('usage_rate', 'desc')
                    ->get(),
                'S' => SupportCardDefinition::where('meta_tier', 'S')
                    ->where('is_active', true)
                    ->orderBy('usage_rate', 'desc')
                    ->get(),
                'A' => SupportCardDefinition::where('meta_tier', 'A')
                    ->where('is_active', true)
                    ->orderBy('usage_rate', 'desc')
                    ->get(),
                'B' => SupportCardDefinition::where('meta_tier', 'B')
                    ->where('is_active', true)
                    ->orderBy('usage_rate', 'desc')
                    ->get(),
                'C' => SupportCardDefinition::where('meta_tier', 'C')
                    ->where('is_active', true)
                    ->orderBy('usage_rate', 'desc')
                    ->get(),
            ];
        });
    }

    /**
     * Get top cards by card type
     */
    public function getTopCardsByType(string $cardType, int $limit = 5): Collection
    {
        return Cache::remember("top_cards_{$cardType}_{$limit}", self::CACHE_DURATION, function () use ($cardType, $limit) {
            return SupportCardDefinition::where('card_type', $cardType)
                ->where('is_active', true)
                ->orderBy('usage_rate', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Update meta tier for a support card
     */
    public function updateMetaTier(int $cardId, string $newTier, array $metadata = []): bool
    {
        try {
            $card = SupportCardDefinition::findOrFail($cardId);
            $oldTier = $card->meta_tier;

            $card->meta_tier = $newTier;

            if (isset($metadata['usage_rate'])) {
                $card->usage_rate = $metadata['usage_rate'];
            }

            if (isset($metadata['win_rate_contribution'])) {
                $card->win_rate_contribution = $metadata['win_rate_contribution'];
            }

            if (isset($metadata['performance_data'])) {
                $card->performance_data = array_merge(
                    $card->performance_data ?? [],
                    $metadata['performance_data']
                );
            }

            $card->save();

            // Clear cache
            Cache::forget('support_cards_by_tier');
            Cache::forget("top_cards_{$card->card_type}_5");

            Log::info("Meta tier updated for {$card->name}", [
                'card_id' => $cardId,
                'old_tier' => $oldTier,
                'new_tier' => $newTier,
                'metadata' => $metadata,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update meta tier for card {$cardId}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get skill provision mapping for a card
     */
    public function getSkillProvisionMapping(int $cardId): array
    {
        $card = SupportCardDefinition::findOrFail($cardId);

        return [
            'card_name' => $card->name,
            'card_type' => $card->card_type,
            'skills_provided' => $card->skill_hints_provided ?? [],
            'guaranteed_events' => $card->guaranteed_events ?? [],
            'special_conditions' => $card->special_conditions ?? [],
        ];
    }

    /**
     * Get all skill provision mappings
     */
    public function getAllSkillProvisionMappings(): Collection
    {
        return Cache::remember('all_skill_provision_mappings', self::CACHE_DURATION, function () {
            return SupportCardDefinition::where('is_active', true)
                ->get()
                ->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'name' => $card->name,
                        'card_type' => $card->card_type,
                        'skills_provided' => $card->skill_hints_provided ?? [],
                        'meta_tier' => $card->meta_tier,
                    ];
                });
        });
    }

    /**
     * Find cards that provide a specific skill
     */
    public function findCardsBySkill(string $skillName): Collection
    {
        return SupportCardDefinition::where('is_active', true)
            ->whereJsonContains('skill_hints_provided', $skillName)
            ->orderBy('meta_tier')
            ->orderBy('usage_rate', 'desc')
            ->get();
    }

    /**
     * Get recommended cards for a scenario
     */
    public function getRecommendedCardsForScenario(string $scenario): Collection
    {
        return Cache::remember("recommended_cards_{$scenario}", self::CACHE_DURATION, function () use ($scenario) {
            return SupportCardDefinition::where('is_active', true)
                ->whereJsonContains('recommended_scenarios', $scenario)
                ->orderBy('usage_rate', 'desc')
                ->get();
        });
    }

    /**
     * Get card synergies
     */
    public function getCardSynergies(int $cardId): array
    {
        $card = SupportCardDefinition::findOrFail($cardId);

        $synergyCardNames = $card->deck_synergies ?? [];

        $synergyCards = SupportCardDefinition::whereIn('name', $synergyCardNames)
            ->where('is_active', true)
            ->get();

        return [
            'card_name' => $card->name,
            'synergy_cards' => $synergyCards->map(function ($synergyCard) {
                return [
                    'id' => $synergyCard->id,
                    'name' => $synergyCard->name,
                    'card_type' => $synergyCard->card_type,
                    'meta_tier' => $synergyCard->meta_tier,
                    'rarity' => $synergyCard->rarity,
                ];
            }),
        ];
    }

    /**
     * Bulk update meta tiers from external source
     */
    public function bulkUpdateMetaTiers(array $updates): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($updates as $update) {
            if (! isset($update['internal_id']) || ! isset($update['meta_tier'])) {
                $results['failed']++;
                $results['errors'][] = 'Missing required fields: internal_id or meta_tier';

                continue;
            }

            $card = SupportCardDefinition::where('internal_id', $update['internal_id'])->first();

            if (! $card) {
                $results['failed']++;
                $results['errors'][] = "Card not found: {$update['internal_id']}";

                continue;
            }

            if ($this->updateMetaTier($card->id, $update['meta_tier'], $update['metadata'] ?? [])) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to update: {$update['internal_id']}";
            }
        }

        return $results;
    }

    /**
     * Clear all meta-related caches
     */
    public function clearMetaCache(): void
    {
        Cache::forget('support_cards_by_tier');
        Cache::forget('all_skill_provision_mappings');

        $cardTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'];
        foreach ($cardTypes as $type) {
            Cache::forget("top_cards_{$type}_5");
        }

        $scenarios = ['URA Finale', 'Unity Cup'];
        foreach ($scenarios as $scenario) {
            Cache::forget("recommended_cards_{$scenario}");
        }

        Log::info('Meta cache cleared successfully');
    }
}
