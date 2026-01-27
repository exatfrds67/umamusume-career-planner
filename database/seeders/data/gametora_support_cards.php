<?php

/**
 * GameTora Support Card Data
 *
 * Detailed support card data fetched from GameTora (https://gametora.com/umamusume/supports/)
 * Data includes: training bonuses, skill hints, event skills, and unique effects
 *
 * Generated: 2026-01-23
 * Source: GameTora Umamusume Support Card Database
 */

return [
    // ============================================================================
    // SSR SPEED CARDS
    // ============================================================================

    [
        'gametora_id' => '30028-kitasan-black',
        'name' => 'Kitasan Black',
        'full_name' => 'Kitasan Black [Fire at My Heels]',
        'internal_id' => 'GAMETORA_30028_KITASAN_BLACK',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'character_name' => 'Kitasan Black',
        'release_date' => '2025-07-16',

        // Training Bonuses (at max level)
        'stat_gains' => [
            'speed' => 6,
            'power' => 2,
        ],
        'friendship_bonus' => 20,
        'training_effectiveness' => 5,
        'race_bonus' => 5,
        'fan_bonus' => 10,
        'mood_effect' => 20,
        'initial_friendship_gauge' => 25,
        'hint_levels' => 1,
        'hint_frequency' => 20,
        'specialty_priority' => null, // Unlocked at level 45
        'power_bonus_unlock' => 35, // Unlocked at level 35

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of training performed together (5%)',
            'Increases the frequency at which the character participates in their preferred training type (20)',
        ],

        // Skill Hints (randomly granted during training)
        'skill_hints' => [
            ['name' => 'Corner Recovery', 'is_gold' => true],
            ['name' => 'Straightaway Recovery', 'is_gold' => false],
            ['name' => 'Extra Tank', 'is_gold' => false],
            ['name' => 'Corner Adept', 'is_gold' => true],
            ['name' => 'Long Corners', 'is_gold' => true],
            ['name' => 'Front Runner Straightaways', 'is_gold' => true],
            ['name' => 'Dodging Danger', 'is_gold' => false],
            ['name' => 'Focus', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Straightaway Adept', 'is_gold' => false],
            ['name' => 'Professor of Curvature', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30002-silence-suzuka',
        'name' => 'Silence Suzuka',
        'full_name' => 'Silence Suzuka [Beyond This Shining Moment]',
        'internal_id' => 'GAMETORA_30002_SILENCE_SUZUKA',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'character_name' => 'Silence Suzuka',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'speed' => 6,
            'power' => 2,
        ],
        'friendship_bonus' => 25,
        'specialty_priority' => 50,
        'mood_effect' => 30,
        'initial_friendship_gauge' => 20,
        'race_bonus' => 5,
        'fan_bonus' => 10,
        'skill_point_bonus_unlock' => 35,
        'initial_speed_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Amplifies the effect of mood when training together (15%)',
            'Increases the frequency at which hint events occur (20%)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Front Runner Savvy', 'is_gold' => true],
            ['name' => 'Rosy Outlook', 'is_gold' => false],
            ['name' => 'Fast-Paced', 'is_gold' => false],
            ['name' => 'Front Runner Straightaways', 'is_gold' => true],
            ['name' => 'Front Runner Corners', 'is_gold' => true],
            ['name' => "Leader's Pride", 'is_gold' => false],
            ['name' => 'Early Lead', 'is_gold' => false],
            ['name' => 'Final Push', 'is_gold' => false],
            ['name' => 'Focus', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Restart', 'is_gold' => false],
            ['name' => 'Focus', 'is_gold' => false],
            ['name' => 'Unrestrained', 'is_gold' => true],
            ['name' => 'Left-Handed', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30026-twin-turbo',
        'name' => 'Twin Turbo',
        'full_name' => 'Twin Turbo [Turbo Booooost!]',
        'internal_id' => 'GAMETORA_30026_TWIN_TURBO',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'character_name' => 'Twin Turbo',
        'release_date' => '2025-07-02',

        // Training Bonuses
        'stat_gains' => [
            'speed' => 6,
            'power' => 2,
        ],
        'friendship_bonus' => 15,
        'mood_effect' => 40,
        'initial_friendship_gauge' => 20,
        'hint_levels' => 2,
        'hint_frequency' => 30,
        'specialty_priority' => 20,
        'speed_bonus_unlock' => 35,
        'training_effectiveness_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Amplifies the effect of mood when training together (15%)',
            'Increases stat gain from races (5%)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Competitive Spirit', 'is_gold' => true],
            ['name' => 'Target in Sight', 'is_gold' => true],
            ['name' => 'Moxie', 'is_gold' => false],
            ['name' => 'Fast-Paced', 'is_gold' => false],
            ['name' => "Leader's Pride", 'is_gold' => false],
            ['name' => 'Early Lead', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => "Leader's Pride", 'is_gold' => false],
            ['name' => 'Early Lead', 'is_gold' => false],
            ['name' => 'Taking the Lead', 'is_gold' => true],
            ['name' => 'Watchful Eye', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30001-special-week',
        'name' => 'Special Week',
        'full_name' => 'Special Week [The Setting Sun and Rising Stars]',
        'internal_id' => 'GAMETORA_30001_SPECIAL_WEEK',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'character_name' => 'Special Week',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'speed' => 1,
            'power' => 1,
            'guts' => 6,
        ],
        'friendship_bonus' => 15,
        'mood_effect' => 40,
        'initial_friendship_gauge' => 20,
        'hint_levels' => 2,
        'hint_frequency' => 30,
        'specialty_priority' => 20,
        'power_bonus_unlock' => 35,
        'training_effectiveness_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases Guts gain when training together (1)',
            'Increases initial Guts when beginning a Career playthrough (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Wet Conditions', 'is_gold' => true],
            ['name' => 'Rainy Days', 'is_gold' => true],
            ['name' => 'Late Surger Savvy', 'is_gold' => true],
            ['name' => 'Hydrate', 'is_gold' => false],
            ['name' => 'Homestretch Haste', 'is_gold' => false],
            ['name' => 'Outer Swell', 'is_gold' => false],
            ['name' => 'Steadfast', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Soft Step', 'is_gold' => false],
            ['name' => 'Straight Descent', 'is_gold' => false],
            ['name' => 'Homestretch Haste', 'is_gold' => false],
            ['name' => 'In Body and Mind', 'is_gold' => true],
            ['name' => 'Extra Tank', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30003-tokai-teio',
        'name' => 'Tokai Teio',
        'full_name' => 'Tokai Teio [Dream Big!]',
        'internal_id' => 'GAMETORA_30003_TOKAI_TEIO',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'character_name' => 'Tokai Teio',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'speed' => 6,
            'power' => 2,
        ],
        'friendship_bonus' => 15,
        'mood_effect' => 40,
        'initial_friendship_gauge' => 20,
        'hint_levels' => 2,
        'hint_frequency' => 30,
        'specialty_priority' => 20,
        'power_bonus_unlock' => 35,
        'race_bonus_unlock' => 45,
        'fan_bonus_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases initial Speed when beginning a Career playthrough (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Soft Step', 'is_gold' => false],
            ['name' => 'Nimble Navigator', 'is_gold' => false],
            ['name' => 'Shrewd Step', 'is_gold' => false],
            ['name' => 'Prudent Positioning', 'is_gold' => false],
            ['name' => 'Go with the Flow', 'is_gold' => false],
            ['name' => 'Thunderbolt Step', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Rushing Gale!', 'is_gold' => true],
            ['name' => 'Pace Chaser Straightaways', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SSR STAMINA CARDS
    // ============================================================================

    [
        'gametora_id' => '30016-super-creek',
        'name' => 'Super Creek',
        'full_name' => 'Super Creek [Piece of Mind]',
        'internal_id' => 'GAMETORA_30016_SUPER_CREEK',
        'card_type' => 'stamina',
        'rarity' => 'SSR',
        'character_name' => 'Super Creek',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'stamina' => 6,
            'guts' => 2,
        ],
        'friendship_bonus' => 20,
        'training_effectiveness' => 10,
        'initial_stamina' => 25,
        'race_bonus' => 5,
        'fan_bonus' => 15,
        'specialty_priority' => 20,
        'stamina_bonus_unlock' => 35,
        'initial_friendship_gauge_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases the frequency at which the character participates in their preferred training type (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Firm Conditions', 'is_gold' => true],
            ['name' => 'Corner Recovery', 'is_gold' => true],
            ['name' => 'Ramp Up', 'is_gold' => false],
            ['name' => 'Homestretch Haste', 'is_gold' => false],
            ['name' => 'Hesitant Pace Chasers', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Swinging Maestro', 'is_gold' => true],
            ['name' => 'Deep Breaths', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30022-mejiro-mcqueen',
        'name' => 'Mejiro McQueen',
        'full_name' => 'Mejiro McQueen [Your Team Ace]',
        'internal_id' => 'GAMETORA_30022_MEJIRO_MCQUEEN',
        'card_type' => 'stamina',
        'rarity' => 'SSR',
        'character_name' => 'Mejiro McQueen',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'stamina' => 6,
            'guts' => 2,
        ],
        'friendship_bonus' => 25,
        'specialty_priority' => 35,
        'mood_effect' => 20,
        'initial_friendship_gauge' => 15,
        'race_bonus' => 1,
        'fan_bonus' => 5,
        'guts_bonus_unlock' => 35,
        'hint_levels_unlock' => 45,
        'hint_frequency_unlock' => 45,

        // Unique Effects (unlocked at level 40)
        'unique_effects' => [
            'Increases Stamina gain when training together (1)',
            'Increases initial Stamina when beginning a Career playthrough (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Kyoto Racecourse', 'is_gold' => true],
            ['name' => 'Wet Conditions', 'is_gold' => true],
            ['name' => 'Rainy Days', 'is_gold' => true],
            ['name' => 'Stamina to Spare', 'is_gold' => false],
            ['name' => 'Deep Breaths', 'is_gold' => false],
            ['name' => 'Extra Tank', 'is_gold' => false],
            ['name' => 'Corner Adept', 'is_gold' => true],
            ['name' => 'Straightaway Adept', 'is_gold' => false],
            ['name' => 'Prepared to Pass', 'is_gold' => false],
            ['name' => 'Inside Scoop', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Cooldown', 'is_gold' => true],
            ['name' => 'Deep Breaths', 'is_gold' => false],
            ['name' => 'Early Lead', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SSR POWER CARDS
    // ============================================================================

    [
        'gametora_id' => '30023-rice-shower',
        'name' => 'Rice Shower',
        'full_name' => 'Rice Shower [Happiness Just around the Bend]',
        'internal_id' => 'GAMETORA_30023_RICE_SHOWER',
        'card_type' => 'power',
        'rarity' => 'SSR',
        'character_name' => 'Rice Shower',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'stamina' => 6,
            'guts' => 2,
        ],
        'friendship_bonus' => 15,
        'mood_effect' => 40,
        'initial_friendship_gauge' => 12,
        'hint_levels' => 2,
        'hint_frequency' => 30,
        'specialty_priority' => 20,
        'guts_bonus_unlock' => 35,
        'race_bonus_unlock' => 45,
        'fan_bonus_unlock' => 45,

        // Unique Effects (unlocked at level 40)
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases the frequency at which hint events occur (20%)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Maverick', 'is_gold' => true],
            ['name' => 'Kyoto Racecourse', 'is_gold' => true],
            ['name' => 'Deep Breaths', 'is_gold' => false],
            ['name' => 'Straight Descent', 'is_gold' => false],
            ['name' => 'Highlander', 'is_gold' => false],
            ['name' => 'Frenzied Pace Chasers', 'is_gold' => false],
            ['name' => 'Subdued Pace Chasers', 'is_gold' => false],
            ['name' => 'Flustered Pace Chasers', 'is_gold' => false],
            ['name' => 'Disorient', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Extra Tank', 'is_gold' => false],
            ['name' => 'Adrenaline Rush', 'is_gold' => true],
            ['name' => 'Firm Conditions', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30007-el-condor-pasa',
        'name' => 'El Condor Pasa',
        'full_name' => "El Condor Pasa [Champion's Passion]",
        'internal_id' => 'GAMETORA_30007_EL_CONDOR_PASA',
        'card_type' => 'power',
        'rarity' => 'SSR',
        'character_name' => 'El Condor Pasa',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'stamina' => 2,
            'power' => 6,
        ],
        'friendship_bonus' => 15,
        'race_bonus' => 5,
        'fan_bonus' => 15,
        'mood_effect' => 30,
        'initial_friendship_gauge' => 20,
        'specialty_priority' => 35,
        'initial_power' => 15,
        'power_bonus_unlock' => 35,
        'training_effectiveness_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases the frequency at which the character participates in their preferred training type (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Standard Distance', 'is_gold' => true],
            ['name' => 'Sunny Days', 'is_gold' => true],
            ['name' => 'Prepared to Pass', 'is_gold' => false],
            ['name' => 'Up-Tempo', 'is_gold' => false],
            ['name' => 'Medium Straightaways', 'is_gold' => true],
            ['name' => 'Pace Chaser Straightaways', 'is_gold' => true],
            ['name' => 'Hawkeye', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Stamina to Spare', 'is_gold' => false],
            ['name' => 'Killer Tunes', 'is_gold' => true],
            ['name' => 'Sunny Days', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30005-vodka',
        'name' => 'Vodka',
        'full_name' => 'Vodka [Vodka Tonic]',
        'internal_id' => 'GAMETORA_30005_VODKA',
        'card_type' => 'power',
        'rarity' => 'SSR',
        'character_name' => 'Vodka',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'stamina' => 2,
            'power' => 6,
        ],
        'friendship_bonus' => 25,
        'specialty_priority' => 50,
        'mood_effect' => 30,
        'initial_friendship_gauge' => 20,
        'race_bonus' => 5,
        'fan_bonus' => 10,
        'power_bonus_unlock' => 35,
        'hint_levels_unlock' => 45,
        'hint_frequency_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases the frequency at which the character participates in their preferred training type (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Tokyo Racecourse', 'is_gold' => true],
            ['name' => 'Straightaway Recovery', 'is_gold' => false],
            ['name' => 'Straightaway Adept', 'is_gold' => false],
            ['name' => 'Homestretch Haste', 'is_gold' => false],
            ['name' => 'Mile Straightaways', 'is_gold' => true],
            ['name' => 'Medium Straightaways', 'is_gold' => true],
            ['name' => 'Straightaway Acceleration', 'is_gold' => false],
            ['name' => 'Slick Surge', 'is_gold' => false],
            ['name' => 'Updrafters', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Updrafters', 'is_gold' => false],
            ['name' => 'Breath of Fresh Air', 'is_gold' => true],
            ['name' => 'Nimble Navigator', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SSR GUTS CARDS
    // ============================================================================

    [
        'gametora_id' => '30019-haru-urara',
        'name' => 'Haru Urara',
        'full_name' => 'Haru Urara [Cheer Up!]',
        'internal_id' => 'GAMETORA_30019_HARU_URARA',
        'card_type' => 'guts',
        'rarity' => 'SSR',
        'character_name' => 'Haru Urara',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'speed' => 1,
            'power' => 1,
            'guts' => 6,
        ],
        'friendship_bonus' => 15,
        'training_effectiveness' => 10,
        'initial_guts' => 25,
        'specialty_priority' => 35,
        'race_bonus' => 1,
        'fan_bonus' => 5,
        'skill_point_bonus_unlock' => 35,
        'mood_effect_unlock' => 45,

        // Unique Effects (unlocked at level 40)
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases stat gain from races (5%)',
        ],

        // Skill Hints (none listed - card has no hint skills)
        'skill_hints' => [],

        // Event Skills
        'event_skills' => [
            ['name' => 'Unruffled', 'is_gold' => false],
            ['name' => 'Long Shot', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SSR WIT CARDS
    // ============================================================================

    [
        'gametora_id' => '30037-symboli-rudolf',
        'name' => 'Symboli Rudolf',
        'full_name' => "Symboli Rudolf [Emperor's Dignity]",
        'internal_id' => 'GAMETORA_30037_SYMBOLI_RUDOLF',
        'card_type' => 'wit',
        'rarity' => 'SSR',
        'character_name' => 'Symboli Rudolf',
        'release_date' => null, // Not yet released on Global
        'jp_only' => true,

        // Training Bonuses
        'stat_gains' => [
            'speed' => 1,
            'power' => 1,
            'guts' => 6,
        ],
        'friendship_bonus' => 15,
        'hint_levels' => 2,
        'hint_frequency' => 40,
        'specialty_priority' => 50,
        'training_effectiveness' => 5,
        'initial_friendship_gauge' => 20,
        'initial_speed' => 15,
        'race_bonus' => 1,
        'fan_bonus' => 5,
        'power_bonus_unlock' => 35,
        'guts_bonus_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Gain Speed Bonus (2) when the bond gauge is at least 80',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Right-Handed', 'is_gold' => true],
            ['name' => 'Nakayama Racecourse', 'is_gold' => true],
            ['name' => 'Corner Recovery', 'is_gold' => true],
            ['name' => 'Rosy Outlook', 'is_gold' => false],
            ['name' => 'Medium Corners', 'is_gold' => true],
            ['name' => 'Fighting Spirit', 'is_gold' => false],
            ['name' => 'Eager', 'is_gold' => false],
            ['name' => 'Ambitions', 'is_gold' => false],
            ['name' => 'Firm Step', 'is_gold' => false],
            ['name' => 'Tooth and Tail', 'is_gold' => false],
            ['name' => 'Mold Breaker', 'is_gold' => false],
            ['name' => 'Tether', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Corner Acceleration', 'is_gold' => true],
            ['name' => 'Corner Adept', 'is_gold' => true],
            ['name' => 'Swinging Maestro', 'is_gold' => true],
            ['name' => 'Rainy Days', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30010-fine-motion',
        'name' => 'Fine Motion',
        'full_name' => 'Fine Motion [Wave of Gratitude]',
        'internal_id' => 'GAMETORA_30010_FINE_MOTION',
        'card_type' => 'wit',
        'rarity' => 'SSR',
        'character_name' => 'Fine Motion',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [
            'wit' => 6,
            'skill_points' => 5,
        ],
        'friendship_bonus' => 20,
        'training_effectiveness' => 10,
        'initial_wit' => 25,
        'race_bonus' => 5,
        'fan_bonus' => 15,
        'wit_friendship_recovery' => 3,
        'specialty_priority' => 20,
        'wit_bonus_unlock' => 35,
        'mood_effect_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases the effectiveness of Friendship Training (10%)',
            'Increases initial Friendship Gauge when beginning a Career playthrough (15)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Right-Handed', 'is_gold' => true],
            ['name' => 'Fall Runner', 'is_gold' => true],
            ['name' => 'Outer Post Proficiency', 'is_gold' => true],
            ['name' => 'Straightaway Acceleration', 'is_gold' => false],
            ['name' => 'Nimble Navigator', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Speed Star', 'is_gold' => true],
            ['name' => 'Prepared to Pass', 'is_gold' => false],
            ['name' => 'Corner Adept', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SSR FRIEND CARDS
    // ============================================================================

    [
        'gametora_id' => '30021-tazuna-hayakawa',
        'name' => 'Tazuna Hayakawa',
        'full_name' => 'Tazuna Hayakawa [Tracen Reception]',
        'internal_id' => 'GAMETORA_30021_TAZUNA_HAYAKAWA',
        'card_type' => 'friend',
        'rarity' => 'SSR',
        'character_name' => 'Tazuna Hayakawa',
        'release_date' => '2025-06-26',

        // Training Bonuses
        'stat_gains' => [],
        'initial_friendship_gauge' => 25,
        'event_recovery' => 40,
        'event_effectiveness' => 25,
        'failure_protection' => 20,
        'energy_cost_reduction' => 15,
        'training_effectiveness_unlock' => 35,
        'initial_speed_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Decreases the probability of failure when training together (10%)',
            'Decreases Energy consumed when training together (5%)',
        ],

        // Skill Hints (Friend cards don't have training hints)
        'skill_hints' => [],

        // Event Skills
        'event_skills' => [
            ['name' => 'Watchful Eye', 'is_gold' => false],
            ['name' => 'Focus', 'is_gold' => false],
            ['name' => 'Concentration', 'is_gold' => true],
            ['name' => 'Tail Held High', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30036-riko-kashimoto',
        'name' => 'Riko Kashimoto',
        'full_name' => 'Riko Kashimoto [Planned Perfection]',
        'internal_id' => 'GAMETORA_30036_RIKO_KASHIMOTO',
        'card_type' => 'friend',
        'rarity' => 'SSR',
        'character_name' => 'Riko Kashimoto',
        'release_date' => '2025-11-06',

        // Training Bonuses
        'stat_gains' => [],
        'initial_friendship_gauge' => 25,
        'race_bonus' => 5,
        'fan_bonus' => 15,
        'event_effectiveness' => 25,
        'failure_protection' => 15,
        'energy_cost_reduction' => 10,
        'event_recovery' => 20,
        'training_effectiveness_unlock' => 35,
        'initial_stamina_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Decreases the probability of failure when training together (10%)',
            'Decreases Energy consumed when training together (5%)',
        ],

        // Skill Hints (Friend cards don't have training hints)
        'skill_hints' => [],

        // Event Skills
        'event_skills' => [
            ['name' => 'Ramp Up', 'is_gold' => false],
            ['name' => 'Maverick', 'is_gold' => true],
            ['name' => 'Lone Wolf', 'is_gold' => true],
            ['name' => 'Rushing Gale!', 'is_gold' => true],
            ['name' => 'Sympathy', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '30080-sasami-anshinzawa',
        'name' => 'Sasami Anshinzawa',
        'full_name' => 'Sasami Anshinzawa [Uma Stan]',
        'internal_id' => 'GAMETORA_30080_SASAMI_ANSHINZAWA',
        'card_type' => 'friend',
        'rarity' => 'SSR',
        'character_name' => 'Sasami Anshinzawa',
        'release_date' => null, // Not yet released on Global
        'jp_only' => true,

        // Training Bonuses
        'stat_gains' => [],
        'mood_effect' => 40,
        'event_effectiveness' => 10,
        'initial_friendship_gauge' => 20,
        'skill_point_bonus_unlock' => 35,
        'event_recovery_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Sasami Anshinzawa random events are more likely to occur',
        ],

        // Skill Hints (Friend cards don't have training hints)
        'skill_hints' => [],

        // Event Skills
        'event_skills' => [
            ['name' => 'Wallflower', 'is_gold' => false],
            ['name' => 'Corner Recovery', 'is_gold' => false, 'is_negative' => true], // Note: × indicates negative/debuff skill
            ['name' => 'Risky Business', 'is_gold' => false],
            ['name' => 'Running Idle', 'is_gold' => false],
            ['name' => 'Nothing Ventured', 'is_gold' => false],
            ['name' => 'Uma Stan', 'is_gold' => true],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // SR CARDS (Sample)
    // ============================================================================

    [
        'gametora_id' => '20001-fuji-kiseki',
        'name' => 'Fuji Kiseki',
        'full_name' => 'Fuji Kiseki [Miracle Maker]',
        'internal_id' => 'GAMETORA_20001_FUJI_KISEKI',
        'card_type' => 'wit',
        'rarity' => 'SR',
        'character_name' => 'Fuji Kiseki',
        'release_date' => '2025-06-26',

        // Training Bonuses (max level 25 for SR)
        'stat_gains' => [
            'wit' => 6,
            'skill_points' => 5,
        ],
        'friendship_bonus' => 15,
        'mood_effect' => 40,
        'wit_friendship_recovery' => 3,
        'initial_friendship_gauge' => 20,
        'hint_levels' => 2,
        'hint_frequency' => 30,
        'specialty_priority' => 20,
        'race_bonus_unlock' => 30,
        'fan_bonus_unlock' => 30,
        'wit_bonus_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Increases skill point gain when training together (1)',
            'Increases initial Wit when beginning a Career playthrough (20)',
        ],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Summer Runner', 'is_gold' => true],
            ['name' => 'Cloudy Days', 'is_gold' => true],
            ['name' => 'Mile Corners', 'is_gold' => true],
            ['name' => 'Unyielding Spirit', 'is_gold' => false],
            ['name' => 'Trick (Front)', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Flustered End Closers', 'is_gold' => false],
            ['name' => 'Prepared to Pass', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '20021-aoi-kiryuin',
        'name' => 'Aoi Kiryuin',
        'full_name' => 'Aoi Kiryuin [Journalist Spirit]',
        'internal_id' => 'GAMETORA_20021_AOI_KIRYUIN',
        'card_type' => 'friend',
        'rarity' => 'SR',
        'character_name' => 'Aoi Kiryuin',
        'release_date' => '2025-06-26',

        // Training Bonuses (max level 25 for SR)
        'stat_gains' => [],
        'initial_friendship_gauge' => 20,
        'event_recovery' => 40,
        'event_effectiveness' => 25,
        'failure_protection' => 20,
        'energy_cost_reduction' => 15,
        'initial_wit_unlock' => 30,
        'training_effectiveness_unlock' => 45,

        // Unique Effects
        'unique_effects' => [
            'Decreases the probability of failure when training together (10%)',
            'Decreases Energy consumed when training together (5%)',
        ],

        // Skill Hints (Friend cards don't have training hints)
        'skill_hints' => [],

        // Event Skills
        'event_skills' => [
            ['name' => 'Maverick', 'is_gold' => true],
            ['name' => 'Subdued Front Runners', 'is_gold' => false],
            ['name' => 'Hesitant End Closers', 'is_gold' => false],
            ['name' => 'Lay Low', 'is_gold' => false],
            ['name' => 'Shake It Out', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    // ============================================================================
    // R CARDS (Sample)
    // ============================================================================

    [
        'gametora_id' => '10001-special-week',
        'name' => 'Special Week',
        'full_name' => 'Special Week [R]',
        'internal_id' => 'GAMETORA_10001_SPECIAL_WEEK_R',
        'card_type' => 'speed',
        'rarity' => 'R',
        'character_name' => 'Special Week',
        'release_date' => '2025-06-26',

        // Training Bonuses (max level 20 for R)
        'stat_gains' => [
            'speed' => 1,
            'power' => 1,
            'guts' => 6,
        ],
        'friendship_bonus' => 10,
        'mood_effect' => 25,
        'initial_friendship_gauge' => 15,
        'hint_levels' => 1,
        'hint_frequency' => 20,
        'specialty_priority' => 20,
        'power_bonus_unlock' => 40,

        // Unique Effects (R cards typically don't have unique effects)
        'unique_effects' => [],

        // Skill Hints
        'skill_hints' => [
            ['name' => 'Wet Conditions', 'is_gold' => true],
            ['name' => 'Rainy Days', 'is_gold' => true],
            ['name' => 'Late Surger Savvy', 'is_gold' => true],
            ['name' => 'Hydrate', 'is_gold' => false],
            ['name' => 'Homestretch Haste', 'is_gold' => false],
            ['name' => 'Outer Swell', 'is_gold' => false],
            ['name' => 'Steadfast', 'is_gold' => false],
        ],

        // Event Skills
        'event_skills' => [
            ['name' => 'Extra Tank', 'is_gold' => false],
        ],

        'is_active' => true,
    ],

    [
        'gametora_id' => '10021-tazuna-hayakawa',
        'name' => 'Tazuna Hayakawa',
        'full_name' => 'Tazuna Hayakawa [R]',
        'internal_id' => 'GAMETORA_10021_TAZUNA_HAYAKAWA_R',
        'card_type' => 'friend',
        'rarity' => 'R',
        'character_name' => 'Tazuna Hayakawa',
        'release_date' => '2025-06-26',

        // Training Bonuses (max level 20 for R - limited data available)
        'stat_gains' => [],

        // Unique Effects (R cards typically don't have unique effects)
        'unique_effects' => [],

        // Skill Hints (Friend cards don't have training hints)
        'skill_hints' => [],

        // Event Skills (limited data available for R cards)
        'event_skills' => [],

        'is_active' => true,
    ],
];
