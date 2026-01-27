<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportCardDefinition;
use Illuminate\Database\Seeder;

/**
 * Enrich Support Cards Seeder
 *
 * Applies detailed GameTora data and generates reasonable defaults
 * for all support cards in the database.
 */
class EnrichSupportCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Support Card Enrichment...');

        // Step 1: Apply detailed GameTora data from static file
        $this->applyGameToraData();

        // Step 2: Generate defaults for cards without detailed data
        $this->generateDefaultData();

        // Step 3: Apply meta tier rankings
        $this->applyMetaTierRankings();

        $this->command->info('Support Card Enrichment Complete!');
    }

    /**
     * Apply detailed data from gametora_support_cards.php
     */
    protected function applyGameToraData(): void
    {
        $this->command->info('Applying GameTora detailed data...');

        $gametoraData = require database_path('seeders/data/gametora_support_cards.php');
        $updated = 0;
        $notFound = 0;

        foreach ($gametoraData as $cardData) {
            $gametoraId = $cardData['gametora_id'] ?? null;
            if ($gametoraId === null) {
                continue;
            }

            // Find card by gametora_id or name
            $card = SupportCardDefinition::where('gametora_id', $gametoraId)->first();

            if ($card === null) {
                // Try to find by name
                $card = SupportCardDefinition::where('name', 'LIKE', '%'.($cardData['name'] ?? '').'%')
                    ->where('rarity', $cardData['rarity'] ?? 'SSR')
                    ->first();
            }

            if ($card === null) {
                $notFound++;

                continue;
            }

            // Apply the detailed data
            $this->applyCardData($card, $cardData);
            $updated++;
        }

        $this->command->info("  - Updated: {$updated} cards with GameTora data");
        $this->command->info("  - Not found: {$notFound} cards");
    }

    /**
     * Apply card data from GameTora array
     */
    protected function applyCardData(SupportCardDefinition $card, array $cardData): void
    {
        $updates = [];

        // Stat bonuses from stat_gains
        $statGains = $cardData['stat_gains'] ?? [];
        if (! empty($statGains)) {
            if (isset($statGains['speed'])) {
                $updates['speed_bonus'] = $statGains['speed'];
            }
            if (isset($statGains['stamina'])) {
                $updates['stamina_bonus'] = $statGains['stamina'];
            }
            if (isset($statGains['power'])) {
                $updates['power_bonus'] = $statGains['power'];
            }
            if (isset($statGains['guts'])) {
                $updates['guts_bonus'] = $statGains['guts'];
            }
            if (isset($statGains['wit'])) {
                $updates['wit_bonus'] = $statGains['wit'];
            }
        }

        // Training bonuses
        if (isset($cardData['friendship_bonus'])) {
            $updates['friendship_bonus'] = $cardData['friendship_bonus'];
        }
        if (isset($cardData['training_effectiveness'])) {
            $updates['training_effect_bonus'] = $cardData['training_effectiveness'];
        }
        if (isset($cardData['event_recovery'])) {
            $updates['event_recovery_bonus'] = $cardData['event_recovery'];
        }
        if (isset($cardData['event_effectiveness'])) {
            $updates['event_effect_bonus'] = $cardData['event_effectiveness'];
        }

        // Skill hints - convert to simple array of names
        $skillHints = $cardData['skill_hints'] ?? [];
        if (! empty($skillHints)) {
            $skillNames = array_map(fn ($h) => $h['name'] ?? '', $skillHints);
            $skillNames = array_filter($skillNames);
            $updates['skill_hints_provided'] = array_values($skillNames);
        }

        // Unique effects
        $uniqueEffects = $cardData['unique_effects'] ?? [];
        if (! empty($uniqueEffects)) {
            // Add event skills to unique effects
            $eventSkills = $cardData['event_skills'] ?? [];
            foreach ($eventSkills as $skill) {
                $rarity = ($skill['is_gold'] ?? false) ? '(Gold)' : '';
                $uniqueEffects[] = "Event Skill: {$skill['name']} {$rarity}";
            }
            $updates['unique_effects'] = $uniqueEffects;
        }

        // Store additional metadata
        $metadata = $card->card_metadata ?? [];
        $metadata['gametora_data'] = [
            'race_bonus' => $cardData['race_bonus'] ?? null,
            'fan_bonus' => $cardData['fan_bonus'] ?? null,
            'mood_effect' => $cardData['mood_effect'] ?? null,
            'initial_friendship_gauge' => $cardData['initial_friendship_gauge'] ?? null,
            'hint_levels' => $cardData['hint_levels'] ?? null,
            'hint_frequency' => $cardData['hint_frequency'] ?? null,
            'specialty_priority' => $cardData['specialty_priority'] ?? null,
            'event_skills' => $cardData['event_skills'] ?? [],
            'skill_hints_detailed' => $cardData['skill_hints'] ?? [],
        ];
        $updates['card_metadata'] = $metadata;

        if (! empty($updates)) {
            $card->update($updates);
        }
    }

    /**
     * Generate default data for cards without detailed GameTora data
     */
    protected function generateDefaultData(): void
    {
        $this->command->info('Generating default data for remaining cards...');

        // Get cards without detailed data
        $cards = SupportCardDefinition::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('friendship_bonus')
                    ->orWhere('friendship_bonus', 0);
            })
            ->get();

        $updated = 0;

        foreach ($cards as $card) {
            $defaults = $this->getDefaultsForCard($card);
            $card->update($defaults);
            $updated++;
        }

        $this->command->info("  - Generated defaults for: {$updated} cards");
    }

    /**
     * Get default values based on card type and rarity
     */
    protected function getDefaultsForCard(SupportCardDefinition $card): array
    {
        $rarity = $card->rarity ?? 'R';
        $cardType = $card->card_type ?? 'speed';

        // Base values by rarity
        $rarityMultipliers = [
            'SSR' => ['stat' => 6, 'friendship' => 20, 'training' => 10],
            'SR' => ['stat' => 4, 'friendship' => 15, 'training' => 5],
            'R' => ['stat' => 2, 'friendship' => 10, 'training' => 0],
        ];

        $multiplier = $rarityMultipliers[$rarity] ?? $rarityMultipliers['R'];

        // Default stat bonuses by card type
        $statDefaults = $this->getStatDefaultsByType($cardType, $multiplier['stat']);

        // Default skill hints by card type
        $skillHints = $this->getDefaultSkillHints($cardType);

        // Default unique effects by card type
        $uniqueEffects = $this->getDefaultUniqueEffects($cardType, $rarity);

        return [
            'speed_bonus' => $statDefaults['speed'] ?? 0,
            'stamina_bonus' => $statDefaults['stamina'] ?? 0,
            'power_bonus' => $statDefaults['power'] ?? 0,
            'guts_bonus' => $statDefaults['guts'] ?? 0,
            'wit_bonus' => $statDefaults['wit'] ?? 0,
            'friendship_bonus' => $multiplier['friendship'],
            'training_effect_bonus' => $multiplier['training'],
            'event_recovery_bonus' => $cardType === 'friend' ? 20 : 0,
            'event_effect_bonus' => $cardType === 'friend' ? 15 : 0,
            'skill_hints_provided' => $skillHints,
            'unique_effects' => $uniqueEffects,
        ];
    }

    /**
     * Get stat defaults by card type
     */
    protected function getStatDefaultsByType(string $cardType, int $primaryStat): array
    {
        $secondaryStat = max(1, (int) ($primaryStat / 3));

        return match ($cardType) {
            'speed' => ['speed' => $primaryStat, 'power' => $secondaryStat],
            'stamina' => ['stamina' => $primaryStat, 'guts' => $secondaryStat],
            'power' => ['power' => $primaryStat, 'stamina' => $secondaryStat],
            'guts' => ['guts' => $primaryStat, 'power' => $secondaryStat],
            'wit' => ['wit' => $primaryStat],
            'friend' => [], // Friend cards don't give stat bonuses
            default => ['speed' => $primaryStat],
        };
    }

    /**
     * Get default skill hints by card type
     */
    protected function getDefaultSkillHints(string $cardType): array
    {
        return match ($cardType) {
            'speed' => [
                'Straightaway Adept',
                'Corner Adept',
                'Speed Star',
                'Acceleration',
            ],
            'stamina' => [
                'Deep Breaths',
                'Extra Tank',
                'Stamina to Spare',
                'Corner Recovery',
            ],
            'power' => [
                'Prepared to Pass',
                'Homestretch Haste',
                'Outer Swell',
                'Slick Surge',
            ],
            'guts' => [
                'Steadfast',
                'Moxie',
                'Fighting Spirit',
                'Unruffled',
            ],
            'wit' => [
                'Nimble Navigator',
                'Shrewd Step',
                'Prudent Positioning',
                'Go with the Flow',
            ],
            'friend' => [], // Friend cards don't have skill hints
            default => [],
        };
    }

    /**
     * Get default unique effects by card type and rarity
     */
    protected function getDefaultUniqueEffects(string $cardType, string $rarity): array
    {
        if ($rarity === 'R') {
            return []; // R cards typically don't have unique effects
        }

        $baseEffects = match ($cardType) {
            'speed' => [
                'Increases Speed gain when training together',
                'Increases the effectiveness of Speed training',
            ],
            'stamina' => [
                'Increases Stamina gain when training together',
                'Increases the effectiveness of Stamina training',
            ],
            'power' => [
                'Increases Power gain when training together',
                'Increases the effectiveness of Power training',
            ],
            'guts' => [
                'Increases Guts gain when training together',
                'Increases the effectiveness of Guts training',
            ],
            'wit' => [
                'Increases Wit gain when training together',
                'Increases skill point gain when training together',
            ],
            'friend' => [
                'Decreases the probability of failure when training together',
                'Decreases Energy consumed when training together',
            ],
            default => [],
        };

        // SSR cards get both effects, SR cards get one
        return $rarity === 'SSR' ? $baseEffects : array_slice($baseEffects, 0, 1);
    }

    /**
     * Apply meta tier rankings to all cards
     */
    protected function applyMetaTierRankings(): void
    {
        $this->command->info('Applying meta tier rankings...');

        // Get comprehensive meta data
        $metaData = $this->getComprehensiveMetaData();
        $updated = 0;

        foreach ($metaData as $identifier => $data) {
            // Try to find card by gametora_id first
            $card = SupportCardDefinition::where('gametora_id', $identifier)->first();

            if ($card === null) {
                // Try by name pattern
                $namePart = str_replace('-', ' ', explode('-', $identifier, 2)[1] ?? '');
                $card = SupportCardDefinition::where('name', 'LIKE', "%{$namePart}%")->first();
            }

            if ($card !== null) {
                $card->update($data);
                $updated++;
            }
        }

        // Apply default tiers to remaining cards based on rarity
        $this->applyDefaultTiers();

        $this->command->info("  - Applied meta rankings to: {$updated} top-tier cards");
    }

    /**
     * Apply default tiers to cards without specific rankings
     */
    protected function applyDefaultTiers(): void
    {
        // SSR cards without tier get B tier
        SupportCardDefinition::where('rarity', 'SSR')
            ->whereNull('meta_tier')
            ->update(['meta_tier' => 'B']);

        // SR cards get C tier
        SupportCardDefinition::where('rarity', 'SR')
            ->whereNull('meta_tier')
            ->update(['meta_tier' => 'C']);

        // R cards get D tier
        SupportCardDefinition::where('rarity', 'R')
            ->whereNull('meta_tier')
            ->update(['meta_tier' => 'D']);
    }

    /**
     * Get comprehensive meta data for top-tier cards
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
                    'Chain event with guaranteed Practice Perfect option',
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
