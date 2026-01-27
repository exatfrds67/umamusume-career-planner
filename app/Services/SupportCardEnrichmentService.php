<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupportCardDefinition;
use Illuminate\Support\Facades\Log;

/**
 * Support Card Enrichment Service
 *
 * Enriches support card data by combining information from multiple sources:
 * - umapyoi.net API (basic card info)
 * - GameTora (detailed effects, skills, events)
 * - Static meta data (tier rankings, strategic notes)
 */
class SupportCardEnrichmentService
{
    protected GameToraScraperService $gameToraService;

    protected CharacterMappingService $characterMapping;

    public function __construct(
        ?GameToraScraperService $gameToraService = null,
        ?CharacterMappingService $characterMapping = null
    ) {
        $this->gameToraService = $gameToraService ?? new GameToraScraperService;
        $this->characterMapping = $characterMapping ?? new CharacterMappingService;
    }

    /**
     * Enrich a single card with GameTora data
     */
    public function enrichCard(SupportCardDefinition $card): bool
    {
        $gametoraId = $card->gametora_id;

        if (empty($gametoraId)) {
            return false;
        }

        $gametoraData = $this->gameToraService->fetchCardDetails($gametoraId);

        if ($gametoraData === null) {
            return false;
        }

        return $this->applyEnrichment($card, $gametoraData);
    }

    /**
     * Enrich all cards with GameTora data
     *
     * @return array{enriched: int, failed: int, skipped: int}
     */
    public function enrichAllCards(?callable $progressCallback = null): array
    {
        $cards = SupportCardDefinition::whereNotNull('gametora_id')
            ->where('gametora_id', '!=', '')
            ->get();

        $enriched = 0;
        $failed = 0;
        $skipped = 0;
        $total = $cards->count();
        $current = 0;

        foreach ($cards as $card) {
            $current++;

            if ($progressCallback !== null) {
                $progressCallback($current, $total, $card->name);
            }

            // Skip cards that already have detailed data
            if ($this->hasDetailedData($card)) {
                $skipped++;

                continue;
            }

            if ($this->enrichCard($card)) {
                $enriched++;
            } else {
                $failed++;
            }
        }

        return [
            'enriched' => $enriched,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Apply enrichment data to card
     *
     * @param  array<string, mixed>  $gametoraData
     */
    protected function applyEnrichment(SupportCardDefinition $card, array $gametoraData): bool
    {
        try {
            $updates = [];

            // Apply effects
            $effects = \is_array($gametoraData['effects'] ?? null) ? $gametoraData['effects'] : [];
            if (! empty($effects)) {
                if (isset($effects['friendship_bonus']) && is_numeric($effects['friendship_bonus'])) {
                    $updates['friendship_bonus'] = (int) $effects['friendship_bonus'];
                }
                if (isset($effects['training_effectiveness']) && is_numeric($effects['training_effectiveness'])) {
                    $updates['training_effect_bonus'] = (int) $effects['training_effectiveness'];
                }
                if (isset($effects['event_recovery']) && is_numeric($effects['event_recovery'])) {
                    $updates['event_recovery_bonus'] = (int) $effects['event_recovery'];
                }
                if (isset($effects['event_effectiveness']) && is_numeric($effects['event_effectiveness'])) {
                    $updates['event_effect_bonus'] = (int) $effects['event_effectiveness'];
                }
            }

            // Apply support hints as skill_hints_provided
            $hints = \is_array($gametoraData['support_hints'] ?? null) ? $gametoraData['support_hints'] : [];
            if (! empty($hints)) {
                $skillNames = [];
                foreach ($hints as $h) {
                    if (\is_array($h) && isset($h['name']) && \is_string($h['name']) && $h['name'] !== '') {
                        $skillNames[] = $h['name'];
                    }
                }
                if (! empty($skillNames)) {
                    $updates['skill_hints_provided'] = $skillNames;
                }
            }

            // Apply event skills to unique_effects
            $eventSkills = \is_array($gametoraData['event_skills'] ?? null) ? $gametoraData['event_skills'] : [];
            if (! empty($eventSkills)) {
                $existingEffects = \is_array($card->unique_effects) ? $card->unique_effects : [];
                $skillEffects = [];
                foreach ($eventSkills as $s) {
                    if (\is_array($s) && isset($s['name']) && \is_string($s['name'])) {
                        $rarity = (isset($s['rarity']) && $s['rarity'] === 'gold') ? '(Gold)' : '';
                        $skillEffects[] = "Event Skill: {$s['name']} {$rarity}";
                    }
                }
                $updates['unique_effects'] = array_merge($existingEffects, $skillEffects);
            }

            // Apply unique effects text
            $uniqueText = \is_array($gametoraData['unique_effects_text'] ?? null) ? $gametoraData['unique_effects_text'] : [];
            if (! empty($uniqueText)) {
                $existingEffects = \is_array($updates['unique_effects'] ?? null) ? $updates['unique_effects'] : (\is_array($card->unique_effects) ? $card->unique_effects : []);
                $updates['unique_effects'] = array_unique(array_merge($existingEffects, $uniqueText));
            }

            // Apply events to card_metadata
            $events = \is_array($gametoraData['events'] ?? null) ? $gametoraData['events'] : [];
            $chainEvents = \is_array($events['chain_events'] ?? null) ? $events['chain_events'] : [];
            $randomEvents = \is_array($events['random_events'] ?? null) ? $events['random_events'] : [];
            if (! empty($chainEvents) || ! empty($randomEvents)) {
                $metadata = \is_array($card->card_metadata) ? $card->card_metadata : [];
                $metadata['chain_events'] = $chainEvents;
                $metadata['random_events'] = $randomEvents;
                $updates['card_metadata'] = $metadata;
            }

            if (! empty($updates)) {
                $card->update($updates);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::warning('[SupportCardEnrichmentService] Failed to apply enrichment', [
                'card_id' => $card->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if card already has detailed data
     */
    protected function hasDetailedData(SupportCardDefinition $card): bool
    {
        // Consider card enriched if it has skill hints and effects
        $hasSkills = ! empty($card->skill_hints_provided) && count($card->skill_hints_provided) > 0;
        $hasEffects = ! empty($card->unique_effects) && count($card->unique_effects) > 0;
        $hasBonuses = ($card->friendship_bonus ?? 0) > 0 || ($card->training_effect_bonus ?? 0) > 0;

        return $hasSkills && $hasEffects && $hasBonuses;
    }

    /**
     * Apply static meta data to cards
     *
     * @return array{updated: int, skipped: int}
     */
    public function applyMetaData(): array
    {
        $metaData = $this->getComprehensiveMetaData();
        $updated = 0;
        $skipped = 0;

        foreach ($metaData as $identifier => $data) {
            // Try to find card by various identifiers
            $card = SupportCardDefinition::where('name', 'LIKE', "%{$identifier}%")
                ->orWhere('gametora_id', 'LIKE', "%{$identifier}%")
                ->first();

            if ($card === null) {
                $skipped++;

                continue;
            }

            try {
                $card->update($data);
                $updated++;
            } catch (\Exception $e) {
                Log::warning('[SupportCardEnrichmentService] Failed to apply meta data', [
                    'identifier' => $identifier,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        return [
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Get comprehensive meta data for top-tier cards
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getComprehensiveMetaData(): array
    {
        return [
            // SS+ Tier - Best in slot
            '30028-kitasan-black' => [
                'meta_tier' => 'S+',
                'usage_rate' => 98.5,
                'win_rate_contribution' => 96.2,
                'strategic_notes' => [
                    'Best all-around Speed Card',
                    'Very high Specialty Priority leads to frequent Speed Friendship Training',
                    'Professor of Curvature is consistent Gold Velocity Skill',
                    'Good energy recovery and Mood events',
                    'Performs best at LB3 or MLB',
                ],
                'deck_synergies' => ['Fine Motion', 'Super Creek', 'Tazuna Hayakawa'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup', 'Grand Masters'],
            ],
            '30016-super-creek' => [
                'meta_tier' => 'S+',
                'usage_rate' => 96.8,
                'win_rate_contribution' => 94.5,
                'strategic_notes' => [
                    'All-around best Stamina card',
                    'Swinging Maestro provides game-breaking stamina recovery',
                    'Works on any distance',
                    'Essential for competitive play',
                ],
                'deck_synergies' => ['Kitasan Black', 'Rice Shower', 'Mejiro McQueen'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup', 'Grand Masters'],
            ],
            '30010-fine-motion' => [
                'meta_tier' => 'S+',
                'usage_rate' => 95.3,
                'win_rate_contribution' => 93.1,
                'strategic_notes' => [
                    'Flexible for any deck',
                    'Has skills for Pace Chasers, notably Speed Star',
                    'Chain event with guaranteed Practice Perfect ◯ option',
                    'Good Training Effectiveness',
                ],
                'deck_synergies' => ['Kitasan Black', 'Super Creek', 'Tokai Teio'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
            '30021-tazuna-hayakawa' => [
                'meta_tier' => 'S+',
                'usage_rate' => 94.7,
                'win_rate_contribution' => 92.8,
                'strategic_notes' => [
                    'Essential for training management',
                    'Makes training easier with energy recovery',
                    'Tail Held High is good in general',
                    'Concentration is great for Front Runners',
                    'Allows healing all bad conditions in 2 chain events',
                ],
                'deck_synergies' => ['Any deck benefits'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup', 'Grand Masters'],
            ],
            '30020-biko-pegasus' => [
                'meta_tier' => 'S+',
                'usage_rate' => 91.2,
                'win_rate_contribution' => 89.5,
                'strategic_notes' => [
                    'Very high Training Effectiveness',
                    'Acts as stat stick for non-Speed training',
                    'Skills oriented towards Mile and Sprint',
                    'Good energy recovery events',
                ],
                'deck_synergies' => ['Kitasan Black', 'Fine Motion'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],

            // S Tier - Elite choices
            '30023-rice-shower' => [
                'meta_tier' => 'S',
                'usage_rate' => 88.4,
                'win_rate_contribution' => 86.7,
                'strategic_notes' => [
                    'Power card that gives Swinging Maestro',
                    'Option for stamina management at shorter distances',
                    'Scenario-linked for Unity Cup',
                    'Good energy recovery events',
                ],
                'deck_synergies' => ['Super Creek', 'El Condor Pasa'],
                'recommended_scenarios' => ['Unity Cup', 'URA Finale'],
            ],
            '30036-riko-kashimoto' => [
                'meta_tier' => 'S',
                'usage_rate' => 87.9,
                'win_rate_contribution' => 85.8,
                'strategic_notes' => [
                    'Great Energy recovery and Mood events',
                    'Gives Stamina and Guts',
                    'Scenario-linked to Unity Cup',
                    'Essential Pal card for consistency',
                ],
                'deck_synergies' => ['Tazuna Hayakawa'],
                'recommended_scenarios' => ['Unity Cup'],
            ],
            '30002-silence-suzuka' => [
                'meta_tier' => 'S',
                'usage_rate' => 84.1,
                'win_rate_contribution' => 82.4,
                'strategic_notes' => [
                    'Large amount of Front Runner yellow skills',
                    'Specialized for Front Runner style',
                    'Has Front Runner Savvy',
                ],
                'deck_synergies' => ['Twin Turbo', 'Kitasan Black'],
                'recommended_scenarios' => ['URA Finale'],
            ],
            '30044-narita-brian' => [
                'meta_tier' => 'S',
                'usage_rate' => 82.3,
                'win_rate_contribution' => 80.7,
                'strategic_notes' => [
                    'Easy-to-get story Speed Card',
                    'Gives Lone Wolf',
                    'Useful skills for Medium-Long distance',
                    'Good for Pace Chasers',
                ],
                'deck_synergies' => ['Fine Motion', 'Mejiro McQueen'],
                'recommended_scenarios' => ['URA Finale'],
            ],

            // A Tier - Strong options
            '30001-special-week' => [
                'meta_tier' => 'A',
                'usage_rate' => 76.8,
                'win_rate_contribution' => 74.5,
                'strategic_notes' => [
                    'All-rounder card',
                    'Flexible for Pace Chasers with benefit for Late Surgers',
                    'Good amount of blue recovery skills',
                ],
                'deck_synergies' => ['Fine Motion', 'Super Creek'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
            '30003-tokai-teio' => [
                'meta_tier' => 'A',
                'usage_rate' => 75.2,
                'win_rate_contribution' => 73.1,
                'strategic_notes' => [
                    'Good for Pace Chaser style',
                    'Has Rushing Gale!',
                    'Best for Mile and Medium distances',
                ],
                'deck_synergies' => ['Fine Motion', 'El Condor Pasa'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
            '30007-el-condor-pasa' => [
                'meta_tier' => 'A',
                'usage_rate' => 74.6,
                'win_rate_contribution' => 72.8,
                'strategic_notes' => [
                    'Good Training Effectiveness',
                    'High Specialty Priority',
                    'Lot of yellow skills for Medium-focused Pace Chasers',
                    'Has navigational skill Hawkeye',
                ],
                'deck_synergies' => ['Tokai Teio', 'Fine Motion'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
            '30022-mejiro-mcqueen' => [
                'meta_tier' => 'A',
                'usage_rate' => 73.4,
                'win_rate_contribution' => 71.6,
                'strategic_notes' => [
                    'Can give Cooldown',
                    'Good for Long distance races',
                    'Essential for Long distance builds',
                    'Good recovery skills for Long-focused Pace Chasers',
                ],
                'deck_synergies' => ['Super Creek', 'Special Week'],
                'recommended_scenarios' => ['URA Finale'],
            ],
            '30026-twin-turbo' => [
                'meta_tier' => 'A',
                'usage_rate' => 72.1,
                'win_rate_contribution' => 70.3,
                'strategic_notes' => [
                    'Large amount of Front Runner yellow skills',
                    'Has Moxie for Stamina Recovery',
                    'Useful event skills for Front Runners',
                    'Has Taking the Lead',
                ],
                'deck_synergies' => ['Silence Suzuka', 'Kitasan Black'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],

            // B Tier - Solid options
            '30005-vodka' => [
                'meta_tier' => 'B',
                'usage_rate' => 65.3,
                'win_rate_contribution' => 63.8,
                'strategic_notes' => [
                    'Budget Power option',
                    'Good for beginners',
                    'Decent Power training bonuses',
                ],
                'deck_synergies' => ['El Condor Pasa', 'Rice Shower'],
                'recommended_scenarios' => ['URA Finale'],
            ],
            '30006-grass-wonder' => [
                'meta_tier' => 'B',
                'usage_rate' => 64.7,
                'win_rate_contribution' => 62.9,
                'strategic_notes' => [
                    'Budget Stamina option',
                    'Alternative to Super Creek',
                    'Decent Stamina training bonuses',
                ],
                'deck_synergies' => ['Super Creek', 'Mejiro McQueen'],
                'recommended_scenarios' => ['URA Finale'],
            ],
            '30019-haru-urara' => [
                'meta_tier' => 'B',
                'usage_rate' => 63.2,
                'win_rate_contribution' => 61.5,
                'strategic_notes' => [
                    'Good energy recovery events',
                    'Mood improvement',
                    'Useful for energy management',
                ],
                'deck_synergies' => ['Tazuna Hayakawa', 'Riko Kashimoto'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
            '30037-symboli-rudolf' => [
                'meta_tier' => 'B',
                'usage_rate' => 62.8,
                'win_rate_contribution' => 60.9,
                'strategic_notes' => [
                    'Budget Wit option',
                    'Good for skill activation builds',
                    'Decent Wit training bonuses',
                ],
                'deck_synergies' => ['Fine Motion'],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
            ],
        ];
    }
}
