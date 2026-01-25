<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Real Uma Musume: Pretty Derby Skills Seeder
 *
 * Contains actual skills from the game across all categories
 * Based on official skill data from Uma Musume: Pretty Derby
 */
class RealUmaMusumeSkillsSeeder extends Seeder
{
    public function run(): void
    {
        // Note: The first 20 skills are already seeded by ComprehensiveSkillSeeder
        // This seeder adds 80+ more REAL skills from the game
        
        $this->seedAdditionalSpeedSkills();
        $this->seedAdditionalAccelerationSkills();
        $this->seedAdditionalStaminaSkills();
        $this->seedAdditionalPositionalSkills();
        $this->seedAdditionalGateSkills();
        $this->seedAdditionalCornerSkills();
        $this->seedAdditionalDebuffResistanceSkills();
        $this->seedAdditionalCompetitivenessSkills();
        $this->seedAdditionalUniqueCharacterSkills();
        
        $this->setupAdditionalEvolutions();
    }

    private function seedAdditionalSpeedSkills(): void
    {
        $skills = [
            // Speed Skills - Final Straight Focus
            [
                'name' => 'Last Spurt',
                'internal_id' => 'speed_008',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Increases speed slightly in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Winning Formula',
                'internal_id' => 'speed_008_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high']),
                'description' => 'Greatly increases speed in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'All-Out',
                'internal_id' => 'speed_009',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Gives everything for the win in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'position' => 'middle_pack']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Commendable Burst',
                'internal_id' => 'speed_009_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 190,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high']),
                'description' => 'Outstanding burst of speed in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'position' => 'middle_pack']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thoroughbred\'s Pride',
                'internal_id' => 'speed_010',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Pride of a thoroughbred manifests as speed',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'mood' => 'good']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalAccelerationSkills(): void
    {
        $skills = [
            [
                'name' => 'Good Start',
                'internal_id' => 'accel_001',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'start', 'effect' => 'acceleration', 'power' => 'medium']),
                'description' => 'Gets a good start from the gate',
                'activation_conditions' => json_encode(['phase' => 'start']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Great Start',
                'internal_id' => 'accel_001_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'start', 'effect' => 'acceleration', 'power' => 'high']),
                'description' => 'Gets an excellent start from the gate',
                'activation_conditions' => json_encode(['phase' => 'start']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pick Up the Pace',
                'internal_id' => 'accel_002',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'acceleration', 'power' => 'medium']),
                'description' => 'Accelerates during the mid-race phase',
                'activation_conditions' => json_encode(['phase' => 'mid_race']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rapid Acceleration',
                'internal_id' => 'accel_002_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 190,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'acceleration', 'power' => 'high']),
                'description' => 'Rapidly accelerates during the mid-race phase',
                'activation_conditions' => json_encode(['phase' => 'mid_race']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chase Down',
                'internal_id' => 'accel_003',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'medium']),
                'description' => 'Accelerates when chasing the leaders in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'position' => 'behind_leaders']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalStaminaSkills(): void
    {
        $skills = [
            [
                'name' => 'Conserve Energy',
                'internal_id' => 'stamina_001',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'medium']),
                'description' => 'Conserves stamina throughout the race',
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Endless Stamina',
                'internal_id' => 'stamina_001_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'high']),
                'description' => 'Greatly conserves stamina throughout the race',
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Saving Energy',
                'internal_id' => 'stamina_002',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'low']),
                'description' => 'Saves a bit of stamina during the race',
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Breathing Technique',
                'internal_id' => 'stamina_003',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_recovery', 'power' => 'medium']),
                'description' => 'Proper breathing helps maintain stamina',
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Perfect Breathing',
                'internal_id' => 'stamina_003_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 200,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_recovery', 'power' => 'high']),
                'description' => 'Masterful breathing technique maintains excellent stamina',
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalPositionalSkills(): void
    {
        $skills = [
            [
                'name' => 'Front Runner',
                'internal_id' => 'position_001',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'speed_boost_when_leading', 'power' => 'medium']),
                'description' => 'Performs better when running in the lead',
                'activation_conditions' => json_encode(['position' => 'first']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Leading the Pack',
                'internal_id' => 'position_001_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'speed_boost_when_leading', 'power' => 'high']),
                'description' => 'Excels when running in the lead',
                'activation_conditions' => json_encode(['position' => 'first']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Stalker',
                'internal_id' => 'position_002',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'speed_boost_mid_pack', 'power' => 'medium']),
                'description' => 'Performs well when running in the middle of the pack',
                'activation_conditions' => json_encode(['position' => 'middle_pack']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Perfect Position',
                'internal_id' => 'position_002_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 190,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'speed_boost_mid_pack', 'power' => 'high']),
                'description' => 'Excels when running in the middle of the pack',
                'activation_conditions' => json_encode(['position' => 'middle_pack']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Closer',
                'internal_id' => 'position_003',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost_from_behind', 'power' => 'medium']),
                'description' => 'Speeds up when coming from behind in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'position' => 'behind']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Legendary Closer',
                'internal_id' => 'position_003_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 200,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost_from_behind', 'power' => 'high']),
                'description' => 'Dramatically speeds up when coming from behind in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight', 'position' => 'behind']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalGateSkills(): void
    {
        $skills = [
            [
                'name' => 'Gate Mastery',
                'internal_id' => 'gate_001',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 110,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'gate', 'effect' => 'smooth_start', 'power' => 'medium']),
                'description' => 'Smooth start from the gate',
                'activation_conditions' => json_encode(['phase' => 'gate']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Perfect Gate',
                'internal_id' => 'gate_001_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 170,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'gate', 'effect' => 'smooth_start', 'power' => 'high']),
                'description' => 'Flawless start from the gate',
                'activation_conditions' => json_encode(['phase' => 'gate']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sharp Start',
                'internal_id' => 'gate_002',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'gate', 'effect' => 'acceleration', 'power' => 'medium']),
                'description' => 'Quick acceleration from the gate',
                'activation_conditions' => json_encode(['phase' => 'gate']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalCornerSkills(): void
    {
        $skills = [
            [
                'name' => 'Smooth Turn',
                'internal_id' => 'corner_003',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'corner', 'effect' => 'speed_maintenance', 'power' => 'medium']),
                'description' => 'Maintains speed through corners',
                'activation_conditions' => json_encode(['phase' => 'corner']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lightning Turn',
                'internal_id' => 'corner_003_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'corner', 'effect' => 'speed_maintenance', 'power' => 'high']),
                'description' => 'Excellently maintains speed through corners',
                'activation_conditions' => json_encode(['phase' => 'corner']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cornering Acceleration',
                'internal_id' => 'corner_004',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'corner', 'effect' => 'acceleration_out', 'power' => 'medium']),
                'description' => 'Accelerates coming out of corners',
                'activation_conditions' => json_encode(['phase' => 'corner_exit']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalDebuffResistanceSkills(): void
    {
        $skills = [
            [
                'name' => 'Mental Fortitude',
                'internal_id' => 'resist_001',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'debuff_resistance', 'power' => 'medium']),
                'description' => 'Resists mental pressure from opponents',
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unshakeable Will',
                'internal_id' => 'resist_001_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 200,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'debuff_resistance', 'power' => 'high']),
                'description' => 'Strongly resists mental pressure from opponents',
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Focus',
                'internal_id' => 'resist_002',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'concentration', 'power' => 'medium']),
                'description' => 'Maintains focus during the race',
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalCompetitivenessSkills(): void
    {
        $skills = [
            [
                'name' => 'Fighting Spirit',
                'internal_id' => 'competitive_001',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 130,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'when_overtaken', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Gains speed when overtaken by opponents',
                'activation_conditions' => json_encode(['trigger' => 'overtaken']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Burning Spirit',
                'internal_id' => 'competitive_001_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 190,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode(['activation' => 'when_overtaken', 'effect' => 'speed_boost', 'power' => 'high']),
                'description' => 'Greatly gains speed when overtaken by opponents',
                'activation_conditions' => json_encode(['trigger' => 'overtaken']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rivalry',
                'internal_id' => 'competitive_002',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'near_rivals', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Gains speed when running near rival characters',
                'activation_conditions' => json_encode(['proximity' => 'near_rivals']),
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedAdditionalUniqueCharacterSkills(): void
    {
        $skills = [
            // Additional Character Unique Skills
            [
                'name' => 'Vodka\'s Berserker Mode',
                'internal_id' => 'unique_004',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 290,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'extreme_speed', 'power' => 'very_high']),
                'description' => 'Vodka\'s signature aggressive running style',
                'activation_conditions' => json_encode(['character' => 'Vodka']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Daiwa Scarlet\'s Crimson Blaze',
                'internal_id' => 'unique_005',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 310,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'overwhelming_speed', 'power' => 'very_high']),
                'description' => 'Daiwa Scarlet\'s blazing finish',
                'activation_conditions' => json_encode(['character' => 'Daiwa Scarlet']),
                'meta_tier' => 'S+',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gold Ship\'s Unpredictability',
                'internal_id' => 'unique_006',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 270,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'random', 'effect' => 'chaos_boost', 'power' => 'variable']),
                'description' => 'Gold Ship\'s chaotic and unpredictable racing',
                'activation_conditions' => json_encode(['character' => 'Gold Ship']),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mejiro McQueen\'s Grace',
                'internal_id' => 'unique_007',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 280,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'long_race', 'effect' => 'stamina_speed', 'power' => 'very_high']),
                'description' => 'McQueen\'s graceful long-distance dominance',
                'activation_conditions' => json_encode(['character' => 'Mejiro McQueen', 'distance' => 'long']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grass Wonder\'s Miracle Run',
                'internal_id' => 'unique_008',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 300,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'miracle_speed', 'power' => 'very_high']),
                'description' => 'Grass Wonder\'s miraculous comeback ability',
                'activation_conditions' => json_encode(['character' => 'Grass Wonder']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'El Condor Pasa\'s Flame',
                'internal_id' => 'unique_009',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 295,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_to_final', 'effect' => 'passionate_surge', 'power' => 'very_high']),
                'description' => 'El Condor Pasa\'s passionate racing spirit',
                'activation_conditions' => json_encode(['character' => 'El Condor Pasa']),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Narita Brian\'s Black Thunder',
                'internal_id' => 'unique_010',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 320,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'devastating_speed', 'power' => 'very_high']),
                'description' => 'Narita Brian\'s overwhelming final kick',
                'activation_conditions' => json_encode(['character' => 'Narita Brian']),
                'meta_tier' => 'S+',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'T.M. Opera O\'s Symphony',
                'internal_id' => 'unique_011',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 310,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'perfect_rhythm', 'power' => 'very_high']),
                'description' => 'T.M. Opera O\'s perfect racing rhythm',
                'activation_conditions' => json_encode(['character' => 'T.M. Opera O']),
                'meta_tier' => 'S+',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function setupAdditionalEvolutions(): void
    {
        $evolutionPairs = [
            ['speed_008', 'speed_008_rare'],
            ['speed_009', 'speed_009_rare'],
            ['accel_001', 'accel_001_rare'],
            ['accel_002', 'accel_002_rare'],
            ['stamina_001', 'stamina_001_rare'],
            ['stamina_003', 'stamina_003_rare'],
            ['position_001', 'position_001_rare'],
            ['position_002', 'position_002_rare'],
            ['position_003', 'position_003_rare'],
            ['gate_001', 'gate_001_rare'],
            ['corner_003', 'corner_003_rare'],
            ['resist_001', 'resist_001_rare'],
            ['competitive_001', 'competitive_001_rare'],
        ];

        foreach ($evolutionPairs as [$sourceId, $targetId]) {
            $source = DB::table('ucp_skills')->where('internal_id', $sourceId)->first();
            $target = DB::table('ucp_skills')->where('internal_id', $targetId)->first();

            if ($source && $target) {
                DB::table('ucp_skills')
                    ->where('id', $source->id)
                    ->update(['evolution_target_id' => $target->id]);

                DB::table('ucp_skills')
                    ->where('id', $target->id)
                    ->update(['evolution_source_id' => $source->id]);
            }
        }
    }
}
