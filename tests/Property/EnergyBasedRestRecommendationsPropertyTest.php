<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCard;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

describe('TrainingAdvisoryService - Property 4: Energy-Based Rest Recommendations', function () {
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
     * Property 4: Energy-Based Rest Recommendations
     *
     * **Validates: Requirements 3.1**
     *
     * When energy falls below 50, the system must recommend rest or Wisdom training.
     * This property ensures that low energy situations are properly detected and
     * appropriate recovery recommendations are provided to avoid high failure rates.
     */
    it('recommends rest or wisdom when energy is below 50', function () {
        $energyLevels = [10, 35, 49]; // Reduced: low, critical boundary, upper boundary

        foreach ($energyLevels as $energy) {
            $context = generateTrainingContextWithLowEnergy('local', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            // Verify recommendations are not empty
            expect($recommendations)->not->toBeEmpty();

            // Get top recommendation
            $topRecommendation = $recommendations->first();

            // Property assertion: Must contain either REST or Wisdom recommendation
            $hasRestOrWisdom = $topRecommendation->type === RecommendationType::REST_RECOVERY
                || (strtolower($topRecommendation->action) === 'wisdom training'
                    || str_contains(strtolower($topRecommendation->action), 'wisdom'));

            expect($hasRestOrWisdom)->toBeTrue(
                "Expected rest or wisdom recommendation for energy {$energy}, got: {$topRecommendation->action}"
            );

            // Should be high priority
            expect($topRecommendation->priority)->toBe(Priority::HIGH);
        }
    })->group('property');

    it('recommends rest for critically low energy (< 40)', function () {
        $criticalEnergyLevels = [10, 25, 39]; // Reduced: very low, mid-critical, boundary

        foreach ($criticalEnergyLevels as $energy) {
            $context = generateTrainingContextWithLowEnergy('local', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            // For critical energy, should recommend rest
            $isRest = $topRecommendation->type === RecommendationType::REST_RECOVERY
                || str_contains(strtolower($topRecommendation->action), 'rest');

            expect($isRest)->toBeTrue(
                "Expected rest recommendation for critical energy {$energy}, got: {$topRecommendation->action}"
            );
        }
    })->group('property');

    it('recommends wisdom for low but not critical energy (40-49)', function () {
        $lowEnergyLevels = [40, 45, 49]; // Reduced: lower boundary, middle, upper boundary

        foreach ($lowEnergyLevels as $energy) {
            $context = generateTrainingContextWithLowEnergy('local', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            // For low but not critical energy, should recommend wisdom
            $isWisdom = strtolower($topRecommendation->action) === 'wisdom training'
                || str_contains(strtolower($topRecommendation->action), 'wisdom');

            expect($isWisdom)->toBeTrue(
                "Expected wisdom recommendation for low energy {$energy}, got: {$topRecommendation->action}"
            );
        }
    })->group('property');

    it('does not recommend rest or wisdom when energy is 50 or above', function () {
        $normalEnergyLevels = [50, 70, 100]; // Reduced: boundary, middle, max

        foreach ($normalEnergyLevels as $energy) {
            $context = generateTrainingContextWithNormalEnergy('local', $energy);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            // Should NOT be rest recommendation when energy is sufficient
            expect($topRecommendation->type)->not->toBe(RecommendationType::REST_RECOVERY);

            // May recommend wisdom for other reasons, but not as top priority for energy recovery
            // If wisdom is recommended, it should not be due to low energy
            if (str_contains(strtolower($topRecommendation->action), 'wisdom')) {
                // Check reasoning doesn't mention energy recovery
                expect(strtolower($topRecommendation->reasoning))
                    ->not->toContain('energy low')
                    ->and(strtolower($topRecommendation->reasoning))
                    ->not->toContain('energy recovery');
            }
        }
    })->group('property');

    it('recommends rest or wisdom across different career phases when energy is low', function () {
        $phases = [
            ['phase' => 'junior_year', 'turn' => 15],
            ['phase' => 'classic_year', 'turn' => 35],
            ['phase' => 'senior_year', 'turn' => 55],
        ];

        foreach ($phases as $phaseData) {
            $context = generateLowEnergyContextAtTurn(
                'local',
                $phaseData['turn'],
                $phaseData['phase'],
                45 // Low energy
            );

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            $hasRestOrWisdom = $topRecommendation->type === RecommendationType::REST_RECOVERY
                || str_contains(strtolower($topRecommendation->action), 'wisdom')
                || str_contains(strtolower($topRecommendation->action), 'rest');

            expect($hasRestOrWisdom)->toBeTrue(
                "Expected rest or wisdom in {$phaseData['phase']} at turn {$phaseData['turn']}"
            );
        }
    })->group('property');

    it('recommends rest or wisdom in both storage modes when energy is low', function () {
        $storageModes = ['local', 'account'];

        foreach ($storageModes as $storageMode) {
            $context = generateTrainingContextWithLowEnergy($storageMode, 45);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            $hasRestOrWisdom = $topRecommendation->type === RecommendationType::REST_RECOVERY
                || str_contains(strtolower($topRecommendation->action), 'wisdom');

            expect($hasRestOrWisdom)->toBeTrue();
            expect($topRecommendation->storageMode)->toBe($storageMode);
        }
    })->group('property');

    it('prioritizes rest over friendship training when energy is critically low', function () {
        // Create context with friendship training available BUT critically low energy
        $context = new TrainingContext(
            turnNumber: 30,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(600, 550, 580, 520, 560),
            spAvailable: 250,
            energy: 35, // Critically low
            mood: Mood::GOOD,
            acquiredSkills: [1, 5, 12],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 85, facility: 'speed'), // Friendship ready
                new SupportCard(id: 2, bond: 82, facility: 'speed'), // Friendship ready
                new SupportCard(id: 3, bond: 90, facility: 'speed'), // Friendship ready
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

        // Should recommend rest, NOT friendship training
        $isRest = $topRecommendation->type === RecommendationType::REST_RECOVERY
            || str_contains(strtolower($topRecommendation->action), 'rest');

        expect($isRest)->toBeTrue(
            'Expected rest to be prioritized over friendship training when energy is critically low'
        );
    })->group('property');

    it('prioritizes wisdom over friendship training when energy is low but not critical', function () {
        // Create context with friendship training available BUT low energy (40-49)
        $context = new TrainingContext(
            turnNumber: 30,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(600, 550, 580, 520, 560),
            spAvailable: 250,
            energy: 45, // Low but not critical
            mood: Mood::GOOD,
            acquiredSkills: [1, 5, 12],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 85, facility: 'speed'), // Friendship ready
                new SupportCard(id: 2, bond: 82, facility: 'speed'), // Friendship ready
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

        // Should recommend wisdom, NOT friendship training
        $isWisdom = str_contains(strtolower($topRecommendation->action), 'wisdom');

        expect($isWisdom)->toBeTrue(
            'Expected wisdom to be prioritized over friendship training when energy is low'
        );
    })->group('property');

    it('recommends rest or wisdom even with multiple support cards present', function () {
        // Create context with many support cards but low energy
        $context = new TrainingContext(
            turnNumber: 25,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(500, 450, 480, 420, 460),
            spAvailable: 200,
            energy: 40, // Low energy
            mood: Mood::NORMAL,
            acquiredSkills: [1, 5],
            skillHints: [],
            deck: new SupportCardDeck([
                new SupportCard(id: 1, bond: 75, facility: 'speed'),
                new SupportCard(id: 2, bond: 70, facility: 'speed'),
                new SupportCard(id: 3, bond: 72, facility: 'speed'),
                new SupportCard(id: 4, bond: 68, facility: 'speed'),
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

        // Should recommend wisdom despite 4 cards at speed
        $isWisdom = str_contains(strtolower($topRecommendation->action), 'wisdom');

        expect($isWisdom)->toBeTrue(
            'Expected wisdom recommendation despite multiple support cards present'
        );
    })->group('property');

    it('includes energy recovery in expected outcomes for rest recommendations', function () {
        $context = generateTrainingContextWithLowEnergy('local', 30);

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should mention energy recovery in expected outcomes
        $outcomesString = json_encode($topRecommendation->expectedOutcomes);

        expect(strtolower($outcomesString))
            ->toContain('energy');
    })->group('property');

    it('includes failure risk warning in reasoning for low energy', function () {
        $context = generateTrainingContextWithLowEnergy('local', 35);

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Reasoning should mention failure risk or energy concerns
        $reasoning = strtolower($topRecommendation->reasoning);

        $mentionsEnergyOrFailure = str_contains($reasoning, 'energy')
            || str_contains($reasoning, 'failure')
            || str_contains($reasoning, 'risk');

        expect($mentionsEnergyOrFailure)->toBeTrue(
            'Expected reasoning to mention energy concerns or failure risk'
        );
    })->group('property');

    it('recommends rest or wisdom with varying mood states when energy is low', function () {
        $moods = ['bad', 'normal', 'great']; // Reduced: representative sample

        foreach ($moods as $mood) {
            $context = generateLowEnergyContextWithMood('local', 40, $mood);

            $recommendations = $this->service->getTrainingRecommendations($context);

            expect($recommendations)->not->toBeEmpty();

            $topRecommendation = $recommendations->first();

            $hasRestOrWisdom = $topRecommendation->type === RecommendationType::REST_RECOVERY
                || str_contains(strtolower($topRecommendation->action), 'wisdom');

            expect($hasRestOrWisdom)->toBeTrue(
                "Expected rest or wisdom with mood {$mood}"
            );
        }
    })->group('property');

    it('recommends rest or wisdom at boundary energy level (exactly 49)', function () {
        $context = generateTrainingContextWithLowEnergy('local', 49);

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        $hasRestOrWisdom = $topRecommendation->type === RecommendationType::REST_RECOVERY
            || str_contains(strtolower($topRecommendation->action), 'wisdom');

        expect($hasRestOrWisdom)->toBeTrue(
            'Expected rest or wisdom at boundary energy level 49'
        );
    })->group('property');

    it('does not recommend rest or wisdom at boundary energy level (exactly 50)', function () {
        $context = generateTrainingContextWithNormalEnergy('local', 50);

        $recommendations = $this->service->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();

        $topRecommendation = $recommendations->first();

        // Should NOT be rest recommendation at energy 50
        expect($topRecommendation->type)->not->toBe(RecommendationType::REST_RECOVERY);

        // If wisdom is recommended, it should not be for energy recovery
        if (str_contains(strtolower($topRecommendation->action), 'wisdom')) {
            expect(strtolower($topRecommendation->reasoning))
                ->not->toContain('energy low');
        }
    })->group('property');
});

/**
 * Helper function to generate a training context with low energy (< 50)
 *
 * @param  string  $storageMode  Storage mode (local/account)
 * @param  int  $energy  Energy level (should be < 50)
 */
function generateTrainingContextWithLowEnergy(
    string $storageMode,
    int $energy
): TrainingContext {
    return new TrainingContext(
        turnNumber: 25,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(500, 450, 480, 420, 460),
        spAvailable: 200,
        energy: $energy,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 70, facility: 'speed'),
            new SupportCard(id: 2, bond: 65, facility: 'stamina'),
            new SupportCard(id: 3, bond: 75, facility: 'power'),
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
 * Helper function to generate a training context with normal energy (≥ 50)
 *
 * @param  string  $storageMode  Storage mode (local/account)
 * @param  int  $energy  Energy level (should be ≥ 50)
 */
function generateTrainingContextWithNormalEnergy(
    string $storageMode,
    int $energy
): TrainingContext {
    return new TrainingContext(
        turnNumber: 25,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(500, 450, 480, 420, 460),
        spAvailable: 200,
        energy: $energy,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 70, facility: 'speed'),
            new SupportCard(id: 2, bond: 65, facility: 'stamina'),
            new SupportCard(id: 3, bond: 75, facility: 'power'),
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
 * Helper function to generate a low energy context at specific turn/phase
 *
 * @param  string  $storageMode  Storage mode (local/account)
 * @param  int  $turnNumber  Turn number
 * @param  string  $phase  Career phase
 * @param  int  $energy  Energy level
 */
function generateLowEnergyContextAtTurn(
    string $storageMode,
    int $turnNumber,
    string $phase,
    int $energy
): TrainingContext {
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
        energy: $energy,
        mood: Mood::NORMAL,
        acquiredSkills: [1, 5, 12],
        skillHints: [],
        deck: new SupportCardDeck([
            new SupportCard(id: 1, bond: 70, facility: 'speed'),
        ]),
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
 * Helper function to generate a low energy context with specific mood
 *
 * @param  string  $storageMode  Storage mode (local/account)
 * @param  int  $energy  Energy level
 * @param  string  $mood  Mood state
 */
function generateLowEnergyContextWithMood(
    string $storageMode,
    int $energy,
    string $mood
): TrainingContext {
    return new TrainingContext(
        turnNumber: 25,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(500, 450, 480, 420, 460),
        spAvailable: 200,
        energy: $energy,
        mood: Mood::from($mood),
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
