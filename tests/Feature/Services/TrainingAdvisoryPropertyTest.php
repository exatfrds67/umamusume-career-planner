<?php

declare(strict_types=1);

use App\Collections\RecommendationCollection;
use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\GameMechanicsEngine;
use App\Services\Neuron\NeuronAIService;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Property-Based Tests for Training Advisory System
 *
 * These tests validate correctness properties that should hold true
 * across all valid executions of the system.
 *
 * Property 1: Storage Mode Consistency
 * Property 2: Response Time Bounds
 */
describe('TrainingAdvisoryService Properties', function () {
    beforeEach(function () {
        $this->neuronAI = Mockery::mock(NeuronAIService::class);
        $this->ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
        $this->mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
        $this->accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
        $this->criticalDetector = Mockery::mock(\App\Services\CriticalSituationDetector::class);
        $this->recommendationCache = Mockery::mock(\App\Services\RecommendationCacheService::class);
        $this->performanceMonitor = Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class);

        // Mock cache to always return null (no cached recommendations)
        $this->recommendationCache
            ->shouldReceive('getCachedRecommendations')
            ->andReturn(null);

        $this->recommendationCache
            ->shouldReceive('cacheRecommendations')
            ->andReturn(true);

        // Mock performance monitor to accept any metrics
        $this->performanceMonitor
            ->shouldReceive('recordRecommendationGeneration')
            ->andReturn(true);

        $this->service = new TrainingAdvisoryService(
            $this->neuronAI,
            $this->ruleBasedAdvisor,
            $this->mechanicsEngine,
            $this->accuracyTracker,
            $this->criticalDetector,
            $this->recommendationCache,
            $this->performanceMonitor
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    /**
     * Property 1: Storage Mode Consistency
     *
     * For any recommendation generated, the storage mode (Local/Account) must be
     * correctly identified and propagated throughout the recommendation lifecycle.
     *
     * Validates: Requirements 3.1, 3.7
     */
    describe('Property 1: Storage Mode Consistency', function () {
        it('preserves local storage mode across all recommendations', function () {
            // Generate multiple test cases with local storage mode
            $testCases = generateStorageModeTestCases('local');

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock AI service
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(true);

                $this->neuronAI
                    ->shouldReceive('getRecommendedTimeout')
                    ->andReturn(15);

                $this->neuronAI
                    ->shouldReceive('generateMultipleRecommendations')
                    ->andReturn([
                        new \App\ValueObjects\Recommendation(
                            type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                            priority: \App\Enums\Priority::HIGH,
                            action: 'Speed Training',
                            reasoning: 'Test recommendation',
                            expectedOutcomes: [],
                            risks: [],
                            confidenceScore: 0.9,
                            source: 'ai',
                            storageMode: 'local'
                        ),
                    ]);

                $recommendations = $this->service->getTrainingRecommendations($context);

                // Property assertion: All recommendations must preserve storage mode
                expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
                foreach ($recommendations as $rec) {
                    expect($rec->storageMode)->toBe('local', 'Recommendation should preserve local storage mode');
                    expect($rec->isLocalMode())->toBeTrue('Recommendation should identify as local mode');
                }
            }
        });

        it('preserves account storage mode across all recommendations', function () {
            // Generate multiple test cases with account storage mode
            $testCases = generateStorageModeTestCases('account');

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock AI service
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(true);

                $this->neuronAI
                    ->shouldReceive('getRecommendedTimeout')
                    ->andReturn(15);

                $this->neuronAI
                    ->shouldReceive('generateMultipleRecommendations')
                    ->andReturn([
                        new \App\ValueObjects\Recommendation(
                            type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                            priority: \App\Enums\Priority::HIGH,
                            action: 'Stamina Training',
                            reasoning: 'Test recommendation',
                            expectedOutcomes: [],
                            risks: [],
                            confidenceScore: 0.85,
                            source: 'ai',
                            storageMode: 'account'
                        ),
                    ]);

                $recommendations = $this->service->getTrainingRecommendations($context);

                // Property assertion: All recommendations must preserve storage mode
                expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
                foreach ($recommendations as $rec) {
                    expect($rec->storageMode)->toBe('account', 'Recommendation should preserve account storage mode');
                    expect($rec->isAccountMode())->toBeTrue('Recommendation should identify as account mode');
                }
            }
        });

        it('preserves storage mode in rule-based fallback', function () {
            // Test both storage modes with rule-based fallback
            $storageModes = ['local', 'account'];

            foreach ($storageModes as $storageMode) {
                $context = new TrainingContext(
                    turnNumber: 20,
                    phase: CareerPhase::CLASSIC,
                    stats: new CharacterStats(500, 450, 480, 400, 420),
                    spAvailable: 150,
                    energy: 70,
                    mood: Mood::GOOD,
                    acquiredSkills: [1, 2, 3],
                    skillHints: [],
                    deck: new SupportCardDeck([]),
                    facilityLevels: ['speed' => 2, 'stamina' => 2, 'power' => 2, 'guts' => 2, 'wisdom' => 2],
                    upcomingRaces: [],
                    scenario: null,
                    storageMode: $storageMode,
                    careerRunId: $storageMode === 'local' ? 'uuid-123' : 456
                );

                // Mock AI unavailable
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(false);

                // Mock rule-based advisor
                $this->ruleBasedAdvisor
                    ->shouldReceive('recommendTrainingFacility')
                    ->with($context)
                    ->andReturn(new \App\ValueObjects\Recommendation(
                        type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                        priority: \App\Enums\Priority::MEDIUM,
                        action: 'Power Training',
                        reasoning: 'Rule-based recommendation',
                        expectedOutcomes: [],
                        risks: [],
                        confidenceScore: null,
                        source: 'rule-based',
                        storageMode: $storageMode
                    ));

                $recommendations = $this->service->getTrainingRecommendations($context);

                // Property assertion: Storage mode preserved in fallback
                foreach ($recommendations as $rec) {
                    expect($rec->storageMode)->toBe($storageMode, "Rule-based recommendation should preserve {$storageMode} storage mode");
                }
            }
        });
    });

    /**
     * Property 2: Response Time Bounds
     *
     * All recommendations must be generated within specified time bounds based on AI provider.
     * - Local AI (Ollama): <2 seconds (p95)
     * - Cloud AI (Bedrock): <5 seconds (p95)
     * - Rule-based fallback: <500ms (p95)
     *
     * Validates: Requirements 3.1, 4.1
     */
    describe('Property 2: Response Time Bounds', function () {
        it('generates AI recommendations within 2 seconds for local provider', function () {
            // Test multiple scenarios with varying complexity
            $testCases = generateResponseTimeTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock AI service (simulating local Ollama)
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(true);

                $this->neuronAI
                    ->shouldReceive('getRecommendedTimeout')
                    ->andReturn($testCase['expectedTimeout']);

                // Simulate AI response time (should be fast for local)
                $this->neuronAI
                    ->shouldReceive('generateMultipleRecommendations')
                    ->andReturnUsing(function () {
                        // Simulate processing time (local AI should be fast)
                        usleep(rand(100000, 500000)); // 100-500ms

                        return [
                            new \App\ValueObjects\Recommendation(
                                type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                                priority: \App\Enums\Priority::HIGH,
                                action: 'Speed Training',
                                reasoning: 'AI recommendation',
                                expectedOutcomes: [],
                                risks: [],
                                confidenceScore: 0.9
                            ),
                        ];
                    });

                $startTime = microtime(true);
                $recommendations = $this->service->getTrainingRecommendations($context);
                $duration = microtime(true) - $startTime;

                // Property assertion: Response time within bounds
                expect($duration)->toBeLessThan(2.0, "Local AI recommendations should complete within 2 seconds (took {$duration}s)");
                expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
                expect($recommendations->count())->toBeGreaterThan(0);
            }
        });

        it('generates rule-based recommendations within 500ms', function () {
            // Test multiple scenarios
            $testCases = generateResponseTimeTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock AI unavailable (force rule-based)
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(false);

                // Mock rule-based advisor (should be very fast)
                $this->ruleBasedAdvisor
                    ->shouldReceive('recommendTrainingFacility')
                    ->with($context)
                    ->andReturnUsing(function () {
                        // Simulate minimal processing time
                        usleep(rand(10000, 50000)); // 10-50ms

                        return new \App\ValueObjects\Recommendation(
                            type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                            priority: \App\Enums\Priority::MEDIUM,
                            action: 'Stamina Training',
                            reasoning: 'Rule-based recommendation',
                            expectedOutcomes: [],
                            risks: [],
                            confidenceScore: null,
                            source: 'rule-based'
                        );
                    });

                $startTime = microtime(true);
                $recommendations = $this->service->getTrainingRecommendations($context);
                $duration = microtime(true) - $startTime;

                // Property assertion: Rule-based should be very fast
                expect($duration)->toBeLessThan(0.5, "Rule-based recommendations should complete within 500ms (took {$duration}s)");
                expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
                expect($recommendations->count())->toBeGreaterThan(0);
            }
        });

        it('respects timeout configuration based on complexity', function () {
            // Test different complexity levels
            $complexityTests = [
                [
                    'name' => 'simple',
                    'context' => new TrainingContext(
                        turnNumber: 5,
                        phase: CareerPhase::JUNIOR,
                        stats: new CharacterStats(150, 120, 130, 100, 110),
                        spAvailable: 30,
                        energy: 90,
                        mood: Mood::NORMAL,
                        acquiredSkills: [1],
                        skillHints: [],
                        deck: new SupportCardDeck([]),
                        facilityLevels: ['speed' => 1, 'stamina' => 1, 'power' => 1, 'guts' => 1, 'wisdom' => 1],
                        upcomingRaces: [],
                        scenario: null
                    ),
                    'expectedTimeout' => 10,
                ],
                [
                    'name' => 'medium',
                    'context' => new TrainingContext(
                        turnNumber: 35,
                        phase: CareerPhase::CLASSIC,
                        stats: new CharacterStats(700, 600, 650, 550, 600),
                        spAvailable: 200,
                        energy: 65,
                        mood: Mood::GOOD,
                        acquiredSkills: array_fill(0, 10, 1),
                        skillHints: [],
                        deck: new SupportCardDeck([]),
                        facilityLevels: ['speed' => 3, 'stamina' => 3, 'power' => 3, 'guts' => 3, 'wisdom' => 3],
                        upcomingRaces: [['id' => 1, 'distance' => 'medium', 'turn' => 38]],
                        scenario: null
                    ),
                    'expectedTimeout' => 15,
                ],
                [
                    'name' => 'complex',
                    'context' => new TrainingContext(
                        turnNumber: 65,
                        phase: CareerPhase::SENIOR,
                        stats: new CharacterStats(1150, 950, 900, 750, 850),
                        spAvailable: 350,
                        energy: 55,
                        mood: Mood::GREAT,
                        acquiredSkills: array_fill(0, 25, 1),
                        skillHints: [],
                        deck: new SupportCardDeck([]),
                        facilityLevels: ['speed' => 5, 'stamina' => 5, 'power' => 5, 'guts' => 4, 'wisdom' => 5],
                        upcomingRaces: [
                            ['id' => 1, 'distance' => 'long', 'turn' => 67],
                            ['id' => 2, 'distance' => 'medium', 'turn' => 70],
                            ['id' => 3, 'distance' => 'long', 'turn' => 72],
                        ],
                        scenario: 'ura_finale'
                    ),
                    'expectedTimeout' => 30,
                ],
            ];

            foreach ($complexityTests as $test) {
                $this->neuronAI
                    ->shouldReceive('isAvailable')
                    ->andReturn(true);

                // Property assertion: Timeout matches complexity
                $this->neuronAI
                    ->shouldReceive('getRecommendedTimeout')
                    ->once()
                    ->with($test['name'])
                    ->andReturn($test['expectedTimeout']);

                $this->neuronAI
                    ->shouldReceive('generateMultipleRecommendations')
                    ->once()
                    ->andReturn([
                        new \App\ValueObjects\Recommendation(
                            type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                            priority: \App\Enums\Priority::HIGH,
                            action: 'Training',
                            reasoning: 'Test',
                            expectedOutcomes: [],
                            risks: [],
                            confidenceScore: 0.9
                        ),
                    ]);

                $recommendations = $this->service->getTrainingRecommendations($test['context']);

                expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
            }
        });
    });

    /**
     * Property 9: Critical Alert Generation
     *
     * Critical alerts must be generated when character state meets alert conditions.
     * Alerts should be prioritized by severity (CRITICAL > HIGH > MEDIUM > LOW).
     *
     * Validates: Requirements 3.4
     */
    describe('Property 9: Critical Alert Generation', function () {
        beforeEach(function () {
            // Create real CriticalSituationDetector for property testing
            $this->criticalDetector = new \App\Services\CriticalSituationDetector(
                $this->mechanicsEngine
            );

            // Recreate service with critical detector
            $this->service = new TrainingAdvisoryService(
                $this->neuronAI,
                $this->ruleBasedAdvisor,
                $this->mechanicsEngine,
                $this->accuracyTracker,
                $this->criticalDetector,
                $this->recommendationCache,
                $this->performanceMonitor
            );
        });

        it('generates stamina crisis alerts when stamina is insufficient', function () {
            // Generate multiple test cases with low stamina
            $testCases = generateStaminaCrisisTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock stamina requirement calculations
                $this->mechanicsEngine
                    ->shouldReceive('calculateStaminaRequirement')
                    ->andReturnUsing(function ($distance, $style, $skills) {
                        // Return requirements based on distance and style
                        $baseRequirements = [
                            'sprint' => 375,
                            'mile' => 475,
                            'medium' => 650,
                            'long' => 925,
                        ];

                        $styleModifiers = [
                            'escape' => 1.0,
                            'lead' => 0.95,
                            'pace' => 0.85,
                            'chase' => 0.75,
                        ];

                        $base = $baseRequirements[$distance->value] ?? 600;
                        $modifier = $styleModifiers[$style->value] ?? 1.0;

                        return (int) ($base * $modifier);
                    });

                $alerts = $this->service->detectCriticalSituations($context);

                // Property assertion: Stamina crisis alert must be generated
                expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);

                $staminaAlert = $alerts->first(fn ($alert) => $alert->type === \App\Enums\AlertType::STAMINA_CRISIS);
                expect($staminaAlert)->not->toBeNull('Stamina crisis alert should be generated when stamina is insufficient');
                expect($staminaAlert->priority)->toBeIn([\App\Enums\Priority::CRITICAL, \App\Enums\Priority::HIGH]);
                expect($staminaAlert->actionItems)->not->toBeEmpty('Alert should include action items');
            }
        });

        it('generates SP shortage alerts when SP budget is low', function () {
            // Generate multiple test cases with low SP
            $testCases = generateSpShortageTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                $alerts = $this->service->detectCriticalSituations($context);

                // Property assertion: SP shortage alert must be generated
                expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);

                $spAlert = $alerts->first(fn ($alert) => $alert->type === \App\Enums\AlertType::SP_SHORTAGE);
                expect($spAlert)->not->toBeNull('SP shortage alert should be generated when SP is low');
                expect($spAlert->actionItems)->not->toBeEmpty('Alert should include action items');
            }
        });

        it('generates energy critical alerts when energy is below 40', function () {
            // Generate multiple test cases with low energy
            $testCases = generateEnergyCriticalTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock failure rate calculation
                $this->mechanicsEngine
                    ->shouldReceive('calculateFailureRate')
                    ->andReturnUsing(function ($energy, $numCards, $conditions) {
                        // Simple failure rate calculation based on energy
                        if ($energy >= 70) {
                            return 0.02;
                        }
                        if ($energy >= 50) {
                            return 0.05;
                        }
                        if ($energy >= 30) {
                            return 0.10;
                        }

                        return 0.20;
                    });

                $alerts = $this->service->detectCriticalSituations($context);

                // Property assertion: Energy critical alert must be generated
                expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);

                $energyAlert = $alerts->first(fn ($alert) => $alert->type === \App\Enums\AlertType::ENERGY_CRITICAL);
                expect($energyAlert)->not->toBeNull('Energy critical alert should be generated when energy is below 40');
                expect($energyAlert->priority)->toBeIn([\App\Enums\Priority::CRITICAL, \App\Enums\Priority::HIGH]);
                expect($energyAlert->turnsUntilCritical)->toBe(0, 'Energy critical is immediate');
            }
        });

        it('generates bond behind schedule alerts when bonds are low near turn 25', function () {
            // Generate multiple test cases with low bonds
            $testCases = generateBondBehindScheduleTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                $alerts = $this->service->detectCriticalSituations($context);

                // Property assertion: Bond alert must be generated
                expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);

                $bondAlert = $alerts->first(fn ($alert) => $alert->type === \App\Enums\AlertType::BOND_BEHIND_SCHEDULE);
                expect($bondAlert)->not->toBeNull('Bond behind schedule alert should be generated when bonds are low');
                expect($bondAlert->actionItems)->not->toBeEmpty('Alert should include action items');
            }
        });

        it('prioritizes alerts by severity (CRITICAL > HIGH > MEDIUM > LOW)', function () {
            // Create context with multiple critical situations
            $supportCards = [
                new \App\ValueObjects\SupportCard(1, 60, 'speed', 0, 'Speed Card'),
                new \App\ValueObjects\SupportCard(2, 65, 'stamina', 0, 'Stamina Card'),
            ];

            $context = new TrainingContext(
                turnNumber: 35,
                phase: CareerPhase::CLASSIC,
                stats: new CharacterStats(
                    speed: 850,
                    stamina: 300, // Low stamina
                    power: 720,
                    guts: 580,
                    wisdom: 690
                ),
                spAvailable: 25, // Low SP
                energy: 32, // Low energy
                mood: Mood::NORMAL,
                acquiredSkills: [1, 2],
                skillHints: [],
                deck: new SupportCardDeck($supportCards),
                facilityLevels: ['stamina' => 2],
                upcomingRaces: [
                    ['id' => 15, 'distance' => 'medium', 'turn' => 38],
                ],
                scenario: null
            );

            // Mock calculations
            $this->mechanicsEngine
                ->shouldReceive('calculateStaminaRequirement')
                ->andReturn(600, 570, 510, 450); // For different running styles

            $this->mechanicsEngine
                ->shouldReceive('calculateFailureRate')
                ->andReturn(0.12);

            $alerts = $this->service->detectCriticalSituations($context);

            // Property assertion: Alerts must be sorted by priority
            expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);
            expect($alerts->count())->toBeGreaterThan(0);

            // Convert priorities to numeric values
            $priorities = $alerts->pluck('priority.value')->toArray();
            $priorityValues = array_map(function ($priority) {
                return match ($priority) {
                    'critical' => 4,
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1,
                    default => 0,
                };
            }, $priorities);

            // Verify descending order
            $sortedValues = $priorityValues;
            rsort($sortedValues);
            expect($priorityValues)->toBe($sortedValues, 'Alerts should be sorted by priority (highest first)');

            // Verify first alert is highest priority
            if (count($priorityValues) > 0) {
                expect($priorityValues[0])->toBeGreaterThanOrEqual(3, 'First alert should be HIGH or CRITICAL priority');
            }
        });

        it('returns empty collection when no critical situations exist', function () {
            // Generate test cases with healthy character state
            $testCases = generateHealthyStateTestCases();

            foreach ($testCases as $testCase) {
                $context = $testCase['context'];

                // Mock calculations for healthy state
                $this->mechanicsEngine
                    ->shouldReceive('calculateStaminaRequirement')
                    ->andReturn(600); // Character has sufficient stamina

                $alerts = $this->service->detectCriticalSituations($context);

                // Property assertion: No alerts or only low-priority alerts
                expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);

                // If there are alerts, they should not be critical
                $criticalAlerts = $alerts->filter(fn ($alert) => $alert->priority === \App\Enums\Priority::CRITICAL);
                expect($criticalAlerts->count())->toBe(0, 'No CRITICAL alerts should exist for healthy state');
            }
        });

        it('handles multiple simultaneous critical situations correctly', function () {
            // Create worst-case scenario with multiple critical issues
            $supportCards = [
                new \App\ValueObjects\SupportCard(1, 50, 'speed', 0, 'Speed Card'),
                new \App\ValueObjects\SupportCard(2, 55, 'stamina', 0, 'Stamina Card'),
                new \App\ValueObjects\SupportCard(3, 45, 'power', 0, 'Power Card'),
            ];

            $context = new TrainingContext(
                turnNumber: 24, // Close to bond target
                phase: CareerPhase::JUNIOR,
                stats: new CharacterStats(
                    speed: 400,
                    stamina: 250, // Very low stamina
                    power: 380,
                    guts: 300,
                    wisdom: 350
                ),
                spAvailable: 15, // Very low SP
                energy: 25, // Very low energy
                mood: Mood::BAD, // Poor mood
                acquiredSkills: [1],
                skillHints: [],
                deck: new SupportCardDeck($supportCards),
                facilityLevels: ['stamina' => 1],
                upcomingRaces: [
                    ['id' => 10, 'distance' => 'mile', 'turn' => 26],
                ],
                scenario: null
            );

            // Mock calculations
            $this->mechanicsEngine
                ->shouldReceive('calculateStaminaRequirement')
                ->andReturn(475, 451, 404, 356); // For different running styles

            $this->mechanicsEngine
                ->shouldReceive('calculateFailureRate')
                ->andReturn(0.20);

            $alerts = $this->service->detectCriticalSituations($context);

            // Property assertion: Multiple alerts generated and properly prioritized
            expect($alerts)->toBeInstanceOf(\App\Collections\CriticalAlertCollection::class);
            expect($alerts->count())->toBeGreaterThanOrEqual(3, 'Multiple critical situations should generate multiple alerts');

            // Verify all expected alert types are present
            $alertTypes = $alerts->pluck('type.value')->toArray();
            expect($alertTypes)->toContain('stamina_crisis');
            expect($alertTypes)->toContain('sp_shortage');
            expect($alertTypes)->toContain('energy_critical');

            // Verify proper prioritization
            $priorities = $alerts->pluck('priority.value')->toArray();
            $priorityValues = array_map(function ($priority) {
                return match ($priority) {
                    'critical' => 4,
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1,
                    default => 0,
                };
            }, $priorities);

            // Should be in descending order
            $sortedValues = $priorityValues;
            rsort($sortedValues);
            expect($priorityValues)->toBe($sortedValues);
        });
    });
});

/**
 * Generate test cases for storage mode consistency testing
 *
 * @param  string  $storageMode  'local' or 'account'
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateStorageModeTestCases(string $storageMode): array
{
    $phases = [CareerPhase::JUNIOR, CareerPhase::CLASSIC, CareerPhase::SENIOR];
    $moods = [Mood::NORMAL, Mood::GOOD, Mood::GREAT];
    $testCases = [];

    foreach ($phases as $phase) {
        foreach ($moods as $mood) {
            $turnNumber = match ($phase) {
                CareerPhase::JUNIOR => rand(1, 24),
                CareerPhase::CLASSIC => rand(25, 48),
                CareerPhase::SENIOR => rand(49, 72),
                default => 20,
            };

            $testCases[] = [
                'context' => new TrainingContext(
                    turnNumber: $turnNumber,
                    phase: $phase,
                    stats: new CharacterStats(
                        speed: rand(200, 1200),
                        stamina: rand(200, 1000),
                        power: rand(200, 900),
                        guts: rand(200, 800),
                        wisdom: rand(200, 900)
                    ),
                    spAvailable: rand(50, 400),
                    energy: rand(40, 100),
                    mood: $mood,
                    acquiredSkills: array_fill(0, rand(0, 20), 1),
                    skillHints: [],
                    deck: new SupportCardDeck([]),
                    facilityLevels: [
                        'speed' => rand(1, 5),
                        'stamina' => rand(1, 5),
                        'power' => rand(1, 5),
                        'guts' => rand(1, 5),
                        'wisdom' => rand(1, 5),
                    ],
                    upcomingRaces: [],
                    scenario: null,
                    storageMode: $storageMode,
                    careerRunId: $storageMode === 'local' ? 'uuid-'.uniqid() : rand(1, 1000)
                ),
                'description' => "Phase: {$phase->value}, Mood: {$mood->value}, Storage: {$storageMode}",
            ];
        }
    }

    return $testCases;
}

/**
 * Generate test cases for response time testing
 *
 * @return array<array{context: TrainingContext, expectedTimeout: int, description: string}>
 */
function generateResponseTimeTestCases(): array
{
    return [
        [
            'context' => new TrainingContext(
                turnNumber: 8,
                phase: CareerPhase::JUNIOR,
                stats: new CharacterStats(180, 150, 160, 130, 140),
                spAvailable: 40,
                energy: 85,
                mood: Mood::NORMAL,
                acquiredSkills: [1, 2],
                skillHints: [],
                deck: new SupportCardDeck([]),
                facilityLevels: ['speed' => 1, 'stamina' => 1, 'power' => 1, 'guts' => 1, 'wisdom' => 1],
                upcomingRaces: [],
                scenario: null
            ),
            'expectedTimeout' => 10,
            'description' => 'Early game, simple context',
        ],
        [
            'context' => new TrainingContext(
                turnNumber: 30,
                phase: CareerPhase::CLASSIC,
                stats: new CharacterStats(650, 550, 600, 500, 550),
                spAvailable: 180,
                energy: 70,
                mood: Mood::GOOD,
                acquiredSkills: array_fill(0, 8, 1),
                skillHints: [],
                deck: new SupportCardDeck([]),
                facilityLevels: ['speed' => 3, 'stamina' => 2, 'power' => 3, 'guts' => 2, 'wisdom' => 3],
                upcomingRaces: [['id' => 1, 'distance' => 'medium', 'turn' => 33]],
                scenario: null
            ),
            'expectedTimeout' => 15,
            'description' => 'Mid game, medium complexity',
        ],
        [
            'context' => new TrainingContext(
                turnNumber: 58,
                phase: CareerPhase::SENIOR,
                stats: new CharacterStats(1050, 900, 850, 700, 800),
                spAvailable: 320,
                energy: 60,
                mood: Mood::GREAT,
                acquiredSkills: array_fill(0, 18, 1),
                skillHints: [],
                deck: new SupportCardDeck([]),
                facilityLevels: ['speed' => 5, 'stamina' => 4, 'power' => 5, 'guts' => 4, 'wisdom' => 5],
                upcomingRaces: [
                    ['id' => 1, 'distance' => 'long', 'turn' => 60],
                    ['id' => 2, 'distance' => 'medium', 'turn' => 63],
                ],
                scenario: 'ura_finale'
            ),
            'expectedTimeout' => 30,
            'description' => 'Late game, complex context',
        ],
    ];
}

/**
 * Generate test cases for stamina crisis detection
 *
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateStaminaCrisisTestCases(): array
{
    $distances = [\App\Enums\RaceDistance::MEDIUM, \App\Enums\RaceDistance::LONG];
    $testCases = [];

    foreach ($distances as $distance) {
        // Low stamina scenarios
        $lowStamina = [250, 300, 350];

        foreach ($lowStamina as $stamina) {
            $testCases[] = [
                'context' => new TrainingContext(
                    turnNumber: 35,
                    phase: CareerPhase::CLASSIC,
                    stats: new CharacterStats(
                        speed: 850,
                        stamina: $stamina,
                        power: 720,
                        guts: 580,
                        wisdom: 690
                    ),
                    spAvailable: 180,
                    energy: 70,
                    mood: Mood::NORMAL,
                    acquiredSkills: [1, 2],
                    skillHints: [],
                    deck: new SupportCardDeck([
                        new \App\ValueObjects\SupportCard(1, 60, 'speed', 0, 'Speed Card'),
                        new \App\ValueObjects\SupportCard(2, 65, 'stamina', 0, 'Stamina Card'),
                    ]),
                    facilityLevels: ['stamina' => 2],
                    upcomingRaces: [
                        ['id' => 15, 'distance' => $distance->value, 'turn' => 38],
                    ],
                    scenario: null
                ),
                'description' => "Low stamina ({$stamina}) for {$distance->value} race",
            ];
        }
    }

    return $testCases;
}

/**
 * Generate test cases for SP shortage detection
 *
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateSpShortageTestCases(): array
{
    $lowSpValues = [15, 25, 35];
    $testCases = [];

    foreach ($lowSpValues as $sp) {
        $testCases[] = [
            'context' => new TrainingContext(
                turnNumber: 40,
                phase: CareerPhase::CLASSIC,
                stats: new CharacterStats(
                    speed: 750,
                    stamina: 650,
                    power: 700,
                    guts: 600,
                    wisdom: 680
                ),
                spAvailable: $sp,
                energy: 75,
                mood: Mood::NORMAL,
                acquiredSkills: [1, 2, 3],
                skillHints: [
                    ['skill_id' => 10, 'level' => 3],
                    ['skill_id' => 15, 'level' => 2],
                ],
                deck: new SupportCardDeck([]),
                facilityLevels: ['speed' => 3, 'stamina' => 3, 'power' => 3, 'guts' => 2, 'wisdom' => 3],
                upcomingRaces: [],
                scenario: null
            ),
            'description' => "Low SP budget ({$sp})",
        ];
    }

    return $testCases;
}

/**
 * Generate test cases for energy critical detection
 *
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateEnergyCriticalTestCases(): array
{
    $criticalEnergyLevels = [25, 30, 35];
    $testCases = [];

    foreach ($criticalEnergyLevels as $energy) {
        $testCases[] = [
            'context' => new TrainingContext(
                turnNumber: 30,
                phase: CareerPhase::CLASSIC,
                stats: new CharacterStats(
                    speed: 650,
                    stamina: 550,
                    power: 600,
                    guts: 500,
                    wisdom: 580
                ),
                spAvailable: 150,
                energy: $energy,
                mood: Mood::NORMAL,
                acquiredSkills: [1, 2, 3],
                skillHints: [],
                deck: new SupportCardDeck([
                    new \App\ValueObjects\SupportCard(1, 70, 'speed', 0, 'Speed Card'),
                    new \App\ValueObjects\SupportCard(2, 65, 'stamina', 0, 'Stamina Card'),
                    new \App\ValueObjects\SupportCard(3, 60, 'power', 0, 'Power Card'),
                ]),
                facilityLevels: ['speed' => 3, 'stamina' => 2, 'power' => 3, 'guts' => 2, 'wisdom' => 2],
                upcomingRaces: [],
                scenario: null
            ),
            'description' => "Critical energy level ({$energy})",
        ];
    }

    return $testCases;
}

/**
 * Generate test cases for bond behind schedule detection
 *
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateBondBehindScheduleTestCases(): array
{
    $turnNumbers = [24, 25, 26];
    $testCases = [];

    foreach ($turnNumbers as $turn) {
        $testCases[] = [
            'context' => new TrainingContext(
                turnNumber: $turn,
                phase: CareerPhase::JUNIOR,
                stats: new CharacterStats(
                    speed: 400,
                    stamina: 350,
                    power: 380,
                    guts: 320,
                    wisdom: 360
                ),
                spAvailable: 80,
                energy: 70,
                mood: Mood::NORMAL,
                acquiredSkills: [1],
                skillHints: [],
                deck: new SupportCardDeck([
                    new \App\ValueObjects\SupportCard(1, 50, 'speed', 0, 'Speed Card'),
                    new \App\ValueObjects\SupportCard(2, 55, 'stamina', 0, 'Stamina Card'),
                    new \App\ValueObjects\SupportCard(3, 45, 'power', 0, 'Power Card'),
                ]),
                facilityLevels: ['speed' => 2, 'stamina' => 2, 'power' => 2, 'guts' => 1, 'wisdom' => 2],
                upcomingRaces: [],
                scenario: null
            ),
            'description' => "Low bonds near turn {$turn}",
        ];
    }

    return $testCases;
}

/**
 * Generate test cases for healthy character state (no critical situations)
 *
 * @return array<array{context: TrainingContext, description: string}>
 */
function generateHealthyStateTestCases(): array
{
    return [
        [
            'context' => new TrainingContext(
                turnNumber: 30,
                phase: CareerPhase::CLASSIC,
                stats: new CharacterStats(
                    speed: 750,
                    stamina: 700,
                    power: 720,
                    guts: 650,
                    wisdom: 680
                ),
                spAvailable: 250,
                energy: 80,
                mood: Mood::GOOD,
                acquiredSkills: [1, 2, 3, 4, 5],
                skillHints: [],
                deck: new SupportCardDeck([
                    new \App\ValueObjects\SupportCard(1, 85, 'speed', 2, 'Speed Card'),
                    new \App\ValueObjects\SupportCard(2, 82, 'stamina', 2, 'Stamina Card'),
                    new \App\ValueObjects\SupportCard(3, 80, 'power', 1, 'Power Card'),
                ]),
                facilityLevels: ['speed' => 3, 'stamina' => 3, 'power' => 3, 'guts' => 3, 'wisdom' => 3],
                upcomingRaces: [
                    ['id' => 15, 'distance' => 'medium', 'turn' => 35],
                ],
                scenario: null
            ),
            'description' => 'Healthy state - good stats, high energy, good bonds',
        ],
        [
            'context' => new TrainingContext(
                turnNumber: 50,
                phase: CareerPhase::SENIOR,
                stats: new CharacterStats(
                    speed: 950,
                    stamina: 850,
                    power: 880,
                    guts: 750,
                    wisdom: 820
                ),
                spAvailable: 320,
                energy: 75,
                mood: Mood::GREAT,
                acquiredSkills: [1, 2, 3, 4, 5, 6, 7, 8],
                skillHints: [],
                deck: new SupportCardDeck([
                    new \App\ValueObjects\SupportCard(1, 95, 'speed', 3, 'Speed Card'),
                    new \App\ValueObjects\SupportCard(2, 90, 'stamina', 3, 'Stamina Card'),
                    new \App\ValueObjects\SupportCard(3, 88, 'power', 2, 'Power Card'),
                ]),
                facilityLevels: ['speed' => 4, 'stamina' => 4, 'power' => 4, 'guts' => 4, 'wisdom' => 4],
                upcomingRaces: [
                    ['id' => 20, 'distance' => 'long', 'turn' => 55],
                ],
                scenario: null
            ),
            'description' => 'Healthy state - excellent stats, high energy, excellent bonds',
        ],
        [
            'context' => new TrainingContext(
                turnNumber: 15,
                phase: CareerPhase::JUNIOR,
                stats: new CharacterStats(
                    speed: 350,
                    stamina: 320,
                    power: 340,
                    guts: 300,
                    wisdom: 330
                ),
                spAvailable: 120,
                energy: 85,
                mood: Mood::GOOD,
                acquiredSkills: [1, 2],
                skillHints: [],
                deck: new SupportCardDeck([
                    new \App\ValueObjects\SupportCard(1, 65, 'speed', 0, 'Speed Card'),
                    new \App\ValueObjects\SupportCard(2, 60, 'stamina', 0, 'Stamina Card'),
                ]),
                facilityLevels: ['speed' => 2, 'stamina' => 2, 'power' => 2, 'guts' => 2, 'wisdom' => 2],
                upcomingRaces: [],
                scenario: null
            ),
            'description' => 'Healthy state - early game, on track',
        ],
    ];
}
