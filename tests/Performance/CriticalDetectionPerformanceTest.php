<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCard;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Performance tests for CriticalSituationDetector
 *
 * Target: <500ms for all detection methods
 */
beforeEach(function () {
    $this->mechanicsEngine = new GameMechanicsEngine;
    $this->detector = new CriticalSituationDetector($this->mechanicsEngine);
});

describe('Critical Detection Performance', function () {
    it('detectStaminaCrisis completes within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 100;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectStaminaCrisis($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000; // Convert to ms

        expect($avgDuration)->toBeLessThan(500, "Average duration: {$avgDuration}ms");
    });

    it('detectSpShortage completes within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 100;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectSpShortage($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000;

        expect($avgDuration)->toBeLessThan(500, "Average duration: {$avgDuration}ms");
    });

    it('detectEnergyCritical completes within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 100;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectEnergyCritical($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000;

        expect($avgDuration)->toBeLessThan(500, "Average duration: {$avgDuration}ms");
    });

    it('detectBondBehindSchedule completes within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 100;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectBondBehindSchedule($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000;

        expect($avgDuration)->toBeLessThan(500, "Average duration: {$avgDuration}ms");
    });

    it('detectFacilityImbalance completes within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 100;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectFacilityImbalance($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000;

        expect($avgDuration)->toBeLessThan(500, "Average duration: {$avgDuration}ms");
    });

    it('all detection methods combined complete within 500ms', function () {
        $context = createPerformanceTestContext();

        $iterations = 20; // Fewer iterations for combined test
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->detector->detectStaminaCrisis($context);
            $this->detector->detectSpShortage($context);
            $this->detector->detectEnergyCritical($context);
            $this->detector->detectBondBehindSchedule($context);
            $this->detector->detectFacilityImbalance($context);
        }

        $duration = microtime(true) - $start;
        $avgDuration = ($duration / $iterations) * 1000;

        expect($avgDuration)->toBeLessThan(500, "Average combined duration: {$avgDuration}ms");
    });

    it('profiles individual method performance', function () {
        $context = createPerformanceTestContext();
        $iterations = 100;

        $methods = [
            'detectStaminaCrisis',
            'detectSpShortage',
            'detectEnergyCritical',
            'detectBondBehindSchedule',
            'detectFacilityImbalance',
        ];

        $results = [];

        foreach ($methods as $method) {
            $start = microtime(true);

            for ($i = 0; $i < $iterations; $i++) {
                $this->detector->$method($context);
            }

            $duration = microtime(true) - $start;
            $avgDuration = ($duration / $iterations) * 1000;

            $results[$method] = $avgDuration;
        }

        // Output profiling results
        echo "\n\nPerformance Profile:\n";
        echo str_repeat('=', 60)."\n";

        foreach ($results as $method => $avgDuration) {
            $status = $avgDuration < 500 ? '✓' : '✗';
            echo sprintf(
                "%s %-35s %8.2fms\n",
                $status,
                $method,
                $avgDuration
            );
        }

        echo str_repeat('=', 60)."\n";

        // All methods should be under 500ms
        foreach ($results as $method => $avgDuration) {
            expect($avgDuration)->toBeLessThan(500, "{$method}: {$avgDuration}ms");
        }
    });
});

/**
 * Create a realistic training context for performance testing
 */
function createPerformanceTestContext(): TrainingContext
{
    return new TrainingContext(
        turnNumber: 35,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 650,
            stamina: 450,
            power: 580,
            guts: 520,
            wisdom: 600
        ),
        spAvailable: 180,
        energy: 65,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12, 23, 45],
        skillHints: [
            ['skill_id' => 23, 'level' => 3],
            ['skill_id' => 45, 'level' => 2],
            ['skill_id' => 67, 'level' => 4],
        ],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 85, facility: 'speed', limitBreak: 3, name: 'Speed Card 1'),
            new SupportCard(id: 2, bond: 72, facility: 'stamina', limitBreak: 2, name: 'Stamina Card 1'),
            new SupportCard(id: 3, bond: 68, facility: 'power', limitBreak: 2, name: 'Power Card 1'),
            new SupportCard(id: 4, bond: 75, facility: 'speed', limitBreak: 1, name: 'Speed Card 2'),
            new SupportCard(id: 5, bond: 80, facility: 'wisdom', limitBreak: 3, name: 'Wisdom Card 1'),
            new SupportCard(id: 6, bond: 65, facility: 'stamina', limitBreak: 1, name: 'Stamina Card 2'),
        ]),
        facilityLevels: [
            'speed' => 4,
            'stamina' => 2,
            'power' => 3,
            'guts' => 2,
            'wisdom' => 4,
        ],
        upcomingRaces: [
            ['id' => 15, 'distance' => 'medium', 'turn' => 38],
            ['id' => 16, 'distance' => 'long', 'turn' => 45],
        ],
        scenario: null,
        storageMode: 'local',
        careerRunId: 'test-uuid-123',
    );
}
