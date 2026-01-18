<?php

namespace Database\Seeders;

use App\Models\SupportCardDefinition;
use Illuminate\Database\Seeder;

/**
 * Support Card Seeder - Umamusume Pretty Derby Global English Server
 *
 * Data sourced from Game8.co official English tier lists (January 2026)
 * Only includes verified support cards from the Global English server
 *
 * Source: https://game8.co/games/Umamusume-Pretty-Derby/archives/536715
 */
class SupportCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SS-Tier Support Cards (Meta tier - highest competitive value)
        $ssTierCards = $this->getSSTierCards();
        foreach ($ssTierCards as $cardData) {
            SupportCardDefinition::updateOrCreate(
                ['internal_id' => $cardData['internal_id']],
                $cardData
            );
        }

        // S-Tier Support Cards (Elite competitive choices)
        $sTierCards = $this->getSTierCards();
        foreach ($sTierCards as $cardData) {
            SupportCardDefinition::updateOrCreate(
                ['internal_id' => $cardData['internal_id']],
                $cardData
            );
        }

        // A-Tier Support Cards (Strong viable options)
        $aTierCards = $this->getATierCards();
        foreach ($aTierCards as $cardData) {
            SupportCardDefinition::updateOrCreate(
                ['internal_id' => $cardData['internal_id']],
                $cardData
            );
        }

        // B-Tier Support Cards (Solid options for specific builds)
        $bTierCards = $this->getBTierCards();
        foreach ($bTierCards as $cardData) {
            SupportCardDefinition::updateOrCreate(
                ['internal_id' => $cardData['internal_id']],
                $cardData
            );
        }

        $this->command->info('Support card database seeded successfully!');
        $this->command->info('Total cards: '.(count($ssTierCards) + count($sTierCards) + count($aTierCards) + count($bTierCards)));
    }

    /**
     * SS-Tier Support Cards
     * Source: Game8.co January 2026 tier list
     * These are the absolute best cards in the Global English server
     */
    private function getSSTierCards(): array
    {
        return [
            // Kitasan Black - Best Speed card in game
            [
                'name' => 'Kitasan Black [Fire at My Heels]',
                'internal_id' => 'GLOBAL_SC_KITASAN_FIRE',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Kitasan Black',
                'character_internal_id' => 'CHAR_KITASAN',
                'speed_bonus' => 1,
                'power_bonus' => 1,
                'training_effect_bonus' => 15, // 10+5% at MLB
                'friendship_bonus' => 25, // 25% at MLB
                'meta_tier' => 'S+',
                'artwork_url' => '/images/support_cards/Kitasan_Black_Fire_at_My_Heels.png',
                'skill_hints_provided' => [
                    'Professor of Curvature',
                    'Corner Adept ◯',
                    'Corner Recovery ◯',
                    'Dodging Danger',
                    'Extra Tank',
                    'Focus',
                    'Front Runner Straightaways ◯',
                    'Long Corners ◯',
                    'Straightaway Recovery',
                    'Straightaway Adept',
                ],
                'unique_effects' => [
                    'Highest Specialty Priority for Speed cards (100 at MLB)',
                    'Training Effectiveness and Specialty Priority boost',
                    'Mood Effect 30% at MLB',
                    'Initial Friendship 35 at MLB',
                ],
                'strategic_notes' => [
                    'Best all-around Speed Card',
                    'Very high Specialty Priority leads to frequent Speed Friendship Training',
                    'Professor of Curvature is consistent Gold Velocity Skill',
                    'Good energy recovery and Mood events',
                    'Can provide Practice Perfect ◯',
                    'Performs best at LB3 or MLB',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Fine Motion', 'Super Creek', 'Tazuna Hayakawa'],
                'usage_rate' => 98.5,
                'win_rate_contribution' => 96.2,
                'is_active' => true,
                'release_date' => '2025-07-16',
            ],
            // Super Creek - Best Stamina card
            [
                'name' => 'Super Creek [Piece of Mind]',
                'internal_id' => 'GLOBAL_SC_SUPERCREEK_PIECE',
                'card_type' => 'stamina',
                'rarity' => 'SSR',
                'character_name' => 'Super Creek',
                'character_internal_id' => 'CHAR_SUPERCREEK',
                'stamina_bonus' => 1,
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'meta_tier' => 'S+',
                'artwork_url' => '/images/support_cards/Super_Creek_Piece_of_Mind.png',
                'skill_hints_provided' => [
                    'Swinging Maestro',
                    'Stamina recovery skills',
                ],
                'unique_effects' => [
                    'Gives Swinging Maestro - best gold recovery skill',
                    'Good Training Effectiveness with modest Specialty Priority',
                ],
                'strategic_notes' => [
                    'All-around best Stamina card',
                    'Swinging Maestro provides game-breaking stamina recovery',
                    'Works on any distance',
                    'Essential for competitive play',
                ],
                'usage_rate' => 96.8,
                'win_rate_contribution' => 94.5,
                'is_active' => true,
            ],

            // Fine Motion - Flexible Speed/Wit card
            [
                'name' => 'Fine Motion [Wave of Gratitude]',
                'internal_id' => 'GLOBAL_SC_FINEMOTION_WAVE',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Fine Motion',
                'character_internal_id' => 'CHAR_FINEMOTION',
                'speed_bonus' => 1,
                'wit_bonus' => 25, // Initial Wit at MLB
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'meta_tier' => 'S+',
                'artwork_url' => '/images/support_cards/Fine_Motion_Wave_of_Gratitude.jpg',
                'skill_hints_provided' => [
                    'Speed Star',
                    'Right-Handed ◯',
                    'Fall Runner ◯',
                    'Outer Post Proficiency ◯',
                    'Straightaway Acceleration',
                    'Nimble Navigator',
                    'Prepared to Pass',
                    'Corner Adept ◯',
                ],
                'unique_effects' => [
                    'Increases Friendship Training effectiveness (10%)',
                    'Increases initial Friendship Gauge (15)',
                    'Initial Wit 25 at MLB',
                    'Race Bonus 5%',
                    'Fan Bonus 15%',
                    'Wit Friendship Recovery 3',
                    'Specialty Priority 20',
                ],
                'strategic_notes' => [
                    'Flexible for any deck',
                    'Has skills for Pace Chasers, notably Speed Star',
                    'Chain event with guaranteed Practice Perfect ◯ option',
                    'Good Training Effectiveness',
                    'Works for sprint, mile, and medium distances',
                ],
                'usage_rate' => 95.3,
                'win_rate_contribution' => 93.1,
                'is_active' => true,
                'release_date' => '2025-06-26',
            ],
            // Tazuna Hayakawa - Best Friend card
            [
                'name' => 'Tazuna Hayakawa [Tracen Reception]',
                'internal_id' => 'GLOBAL_SC_TAZUNA_RECEPTION',
                'card_type' => 'friend',
                'rarity' => 'SSR',
                'character_name' => 'Tazuna Hayakawa',
                'character_internal_id' => 'CHAR_TAZUNA',
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 15,
                'event_effect_bonus' => 15,
                'meta_tier' => 'S+',
                'artwork_url' => '/images/support_cards/Tazuna_Hayakawa_Tracen_Reception.jpg',
                'skill_hints_provided' => [
                    'Tail Held High',
                    'Concentration',
                ],
                'unique_effects' => [
                    'Great Energy recovery and Mood events',
                    'Reduces failure rates',
                    'Allows healing all bad conditions in 2 chain events',
                ],
                'strategic_notes' => [
                    'Essential for training management',
                    'Makes training easier with energy recovery',
                    'Tail Held High is good in general',
                    'Concentration is great for Front Runners',
                ],
                'usage_rate' => 94.7,
                'win_rate_contribution' => 92.8,
                'is_active' => true,
            ],

            // Biko Pegasus - High Training Effectiveness Speed card
            [
                'name' => 'Biko Pegasus [Double Carrot Punch!]',
                'internal_id' => 'GLOBAL_SC_BIKOPEGASUS_CARROT',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Biko Pegasus',
                'character_internal_id' => 'CHAR_BIKOPEGASUS',
                'speed_bonus' => 1,
                'training_effect_bonus' => 15, // Very high
                'friendship_bonus' => 20,
                'meta_tier' => 'S+',
                'skill_hints_provided' => [
                    'Mile and Sprint oriented skills',
                ],
                'unique_effects' => [
                    'Very high Training Effectiveness',
                    'Great card to float and boost other training stats',
                ],
                'strategic_notes' => [
                    'Acts as stat stick for non-Speed training',
                    'Skills oriented towards Mile and Sprint',
                    'Good energy recovery events',
                ],
                'usage_rate' => 91.2,
                'win_rate_contribution' => 89.5,
                'is_active' => true,
            ],
        ];
    }

    /**
     * S-Tier Support Cards
     * Source: Game8.co January 2026 tier list
     * Elite competitive choices for the Global English server
     */
    private function getSTierCards(): array
    {
        return [
            // Rice Shower - Power card with Swinging Maestro
            [
                'name' => 'Rice Shower [Happiness Just around the Bend]',
                'internal_id' => 'GLOBAL_SC_RICESHOWER_HAPPINESS',
                'card_type' => 'power',
                'rarity' => 'SSR',
                'character_name' => 'Rice Shower',
                'character_internal_id' => 'CHAR_RICESHOWER',
                'power_bonus' => 1,
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'meta_tier' => 'S',
                'skill_hints_provided' => [
                    'Swinging Maestro',
                    'Cooldown',
                ],
                'unique_effects' => [
                    'Power card that gives Swinging Maestro',
                    'High Training Effectiveness and Friendship Bonus at MLB',
                    'Good Specialty Priority',
                    'Scenario-linked for Unity Cup',
                ],
                'strategic_notes' => [
                    'Option for stamina management at shorter distances without Stamina card',
                    'Rewards Cooldown at Unity Cup scenario finals',
                    'Good energy recovery events',
                ],
                'usage_rate' => 88.4,
                'win_rate_contribution' => 86.7,
                'is_active' => true,
            ],

            // Riko Kashimoto - Friend card
            [
                'name' => 'Riko Kashimoto [Planned Perfection]',
                'internal_id' => 'GLOBAL_SC_RIKOKASHIMOTO_PLANNED',
                'card_type' => 'friend',
                'rarity' => 'SSR',
                'character_name' => 'Riko Kashimoto',
                'character_internal_id' => 'CHAR_RIKOKASHIMOTO',
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 15,
                'event_effect_bonus' => 15,
                'meta_tier' => 'S',
                'skill_hints_provided' => [
                    'Energy recovery skills',
                ],
                'unique_effects' => [
                    'Great Energy recovery and Mood events',
                    'Gives Stamina and Guts',
                    'Scenario-linked to Unity Cup',
                    'Decreases failure rate and energy consumption',
                ],
                'strategic_notes' => [
                    'More gains in Unity Cup scenario',
                    'Essential Pal card for consistency',
                ],
                'usage_rate' => 87.9,
                'win_rate_contribution' => 85.8,
                'is_active' => true,
            ],
            // Sweep Tosho - Speed card with Charming and Lone Wolf
            [
                'name' => 'Sweep Tosho [Lamplit Training of a Witch-to-Be]',
                'internal_id' => 'GLOBAL_SC_SWEEPTOSHO_LAMPLIT',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Sweep Tosho',
                'character_internal_id' => 'CHAR_SWEEPTOSHO',
                'speed_bonus' => 1,
                'training_effect_bonus' => 8,
                'friendship_bonus' => 20,
                'meta_tier' => 'S',
                'skill_hints_provided' => [
                    'Charming ◯',
                    'Lone Wolf',
                ],
                'unique_effects' => [
                    'Accessible Speed stat stick',
                    'High Specialty Priority',
                    'Some Training Effectiveness',
                ],
                'strategic_notes' => [
                    'Can give Charming ◯ and Lone Wolf',
                    'Good for Career runs and viable for Team Trials',
                ],
                'usage_rate' => 85.6,
                'win_rate_contribution' => 83.9,
                'is_active' => true,
            ],

            // Narita Brian - Story Speed card
            [
                'name' => 'Narita Brian [Two Pieces]',
                'internal_id' => 'GLOBAL_SC_NARITABRIAN_TWOPIECES',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Narita Brian',
                'character_internal_id' => 'CHAR_NARITABRIAN',
                'speed_bonus' => 1,
                'training_effect_bonus' => 8,
                'friendship_bonus' => 18,
                'meta_tier' => 'S',
                'skill_hints_provided' => [
                    'Lone Wolf',
                    'Medium-Long distance skills',
                    'Pace Chaser skills',
                ],
                'unique_effects' => [
                    'Easy-to-get story Speed Card',
                ],
                'strategic_notes' => [
                    'Gives Lone Wolf',
                    'Useful skills for Medium-Long distance',
                    'Good for Pace Chasers',
                ],
                'usage_rate' => 82.3,
                'win_rate_contribution' => 80.7,
                'is_active' => true,
            ],

            // Silence Suzuka - Front Runner Speed card
            [
                'name' => 'Silence Suzuka [Beyond This Shining Moment]',
                'internal_id' => 'GLOBAL_SC_SILENCESUZUKA_SHINING',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Silence Suzuka',
                'character_internal_id' => 'CHAR_SILENCESUZUKA',
                'speed_bonus' => 1,
                'training_effect_bonus' => 8,
                'friendship_bonus' => 20,
                'meta_tier' => 'S',
                'artwork_url' => '/images/support_cards/Silence_Suzuka_Beyond_This_Shining_Moment.png',
                'skill_hints_provided' => [
                    'Front Runner Savvy',
                    'Unrestrained',
                    'Focus',
                    'Front Runner Straightaways ◯',
                    'Front Runner Corners ◯',
                ],
                'unique_effects' => [
                    'Large amount of Front Runner yellow skills',
                ],
                'strategic_notes' => [
                    'Specialized for Front Runner style',
                    'Has Front Runner Savvy',
                ],
                'usage_rate' => 84.1,
                'win_rate_contribution' => 82.4,
                'is_active' => true,
            ],
        ];
    }

    /**
     * A-Tier Support Cards
     * Source: Game8.co January 2026 tier list
     * Strong viable options for the Global English server
     */
    private function getATierCards(): array
    {
        return [
            // Special Week - All-rounder Speed card
            [
                'name' => 'Special Week [The Setting Sun and Rising Stars]',
                'internal_id' => 'GLOBAL_SC_SPECIALWEEK_SETTING',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Special Week',
                'character_internal_id' => 'CHAR_SPECIALWEEK',
                'speed_bonus' => 1,
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'meta_tier' => 'A',
                'skill_hints_provided' => [
                    'Gourmand',
                    'Recovery skills',
                ],
                'unique_effects' => [
                    'Flexible for Pace Chasers with benefit for Late Surgers',
                    'Good amount of blue recovery skills',
                ],
                'strategic_notes' => [
                    'All-rounder card',
                    'Good for Pace Chasers',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Fine Motion', 'Super Creek'],
                'usage_rate' => 76.8,
                'win_rate_contribution' => 74.5,
                'is_active' => true,
            ],

            // Tokai Teio - Pace Chaser Speed card
            [
                'name' => 'Tokai Teio [Dream Big!]',
                'internal_id' => 'GLOBAL_SC_TOKAITEIO_DREAM',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Tokai Teio',
                'character_internal_id' => 'CHAR_TOKAITEIO',
                'speed_bonus' => 1,
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'meta_tier' => 'A',
                'artwork_url' => '/images/support_cards/Tokai_Teio_Dream_Big!.png',
                'skill_hints_provided' => [
                    'Rushing Gale!',
                    'Pace Chaser Straightaways ◯',
                    'Pace Chaser Corners ◯',
                    'Pace Chaser Acceleration',
                ],
                'unique_effects' => [
                    'Lot of yellow skills for Pace Chasers',
                    'Good Training Effectiveness',
                ],
                'strategic_notes' => [
                    'Good for Pace Chaser style',
                    'Has Rushing Gale!',
                    'Best for Mile and Medium distances',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Fine Motion', 'El Condor Pasa'],
                'usage_rate' => 75.2,
                'win_rate_contribution' => 73.1,
                'is_active' => true,
            ],

            // El Condor Pasa - Power card for Pace Chasers
            [
                'name' => 'El Condor Pasa [Champion\'s Passion]',
                'internal_id' => 'GLOBAL_SC_ELCONDORPASA_CHAMPION',
                'card_type' => 'power',
                'rarity' => 'SSR',
                'character_name' => 'El Condor Pasa',
                'character_internal_id' => 'CHAR_ELCONDORPASA',
                'power_bonus' => 1,
                'training_effect_bonus' => 8,
                'friendship_bonus' => 18,
                'meta_tier' => 'A',
                'skill_hints_provided' => [
                    'Killer Tunes',
                    'Hawkeye',
                    'Pace Chaser skills',
                ],
                'unique_effects' => [
                    'Good Training Effectiveness',
                    'High Specialty Priority',
                ],
                'strategic_notes' => [
                    'Lot of yellow skills for Medium-focused Pace Chasers',
                    'Has navigational skill Hawkeye',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Tokai Teio', 'Fine Motion'],
                'usage_rate' => 74.6,
                'win_rate_contribution' => 72.8,
                'is_active' => true,
            ],
            // Mejiro McQueen - Long distance Pace Chaser
            [
                'name' => 'Mejiro McQueen [Your Team Ace]',
                'internal_id' => 'GLOBAL_SC_MEJIROMCQUEEN_TEAMACE',
                'card_type' => 'stamina',
                'rarity' => 'SSR',
                'character_name' => 'Mejiro McQueen',
                'character_internal_id' => 'CHAR_MEJIROMCQUEEN',
                'stamina_bonus' => 1,
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'meta_tier' => 'A',
                'artwork_url' => '/images/support_cards/Mejiro_McQueen_Your_Team_Ace.png',
                'skill_hints_provided' => [
                    'Cooldown',
                    'Recovery skills',
                    'Long distance skills',
                ],
                'unique_effects' => [
                    'Good recovery skills for Long-focused Pace Chasers',
                    'Sufficient yellow skills for Long-focused Pace Chasers',
                ],
                'strategic_notes' => [
                    'Can give Cooldown',
                    'Good for Long distance races',
                    'Essential for Long distance builds',
                ],
                'recommended_scenarios' => ['URA Finale'],
                'deck_synergies' => ['Super Creek', 'Special Week'],
                'usage_rate' => 73.4,
                'win_rate_contribution' => 71.6,
                'is_active' => true,
            ],

            // Twin Turbo - Front Runner Speed card
            [
                'name' => 'Twin Turbo [Turbo Booooost!]',
                'internal_id' => 'GLOBAL_SC_TWINTURBO_TURBO',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'character_name' => 'Twin Turbo',
                'character_internal_id' => 'CHAR_TWINTURBO',
                'speed_bonus' => 1,
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'meta_tier' => 'A',
                'skill_hints_provided' => [
                    'Moxie',
                    'Leader\'s Pride',
                    'Taking the Lead',
                    'Front Runner skills',
                ],
                'unique_effects' => [
                    'Large amount of Front Runner yellow skills',
                    'Has Moxie for Stamina Recovery',
                ],
                'strategic_notes' => [
                    'Useful event skills for Front Runners',
                    'Has Taking the Lead',
                    'Best for Sprint and Mile distances',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Silence Suzuka', 'Kitasan Black'],
                'usage_rate' => 72.1,
                'win_rate_contribution' => 70.3,
                'is_active' => true,
            ],
        ];
    }

    /**
     * B-Tier Support Cards
     * Solid options for specific builds or budget alternatives
     */
    private function getBTierCards(): array
    {
        return [
            // Vodka - Power card
            [
                'name' => 'Vodka [Vodka Tonic]',
                'internal_id' => 'GLOBAL_SC_VODKA_TONIC',
                'card_type' => 'power',
                'rarity' => 'SSR',
                'character_name' => 'Vodka',
                'character_internal_id' => 'CHAR_VODKA',
                'power_bonus' => 1,
                'training_effect_bonus' => 6,
                'friendship_bonus' => 16,
                'meta_tier' => 'B',
                'skill_hints_provided' => [
                    'Power-related skills',
                ],
                'unique_effects' => [
                    'Decent Power training bonuses',
                ],
                'strategic_notes' => [
                    'Budget Power option',
                    'Good for beginners',
                ],
                'recommended_scenarios' => ['URA Finale'],
                'deck_synergies' => ['El Condor Pasa', 'Rice Shower'],
                'usage_rate' => 65.3,
                'win_rate_contribution' => 63.8,
                'is_active' => true,
            ],

            // Grass Wonder - Stamina card
            [
                'name' => 'Grass Wonder [Endless Curiosity]',
                'internal_id' => 'GLOBAL_SC_GRASSWONDER_CURIOSITY',
                'card_type' => 'stamina',
                'rarity' => 'SSR',
                'character_name' => 'Grass Wonder',
                'character_internal_id' => 'CHAR_GRASSWONDER',
                'stamina_bonus' => 1,
                'training_effect_bonus' => 6,
                'friendship_bonus' => 16,
                'meta_tier' => 'B',
                'skill_hints_provided' => [
                    'Stamina recovery skills',
                ],
                'unique_effects' => [
                    'Decent Stamina training bonuses',
                ],
                'strategic_notes' => [
                    'Budget Stamina option',
                    'Alternative to Super Creek',
                ],
                'recommended_scenarios' => ['URA Finale'],
                'deck_synergies' => ['Super Creek', 'Mejiro McQueen'],
                'usage_rate' => 64.7,
                'win_rate_contribution' => 62.9,
                'is_active' => true,
            ],

            // Haru Urara - Guts card
            [
                'name' => 'Haru Urara [Cheer Up!]',
                'internal_id' => 'GLOBAL_SC_HARUURA_CHEERUP',
                'card_type' => 'guts',
                'rarity' => 'SSR',
                'character_name' => 'Haru Urara',
                'character_internal_id' => 'CHAR_HARUURA',
                'guts_bonus' => 1,
                'training_effect_bonus' => 6,
                'friendship_bonus' => 16,
                'event_recovery_bonus' => 10,
                'meta_tier' => 'B',
                'skill_hints_provided' => [
                    'Guts-related skills',
                    'Recovery skills',
                ],
                'unique_effects' => [
                    'Good energy recovery events',
                    'Mood improvement',
                ],
                'strategic_notes' => [
                    'Useful for energy management',
                    'Good events for consistency',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Tazuna Hayakawa', 'Riko Kashimoto'],
                'usage_rate' => 63.2,
                'win_rate_contribution' => 61.5,
                'is_active' => true,
            ],

            // Symboli Rudolf - Wit card
            [
                'name' => 'Symboli Rudolf [Emperor\'s Dignity]',
                'internal_id' => 'GLOBAL_SC_SYMBOLIRUDOLF_EMPEROR',
                'card_type' => 'wit',
                'rarity' => 'SSR',
                'character_name' => 'Symboli Rudolf',
                'character_internal_id' => 'CHAR_SYMBOLIRUDOLF',
                'wit_bonus' => 1,
                'training_effect_bonus' => 6,
                'friendship_bonus' => 16,
                'meta_tier' => 'B',
                'skill_hints_provided' => [
                    'Wit-related skills',
                    'Positioning skills',
                ],
                'unique_effects' => [
                    'Decent Wit training bonuses',
                ],
                'strategic_notes' => [
                    'Budget Wit option',
                    'Good for skill activation builds',
                ],
                'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
                'deck_synergies' => ['Fine Motion'],
                'usage_rate' => 62.8,
                'win_rate_contribution' => 60.9,
                'is_active' => true,
            ],

            // Machikane Fukukitaru - Friend card
            [
                'name' => 'Machikane Fukukitaru [Lucky Charm]',
                'internal_id' => 'GLOBAL_SC_MACHIKANEFUKUKITARU_LUCKY',
                'card_type' => 'friend',
                'rarity' => 'SSR',
                'character_name' => 'Machikane Fukukitaru',
                'character_internal_id' => 'CHAR_MACHIKANEFUKUKITARU',
                'friendship_bonus' => 16,
                'event_recovery_bonus' => 12,
                'event_effect_bonus' => 12,
                'meta_tier' => 'B',
                'skill_hints_provided' => [
                    'Support skills',
                ],
                'unique_effects' => [
                    'Good energy recovery',
                    'Mood improvement events',
                ],
                'strategic_notes' => [
                    'Budget Friend option',
                    'Alternative to Tazuna',
                ],
                'recommended_scenarios' => ['URA Finale'],
                'deck_synergies' => ['Tazuna Hayakawa', 'Riko Kashimoto'],
                'usage_rate' => 61.5,
                'win_rate_contribution' => 59.7,
                'is_active' => true,
            ],
        ];
    }
}
