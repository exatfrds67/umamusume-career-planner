<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Services\GameMechanicsEngine;

/**
 * Property-Based Tests for GameMechanicsEngine
 *
 * These tests validate correctness properties that should hold true
 * across all valid inputs, not just specific examples.
 */
describe('GameMechanicsEngine Property-Based Tests', function () {
    beforeEach(function () {
        $this->engine = app(GameMechanicsEngine::class);
    });

    /**
     * Property 11: Facility Level Progression
     *
     * **Validates: Requirements 3.1**
     *
     * Facility levels must progress correctly (every 4 uses = +1 level, max Level 5).
     *
     * This property ensures that:
     * 1. Level increases by 1 for every 4 uses
     * 2. Level never exceeds 5
     * 3. Level is always at least 1
     * 4. Level progression is monotonic (never decreases)
     */
    describe('Property 11: Facility Level Progression', function () {
        it('progresses by 1 level every 4 uses', function () {
            // Test a range of use counts
            for ($uses = 0; $uses < 100; $uses++) {
                $level = $this->engine->calculateFacilityLevel($uses);
                $expectedLevel = min((int) floor($uses / 4) + 1, 5);

                expect($level)->toBe($expectedLevel, "Failed for {$uses} uses");
            }
        });

        it('never exceeds level 5', function () {
            // Test with very high use counts
            $useCounts = [16, 20, 50, 100, 500, 1000];

            foreach ($useCounts as $uses) {
                $level = $this->engine->calculateFacilityLevel($uses);
                expect($level)->toBeLessThanOrEqual(5, "Failed for {$uses} uses");
            }
        });

        it('is always at least level 1', function () {
            // Test with various use counts including edge cases
            $useCounts = [0, 1, 2, 3, 4, 10, 50, 100];

            foreach ($useCounts as $uses) {
                $level = $this->engine->calculateFacilityLevel($uses);
                expect($level)->toBeGreaterThanOrEqual(1, "Failed for {$uses} uses");
            }
        });

        it('is monotonically increasing', function () {
            // Level should never decrease as use count increases
            $previousLevel = 0;

            for ($uses = 0; $uses < 100; $uses++) {
                $currentLevel = $this->engine->calculateFacilityLevel($uses);

                expect($currentLevel)->toBeGreaterThanOrEqual($previousLevel, "Level decreased at {$uses} uses");

                $previousLevel = $currentLevel;
            }
        });

        it('has correct boundaries at level transitions', function () {
            // Test exact boundaries where level should change
            $boundaries = [
                ['uses' => 3, 'level' => 1],
                ['uses' => 4, 'level' => 2],
                ['uses' => 7, 'level' => 2],
                ['uses' => 8, 'level' => 3],
                ['uses' => 11, 'level' => 3],
                ['uses' => 12, 'level' => 4],
                ['uses' => 15, 'level' => 4],
                ['uses' => 16, 'level' => 5],
            ];

            foreach ($boundaries as $boundary) {
                $level = $this->engine->calculateFacilityLevel($boundary['uses']);
                expect($level)->toBe($boundary['level'], "Failed at boundary: {$boundary['uses']} uses");
            }
        });

        it('maintains correct level for all values within a range', function () {
            // Each level should be maintained for exactly 4 consecutive use counts
            $ranges = [
                ['start' => 0, 'end' => 3, 'level' => 1],
                ['start' => 4, 'end' => 7, 'level' => 2],
                ['start' => 8, 'end' => 11, 'level' => 3],
                ['start' => 12, 'end' => 15, 'level' => 4],
                ['start' => 16, 'end' => 100, 'level' => 5], // Level 5 is capped
            ];

            foreach ($ranges as $range) {
                for ($uses = $range['start']; $uses <= $range['end']; $uses++) {
                    $level = $this->engine->calculateFacilityLevel($uses);
                    expect($level)->toBe($range['level'], "Failed for {$uses} uses in range {$range['start']}-{$range['end']}");
                }
            }
        });
    });

    /**
     * Property 10: Multi-Training Bonus Calculation
     *
     * **Validates: Requirements 3.5**
     *
     * Multi-training bonus must be calculated as +5% per support card (max +30%).
     */
    describe('Property 10: Multi-Training Bonus Calculation', function () {
        it('scales linearly at 5% per card up to 6 cards', function () {
            for ($numCards = 0; $numCards <= 6; $numCards++) {
                $bonus = $this->engine->calculateMultiTrainingBonus($numCards);
                $expected = min($numCards * 0.05, 0.30);

                expect($bonus)->toEqualWithDelta($expected, 0.001, "Failed for {$numCards} cards");
            }
        });

        it('never exceeds 30% regardless of card count', function () {
            // Test with various card counts including unrealistic high values
            $cardCounts = [0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 20, 100];

            foreach ($cardCounts as $numCards) {
                $bonus = $this->engine->calculateMultiTrainingBonus($numCards);
                expect($bonus)->toBeLessThanOrEqual(0.30, "Failed for {$numCards} cards");
            }
        });

        it('is always non-negative', function () {
            // Test with various card counts including edge cases
            $cardCounts = [0, 1, 5, 10, 100];

            foreach ($cardCounts as $numCards) {
                $bonus = $this->engine->calculateMultiTrainingBonus($numCards);
                expect($bonus)->toBeGreaterThanOrEqual(0.0, "Failed for {$numCards} cards");
            }
        });

        it('is monotonically increasing up to the cap', function () {
            // Bonus should never decrease as card count increases
            $previousBonus = 0.0;

            for ($numCards = 0; $numCards <= 10; $numCards++) {
                $currentBonus = $this->engine->calculateMultiTrainingBonus($numCards);

                expect($currentBonus)->toBeGreaterThanOrEqual($previousBonus, "Bonus decreased at {$numCards} cards");

                $previousBonus = $currentBonus;
            }
        });
    });

    /**
     * Property 7: Stamina Requirement Calculation
     *
     * **Validates: Requirements 3.3**
     *
     * Stamina requirements must be calculated correctly based on distance and running style.
     *
     * This property ensures that:
     * 1. Requirements fall within expected ranges for each distance
     * 2. Running style modifiers are applied correctly
     * 3. Recovery skills reduce requirements appropriately
     * 4. Requirements never go negative
     */
    describe('Property 7: Stamina Requirement Calculation', function () {
        it('falls within expected ranges for each distance with Escape style', function () {
            $distances = [
                ['distance' => RaceDistance::SPRINT, 'min' => 350, 'max' => 400],
                ['distance' => RaceDistance::MILE, 'min' => 450, 'max' => 500],
                ['distance' => RaceDistance::MEDIUM, 'min' => 600, 'max' => 700],
                ['distance' => RaceDistance::LONG, 'min' => 850, 'max' => 1000],
            ];

            foreach ($distances as $data) {
                $distance = $data['distance'];
                $min = $data['min'];
                $max = $data['max'];

                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    RunningStyle::ESCAPE,
                    []
                );

                expect($requirement)->toBeGreaterThanOrEqual($min, "Failed for {$distance->value}: requirement {$requirement} < min {$min}");
                expect($requirement)->toBeLessThanOrEqual($max, "Failed for {$distance->value}: requirement {$requirement} > max {$max}");
            }
        });

        it('decreases with less aggressive running styles', function () {
            $styles = [RunningStyle::ESCAPE, RunningStyle::LEAD, RunningStyle::PACE, RunningStyle::CHASE];
            $previousRequirement = PHP_INT_MAX;

            foreach ($styles as $style) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    RaceDistance::MEDIUM,
                    $style,
                    []
                );

                expect($requirement)->toBeLessThan($previousRequirement, "Requirement did not decrease for style {$style->value}");

                $previousRequirement = $requirement;
            }
        });

        it('is always non-negative', function () {
            $distances = RaceDistance::cases();
            $styles = RunningStyle::cases();

            // Test with various recovery skill counts
            $recoverySkillCounts = [0, 1, 2, 3, 5, 10];

            foreach ($distances as $distance) {
                foreach ($styles as $style) {
                    foreach ($recoverySkillCounts as $count) {
                        $skills = array_fill(0, $count, 1); // Create array of skill IDs

                        $requirement = $this->engine->calculateStaminaRequirement(
                            $distance,
                            $style,
                            $skills
                        );

                        expect($requirement)->toBeGreaterThanOrEqual(0, "Negative requirement for {$distance->value}, {$style->value}, {$count} skills");
                    }
                }
            }
        });

        it('decreases with more recovery skills', function () {
            $distance = RaceDistance::LONG;
            $style = RunningStyle::ESCAPE;
            $previousRequirement = PHP_INT_MAX;

            for ($numSkills = 0; $numSkills <= 5; $numSkills++) {
                $skills = array_fill(0, $numSkills, 1);

                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    $skills
                );

                expect($requirement)->toBeLessThanOrEqual($previousRequirement, "Requirement increased with {$numSkills} skills");

                $previousRequirement = $requirement;
            }
        });

        it('increases with longer distances', function () {
            $distances = [RaceDistance::SPRINT, RaceDistance::MILE, RaceDistance::MEDIUM, RaceDistance::LONG];
            $previousRequirement = 0;

            foreach ($distances as $distance) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    RunningStyle::ESCAPE,
                    []
                );

                expect($requirement)->toBeGreaterThan($previousRequirement, "Requirement did not increase for {$distance->value}");

                $previousRequirement = $requirement;
            }
        });

        it('applies style multipliers correctly', function () {
            $distance = RaceDistance::MEDIUM;
            $baseRequirement = $this->engine->calculateStaminaRequirement(
                $distance,
                RunningStyle::ESCAPE,
                []
            );

            // Test each style's multiplier
            $styles = [
                ['style' => RunningStyle::LEAD, 'multiplier' => 0.95],
                ['style' => RunningStyle::PACE, 'multiplier' => 0.85],
                ['style' => RunningStyle::CHASE, 'multiplier' => 0.75],
            ];

            foreach ($styles as $data) {
                $style = $data['style'];
                $expectedMultiplier = $data['multiplier'];

                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    []
                );

                // Calculate expected value with some tolerance for rounding
                $expected = (int) round($baseRequirement * $expectedMultiplier);

                expect($requirement)->toEqualWithDelta($expected, 1, "Failed for style {$style->value}");
            }
        });

        it('reduces by approximately 150-200 per recovery skill', function () {
            $distance = RaceDistance::LONG;
            $style = RunningStyle::ESCAPE;

            $withoutSkills = $this->engine->calculateStaminaRequirement($distance, $style, []);
            $withOneSkill = $this->engine->calculateStaminaRequirement($distance, $style, [1]);
            $withTwoSkills = $this->engine->calculateStaminaRequirement($distance, $style, [1, 2]);

            $reductionPerSkill1 = $withoutSkills - $withOneSkill;
            $reductionPerSkill2 = ($withoutSkills - $withTwoSkills) / 2;

            // Each skill should reduce by 150-200 (we use 175 as midpoint)
            expect($reductionPerSkill1)->toBeGreaterThanOrEqual(150);
            expect($reductionPerSkill1)->toBeLessThanOrEqual(200);

            expect($reductionPerSkill2)->toBeGreaterThanOrEqual(150);
            expect($reductionPerSkill2)->toBeLessThanOrEqual(200);
        });

        it('is deterministic for the same inputs', function () {
            $distance = RaceDistance::MEDIUM;
            $style = RunningStyle::PACE;
            $skills = [1, 2];

            // Call multiple times with same inputs
            $results = [];
            for ($i = 0; $i < 10; $i++) {
                $results[] = $this->engine->calculateStaminaRequirement($distance, $style, $skills);
            }

            // All results should be identical
            $firstResult = $results[0];
            foreach ($results as $result) {
                expect($result)->toBe($firstResult);
            }
        });

        it('handles all valid distance-style combinations', function () {
            $distances = RaceDistance::cases();
            $styles = RunningStyle::cases();

            foreach ($distances as $distance) {
                foreach ($styles as $style) {
                    $requirement = $this->engine->calculateStaminaRequirement(
                        $distance,
                        $style,
                        []
                    );

                    // Should return a valid positive integer
                    expect($requirement)->toBeInt();
                    expect($requirement)->toBeGreaterThan(0);
                }
            }
        });
    });

    /**
     * Property 5: Skill Hint Level Discount Accuracy
     *
     * **Validates: Requirements 3.2**
     *
     * Skill cost calculations must correctly apply hint level discounts (10%/20%/30%/35%/40%).
     *
     * This property ensures that:
     * 1. Hint level discounts are applied correctly (0%/10%/20%/30%/35%/40%)
     * 2. Fast Learner bonus is applied correctly (additional 10% discount)
     * 3. Costs are always non-negative integers
     * 4. Costs decrease monotonically with higher hint levels
     * 5. Costs are deterministic for the same inputs
     */
    describe('Property 5: Skill Hint Level Discount Accuracy', function () {
        it('applies correct hint level discounts', function () {
            $baseCost = 100;
            $hintLevels = [
                0 => 0.00,  // No hint: 0% discount
                1 => 0.10,  // Level 1: 10% discount
                2 => 0.20,  // Level 2: 20% discount
                3 => 0.30,  // Level 3: 30% discount
                4 => 0.35,  // Level 4: 35% discount
                5 => 0.40,  // Level 5: 40% discount (max)
            ];

            foreach ($hintLevels as $level => $discount) {
                $expectedCost = (int) round($baseCost * (1 - $discount));
                $actualCost = $this->engine->calculateSkillCost($baseCost, $level, false);

                expect($actualCost)->toBe($expectedCost, "Failed for hint level {$level}");
            }
        });

        it('applies Fast Learner bonus correctly', function () {
            $baseCost = 100;
            $hintLevel = 3; // 30% discount

            // Without Fast Learner: 100 * (1 - 0.30) = 70
            $withoutFastLearner = $this->engine->calculateSkillCost($baseCost, $hintLevel, false);
            expect($withoutFastLearner)->toBe(70);

            // With Fast Learner: 100 * (1 - 0.30) * 0.90 = 63
            $withFastLearner = $this->engine->calculateSkillCost($baseCost, $hintLevel, true);
            expect($withFastLearner)->toBe(63);
        });

        it('applies Fast Learner bonus on top of hint discounts', function () {
            $baseCost = 180;
            $hintLevels = [0, 1, 2, 3, 4, 5];

            foreach ($hintLevels as $level) {
                $withoutFastLearner = $this->engine->calculateSkillCost($baseCost, $level, false);
                $withFastLearner = $this->engine->calculateSkillCost($baseCost, $level, true);

                // Fast Learner should always reduce cost further
                expect($withFastLearner)->toBeLessThan($withoutFastLearner, "Failed for hint level {$level}");

                // The reduction should be approximately 10% of the hint-discounted cost
                $expectedReduction = (int) round($withoutFastLearner * 0.10);
                $actualReduction = $withoutFastLearner - $withFastLearner;

                expect($actualReduction)->toEqualWithDelta($expectedReduction, 1, "Fast Learner reduction incorrect at hint level {$level}");
            }
        });

        it('always returns non-negative integers', function () {
            $baseCosts = [50, 100, 150, 180, 200, 250, 300];
            $hintLevels = [0, 1, 2, 3, 4, 5];
            $fastLearnerOptions = [false, true];

            foreach ($baseCosts as $baseCost) {
                foreach ($hintLevels as $level) {
                    foreach ($fastLearnerOptions as $hasFastLearner) {
                        $cost = $this->engine->calculateSkillCost($baseCost, $level, $hasFastLearner);

                        expect($cost)->toBeInt();
                        expect($cost)->toBeGreaterThanOrEqual(0);
                    }
                }
            }
        });

        it('decreases monotonically with higher hint levels', function () {
            $baseCost = 180;
            $previousCost = PHP_INT_MAX;

            for ($level = 0; $level <= 5; $level++) {
                $currentCost = $this->engine->calculateSkillCost($baseCost, $level, false);

                expect($currentCost)->toBeLessThanOrEqual($previousCost, "Cost increased at hint level {$level}");

                $previousCost = $currentCost;
            }
        });

        it('is deterministic for the same inputs', function () {
            $baseCost = 150;
            $hintLevel = 3;
            $hasFastLearner = true;

            // Call multiple times with same inputs
            $results = [];
            for ($i = 0; $i < 10; $i++) {
                $results[] = $this->engine->calculateSkillCost($baseCost, $hintLevel, $hasFastLearner);
            }

            // All results should be identical
            $firstResult = $results[0];
            foreach ($results as $result) {
                expect($result)->toBe($firstResult);
            }
        });

        it('handles edge case hint levels correctly', function () {
            $baseCost = 100;

            // Test hint level 0 (no discount)
            $level0 = $this->engine->calculateSkillCost($baseCost, 0, false);
            expect($level0)->toBe(100);

            // Test hint level 5 (max discount)
            $level5 = $this->engine->calculateSkillCost($baseCost, 5, false);
            expect($level5)->toBe(60); // 100 * (1 - 0.40) = 60

            // Test hint level 5 with Fast Learner (max discount + Fast Learner)
            $level5FastLearner = $this->engine->calculateSkillCost($baseCost, 5, true);
            expect($level5FastLearner)->toBe(54); // 100 * (1 - 0.40) * 0.90 = 54
        });

        it('handles various base costs correctly', function () {
            $testCases = [
                ['baseCost' => 50, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 35],   // 50 * 0.70 = 35
                ['baseCost' => 120, 'hintLevel' => 2, 'fastLearner' => false, 'expected' => 96],  // 120 * 0.80 = 96
                ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 126], // 180 * 0.70 = 126
                ['baseCost' => 200, 'hintLevel' => 4, 'fastLearner' => false, 'expected' => 130], // 200 * 0.65 = 130
                ['baseCost' => 250, 'hintLevel' => 5, 'fastLearner' => false, 'expected' => 150], // 250 * 0.60 = 150
            ];

            foreach ($testCases as $case) {
                $actualCost = $this->engine->calculateSkillCost(
                    $case['baseCost'],
                    $case['hintLevel'],
                    $case['fastLearner']
                );

                expect($actualCost)->toBe($case['expected'], "Failed for base cost {$case['baseCost']}, hint level {$case['hintLevel']}");
            }
        });

        it('handles Fast Learner with various base costs', function () {
            $testCases = [
                ['baseCost' => 100, 'hintLevel' => 3, 'expected' => 63],  // 100 * 0.70 * 0.90 = 63
                ['baseCost' => 180, 'hintLevel' => 3, 'expected' => 113], // 180 * 0.70 * 0.90 = 113.4 ≈ 113
                ['baseCost' => 200, 'hintLevel' => 4, 'expected' => 117], // 200 * 0.65 * 0.90 = 117
                ['baseCost' => 250, 'hintLevel' => 5, 'expected' => 135], // 250 * 0.60 * 0.90 = 135
            ];

            foreach ($testCases as $case) {
                $actualCost = $this->engine->calculateSkillCost(
                    $case['baseCost'],
                    $case['hintLevel'],
                    true
                );

                expect($actualCost)->toBe($case['expected'], "Failed for base cost {$case['baseCost']}, hint level {$case['hintLevel']} with Fast Learner");
            }
        });

        it('calculates correct discount percentages', function () {
            $baseCost = 1000; // Use large base cost for easier percentage verification

            $testCases = [
                ['hintLevel' => 0, 'expectedCost' => 1000], // 0% discount
                ['hintLevel' => 1, 'expectedCost' => 900],  // 10% discount
                ['hintLevel' => 2, 'expectedCost' => 800],  // 20% discount
                ['hintLevel' => 3, 'expectedCost' => 700],  // 30% discount
                ['hintLevel' => 4, 'expectedCost' => 650],  // 35% discount
                ['hintLevel' => 5, 'expectedCost' => 600],  // 40% discount
            ];

            foreach ($testCases as $case) {
                $actualCost = $this->engine->calculateSkillCost($baseCost, $case['hintLevel'], false);

                expect($actualCost)->toBe($case['expectedCost'], "Failed for hint level {$case['hintLevel']}");
            }
        });

        it('handles hint level boundaries correctly', function () {
            $baseCost = 100;

            // Test transition from level 3 (30%) to level 4 (35%)
            $level3 = $this->engine->calculateSkillCost($baseCost, 3, false);
            $level4 = $this->engine->calculateSkillCost($baseCost, 4, false);

            expect($level3)->toBe(70); // 30% discount
            expect($level4)->toBe(65); // 35% discount
            expect($level4)->toBeLessThan($level3);

            // Test transition from level 4 (35%) to level 5 (40%)
            $level5 = $this->engine->calculateSkillCost($baseCost, 5, false);

            expect($level5)->toBe(60); // 40% discount
            expect($level5)->toBeLessThan($level4);
        });

        it('applies discounts correctly for common skill costs', function () {
            // Test with common skill costs from the game
            $commonSkillCosts = [
                ['baseCost' => 120, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 84],   // Common normal skill
                ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 126],  // Common gold skill
                ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => true, 'expected' => 113],   // Gold skill with Fast Learner
                ['baseCost' => 120, 'hintLevel' => 5, 'fastLearner' => false, 'expected' => 72],   // Max hint normal skill
                ['baseCost' => 180, 'hintLevel' => 5, 'fastLearner' => true, 'expected' => 97],    // Max hint gold skill with Fast Learner
            ];

            foreach ($commonSkillCosts as $case) {
                $actualCost = $this->engine->calculateSkillCost(
                    $case['baseCost'],
                    $case['hintLevel'],
                    $case['fastLearner']
                );

                expect($actualCost)->toBe($case['expected'], "Failed for base cost {$case['baseCost']}, hint level {$case['hintLevel']}, Fast Learner: ".($case['fastLearner'] ? 'yes' : 'no'));
            }
        });

        it('never increases cost with higher hint levels', function () {
            $baseCosts = [50, 100, 150, 180, 200, 250];

            foreach ($baseCosts as $baseCost) {
                $costs = [];

                for ($level = 0; $level <= 5; $level++) {
                    $costs[$level] = $this->engine->calculateSkillCost($baseCost, $level, false);
                }

                // Verify costs are monotonically decreasing
                for ($level = 1; $level <= 5; $level++) {
                    expect($costs[$level])->toBeLessThanOrEqual($costs[$level - 1], 'Cost increased from level '.($level - 1)." to {$level} for base cost {$baseCost}");
                }
            }
        });

        it('Fast Learner never increases cost', function () {
            $baseCosts = [50, 100, 150, 180, 200, 250];
            $hintLevels = [0, 1, 2, 3, 4, 5];

            foreach ($baseCosts as $baseCost) {
                foreach ($hintLevels as $level) {
                    $withoutFastLearner = $this->engine->calculateSkillCost($baseCost, $level, false);
                    $withFastLearner = $this->engine->calculateSkillCost($baseCost, $level, true);

                    expect($withFastLearner)->toBeLessThanOrEqual($withoutFastLearner, "Fast Learner increased cost for base cost {$baseCost}, hint level {$level}");
                }
            }
        });
    });

    /**
     * Property 8: Stamina Recovery Skill Adjustment
     *
     * **Validates: Requirements 3.3**
     *
     * Stamina requirements must be reduced by 150-200 per gold recovery skill equipped.
     */
    describe('Property 8: Stamina Recovery Skill Adjustment', function () {
        it('reduces stamina requirements for each recovery skill', function () {
            $distance = RaceDistance::LONG;
            $style = RunningStyle::ESCAPE;

            $baseRequirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                []
            );

            $withRecovery = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                [1] // One gold recovery skill
            );

            $reduction = $baseRequirement - $withRecovery;

            // Reduction should be between 150-200 per skill
            expect($reduction)->toBeGreaterThanOrEqual(150);
            expect($reduction)->toBeLessThanOrEqual(200);
        });

        it('scales linearly with number of recovery skills', function () {
            $distance = RaceDistance::LONG;
            $style = RunningStyle::ESCAPE;

            $baseRequirement = $this->engine->calculateStaminaRequirement($distance, $style, []);
            $withOneSkill = $this->engine->calculateStaminaRequirement($distance, $style, [1]);
            $withTwoSkills = $this->engine->calculateStaminaRequirement($distance, $style, [1, 2]);
            $withThreeSkills = $this->engine->calculateStaminaRequirement($distance, $style, [1, 2, 3]);

            // Calculate reductions
            $reduction1 = $baseRequirement - $withOneSkill;
            $reduction2 = $baseRequirement - $withTwoSkills;
            $reduction3 = $baseRequirement - $withThreeSkills;

            // Reductions should scale linearly (approximately)
            // Allow some tolerance for rounding
            expect($reduction2)->toEqualWithDelta($reduction1 * 2, 5);
            expect($reduction3)->toEqualWithDelta($reduction1 * 3, 5);
        });

        it('applies recovery reduction after style modifier', function () {
            $distance = RaceDistance::MEDIUM;

            // Test with Escape (no style modifier)
            $escapeBase = $this->engine->calculateStaminaRequirement($distance, RunningStyle::ESCAPE, []);
            $escapeWithSkill = $this->engine->calculateStaminaRequirement($distance, RunningStyle::ESCAPE, [1]);
            $escapeReduction = $escapeBase - $escapeWithSkill;

            // Test with Chase (0.75x style modifier)
            $chaseBase = $this->engine->calculateStaminaRequirement($distance, RunningStyle::CHASE, []);
            $chaseWithSkill = $this->engine->calculateStaminaRequirement($distance, RunningStyle::CHASE, [1]);
            $chaseReduction = $chaseBase - $chaseWithSkill;

            // Both should have the same reduction amount (175)
            // because recovery is applied after style modifier
            expect($escapeReduction)->toBe($chaseReduction);
            expect($escapeReduction)->toEqualWithDelta(175, 1);
        });

        it('never results in negative stamina requirement', function () {
            // Test extreme case: short distance, efficient style, many recovery skills
            $requirement = $this->engine->calculateStaminaRequirement(
                RaceDistance::SPRINT,
                RunningStyle::CHASE,
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // 10 recovery skills
            );

            // Should be capped at 0, not negative
            expect($requirement)->toBeGreaterThanOrEqual(0);
        });

        it('works with empty recovery skills array', function () {
            $requirement = $this->engine->calculateStaminaRequirement(
                RaceDistance::MEDIUM,
                RunningStyle::ESCAPE,
                []
            );

            // Should work without errors and return base requirement
            expect($requirement)->toBeInt();
            expect($requirement)->toBeGreaterThan(0);
        });
    });

    /**
     * Training Gain Properties
     *
     * These properties validate the behavior of calculateTrainingGain across various inputs.
     */
    describe('Training Gain Properties', function () {
        it('always returns a positive integer for valid inputs', function () {
            // Test with various combinations of inputs
            $testCases = [
                ['facilityLevel' => 1, 'growthRate' => 1.0, 'mood' => Mood::NORMAL, 'numCards' => 0, 'friendship' => false],
                ['facilityLevel' => 5, 'growthRate' => 1.2, 'mood' => Mood::GREAT, 'numCards' => 6, 'friendship' => true],
                ['facilityLevel' => 3, 'growthRate' => 0.8, 'mood' => Mood::BAD, 'numCards' => 3, 'friendship' => false],
                ['facilityLevel' => 2, 'growthRate' => 1.0, 'mood' => Mood::GOOD, 'numCards' => 2, 'friendship' => true],
            ];

            foreach ($testCases as $case) {
                $result = $this->engine->calculateTrainingGain(
                    baseStat: 500,
                    facilityLevel: $case['facilityLevel'],
                    growthRate: $case['growthRate'],
                    mood: $case['mood'],
                    supportCardBonuses: [],
                    numCardsPresent: $case['numCards'],
                    isFriendshipTraining: $case['friendship']
                );

                expect($result)->toBeInt();
                expect($result)->toBeGreaterThan(0);
            }
        });

        it('increases with higher facility levels', function () {
            // Training gain should increase as facility level increases
            $previousGain = 0;

            for ($level = 1; $level <= 5; $level++) {
                $gain = $this->engine->calculateTrainingGain(
                    baseStat: 500,
                    facilityLevel: $level,
                    growthRate: 1.0,
                    mood: Mood::NORMAL,
                    supportCardBonuses: [],
                    numCardsPresent: 0,
                    isFriendshipTraining: false
                );

                expect($gain)->toBeGreaterThan($previousGain, "Gain did not increase at level {$level}");

                $previousGain = $gain;
            }
        });

        it('increases with more support cards present', function () {
            // Training gain should increase as more cards are present
            $previousGain = 0;

            for ($numCards = 0; $numCards <= 6; $numCards++) {
                $gain = $this->engine->calculateTrainingGain(
                    baseStat: 500,
                    facilityLevel: 1,
                    growthRate: 1.0,
                    mood: Mood::NORMAL,
                    supportCardBonuses: [],
                    numCardsPresent: $numCards,
                    isFriendshipTraining: false
                );

                expect($gain)->toBeGreaterThanOrEqual($previousGain, "Gain decreased at {$numCards} cards");

                $previousGain = $gain;
            }
        });

        it('increases with better mood', function () {
            // Training gain should increase as mood improves
            $moods = [Mood::VERY_BAD, Mood::BAD, Mood::NORMAL, Mood::GOOD, Mood::GREAT];
            $previousGain = 0;

            foreach ($moods as $mood) {
                $gain = $this->engine->calculateTrainingGain(
                    baseStat: 500,
                    facilityLevel: 1,
                    growthRate: 1.0,
                    mood: $mood,
                    supportCardBonuses: [],
                    numCardsPresent: 0,
                    isFriendshipTraining: false
                );

                expect($gain)->toBeGreaterThan($previousGain, "Gain did not increase for mood {$mood->value}");

                $previousGain = $gain;
            }
        });

        it('is higher with friendship training than without', function () {
            $withoutFriendship = $this->engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: 3,
                growthRate: 1.0,
                mood: Mood::NORMAL,
                supportCardBonuses: [],
                numCardsPresent: 3,
                isFriendshipTraining: false
            );

            $withFriendship = $this->engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: 3,
                growthRate: 1.0,
                mood: Mood::NORMAL,
                supportCardBonuses: [],
                numCardsPresent: 3,
                isFriendshipTraining: true
            );

            expect($withFriendship)->toBeGreaterThan($withoutFriendship);
        });

        it('adds support card bonuses to the final result', function () {
            $withoutBonus = $this->engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: 1,
                growthRate: 1.0,
                mood: Mood::NORMAL,
                supportCardBonuses: [],
                numCardsPresent: 0,
                isFriendshipTraining: false
            );

            $withBonus = $this->engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: 1,
                growthRate: 1.0,
                mood: Mood::NORMAL,
                supportCardBonuses: ['speed' => 20],
                numCardsPresent: 0,
                isFriendshipTraining: false
            );

            // The difference should be approximately the bonus amount
            $difference = $withBonus - $withoutBonus;
            expect($difference)->toEqualWithDelta(20, 1); // Allow 1 point variance due to rounding
        });
    });
});
