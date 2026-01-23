<?php

declare(strict_types=1);

/**
 * Property-based tests for Structured Output Validation.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for structured output validation.
 *
 * Property 10: Structured Output Validation
 * For any structured output response, when validation rules are defined,
 * the system should validate the response against those rules and reject
 * invalid responses.
 *
 * **Validates: Requirements 9.3**
 *
 * Testing Strategy:
 * - Generate valid and invalid response objects
 * - Validate responses using validate() method
 * - Verify validation catches invalid data
 * - Verify validation passes valid data
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 10 - Structured Output Validation
 */

use App\Neuron\Responses\RaceStrategyResponse;
use App\Neuron\Responses\SkillRecommendationResponse;
use App\Neuron\Responses\TrainingAdviceResponse;

describe('Property 10: Structured Output Validation', function () {
    it('validates TrainingAdviceResponse with valid data returns no errors', function () {
        // Create a valid response
        $response = new TrainingAdviceResponse(
            recommendedTraining: ['speed', 'stamina', 'power', 'guts', 'wit'][rand(0, 4)],
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: [
                'speed' => rand(5, 30),
                'stamina' => rand(5, 30),
            ],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Valid response should have no validation errors
        expect($errors)->toBeArray();
        expect($errors)->toBeEmpty();
    })->repeat(100);

    it('validates TrainingAdviceResponse with invalid training type returns error', function () {
        // Create a response with invalid training type
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'invalid_training_type_'.rand(1, 1000),
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Invalid training type should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_training');
    })->repeat(100);

    it('validates TrainingAdviceResponse with short reasoning returns error', function () {
        // Create a response with reasoning too short
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Short', // Less than 20 characters
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Short reasoning should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('reasoning');
    })->repeat(50);

    it('validates TrainingAdviceResponse with long reasoning returns error', function () {
        // Create a response with reasoning too long
        $longReasoning = str_repeat('a', 501); // More than 500 characters
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: $longReasoning,
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Long reasoning should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('reasoning');
    })->repeat(50);

    it('validates TrainingAdviceResponse with empty expected gains returns error', function () {
        // Create a response with empty expected gains
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: [],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Empty expected gains should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('expected_gains');
    })->repeat(50);

    it('validates TrainingAdviceResponse with invalid stat name returns error', function () {
        // Create a response with invalid stat name
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: [
                'invalid_stat_'.rand(1, 1000) => 10,
            ],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Invalid stat name should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('expected_gains');
    })->repeat(100);

    it('validates TrainingAdviceResponse with negative stat gain returns error', function () {
        // Create a response with negative stat gain
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: [
                'speed' => -10,
            ],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Negative stat gain should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('expected_gains');
    })->repeat(50);

    it('validates RaceStrategyResponse with valid data returns no errors', function () {
        // Create a valid response
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: ['escape', 'leader', 'betweener', 'chaser'][rand(0, 3)],
            recommendedSkills: ['Skill '.rand(1, 100), 'Skill '.rand(101, 200)],
            racePreparationAdvice: 'This is valid advice with sufficient length to pass validation rules'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Valid response should have no validation errors
        expect($errors)->toBeArray();
        expect($errors)->toBeEmpty();
    })->repeat(100);

    it('validates RaceStrategyResponse with invalid running style returns error', function () {
        // Create a response with invalid running style
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'invalid_style_'.rand(1, 1000),
            recommendedSkills: ['Skill 1'],
            racePreparationAdvice: 'This is valid advice with sufficient length to pass validation rules'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Invalid running style should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_running_style');
    })->repeat(100);

    it('validates RaceStrategyResponse with empty skills returns error', function () {
        // Create a response with no skills
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'escape',
            recommendedSkills: [],
            racePreparationAdvice: 'This is valid advice with sufficient length to pass validation rules'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Empty skills should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    })->repeat(50);

    it('validates RaceStrategyResponse with short advice returns error', function () {
        // Create a response with advice too short
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'escape',
            recommendedSkills: ['Skill 1'],
            racePreparationAdvice: 'Short'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Short advice should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('race_preparation_advice');
    })->repeat(50);

    it('validates SkillRecommendationResponse with valid data returns no errors', function () {
        // Create a valid response
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill '.rand(1, 100),
                    'reason' => 'Valid reason for this skill',
                    'priority' => ['high', 'medium', 'low'][rand(0, 2)],
                ],
            ],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Valid response should have no validation errors
        expect($errors)->toBeArray();
        expect($errors)->toBeEmpty();
    })->repeat(100);

    it('validates SkillRecommendationResponse with empty skills returns error', function () {
        // Create a response with no skills
        $response = new SkillRecommendationResponse(
            recommendedSkills: [],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Empty skills should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    })->repeat(50);

    it('validates SkillRecommendationResponse with missing skill keys returns error', function () {
        // Create a response with skill missing required keys
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill 1',
                    // Missing 'reason' and 'priority'
                ],
            ],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Missing keys should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    })->repeat(50);

    it('validates SkillRecommendationResponse with invalid priority returns error', function () {
        // Create a response with invalid priority
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill 1',
                    'reason' => 'Valid reason',
                    'priority' => 'invalid_priority_'.rand(1, 1000),
                ],
            ],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Invalid priority should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    })->repeat(100);

    it('validates SkillRecommendationResponse with short strategy returns error', function () {
        // Create a response with strategy too short
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill 1',
                    'reason' => 'Valid reason',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Short',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Short strategy should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('acquisition_strategy');
    })->repeat(50);

    it('validates SkillRecommendationResponse with short budget advice returns error', function () {
        // Create a response with budget advice too short
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill 1',
                    'reason' => 'Valid reason',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'Short'
        );

        // Validate response
        $errors = $response->validate();

        // Property: Short budget advice should produce validation error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('sp_budget_considerations');
    })->repeat(50);

    it('validates multiple errors are returned when multiple fields are invalid', function () {
        // Create a response with multiple invalid fields
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'invalid_type',
            reasoning: 'Short',
            expectedGains: [],
            alternatives: []
        );

        // Validate response
        $errors = $response->validate();

        // Property: Multiple errors should be returned
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect(count($errors))->toBeGreaterThan(1);
    })->repeat(50);

    it('validates alternatives structure in TrainingAdviceResponse', function () {
        // Create a response with invalid alternatives structure
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'This is a valid reasoning with sufficient length to pass validation rules',
            expectedGains: ['speed' => 10],
            alternatives: [
                [
                    'training' => 'invalid_type',
                    'reason' => 'Some reason',
                ],
            ]
        );

        // Validate response
        $errors = $response->validate();

        // Property: Invalid alternative training type should produce error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('alternatives');
    })->repeat(50);

    it('validates skill synergies structure in SkillRecommendationResponse', function () {
        // Create a response with invalid synergies structure
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Skill 1',
                    'reason' => 'Valid reason',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'This is a valid strategy with sufficient length to pass validation rules',
            spBudgetConsiderations: 'This is valid budget advice with sufficient length to pass validation',
            skillSynergies: [
                [
                    'skills' => [], // Empty skills array
                    'benefit' => 'Some benefit',
                ],
            ]
        );

        // Validate response
        $errors = $response->validate();

        // Property: Empty synergy skills should produce error
        expect($errors)->toBeArray();
        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('skill_synergies');
    })->repeat(50);
});
