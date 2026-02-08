<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Enums\Priority;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCard;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

describe('TrainingAdvisoryService - Property 3: Friendship Training Priority', function () {
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
     * Property 3: Friendship Training Priority
     *
     * **Validates: Requirements 3.1**
     *
     * When Friendship Training is available (bond ≥80), it must be prioritized
     * in recommendations. The top recommendation should be a Friendship Training
     * recommendation with HIGH priority.
     */
    it('prioritizes friendship training when single card has bond ≥80', function () {
        $context = generateFriendshipTrainingContext('local', 1, 'speed');

        $recommendations = $this->service->getTrainingRecommendations($context);

        // Verify recommendations are not empty
        expect($recommendations)->not->toBeEmpty();

        // Get top recommendation
        $topRecommendation = $recommendations->first();

        // Property assertions
        expect($topRecommendation->isFriendshipTraining)->toBeTrue()
            ->and($topRecommendation->priority)->toBe(Priority::HIGH)
            ->and(strtolower($topRecommendation->action))->toContain(strtolower('speed'));
    })->group('property');

    it('prioritizes friendship training when multiple cards have bond ≥80', function () {
        $context = generateFriendshipTrainingContext('local', 3, 'speed');

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        expect($topRecommendation->isFriendshipTraining)->toBeTrue()
            ->and($topRecommendation->priority)->toBe(Priority::HIGH)
            ->and(strtolower($topRecommendation->action))->toContain('speed');
    })->group('property');

    it('prioritizes friendship training across different facilities', function () {
        $facilities = ['speed', 'stamina', 'power', 'guts', 'wisdom'];

        foreach ($facilities as $facility) {
            $context = generateFriendshipTrainingContext('local', 2, $facility);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH)
                ->and(strtolower($topRecommendation->action))->toContain($facility);
        }
    })->group('property');

    it('prioritizes friendship training across different career phases', function () {
        $phases = [
            ['phase' => 'junior_year', 'turn' => 15],
            ['phase' => 'classic_year', 'turn' => 35],
            ['phase' => 'senior_year', 'turn' => 55],
        ];

        foreach ($phases as $phaseData) {
            $context = generateFriendshipTrainingContextAtTurn(
                'local',
                2,
                'speed',
                $phaseData['turn'],
                $phaseData['phase']
            );

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('prioritizes friendship training with varying bond levels ≥80', function () {
        $bondLevels = [80, 90, 100]; // Reduced: boundary, middle, max

        foreach ($bondLevels as $bondLevel) {
            $context = generateFriendshipContextWithBondLevel('local', $bondLevel, 'speed');

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('prioritizes friendship training even with low facility levels', function () {
        $facilityLevels = [1, 3, 5]; // Reduced: min, middle, max

        foreach ($facilityLevels as $level) {
            $context = generateFriendshipContextWithFacilityLevel(
                'local',
                2,
                'speed',
                $level
            );

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('prioritizes friendship training with different mood states', function () {
        $moods = ['normal', 'great']; // Reduced: representative sample

        foreach ($moods as $mood) {
            $context = generateFriendshipContextWithMood('local', 2, 'speed', $mood);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('prioritizes friendship training in both storage modes', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            $context = generateFriendshipTrainingContext($storageMode, 2, 'speed');

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH)
                ->and($topRecommendation->storageMode)->toBe($storageMode);
        }
    })->group('property');

    it('prioritizes facility with most friendship-ready cards when multiple facilities available', function () {
        // Create context with friendship cards at multiple facilities
        // Speed: 3 cards with bond ≥80
        // Stamina: 1 card with bond ≥80
        $context = new TrainingContext(
            turnNumber: 30,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(600, 550, 580, 520, 560),
            spAvailable: 250,
            energy: 80,
            mood: Mood::GOOD,
            acquiredSkills: [1, 5, 12],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 85, facility: 'speed'),
                new SupportCard(id: 2, bond: 82, facility: 'speed'),
                new SupportCard(id: 3, bond: 90, facility: 'speed'),
                new SupportCard(id: 4, bond: 80, facility: 'stamina'),
                new SupportCard(id: 5, bond: 75, facility: 'power'),
                new SupportCard(id: 6, bond: 70, facility: 'guts'),
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
            storageMode: 'local',
            careerRunId: 'uuid-'.uniqid()
        );

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should recommend speed (3 friendship cards) over stamina (1 friendship card)
        expect($topRecommendation->isFriendshipTraining)->toBeTrue()
            ->and($topRecommendation->priority)->toBe(Priority::HIGH)
            ->and(strtolower($topRecommendation->action))->toContain('speed');
    })->group('property');

    it('does not prioritize friendship training when no cards have bond ≥80', function () {
        // Create context with all cards below bond 80
        $context = new TrainingContext(
            turnNumber: 25,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(500, 450, 480, 420, 460),
            spAvailable: 200,
            energy: 75,
            mood: Mood::NORMAL,
            acquiredSkills: [1, 5],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 75, facility: 'speed'),
                new SupportCard(id: 2, bond: 70, facility: 'speed'),
                new SupportCard(id: 3, bond: 79, facility: 'speed'), // Just below threshold
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
            storageMode: 'local',
            careerRunId: 'uuid-'.uniqid()
        );

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should NOT be friendship training since no cards have bond ≥80
        expect($topRecommendation->isFriendshipTraining)->toBeFalse();
    })->group('property');

    it('prioritizes friendship training over multi-training bonus', function () {
        // Create context where:
        // - Speed has 1 card with bond ≥80 (friendship available)
        // - Stamina has 4 cards with bond <80 (higher multi-training bonus but no friendship)
        $context = new TrainingContext(
            turnNumber: 30,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(600, 550, 580, 520, 560),
            spAvailable: 250,
            energy: 80,
            mood: Mood::GOOD,
            acquiredSkills: [1, 5, 12],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 85, facility: 'speed'), // Friendship ready
                new SupportCard(id: 2, bond: 75, facility: 'stamina'),
                new SupportCard(id: 3, bond: 70, facility: 'stamina'),
                new SupportCard(id: 4, bond: 72, facility: 'stamina'),
                new SupportCard(id: 5, bond: 78, facility: 'stamina'),
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
            storageMode: 'local',
            careerRunId: 'uuid-'.uniqid()
        );

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should recommend speed (friendship) over stamina (more cards but no friendship)
        expect($topRecommendation->isFriendshipTraining)->toBeTrue()
            ->and($topRecommendation->priority)->toBe(Priority::HIGH)
            ->and(strtolower($topRecommendation->action))->toContain('speed');
    })->group('property');

    it('maintains friendship training priority with varying energy levels above threshold', function () {
        $energyLevels = [50, 75, 100]; // Reduced: boundary, middle, max

        foreach ($energyLevels as $energy) {
            $context = generateFriendshipContextWithEnergy('local', 2, 'speed', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            expect($topRecommendation->isFriendshipTraining)->toBeTrue()
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('prioritizes rest over friendship training when energy is critically low', function () {
        // When energy < 40, rest should be prioritized even over friendship training
        $context = generateFriendshipContextWithEnergy('local', 2, 'speed', 35);

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should recommend rest due to critical energy, not friendship training
        expect(strtolower($topRecommendation->action))->toContain('rest')
            ->and($topRecommendation->priority)->toBe(Priority::HIGH);
    })->group('property');

    it('prioritizes wisdom over friendship training when energy is low but not critical', function () {
        // When energy is 40-49, wisdom should be prioritized for energy recovery
        $energyLevels = [40, 45, 49]; // Reduced: boundary, middle, upper boundary

        foreach ($energyLevels as $energy) {
            $context = generateFriendshipContextWithEnergy('local', 2, 'speed', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            // Should recommend wisdom for energy recovery, not friendship training
            expect(strtolower($topRecommendation->action))->toContain('wisdom')
                ->and($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');
});

/**
 * Helper function to generate a training context with friendship training available
 *
 * @param  string  $storageMode  Storage mode (local/account)
 * @param  int  $numFriendshipCards  Number of cards with bond ≥80
 * @param  string  $facility  Facility where friendship cards are located
 */
function generateFriendshipTrainingContext(
    string $storageMode,
    int $numFriendshipCards,
    string $facility
): TrainingContext {
    $cards = [];

    // Add friendship-ready cards at specified facility
    for ($i = 0; $i < $numFriendshipCards; $i++) {
        $cards[] = new SupportCard(
            id: $i + 1,
            bond: 80 + ($i * 2), // 80, 82, 84, etc.
            facility: $facility
        );
    }

    // Add some non-friendship cards at other facilities
    $cards[] = new SupportCard(id: 10, bond: 70, facility: 'power');
    $cards[] = new SupportCard(id: 11, bond: 65, facility: 'guts');

    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(600, 550, 580, 520, 560),
        spAvailable: 250,
        energy: 80,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12, 23],
        skillHints: [],
        deck: new SupportCardDeck($cards),
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
 * Helper function to generate a training context with friendship training at specific turn/phase
 */
function generateFriendshipTrainingContextAtTurn(
    string $storageMode,
    int $numFriendshipCards,
    string $facility,
    int $turnNumber,
    string $phase
): TrainingContext {
    $cards = [];

    for ($i = 0; $i < $numFriendshipCards; $i++) {
        $cards[] = new SupportCard(
            id: $i + 1,
            bond: 80 + ($i * 2),
            facility: $facility
        );
    }

    return new TrainingContext(
        turnNumber: $turnNumber,
        phase: CareerPhase::from($phase),
        stats: new CharacterStats(
            400 + ($turnNumber * 10),
            350 + ($turnNumber * 8),
            380 + ($turnNumber * 9),
            320 + ($turnNumber * 7),
            360 + ($turnNumber * 8)
        ),
        spAvailable: 150 + ($turnNumber * 3),
        energy: 80,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck($cards),
        facilityLevels: [
            'speed' => min(5, 1 + (int) ($turnNumber / 10)),
            'stamina' => min(5, 1 + (int) ($turnNumber / 12)),
            'power' => min(5, 1 + (int) ($turnNumber / 11)),
            'guts' => min(5, 1 + (int) ($turnNumber / 13)),
            'wisdom' => min(5, 1 + (int) ($turnNumber / 10)),
        ],
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with specific bond level
 */
function generateFriendshipContextWithBondLevel(
    string $storageMode,
    int $bondLevel,
    string $facility
): TrainingContext {
    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(600, 550, 580, 520, 560),
        spAvailable: 250,
        energy: 80,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: $bondLevel, facility: $facility),
            new SupportCard(id: 2, bond: $bondLevel, facility: $facility),
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
 * Helper function to generate a training context with friendship and specific facility level
 */
function generateFriendshipContextWithFacilityLevel(
    string $storageMode,
    int $numFriendshipCards,
    string $facility,
    int $facilityLevel
): TrainingContext {
    $cards = [];

    for ($i = 0; $i < $numFriendshipCards; $i++) {
        $cards[] = new SupportCard(
            id: $i + 1,
            bond: 80 + ($i * 2),
            facility: $facility
        );
    }

    $facilityLevels = [
        'speed' => 3,
        'stamina' => 3,
        'power' => 3,
        'guts' => 2,
        'wisdom' => 3,
    ];
    $facilityLevels[$facility] = $facilityLevel;

    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(600, 550, 580, 520, 560),
        spAvailable: 250,
        energy: 80,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck($cards),
        facilityLevels: $facilityLevels,
        upcomingRaces: [],
        scenario: 'ura_finale',
        storageMode: $storageMode,
        careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
    );
}

/**
 * Helper function to generate a training context with friendship and specific mood
 */
function generateFriendshipContextWithMood(
    string $storageMode,
    int $numFriendshipCards,
    string $facility,
    string $mood
): TrainingContext {
    $cards = [];

    for ($i = 0; $i < $numFriendshipCards; $i++) {
        $cards[] = new SupportCard(
            id: $i + 1,
            bond: 80 + ($i * 2),
            facility: $facility
        );
    }

    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(600, 550, 580, 520, 560),
        spAvailable: 250,
        energy: 80,
        mood: Mood::from($mood),
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck($cards),
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
 * Helper function to generate a training context with friendship and specific energy level
 */
function generateFriendshipContextWithEnergy(
    string $storageMode,
    int $numFriendshipCards,
    string $facility,
    int $energy
): TrainingContext {
    $cards = [];

    for ($i = 0; $i < $numFriendshipCards; $i++) {
        $cards[] = new SupportCard(
            id: $i + 1,
            bond: 80 + ($i * 2),
            facility: $facility
        );
    }

    return new TrainingContext(
        turnNumber: 30,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(600, 550, 580, 520, 560),
        spAvailable: 250,
        energy: $energy,
        mood: Mood::GOOD,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck($cards),
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
