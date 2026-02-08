<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;

/**
 * Facility Imbalance Detection Tests
 *
 * Tests the detectFacilityImbalance method of CriticalSituationDetector
 * to ensure it correctly identifies when facility levels are imbalanced
 * and generates appropriate recommendations.
 *
 * Test Coverage:
 * - Balanced facilities (no alert)
 * - Minor imbalance (variance = 3)
 * - Major imbalance (variance >= 4)
 * - Critical facilities behind (Speed, Stamina)
 * - Action item generation
 * - Detailed analysis content
 * - Priority levels
 * - Storage mode preservation
 */
beforeEach(function () {
    $this->mechanicsEngine = app(GameMechanicsEngine::class);
    $this->detector = new CriticalSituationDetector($this->mechanicsEngine);
});

describe('detectFacilityImbalance method', function () {
    it('returns null when facilities are balanced', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 3,
                'power' => 3,
                'guts' => 3,
                'wisdom' => 3,
            ],
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->toBeNull();
    });

    it('returns null when variance is exactly 2 (threshold)', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 3,
                'power' => 3,
                'guts' => 3,
                'wisdom' => 5, // Variance of 2 (5 - 3 = 2)
            ],
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->toBeNull();
    });

    it('detects minor imbalance when variance is 3', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 2,
                'stamina' => 2,
                'power' => 3,
                'guts' => 3,
                'wisdom' => 5, // Variance of 3 (5 - 2 = 3)
            ],
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->not->toBeNull();
        expect($result->priority)->toBe(Priority::MEDIUM);
        expect($result->message)->toContain('Facility levels imbalanced');
        expect($result->actionItems)->toBeArray();
        expect($result->actionItems)->not->toBeEmpty();
        expect($result->detailedAnalysis)->toContain('Facility Level Imbalance Analysis');
    });

    it('detects major imbalance when variance is 4 or more', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 1,
                'stamina' => 2,
                'power' => 3,
                'guts' => 4,
                'wisdom' => 5, // Variance of 4 (5 - 1 = 4)
            ],
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->not->toBeNull();
        expect($result->priority)->toBe(Priority::HIGH);
        expect($result->message)->toContain('severely imbalanced');
    });

    it('identifies lowest facility in action items', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 1, // Lowest
                'stamina' => 3,
                'power' => 3,
                'guts' => 3,
                'wisdom' => 4,
            ],
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->not->toBeNull();
        expect($result->actionItems)->toContain('Train at speed facility (currently Level 1)');
    });

    it('preserves storage mode in alert', function () {
        $context = createTestTrainingContext([
            'facility_levels' => [
                'speed' => 2,
                'stamina' => 2,
                'power' => 3,
                'guts' => 3,
                'wisdom' => 5,
            ],
            'storage_mode' => 'local',
        ]);

        $result = $this->detector->detectFacilityImbalance($context);

        expect($result)->not->toBeNull();
        expect($result->storageMode)->toBe('local');
    });
});
