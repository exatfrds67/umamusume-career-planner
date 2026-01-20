<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UcpSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing skills
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ucp_skills')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert all skills
        $skills = $this->getAllSkills();

        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }
    }

    /**
     * Get all skills data.
     */
    private function getAllSkills(): array
    {
        return $this->getSpeedSkills();
    }

    /**
     * Get speed skills (Normal: 120-180 SP, Rare: 180-240 SP).
     */
    private function getSpeedSkills(): array
    {
        $now = now();

        return [
            // Normal Speed Skills
            [
                'name' => 'Go with the Flow',
                'internal_id' => 'speed_001',
                'skill_type' => 'speed',
                'rarity' => 'normal',
                'base_sp_cost' => 120,

                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode([
                    'activation' => 'final_straight',
                    'effect' => 'speed_boost',
                    'duration' => 'short',
                    'power' => 'medium',
                ]),
                'description' => 'Increases speed in the final straight when in good position',
                'activation_conditions' => json_encode([
                    'position' => 'top_3',
                    'phase' => 'final_straight',
                ]),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Go with the Flow+',
                'internal_id' => 'skill_001_rare',
                'skill_type' => 'speed',
                'rarity' => 'rare',
                'base_sp_cost' => 180,
                'can_evolve' => false,
                'is_evolution' => true,
                'effects' => json_encode([
                    'activation' => 'final_straight',
                    'effect' => 'speed_boost',
                    'duration' => 'medium',
                    'power' => 'high',
                ]),
                'description' => 'Enhanced version - Significantly increases speed in the final straight when in good position',
                'activation_conditions' => json_encode([
                    'position' => 'top_4',
                    'phase' => 'final_straight',
                ]),
                'meta_tier' => 'S',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Passive Skills
            [
                'name' => 'Stamina Keeper',
                'internal_id' => 'skill_002',
                'skill_type' => 'passive',
                'rarity' => 'normal',
                'base_sp_cost' => 140,
                'can_evolve' => true,
                'is_evolution' => false,
                'effects' => json_encode([
                    'activation' => 'passive',
                    'effect' => 'stamina_conservation',
                    'power' => 'medium',
                ]),
                'description' => 'Reduces stamina consumption during races',
                'meta_tier' => 'B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Recovery Skills
            [
                'name' => 'Recovery',
                'internal_id' => 'skill_003',
                'skill_type' => 'recovery',
                'rarity' => 'normal',
                'base_sp_cost' => 160,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode([
                    'activation' => 'mid_race',
                    'effect' => 'stamina_recovery',
                    'power' => 'medium',
                ]),
                'description' => 'Recovers stamina during the race',
                'activation_conditions' => json_encode([
                    'stamina_threshold' => 'below_50_percent',
                ]),
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Unique Skills
            [
                'name' => 'Special Week\'s Determination',
                'internal_id' => 'skill_unique_001',
                'skill_type' => 'unique',
                'rarity' => 'unique',
                'base_sp_cost' => 300,
                'can_evolve' => false,
                'is_evolution' => false,
                'effects' => json_encode([
                    'activation' => 'final_straight',
                    'effect' => 'massive_speed_boost',
                    'duration' => 'long',
                    'power' => 'very_high',
                ]),
                'description' => 'Special Week\'s signature skill - Massive speed boost in final straight',
                'activation_conditions' => json_encode([
                    'character' => 'Special Week',
                    'phase' => 'final_straight',
                    'position' => 'any',
                ]),
                'meta_tier' => 'S+',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert skills and set up evolution relationships
        foreach ($skills as $skill) {
            DB::table('ucp_skills')->insert($skill);
        }

        // Set up evolution relationships
        $goWithFlow = DB::table('ucp_skills')->where('internal_id', 'skill_001')->first();
        $goWithFlowRare = DB::table('ucp_skills')->where('internal_id', 'skill_001_rare')->first();

        if ($goWithFlow && $goWithFlowRare) {
            DB::table('ucp_skills')
                ->where('id', $goWithFlow->id)
                ->update(['evolution_target_id' => $goWithFlowRare->id]);

            DB::table('ucp_skills')
                ->where('id', $goWithFlowRare->id)
                ->update(['evolution_source_id' => $goWithFlow->id]);
        }

        $stamKeeper = DB::table('ucp_skills')->where('internal_id', 'skill_002')->first();
        if ($stamKeeper) {
            // Add a rare version for Stamina Keeper
            DB::table('ucp_skills')->insert([
                'name' => 'Stamina Keeper+',
                'internal_id' => 'skill_002_rare',
                'skill_type' => 'passive',
                'rarity' => 'rare',
                'base_sp_cost' => 200,
                'can_evolve' => false,
                'is_evolution' => true,
                'evolution_source_id' => $stamKeeper->id,
                'effects' => json_encode([
                    'activation' => 'passive',
                    'effect' => 'stamina_conservation',
                    'power' => 'high',
                ]),
                'description' => 'Enhanced version - Significantly reduces stamina consumption during races',
                'meta_tier' => 'A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $stamKeeperRare = DB::table('ucp_skills')->where('internal_id', 'skill_002_rare')->first();
            if ($stamKeeperRare) {
                DB::table('ucp_skills')
                    ->where('id', $stamKeeper->id)
                    ->update(['evolution_target_id' => $stamKeeperRare->id]);
            }
        }
    }
}
