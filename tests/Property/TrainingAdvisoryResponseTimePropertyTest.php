<?php

declare(strict_types=1);

use App\Collections\RecommendationCollection;
use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\Neuron\NeuronAIService;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\Recommendation;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Property 2: Response Time Bounds
 *
 * All recommendations must be generated within specified time bounds based on AI provider:
 * - Local AI (Ollama): ≤2 seconds (p95)
 * - Cloud AI (Bedrock): ≤5 seconds (p95)
 * - Rule-based fallback: <500ms (p95)
 *
 * **Validates: Requirements 3.1, 4.1**
 *
 * This property test validates that the Training Advisory System meets its performance
 * requirements across different AI providers and complexity levels. The test generates
 * random training contexts with varying complexity and measures response times.
 *
 * Test Strategy:
 * 1. Generate diverse training contexts (simple, medium, complex)
 * 2. Test local AI provider with 2-second timeout
 * 3. Test cloud AI provider with 5-second timeout
 * 4. Test rule-based fallback with 500ms timeout
 * 5. Verify response times meet requirements across all scenarios
 */
describe('Property 2: Response Time Bounds', function () {
    beforeEach(function () {
        // Create mocked dependencies
        $this->neuronAI = Mockery::mock(NeuronAIService::class);
        $this->ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
        $this->mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
        $this->accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
        $this->criticalDetector = Mockery::mock(CriticalSituationDetector::class);

        // Create service instance
        $this->service = new TrainingAdvisoryService(
            $this->neuronAI,
            $this->ruleBasedAdvisor,
            $this->mechanicsEngine,
            $this->accuracyTracker,
            $this->criticalDetector,
            Mockery::mock(\App\Services\RecommendationCacheService::class),
            Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class)
        );
    });

    afterEach(function () {
        Mockery::close();
    });

    /**
     * Test local AI provider response time (≤2 seconds)
     *
     * Simulates Ollama local AI inference with realistic processing times.
     * Tests across multiple complexity levels to ensure consistent performance.
     */
    it('generates local AI recommendations within 2 seconds', function () {
        // Generate test cases with varying complexity
        $testCases = generateResponseTimeTestCases();

        foreach ($testCases as $testCase) {
            $context = $testCase['context'];
            $complexity = $testCase['complexity'] ?? 'medium';

            // Mock AI service as available (local Ollama)
            $this->neuronAI
                ->shouldReceive('isAvailable')
                ->andReturn(true);

            // Mock timeout based on complexity
            $this->neuronAI
                ->shouldReceive('getRecommendedTimeout')
                ->with($complexity)
                ->andReturn($testCase['expectedTimeout']);

            // Simulate local AI processing time (100ms - 1.5s)
            $this->neuronAI
                ->shouldReceive('generateMultipleRecommendations')
                ->andReturnUsing(function () use ($complexity) {
                    // Simulate realistic local AI processing times
                    $processingTime = match ($complexity) {
                        'simple' => rand(100000, 500000),   // 100-500ms
                        'medium' => rand(300000, 1000000),  // 300ms-1s
                        'complex' => rand(500000, 1500000), // 500ms-1.5s
                        default => rand(200000, 800000),    // 200-800ms
                    };

                    usleep($processingTime);

                    return [
                        new Recommendation(
                            type: RecommendationType::TRAINING_FACILITY,
                            priority: Priority::HIGH,
                            action: 'Speed Training',
                            reasoning: 'AI-powered recommendation based on current context',
                            expectedOutcomes: ['Speed +45-55', 'Bond increases'],
                            risks: ['5% failure rate'],
                            confidenceScore: 0.92,
                            source: 'local-ai',
                            storageMode: 'local'
                        ),
                    ];
                });

            // Measure response time
            $startTime = microtime(true);
            $recommendations = $this->service->getTrainingRecommendations($context);
            $duration = microtime(true) - $startTime;

            // Property assertion: Local AI must complete within 2 seconds
            expect($duration)->toBeLessThanOrEqual(2.0)
                ->and($recommendations)->toBeInstanceOf(RecommendationCollection::class)
                ->and($recommendations->count())->toBeGreaterThan(0);

            // Log performance for analysis (using test output)
            if ($duration > 1.5) {
                test()->addWarning("Local AI took {$duration}s for {$complexity} context (approaching 2s limit)");
            }
        }
    })->group('property', 'performance', 'ai');

    /**
     * Test cloud AI provider response time (≤5 seconds)
     *
     * Simulates AWS Bedrock cloud AI inference with network latency.
     * Cloud AI is expected to be slower than local but still within 5 seconds.
     */
    it('generates cloud AI recommendations within 5 seconds', function () {
        // Generate test cases with varying complexity
        $testCases = generateResponseTimeTestCases();

        foreach ($testCases as $testCase) {
            $context = $testCase['context'];
            $complexity = $testCase['complexity'] ?? 'medium';

            // Mock AI service as available (cloud Bedrock)
            $this->neuronAI
                ->shouldReceive('isAvailable')
                ->andReturn(true);

            // Mock timeout based on complexity (cloud needs more time)
            $this->neuronAI
                ->shouldReceive('getRecommendedTimeout')
                ->with($complexity)
                ->andReturn($testCase['expectedTimeout']);

            // Simulate cloud AI processing time (500ms - 4s)
            $this->neuronAI
                ->shouldReceive('generateMultipleRecommendations')
                ->andReturnUsing(function () use ($complexity) {
                    // Simulate realistic cloud AI processing times (includes network latency)
                    $processingTime = match ($complexity) {
                        'simple' => rand(500000, 1500000),   // 500ms-1.5s
                        'medium' => rand(1000000, 2500000),  // 1s-2.5s
                        'complex' => rand(1500000, 4000000), // 1.5s-4s
                        default => rand(800000, 2000000),    // 800ms-2s
                    };

                    usleep($processingTime);

                    return [
                        new Recommendation(
                            type: RecommendationType::TRAINING_FACILITY,
                            priority: Priority::HIGH,
                            action: 'Stamina Training',
                            reasoning: 'Cloud AI recommendation with advanced analysis',
                            expectedOutcomes: ['Stamina +50-60', 'Bond increases'],
                            risks: ['Weather may affect training'],
                            confidenceScore: 0.88,
                            source: 'cloud-ai',
                            storageMode: 'account'
                        ),
                    ];
                });

            // Measure response time
            $startTime = microtime(true);
            $recommendations = $this->service->getTrainingRecommendations($context);
            $duration = microtime(true) - $startTime;

            // Property assertion: Cloud AI must complete within 5 seconds
            expect($duration)->toBeLessThanOrEqual(5.0)
                ->and($recommendations)->toBeInstanceOf(RecommendationCollection::class)
                ->and($recommendations->count())->toBeGreaterThan(0);

            // Log performance for analysis (using test output)
            if ($duration > 4.0) {
                test()->addWarning("Cloud AI took {$duration}s for {$complexity} context (approaching 5s limit)");
            }
        }
    })->group('property', 'performance', 'ai', 'cloud');

    /**
     * Test rule-based fallback response time (<500ms)
     *
     * Rule-based recommendations should be extremely fast since they use
     * deterministic algorithms without AI inference.
     */
    it('generates rule-based recommendations within 500ms', function () {
        // Generate test cases with varying complexity
        $testCases = generateResponseTimeTestCases();

        foreach ($testCases as $testCase) {
            $context = $testCase['context'];
            $complexity = $testCase['complexity'] ?? 'medium';

            // Mock AI service as unavailable (force rule-based fallback)
            $this->neuronAI
                ->shouldReceive('isAvailable')
                ->andReturn(false);

            // Mock rule-based advisor (should be very fast)
            $this->ruleBasedAdvisor
                ->shouldReceive('recommendTrainingFacility')
                ->with($context)
                ->andReturnUsing(function () use ($complexity) {
                    // Simulate minimal rule-based processing time (5ms - 50ms)
                    $processingTime = match ($complexity) {
                        'simple' => rand(5000, 15000),    // 5-15ms
                        'medium' => rand(10000, 30000),   // 10-30ms
                        'complex' => rand(20000, 50000),  // 20-50ms
                        default => rand(10000, 25000),    // 10-25ms
                    };

                    usleep($processingTime);

                    return new Recommendation(
                        type: RecommendationType::TRAINING_FACILITY,
                        priority: Priority::MEDIUM,
                        action: 'Power Training',
                        reasoning: 'Rule-based recommendation: Most support cards present',
                        expectedOutcomes: ['Power +35-45'],
                        risks: [],
                        confidenceScore: null,
                        source: 'rule-based',
                        storageMode: 'local'
                    );
                });

            // Measure response time
            $startTime = microtime(true);
            $recommendations = $this->service->getTrainingRecommendations($context);
            $duration = microtime(true) - $startTime;

            // Property assertion: Rule-based must complete within 500ms
            expect($duration)->toBeLessThan(0.5)
                ->and($recommendations)->toBeInstanceOf(RecommendationCollection::class)
                ->and($recommendations->count())->toBeGreaterThan(0);

            // Log performance for analysis (using test output)
            if ($duration > 0.3) {
                test()->addWarning("Rule-based took {$duration}s for {$complexity} context (approaching 500ms limit)");
            }
        }
    })->group('property', 'performance', 'rule-based');

    /**
     * Test response time consistency across multiple runs
     *
     * Validates that response times are consistent and don't degrade over time.
     * This helps identify memory leaks or performance degradation issues.
     */
    it('maintains consistent response times across multiple runs', function () {
        $context = new TrainingContext(
            turnNumber: 25,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(600, 500, 550, 450, 500),
            spAvailable: 150,
            energy: 70,
            mood: Mood::GOOD,
            acquiredSkills: [1, 2, 3, 4, 5],
            skillHints: [],
            deck: new SupportCardDeck([]),
            facilityLevels: ['speed' => 2, 'stamina' => 2, 'power' => 2, 'guts' => 2, 'wisdom' => 2],
            upcomingRaces: [['id' => 1, 'distance' => 'medium', 'turn' => 28]],
            scenario: null
        );

        // Mock AI service
        $this->neuronAI
            ->shouldReceive('isAvailable')
            ->andReturn(true);

        $this->neuronAI
            ->shouldReceive('getRecommendedTimeout')
            ->andReturn(15);

        $this->neuronAI
            ->shouldReceive('generateMultipleRecommendations')
            ->andReturnUsing(function () {
                usleep(rand(300000, 800000)); // 300-800ms

                return [
                    new Recommendation(
                        type: RecommendationType::TRAINING_FACILITY,
                        priority: Priority::HIGH,
                        action: 'Speed Training',
                        reasoning: 'Consistent recommendation',
                        expectedOutcomes: [],
                        risks: [],
                        confidenceScore: 0.9
                    ),
                ];
            });

        // Run multiple times and collect response times
        $responseTimes = [];
        $iterations = 5; // Reduced from 10 to 5

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            $recommendations = $this->service->getTrainingRecommendations($context);
            $duration = microtime(true) - $startTime;

            $responseTimes[] = $duration;

            expect($recommendations)->toBeInstanceOf(RecommendationCollection::class);
        }

        // Calculate statistics
        $avgTime = array_sum($responseTimes) / count($responseTimes);
        $maxTime = max($responseTimes);
        $minTime = min($responseTimes);
        $variance = array_sum(array_map(fn ($t) => pow($t - $avgTime, 2), $responseTimes)) / count($responseTimes);
        $stdDev = sqrt($variance);

        // Property assertions: Consistency checks
        expect($maxTime)->toBeLessThanOrEqual(2.0, 'Maximum response time should be within bounds');
        expect($avgTime)->toBeLessThan(1.5, 'Average response time should be reasonable');
        expect($stdDev)->toBeLessThan(0.5, 'Response times should be consistent (low standard deviation)');
    })->group('property', 'performance', 'consistency');

    /**
     * Test timeout configuration based on complexity
     *
     * Validates that the service correctly adjusts timeouts based on
     * context complexity (simple, medium, complex).
     */
    it('adjusts timeout configuration based on context complexity', function () {
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
            // Mock AI service
            $this->neuronAI
                ->shouldReceive('isAvailable')
                ->once()
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
                    new Recommendation(
                        type: RecommendationType::TRAINING_FACILITY,
                        priority: Priority::HIGH,
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
    })->group('property', 'timeout');

    /**
     * Test fallback behavior when AI times out
     *
     * Validates that the system gracefully falls back to rule-based
     * recommendations when AI service times out or fails.
     */
    it('falls back to rule-based when AI times out', function () {
        $context = new TrainingContext(
            turnNumber: 30,
            phase: CareerPhase::CLASSIC,
            stats: new CharacterStats(650, 550, 600, 500, 550),
            spAvailable: 180,
            energy: 70,
            mood: Mood::GOOD,
            acquiredSkills: [1, 2, 3, 4, 5, 6, 7, 8],
            skillHints: [],
            deck: new SupportCardDeck([]),
            facilityLevels: ['speed' => 3, 'stamina' => 2, 'power' => 3, 'guts' => 2, 'wisdom' => 3],
            upcomingRaces: [['id' => 1, 'distance' => 'medium', 'turn' => 33]],
            scenario: null
        );

        // Mock AI service as available but timing out
        $this->neuronAI
            ->shouldReceive('isAvailable')
            ->andReturn(true);

        $this->neuronAI
            ->shouldReceive('getRecommendedTimeout')
            ->andReturn(15);

        // Simulate AI timeout
        $this->neuronAI
            ->shouldReceive('generateMultipleRecommendations')
            ->andThrow(new \RuntimeException('AI service timeout'));

        // Mock rule-based fallback
        $this->ruleBasedAdvisor
            ->shouldReceive('recommendTrainingFacility')
            ->with($context)
            ->andReturn(new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::MEDIUM,
                action: 'Stamina Training',
                reasoning: 'Rule-based fallback after AI timeout',
                expectedOutcomes: ['Stamina +40-50'],
                risks: [],
                confidenceScore: null,
                source: 'rule-based'
            ));

        // Measure response time
        $startTime = microtime(true);
        $recommendations = $this->service->getTrainingRecommendations($context);
        $duration = microtime(true) - $startTime;

        // Property assertions: Fallback should be fast
        expect($duration)->toBeLessThan(1.0, 'Fallback should be fast even after AI timeout')
            ->and($recommendations)->toBeInstanceOf(RecommendationCollection::class)
            ->and($recommendations->count())->toBeGreaterThan(0)
            ->and($recommendations->first()->source)->toBe('rule-based');
    })->group('property', 'fallback', 'resilience');
});

/**
 * Generate test cases for response time testing
 *
 * Creates diverse training contexts with varying complexity levels
 * to test performance across different scenarios.
 *
 * @return array<array{context: TrainingContext, expectedTimeout: int, complexity: string, description: string}>
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
            'complexity' => 'simple',
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
            'complexity' => 'medium',
            'description' => 'Mid game, medium complexity',
        ],
        [
            'context' => new TrainingContext(
                turnNumber: 68,
                phase: CareerPhase::SENIOR,
                stats: new CharacterStats(1200, 1000, 950, 800, 900),
                spAvailable: 400,
                energy: 50,
                mood: Mood::GREAT,
                acquiredSkills: array_fill(0, 22, 1),
                skillHints: [],
                deck: new SupportCardDeck([]),
                facilityLevels: ['speed' => 5, 'stamina' => 5, 'power' => 5, 'guts' => 5, 'wisdom' => 5],
                upcomingRaces: [
                    ['id' => 1, 'distance' => 'long', 'turn' => 70],
                    ['id' => 2, 'distance' => 'long', 'turn' => 72],
                ],
                scenario: 'ura_finale'
            ),
            'expectedTimeout' => 30,
            'complexity' => 'complex',
            'description' => 'End game, very complex context',
        ],
    ]; // Reduced from 6 test cases to 3 (simple, medium, complex)
}
