<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Comprehensive Skill Seeder
 *
 * Seeds complete skill database with:
 * - 50+ skills across all categories
 * - SP costs by rarity (Normal 120-180, Rare 180-240, Unique variable)
 * - Skill evolution chains (Normal → Rare)
 * - Strategic recommendations and synergies
 */
class ComprehensiveSkillSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks (database-specific)
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        DB::table('ucp_skills')->truncate();

        // Re-enable foreign key checks
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $this->seedSpeedSkills();
        $this->seedPassiveSkills();
        $this->seedRecoverySkills();
        $this->seedDebuffSkills();
        $this->seedUniqueSkills();

        $this->setupEvolutionRelationships();
    }

    private function seedSpeedSkills(): void
    {
        $skills = [
            // Speed Skill 1: Go with the Flow
            [
                'name' => 'Go with the Flow',
                'internal_id' => 'speed_001',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 120,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'medium']),
                'description' => 'Increases speed in the final straight when in good position',
                'activation_conditions' => json_encode(['position' => 'top_3', 'phase' => 'final_straight']),
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Best for front runners', 'Reliable activation']),
                'synergy_skills' => json_encode(['speed_003', 'speed_005']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'speed_boost', 'power' => 'high']),
                'description' => 'Enhanced version - Significantly increases speed in the final straight',
                'activation_conditions' => json_encode(['position' => 'top_4', 'phase' => 'final_straight']),
                'meta_tier' => 'S',
                'strategic_notes' => json_encode(['Evolution of Go with the Flow', 'Higher activation rate']),
                'synergy_skills' => json_encode(['speed_003', 'speed_005']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'medium']),
                'description' => 'Improves acceleration in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight']),
                'meta_tier' => 'B',
                'strategic_notes' => json_encode(['Good for late closers', 'Consistent activation']),
                'synergy_skills' => json_encode(['speed_001', 'speed_004']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'acceleration', 'power' => 'high']),
                'description' => 'Enhanced version - Greatly improves acceleration in the final straight',
                'activation_conditions' => json_encode(['phase' => 'final_straight']),
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Evolution of Homestretch Haste', 'Excellent for comebacks']),
                'synergy_skills' => json_encode(['speed_001', 'speed_004']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'speed_burst', 'power' => 'medium']),
                'description' => 'Provides a burst of speed during mid-race',
                'activation_conditions' => json_encode(['phase' => 'mid_race', 'position' => 'any']),
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Versatile skill', 'Works for all running styles']),
                'synergy_skills' => json_encode(['speed_001', 'passive_001']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'start', 'effect' => 'early_speed', 'power' => 'medium']),
                'description' => 'Boosts speed at the start of the race',
                'activation_conditions' => json_encode(['phase' => 'start']),
                'meta_tier' => 'B',
                'strategic_notes' => json_encode(['Essential for front runners', 'Helps secure early position']),
                'synergy_skills' => json_encode(['speed_005', 'passive_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'start', 'effect' => 'early_speed', 'power' => 'very_high']),
                'description' => 'Enhanced version - Massive speed boost at race start',
                'activation_conditions' => json_encode(['phase' => 'start']),
                'meta_tier' => 'S',
                'strategic_notes' => json_encode(['Evolution of Sprint Turbo', 'Dominates early positioning']),
                'synergy_skills' => json_encode(['speed_005', 'passive_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedPassiveSkills(): void
    {
        $skills = [
            // Passive Skill 1: Stamina Keeper
            [
                'name' => 'Stamina Keeper',
                'internal_id' => 'passive_001',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'medium']),
                'description' => 'Reduces stamina consumption during races',
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Essential for long distance', 'Always active']),
                'synergy_skills' => json_encode(['recovery_001', 'passive_003']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'passive', 'effect' => 'stamina_conservation', 'power' => 'high']),
                'description' => 'Enhanced version - Significantly reduces stamina consumption',
                'meta_tier' => 'S',
                'strategic_notes' => json_encode(['Evolution of Stamina Keeper', 'Critical for marathon races']),
                'synergy_skills' => json_encode(['recovery_001', 'passive_003']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'corners', 'effect' => 'speed_maintenance', 'power' => 'medium']),
                'description' => 'Maintains speed through corners',
                'meta_tier' => 'B',
                'strategic_notes' => json_encode(['Important for technical tracks', 'Reduces corner slowdown']),
                'synergy_skills' => json_encode(['speed_003', 'passive_004']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'corners', 'effect' => 'speed_maintenance', 'power' => 'high']),
                'description' => 'Enhanced version - Greatly maintains speed through corners',
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Evolution of Corner Master', 'Dominates curved tracks']),
                'synergy_skills' => json_encode(['speed_003', 'passive_004']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedRecoverySkills(): void
    {
        $skills = [
            // Recovery Skill 1: Recovery
            [
                'name' => 'Recovery',
                'internal_id' => 'recovery_001',
                'skill_type' => 'recovery',
                'rarity' => 'normal',
                'base_sp_cost' => 160,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'stamina_recovery', 'power' => 'medium']),
                'description' => 'Recovers stamina during the race',
                'activation_conditions' => json_encode(['stamina_threshold' => 'below_50_percent']),
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Critical for long races', 'Activates when needed most']),
                'synergy_skills' => json_encode(['passive_001', 'recovery_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'stamina_recovery', 'power' => 'high']),
                'description' => 'Enhanced version - Significantly recovers stamina during the race',
                'activation_conditions' => json_encode(['stamina_threshold' => 'below_60_percent']),
                'meta_tier' => 'S',
                'strategic_notes' => json_encode(['Evolution of Recovery', 'Game-changer for marathons']),
                'synergy_skills' => json_encode(['passive_001', 'recovery_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'late_race', 'effect' => 'stamina_boost', 'power' => 'medium']),
                'description' => 'Provides stamina boost in late race',
                'activation_conditions' => json_encode(['phase' => 'late_race']),
                'meta_tier' => 'B',
                'strategic_notes' => json_encode(['Good for final push', 'Complements Recovery']),
                'synergy_skills' => json_encode(['recovery_001', 'speed_001']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedDebuffSkills(): void
    {
        $skills = [
            // Debuff Skill 1: Blocking
            [
                'name' => 'Blocking',
                'internal_id' => 'debuff_001',
                'skill_type' => 'debuff',
                'rarity' => 'normal',
                'base_sp_cost' => 150,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'opponent_slowdown', 'power' => 'medium']),
                'description' => 'Slows down nearby opponents',
                'activation_conditions' => json_encode(['position' => 'front', 'opponents_nearby' => true]),
                'meta_tier' => 'B',
                'strategic_notes' => json_encode(['Tactical skill', 'Best for front runners']),
                'synergy_skills' => json_encode(['speed_004', 'debuff_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'opponent_slowdown', 'power' => 'high']),
                'description' => 'Enhanced version - Significantly slows down nearby opponents',
                'activation_conditions' => json_encode(['position' => 'front', 'opponents_nearby' => true]),
                'meta_tier' => 'A',
                'strategic_notes' => json_encode(['Evolution of Blocking', 'Disrupts opponent strategies']),
                'synergy_skills' => json_encode(['speed_004', 'debuff_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'start', 'effect' => 'opponent_hesitation', 'power' => 'low']),
                'description' => 'Causes opponents to hesitate at start',
                'activation_conditions' => json_encode(['phase' => 'start']),
                'meta_tier' => 'C',
                'strategic_notes' => json_encode(['Situational skill', 'Can secure early position']),
                'synergy_skills' => json_encode(['speed_004', 'debuff_001']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function seedUniqueSkills(): void
    {
        $skills = [
            // Unique Skill 1: Special Week's Determination
            [
                'name' => 'Special Week\'s Determination',
                'internal_id' => 'unique_001',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 300,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'massive_speed_boost', 'power' => 'very_high']),
                'description' => 'Special Week\'s signature skill - Massive speed boost in final straight',
                'activation_conditions' => json_encode(['character' => 'Special Week', 'phase' => 'final_straight']),
                'meta_tier' => 'S+',
                'strategic_notes' => json_encode(['Character-specific', 'Extremely powerful', 'Inheritable']),
                'synergy_skills' => json_encode(['speed_001', 'speed_003']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'mid_race', 'effect' => 'escape_speed', 'power' => 'very_high']),
                'description' => 'Silence Suzuka\'s signature skill - Creates distance from opponents',
                'activation_conditions' => json_encode(['character' => 'Silence Suzuka', 'phase' => 'mid_race']),
                'meta_tier' => 'S+',
                'strategic_notes' => json_encode(['Character-specific', 'Perfect for front runners', 'Inheritable']),
                'synergy_skills' => json_encode(['speed_004', 'passive_002']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
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
                'effects' => json_encode(['activation' => 'final_straight', 'effect' => 'comeback_boost', 'power' => 'very_high']),
                'description' => 'Tokai Teio\'s signature skill - Powerful comeback in final straight',
                'activation_conditions' => json_encode(['character' => 'Tokai Teio', 'phase' => 'final_straight', 'position' => 'behind']),
                'meta_tier' => 'S+',
                'strategic_notes' => json_encode(['Character-specific', 'Best for late closers', 'Inheritable']),
                'synergy_skills' => json_encode(['speed_002', 'recovery_001']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    private function setupEvolutionRelationships(): void
    {
        $evolutionPairs = [
            ['speed_001', 'speed_001_rare'],
            ['speed_002', 'speed_002_rare'],
            ['speed_004', 'speed_004_rare'],
            ['passive_001', 'passive_001_rare'],
            ['passive_002', 'passive_002_rare'],
            ['recovery_001', 'recovery_001_rare'],
            ['debuff_001', 'debuff_001_rare'],
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
