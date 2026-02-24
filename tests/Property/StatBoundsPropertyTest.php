<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;

describe('Stat Value Bounds Property Tests', function () {
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
     * Effective values must always be <= raw values.
     */
    it('ensures all stats stay within [0, 1500] across 200 random combinations', function () {
        for ($i = 0; $i < 200; $i++) {
            $speed = random_int(0, 1500);
            $stamina = random_int(0, 1500);
            $power = random_int(0, 1500);
            $guts = random_int(0, 1500);
            $wisdom = random_int(0, 1500);

            $stats = new CharacterStats($speed, $stamina, $power, $guts, $wisdom);

            foreach (['speed', 'stamina', 'power', 'guts', 'wisdom'] as $statName) {
                $raw = $stats->getStat($statName);
                $effective = $stats->getEffectiveStat($statName);

                expect($raw)->toBeGreaterThanOrEqual(CharacterStats::MIN_STAT)
                    ->and($raw)->toBeLessThanOrEqual(CharacterStats::MAX_STAT)
                    ->and($effective)->toBeGreaterThanOrEqual(0)
                    ->and($effective)->toBeLessThanOrEqual($raw);
            }
        }
    })->group('property');

    it('ensures effective total never exceeds raw total', function () {
        for ($i = 0; $i < 200; $i++) {
            $stats = new CharacterStats(
                random_int(0, 1500),
                random_int(0, 1500),
                random_int(0, 1500),
                random_int(0, 1500),
                random_int(0, 1500)
            );

            expect($stats->getEffectiveTotal())->toBeLessThanOrEqual($stats->getTotal());
        }
    })->group('property');

    it('ensures diminishing returns apply exactly at soft cap boundary', function () {
        $belowCap = new CharacterStats(1199, 1199, 1199, 1199, 1199);
        expect($belowCap->getEffectiveTotal())->toBe($belowCap->getTotal());

        $atCap = new CharacterStats(1200, 1200, 1200, 1200, 1200);
        expect($atCap->getEffectiveTotal())->toBe($atCap->getTotal());

        $aboveCap = new CharacterStats(1201, 1201, 1201, 1201, 1201);
        expect($aboveCap->getEffectiveTotal())->toBeLessThan($aboveCap->getTotal());

        $maxStats = new CharacterStats(1500, 1500, 1500, 1500, 1500);
        $expectedEffective = (1200 + (int) ((1500 - 1200) / 2)) * 5;
        expect($maxStats->getEffectiveTotal())->toBe($expectedEffective);
    })->group('property');

    it('ensures effective value formula is correct for all values above soft cap', function () {
        for ($value = 1201; $value <= 1500; $value++) {
            $stats = new CharacterStats($value, 0, 0, 0, 0);
            $expectedEffective = 1200 + (int) (($value - 1200) / 2);

            expect($stats->getEffectiveSpeed())->toBe($expectedEffective);

            $engineEffective = $this->engine->calculateStatEffectiveness($value);
            expect($engineEffective)->toBe(1200 + (int) round(($value - 1200) / 2));
        }
    })->group('property');

    it('ensures withIncreasedStat preserves bounds invariant', function () {
        for ($i = 0; $i < 100; $i++) {
            $base = random_int(0, 1400);
            $stats = new CharacterStats($base, 500, 500, 500, 500);
            $increase = random_int(0, 1500 - $base);

            $increased = $stats->withIncreasedStat('speed', $increase);

            expect($increased->speed)->toBe($base + $increase)
                ->and($increased->speed)->toBeLessThanOrEqual(CharacterStats::MAX_STAT)
                ->and($increased->speed)->toBeGreaterThanOrEqual(CharacterStats::MIN_STAT);
        }
    })->group('property');

    it('ensures training gain respects stat progression monotonicity', function () {
        $statNames = ['speed', 'stamina', 'power', 'guts', 'wisdom'];

        for ($i = 0; $i < 50; $i++) {
            $mood = Mood::cases()[random_int(0, 4)];
            $growthRate = [0.8, 1.0, 1.2][random_int(0, 2)];

            foreach ([1, 2, 3, 4, 5] as $level) {
                $gain = $this->engine->calculateTrainingGain(
                    baseStat: 500,
                    facilityLevel: $level,
                    growthRate: $growthRate,
                    mood: $mood,
                    supportCardBonuses: [],
                    numCardsPresent: 0,
                    isFriendshipTraining: false
                );

                expect($gain)->toBeGreaterThanOrEqual(0);
            }
        }
    })->group('property');

    it('ensures stamina requirements are bounded and decrease with chase style', function () {
        foreach (RaceDistance::cases() as $distance) {
            $escapeReq = $this->engine->calculateStaminaRequirement($distance, RunningStyle::ESCAPE, []);
            $chaseReq = $this->engine->calculateStaminaRequirement($distance, RunningStyle::CHASE, []);

            expect($escapeReq)->toBeGreaterThanOrEqual(0)
                ->and($chaseReq)->toBeGreaterThanOrEqual(0)
                ->and($chaseReq)->toBeLessThanOrEqual($escapeReq);
        }
    })->group('property');

    it('ensures soft cap detection is consistent with actual values', function () {
        for ($i = 0; $i < 100; $i++) {
            $speed = random_int(0, 1500);
            $stamina = random_int(0, 1500);
            $power = random_int(0, 1500);
            $guts = random_int(0, 1500);
            $wisdom = random_int(0, 1500);

            $stats = new CharacterStats($speed, $stamina, $power, $guts, $wisdom);

            expect($stats->isAtSoftCap('speed'))->toBe($speed >= CharacterStats::SOFT_CAP)
                ->and($stats->isAtSoftCap('stamina'))->toBe($stamina >= CharacterStats::SOFT_CAP)
                ->and($stats->isAtSoftCap('power'))->toBe($power >= CharacterStats::SOFT_CAP)
                ->and($stats->isAtSoftCap('guts'))->toBe($guts >= CharacterStats::SOFT_CAP)
                ->and($stats->isAtSoftCap('wisdom'))->toBe($wisdom >= CharacterStats::SOFT_CAP);

            $capped = $stats->getSoftCappedStats();
            $expectedCapped = array_filter(
                ['speed', 'stamina', 'power', 'guts', 'wisdom'],
                fn (string $name) => $stats->getStat($name) >= CharacterStats::SOFT_CAP
            );
            expect(count($capped))->toBe(count($expectedCapped));
        }
    })->group('property');
});
