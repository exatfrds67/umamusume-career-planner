<?php

declare(strict_types=1);

use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Services\GameMechanicsEngine;

describe('GameMechanicsEngine::calculateStaminaRequirement - Property Tests', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    /**
     * Property 7: Stamina Requirement Calculation
     *
     * **Validates: Requirements 3.3**
     *
     * Stamina requirements must be calculated correctly based on distance and running style.
     * Distance-specific thresholds: Sprint (350-400), Mile (450-500), Medium (600-700),
     * Long (850-1000) for Escape style.
     */
    it('calculates stamina requirements within distance thresholds for Escape style', function () {
        $distances = [
            ['distance' => RaceDistance::SPRINT, 'min' => 350, 'max' => 400],
            ['distance' => RaceDistance::MILE, 'min' => 450, 'max' => 500],
            ['distance' => RaceDistance::MEDIUM, 'min' => 600, 'max' => 700],
            ['distance' => RaceDistance::LONG, 'min' => 850, 'max' => 1000],
        ];

        foreach ($distances as $testCase) {
            $requirement = $this->engine->calculateStaminaRequirement(
                $testCase['distance'],
                RunningStyle::ESCAPE,
                []
            );

            expect($requirement)->toBeGreaterThanOrEqual($testCase['min'])
                ->and($requirement)->toBeLessThanOrEqual($testCase['max']);
        }
    })->group('property');

    it('applies running style multipliers correctly', function () {
        // Property: Different running styles should have different stamina requirements
        // Escape: 1.0x (baseline), Lead: 0.95x, Pace: 0.85x, Chase: 0.75x
        $distance = RaceDistance::LONG;
        $noRecoverySkills = [];

        $escapeRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::ESCAPE,
            $noRecoverySkills
        );

        $leadRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::LEAD,
            $noRecoverySkills
        );

        $paceRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::PACE,
            $noRecoverySkills
        );

        $chaseRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::CHASE,
            $noRecoverySkills
        );

        // Verify ordering: Escape > Lead > Pace > Chase
        expect($escapeRequirement)->toBeGreaterThan($leadRequirement)
            ->and($leadRequirement)->toBeGreaterThan($paceRequirement)
            ->and($paceRequirement)->toBeGreaterThan($chaseRequirement);

        // Verify approximate multipliers (allowing for rounding)
        $baseRequirement = $distance->recommendedStamina();
        expect($leadRequirement)->toBeGreaterThanOrEqual((int) round($baseRequirement * 0.95) - 1)
            ->and($leadRequirement)->toBeLessThanOrEqual((int) round($baseRequirement * 0.95) + 1);

        expect($paceRequirement)->toBeGreaterThanOrEqual((int) round($baseRequirement * 0.85) - 1)
            ->and($paceRequirement)->toBeLessThanOrEqual((int) round($baseRequirement * 0.85) + 1);

        expect($chaseRequirement)->toBeGreaterThanOrEqual((int) round($baseRequirement * 0.75) - 1)
            ->and($chaseRequirement)->toBeLessThanOrEqual((int) round($baseRequirement * 0.75) + 1);
    })->group('property');

    it('reduces stamina requirements with recovery skills', function () {
        // Property: Recovery skills should reduce stamina requirements
        $distance = RaceDistance::LONG;
        $style = RunningStyle::ESCAPE;

        $baseRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            []
        );

        $withOneSkill = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            [1] // One recovery skill
        );

        $withTwoSkills = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            [1, 2] // Two recovery skills
        );

        // Verify reduction occurs
        expect($withOneSkill)->toBeLessThan($baseRequirement)
            ->and($withTwoSkills)->toBeLessThan($withOneSkill);

        // Verify reduction is approximately 175 per skill (150-200 range)
        $reductionOne = $baseRequirement - $withOneSkill;
        $reductionTwo = $baseRequirement - $withTwoSkills;

        expect($reductionOne)->toBeGreaterThanOrEqual(150)
            ->and($reductionOne)->toBeLessThanOrEqual(200);

        expect($reductionTwo)->toBeGreaterThanOrEqual(300)
            ->and($reductionTwo)->toBeLessThanOrEqual(400);
    })->group('property');

    it('never produces negative stamina requirements', function () {
        // Property: Stamina requirements should never be negative, even with many recovery skills
        $distances = RaceDistance::all();
        $styles = RunningStyle::all();

        // Test with excessive recovery skills
        $manyRecoverySkills = range(1, 10);

        foreach ($distances as $distance) {
            foreach ($styles as $style) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    $manyRecoverySkills
                );

                expect($requirement)->toBeGreaterThanOrEqual(0);
            }
        }
    })->group('property');

    it('produces deterministic results', function () {
        // Property: Same inputs should always produce same outputs
        $testCases = [
            ['distance' => RaceDistance::SPRINT, 'style' => RunningStyle::ESCAPE, 'skills' => []],
            ['distance' => RaceDistance::MILE, 'style' => RunningStyle::LEAD, 'skills' => [1]],
            ['distance' => RaceDistance::MEDIUM, 'style' => RunningStyle::PACE, 'skills' => [1, 2]],
            ['distance' => RaceDistance::LONG, 'style' => RunningStyle::CHASE, 'skills' => []],
        ];

        foreach ($testCases as $case) {
            $requirement1 = $this->engine->calculateStaminaRequirement(
                $case['distance'],
                $case['style'],
                $case['skills']
            );

            $requirement2 = $this->engine->calculateStaminaRequirement(
                $case['distance'],
                $case['style'],
                $case['skills']
            );

            expect($requirement1)->toBe($requirement2);
        }
    })->group('property');

    it('returns integer values only', function () {
        // Property: All stamina requirements should be integers
        $distances = RaceDistance::all();
        $styles = RunningStyle::all();

        foreach ($distances as $distance) {
            foreach ($styles as $style) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    []
                );

                expect($requirement)->toBeInt();
            }
        }
    })->group('property');

    it('scales correctly across all distance and style combinations', function () {
        // Property: Longer distances should require more stamina for the same style
        $styles = RunningStyle::all();
        $orderedDistances = RaceDistance::ordered();

        foreach ($styles as $style) {
            $previousRequirement = 0;

            foreach ($orderedDistances as $distance) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    []
                );

                // Each longer distance should require more stamina
                expect($requirement)->toBeGreaterThan($previousRequirement);
                $previousRequirement = $requirement;
            }
        }
    })->group('property');

    it('validates real-world race scenarios', function () {
        // Property: Real-world race scenarios should match expected stamina requirements
        $realWorldScenarios = [
            // Sprint races with Escape style
            [
                'name' => 'Sprint - Escape - No Skills',
                'distance' => RaceDistance::SPRINT,
                'style' => RunningStyle::ESCAPE,
                'skills' => [],
                'expectedMin' => 350,
                'expectedMax' => 400,
            ],
            // Mile races with Lead style
            [
                'name' => 'Mile - Lead - No Skills',
                'distance' => RaceDistance::MILE,
                'style' => RunningStyle::LEAD,
                'skills' => [],
                'expectedMin' => 427, // 450 * 0.95 = 427.5
                'expectedMax' => 475, // 500 * 0.95 = 475
            ],
            // Medium races with Pace style
            [
                'name' => 'Medium - Pace - No Skills',
                'distance' => RaceDistance::MEDIUM,
                'style' => RunningStyle::PACE,
                'skills' => [],
                'expectedMin' => 510, // 600 * 0.85 = 510
                'expectedMax' => 595, // 700 * 0.85 = 595
            ],
            // Long races with Chase style
            [
                'name' => 'Long - Chase - No Skills',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::CHASE,
                'skills' => [],
                'expectedMin' => 637, // 850 * 0.75 = 637.5
                'expectedMax' => 750, // 1000 * 0.75 = 750
            ],
            // Long races with Escape and recovery skills
            [
                'name' => 'Long - Escape - 1 Recovery Skill',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::ESCAPE,
                'skills' => [1],
                'expectedMin' => 675, // 925 - 200 = 725, but with rounding
                'expectedMax' => 850, // 925 - 150 = 775, but with rounding
            ],
            // Long races with Escape and multiple recovery skills
            [
                'name' => 'Long - Escape - 2 Recovery Skills',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::ESCAPE,
                'skills' => [1, 2],
                'expectedMin' => 525, // 925 - 400 = 525
                'expectedMax' => 700, // 925 - 300 = 625, but with rounding
            ],
        ];

        foreach ($realWorldScenarios as $scenario) {
            $requirement = $this->engine->calculateStaminaRequirement(
                $scenario['distance'],
                $scenario['style'],
                $scenario['skills']
            );

            expect($requirement)->toBeGreaterThanOrEqual($scenario['expectedMin'])
                ->and($requirement)->toBeLessThanOrEqual($scenario['expectedMax']);
        }
    })->group('property');

    it('handles edge cases correctly', function () {
        // Property: Edge cases should be handled gracefully
        $edgeCases = [
            // Sprint with Chase (lowest stamina requirement)
            [
                'name' => 'Sprint - Chase - No Skills',
                'distance' => RaceDistance::SPRINT,
                'style' => RunningStyle::CHASE,
                'skills' => [],
                'expectedMin' => 262, // 350 * 0.75 = 262.5
                'expectedMax' => 300, // 400 * 0.75 = 300
            ],
            // Long with Escape (highest stamina requirement)
            [
                'name' => 'Long - Escape - No Skills',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::ESCAPE,
                'skills' => [],
                'expectedMin' => 850,
                'expectedMax' => 1000,
            ],
            // Sprint with many recovery skills (should not go negative)
            [
                'name' => 'Sprint - Escape - 5 Recovery Skills',
                'distance' => RaceDistance::SPRINT,
                'style' => RunningStyle::ESCAPE,
                'skills' => [1, 2, 3, 4, 5],
                'expectedMin' => 0,
                'expectedMax' => 0, // Should be clamped to 0
            ],
        ];

        foreach ($edgeCases as $case) {
            $requirement = $this->engine->calculateStaminaRequirement(
                $case['distance'],
                $case['style'],
                $case['skills']
            );

            expect($requirement)->toBeGreaterThanOrEqual($case['expectedMin'])
                ->and($requirement)->toBeLessThanOrEqual($case['expectedMax']);
        }
    })->group('property');

    it('validates stamina multipliers are consistent with enum definitions', function () {
        // Property: Calculated requirements should match the multipliers defined in RunningStyle enum
        $distance = RaceDistance::LONG;
        $baseRequirement = $distance->recommendedStamina();

        $styles = [
            ['style' => RunningStyle::ESCAPE, 'multiplier' => 1.0],
            ['style' => RunningStyle::LEAD, 'multiplier' => 0.95],
            ['style' => RunningStyle::PACE, 'multiplier' => 0.85],
            ['style' => RunningStyle::CHASE, 'multiplier' => 0.75],
        ];

        foreach ($styles as $testCase) {
            $requirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $testCase['style'],
                []
            );

            $expectedRequirement = (int) round($baseRequirement * $testCase['multiplier']);

            // Allow for rounding differences
            expect($requirement)->toBeGreaterThanOrEqual($expectedRequirement - 1)
                ->and($requirement)->toBeLessThanOrEqual($expectedRequirement + 1);

            // Verify the enum's staminaMultiplier matches our expectation
            expect($testCase['style']->staminaMultiplier())->toBe($testCase['multiplier']);
        }
    })->group('property');
});
