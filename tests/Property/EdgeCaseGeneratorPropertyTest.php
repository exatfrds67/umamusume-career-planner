<?php

declare(strict_types=1);

use App\Enums\Mood;
use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;

describe('Edge Case Generator Property Tests', function () {
    beforeEach(function () {
        $this->engine = new GameMechanicsEngine;
    });

    /**
     * Boundary Value Generators - CharacterStats
     *
     * Feature: umamusume-career-planner-main-v2.4.0
     * Validates: Requirements FR-05.7
     *
     * Tests that CharacterStats correctly handles boundary values at
     * MIN_STAT (0), SOFT_CAP (1200), and MAX_STAT (1500).
     */
    it('handles CharacterStats at all boundary values', function () {
        $boundaries = [0, 1, 599, 600, 1199, 1200, 1201, 1499, 1500];

        foreach ($boundaries as $value) {
            $stats = new CharacterStats($value, $value, $value, $value, $value);

            expect($stats->speed)->toBe($value)
                ->and($stats->stamina)->toBe($value)
                ->and($stats->power)->toBe($value)
                ->and($stats->guts)->toBe($value)
                ->and($stats->wisdom)->toBe($value)
                ->and($stats->getTotal())->toBe($value * 5);

            if ($value <= CharacterStats::SOFT_CAP) {
                expect($stats->getEffectiveTotal())->toBe($value * 5);
            } else {
                $effective = CharacterStats::SOFT_CAP + (int) (($value - CharacterStats::SOFT_CAP) / 2);
                expect($stats->getEffectiveTotal())->toBe($effective * 5);
            }
        }
    })->group('property');

    it('rejects stat values below minimum boundary', function () {
        $invalidValues = [-1, -100, -1000, PHP_INT_MIN];

        foreach ($invalidValues as $value) {
            expect(fn () => new CharacterStats($value, 500, 500, 500, 500))
                ->toThrow(InvalidArgumentException::class);
        }
    })->group('property');

    it('rejects stat values above maximum boundary', function () {
        $invalidValues = [1501, 2000, 5000, PHP_INT_MAX];

        foreach ($invalidValues as $value) {
            expect(fn () => new CharacterStats($value, 500, 500, 500, 500))
                ->toThrow(InvalidArgumentException::class);
        }
    })->group('property');

    /**
     * Boundary Value Generators - GameMechanicsEngine
     *
     * Tests engine methods with boundary and edge-case inputs.
     */
    it('handles training gain at boundary facility levels', function () {
        $facilityLevels = [1, 2, 3, 4, 5];
        $previousGain = 0;

        foreach ($facilityLevels as $level) {
            $gain = $this->engine->calculateTrainingGain(
                baseStat: 500,
                facilityLevel: $level,
                growthRate: 1.0,
                mood: Mood::NORMAL,
                supportCardBonuses: [],
                numCardsPresent: 0,
                isFriendshipTraining: false
            );

            expect($gain)->toBeGreaterThanOrEqual(0)
                ->and($gain)->toBeGreaterThanOrEqual($previousGain);

            $previousGain = $gain;
        }
    })->group('property');

    it('handles skill cost with boundary hint levels', function () {
        $hintLevels = [-1, 0, 1, 2, 3, 4, 5, 6, 100];

        foreach ($hintLevels as $hintLevel) {
            $cost = $this->engine->calculateSkillCost(
                baseCost: 200,
                hintLevel: $hintLevel,
                hasFastLearner: false
            );

            expect($cost)->toBeGreaterThanOrEqual(0)
                ->and($cost)->toBeLessThanOrEqual(200);
        }
    })->group('property');

    it('handles failure rate at energy boundaries', function () {
        $energyLevels = [-10, 0, 1, 29, 30, 49, 50, 69, 70, 99, 100, 150];

        foreach ($energyLevels as $energy) {
            $rate = $this->engine->calculateFailureRate(
                energy: $energy,
                numSupportCards: 0,
                conditions: []
            );

            expect($rate)->toBeGreaterThanOrEqual(0.0)
                ->and($rate)->toBeLessThanOrEqual(1.0);
        }
    })->group('property');

    it('handles facility level calculation at use-count boundaries', function () {
        $useCounts = [0, 1, 3, 4, 7, 8, 11, 12, 15, 16, 17, 100, 1000];

        foreach ($useCounts as $useCount) {
            $level = $this->engine->calculateFacilityLevel($useCount);

            expect($level)->toBeGreaterThanOrEqual(1)
                ->and($level)->toBeLessThanOrEqual(5);
        }

        expect($this->engine->calculateFacilityLevel(0))->toBe(1)
            ->and($this->engine->calculateFacilityLevel(3))->toBe(1)
            ->and($this->engine->calculateFacilityLevel(4))->toBe(2)
            ->and($this->engine->calculateFacilityLevel(7))->toBe(2)
            ->and($this->engine->calculateFacilityLevel(8))->toBe(3)
            ->and($this->engine->calculateFacilityLevel(12))->toBe(4)
            ->and($this->engine->calculateFacilityLevel(16))->toBe(5)
            ->and($this->engine->calculateFacilityLevel(1000))->toBe(5);
    })->group('property');

    it('handles multi-training bonus at card-count boundaries', function () {
        $cardCounts = [0, 1, 2, 3, 4, 5, 6, 7, 100];

        foreach ($cardCounts as $numCards) {
            $bonus = $this->engine->calculateMultiTrainingBonus($numCards);

            expect($bonus)->toBeGreaterThanOrEqual(0.0)
                ->and($bonus)->toBeLessThanOrEqual(0.30);
        }

        expect($this->engine->calculateMultiTrainingBonus(0))->toBe(0.0)
            ->and($this->engine->calculateMultiTrainingBonus(1))->toBe(0.05)
            ->and($this->engine->calculateMultiTrainingBonus(6))->toBe(0.30)
            ->and($this->engine->calculateMultiTrainingBonus(100))->toBe(0.30);
    })->group('property');

    /**
     * Invalid Input Generators
     *
     * Tests that methods handle gracefully or clamp invalid inputs.
     */
    it('handles stat effectiveness with boundary and extreme values', function () {
        $statValues = [0, 1, 600, 1199, 1200, 1201, 1300, 1400, 1500];

        foreach ($statValues as $value) {
            $effective = $this->engine->calculateStatEffectiveness($value);

            expect($effective)->toBeGreaterThanOrEqual(0);

            if ($value <= 1200) {
                expect($effective)->toBe($value);
            } else {
                $expected = 1200 + (int) round(($value - 1200) / 2);
                expect($effective)->toBe($expected);
            }
        }
    })->group('property');

    it('handles failure rate with multiple stacked conditions', function () {
        $conditionsSet = [
            [],
            ['injury'],
            ['poor_health'],
            ['overworked'],
            ['injury', 'poor_health'],
            ['injury', 'poor_health', 'overworked'],
            ['unknown_condition'],
            ['injury', 'injury', 'injury'],
            ['unknown_a', 'unknown_b'],
        ];

        foreach ($conditionsSet as $conditions) {
            $rate = $this->engine->calculateFailureRate(
                energy: 50,
                numSupportCards: 0,
                conditions: $conditions
            );

            expect($rate)->toBeGreaterThanOrEqual(0.0)
                ->and($rate)->toBeLessThanOrEqual(1.0);
        }
    })->group('property');

    it('handles failure rate reduction from many support cards', function () {
        for ($cards = 0; $cards <= 10; $cards++) {
            $rate = $this->engine->calculateFailureRate(
                energy: 80,
                numSupportCards: $cards,
                conditions: []
            );

            expect($rate)->toBeGreaterThanOrEqual(0.0)
                ->and($rate)->toBeLessThanOrEqual(1.0);
        }
    })->group('property');

    it('handles training gain with zero growth rate', function () {
        $gain = $this->engine->calculateTrainingGain(
            baseStat: 500,
            facilityLevel: 3,
            growthRate: 0.0,
            mood: Mood::NORMAL,
            supportCardBonuses: [],
            numCardsPresent: 0,
            isFriendshipTraining: false
        );

        expect($gain)->toBeGreaterThanOrEqual(0);
    })->group('property');

    it('handles training gain with extreme growth rate', function () {
        $gain = $this->engine->calculateTrainingGain(
            baseStat: 500,
            facilityLevel: 5,
            growthRate: 100.0,
            mood: Mood::GREAT,
            supportCardBonuses: ['speed' => 50, 'power' => 30],
            numCardsPresent: 6,
            isFriendshipTraining: true
        );

        expect($gain)->toBeInt()
            ->and($gain)->toBeGreaterThanOrEqual(0);
    })->group('property');

    it('handles skill cost with zero base cost', function () {
        foreach ([0, 1, 2, 3, 4, 5] as $hintLevel) {
            $cost = $this->engine->calculateSkillCost(0, $hintLevel, false);
            expect($cost)->toBe(0);

            $costWithLearner = $this->engine->calculateSkillCost(0, $hintLevel, true);
            expect($costWithLearner)->toBe(0);
        }
    })->group('property');

    /**
     * Unicode / Japanese Handling Tests
     *
     * Tests that Japanese text (skill names, character names) is preserved
     * through data transformations.
     */
    it('preserves Japanese character names through JSON encoding', function () {
        $japaneseNames = [
            'サイレンススズカ',
            'スペシャルウィーク',
            'トウカイテイオー',
            'メジロマックイーン',
            'オグリキャップ',
            'ゴールドシップ',
            'ダイワスカーレット',
            'ウオッカ',
            'タイキシャトル',
            'エルコンドルパサー',
        ];

        foreach ($japaneseNames as $name) {
            $data = [
                'character_name' => $name,
                'stats' => ['speed' => 800, 'stamina' => 700, 'power' => 600, 'guts' => 500, 'wisdom' => 400],
            ];

            $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);
            $decoded = json_decode($encoded, true);

            expect($decoded['character_name'])->toBe($name)
                ->and(mb_strlen($decoded['character_name']))->toBe(mb_strlen($name));
        }
    })->group('property');

    it('preserves Japanese skill names through data serialization', function () {
        $skillNames = [
            '逃げのコツ◎',
            '先行のコツ◎',
            '差しのコツ◎',
            '追込のコツ◎',
            '末脚',
            'コーナー巧者',
            '直線加速',
            '好位差し',
            '一陣の風',
            '垂れウマ回避',
        ];

        foreach ($skillNames as $skillName) {
            $data = [
                'skill_name' => $skillName,
                'sp_cost' => random_int(50, 500),
                'is_gold' => (bool) random_int(0, 1),
            ];

            $serialized = serialize($data);
            $unserialized = unserialize($serialized);

            expect($unserialized['skill_name'])->toBe($skillName);

            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            $fromJson = json_decode($json, true);

            expect($fromJson['skill_name'])->toBe($skillName);
        }
    })->group('property');

    it('handles Mood enum Japanese names correctly', function () {
        $expectedJapanese = [
            [Mood::VERY_BAD, '絶不調'],
            [Mood::BAD, '不調'],
            [Mood::NORMAL, '普通'],
            [Mood::GOOD, '好調'],
            [Mood::GREAT, '絶好調'],
        ];

        foreach ($expectedJapanese as [$mood, $expectedName]) {
            expect($mood->japaneseName())->toBe($expectedName)
                ->and(mb_strlen($mood->japaneseName()))->toBeGreaterThan(0);

            $encoded = json_encode(['mood' => $mood->japaneseName()], JSON_UNESCAPED_UNICODE);
            $decoded = json_decode($encoded, true);
            expect($decoded['mood'])->toBe($expectedName);
        }
    })->group('property');

    /**
     * CharacterStats Value Object Edge Cases
     *
     * Tests additional methods like fromArray, toArray, compareTo, etc.
     */
    it('handles CharacterStats fromArray with missing keys defaulting to zero', function () {
        $partialData = [
            ['speed' => 500],
            ['stamina' => 300, 'power' => 200],
            ['wisdom' => 1200],
            [],
        ];

        foreach ($partialData as $data) {
            $stats = CharacterStats::fromArray($data);

            expect($stats->speed)->toBe($data['speed'] ?? 0)
                ->and($stats->stamina)->toBe($data['stamina'] ?? 0)
                ->and($stats->power)->toBe($data['power'] ?? 0)
                ->and($stats->guts)->toBe($data['guts'] ?? 0)
                ->and($stats->wisdom)->toBe($data['wisdom'] ?? 0);
        }
    })->group('property');

    it('handles CharacterStats toArray-fromArray round-trip at boundaries', function () {
        $boundaryStats = [
            new CharacterStats(0, 0, 0, 0, 0),
            new CharacterStats(1200, 1200, 1200, 1200, 1200),
            new CharacterStats(1500, 1500, 1500, 1500, 1500),
            new CharacterStats(0, 1200, 600, 1500, 300),
        ];

        foreach ($boundaryStats as $original) {
            $array = $original->toArray();
            $restored = CharacterStats::fromArray($array);

            expect($restored->speed)->toBe($original->speed)
                ->and($restored->stamina)->toBe($original->stamina)
                ->and($restored->power)->toBe($original->power)
                ->and($restored->guts)->toBe($original->guts)
                ->and($restored->wisdom)->toBe($original->wisdom)
                ->and($restored->getTotal())->toBe($original->getTotal());
        }
    })->group('property');

    it('handles CharacterStats compareTo with identical and extreme differences', function () {
        $zero = CharacterStats::zero();
        $max = new CharacterStats(1500, 1500, 1500, 1500, 1500);
        $mid = new CharacterStats(750, 750, 750, 750, 750);

        $selfDiff = $mid->compareTo($mid);
        expect(array_sum($selfDiff))->toBe(0);

        $maxToZero = $max->compareTo($zero);
        expect($maxToZero['speed'])->toBe(1500)
            ->and($maxToZero['stamina'])->toBe(1500)
            ->and($maxToZero['power'])->toBe(1500)
            ->and($maxToZero['guts'])->toBe(1500)
            ->and($maxToZero['wisdom'])->toBe(1500);

        $zeroToMax = $zero->compareTo($max);
        expect($zeroToMax['speed'])->toBe(-1500)
            ->and($zeroToMax['stamina'])->toBe(-1500);
    })->group('property');

    it('handles CharacterStats meetsTargets and getDeficits at exact boundaries', function () {
        $targets = new CharacterStats(800, 600, 500, 400, 700);

        $exact = new CharacterStats(800, 600, 500, 400, 700);
        expect($exact->meetsTargets($targets))->toBeTrue()
            ->and($exact->getDeficits($targets))->toBeEmpty();

        $oneShort = new CharacterStats(799, 600, 500, 400, 700);
        expect($oneShort->meetsTargets($targets))->toBeFalse();
        $deficits = $oneShort->getDeficits($targets);
        expect($deficits)->toHaveKey('speed')
            ->and($deficits['speed']['deficit'])->toBe(1);

        $allZero = CharacterStats::zero();
        expect($allZero->meetsTargets($targets))->toBeFalse();
        $allDeficits = $allZero->getDeficits($targets);
        expect(count($allDeficits))->toBe(5);
    })->group('property');

    it('handles CharacterStats withIncreasedStat at boundaries', function () {
        $stats = new CharacterStats(1490, 500, 500, 500, 500);

        $increased = $stats->withIncreasedStat('speed', 10);
        expect($increased->speed)->toBe(1500);

        expect(fn () => $stats->withIncreasedStat('speed', 11))
            ->toThrow(InvalidArgumentException::class);

        expect(fn () => $stats->withIncreasedStat('invalid_stat', 10))
            ->toThrow(InvalidArgumentException::class);
    })->group('property');

    it('handles CharacterStats isAtSoftCap and factory methods', function () {
        $zero = CharacterStats::zero();
        expect($zero->isAtSoftCap('speed'))->toBeFalse()
            ->and($zero->hasAnySoftCapped())->toBeFalse()
            ->and($zero->getSoftCappedStats())->toBeEmpty();

        $softCap = CharacterStats::softCap();
        expect($softCap->isAtSoftCap('speed'))->toBeTrue()
            ->and($softCap->isAtSoftCap('stamina'))->toBeTrue()
            ->and($softCap->hasAnySoftCapped())->toBeTrue()
            ->and(count($softCap->getSoftCappedStats()))->toBe(5);

        expect($softCap->isAtSoftCap('invalid'))->toBeFalse();
    })->group('property');

    it('handles CharacterStats getStat and getEffectiveStat with valid and invalid names', function () {
        $stats = new CharacterStats(800, 1300, 600, 1200, 900);
        $validNames = ['speed', 'stamina', 'power', 'guts', 'wisdom'];

        foreach ($validNames as $name) {
            expect($stats->getStat($name))->toBeInt()
                ->and($stats->getEffectiveStat($name))->toBeInt()
                ->and($stats->getEffectiveStat($name))->toBeLessThanOrEqual($stats->getStat($name));
        }

        expect(fn () => $stats->getStat('wit'))
            ->toThrow(InvalidArgumentException::class);

        expect(fn () => $stats->getEffectiveStat('intelligence'))
            ->toThrow(InvalidArgumentException::class);
    })->group('property');

    /**
     * Randomized Edge Case Generation
     *
     * Uses random inputs to find unexpected edge cases.
     */
    it('handles randomized stat combinations for CharacterStats operations', function () {
        for ($i = 0; $i < 100; $i++) {
            $speed = random_int(0, 1500);
            $stamina = random_int(0, 1500);
            $power = random_int(0, 1500);
            $guts = random_int(0, 1500);
            $wisdom = random_int(0, 1500);

            $stats = new CharacterStats($speed, $stamina, $power, $guts, $wisdom);

            $array = $stats->toArray();
            $restored = CharacterStats::fromArray($array);
            expect($restored->getTotal())->toBe($stats->getTotal());

            $effectiveArray = $stats->toEffectiveArray();
            foreach ($effectiveArray as $statName => $effectiveValue) {
                expect($effectiveValue)->toBeLessThanOrEqual($stats->getStat($statName));
            }

            $effectiveTotal = $stats->getEffectiveTotal();
            expect($effectiveTotal)->toBeLessThanOrEqual($stats->getTotal());
        }
    })->group('property');

    it('handles randomized skill cost calculations without errors', function () {
        for ($i = 0; $i < 100; $i++) {
            $baseCost = random_int(0, 1000);
            $hintLevel = random_int(0, 5);
            $hasFastLearner = (bool) random_int(0, 1);

            $cost = $this->engine->calculateSkillCost($baseCost, $hintLevel, $hasFastLearner);

            expect($cost)->toBeGreaterThanOrEqual(0)
                ->and($cost)->toBeLessThanOrEqual($baseCost);
        }
    })->group('property');

    it('handles all RaceDistance and RunningStyle combinations for stamina requirements', function () {
        foreach (RaceDistance::cases() as $distance) {
            foreach (RunningStyle::cases() as $style) {
                $requirement = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    []
                );

                expect($requirement)->toBeGreaterThanOrEqual(0)
                    ->and($requirement)->toBeInt();

                $withRecovery = $this->engine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    [1, 2, 3]
                );

                expect($withRecovery)->toBeLessThanOrEqual($requirement)
                    ->and($withRecovery)->toBeGreaterThanOrEqual(0);
            }
        }
    })->group('property');

    it('handles RaceDistance fromMeters at exact boundary values', function () {
        expect(RaceDistance::fromMeters(0))->toBe(RaceDistance::SPRINT)
            ->and(RaceDistance::fromMeters(1000))->toBe(RaceDistance::SPRINT)
            ->and(RaceDistance::fromMeters(1399))->toBe(RaceDistance::SPRINT)
            ->and(RaceDistance::fromMeters(1400))->toBe(RaceDistance::MILE)
            ->and(RaceDistance::fromMeters(1799))->toBe(RaceDistance::MILE)
            ->and(RaceDistance::fromMeters(1800))->toBe(RaceDistance::MEDIUM)
            ->and(RaceDistance::fromMeters(2399))->toBe(RaceDistance::MEDIUM)
            ->and(RaceDistance::fromMeters(2400))->toBe(RaceDistance::LONG)
            ->and(RaceDistance::fromMeters(99999))->toBe(RaceDistance::LONG);
    })->group('property');

    it('validates Mood effectiveness multipliers are monotonically ordered', function () {
        $moods = [Mood::VERY_BAD, Mood::BAD, Mood::NORMAL, Mood::GOOD, Mood::GREAT];

        for ($i = 1; $i < count($moods); $i++) {
            expect($moods[$i]->effectivenessMultiplier())
                ->toBeGreaterThan($moods[$i - 1]->effectivenessMultiplier());
        }
    })->group('property');
});
