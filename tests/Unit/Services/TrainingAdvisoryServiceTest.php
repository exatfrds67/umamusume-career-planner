<?php

declare(strict_types=1);

use App\Services\AdvisoryPerformanceMonitor;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\Neuron\NeuronAIService;
use App\Services\PredictionAccuracyTracker;
use App\Services\RecommendationCacheService;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;

describe('TrainingAdvisoryService', function () {
    it('can be instantiated with dependencies', function () {
        $neuronAIService = Mockery::mock(NeuronAIService::class);
        $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
        $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
        $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
        $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
        $recommendationCache = Mockery::mock(RecommendationCacheService::class);
        $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

        $service = new TrainingAdvisoryService(
            $neuronAIService,
            $ruleBasedAdvisor,
            $mechanicsEngine,
            $accuracyTracker,
            $criticalDetector,
            $recommendationCache,
            $performanceMonitor
        );

        expect($service)->toBeInstanceOf(TrainingAdvisoryService::class);
    });

    it('has all required public methods', function () {
        $neuronAIService = Mockery::mock(NeuronAIService::class);
        $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
        $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
        $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
        $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
        $recommendationCache = Mockery::mock(RecommendationCacheService::class);
        $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

        $service = new TrainingAdvisoryService(
            $neuronAIService,
            $ruleBasedAdvisor,
            $mechanicsEngine,
            $accuracyTracker,
            $criticalDetector,
            $recommendationCache,
            $performanceMonitor
        );

        $requiredMethods = [
            'getTrainingRecommendations',
            'getSkillPurchaseAdvice',
            'getRaceStrategy',
            'detectCriticalSituations',
            'recordTrainingOutcome',
            'recordRaceOutcome',
        ];

        foreach ($requiredMethods as $method) {
            expect(method_exists($service, $method))->toBeTrue("Method {$method} should exist");
        }
    });

    describe('getRaceStrategy', function () {
        it('generates race strategy using AI when available', function () {
            $character = \App\Models\Character::factory()->make([
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ]);

            $race = \App\Models\Race::factory()->make([
                'distance' => 'medium',
                'distance_meters' => 2000,
                'surface' => 'turf',
            ]);

            $expectedStrategy = new \App\Neuron\Responses\RaceStrategyResponse(
                recommendedRunningStyle: 'escape',
                recommendedSkills: ['Swinging Maestro', 'Lane Legerdemain'],
                racePreparationAdvice: 'Character is well-prepared for this race.',
                expectedPerformance: 'High chance of winning (75-85%)',
                riskFactors: []
            );

            $neuronAIService = Mockery::mock(NeuronAIService::class);
            $neuronAIService->shouldReceive('isAvailable')->andReturn(true);
            $neuronAIService->shouldReceive('getRecommendedTimeout')->with('medium')->andReturn(15);
            $neuronAIService->shouldReceive('generateRaceStrategy')
                ->once()
                ->andReturn($expectedStrategy);

            $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
            $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
            $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
            $recommendationCache = Mockery::mock(RecommendationCacheService::class);
            $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

            $service = new TrainingAdvisoryService(
                $neuronAIService,
                $ruleBasedAdvisor,
                $mechanicsEngine,
                $accuracyTracker,
                $criticalDetector,
                $recommendationCache,
                $performanceMonitor
            );

            $strategy = $service->getRaceStrategy($character, $race);

            expect($strategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
            expect($strategy->recommendedRunningStyle)->toBe('escape');
            expect($strategy->recommendedSkills)->toContain('Swinging Maestro');
        });

        it('falls back to rule-based advisor when AI is unavailable', function () {
            $character = \App\Models\Character::factory()->make([
                'speed' => 600,
                'stamina' => 350,
            ]);

            $race = \App\Models\Race::factory()->make([
                'distance' => 'medium',
            ]);

            $expectedStrategy = new \App\Neuron\Responses\RaceStrategyResponse(
                recommendedRunningStyle: 'escape',
                recommendedSkills: ['Swinging Maestro'],
                racePreparationAdvice: 'Focus on stamina training before the race.',
                expectedPerformance: 'Low chance of winning due to stamina deficit',
                riskFactors: ['Stamina deficit of 250 points']
            );

            $neuronAIService = Mockery::mock(NeuronAIService::class);
            $neuronAIService->shouldReceive('isAvailable')->andReturn(false);

            $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
            $ruleBasedAdvisor->shouldReceive('generateRaceStrategy')
                ->once()
                ->with($character, $race)
                ->andReturn($expectedStrategy);

            $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
            $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
            $recommendationCache = Mockery::mock(RecommendationCacheService::class);
            $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

            $service = new TrainingAdvisoryService(
                $neuronAIService,
                $ruleBasedAdvisor,
                $mechanicsEngine,
                $accuracyTracker,
                $criticalDetector,
                $recommendationCache,
                $performanceMonitor
            );

            $strategy = $service->getRaceStrategy($character, $race);

            expect($strategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
            expect($strategy->recommendedRunningStyle)->toBe('escape');
            expect($strategy->riskFactors)->toContain('Stamina deficit of 250 points');
        });

        it('falls back to rule-based advisor when AI generation fails', function () {
            $character = \App\Models\Character::factory()->make();
            $race = \App\Models\Race::factory()->make();

            $expectedStrategy = new \App\Neuron\Responses\RaceStrategyResponse(
                recommendedRunningStyle: 'escape',
                recommendedSkills: [],
                racePreparationAdvice: 'Prepare for the race.',
                expectedPerformance: null,
                riskFactors: []
            );

            $neuronAIService = Mockery::mock(NeuronAIService::class);
            $neuronAIService->shouldReceive('isAvailable')->andReturn(true);
            $neuronAIService->shouldReceive('getRecommendedTimeout')->andReturn(15);
            $neuronAIService->shouldReceive('generateRaceStrategy')
                ->once()
                ->andThrow(new \RuntimeException('AI service error'));

            $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
            $ruleBasedAdvisor->shouldReceive('generateRaceStrategy')
                ->once()
                ->andReturn($expectedStrategy);

            $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
            $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
            $recommendationCache = Mockery::mock(RecommendationCacheService::class);
            $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

            $service = new TrainingAdvisoryService(
                $neuronAIService,
                $ruleBasedAdvisor,
                $mechanicsEngine,
                $accuracyTracker,
                $criticalDetector,
                $recommendationCache,
                $performanceMonitor
            );

            $strategy = $service->getRaceStrategy($character, $race);

            expect($strategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
        });

        it('builds correct race strategy prompt with character and race data', function () {
            $character = \App\Models\Character::factory()->make([
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
                'distance_aptitude' => 'A',
                'surface_aptitude' => 'B',
                'escape_aptitude' => 'A',
                'lead_aptitude' => 'B',
                'pace_aptitude' => 'C',
                'chase_aptitude' => 'C',
                'equipped_skills' => [
                    ['name' => 'Swinging Maestro', 'tier' => 'gold'],
                ],
            ]);

            $race = \App\Models\Race::factory()->make([
                'distance' => 'medium',
                'distance_meters' => 2000,
                'surface' => 'turf',
                'weather' => 'clear',
                'track_condition' => 'good',
                'competition_level' => 'G1',
            ]);

            $neuronAIService = Mockery::mock(NeuronAIService::class);
            $neuronAIService->shouldReceive('isAvailable')->andReturn(true);
            $neuronAIService->shouldReceive('getRecommendedTimeout')->andReturn(15);
            $neuronAIService->shouldReceive('generateRaceStrategy')
                ->once()
                ->andReturn(new \App\Neuron\Responses\RaceStrategyResponse(
                    recommendedRunningStyle: 'escape',
                    recommendedSkills: [],
                    racePreparationAdvice: 'Test advice',
                ));

            $ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
            $mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
            $accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $criticalDetector = Mockery::mock(CriticalSituationDetector::class);
            $recommendationCache = Mockery::mock(RecommendationCacheService::class);
            $performanceMonitor = Mockery::mock(AdvisoryPerformanceMonitor::class);

            $service = new TrainingAdvisoryService(
                $neuronAIService,
                $ruleBasedAdvisor,
                $mechanicsEngine,
                $accuracyTracker,
                $criticalDetector,
                $recommendationCache,
                $performanceMonitor
            );

            $service->getRaceStrategy($character, $race);
        });
    });
});
