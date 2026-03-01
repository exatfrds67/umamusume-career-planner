<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Career;
use App\Models\RunSnapshot;
use App\Models\SkillBuild;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * SamplePlansMigrationSeeder
 *
 * Migrates the 8 real career plans from the old uma-musume-planner-laravel project
 * into the current project under the admin account.
 *
 * Source data: uma_musume_planner_07092025.json / plan_sample_data.sql
 *
 * Character IDs (ucp_characters):
 *   Biwa Hayahide = 38, Vodka = 159, Daiwa Scarlet = 51,
 *   Tokai Teio = 151, Haru Urara = 73, El Condor Pasa = 59
 */
class SamplePlansMigrationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::where('email', 'admin@umamusume.local')->firstOrFail();

        $this->command->info("Seeding real career plans for admin: {$admin->email} (id: {$admin->id})");

        foreach ($this->plans() as $plan) {
            $career = Career::create([
                'user_id' => $admin->id,
                'character_id' => $plan['character_id'],
                'career_name' => $plan['career_name'],
                'scenario_type' => $plan['scenario_type'],
                'status' => $plan['status'],
                'current_turn' => $plan['current_turn'],
                'current_phase' => $plan['current_phase'],
                'started_at' => now()->toDateString(),
                'completed_at' => $plan['status'] === 'completed' ? now()->toDateString() : null,
                'final_speed' => $plan['speed'],
                'final_stamina' => $plan['stamina'],
                'final_power' => $plan['power'],
                'final_guts' => $plan['guts'],
                'final_wit' => $plan['wit'],
                'final_sp' => $plan['sp_remaining'],
                'ura_finale_cleared' => $plan['status'] === 'completed' ? true : null,
                'career_notes' => $plan['notes'] ?? null,
                'strategic_goals' => [
                    ['goal' => $plan['goal'], 'result' => $plan['status'] === 'completed' ? 'achieved' : 'pending'],
                ],
                'career_metadata' => [
                    'imported_from' => 'uma_musume_planner_v1',
                    'original_plan_title' => $plan['plan_title'],
                    'class_rank' => $plan['class'],
                    'career_stage' => $plan['original_stage'],
                    'race_name' => $plan['race_name'],
                    'strategy' => $plan['strategy'],
                    'energy' => $plan['energy'],
                    'mood' => $plan['mood'],
                    'condition' => $plan['condition'],
                    'race_day' => $plan['race_day'],
                    'turn_before_race' => $plan['turn_before'],
                    'acquire_skill_turn' => $plan['acquire_skill'],
                    'growth_rates' => [
                        'speed' => $plan['gr_speed'],
                        'stamina' => $plan['gr_stamina'],
                        'power' => $plan['gr_power'],
                        'guts' => $plan['gr_guts'],
                        'wit' => $plan['gr_wit'],
                    ],
                    'attribute_grades' => $plan['grades'],
                    'terrain_grades' => $plan['terrain'],
                    'distance_grades' => $plan['distance'],
                    'style_grades' => $plan['style'],
                    'skills' => $plan['skills'],
                ],
            ]);

            // Create a run snapshot capturing the stat state at time of import
            RunSnapshot::create([
                'career_id' => $career->id,
                'turn_number' => $plan['current_turn'],
                'trigger_type' => 'manual',
                'description' => "Imported from legacy planner — {$plan['plan_title']}",
                'snapshot_data' => [
                    'speed' => $plan['speed'],
                    'stamina' => $plan['stamina'],
                    'power' => $plan['power'],
                    'guts' => $plan['guts'],
                    'wit' => $plan['wit'],
                    'sp' => $plan['sp_remaining'],
                    'grades' => $plan['grades'],
                    'energy' => $plan['energy'],
                    'mood' => $plan['mood'],
                ],
                'checksum' => md5($career->id.$plan['plan_title']),
            ]);

            // Create a skill build referencing all skills from this plan
            SkillBuild::create([
                'user_id' => $admin->id,
                'character_id' => $plan['character_id'],
                'name' => $plan['career_name'].' — Skill Build',
                'description' => "Imported skill build from: {$plan['plan_title']}",
                'skill_ids' => [],
                'total_sp_cost' => collect($plan['skills'])
                    ->whereNotNull('sp_cost')
                    ->sum(fn ($s) => (int) ($s['sp_cost'] ?? 0)),
                'optimized_cost' => 0,
                'tags' => [$plan['strategy'], $plan['original_stage'], $plan['class']],
                'is_template' => false,
            ]);

            $this->command->info("  ✓ Created career: {$plan['career_name']}");
        }

        $this->command->info('');
        $this->command->info('8 career plans successfully imported under admin account.');
    }

    /**
     * Returns all 8 real plans from the legacy uma_musume_planner project.
     *
     * @return array<int, array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            // ─────────────────────────────────────────────
            // Plan 1: [pf. Winning Equation…] Biwa Hayahide
            // Senior Year | Silver Class | Tenno Sho (Spring)
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[pf. Winning Equation…] Biwa Hayahide Plan',
                'career_name' => '[pf. Winning Equation…] Biwa Hayahide',
                'character_id' => 38,
                'scenario_type' => 'ura_finale',
                'status' => 'planning',
                'current_phase' => 'senior',
                'original_stage' => 'senior',
                'class' => 'silver',
                'current_turn' => 1,
                'turn_before' => 0,
                'race_name' => 'Tenno Sho (Spring)',
                'race_day' => true,
                'goal' => 'TOP 3',
                'strategy' => 'PACE',
                'energy' => 20,
                'mood' => 'GOOD',
                'condition' => 'HOT TOPIC',
                'acquire_skill' => false,
                'sp_remaining' => 17,
                'speed' => 396, 'stamina' => 340, 'power' => 368, 'guts' => 400, 'wit' => 254,
                'gr_speed' => 0, 'gr_stamina' => 0, 'gr_power' => 0, 'gr_guts' => 10, 'gr_wit' => 20,
                'grades' => ['SPEED' => 'D+', 'STAMINA' => 'D', 'POWER' => 'D+', 'GUTS' => 'C', 'WIT' => 'E+'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'F'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'B', 'Medium' => 'A', 'Long' => 'A'],
                'style' => ['Front' => 'E', 'Pace' => 'A', 'Late' => 'B', 'End' => 'E'],
                'skills' => [
                    ['name' => '∴ Win Q.E.D. Lvl.2',          'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique) Final-corner burst'],
                    ['name' => 'Wet Conditions ()',             'acquired' => true,  'sp_cost' => null,  'notes' => 'Situational — only if forecast wet'],
                    ['name' => 'Wet Conditions (())',           'acquired' => false, 'sp_cost' => 110,   'notes' => 'Situational — only if forecast wet'],
                    ['name' => 'Outer Post Proficiency ()',     'acquired' => false, 'sp_cost' => 90,    'notes' => 'Position-based skill'],
                    ['name' => 'Straightaway Acceleration',    'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts acceleration on straights'],
                    ['name' => 'In Body and Mind',             'acquired' => false, 'sp_cost' => 119,   'notes' => 'Slight stamina & mentality buff'],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => 'Late-spurt speed boost'],
                    ['name' => 'Unrestrained',                 'acquired' => false, 'sp_cost' => 342,   'notes' => 'Final-corner lead hold (front-runner)'],
                    ['name' => 'Final Push',                   'acquired' => false, 'sp_cost' => 180,   'notes' => 'Slight acceleration on final corner'],
                    ['name' => 'Stamina to Spare',             'acquired' => true,  'sp_cost' => null,  'notes' => 'High-cost, stamina recovery'],
                    ['name' => 'Masterful Gambit',             'acquired' => false, 'sp_cost' => 162,   'notes' => 'Power burst in mid-race corners'],
                    ['name' => 'Up-Tempo',                     'acquired' => false, 'sp_cost' => 144,   'notes' => 'Mid‑race positioning boost'],
                    ['name' => 'Deep Breaths',                 'acquired' => false, 'sp_cost' => 160,   'notes' => 'Mid-race stamina sustain'],
                    ['name' => 'Flustered End Closers',        'acquired' => false, 'sp_cost' => 91,    'notes' => 'Debuffs late chasers'],
                    ['name' => 'Hesitant End Closers',         'acquired' => false, 'sp_cost' => 91,    'notes' => 'Debuffs tired late runners'],
                    ['name' => 'Tactical Tweak',               'acquired' => true,  'sp_cost' => null,  'notes' => 'Positioning tweak mid-race'],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 2: [Wild Top Gear] Vodka — Finale (STAR)
            // URA Finale Finals | status: Finished
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Wild Top Gear] Vodka Plan',
                'career_name' => '[Wild Top Gear] Vodka — URA Finale (Star)',
                'character_id' => 159,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
                'current_phase' => 'senior',
                'original_stage' => 'finale',
                'class' => 'star',
                'current_turn' => 78,
                'turn_before' => null,
                'race_name' => 'URA Finale Finals',
                'race_day' => false,
                'goal' => 'VODKA IS 1ST PLACE',
                'strategy' => null,
                'energy' => null,
                'mood' => null,
                'condition' => null,
                'acquire_skill' => false,
                'sp_remaining' => null,
                'speed' => 767, 'stamina' => 410, 'power' => 769, 'guts' => 324, 'wit' => 253,
                'gr_speed' => 10, 'gr_stamina' => 0, 'gr_power' => 20, 'gr_guts' => 0, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'B+', 'STAMINA' => 'C', 'POWER' => 'B+', 'GUTS' => 'D', 'WIT' => 'E+'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'G'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'A', 'Medium' => 'A', 'Long' => 'F'],
                'style' => ['Front' => 'C', 'Pace' => 'B', 'Late' => 'A', 'End' => 'F'],
                'skills' => [
                    ['name' => 'Xceleration Lvl. 2',           'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Bursts)'],
                    ['name' => '∴ Win Q.E.D.',                 'acquired' => false, 'sp_cost' => 180,   'notes' => 'Final-corner burst'],
                    ['name' => 'Standard Distance ○',          'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Standard Distance ⦾',          'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Remove Wet Conditions x',      'acquired' => true,  'sp_cost' => null,  'notes' => 'Removes the skill debuff: DONE'],
                    ['name' => 'Straightaway Acceleration',    'acquired' => false, 'sp_cost' => 170,   'notes' => 'Boosts acceleration on straights'],
                    ['name' => 'Straightaway Recovery',        'acquired' => false, 'sp_cost' => 170,   'notes' => null],
                    ['name' => 'Iron Will',                    'acquired' => false, 'sp_cost' => 304,   'notes' => null],
                    ['name' => 'Lay Low',                      'acquired' => false, 'sp_cost' => 160,   'notes' => null],
                    ['name' => 'Nimble Navigator',             'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Early Lead',                   'acquired' => false, 'sp_cost' => 108,   'notes' => null],
                    ['name' => 'Unrestrained',                 'acquired' => false, 'sp_cost' => 162,   'notes' => null],
                    ['name' => 'Final Push',                   'acquired' => true,  'sp_cost' => 162,   'notes' => null],
                    ['name' => 'Slick Surge',                  'acquired' => false, 'sp_cost' => 180,   'notes' => 'Late-race acceleration'],
                    ['name' => 'Masterful Gambit',             'acquired' => false, 'sp_cost' => 162,   'notes' => null],
                    ['name' => 'Updrafters',                   'acquired' => true,  'sp_cost' => null,  'notes' => 'Late surger burst'],
                    ['name' => 'Steadfast',                    'acquired' => true,  'sp_cost' => 112,   'notes' => null],
                    ['name' => 'Hesitant End Closers',         'acquired' => false, 'sp_cost' => 117,   'notes' => null],
                    ['name' => 'Shifting Gears',               'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Front Runner Straightaways ○', 'acquired' => false, 'sp_cost' => 117,   'notes' => null],
                    ['name' => 'Front Runner Corners ○',       'acquired' => false, 'sp_cost' => 117,   'notes' => null],
                    ['name' => 'Front Runner Savvy○',          'acquired' => false, 'sp_cost' => 99,    'notes' => null],
                    ['name' => 'Tail Held High',               'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Wet Conditions ○',             'acquired' => false, 'sp_cost' => 90,    'notes' => 'Moderately increase performance on good, soft, and heavy ground.'],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 3: [Wild Top Gear] Vodka — Finale (PLATINUM)
            // URA Finale Finals | status: Finished | SP=347
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Wild Top Gear] Vodka Plan',
                'career_name' => '[Wild Top Gear] Vodka — URA Finale (Platinum)',
                'character_id' => 159,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
                'current_phase' => 'senior',
                'original_stage' => 'finale',
                'class' => 'platinum',
                'current_turn' => 78,
                'turn_before' => null,
                'race_name' => 'URA Finale Finals',
                'race_day' => false,
                'goal' => 'SHE WON 1ST',
                'strategy' => null,
                'energy' => null,
                'mood' => null,
                'condition' => null,
                'acquire_skill' => true,
                'sp_remaining' => 347,
                'speed' => 646, 'stamina' => 474, 'power' => 765, 'guts' => 284, 'wit' => 279,
                'gr_speed' => 10, 'gr_stamina' => 0, 'gr_power' => 20, 'gr_guts' => 0, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'B', 'STAMINA' => 'C', 'POWER' => 'B+', 'GUTS' => 'E+', 'WIT' => 'E+'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'G'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'A', 'Medium' => 'A', 'Long' => 'F'],
                'style' => ['Front' => 'C', 'Pace' => 'B', 'Late' => 'A', 'End' => 'F'],
                'skills' => [
                    ['name' => 'Cut and Drive! Lvl. 1',        'acquired' => true,  'sp_cost' => null,  'notes' => 'Unique front-run burst in final 200m'],
                    ['name' => '∴ Win Q.E.D.',                 'acquired' => false, 'sp_cost' => 180,   'notes' => 'Unique: speed boost when passing at final corner'],
                    ['name' => 'Straightaway Acceleration',    'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts acceleration on straights'],
                    ['name' => 'Straightaway Recovery',        'acquired' => true,  'sp_cost' => null,  'notes' => 'Normal stamina recovery skill on straights'],
                    ['name' => 'Focus',                        'acquired' => true,  'sp_cost' => null,  'notes' => 'Improves early positioning/cornering'],
                    ['name' => 'Nimble Navigator',             'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => 'Speed boost near race end'],
                    ['name' => 'Unrestrained',                 'acquired' => false, 'sp_cost' => 342,   'notes' => null],
                    ['name' => 'Final Push',                   'acquired' => false, 'sp_cost' => 180,   'notes' => 'Late acceleration buffer'],
                    ['name' => 'Slick Surge',                  'acquired' => false, 'sp_cost' => 180,   'notes' => 'Normal late‑race acceleration boost'],
                    ['name' => 'Masterful Gambit',             'acquired' => false, 'sp_cost' => 162,   'notes' => 'Late burst skill with riskier pacing'],
                    ['name' => 'Updrafted',                    'acquired' => false, 'sp_cost' => 160,   'notes' => 'Normal passing aid skill in late race'],
                    ['name' => 'Front Runner Straightaways ○', 'acquired' => false, 'sp_cost' => 117,   'notes' => 'Boosts straight speed for front-style'],
                    ['name' => 'Hydrate',                      'acquired' => false, 'sp_cost' => 126,   'notes' => null],
                    ['name' => 'Late Surger Savvy ○',          'acquired' => true,  'sp_cost' => null,  'notes' => 'Enhances burst in late/mid-race segments'],
                    ['name' => 'Late Surger Savvy ⦾',          'acquired' => true,  'sp_cost' => null,  'notes' => null],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 4: [Peak Blue] Daiwa Scarlet — Finale (STAR)
            // URA Finale Finals | status: Finished | SP=75
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Peak Blue] Daiwa Scarlet Plan',
                'career_name' => '[Peak Blue] Daiwa Scarlet',
                'character_id' => 51,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
                'current_phase' => 'senior',
                'original_stage' => 'finale',
                'class' => 'star',
                'current_turn' => 78,
                'turn_before' => null,
                'race_name' => 'URA Finale Finals',
                'race_day' => false,
                'goal' => "SHE'S 2ND PLACE",
                'strategy' => 'FRONT',
                'energy' => null,
                'mood' => null,
                'condition' => null,
                'acquire_skill' => true,
                'sp_remaining' => 75,
                'speed' => 663, 'stamina' => 437, 'power' => 543, 'guts' => 408, 'wit' => 303,
                'gr_speed' => 10, 'gr_stamina' => 0, 'gr_power' => 0, 'gr_guts' => 20, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'B', 'STAMINA' => 'C', 'POWER' => 'C+', 'GUTS' => 'C', 'WIT' => 'D'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'G'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'A', 'Medium' => 'A', 'Long' => 'A'],
                'style' => ['Front' => 'A', 'Pace' => 'A', 'Late' => 'E', 'End' => 'G'],
                'skills' => [
                    ['name' => 'Resplendent Red Ace Lvl. 1',   'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Burst) Boosts speed when maintaining front in latter half'],
                    ['name' => 'Standard Distance ○',          'acquired' => false, 'sp_cost' => 63,    'notes' => null],
                    ['name' => 'Standard Distance ⦾',          'acquired' => false, 'sp_cost' => 77,    'notes' => null],
                    ['name' => 'Wet Conditions ○',             'acquired' => false, 'sp_cost' => 63,    'notes' => 'Moderately boosts performance on soft or heavy turf'],
                    ['name' => 'Fall Runner ○',                'acquired' => false, 'sp_cost' => 81,    'notes' => 'Improves start performance in Autumn races'],
                    ['name' => 'Maverick ○',                   'acquired' => false, 'sp_cost' => 54,    'notes' => 'Increases performance when running a unique strategy'],
                    ['name' => 'Competitive Spirit ○',         'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts power when leading with several others in same strategy'],
                    ['name' => 'Competitive Spirit ⦾',         'acquired' => false, 'sp_cost' => 110,   'notes' => "Stronger tier of Competitive Spirit's power boost"],
                    ['name' => 'Corner Connoisseur',           'acquired' => false, 'sp_cost' => 342,   'notes' => 'Acceleration through corners'],
                    ['name' => 'Corner Acceleration ○',        'acquired' => false, 'sp_cost' => 180,   'notes' => 'Burst of speed when taking corners'],
                    ['name' => 'Straightaway Acceleration',    'acquired' => true,  'sp_cost' => null,  'notes' => 'Burst speed on long straight sections'],
                    ['name' => 'Iron Will',                    'acquired' => false, 'sp_cost' => 304,   'notes' => 'Recovery if trapped mid-pack early'],
                    ['name' => 'Lay Low',                      'acquired' => false, 'sp_cost' => 160,   'notes' => 'Better stamina in back pack'],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => 'Speed burst in final spurt'],
                    ['name' => 'Fast-Paced',                   'acquired' => true,  'sp_cost' => null,  'notes' => 'Mid-race speed boost'],
                    ['name' => 'Final Push',                   'acquired' => false, 'sp_cost' => 162,   'notes' => 'Helps maintain corner speed in late race'],
                    ['name' => 'Stamina to Spare',             'acquired' => false, 'sp_cost' => 180,   'notes' => 'Early-race stamina recovery'],
                    ['name' => 'Preferred Position',           'acquired' => false, 'sp_cost' => 180,   'notes' => 'Reduces mid-race stamina loss'],
                    ['name' => 'Speed Star',                   'acquired' => false, 'sp_cost' => 306,   'notes' => 'Enhanced speed on straight paths'],
                    ['name' => 'Prepared to Pass',             'acquired' => false, 'sp_cost' => 180,   'notes' => 'Bonus when challenging others'],
                    ['name' => 'Up-Tempo',                     'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts positioning when running own pace'],
                    ['name' => 'Steadfast',                    'acquired' => false, 'sp_cost' => 144,   'notes' => null],
                    ['name' => 'Inside Scoop',                 'acquired' => false, 'sp_cost' => 144,   'notes' => 'Lane awareness boost'],
                    ['name' => 'Unyielding Spirit',            'acquired' => true,  'sp_cost' => null,  'notes' => 'Recovery when losing lead'],
                    ['name' => 'Pressure',                     'acquired' => false, 'sp_cost' => 144,   'notes' => 'Speed boost when overtaking another runner'],
                    ['name' => 'Front Runner Corners ○',       'acquired' => true,  'sp_cost' => null,  'notes' => 'Speed bonus on corners when leading'],
                    ['name' => 'Front Runner Corners ⦾',       'acquired' => true,  'sp_cost' => null,  'notes' => 'Stronger version'],
                    ['name' => 'Late Surger Straightaways ○',  'acquired' => false, 'sp_cost' => 91,    'notes' => 'Acceleration at late straight for closing runners'],
                    ['name' => 'After-School Stroll',          'acquired' => false, 'sp_cost' => 153,   'notes' => 'Small recovery + stamina balance'],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 5: [Beyond the Horizon] Tokai Teio
            // Classic Year | Gold Class | Tenno Sho (Spring)
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Beyond the Horizon] Tokai Teio Plan',
                'career_name' => '[Beyond the Horizon] Tokai Teio',
                'character_id' => 151,
                'scenario_type' => 'ura_finale',
                'status' => 'planning',
                'current_phase' => 'classic',
                'original_stage' => 'classic',
                'class' => 'gold',
                'current_turn' => 28,
                'turn_before' => null,
                'race_name' => 'Tenno Sho (Spring)',
                'race_day' => true,
                'goal' => 'TOP 3',
                'strategy' => 'PACE',
                'energy' => 30,
                'mood' => 'NORMAL',
                'condition' => null,
                'acquire_skill' => false,
                'sp_remaining' => 38,
                'speed' => 345, 'stamina' => 395, 'power' => 256, 'guts' => 252, 'wit' => 303,
                'gr_speed' => 10, 'gr_stamina' => 10, 'gr_power' => 0, 'gr_guts' => 10, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'D', 'STAMINA' => 'D+', 'POWER' => 'E+', 'GUTS' => 'E+', 'WIT' => 'D'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'G'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'D', 'Medium' => 'A', 'Long' => 'A'],
                'style' => ['Front' => 'C', 'Pace' => 'A', 'Late' => 'C', 'End' => 'E'],
                'skills' => [
                    ['name' => 'Certain Victory Lvl. 2',       'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Burst) – triggers when overtaking in the front of final straight'],
                    ['name' => 'Resplendent Red Ace',          'acquired' => true,  'sp_cost' => null,  'notes' => 'Unique Speed Boost: activates in the second half of the race'],
                    ['name' => 'Corner Connoisseur',           'acquired' => false, 'sp_cost' => 342,   'notes' => 'Acceleration burst when navigating corners'],
                    ['name' => 'Corner Acceleration ○',        'acquired' => false, 'sp_cost' => 180,   'notes' => 'Speed burst on corners'],
                    ['name' => 'Iron Will',                    'acquired' => false, 'sp_cost' => 304,   'notes' => null],
                    ['name' => 'Lay Low',                      'acquired' => false, 'sp_cost' => 160,   'notes' => null],
                    ['name' => 'Prudent Positioning',          'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts lane navigation early in the race'],
                    ['name' => 'Nimble Navigator',             'acquired' => false, 'sp_cost' => 135,   'notes' => null],
                    ['name' => 'Stamina to Spare',             'acquired' => true,  'sp_cost' => null,  'notes' => 'Recovers stamina when fatigue sets in early'],
                    ['name' => 'Preferred Position',           'acquired' => true,  'sp_cost' => null,  'notes' => 'Reduces mid-race stamina loss'],
                    ['name' => 'Prepared to Pass',             'acquired' => false, 'sp_cost' => 162,   'notes' => null],
                    ['name' => 'Up-Tempo',                     'acquired' => false, 'sp_cost' => 144,   'notes' => null],
                    ['name' => 'Deep Breaths',                 'acquired' => false, 'sp_cost' => 144,   'notes' => null],
                    ['name' => 'Inside Scoop',                 'acquired' => false, 'sp_cost' => 144,   'notes' => 'Enhances acceleration when moving toward inner rail'],
                    ['name' => 'Frenzied End Closers',         'acquired' => false, 'sp_cost' => 91,    'notes' => 'Debuffs late-racing opponents in the homestretch'],
                    ['name' => 'Soft Step',                    'acquired' => false, 'sp_cost' => 160,   'notes' => 'Ground condition agility; early race aid'],
                    ['name' => 'Pressure',                     'acquired' => false, 'sp_cost' => 144,   'notes' => null],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 6: [Bestest Prize 𝆕] Haru Urara
            // Senior Year | Silver Class | JBC SPRINT
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Bestest Prize 𝆕] Haru Urara Plan',
                'career_name' => '[Bestest Prize 𝆕] Haru Urara — JBC Sprint',
                'character_id' => 73,
                'scenario_type' => 'ura_finale',
                'status' => 'planning',
                'current_phase' => 'senior',
                'original_stage' => 'senior',
                'class' => 'silver',
                'current_turn' => 56,
                'turn_before' => null,
                'race_name' => 'JBC SPRINT',
                'race_day' => true,
                'goal' => 'MUST 1ST',
                'strategy' => 'LATE',
                'energy' => 20,
                'mood' => 'GOOD',
                'condition' => null,
                'acquire_skill' => true,
                'sp_remaining' => 174,
                'speed' => 485, 'stamina' => 305, 'power' => 404, 'guts' => 314, 'wit' => 264,
                'gr_speed' => 0, 'gr_stamina' => 0, 'gr_power' => 0, 'gr_guts' => 20, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'C', 'STAMINA' => 'D', 'POWER' => 'C', 'GUTS' => 'D', 'WIT' => 'E+'],
                'terrain' => ['Turf' => 'D', 'Dirt' => 'A'],
                'distance' => ['Sprint' => 'A', 'Mile' => 'A', 'Medium' => 'G', 'Long' => 'G'],
                'style' => ['Front' => 'G', 'Pace' => 'G', 'Late' => 'A', 'End' => 'B'],
                'skills' => [
                    ['name' => 'Super Duper Stoked Lvl.1',     'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Burst) – huge late-race power for close finishes'],
                    ['name' => '∴ Win Q.E.D.',                 'acquired' => false, 'sp_cost' => 180,   'notes' => 'Received from legacy — powerful finishing burst'],
                    ['name' => 'Wet Conditions ○',             'acquired' => true,  'sp_cost' => null,  'notes' => 'Track Softness bonus — boosts speed/power on wet dirt'],
                    ['name' => 'Wet Conditions ⦾',             'acquired' => false, 'sp_cost' => 77,    'notes' => null],
                    ['name' => 'Straightaway Acceleration',    'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts mid-race — great for long straight sections'],
                    ['name' => 'Lay Low',                      'acquired' => false, 'sp_cost' => 160,   'notes' => 'Late positioning — helps close the pack when behind'],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => 'Good burst entering the final stretch'],
                    ['name' => 'Unrestrained',                 'acquired' => false, 'sp_cost' => 342,   'notes' => null],
                    ['name' => 'Final Push',                   'acquired' => false, 'sp_cost' => 180,   'notes' => 'Guaranteed burst in final push'],
                    ['name' => 'Stamina To Spare',             'acquired' => false, 'sp_cost' => 162,   'notes' => 'Minor recovery during race'],
                    ['name' => 'Masterful Gambit',             'acquired' => true,  'sp_cost' => null,  'notes' => 'Big burst if she stays far back'],
                    ['name' => 'Sprinting Gear',               'acquired' => true,  'sp_cost' => null,  'notes' => 'Early burst — helps build momentum around stretch'],
                    ['name' => 'Trick (Front)',                'acquired' => false, 'sp_cost' => 98,    'notes' => 'Sabotage Front runners'],
                    ['name' => 'Flustered End Corners',        'acquired' => false, 'sp_cost' => 117,   'notes' => 'Debuffs close competitors in turn exit'],
                    ['name' => 'Hydrate',                      'acquired' => false, 'sp_cost' => 126,   'notes' => 'Minor performance stabilization'],
                    ['name' => '1,500,000 CC',                 'acquired' => true,  'sp_cost' => null,  'notes' => null],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 7: [Bestest Prize 𝆕] Haru Urara — Finale (SILVER)
            // URA Finale Qualifier | SP=4
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[Bestest Prize 𝆕] Haru Urara Plan',
                'career_name' => '[Bestest Prize 𝆕] Haru Urara — URA Finale Qualifier',
                'character_id' => 73,
                'scenario_type' => 'ura_finale',
                'status' => 'planning',
                'current_phase' => 'senior',
                'original_stage' => 'finale',
                'class' => 'silver',
                'current_turn' => 78,
                'turn_before' => null,
                'race_name' => 'URA Finale Qualifier',
                'race_day' => true,
                'goal' => '1ST',
                'strategy' => 'LATE',
                'energy' => 20,
                'mood' => 'GOOD',
                'condition' => 'CHARMING',
                'acquire_skill' => false,
                'sp_remaining' => 4,
                'speed' => 423, 'stamina' => 276, 'power' => 461, 'guts' => 448, 'wit' => 264,
                'gr_speed' => 0, 'gr_stamina' => 0, 'gr_power' => 10, 'gr_guts' => 20, 'gr_wit' => 0,
                'grades' => ['SPEED' => 'C', 'STAMINA' => 'E+', 'POWER' => 'C', 'GUTS' => 'C', 'WIT' => 'E+'],
                'terrain' => ['Turf' => 'C', 'Dirt' => 'A'],
                'distance' => ['Sprint' => 'A', 'Mile' => 'A', 'Medium' => 'G', 'Long' => 'G'],
                'style' => ['Front' => 'G', 'Pace' => 'G', 'Late' => 'A', 'End' => 'B'],
                'skills' => [
                    ['name' => 'Super Duper Stoked Lvl.1',     'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Burst)'],
                    ['name' => '∴ Win Q.E.D.',                 'acquired' => true,  'sp_cost' => null,  'notes' => 'Received from legacy — powerful finishing burst'],
                    ['name' => 'Summer Runner ○',              'acquired' => false, 'sp_cost' => 63,    'notes' => null],
                    ['name' => 'Rainy Days ○',                 'acquired' => false, 'sp_cost' => 63,    'notes' => 'Minor boost in rain'],
                    ['name' => 'Beeline Burst',                'acquired' => false, 'sp_cost' => 323,   'notes' => 'Strong mid-stretch boost for overtaking'],
                    ['name' => 'Straightaway Adept',           'acquired' => false, 'sp_cost' => 170,   'notes' => 'Enhances straight-line speed on dirt'],
                    ['name' => 'Lay Low',                      'acquired' => true,  'sp_cost' => null,  'notes' => 'Late positioning — helps close the pack when behind'],
                    ['name' => 'Pace Strategy',                'acquired' => false, 'sp_cost' => 170,   'notes' => 'Improves race rhythm and stamina efficiency'],
                    ['name' => 'Calm in a Crowd',              'acquired' => true,  'sp_cost' => null,  'notes' => 'Boosts mental focus and late-game consistency'],
                    ['name' => 'Homestretch Haste',            'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Unrestrained',                 'acquired' => false, 'sp_cost' => 342,   'notes' => 'Powerful burst'],
                    ['name' => 'Final Push',                   'acquired' => false, 'sp_cost' => 180,   'notes' => 'Ideal for late-stage sprint'],
                    ['name' => 'Masterful Gambit',             'acquired' => true,  'sp_cost' => null,  'notes' => 'Tactical speed/damage buff affecting positioning'],
                    ['name' => 'Sprinting Gear',               'acquired' => true,  'sp_cost' => null,  'notes' => 'Early burst — helps build momentum around stretch'],
                    ['name' => 'Rosy Outlook',                 'acquired' => false, 'sp_cost' => 144,   'notes' => 'Passive boost in morale → slight stat edge'],
                    ['name' => 'Subdued Front Runners',        'acquired' => false, 'sp_cost' => 117,   'notes' => null],
                    ['name' => 'Flustered End Closers',        'acquired' => false, 'sp_cost' => 117,   'notes' => 'Lowest-cost late sprint burst'],
                    ['name' => 'Meticulous Measures',          'acquired' => false, 'sp_cost' => 126,   'notes' => null],
                    ['name' => 'Second Wind',                  'acquired' => false, 'sp_cost' => 162,   'notes' => 'Recovers stamina mid-race'],
                    ['name' => 'Hydrate',                      'acquired' => false, 'sp_cost' => 126,   'notes' => 'Minor stamina regen mid-race'],
                    ['name' => 'Tactical Tweak',               'acquired' => false, 'sp_cost' => 108,   'notes' => 'Small boost to decision-making speed'],
                    ['name' => '1,500,000 CC',                 'acquired' => true,  'sp_cost' => null,  'notes' => 'Slightly increase velocity on an uphill. (Late Surger)'],
                ],
            ],

            // ─────────────────────────────────────────────
            // Plan 8: [El☆Número 1] El Condor Pasa
            // Junior Year | Beginner Class | Kyodo News Hai
            // Turn 12 before race
            // ─────────────────────────────────────────────
            [
                'plan_title' => '[El☆Número 1] El Condor Pasa Plan',
                'career_name' => '[El☆Número 1] El Condor Pasa',
                'character_id' => 59,
                'scenario_type' => 'ura_finale',
                'status' => 'planning',
                'current_phase' => 'junior',
                'original_stage' => 'junior',
                'class' => 'beginner',
                'current_turn' => 12,
                'turn_before' => 12,
                'race_name' => 'Kyodo News Hai',
                'race_day' => false,
                'goal' => 'TOP 5',
                'strategy' => 'PACE',
                'energy' => 50,
                'mood' => 'GOOD',
                'condition' => null,
                'acquire_skill' => false,
                'sp_remaining' => 38,
                'speed' => 194, 'stamina' => 154, 'power' => 133, 'guts' => 97, 'wit' => 156,
                'gr_speed' => 20, 'gr_stamina' => 0, 'gr_power' => 0, 'gr_guts' => 0, 'gr_wit' => 10,
                'grades' => ['SPEED' => 'F+', 'STAMINA' => 'F+', 'POWER' => 'F', 'GUTS' => 'G+', 'WIT' => 'F+'],
                'terrain' => ['Turf' => 'A', 'Dirt' => 'B'],
                'distance' => ['Sprint' => 'F', 'Mile' => 'A', 'Medium' => 'A', 'Long' => 'B'],
                'style' => ['Front' => 'C', 'Pace' => 'A', 'Late' => 'A', 'End' => 'C'],
                'skills' => [
                    ['name' => 'Corazón ☆ Ardiente',           'acquired' => true,  'sp_cost' => null,  'notes' => '(Unique Burst)'],
                    ['name' => 'Resplendent Red Ace',          'acquired' => false, 'sp_cost' => 180,   'notes' => 'Received from Legacy'],
                    ['name' => 'Certain Victory',              'acquired' => false, 'sp_cost' => 180,   'notes' => 'Received from Legacy'],
                    ['name' => 'Straightaway Adept',           'acquired' => false, 'sp_cost' => 170,   'notes' => null],
                    ['name' => 'Stamina to Spare',             'acquired' => false, 'sp_cost' => 180,   'notes' => null],
                    ['name' => 'Hawkeye',                      'acquired' => false, 'sp_cost' => 110,   'notes' => null],
                    ['name' => 'Soft Step',                    'acquired' => false, 'sp_cost' => 144,   'notes' => null],
                    ['name' => 'Pace Chaser Straightaways ○',  'acquired' => true,  'sp_cost' => null,  'notes' => null],
                    ['name' => 'Pace Chaser Straightaways ⦾',  'acquired' => false, 'sp_cost' => 140,   'notes' => null],
                ],
            ],
        ];
    }
}
