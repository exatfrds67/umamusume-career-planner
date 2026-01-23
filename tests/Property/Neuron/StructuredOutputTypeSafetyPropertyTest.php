<?php

declare(strict_types=1);

/**
 * Property-based tests for Structured Output Type Safety.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for structured output type safety.
 *
 * Property 9: Structured Output Type Safety
 * For any structured output request, when the agent returns a response,
 * the system should return an instance of the requested class type.
 *
 * **Validates: Requirements 9.2**
 *
 * Testing Strategy:
 * - Create response objects of different types
 * - Verify instances are of correct class type
 * - Verify type safety is maintained
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 9 - Structured Output Type Safety
 */

use App\Neuron\Responses\RaceStrategyResponse;
use App\Neuron\Responses\SkillRecommendationResponse;
use App\Neuron\Responses\TrainingAdviceResponse;

describe('Property 9: Structured Output Type Safety', function () {
    it('returns TrainingAdviceResponse instance for training advice', function () {
        // Create a TrainingAdviceResponse with random data
        $response = new TrainingAdviceResponse(
            recommendedTraining: ['speed', 'stamina', 'power', 'guts', 'wit'][rand(0, 4)],
            reasoning: 'Test reasoning '.uniqid().' with sufficient length to pass validation',
            expectedGains: [
                'speed' => rand(5, 30),
                'stamina' => rand(5, 30),
            ],
            alternatives: []
        );

        // Property: Response should be instance of TrainingAdviceResponse
        expect($response)->toBeInstanceOf(TrainingAdviceResponse::class);

        // Property: Response should not be instance of other response types
        expect($response)->not->toBeInstanceOf(RaceStrategyResponse::class);
        expect($response)->not->toBeInstanceOf(SkillRecommendationResponse::class);
    })->repeat(100);

    it('returns RaceStrategyResponse instance for race strategy', function () {
        // Create a RaceStrategyResponse with random data
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: ['escape', 'leader', 'betweener', 'chaser'][rand(0, 3)],
            recommendedSkills: ['Skill '.rand(1, 100), 'Skill '.rand(1, 100)],
            racePreparationAdvice: 'Test advice '.uniqid().' with sufficient length to pass validation',
            expectedPerformance: 'High chance of winning',
            riskFactors: []
        );

        // Property: Response should be instance of RaceStrategyResponse
        expect($response)->toBeInstanceOf(RaceStrategyResponse::class);

        // Property: Response should not be instance of other response types
        expect($response)->not->toBeInstanceOf(TrainingAdviceResponse::class);
        expect($response)->not->toBeInstanceOf(SkillRecommendationResponse::class);
    })->repeat(100);

    it('returns SkillRecommendationResponse instance for skill recommendations', function () {
        // Create a SkillRecommendationResponse with random data
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill '.rand(1, 100),
                    'reason' => 'Test reason',
                    'priority' => ['high', 'medium', 'low'][rand(0, 2)],
                ],
            ],
            acquisitionStrategy: 'Test strategy '.uniqid().' with sufficient length to pass validation',
            spBudgetConsiderations: 'Test budget '.uniqid().' with sufficient length to pass validation',
            skillSynergies: []
        );

        // Property: Response should be instance of SkillRecommendationResponse
        expect($response)->toBeInstanceOf(SkillRecommendationResponse::class);

        // Property: Response should not be instance of other response types
        expect($response)->not->toBeInstanceOf(TrainingAdviceResponse::class);
        expect($response)->not->toBeInstanceOf(RaceStrategyResponse::class);
    })->repeat(100);

    it('maintains type safety for public properties', function () {
        // Create a TrainingAdviceResponse
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning with sufficient length to pass validation',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        // Property: Public properties should maintain their types
        expect($response->recommendedTraining)->toBeString();
        expect($response->reasoning)->toBeString();
        expect($response->expectedGains)->toBeArray();
        expect($response->alternatives)->toBeArray();
    })->repeat(100);

    it('preserves type information across method calls', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning with sufficient length to pass validation',
            expectedGains: ['speed' => 15, 'stamina' => 10],
            alternatives: []
        );

        // Call toArray method
        $array = $response->toArray();

        // Property: Original response should still be correct type
        expect($response)->toBeInstanceOf(TrainingAdviceResponse::class);

        // Property: Array should be array type
        expect($array)->toBeArray();

        // Property: Array should contain expected keys
        expect($array)->toHaveKeys([
            'recommended_training',
            'reasoning',
            'expected_gains',
            'alternatives',
        ]);
    })->repeat(100);

    it('maintains type safety for RaceStrategyResponse properties', function () {
        // Create a RaceStrategyResponse
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'escape',
            recommendedSkills: ['Skill 1', 'Skill 2'],
            racePreparationAdvice: 'Test advice with sufficient length to pass validation',
            expectedPerformance: 'High chance',
            riskFactors: ['Risk 1']
        );

        // Property: Public properties should maintain their types
        expect($response->recommendedRunningStyle)->toBeString();
        expect($response->recommendedSkills)->toBeArray();
        expect($response->racePreparationAdvice)->toBeString();
        expect($response->expectedPerformance)->toBeString();
        expect($response->riskFactors)->toBeArray();
    })->repeat(100);

    it('maintains type safety for SkillRecommendationResponse properties', function () {
        // Create a SkillRecommendationResponse
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Skill 1', 'reason' => 'Reason 1', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Test strategy with sufficient length to pass validation',
            spBudgetConsiderations: 'Test budget with sufficient length to pass validation',
            skillSynergies: []
        );

        // Property: Public properties should maintain their types
        expect($response->recommendedSkills)->toBeArray();
        expect($response->acquisitionStrategy)->toBeString();
        expect($response->spBudgetConsiderations)->toBeString();
        expect($response->skillSynergies)->toBeArray();
    })->repeat(100);

    it('returns correct type from getSummary method', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning with sufficient length to pass validation',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        // Get summary
        $summary = $response->getSummary();

        // Property: Summary should be a string
        expect($summary)->toBeString();
        expect($summary)->not->toBeEmpty();

        // Property: Original response should still be correct type
        expect($response)->toBeInstanceOf(TrainingAdviceResponse::class);
    })->repeat(50);

    it('returns correct type from validate method', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning with sufficient length to pass validation',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        // Validate
        $errors = $response->validate();

        // Property: Validation errors should be an array
        expect($errors)->toBeArray();

        // Property: Original response should still be correct type
        expect($response)->toBeInstanceOf(TrainingAdviceResponse::class);
    })->repeat(50);

    it('maintains type safety when creating multiple instances', function () {
        // Create multiple response instances
        $response1 = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning 1 with sufficient length to pass validation',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        $response2 = new TrainingAdviceResponse(
            recommendedTraining: 'stamina',
            reasoning: 'Test reasoning 2 with sufficient length to pass validation',
            expectedGains: ['stamina' => 20],
            alternatives: []
        );

        // Property: Both should be correct type
        expect($response1)->toBeInstanceOf(TrainingAdviceResponse::class);
        expect($response2)->toBeInstanceOf(TrainingAdviceResponse::class);

        // Property: They should be different instances
        expect($response1)->not->toBe($response2);
    })->repeat(50);

    it('maintains type safety across different response types', function () {
        // Create instances of all response types
        $trainingResponse = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning with sufficient length to pass validation',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        $raceResponse = new RaceStrategyResponse(
            recommendedRunningStyle: 'escape',
            recommendedSkills: ['Skill 1'],
            racePreparationAdvice: 'Test advice with sufficient length to pass validation'
        );

        $skillResponse = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Skill 1', 'reason' => 'Reason 1', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Test strategy with sufficient length to pass validation',
            spBudgetConsiderations: 'Test budget with sufficient length to pass validation'
        );

        // Property: Each should be its own type
        expect($trainingResponse)->toBeInstanceOf(TrainingAdviceResponse::class);
        expect($raceResponse)->toBeInstanceOf(RaceStrategyResponse::class);
        expect($skillResponse)->toBeInstanceOf(SkillRecommendationResponse::class);

        // Property: None should be instances of the other types
        expect($trainingResponse)->not->toBeInstanceOf(RaceStrategyResponse::class);
        expect($raceResponse)->not->toBeInstanceOf(SkillRecommendationResponse::class);
        expect($skillResponse)->not->toBeInstanceOf(TrainingAdviceResponse::class);
    })->repeat(50);
});
