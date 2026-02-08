<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Property 13: Phase-Specific Goal Tracking
 *
 * Validates: Requirements 3.6
 *
 * Phase-specific milestones must be tracked and reported correctly.
 */
describe('Property 13: Phase-Specific Goal Tracking', function () {
    beforeEach(function () {
        // Mock the dependencies to avoid AWS credential issues
        $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $this->mock(\App\Services\RuleBasedAdvisor::class);
        $this->mock(\App\Services\GameMechanicsEngine::class);
        $this->mock(\App\Services\PredictionAccuracyTracker::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        $this->advisoryService = app(TrainingAdvisoryService::class);
    });

    it('tracks junior year milestones correctly', function () {
        $context = generateTrainingContextForPhase(CareerPhase::JUNIOR);
        $tracking = $this->advisoryService->getPhaseGoalTracking($context);

        expect($tracking->phase)->toBe('junior_year');
        expect($tracking->milestones)->toHaveKey('bondBuilding');
        expect($tracking->milestones)->toHaveKey('facilityLevels');
        expect($tracking->milestones['bondBuilding'])->toBeTrue();
        expect($tracking->milestones['facilityLevels'])->toBeTrue();
    })->group('property', 'phase-tracking');

    it('tracks classic year milestones correctly', function () {
        $context = generateTrainingContextForPhase(CareerPhase::CLASSIC);
        $tracking = $this->advisoryService->getPhaseGoalTracking($context);

        expect($tracking->phase)->toBe('classic_year');
        expect($tracking->milestones)->toHaveKey('statOptimization');
        expect($tracking->milestones)->toHaveKey('skillAcquisition');
        expect($tracking->milestones['statOptimization'])->toBeTrue();
        expect($tracking->milestones['skillAcquisition'])->toBeTrue();
    })->group('property', 'phase-tracking');

    it('tracks senior year milestones correctly', function () {
        $context = generateTrainingContextForPhase(CareerPhase::SENIOR);
        $tracking = $this->advisoryService->getPhaseGoalTracking($context);

        expect($tracking->phase)->toBe('senior_year');
        expect($tracking->milestones)->toHaveKey('finalPreparation');
        expect($tracking->milestones)->toHaveKey('uraReadiness');
        expect($tracking->milestones['finalPreparation'])->toBeTrue();
        expect($tracking->milestones['uraReadiness'])->toBeTrue();
    })->group('property', 'phase-tracking');

    it('handles all career phases', function () {
        $phases = [
            ['phase' => CareerPhase::JUNIOR, 'milestones' => ['bondBuilding', 'facilityLevels']],
            ['phase' => CareerPhase::CLASSIC, 'milestones' => ['statOptimization', 'skillAcquisition']],
            ['phase' => CareerPhase::SENIOR, 'milestones' => ['finalPreparation', 'uraReadiness']],
        ];

        foreach ($phases as $phaseData) {
            $context = generateTrainingContextForPhase($phaseData['phase']);
            $tracking = $this->advisoryService->getPhaseGoalTracking($context);

            foreach ($phaseData['milestones'] as $milestone) {
                expect($tracking->milestones)->toHaveKey($milestone);
                expect($tracking->milestones[$milestone])->toBeTrue();
            }
        }
    })->group('property', 'phase-tracking');
});

/**
 * Helper: Generate TrainingContext for a specific phase
 */
function generateTrainingContextForPhase(CareerPhase $phase): TrainingContext
{
    return new TrainingContext(
        turnNumber: 15,
        phase: $phase,
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
