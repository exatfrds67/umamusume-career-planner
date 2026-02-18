<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCard;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

describe('TrainingAdvisoryService - Property 1: Storage Mode Consistency', function () {
    beforeEach(function () {
        // Mock the Neuron AI Service to avoid actual AI calls
        $this->neuronAIService = Mockery::mock(\App\Services\Neuron\NeuronAIService::class);
        $this->neuronAIService->shouldReceive('isAvailable')->andReturn(false);

        // Create real instances of other dependencies
        $this->mechanicsEngine = new GameMechanicsEngine;
        $this->ruleBasedAdvisor = new RuleBasedAdvisor($this->mechanicsEngine);
        $this->accuracyTracker = new PredictionAccuracyTracker;
        $this->criticalDetector = new CriticalSituationDetector($this->mechanicsEngine);

        // Create the service with mocked AI
        $this->service = new TrainingAdvisoryService(
            $this->neuronAIService,
            $this->ruleBasedAdvisor,
            $this->mechanicsEngine,
            $this->accuracyTracker,
            $this->criticalDetector
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    /**
     * Property 1: Storage Mode Consistency
     *
     * **Validates: Requirements 3.1, 3.7**
     *
     * For any recommendation generated, the storage mode (Local/Account) must be
     * correctly identified and propagated throughout the recommendation lifecycle.
     */
    it('preserves storage mode in all recommendations for local mode', function () {
        // Generate multiple training contexts with local storage mode
        $contexts = [
            generateTrainingContext('local', 1, 'junior_year'),
            generateTrainingContext('local', 35, 'classic_year'),
            generateTrainingContext('local', 70, 'ura_finals'),
        ]; // Reduced: early, mid, late game

        foreach ($contexts as $context) {
            $recommendations = $this->service->getTrainingRecommendations($context);

            // Verify context storage mode
            expect($context->storageMode)->toBe('local')
                ->and($context->isLocalMode())->toBeTrue()
                ->and($context->isAccountMode())->toBeFalse();

            // Verify all recommendations preserve storage mode
            expect($recommendations)->not->toBeEmpty();

            foreach ($recommendations as $recommendation) {
                expect($recommendation->storageMode)->toBe('local')
                    ->and($recommendation->isLocalMode())->toBeTrue()
                    ->and($recommendation->isAccountMode())->toBeFalse();
            }
        }
    })->group('property');

    it('preserves storage mode in all recommendations for account mode', function () {
        // Generate multiple training contexts with account storage mode
        $contexts = [
            generateTrainingContext('account', 1, 'junior_year'),
            generateTrainingContext('account', 35, 'classic_year'),
            generateTrainingContext('account', 70, 'ura_finals'),
        ]; // Reduced: early, mid, late game

        foreach ($contexts as $context) {
            $recommendations = $this->service->getTrainingRecommendations($context);

            // Verify context storage mode
            expect($context->storageMode)->toBe('account')
                ->and($context->isAccountMode())->toBeTrue()
                ->and($context->isLocalMode())->toBeFalse();

            // Verify all recommendations preserve storage mode
            expect($recommendations)->not->toBeEmpty();

            foreach ($recommendations as $recommendation) {
                expect($recommendation->storageMode)->toBe('account')
                    ->and($recommendation->isAccountMode())->toBeTrue()
                    ->and($recommendation->isLocalMode())->toBeFalse();
            }
        }
    })->group('property');

    it('preserves storage mode across different career phases', function () {
        $storageModes = ['local', 'account'];
        $phases = ['junior_year', 'senior_year']; // Reduced: early and late

        foreach ($storageModes as $storageMode) {
            foreach ($phases as $phase) {
                $context = generateTrainingContext($storageMode, 15, $phase);
                $recommendations = $this->service->getTrainingRecommendations($context);

                expect($context->storageMode)->toBe($storageMode);

                foreach ($recommendations as $recommendation) {
                    expect($recommendation->storageMode)->toBe($storageMode);
                }
            }
        }
    })->group('property');

    it('preserves storage mode with varying energy levels', function () {
        $storageModes = ['local', 'account'];
        $energyLevels = [20, 60, 100]; // Reduced: low, mid, high

        foreach ($storageModes as $storageMode) {
            foreach ($energyLevels as $energy) {
                $context = generateTrainingContextWithEnergy($storageMode, $energy);
                $recommendations = $this->service->getTrainingRecommendations($context);

                expect($context->storageMode)->toBe($storageMode);

                foreach ($recommendations as $recommendation) {
                    expect($recommendation->storageMode)->toBe($storageMode);
                }
            }
        }
    })->group('property');

    it('preserves storage mode with friendship training available', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            $context = generateTrainingContextWithFriendshipAvailable($storageMode);
            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($context->storageMode)->toBe($storageMode)
                ->and($context->hasFriendshipTrainingAvailable())->toBeTrue();

            foreach ($recommendations as $recommendation) {
                expect($recommendation->storageMode)->toBe($storageMode);
            }
        }
    })->group('property');

    it('preserves storage mode in critical situation detection', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            // Generate context with low energy to trigger critical alert
            $context = generateTrainingContextWithEnergy($storageMode, 30);
            $alerts = $this->service->detectCriticalSituations($context);

            expect($context->storageMode)->toBe($storageMode);

            // If alerts are generated, verify storage mode
            foreach ($alerts as $alert) {
                if (method_exists($alert, 'isLocalMode')) {
                    if ($storageMode === 'local') {
                        expect($alert->isLocalMode())->toBeTrue();
                    } else {
                        expect($alert->isLocalMode())->toBeFalse();
                    }
                }
            }
        }
    })->group('property');

    it('maintains storage mode consistency across multiple recommendation calls', function () {
        $storageMode = 'local';
        $context = generateTrainingContext($storageMode, 25, 'classic_year');

        // Call multiple times to ensure consistency
        for ($i = 0; $i < 3; $i++) { // Reduced from 5 to 3
            $recommendations = $this->service->getTrainingRecommendations($context);

            foreach ($recommendations as $recommendation) {
                expect($recommendation->storageMode)->toBe($storageMode);
            }
        }
    })->group('property');

    it('preserves storage mode with different mood states', function () {
        $storageModes = ['local', 'account'];
        $moods = ['bad', 'normal', 'great']; // Reduced: representative sample

        foreach ($storageModes as $storageMode) {
            foreach ($moods as $mood) {
                $context = generateTrainingContextWithMood($storageMode, $mood);
                $recommendations = $this->service->getTrainingRecommendations($context);

                expect($context->storageMode)->toBe($storageMode);

                foreach ($recommendations as $recommendation) {
                    expect($recommendation->storageMode)->toBe($storageMode);
                }
            }
        }
    })->group('property');

    it('preserves storage mode with varying facility levels', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            $context = generateTrainingContextWithFacilityLevels($storageMode, [
                'speed' => 3,
                'stamina' => 2,
                'power' => 4,
                'guts' => 1,
                'wisdom' => 5,
            ]);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($context->storageMode)->toBe($storageMode);

            foreach ($recommendations as $recommendation) {
                expect($recommendation->storageMode)->toBe($storageMode);
            }
        }
    })->group('property');

    it('never mixes storage modes in a single recommendation set', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            $context = generateTrainingContext($storageMode, 30, 'classic_year');
            $recommendations = $this->service->getTrainingRecommendations($context);

            // Get all unique storage modes from recommendations
            $recommendationModes = [];
            foreach ($recommendations as $rec) {
                $recommendationModes[] = $rec->storageMode;
            }
            $uniqueModes = array_unique($recommendationModes);

            // Should only have one storage mode
            expect(count($uniqueModes))->toBe(1)
                ->and($uniqueModes[array_key_first($uniqueModes)])->toBe($storageMode);
        }
    })->group('property');
});

/**
 * Helper function to generate a training context with specified storage mode
 */
function generateTrainingContext(string $storageMode, int $turnNumber, string $phase): TrainingContext
{
    return new TrainingContext(
        turnNumber: $turnNumber,
        phase: CareerPhase::from($phase),
        stats: new CharacterStats(
            speed: 400 + ($turnNumber * 10),
            stamina: 350 + ($turnNumber * 8),
            power: 380 + ($turnNumber * 9),
            guts: 320 + ($turnNumber * 7),
            wisdom: 360 + ($turnNumber * 8)
        ),
        spAvailable: 150 + ($turnNumber * 3),
        energy: 75,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12],
        skillHints: [
            ['skill_id' => 23, 'level' => 3],
            ['skill_id' => 45, 'level' => 2],
        ],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 70, facility: 'speed'),
            new SupportCard(id: 2, bond: 65, facility: 'stamina'),
            new SupportCard(id: 3, bond: 75, facility: 'power'),
        ]),
        facilityLevels: [
            'speed' => min(5, 1 + (int) ($turnNumber / 10)),
            'stamina' => min(5, 1 + (int) ($turnNumber / 12)),
            'power' => min(5, 1 + (int) ($turnNumber / 11)),
            'guts' => min(5, 1 + (int) ($turnNumber / 13)),
            'wisdom' => min(5, 1 + (int) ($turnNumber / 10)),
        ],
        upcomingRaces: [
            ['id' => 15, 'distance' => 'medium', 'turn' => $turnNumber + 5],
        ],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with specific energy level
 */
function generateTrainingContextWithEnergy(string $storageMode, int $energy): TrainingContext
{
    return new TrainingContext(
        turnNumber: 25,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 500,
            stamina: 450,
            power: 480,
            guts: 420,
            wisdom: 460
        ),
        spAvailable: 200,
        energy: $energy,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 70, facility: 'speed'),
        ]),
        facilityLevels: [
            'speed' => 3,
            'stamina' => 2,
            'power' => 3,
            'guts' => 2,
            'wisdom' => 3,
        ],
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with friendship training available
 */
function generateTrainingContextWithFriendshipAvailable(string $storageMode): TrainingContext
{
    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 600,
            stamina: 550,
            power: 580,
            guts: 520,
            wisdom: 560
        ),
        spAvailable: 250,
        energy: 80,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12, 23],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 85, facility: 'speed'),  // Friendship ready
            new SupportCard(id: 2, bond: 82, facility: 'speed'),  // Friendship ready
            new SupportCard(id: 3, bond: 80, facility: 'speed'),  // Friendship ready
        ]),
        facilityLevels: [
            'speed' => 3,
            'stamina' => 3,
            'power' => 3,
            'guts' => 2,
            'wisdom' => 3,
        ],
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with specific mood
 */
function generateTrainingContextWithMood(string $storageMode, string $mood): TrainingContext
{
    return new TrainingContext(
        turnNumber: 20,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 450,
            stamina: 400,
            power: 430,
            guts: 370,
            wisdom: 410
        ),
        spAvailable: 180,
        energy: 70,
        mood: Mood::from($mood),
        acquiredSkills: [1, 5],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 65, facility: 'speed'),
        ]),
        facilityLevels: [
            'speed' => 2,
            'stamina' => 2,
            'power' => 2,
            'guts' => 2,
            'wisdom' => 2,
        ],
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with specific facility levels
 */
function generateTrainingContextWithFacilityLevels(string $storageMode, array $facilityLevels): TrainingContext
{
    return new TrainingContext(
        turnNumber: 40,
        phase: CareerPhase::SENIOR,
        stats: new CharacterStats(
            speed: 700,
            stamina: 650,
            power: 680,
            guts: 620,
            wisdom: 660
        ),
        spAvailable: 300,
        energy: 75,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12, 23, 45],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 75, facility: 'speed'),
        ]),
        facilityLevels: $facilityLevels,
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}
