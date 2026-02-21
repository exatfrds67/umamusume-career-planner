<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\Recommendation;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Property 14: Offline Fallback Behavior
 *
 * Validates: Requirements 3.9, 4.2
 *
 * When AI services are unavailable, the system must fall back to rule-based recommendations.
 */
describe('Property 14: Offline Fallback Behavior', function () {
    it('falls back to rule-based recommendations when AI service throws exception', function () {
        $context = generateOfflineTestContext();

        // Mock NeuronAIService to throw exception (simulating AI unavailability)
        $neuronMock = $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $neuronMock->shouldReceive('isAvailable')->andReturn(true);
        $neuronMock->shouldReceive('getRecommendedTimeout')->andReturn(5);
        $neuronMock->shouldReceive('generateMultipleRecommendations')
            ->andThrow(new \RuntimeException('AI service unavailable'));

        // Mock RuleBasedAdvisor to return a recommendation
        $ruleBasedMock = $this->mock(RuleBasedAdvisor::class);
        $ruleBasedMock->shouldReceive('recommendTrainingFacility')
            ->andReturn(new Recommendation(
                type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                priority: \App\Enums\Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Rule-based recommendation',
                expectedOutcomes: ['Speed gain: +40'],
                risks: [],
                confidenceScore: 0.85
            ));

        // Mock other dependencies
        $this->mock(GameMechanicsEngine::class);
        $this->mock(PredictionAccuracyTracker::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        $advisoryService = app(TrainingAdvisoryService::class);
        $recommendations = $advisoryService->getTrainingRecommendations($context);

        // Verify we got recommendations (from rule-based fallback)
        expect($recommendations)->not->toBeEmpty();
        expect($recommendations->first())->toBeInstanceOf(Recommendation::class);
        expect($recommendations->first()->reasoning)->toContain('Rule-based');
    })->group('property', 'offline-fallback');

    it('uses rule-based advisor when AI service is not available', function () {
        $context = generateOfflineTestContext();

        // Mock NeuronAIService to return false for isAvailable()
        $neuronMock = $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $neuronMock->shouldReceive('isAvailable')->andReturn(false);

        // Mock RuleBasedAdvisor to return a recommendation
        $ruleBasedMock = $this->mock(RuleBasedAdvisor::class);
        $ruleBasedMock->shouldReceive('recommendTrainingFacility')
            ->andReturn(new Recommendation(
                type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                priority: \App\Enums\Priority::HIGH,
                action: 'Stamina Training',
                reasoning: 'Rule-based recommendation for offline mode',
                expectedOutcomes: ['Stamina gain: +35'],
                risks: [],
                confidenceScore: 0.80
            ));

        // Mock other dependencies
        $this->mock(GameMechanicsEngine::class);
        $this->mock(PredictionAccuracyTracker::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        $advisoryService = app(TrainingAdvisoryService::class);
        $recommendations = $advisoryService->getTrainingRecommendations($context);

        // Verify we got recommendations (from rule-based advisor)
        expect($recommendations)->not->toBeEmpty();
        expect($recommendations->first())->toBeInstanceOf(Recommendation::class);
        expect($recommendations->first()->reasoning)->toContain('Rule-based');
    })->group('property', 'offline-fallback');

    it('always provides recommendations regardless of AI availability', function () {
        $context = generateOfflineTestContext();

        // Test with AI available
        $neuronMock = $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $neuronMock->shouldReceive('isAvailable')->andReturn(true);
        $neuronMock->shouldReceive('getRecommendedTimeout')->andReturn(5);
        $neuronMock->shouldReceive('generateMultipleRecommendations')
            ->andReturn([
                new Recommendation(
                    type: \App\Enums\RecommendationType::TRAINING_FACILITY,
                    priority: \App\Enums\Priority::HIGH,
                    action: 'Power Training',
                    reasoning: 'AI-powered recommendation',
                    expectedOutcomes: ['Power gain: +45'],
                    risks: [],
                    confidenceScore: 0.92
                ),
            ]);

        $this->mock(RuleBasedAdvisor::class);
        $this->mock(GameMechanicsEngine::class);
        $this->mock(PredictionAccuracyTracker::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        $advisoryService = app(TrainingAdvisoryService::class);
        $recommendations = $advisoryService->getTrainingRecommendations($context);

        expect($recommendations)->not->toBeEmpty();
        expect($recommendations->first())->toBeInstanceOf(Recommendation::class);
    })->group('property', 'offline-fallback');
});

/**
 * Helper: Generate a basic TrainingContext for testing
 */
function generateOfflineTestContext(): TrainingContext
{
    return new TrainingContext(
        turnNumber: 15,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 400,
            stamina: 350,
            power: 380,
            guts: 320,
            wisdom: 360
        ),
        spAvailable: 150,
        energy: 80,
        mood: Mood::NORMAL,
        acquiredSkills: [],
        skillHints: [],
        deck: new SupportCardDeck([]),
        facilityLevels: [
            'speed' => 2,
            'stamina' => 2,
            'power' => 2,
            'guts' => 2,
            'wisdom' => 2,
        ],
        upcomingRaces: [],
        scenario: null,
        storageMode: 'account',
        careerRunId: 1
    );
}
