<?php

declare(strict_types=1);

use App\Services\GameMechanicsEngine;

describe('GameMechanicsEngine::calculateSkillCost - Property Tests', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    /**
     * Property 5: Skill Hint Level Discount Accuracy
     *
     * **Validates: Requirements 3.2**
     *
     * Skill cost calculations must correctly apply hint level discounts
     * (10%/20%/30%/35%/40%) and Fast Learner bonus.
     */
    it('applies correct hint level discounts for all valid hint levels', function () {
        $baseCost = 100;
        $expectedDiscounts = [
            0 => 0.00,  // 0% discount
            1 => 0.10,  // 10% discount
            2 => 0.20,  // 20% discount
            3 => 0.30,  // 30% discount
            4 => 0.35,  // 35% discount
            5 => 0.40,  // 40% discount (max)
        ];

        foreach ($expectedDiscounts as $hintLevel => $discount) {
            $expectedCost = (int) round($baseCost * (1.0 - $discount));
            $actualCost = $this->engine->calculateSkillCost($baseCost, $hintLevel, false);

            expect($actualCost)->toBe($expectedCost)
                ->and($actualCost)->toBeLessThanOrEqual($baseCost)
                ->and($actualCost)->toBeGreaterThanOrEqual(0);
        }
    })->group('property');

    it('never increases cost above base cost', function () {
        // Property: For any base cost, hint level, and Fast Learner status,
        // the final cost should never exceed the base cost
        $testCases = [
            ['baseCost' => 50, 'hintLevel' => 0, 'fastLearner' => false],
            ['baseCost' => 100, 'hintLevel' => 1, 'fastLearner' => false],
            ['baseCost' => 150, 'hintLevel' => 2, 'fastLearner' => true],
            ['baseCost' => 200, 'hintLevel' => 3, 'fastLearner' => false],
            ['baseCost' => 250, 'hintLevel' => 4, 'fastLearner' => true],
            ['baseCost' => 300, 'hintLevel' => 5, 'fastLearner' => false],
            ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => true],
            ['baseCost' => 500, 'hintLevel' => 5, 'fastLearner' => true],
        ];

        foreach ($testCases as $case) {
            $cost = $this->engine->calculateSkillCost(
                $case['baseCost'],
                $case['hintLevel'],
                $case['fastLearner']
            );

            expect($cost)->toBeLessThanOrEqual($case['baseCost']);
        }
    })->group('property');

    it('applies monotonically increasing discounts with hint levels', function () {
        // Property: Higher hint levels should always result in equal or lower costs
        $baseCost = 100;
        $previousCost = $baseCost;

        for ($hintLevel = 0; $hintLevel <= 5; $hintLevel++) {
            $cost = $this->engine->calculateSkillCost($baseCost, $hintLevel, false);

            expect($cost)->toBeLessThanOrEqual($previousCost);
            $previousCost = $cost;
        }
    })->group('property');

    it('applies Fast Learner discount consistently across all hint levels', function () {
        // Property: Fast Learner should always reduce cost compared to without it
        $baseCost = 100;

        for ($hintLevel = 0; $hintLevel <= 5; $hintLevel++) {
            $costWithoutFastLearner = $this->engine->calculateSkillCost($baseCost, $hintLevel, false);
            $costWithFastLearner = $this->engine->calculateSkillCost($baseCost, $hintLevel, true);

            expect($costWithFastLearner)->toBeLessThan($costWithoutFastLearner);
        }
    })->group('property');

    it('scales proportionally with base cost', function () {
        // Property: Doubling the base cost should approximately double the final cost
        $hintLevel = 3;
        $hasFastLearner = false;

        $cost100 = $this->engine->calculateSkillCost(100, $hintLevel, $hasFastLearner);
        $cost200 = $this->engine->calculateSkillCost(200, $hintLevel, $hasFastLearner);

        // Due to rounding, we allow a small tolerance (±1)
        expect($cost200)->toBeGreaterThanOrEqual($cost100 * 2 - 1)
            ->and($cost200)->toBeLessThanOrEqual($cost100 * 2 + 1);
    })->group('property');

    it('handles edge cases correctly', function () {
        // Property: Edge cases should be handled gracefully
        $edgeCases = [
            // Zero base cost
            ['baseCost' => 0, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 0],
            ['baseCost' => 0, 'hintLevel' => 5, 'fastLearner' => true, 'expected' => 0],

            // Negative hint level (should clamp to 0)
            ['baseCost' => 100, 'hintLevel' => -1, 'fastLearner' => false, 'expected' => 100],
            ['baseCost' => 100, 'hintLevel' => -5, 'fastLearner' => false, 'expected' => 100],

            // Hint level above max (should clamp to 5)
            ['baseCost' => 100, 'hintLevel' => 6, 'fastLearner' => false, 'expected' => 60],
            ['baseCost' => 100, 'hintLevel' => 10, 'fastLearner' => false, 'expected' => 60],
            ['baseCost' => 100, 'hintLevel' => 100, 'fastLearner' => false, 'expected' => 60],

            // Very large base cost
            ['baseCost' => 10000, 'hintLevel' => 5, 'fastLearner' => true, 'expected' => 5400],
        ];

        foreach ($edgeCases as $case) {
            $cost = $this->engine->calculateSkillCost(
                $case['baseCost'],
                $case['hintLevel'],
                $case['fastLearner']
            );

            expect($cost)->toBe($case['expected']);
        }
    })->group('property');

    it('produces deterministic results', function () {
        // Property: Same inputs should always produce same outputs
        $testCases = [
            ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => false],
            ['baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => true],
            ['baseCost' => 100, 'hintLevel' => 5, 'fastLearner' => true],
        ];

        foreach ($testCases as $case) {
            $cost1 = $this->engine->calculateSkillCost(
                $case['baseCost'],
                $case['hintLevel'],
                $case['fastLearner']
            );

            $cost2 = $this->engine->calculateSkillCost(
                $case['baseCost'],
                $case['hintLevel'],
                $case['fastLearner']
            );

            expect($cost1)->toBe($cost2);
        }
    })->group('property');

    it('returns integer values only', function () {
        // Property: All costs should be integers (no fractional SP)
        $testCases = [
            ['baseCost' => 33, 'hintLevel' => 1, 'fastLearner' => false],
            ['baseCost' => 77, 'hintLevel' => 2, 'fastLearner' => true],
            ['baseCost' => 123, 'hintLevel' => 3, 'fastLearner' => false],
            ['baseCost' => 199, 'hintLevel' => 4, 'fastLearner' => true],
            ['baseCost' => 251, 'hintLevel' => 5, 'fastLearner' => false],
        ];

        foreach ($testCases as $case) {
            $cost = $this->engine->calculateSkillCost(
                $case['baseCost'],
                $case['hintLevel'],
                $case['fastLearner']
            );

            expect($cost)->toBeInt();
        }
    })->group('property');

    it('applies maximum 46% total discount with level 5 hint and Fast Learner', function () {
        // Property: Maximum discount is 40% (hint level 5) + 10% (Fast Learner on remaining)
        // = 40% + (60% * 10%) = 40% + 6% = 46% total discount
        // So minimum cost is 54% of base cost
        $baseCost = 100;
        $cost = $this->engine->calculateSkillCost($baseCost, 5, true);

        // 100 * 0.6 * 0.9 = 54
        expect($cost)->toBe(54);

        // Verify this is the maximum discount
        $minPossibleCost = (int) round($baseCost * 0.54);
        expect($cost)->toBe($minPossibleCost);
    })->group('property');

    it('validates real-world skill cost scenarios', function () {
        // Property: Real-world skill costs should match expected values
        $realWorldScenarios = [
            // Common gold skill: Swinging Maestro (180 SP)
            ['name' => 'Swinging Maestro L3', 'baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => false, 'expected' => 126],
            ['name' => 'Swinging Maestro L3 + FL', 'baseCost' => 180, 'hintLevel' => 3, 'fastLearner' => true, 'expected' => 113],
            ['name' => 'Swinging Maestro L5', 'baseCost' => 180, 'hintLevel' => 5, 'fastLearner' => false, 'expected' => 108],
            ['name' => 'Swinging Maestro L5 + FL', 'baseCost' => 180, 'hintLevel' => 5, 'fastLearner' => true, 'expected' => 97],

            // Common rare skill: Lane Legerdemain (120 SP)
            ['name' => 'Lane Legerdemain L2', 'baseCost' => 120, 'hintLevel' => 2, 'fastLearner' => false, 'expected' => 96],
            ['name' => 'Lane Legerdemain L2 + FL', 'baseCost' => 120, 'hintLevel' => 2, 'fastLearner' => true, 'expected' => 86],

            // Common normal skill (80 SP)
            ['name' => 'Normal Skill L1', 'baseCost' => 80, 'hintLevel' => 1, 'fastLearner' => false, 'expected' => 72],
            ['name' => 'Normal Skill L1 + FL', 'baseCost' => 80, 'hintLevel' => 1, 'fastLearner' => true, 'expected' => 65],
        ];

        foreach ($realWorldScenarios as $scenario) {
            $cost = $this->engine->calculateSkillCost(
                $scenario['baseCost'],
                $scenario['hintLevel'],
                $scenario['fastLearner']
            );

            expect($cost)->toBe($scenario['expected'], "Failed for: {$scenario['name']}");
        }
    })->group('property');
});
