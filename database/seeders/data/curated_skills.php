<?php

/**
 * Curated Skills Data File
 *
 * This file contains all curated skill definitions consolidated from:
 * - ComprehensiveSkillSeeder
 * - RealUmaMusumeSkillsSeeder
 *
 * Structure:
 * - 'skills' array: All skill definitions with consistent field structure
 * - 'evolution_pairs' array: All evolution relationships [source_internal_id, target_internal_id]
 *
 * Required fields for each skill:
 * - name: Skill name (string)
 * - internal_id: Unique identifier for upsert operations (string)
 * - skill_type: speed, passive, recovery, debuff, unique (string)
 * - rarity: normal, rare, unique (string)
 * - base_sp_cost: SP cost to acquire (int)
 *
 * Optional fields:
 * - can_evolve: Whether skill can evolve (bool)
 * - is_evolution: Whether skill is an evolved version (bool)
 * - effects: Skill effects and bonuses (array)
 * - description: Skill description (string)
 * - activation_conditions: Conditions for activation (array)
 * - meta_tier: S+, S, A, B, C (string)
 * - strategic_notes: Strategic usage notes (array)
 * - synergy_skills: Related skill internal_ids (array)
 */

return [
    'skills' => [
        // =====================================================================
        // SPEED SKILLS (from ComprehensiveSkillSeeder)
        // =====================================================================

        // Speed Skill 1: Go with the Flow
        [
            'name' => 'Go with the Flow',
            'internal_id' => 'speed_001',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Increases speed in the final straight when in good position',
            'activation_conditions' => ['position' => 'top_3', 'phase' => 'final_straight'],
            'meta_tier' => 'A',
            'strategic_notes' => ['Best for front runners', 'Reliable activation'],
            'synergy_skills' => ['speed_003', 'speed_005'],
        ],

        // Speed Skill 1 Rare: Lane Legerdemain
        [
            'name' => 'Lane Legerdemain',
            'internal_id' => 'speed_001_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high'],
            'description' => 'Enhanced version - Significantly increases speed in the final straight',
            'activation_conditions' => ['position' => 'top_4', 'phase' => 'final_straight'],
            'meta_tier' => 'S',
            'strategic_notes' => ['Evolution of Go with the Flow', 'Higher activation rate'],
            'synergy_skills' => ['speed_003', 'speed_005'],
        ],

        // Speed Skill 2: Homestretch Haste
        [
            'name' => 'Homestretch Haste',
            'internal_id' => 'speed_002',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'medium'],
            'description' => 'Improves acceleration in the final straight',
            'activation_conditions' => ['phase' => 'final_straight'],
            'meta_tier' => 'B',
            'strategic_notes' => ['Good for late closers', 'Consistent activation'],
            'synergy_skills' => ['speed_001', 'speed_004'],
        ],

        // Speed Skill 2 Rare: In Body and Mind
        [
            'name' => 'In Body and Mind',
            'internal_id' => 'speed_002_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'high'],
            'description' => 'Enhanced version - Greatly improves acceleration in the final straight',
            'activation_conditions' => ['phase' => 'final_straight'],
            'meta_tier' => 'A',
            'strategic_notes' => ['Evolution of Homestretch Haste', 'Excellent for comebacks'],
            'synergy_skills' => ['speed_001', 'speed_004'],
        ],

        // Speed Skill 3: Quick Charge
        [
            'name' => 'Quick Charge',
            'internal_id' => 'speed_003',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'speed_burst', 'power' => 'medium'],
            'description' => 'Provides a burst of speed during mid-race',
            'activation_conditions' => ['phase' => 'mid_race', 'position' => 'any'],
            'meta_tier' => 'A',
            'strategic_notes' => ['Versatile skill', 'Works for all running styles'],
            'synergy_skills' => ['speed_001', 'passive_001'],
        ],

        // Speed Skill 4: Sprint Turbo
        [
            'name' => 'Sprint Turbo',
            'internal_id' => 'speed_004',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 150,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'start', 'effect' => 'early_speed', 'power' => 'medium'],
            'description' => 'Boosts speed at the start of the race',
            'activation_conditions' => ['phase' => 'start'],
            'meta_tier' => 'B',
            'strategic_notes' => ['Essential for front runners', 'Helps secure early position'],
            'synergy_skills' => ['speed_005', 'passive_002'],
        ],

        // Speed Skill 4 Rare: Rocket Start
        [
            'name' => 'Rocket Start',
            'internal_id' => 'speed_004_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 210,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'start', 'effect' => 'early_speed', 'power' => 'very_high'],
            'description' => 'Enhanced version - Massive speed boost at race start',
            'activation_conditions' => ['phase' => 'start'],
            'meta_tier' => 'S',
            'strategic_notes' => ['Evolution of Sprint Turbo', 'Dominates early positioning'],
            'synergy_skills' => ['speed_005', 'passive_002'],
        ],

        // =====================================================================
        // PASSIVE SKILLS (from ComprehensiveSkillSeeder)
        // =====================================================================

        // Passive Skill 1: Stamina Keeper
        [
            'name' => 'Stamina Keeper',
            'internal_id' => 'passive_001',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'medium'],
            'description' => 'Reduces stamina consumption during races',
            'meta_tier' => 'A',
            'strategic_notes' => ['Essential for long distance', 'Always active'],
            'synergy_skills' => ['recovery_001', 'passive_003'],
        ],

        // Passive Skill 1 Rare: Stamina Master
        [
            'name' => 'Stamina Master',
            'internal_id' => 'passive_001_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 200,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'high'],
            'description' => 'Enhanced version - Significantly reduces stamina consumption',
            'meta_tier' => 'S',
            'strategic_notes' => ['Evolution of Stamina Keeper', 'Critical for marathon races'],
            'synergy_skills' => ['recovery_001', 'passive_003'],
        ],

        // Passive Skill 2: Corner Master
        [
            'name' => 'Corner Master',
            'internal_id' => 'passive_002',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'corners', 'effect' => 'speed_maintenance', 'power' => 'medium'],
            'description' => 'Maintains speed through corners',
            'meta_tier' => 'B',
            'strategic_notes' => ['Important for technical tracks', 'Reduces corner slowdown'],
            'synergy_skills' => ['speed_003', 'passive_004'],
        ],

        // Passive Skill 2 Rare: Corner Expert
        [
            'name' => 'Corner Expert',
            'internal_id' => 'passive_002_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'corners', 'effect' => 'speed_maintenance', 'power' => 'high'],
            'description' => 'Enhanced version - Greatly maintains speed through corners',
            'meta_tier' => 'A',
            'strategic_notes' => ['Evolution of Corner Master', 'Dominates curved tracks'],
            'synergy_skills' => ['speed_003', 'passive_004'],
        ],

        // =====================================================================
        // RECOVERY SKILLS (from ComprehensiveSkillSeeder)
        // =====================================================================

        // Recovery Skill 1: Recovery
        [
            'name' => 'Recovery',
            'internal_id' => 'recovery_001',
            'skill_type' => 'recovery',
            'rarity' => 'normal',
            'base_sp_cost' => 160,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'stamina_recovery', 'power' => 'medium'],
            'description' => 'Recovers stamina during the race',
            'activation_conditions' => ['stamina_threshold' => 'below_50_percent'],
            'meta_tier' => 'A',
            'strategic_notes' => ['Critical for long races', 'Activates when needed most'],
            'synergy_skills' => ['passive_001', 'recovery_002'],
        ],

        // Recovery Skill 1 Rare: Full Recovery
        [
            'name' => 'Full Recovery',
            'internal_id' => 'recovery_001_rare',
            'skill_type' => 'recovery',
            'rarity' => 'rare',
            'base_sp_cost' => 220,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'mid_race', 'effect' => 'stamina_recovery', 'power' => 'high'],
            'description' => 'Enhanced version - Significantly recovers stamina during the race',
            'activation_conditions' => ['stamina_threshold' => 'below_60_percent'],
            'meta_tier' => 'S',
            'strategic_notes' => ['Evolution of Recovery', 'Game-changer for marathons'],
            'synergy_skills' => ['passive_001', 'recovery_002'],
        ],

        // Recovery Skill 2: Second Wind
        [
            'name' => 'Second Wind',
            'internal_id' => 'recovery_002',
            'skill_type' => 'recovery',
            'rarity' => 'normal',
            'base_sp_cost' => 170,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'late_race', 'effect' => 'stamina_boost', 'power' => 'medium'],
            'description' => 'Provides stamina boost in late race',
            'activation_conditions' => ['phase' => 'late_race'],
            'meta_tier' => 'B',
            'strategic_notes' => ['Good for final push', 'Complements Recovery'],
            'synergy_skills' => ['recovery_001', 'speed_001'],
        ],

        // =====================================================================
        // DEBUFF SKILLS (from ComprehensiveSkillSeeder)
        // =====================================================================

        // Debuff Skill 1: Blocking
        [
            'name' => 'Blocking',
            'internal_id' => 'debuff_001',
            'skill_type' => 'debuff',
            'rarity' => 'normal',
            'base_sp_cost' => 150,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'opponent_slowdown', 'power' => 'medium'],
            'description' => 'Slows down nearby opponents',
            'activation_conditions' => ['position' => 'front', 'opponents_nearby' => true],
            'meta_tier' => 'B',
            'strategic_notes' => ['Tactical skill', 'Best for front runners'],
            'synergy_skills' => ['speed_004', 'debuff_002'],
        ],

        // Debuff Skill 1 Rare: Perfect Blocking
        [
            'name' => 'Perfect Blocking',
            'internal_id' => 'debuff_001_rare',
            'skill_type' => 'debuff',
            'rarity' => 'rare',
            'base_sp_cost' => 210,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'mid_race', 'effect' => 'opponent_slowdown', 'power' => 'high'],
            'description' => 'Enhanced version - Significantly slows down nearby opponents',
            'activation_conditions' => ['position' => 'front', 'opponents_nearby' => true],
            'meta_tier' => 'A',
            'strategic_notes' => ['Evolution of Blocking', 'Disrupts opponent strategies'],
            'synergy_skills' => ['speed_004', 'debuff_002'],
        ],

        // Debuff Skill 2: Intimidation
        [
            'name' => 'Intimidation',
            'internal_id' => 'debuff_002',
            'skill_type' => 'debuff',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'start', 'effect' => 'opponent_hesitation', 'power' => 'low'],
            'description' => 'Causes opponents to hesitate at start',
            'activation_conditions' => ['phase' => 'start'],
            'meta_tier' => 'C',
            'strategic_notes' => ['Situational skill', 'Can secure early position'],
            'synergy_skills' => ['speed_004', 'debuff_001'],
        ],

        // =====================================================================
        // UNIQUE SKILLS (from ComprehensiveSkillSeeder)
        // =====================================================================

        // Unique Skill 1: Special Week's Determination
        [
            'name' => 'Special Week\'s Determination',
            'internal_id' => 'unique_001',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 300,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'massive_speed_boost', 'power' => 'very_high'],
            'description' => 'Special Week\'s signature skill - Massive speed boost in final straight',
            'activation_conditions' => ['character' => 'Special Week', 'phase' => 'final_straight'],
            'meta_tier' => 'S+',
            'strategic_notes' => ['Character-specific', 'Extremely powerful', 'Inheritable'],
            'synergy_skills' => ['speed_001', 'speed_003'],
        ],

        // Unique Skill 2: Silence Suzuka's Silent Step
        [
            'name' => 'Silence Suzuka\'s Silent Step',
            'internal_id' => 'unique_002',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 280,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'escape_speed', 'power' => 'very_high'],
            'description' => 'Silence Suzuka\'s signature skill - Creates distance from opponents',
            'activation_conditions' => ['character' => 'Silence Suzuka', 'phase' => 'mid_race'],
            'meta_tier' => 'S+',
            'strategic_notes' => ['Character-specific', 'Perfect for front runners', 'Inheritable'],
            'synergy_skills' => ['speed_004', 'passive_002'],
        ],

        // Unique Skill 3: Tokai Teio's Emperor's Dignity
        [
            'name' => 'Tokai Teio\'s Emperor\'s Dignity',
            'internal_id' => 'unique_003',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 320,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'comeback_boost', 'power' => 'very_high'],
            'description' => 'Tokai Teio\'s signature skill - Powerful comeback in final straight',
            'activation_conditions' => ['character' => 'Tokai Teio', 'phase' => 'final_straight', 'position' => 'behind'],
            'meta_tier' => 'S+',
            'strategic_notes' => ['Character-specific', 'Best for late closers', 'Inheritable'],
            'synergy_skills' => ['speed_002', 'recovery_001'],
        ],

        // =====================================================================
        // ADDITIONAL SPEED SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Last Spurt
        [
            'name' => 'Last Spurt',
            'internal_id' => 'speed_008',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Increases speed slightly in the final straight',
            'activation_conditions' => ['phase' => 'final_straight'],
            'meta_tier' => 'B',
        ],

        // Winning Formula (evolution of Last Spurt)
        [
            'name' => 'Winning Formula',
            'internal_id' => 'speed_008_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high'],
            'description' => 'Greatly increases speed in the final straight',
            'activation_conditions' => ['phase' => 'final_straight'],
            'meta_tier' => 'A',
        ],

        // All-Out
        [
            'name' => 'All-Out',
            'internal_id' => 'speed_009',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Gives everything for the win in the final straight',
            'activation_conditions' => ['phase' => 'final_straight', 'position' => 'middle_pack'],
            'meta_tier' => 'B',
        ],

        // Commendable Burst (evolution of All-Out)
        [
            'name' => 'Commendable Burst',
            'internal_id' => 'speed_009_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high'],
            'description' => 'Outstanding burst of speed in the final straight',
            'activation_conditions' => ['phase' => 'final_straight', 'position' => 'middle_pack'],
            'meta_tier' => 'A',
        ],

        // Thoroughbred's Pride
        [
            'name' => 'Thoroughbred\'s Pride',
            'internal_id' => 'speed_010',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Pride of a thoroughbred manifests as speed',
            'activation_conditions' => ['phase' => 'final_straight', 'mood' => 'good'],
            'meta_tier' => 'B',
        ],

        // =====================================================================
        // ACCELERATION SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Good Start
        [
            'name' => 'Good Start',
            'internal_id' => 'accel_001',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'start', 'effect' => 'acceleration', 'power' => 'medium'],
            'description' => 'Gets a good start from the gate',
            'activation_conditions' => ['phase' => 'start'],
            'meta_tier' => 'B',
        ],

        // Great Start (evolution of Good Start)
        [
            'name' => 'Great Start',
            'internal_id' => 'accel_001_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'start', 'effect' => 'acceleration', 'power' => 'high'],
            'description' => 'Gets an excellent start from the gate',
            'activation_conditions' => ['phase' => 'start'],
            'meta_tier' => 'A',
        ],

        // Pick Up the Pace
        [
            'name' => 'Pick Up the Pace',
            'internal_id' => 'accel_002',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'acceleration', 'power' => 'medium'],
            'description' => 'Accelerates during the mid-race phase',
            'activation_conditions' => ['phase' => 'mid_race'],
            'meta_tier' => 'B',
        ],

        // Rapid Acceleration (evolution of Pick Up the Pace)
        [
            'name' => 'Rapid Acceleration',
            'internal_id' => 'accel_002_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'mid_race', 'effect' => 'acceleration', 'power' => 'high'],
            'description' => 'Rapidly accelerates during the mid-race phase',
            'activation_conditions' => ['phase' => 'mid_race'],
            'meta_tier' => 'A',
        ],

        // Chase Down
        [
            'name' => 'Chase Down',
            'internal_id' => 'accel_003',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'medium'],
            'description' => 'Accelerates when chasing the leaders in the final straight',
            'activation_conditions' => ['phase' => 'final_straight', 'position' => 'behind_leaders'],
            'meta_tier' => 'B',
        ],

        // =====================================================================
        // STAMINA SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Conserve Energy
        [
            'name' => 'Conserve Energy',
            'internal_id' => 'stamina_001',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'medium'],
            'description' => 'Conserves stamina throughout the race',
            'meta_tier' => 'A',
        ],

        // Endless Stamina (evolution of Conserve Energy)
        [
            'name' => 'Endless Stamina',
            'internal_id' => 'stamina_001_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'high'],
            'description' => 'Greatly conserves stamina throughout the race',
            'meta_tier' => 'S',
        ],

        // Saving Energy
        [
            'name' => 'Saving Energy',
            'internal_id' => 'stamina_002',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'low'],
            'description' => 'Saves a bit of stamina during the race',
            'meta_tier' => 'B',
        ],

        // Breathing Technique
        [
            'name' => 'Breathing Technique',
            'internal_id' => 'stamina_003',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_recovery', 'power' => 'medium'],
            'description' => 'Proper breathing helps maintain stamina',
            'meta_tier' => 'A',
        ],

        // Perfect Breathing (evolution of Breathing Technique)
        [
            'name' => 'Perfect Breathing',
            'internal_id' => 'stamina_003_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 200,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'stamina_recovery', 'power' => 'high'],
            'description' => 'Masterful breathing technique maintains excellent stamina',
            'meta_tier' => 'S',
        ],

        // =====================================================================
        // POSITIONAL SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Front Runner
        [
            'name' => 'Front Runner',
            'internal_id' => 'position_001',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'speed_boost_when_leading', 'power' => 'medium'],
            'description' => 'Performs better when running in the lead',
            'activation_conditions' => ['position' => 'first'],
            'meta_tier' => 'A',
        ],

        // Leading the Pack (evolution of Front Runner)
        [
            'name' => 'Leading the Pack',
            'internal_id' => 'position_001_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'speed_boost_when_leading', 'power' => 'high'],
            'description' => 'Excels when running in the lead',
            'activation_conditions' => ['position' => 'first'],
            'meta_tier' => 'S',
        ],

        // Stalker
        [
            'name' => 'Stalker',
            'internal_id' => 'position_002',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'speed_boost_mid_pack', 'power' => 'medium'],
            'description' => 'Performs well when running in the middle of the pack',
            'activation_conditions' => ['position' => 'middle_pack'],
            'meta_tier' => 'A',
        ],

        // Perfect Position (evolution of Stalker)
        [
            'name' => 'Perfect Position',
            'internal_id' => 'position_002_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'speed_boost_mid_pack', 'power' => 'high'],
            'description' => 'Excels when running in the middle of the pack',
            'activation_conditions' => ['position' => 'middle_pack'],
            'meta_tier' => 'S',
        ],

        // Closer
        [
            'name' => 'Closer',
            'internal_id' => 'position_003',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost_from_behind', 'power' => 'medium'],
            'description' => 'Speeds up when coming from behind in the final straight',
            'activation_conditions' => ['phase' => 'final_straight', 'position' => 'behind'],
            'meta_tier' => 'A',
        ],

        // Legendary Closer (evolution of Closer)
        [
            'name' => 'Legendary Closer',
            'internal_id' => 'position_003_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 200,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'final_straight', 'effect' => 'speed_boost_from_behind', 'power' => 'high'],
            'description' => 'Dramatically speeds up when coming from behind in the final straight',
            'activation_conditions' => ['phase' => 'final_straight', 'position' => 'behind'],
            'meta_tier' => 'S',
        ],

        // =====================================================================
        // GATE SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Gate Mastery
        [
            'name' => 'Gate Mastery',
            'internal_id' => 'gate_001',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 110,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'gate', 'effect' => 'smooth_start', 'power' => 'medium'],
            'description' => 'Smooth start from the gate',
            'activation_conditions' => ['phase' => 'gate'],
            'meta_tier' => 'B',
        ],

        // Perfect Gate (evolution of Gate Mastery)
        [
            'name' => 'Perfect Gate',
            'internal_id' => 'gate_001_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 170,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'gate', 'effect' => 'smooth_start', 'power' => 'high'],
            'description' => 'Flawless start from the gate',
            'activation_conditions' => ['phase' => 'gate'],
            'meta_tier' => 'A',
        ],

        // Sharp Start
        [
            'name' => 'Sharp Start',
            'internal_id' => 'gate_002',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'gate', 'effect' => 'acceleration', 'power' => 'medium'],
            'description' => 'Quick acceleration from the gate',
            'activation_conditions' => ['phase' => 'gate'],
            'meta_tier' => 'B',
        ],

        // =====================================================================
        // CORNER SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Smooth Turn
        [
            'name' => 'Smooth Turn',
            'internal_id' => 'corner_003',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 120,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'corner', 'effect' => 'speed_maintenance', 'power' => 'medium'],
            'description' => 'Maintains speed through corners',
            'activation_conditions' => ['phase' => 'corner'],
            'meta_tier' => 'B',
        ],

        // Lightning Turn (evolution of Smooth Turn)
        [
            'name' => 'Lightning Turn',
            'internal_id' => 'corner_003_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 180,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'corner', 'effect' => 'speed_maintenance', 'power' => 'high'],
            'description' => 'Excellently maintains speed through corners',
            'activation_conditions' => ['phase' => 'corner'],
            'meta_tier' => 'A',
        ],

        // Cornering Acceleration
        [
            'name' => 'Cornering Acceleration',
            'internal_id' => 'corner_004',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'corner', 'effect' => 'acceleration_out', 'power' => 'medium'],
            'description' => 'Accelerates coming out of corners',
            'activation_conditions' => ['phase' => 'corner_exit'],
            'meta_tier' => 'A',
        ],

        // =====================================================================
        // DEBUFF RESISTANCE SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Mental Fortitude
        [
            'name' => 'Mental Fortitude',
            'internal_id' => 'resist_001',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'debuff_resistance', 'power' => 'medium'],
            'description' => 'Resists mental pressure from opponents',
            'meta_tier' => 'B',
        ],

        // Unshakeable Will (evolution of Mental Fortitude)
        [
            'name' => 'Unshakeable Will',
            'internal_id' => 'resist_001_rare',
            'skill_type' => 'passive',
            'rarity' => 'rare',
            'base_sp_cost' => 200,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'passive', 'effect' => 'debuff_resistance', 'power' => 'high'],
            'description' => 'Strongly resists mental pressure from opponents',
            'meta_tier' => 'A',
        ],

        // Focus
        [
            'name' => 'Focus',
            'internal_id' => 'resist_002',
            'skill_type' => 'passive',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'passive', 'effect' => 'concentration', 'power' => 'medium'],
            'description' => 'Maintains focus during the race',
            'meta_tier' => 'B',
        ],

        // =====================================================================
        // COMPETITIVENESS SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Fighting Spirit
        [
            'name' => 'Fighting Spirit',
            'internal_id' => 'competitive_001',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 130,
            'can_evolve' => true,
            'is_evolution' => false,
            'effects' => ['activation' => 'when_overtaken', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Gains speed when overtaken by opponents',
            'activation_conditions' => ['trigger' => 'overtaken'],
            'meta_tier' => 'B',
        ],

        // Burning Spirit (evolution of Fighting Spirit)
        [
            'name' => 'Burning Spirit',
            'internal_id' => 'competitive_001_rare',
            'skill_type' => 'speed',
            'rarity' => 'rare',
            'base_sp_cost' => 190,
            'can_evolve' => false,
            'is_evolution' => true,
            'effects' => ['activation' => 'when_overtaken', 'effect' => 'speed_boost', 'power' => 'high'],
            'description' => 'Greatly gains speed when overtaken by opponents',
            'activation_conditions' => ['trigger' => 'overtaken'],
            'meta_tier' => 'A',
        ],

        // Rivalry
        [
            'name' => 'Rivalry',
            'internal_id' => 'competitive_002',
            'skill_type' => 'speed',
            'rarity' => 'normal',
            'base_sp_cost' => 140,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'near_rivals', 'effect' => 'speed_boost', 'power' => 'medium'],
            'description' => 'Gains speed when running near rival characters',
            'activation_conditions' => ['proximity' => 'near_rivals'],
            'meta_tier' => 'B',
        ],

        // =====================================================================
        // ADDITIONAL UNIQUE CHARACTER SKILLS (from RealUmaMusumeSkillsSeeder)
        // =====================================================================

        // Vodka's Berserker Mode
        [
            'name' => 'Vodka\'s Berserker Mode',
            'internal_id' => 'unique_004',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 290,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'extreme_speed', 'power' => 'very_high'],
            'description' => 'Vodka\'s signature aggressive running style',
            'activation_conditions' => ['character' => 'Vodka'],
            'meta_tier' => 'S',
        ],

        // Daiwa Scarlet's Crimson Blaze
        [
            'name' => 'Daiwa Scarlet\'s Crimson Blaze',
            'internal_id' => 'unique_005',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 310,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'overwhelming_speed', 'power' => 'very_high'],
            'description' => 'Daiwa Scarlet\'s blazing finish',
            'activation_conditions' => ['character' => 'Daiwa Scarlet'],
            'meta_tier' => 'S+',
        ],

        // Gold Ship's Unpredictability
        [
            'name' => 'Gold Ship\'s Unpredictability',
            'internal_id' => 'unique_006',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 270,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'random', 'effect' => 'chaos_boost', 'power' => 'variable'],
            'description' => 'Gold Ship\'s chaotic and unpredictable racing',
            'activation_conditions' => ['character' => 'Gold Ship'],
            'meta_tier' => 'A',
        ],

        // Mejiro McQueen's Grace
        [
            'name' => 'Mejiro McQueen\'s Grace',
            'internal_id' => 'unique_007',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 280,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'long_race', 'effect' => 'stamina_speed', 'power' => 'very_high'],
            'description' => 'McQueen\'s graceful long-distance dominance',
            'activation_conditions' => ['character' => 'Mejiro McQueen', 'distance' => 'long'],
            'meta_tier' => 'S',
        ],

        // Grass Wonder's Miracle Run
        [
            'name' => 'Grass Wonder\'s Miracle Run',
            'internal_id' => 'unique_008',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 300,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'miracle_speed', 'power' => 'very_high'],
            'description' => 'Grass Wonder\'s miraculous comeback ability',
            'activation_conditions' => ['character' => 'Grass Wonder'],
            'meta_tier' => 'S',
        ],

        // El Condor Pasa's Flame
        [
            'name' => 'El Condor Pasa\'s Flame',
            'internal_id' => 'unique_009',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 295,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_to_final', 'effect' => 'passionate_surge', 'power' => 'very_high'],
            'description' => 'El Condor Pasa\'s passionate racing spirit',
            'activation_conditions' => ['character' => 'El Condor Pasa'],
            'meta_tier' => 'S',
        ],

        // Narita Brian's Black Thunder
        [
            'name' => 'Narita Brian\'s Black Thunder',
            'internal_id' => 'unique_010',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 320,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'final_straight', 'effect' => 'devastating_speed', 'power' => 'very_high'],
            'description' => 'Narita Brian\'s overwhelming final kick',
            'activation_conditions' => ['character' => 'Narita Brian'],
            'meta_tier' => 'S+',
        ],

        // T.M. Opera O's Symphony
        [
            'name' => 'T.M. Opera O\'s Symphony',
            'internal_id' => 'unique_011',
            'skill_type' => 'unique',
            'rarity' => 'unique',
            'base_sp_cost' => 310,
            'can_evolve' => false,
            'is_evolution' => false,
            'effects' => ['activation' => 'mid_race', 'effect' => 'perfect_rhythm', 'power' => 'very_high'],
            'description' => 'T.M. Opera O\'s perfect racing rhythm',
            'activation_conditions' => ['character' => 'T.M. Opera O'],
            'meta_tier' => 'S+',
        ],
    ],

    // =========================================================================
    // EVOLUTION PAIRS
    // =========================================================================
    // Format: [source_internal_id, target_internal_id]
    // Source is the normal skill, target is the rare/evolved version
    // =========================================================================

    'evolution_pairs' => [
        // From ComprehensiveSkillSeeder
        ['speed_001', 'speed_001_rare'],      // Go with the Flow → Lane Legerdemain
        ['speed_002', 'speed_002_rare'],      // Homestretch Haste → In Body and Mind
        ['speed_004', 'speed_004_rare'],      // Sprint Turbo → Rocket Start
        ['passive_001', 'passive_001_rare'],  // Stamina Keeper → Stamina Master
        ['passive_002', 'passive_002_rare'],  // Corner Master → Corner Expert
        ['recovery_001', 'recovery_001_rare'], // Recovery → Full Recovery
        ['debuff_001', 'debuff_001_rare'],    // Blocking → Perfect Blocking

        // From RealUmaMusumeSkillsSeeder
        ['speed_008', 'speed_008_rare'],      // Last Spurt → Winning Formula
        ['speed_009', 'speed_009_rare'],      // All-Out → Commendable Burst
        ['accel_001', 'accel_001_rare'],      // Good Start → Great Start
        ['accel_002', 'accel_002_rare'],      // Pick Up the Pace → Rapid Acceleration
        ['stamina_001', 'stamina_001_rare'],  // Conserve Energy → Endless Stamina
        ['stamina_003', 'stamina_003_rare'],  // Breathing Technique → Perfect Breathing
        ['position_001', 'position_001_rare'], // Front Runner → Leading the Pack
        ['position_002', 'position_002_rare'], // Stalker → Perfect Position
        ['position_003', 'position_003_rare'], // Closer → Legendary Closer
        ['gate_001', 'gate_001_rare'],        // Gate Mastery → Perfect Gate
        ['corner_003', 'corner_003_rare'],    // Smooth Turn → Lightning Turn
        ['resist_001', 'resist_001_rare'],    // Mental Fortitude → Unshakeable Will
        ['competitive_001', 'competitive_001_rare'], // Fighting Spirit → Burning Spirit
    ],
];
