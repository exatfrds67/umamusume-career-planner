<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TrainingPrediction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * PlanTrainingPredictionsSeeder
 *
 * Seeds TrainingPrediction records for the 8 imported career plans (ucp_careers ids 1–8)
 * under the admin account.
 *
 * Each career gets 3 prediction records:
 *   - stat_projection  : predicted final stats vs actual (accuracy filled for completed runs)
 *   - race_outcome     : predicted race placement & performance
 *   - sp_efficiency    : skill-point budget prediction vs actual spend
 *
 * Completed careers (2, 3, 4) have actual_value + accuracy_score populated.
 * Planning careers (1, 5, 6, 7, 8) have actual_value = null.
 */
class PlanTrainingPredictionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::where('email', 'admin@umamusume.local')->firstOrFail();

        // Guard: skip if predictions already exist for these careers
        $alreadySeeded = TrainingPrediction::where('user_id', $admin->id)
            ->whereBetween('career_id', [1, 8])
            ->exists();

        if ($alreadySeeded) {
            $this->command->warn('Training predictions for careers 1–8 already exist. Skipping.');

            return;
        }

        $this->command->info("Seeding training predictions for admin: {$admin->email}");

        $rows = $this->buildPredictions($admin->id);

        foreach ($rows as $row) {
            TrainingPrediction::create($row);
        }

        $this->command->info('  Created '.count($rows).' training prediction records (3 per career × 8 careers).');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPredictions(int $userId): array
    {
        return [
            // ══════════════════════════════════════════════════════
            // CAREER 1 — [pf. Winning Equation…] Biwa Hayahide
            // Senior | Silver | Tenno Sho (Spring) | PLANNING
            // Current stats: 396/340/368/400/254 | SP remaining: 17
            // Growth rate focus: Guts +10%, Wit +20%
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 1,
                'user_id' => $userId,
                'turn_number' => 1,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 455, 'stamina' => 385, 'power' => 425,
                    'guts' => 460, 'wit' => 315,
                    'notes' => 'Projected end-of-career stats. Guts/Wit growth rates (10%/20%) applied over ~24 remaining turns.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6800,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 1,
                'user_id' => $userId,
                'turn_number' => 1,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'Tenno Sho (Spring)',
                    'predicted_place' => 'TOP 3',
                    'strategy' => 'PACE',
                    'key_skills' => ['Homestretch Haste', 'Stamina to Spare', 'Tactical Tweak', 'Straightaway Acceleration'],
                    'risk_factors' => ['Low SP (17) — limited skill budget', 'PACE on long course requires strong stamina'],
                    'confidence_note' => 'Guts (C) is a relative strength; Wit (E+) is the biggest concern.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6200,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 1,
                'user_id' => $userId,
                'turn_number' => 1,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 17,
                    'estimated_sp_needed' => 1489,
                    'acquired_skills' => 6,
                    'pending_skills' => 10,
                    'highest_priority' => ['Unrestrained (342 SP)', 'Masterful Gambit (162 SP)', 'Deep Breaths (160 SP)'],
                    'recommendation' => 'SP budget is tight. Prioritise Unrestrained and Deep Breaths if skill acquisition trigger fires soon.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.7200,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 2 — [Wild Top Gear] Vodka — URA Finale (Star)
            // Finale | Star | URA Finale Finals | COMPLETED
            // Final stats: 767/410/769/324/253 | Goal: VODKA IS 1ST PLACE ✓
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 2,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 730, 'stamina' => 395, 'power' => 745,
                    'guts' => 315, 'wit' => 245,
                    'notes' => 'Pre-finale projection. Speed/Power growth focus (10%/20%) expected strong B+ ceiling.',
                ],
                'actual_value' => [
                    'speed' => 767, 'stamina' => 410, 'power' => 769,
                    'guts' => 324, 'wit' => 253,
                    'notes' => 'Final recorded stats at URA Finale Finals.',
                ],
                'confidence_score' => 0.8800,
                'accuracy_score' => 0.9550,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 2,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'URA Finale Finals',
                    'predicted_place' => '1ST',
                    'strategy' => 'FRONT/PACE',
                    'key_skills' => ['Xceleration Lvl. 2', 'Homestretch Haste', 'Tail Held High', 'Shifting Gears'],
                    'confidence_note' => 'B+ Speed/Power with Star-class status — high placement probability.',
                ],
                'actual_value' => [
                    'place' => '1ST',
                    'goal' => 'VODKA IS 1ST PLACE',
                    'result' => 'ACHIEVED',
                ],
                'confidence_score' => 0.9200,
                'accuracy_score' => 1.0000,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 2,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 0,
                    'estimated_sp_needed' => 2410,
                    'acquired_skills' => 9,
                    'pending_skills' => 16,
                    'recommendation' => 'SP fully spent; strong acquired skill base including Xceleration unique. Budget constrained by competing skills.',
                ],
                'actual_value' => [
                    'sp_remaining' => null,
                    'skills_acquired' => ['Xceleration Lvl. 2', 'Standard Distance ○', 'Standard Distance ⦾', 'Remove Wet Conditions x',
                        'Nimble Navigator', 'Homestretch Haste', 'Final Push', 'Updrafters', 'Steadfast', 'Shifting Gears', 'Tail Held High'],
                    'note' => 'SP fully utilised by race day.',
                ],
                'confidence_score' => 0.8500,
                'accuracy_score' => 0.8800,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 3 — [Wild Top Gear] Vodka — URA Finale (Platinum)
            // Finale | Platinum | URA Finale Finals | COMPLETED
            // Final stats: 646/474/765/284/279 | SP: 347 left | Goal: SHE WON 1ST ✓
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 3,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 660, 'stamina' => 460, 'power' => 750,
                    'guts' => 280, 'wit' => 270,
                    'notes' => 'Platinum-class projection. Better stamina management vs Star run; Power growth dominant.',
                ],
                'actual_value' => [
                    'speed' => 646, 'stamina' => 474, 'power' => 765,
                    'guts' => 284, 'wit' => 279,
                    'notes' => 'Final stats — stamina exceeded projection, speed slightly under.',
                ],
                'confidence_score' => 0.8600,
                'accuracy_score' => 0.9300,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 3,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'URA Finale Finals',
                    'predicted_place' => '1ST',
                    'strategy' => 'LATE',
                    'key_skills' => ['Cut and Drive! Lvl. 1', 'Homestretch Haste', 'Late Surger Savvy ○', 'Late Surger Savvy ⦾'],
                    'confidence_note' => 'Platinum class with strong Power (B+). Late surge style well-equipped.',
                ],
                'actual_value' => [
                    'place' => '1ST',
                    'goal' => 'SHE WON 1ST',
                    'result' => 'ACHIEVED',
                ],
                'confidence_score' => 0.8800,
                'accuracy_score' => 1.0000,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 3,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 100,
                    'estimated_sp_needed' => 1447,
                    'acquired_skills' => 8,
                    'pending_skills' => 8,
                    'recommendation' => 'Expected moderate SP surplus. Focus remaining budget on Unrestrained or Final Push.',
                ],
                'actual_value' => [
                    'sp_remaining' => 347,
                    'skills_acquired' => ['Cut and Drive! Lvl. 1', 'Straightaway Acceleration', 'Straightaway Recovery',
                        'Focus', 'Nimble Navigator', 'Homestretch Haste', 'Late Surger Savvy ○', 'Late Surger Savvy ⦾'],
                    'note' => 'SP surplus of 347 — more than projected. Key pending skills like Unrestrained (342) were skipped.',
                ],
                'confidence_score' => 0.7500,
                'accuracy_score' => 0.7200,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 4 — [Peak Blue] Daiwa Scarlet
            // Finale | Star | URA Finale Finals | COMPLETED
            // Final stats: 663/437/543/408/303 | SP: 75 | Goal: SHE'S 2ND PLACE ✓
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 4,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 695, 'stamina' => 445, 'power' => 565,
                    'guts' => 415, 'wit' => 295,
                    'notes' => 'Projected stats. Guts/Speed growth focus (20%/10%). Front-runner build expected high Speed.',
                ],
                'actual_value' => [
                    'speed' => 663, 'stamina' => 437, 'power' => 543,
                    'guts' => 408, 'wit' => 303,
                    'notes' => 'Final stats — Speed and Power undershot projection; Wit slightly above.',
                ],
                'confidence_score' => 0.8200,
                'accuracy_score' => 0.8800,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 4,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'URA Finale Finals',
                    'predicted_place' => '2ND',
                    'strategy' => 'FRONT',
                    'key_skills' => ['Resplendent Red Ace Lvl. 1', 'Homestretch Haste', 'Fast-Paced', 'Front Runner Corners ○', 'Front Runner Corners ⦾'],
                    'confidence_note' => 'Front-runner with A/A terrain/mile aptitude. Strong corner skills. 2nd predicted vs powerful Speed/Power field.',
                ],
                'actual_value' => [
                    'place' => '2ND',
                    'goal' => "SHE'S 2ND PLACE",
                    'result' => 'ACHIEVED',
                ],
                'confidence_score' => 0.8600,
                'accuracy_score' => 1.0000,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 4,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 0,
                    'estimated_sp_needed' => 3118,
                    'acquired_skills' => 10,
                    'pending_skills' => 19,
                    'recommendation' => 'Very large skill pool (29 skills planned). Expect near-zero SP at end. Corner Connoisseur (342) is the key trade-off.',
                ],
                'actual_value' => [
                    'sp_remaining' => 75,
                    'skills_acquired' => ['Resplendent Red Ace Lvl. 1', 'Competitive Spirit ○', 'Straightaway Acceleration',
                        'Homestretch Haste', 'Fast-Paced', 'Up-Tempo', 'Unyielding Spirit',
                        'Front Runner Corners ○', 'Front Runner Corners ⦾'],
                    'note' => 'Minimal SP surplus (75). Heavy skill list was effectively managed; Corner Connoisseur skipped.',
                ],
                'confidence_score' => 0.7800,
                'accuracy_score' => 0.8500,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 5 — [Beyond the Horizon] Tokai Teio
            // Classic | Gold | Tenno Sho (Spring) | PLANNING
            // Current stats: 345/395/256/252/303 | SP: 38 | Turn ~28
            // Growth rates: Speed +10%, Stamina +10%, Guts +10%
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 5,
                'user_id' => $userId,
                'turn_number' => 28,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 510, 'stamina' => 560, 'power' => 390,
                    'guts' => 380, 'wit' => 390,
                    'notes' => 'Projected end-of-classic stats (~50 turns remaining). Balanced growth across Speed/Stamina/Guts (all +10%). Stamina is the current strength.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6500,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 5,
                'user_id' => $userId,
                'turn_number' => 28,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'Tenno Sho (Spring)',
                    'predicted_place' => 'TOP 3',
                    'strategy' => 'PACE',
                    'key_skills' => ['Certain Victory Lvl. 2', 'Stamina to Spare', 'Preferred Position', 'Prudent Positioning'],
                    'risk_factors' => ['Power (E+) and Guts (E+) are very low for Classic-year long races', 'Wit (D) limits mid-race positioning'],
                    'confidence_note' => 'Stamina (D+) is relatively best stat at this stage. Long-distance aptitude strong (A). Risk: insufficient Power.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.5500,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 5,
                'user_id' => $userId,
                'turn_number' => 28,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 38,
                    'estimated_sp_needed' => 2110,
                    'acquired_skills' => 4,
                    'pending_skills' => 13,
                    'highest_priority' => ['Corner Connoisseur (342 SP)', 'Iron Will (304 SP)', 'Corner Acceleration ○ (180 SP)'],
                    'recommendation' => 'SP extremely tight at 38 remaining. Only 2–3 medium-cost skills acquirable this run. Prioritise Corner Connoisseur or Iron Will.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.7000,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 6 — [Bestest Prize 𝆕] Haru Urara — JBC Sprint
            // Senior | Silver | JBC Sprint (Dirt) | PLANNING
            // Current stats: 485/305/404/314/264 | SP: 174
            // Growth rates: Guts +20%
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 6,
                'user_id' => $userId,
                'turn_number' => 56,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 545, 'stamina' => 340, 'power' => 460,
                    'guts' => 390, 'wit' => 295,
                    'notes' => 'Projected end stats (Guts +20% growth dominant). Dirt/Sprint specialist. Speed and Power expected to grow organically.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6800,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 6,
                'user_id' => $userId,
                'turn_number' => 56,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'JBC SPRINT',
                    'predicted_place' => '1ST',
                    'strategy' => 'LATE',
                    'key_skills' => ['Super Duper Stoked Lvl.1', 'Masterful Gambit', 'Sprinting Gear', 'Straightaway Acceleration', 'Homestretch Haste'],
                    'risk_factors' => ['Stamina low (D) for late-surge style — risk of running out before burst', 'No major debuff skills acquired yet'],
                    'confidence_note' => 'Dirt A / Sprint A aptitude. Late-surge style on sprint course gives huge burst potential. Stamina is the key risk.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.7000,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 6,
                'user_id' => $userId,
                'turn_number' => 56,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 174,
                    'estimated_sp_needed' => 1442,
                    'acquired_skills' => 5,
                    'pending_skills' => 11,
                    'highest_priority' => ['Unrestrained (342 SP)', 'Final Push (180 SP)', 'Lay Low (160 SP)'],
                    'recommendation' => 'Good SP buffer (174). Can acquire Unrestrained + one more high-value skill. Avoid stacking SP on debuf skills — focus on burst.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.7500,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 7 — [Bestest Prize 𝆕] Haru Urara — URA Finale Qualifier
            // Finale | Silver | URA Finale Qualifier | PLANNING
            // Current stats: 423/276/461/448/264 | SP: 4 | Condition: CHARMING
            // Growth rates: Power +10%, Guts +20%
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 7,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 475, 'stamina' => 310, 'power' => 510,
                    'guts' => 510, 'wit' => 295,
                    'notes' => 'Finale-phase projection. Guts/Power growth (20%/10%) — guts emerging as near-top stat. Stamina critically low.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6200,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 7,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'URA Finale Qualifier',
                    'predicted_place' => '1ST',
                    'strategy' => 'LATE',
                    'key_skills' => ['Super Duper Stoked Lvl.1', '∴ Win Q.E.D.', 'Masterful Gambit', 'Sprinting Gear', 'Lay Low', 'Calm in a Crowd'],
                    'risk_factors' => ['SP remaining = 4 — zero room for last-minute skill purchases', 'Stamina (E+) dangerously low for URA Finale distance', 'CHARMING condition may help compensate'],
                    'confidence_note' => 'Unique skill (∴ Win Q.E.D.) already acquired — major finisher. Guts (C) is stronger vs previous Haru run. Qualifier should be achievable.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6800,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 7,
                'user_id' => $userId,
                'turn_number' => 78,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 4,
                    'estimated_sp_needed' => 2211,
                    'acquired_skills' => 9,
                    'pending_skills' => 13,
                    'highest_priority' => ['Unrestrained (342 SP)', 'Beeline Burst (323 SP)', 'Final Push (180 SP)'],
                    'recommendation' => 'SP = 4 — essentially exhausted. Entire budget already committed. No further skill acquisitions possible this run without an event reward.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.9500,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],

            // ══════════════════════════════════════════════════════
            // CAREER 8 — [El☆Número 1] El Condor Pasa
            // Junior | Beginner | Kyodo News Hai | PLANNING
            // Turn 12 | Stats: 194/154/133/97/156 | SP: 38
            // Growth rates: Speed +20%, Wit +10%
            // ══════════════════════════════════════════════════════
            [
                'career_id' => 8,
                'user_id' => $userId,
                'turn_number' => 12,
                'prediction_type' => 'stat_projection',
                'predicted_value' => [
                    'speed' => 470, 'stamina' => 360, 'power' => 340,
                    'guts' => 270, 'wit' => 400,
                    'notes' => 'End-of-junior projected stats (~14 turns remaining in junior). Speed growth (20%) is the main driver. Wit growth (10%) building nicely. Guts critically lagging.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.5800,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 8,
                'user_id' => $userId,
                'turn_number' => 12,
                'prediction_type' => 'race_outcome',
                'predicted_value' => [
                    'race' => 'Kyodo News Hai',
                    'predicted_place' => 'TOP 5',
                    'strategy' => 'PACE',
                    'key_skills' => ['Corazón ☆ Ardiente', 'Pace Chaser Straightaways ○'],
                    'risk_factors' => ['All stats are very low (F/G tier) — early junior is high variance', 'Only 2 acquired skills — highly underpowered for higher placement'],
                    'confidence_note' => 'Beginner class on junior schedule — TOP 5 is a realistic minimum target. Speed growth (20%) gives good mid-career potential.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.6500,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
            [
                'career_id' => 8,
                'user_id' => $userId,
                'turn_number' => 12,
                'prediction_type' => 'sp_efficiency',
                'predicted_value' => [
                    'sp_remaining' => 38,
                    'estimated_sp_needed' => 1104,
                    'acquired_skills' => 2,
                    'pending_skills' => 7,
                    'highest_priority' => ['Resplendent Red Ace (180 SP)', 'Certain Victory (180 SP)', 'Straightaway Adept (170 SP)'],
                    'recommendation' => 'Moderate SP (38) at early junior. Focus on at least one legacy-inherited skill (180 SP each). Pace Chaser Straightaways ⦾ upgrade (140 SP) is next best.',
                ],
                'actual_value' => null,
                'confidence_score' => 0.7200,
                'accuracy_score' => null,
                'model_version' => 'v1.0-import',
                'is_ab_test' => false,
                'ab_variant' => null,
            ],
        ];
    }
}
