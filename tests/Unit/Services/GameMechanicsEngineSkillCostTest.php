<?php

declare(strict_types=1);

use App\Services\GameMechanicsEngine;

describe('GameMechanicsEngine::calculateSkillCost', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    it('calculates base cost with no hint and no Fast Learner', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 0,
            hasFastLearner: false
        );

        expect($cost)->toBe(100);
    });

    it('applies 10% discount for hint level 1', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 1,
            hasFastLearner: false
        );

        expect($cost)->toBe(90);
    });

    it('applies 20% discount for hint level 2', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 2,
            hasFastLearner: false
        );

        expect($cost)->toBe(80);
    });

    it('applies 30% discount for hint level 3', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 3,
            hasFastLearner: false
        );

        expect($cost)->toBe(70);
    });

    it('applies 35% discount for hint level 4', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 4,
            hasFastLearner: false
        );

        expect($cost)->toBe(65);
    });

    it('applies 40% discount for hint level 5 (max)', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 5,
            hasFastLearner: false
        );

        expect($cost)->toBe(60);
    });

    it('applies Fast Learner discount on top of hint discount', function () {
        // Level 3 hint: 30% discount = 70
        // Fast Learner: additional 10% discount = 70 * 0.9 = 63
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 3,
            hasFastLearner: true
        );

        expect($cost)->toBe(63);
    });

    it('applies Fast Learner discount with no hint', function () {
        // No hint: 0% discount = 100
        // Fast Learner: 10% discount = 100 * 0.9 = 90
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 0,
            hasFastLearner: true
        );

        expect($cost)->toBe(90);
    });

    it('applies maximum discount with level 5 hint and Fast Learner', function () {
        // Level 5 hint: 40% discount = 60
        // Fast Learner: additional 10% discount = 60 * 0.9 = 54
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 5,
            hasFastLearner: true
        );

        expect($cost)->toBe(54);
    });

    it('handles realistic skill costs', function () {
        // Swinging Maestro: 180 SP base, Level 3 hint
        $cost = $this->engine->calculateSkillCost(
            baseCost: 180,
            hintLevel: 3,
            hasFastLearner: false
        );

        expect($cost)->toBe(126); // 180 * 0.7 = 126
    });

    it('handles realistic skill costs with Fast Learner', function () {
        // Swinging Maestro: 180 SP base, Level 3 hint, Fast Learner
        $cost = $this->engine->calculateSkillCost(
            baseCost: 180,
            hintLevel: 3,
            hasFastLearner: true
        );

        expect($cost)->toBe(113); // 180 * 0.7 * 0.9 = 113.4 → 113
    });

    it('rounds fractional costs correctly', function () {
        // Test rounding: 100 * 0.65 * 0.9 = 58.5 → 59 (rounds up)
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 4,
            hasFastLearner: true
        );

        expect($cost)->toBe(59);
    });

    it('clamps hint level above 5 to 5', function () {
        // Hint level 10 should be treated as level 5 (40% discount)
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: 10,
            hasFastLearner: false
        );

        expect($cost)->toBe(60);
    });

    it('clamps negative hint level to 0', function () {
        // Hint level -1 should be treated as level 0 (0% discount)
        $cost = $this->engine->calculateSkillCost(
            baseCost: 100,
            hintLevel: -1,
            hasFastLearner: false
        );

        expect($cost)->toBe(100);
    });

    it('handles zero base cost', function () {
        $cost = $this->engine->calculateSkillCost(
            baseCost: 0,
            hintLevel: 3,
            hasFastLearner: true
        );

        expect($cost)->toBe(0);
    });

    it('handles large base costs', function () {
        // Test with a very large base cost
        $cost = $this->engine->calculateSkillCost(
            baseCost: 1000,
            hintLevel: 5,
            hasFastLearner: true
        );

        expect($cost)->toBe(540); // 1000 * 0.6 * 0.9 = 540
    });
});
