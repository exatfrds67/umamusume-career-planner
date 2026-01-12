<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UcpSupportCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supportCards = [
            // Speed Support Cards
            [
                'name' => '[Winning Ticket] Daiwa Scarlet',
                'internal_id' => 'support_001',
                'card_type' => 'speed',
                'rarity' => 'SSR',
                'max_level' => 50,
                'max_limit_break' => 4,
                'character_name' => 'Daiwa Scarlet',
                'character_internal_id' => 'char_daiwa_scarlet',
                'speed_bonus' => 35,
                'stamina_bonus' => 0,
                'power_bonus' => 10,
                'guts_bonus' => 0,
                'wit_bonus' => 0,
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 15,
                'event_effect_bonus' => 20,
                'training_effect_bonus' => 15,
                'unique_effects' => json_encode([
                    'speed_training_boost' => 'high',
                    'race_bonus' => 'speed_races',
                    'special_events' => ['daiwa_scarlet_events']
                ]),
                'skill_hints_provided' => json_encode([
                    'Go with the Flow',
                    'Speed Star',
                    'Acceleration'
                ]),
                'meta_tier' => 'S',
                'is_limited' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '[Maverick] El Condor Pasa',
                'internal_id' => 'support_002',
                'card_type' => 'speed',
                'rarity' => 'SR',
                'max_level' => 45,
                'max_limit_break' => 4,
                'character_name' => 'El Condor Pasa',
                'character_internal_id' => 'char_el_condor_pasa',
                'speed_bonus' => 25,
                'stamina_bonus' => 0,
                'power_bonus' => 15,
                'guts_bonus' => 5,
                'wit_bonus' => 0,
                'friendship_bonus' => 15,
                'event_recovery_bonus' => 10,
                'event_effect_bonus' => 15,
                'training_effect_bonus' => 10,
                'unique_effects' => json_encode([
                    'dirt_race_bonus' => 'high',
                    'overseas_training' => true
                ]),
                'skill_hints_provided' => json_encode([
                    'Dirt Adaptation',
                    'Overseas Expedition'
                ]),
                'meta_tier' => 'A',
                'is_limited' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Stamina Support Cards
            [
                'name' => '[Endurance Master] Gold Ship',
                'internal_id' => 'support_003',
                'card_type' => 'stamina',
                'rarity' => 'SSR',
                'max_level' => 50,
                'max_limit_break' => 4,
                'character_name' => 'Gold Ship',
                'character_internal_id' => 'char_gold_ship',
                'speed_bonus' => 0,
                'stamina_bonus' => 35,
                'power_bonus' => 0,
                'guts_bonus' => 10,
                'wit_bonus' => 0,
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 20,
                'event_effect_bonus' => 15,
                'training_effect_bonus' => 15,
                'unique_effects' => json_encode([
                    'stamina_training_boost' => 'very_high',
                    'long_distance_bonus' => true,
                    'unpredictable_events' => true
                ]),
                'skill_hints_provided' => json_encode([
                    'Stamina Keeper',
                    'Long Distance',
                    'Recovery'
                ]),
                'meta_tier' => 'S+',
                'is_limited' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Power Support Cards
            [
                'name' => '[Burning Heart] Oguri Cap',
                'internal_id' => 'support_004',
                'card_type' => 'power',
                'rarity' => 'SSR',
                'max_level' => 50,
                'max_limit_break' => 4,
                'character_name' => 'Oguri Cap',
                'character_internal_id' => 'char_oguri_cap',
                'speed_bonus' => 5,
                'stamina_bonus' => 0,
                'power_bonus' => 35,
                'guts_bonus' => 5,
                'wit_bonus' => 0,
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 10,
                'event_effect_bonus' => 20,
                'training_effect_bonus' => 15,
                'unique_effects' => json_encode([
                    'power_training_boost' => 'very_high',
                    'comeback_specialist' => true,
                    'fighting_spirit' => 'high'
                ]),
                'skill_hints_provided' => json_encode([
                    'Power Surge',
                    'Fighting Spirit',
                    'Comeback'
                ]),
                'meta_tier' => 'S',
                'is_limited' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Friend Support Cards
            [
                'name' => '[Cheerful Companion] Trainer',
                'internal_id' => 'support_005',
                'card_type' => 'friend',
                'rarity' => 'SR',
                'max_level' => 45,
                'max_limit_break' => 4,
                'character_name' => null,
                'character_internal_id' => null,
                'speed_bonus' => 0,
                'stamina_bonus' => 0,
                'power_bonus' => 0,
                'guts_bonus' => 0,
                'wit_bonus' => 0,
                'friendship_bonus' => 30,
                'event_recovery_bonus' => 25,
                'event_effect_bonus' => 30,
                'training_effect_bonus' => 5,
                'unique_effects' => json_encode([
                    'motivation_boost' => 'high',
                    'energy_recovery' => 'high',
                    'special_training_events' => true
                ]),
                'skill_hints_provided' => json_encode([
                    'Motivation Up',
                    'Energy Recovery',
                    'Training Efficiency'
                ]),
                'meta_tier' => 'A',
                'is_limited' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($supportCards as $card) {
            DB::table('ucp_support_cards')->insert($card);
        }
    }
}
