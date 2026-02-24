<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;

describe('Stat Calculation Property Tests', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    /**
     * Property 4: Stat Value Bounds
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 4: Stat Value Bounds
     * Validates: Requirements FR-02.2
     *
     * All stat values must remain within valid bounds [0, 1500].
     * The soft cap is at 1200 with diminishing returns above it.
     */
    it('ensures CharacterStats enforces value bounds across randomized inputs', function () {
        for ($i = 0; $i < 100; $i++) {
            $speed = random_int(0, 1500);
            $stamina = random_int(0, 1500);
            $power = random_int(0, 1500);
            $guts = random_int(0, 1500);
            $wisdom = random_int(0, 1500);

            $stats = new CharacterStats($speed, $stamina, $power, $guts, $wisdom);

            expect($stats->speed)->toBeGreaterThanOrEqual(0)
                ->and($stats->speed)->toBeLessThanOrEqual(1500)
                ->and($stats->stamina)->toBeGreaterThanOrEqual(0)
                ->and($stats->stamina)->toBeLessThanOrEqual(1500)
                ->and($stats->power)->toBeGreaterThanOrEqual(0)
                ->and($stats->power)->toBeLessThanOrEqual(1500)
                ->and($stats->guts)->toBeGreaterThanOrEqual(0)
                ->and($stats->guts)->toBeLessThanOrEqual(1500)
                ->and($stats->wisdom)->toBeGreaterThanOrEqual(0)
                ->and($stats->wisdom)->toBeLessThanOrEqual(1500);
        }
    })->group('property');

    it('rejects stats below minimum bound', function () {
        for ($i = 0; $i < 100; $i++) {
            $negativeValue = random_int(-1000, -1);

            expect(fn () => new CharacterStats($negativeValue, 500, 500, 500, 500))
                ->toThrow(InvalidArgumentException::class);
        }
    })->group('property');

    it('rejects stats above maximum bound', function () {
        for ($i = 0; $i < 100; $i++) {
            $tooHigh = random_int(1501, 9999);

            expect(fn () => new CharacterStats($tooHigh, 500, 500, 500, 500))
                ->toThrow(InvalidArgumentException::class);
        }
    })->group('property');

    it('applies soft cap diminishing returns above 1200', function () {
        for ($i = 0; $i < 100; $i++) {
            $statValue = random_int(1201, 1500);

            $effective = $this->engine->calculateStatEffectiveness($statValue);

            $excess = $statValue - 1200;
            $expectedEffective = 1200 + (int) round($excess / 2);

            expect($effective)->toBe($expectedEffective)
                ->and($effective)->toBeLessThanOrEqual($statValue)
                ->and($effective)->toBeGreaterThanOrEqual(1200);
        }
    })->group('property');

    it('returns exact value for stats at or below soft cap', function () {
        for ($i = 0; $i < 100; $i++) {
            $statValue = random_int(0, 1200);

            $effective = $this->engine->calculateStatEffectiveness($statValue);

            expect($effective)->toBe($statValue);
        }
    })->group('property');

    /**
     * Property: Training Gain Non-Negativity
     *
     * Training gains should always be non-negative regardless of input parameters.
     */
    it('produces non-negative training gains', function () {
        $moods = [Mood::GREAT, Mood::GOOD, Mood::NORMAL, Mood::BAD, Mood::VERY_BAD];

        for ($i = 0; $i < 100; $i++) {
            $baseStat = random_int(0, 1200);
            $facilityLevel = random_int(1, 5);
            $growthRate = random_int(50, 150) / 100;
            $mood = $moods[array_rand($moods)];
            $numCards = random_int(0, 6);
            $isFriendship = (bool) random_int(0, 1);

            $gain = $this->engine->calculateTrainingGain(
                $baseStat,
                $facilityLevel,
                $growthRate,
                $mood,
                [],
                $numCards,
                $isFriendship
            );

            expect($gain)->toBeGreaterThanOrEqual(0);
        }
    })->group('property');

    /**
     * Property: Training Gain Determinism
     *
     * The same inputs must always produce the same output.
     */
    it('is deterministic for training gain calculations', function () {
        for ($i = 0; $i < 100; $i++) {
            $baseStat = random_int(0, 1200);
            $facilityLevel = random_int(1, 5);
            $growthRate = random_int(50, 150) / 100;
            $mood = Mood::GOOD;

            $result1 = $this->engine->calculateTrainingGain(
                $baseStat,
                $facilityLevel,
                $growthRate,
                $mood,
                [],
                3,
                false
            );

            $result2 = $this->engine->calculateTrainingGain(
                $baseStat,
                $facilityLevel,
                $growthRate,
                $mood,
                [],
                3,
                false
            );

            expect($result1)->toBe($result2);
        }
    })->group('property');

    /**
     * Property: Higher Facility Level Produces Equal or Greater Gains
     *
     * Training at a higher facility level should produce >= the gain of a lower level.
     */
    it('facility level monotonically increases training gains', function () {
        for ($i = 0; $i < 100; $i++) {
            $baseStat = random_int(0, 1200);
            $growthRate = random_int(80, 120) / 100;
            $mood = Mood::NORMAL;

            $previousGain = 0;
            for ($level = 1; $level <= 5; $level++) {
                $gain = $this->engine->calculateTrainingGain(
                    $baseStat,
                    $level,
                    $growthRate,
                    $mood,
                    [],
                    2,
                    false
                );

                expect($gain)->toBeGreaterThanOrEqual($previousGain);
                $previousGain = $gain;
            }
        }
    })->group('property');

    /**
     * Property: Friendship Training Bonus
     *
     * Friendship training should produce >= non-friendship training.
     */
    it('friendship training produces equal or greater gains', function () {
        for ($i = 0; $i < 100; $i++) {
            $baseStat = random_int(0, 1200);
            $facilityLevel = random_int(1, 5);
            $growthRate = random_int(80, 120) / 100;
            $mood = Mood::GOOD;

            $normalGain = $this->engine->calculateTrainingGain(
                $baseStat,
                $facilityLevel,
                $growthRate,
                $mood,
                [],
                3,
                false
            );

            $friendshipGain = $this->engine->calculateTrainingGain(
                $baseStat,
                $facilityLevel,
                $growthRate,
                $mood,
                [],
                3,
                true
            );

            expect($friendshipGain)->toBeGreaterThanOrEqual($normalGain);
        }
    })->group('property');

    /**
     * Property: Skill Cost Non-Negativity
     *
     * Skill costs should always be >= 0 regardless of discount factors.
     */
    it('skill costs are always non-negative', function () {
        for ($i = 0; $i < 100; $i++) {
            $baseCost = random_int(1, 500);
            $hintLevel = random_int(0, 5);
            $hasFastLearner = (bool) random_int(0, 1);

            $cost = $this->engine->calculateSkillCost($baseCost, $hintLevel, $hasFastLearner);

            expect($cost)->toBeGreaterThanOrEqual(0);
        }
    })->group('property');

    /**
     * Property: Failure Rate Bounds
     *
     * Failure rates must be between 0.0 and 1.0 (probability).
     */
    it('failure rates are within probability bounds', function () {
        for ($i = 0; $i < 100; $i++) {
            $energy = random_int(0, 100);
            $numCards = random_int(0, 6);

            $failureRate = $this->engine->calculateFailureRate($energy, $numCards, []);

            expect($failureRate)->toBeGreaterThanOrEqual(0.0)
                ->and($failureRate)->toBeLessThanOrEqual(1.0);
        }
    })->group('property');

    /**
     * Property: Total Stats Symmetry
     *
     * CharacterStats total should equal sum of individual stats.
     */
    it('total stats equals sum of individual stats', function () {
        for ($i = 0; $i < 100; $i++) {
            $speed = random_int(0, 1500);
            $stamina = random_int(0, 1500);
            $power = random_int(0, 1500);
            $guts = random_int(0, 1500);
            $wisdom = random_int(0, 1500);

            $stats = new CharacterStats($speed, $stamina, $power, $guts, $wisdom);

            expect($stats->getTotal())->toBe($speed + $stamina + $power + $guts + $wisdom);
        }
    })->group('property');
});
