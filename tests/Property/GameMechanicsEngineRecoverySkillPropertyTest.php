<?php

declare(strict_types=1);

use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Services\GameMechanicsEngine;

describe('GameMechanicsEngine::calculateStaminaRequirement - Recovery Skill Property Tests', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    /**
     * Property 8: Stamina Recovery Skill Adjustment
     *
     * **Validates: Requirements 3.3**
     *
     * Stamina requirements must be reduced by 150-200 per gold recovery skill equipped.
     * This property verifies that recovery skills correctly reduce stamina requirements
     * across all distance and style combinations.
     */
    it('reduces stamina requirements by 150-200 per recovery skill', function () {
        $distances = RaceDistance::all();
        $styles = RunningStyle::all();

        foreach ($distances as $distance) {
            foreach ($styles as $style) {
                // Calculate base requirement (no recovery skills)
                $baseRequirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    []
                );

                // Calculate requirement with one recovery skill
                $withOneSkill = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    [1] // One recovery skill
                );

                // Calculate the reduction
                $reduction = $baseRequirement - $withOneSkill;

                // Verify reduction is within 150-200 range (or base requirement if it would go negative)
                if ($baseRequirement > 0) {
                    $expectedReduction = min($reduction, $baseRequirement);
                    expect($expectedReduction)->toBeGreaterThanOrEqual(min(150, $baseRequirement))
                        ->and($expectedReduction)->toBeLessThanOrEqual(min(200, $baseRequirement));
                }
            }
        }
    })->group('property');

    it('applies cumulative reductions for multiple recovery skills', function () {
        $distance = RaceDistance::LONG;
        $style = RunningStyle::ESCAPE;

        $baseRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            []
        );

        // Test with 1, 2, and 3 recovery skills
        $skillCounts = [1, 2, 3];
        $previousRequirement = $baseRequirement;

        foreach ($skillCounts as $count) {
            $skills = range(1, $count);
            $requirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                $skills
            );

            // Each additional skill should reduce the requirement further
            expect($requirement)->toBeLessThanOrEqual($previousRequirement);

            // Calculate total reduction
            $totalReduction = $baseRequirement - $requirement;

            // Verify total reduction is within expected range (150-200 per skill)
            $minExpectedReduction = $count * 150;
            $maxExpectedReduction = $count * 200;

            // Account for the case where requirement hits zero
            if ($requirement > 0) {
                expect($totalReduction)->toBeGreaterThanOrEqual($minExpectedReduction)
                    ->and($totalReduction)->toBeLessThanOrEqual($maxExpectedReduction);
            } else {
                // If requirement is zero, reduction should be at least the minimum expected
                expect($totalReduction)->toBeGreaterThanOrEqual($minExpectedReduction);
            }

            $previousRequirement = $requirement;
        }
    })->group('property');

    it('maintains consistent reduction per skill across distances', function () {
        $distances = RaceDistance::all();
        $style = RunningStyle::ESCAPE;

        foreach ($distances as $distance) {
            $baseRequirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                []
            );

            $withOneSkill = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                [1]
            );

            $reduction = $baseRequirement - $withOneSkill;

            // Verify reduction is consistent (150-200) regardless of distance
            // Unless the base requirement is too low
            if ($baseRequirement >= 150) {
                expect($reduction)->toBeGreaterThanOrEqual(150)
                    ->and($reduction)->toBeLessThanOrEqual(200);
            } else {
                // For very low base requirements, reduction should not exceed base
                expect($reduction)->toBeLessThanOrEqual($baseRequirement);
            }
        }
    })->group('property');

    it('maintains consistent reduction per skill across running styles', function () {
        $distance = RaceDistance::LONG;
        $styles = RunningStyle::all();

        foreach ($styles as $style) {
            $baseRequirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                []
            );

            $withOneSkill = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                [1]
            );

            $reduction = $baseRequirement - $withOneSkill;

            // Verify reduction is consistent (150-200) regardless of running style
            expect($reduction)->toBeGreaterThanOrEqual(150)
                ->and($reduction)->toBeLessThanOrEqual(200);
        }
    })->group('property');

    it('applies reductions independently of style multipliers', function () {
        // Property: Recovery skill reduction should be applied after style multipliers
        $distance = RaceDistance::LONG;
        $recoverySkills = [1];

        // Get base requirements for different styles
        $escapeBase = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::ESCAPE,
            []
        );

        $chaseBase = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::CHASE,
            []
        );

        // Get requirements with recovery skills
        $escapeWithSkill = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::ESCAPE,
            $recoverySkills
        );

        $chaseWithSkill = $this->engine->calculateStaminaRequirement(
            $distance,
            RunningStyle::CHASE,
            $recoverySkills
        );

        // Calculate reductions
        $escapeReduction = $escapeBase - $escapeWithSkill;
        $chaseReduction = $chaseBase - $chaseWithSkill;

        // Both should have similar reductions (150-200 range)
        expect($escapeReduction)->toBeGreaterThanOrEqual(150)
            ->and($escapeReduction)->toBeLessThanOrEqual(200)
            ->and($chaseReduction)->toBeGreaterThanOrEqual(150)
            ->and($chaseReduction)->toBeLessThanOrEqual(200);
    })->group('property');

    it('handles edge case of many recovery skills without going negative', function () {
        // Property: Even with many recovery skills, stamina requirement should not be negative
        $distances = RaceDistance::all();
        $styles = RunningStyle::all();
        $manySkills = range(1, 10); // 10 recovery skills

        foreach ($distances as $distance) {
            foreach ($styles as $style) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    $manySkills
                );

                // Should never be negative
                expect($requirement)->toBeGreaterThanOrEqual(0);
            }
        }
    })->group('property');

    it('validates recovery skill reduction with real-world scenarios', function () {
        // Property: Real-world scenarios should match expected reductions
        $scenarios = [
            [
                'name' => 'Long - Escape - 1 Recovery Skill',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::ESCAPE,
                'skills' => [1],
                'expectedReductionMin' => 150,
                'expectedReductionMax' => 200,
            ],
            [
                'name' => 'Long - Escape - 2 Recovery Skills',
                'distance' => RaceDistance::LONG,
                'style' => RunningStyle::ESCAPE,
                'skills' => [1, 2],
                'expectedReductionMin' => 300,
                'expectedReductionMax' => 400,
            ],
            [
                'name' => 'Medium - Lead - 1 Recovery Skill',
                'distance' => RaceDistance::MEDIUM,
                'style' => RunningStyle::LEAD,
                'skills' => [1],
                'expectedReductionMin' => 150,
                'expectedReductionMax' => 200,
            ],
            [
                'name' => 'Mile - Pace - 1 Recovery Skill',
                'distance' => RaceDistance::MILE,
                'style' => RunningStyle::PACE,
                'skills' => [1],
                'expectedReductionMin' => 150,
                'expectedReductionMax' => 200,
            ],
            [
                'name' => 'Sprint - Chase - 1 Recovery Skill',
                'distance' => RaceDistance::SPRINT,
                'style' => RunningStyle::CHASE,
                'skills' => [1],
                'expectedReductionMin' => 150,
                'expectedReductionMax' => 200,
            ],
        ];

        foreach ($scenarios as $scenario) {
            $baseRequirement = $this->engine->calculateStaminaRequirement(
                $scenario['distance'],
                $scenario['style'],
                []
            );

            $withSkills = $this->engine->calculateStaminaRequirement(
                $scenario['distance'],
                $scenario['style'],
                $scenario['skills']
            );

            $actualReduction = $baseRequirement - $withSkills;

            // Verify reduction is within expected range
            // Account for cases where base requirement is low
            if ($baseRequirement >= $scenario['expectedReductionMin']) {
                expect($actualReduction)->toBeGreaterThanOrEqual($scenario['expectedReductionMin'])
                    ->and($actualReduction)->toBeLessThanOrEqual($scenario['expectedReductionMax']);
            } else {
                // If base requirement is lower than minimum expected reduction,
                // actual reduction should equal base requirement (clamped to 0)
                expect($actualReduction)->toBeLessThanOrEqual($baseRequirement);
            }
        }
    })->group('property');

    it('produces deterministic results with recovery skills', function () {
        // Property: Same inputs should always produce same outputs
        $testCases = [
            ['distance' => RaceDistance::SPRINT, 'style' => RunningStyle::ESCAPE, 'skills' => [1]],
            ['distance' => RaceDistance::MILE, 'style' => RunningStyle::LEAD, 'skills' => [1, 2]],
            ['distance' => RaceDistance::MEDIUM, 'style' => RunningStyle::PACE, 'skills' => [1, 2, 3]],
            ['distance' => RaceDistance::LONG, 'style' => RunningStyle::CHASE, 'skills' => [1]],
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

    it('validates reduction amount is exactly 175 per skill', function () {
        // Property: The implementation uses 175 as the midpoint (150-200 range)
        // This test verifies the exact reduction amount
        $distance = RaceDistance::LONG;
        $style = RunningStyle::ESCAPE;

        $baseRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            []
        );

        // Test with 1, 2, and 3 skills
        for ($skillCount = 1; $skillCount <= 3; $skillCount++) {
            $skills = range(1, $skillCount);
            $requirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                $skills
            );

            $actualReduction = $baseRequirement - $requirement;
            $expectedReduction = $skillCount * 175;

            // Verify the reduction matches the expected 175 per skill
            expect($actualReduction)->toBe($expectedReduction);
        }
    })->group('property');

    it('validates recovery skills work with all distance and style combinations', function () {
        // Property: Recovery skills should work consistently across all valid combinations
        $distances = RaceDistance::all();
        $styles = RunningStyle::all();
        $skillCounts = [0, 1, 2, 3];

        foreach ($distances as $distance) {
            foreach ($styles as $style) {
                $previousRequirement = null;

                foreach ($skillCounts as $count) {
                    $skills = $count > 0 ? range(1, $count) : [];
                    $requirement = $this->engine->calculateStaminaRequirement(
                        $distance,
                        $style,
                        $skills
                    );

                    // Each additional skill should reduce or maintain the requirement
                    if ($previousRequirement !== null) {
                        expect($requirement)->toBeLessThanOrEqual($previousRequirement);
                    }

                    // Requirement should never be negative
                    expect($requirement)->toBeGreaterThanOrEqual(0);

                    // Requirement should be an integer
                    expect($requirement)->toBeInt();

                    $previousRequirement = $requirement;
                }
            }
        }
    })->group('property');

    it('validates skill IDs do not affect reduction amount', function () {
        // Property: The reduction should be based on count, not specific skill IDs
        $distance = RaceDistance::LONG;
        $style = RunningStyle::ESCAPE;

        $baseRequirement = $this->engine->calculateStaminaRequirement(
            $distance,
            $style,
            []
        );

        // Test with different skill ID combinations
        $skillCombinations = [
            [1],
            [5],
            [100],
            [1, 2],
            [5, 10],
            [100, 200],
        ];

        foreach ($skillCombinations as $skills) {
            $requirement = $this->engine->calculateStaminaRequirement(
                $distance,
                $style,
                $skills
            );

            $reduction = $baseRequirement - $requirement;
            $expectedReduction = count($skills) * 175;

            // Reduction should be based on count, not IDs
            expect($reduction)->toBe($expectedReduction);
        }
    })->group('property');
});
