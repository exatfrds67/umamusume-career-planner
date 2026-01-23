<?php

declare(strict_types=1);

/**
 * Property-based tests for Response Parsing.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for response parsing.
 *
 * Property 8: Response Parsing
 * For any agent response, when parsed by a service class, the system
 * should extract structured data matching the expected response schema.
 *
 * **Validates: Requirements 7.4**
 *
 * Testing Strategy:
 * - Generate random response objects with various data
 * - Parse responses using service methods
 * - Verify parsed data matches expected schema
 * - Verify all required fields are present
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 8 - Response Parsing
 */

use App\Neuron\Responses\TrainingAdviceResponse;
use App\Services\Neuron\TrainingAdvisorService;

describe('Property 8: Response Parsing', function () {
    it('parses TrainingAdviceResponse into structured array', function () {
        // Create a response with random data
        $response = new TrainingAdviceResponse(
            recommendedTraining: ['speed', 'stamina', 'power', 'guts', 'wit'][rand(0, 4)],
            reasoning: 'Test reasoning '.uniqid(),
            expectedGains: [
                'speed' => rand(5, 30),
                'stamina' => rand(5, 30),
            ],
            alternatives: [
                ['type' => 'rest', 'reason' => 'Low energy'],
            ]
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Parsed data should be an array
        expect($parsed)->toBeArray();

        // Property: Parsed data should contain all required fields
        expect($parsed)->toHaveKeys([
            'recommended_training',
            'reasoning',
            'expected_gains',
            'alternatives',
            'summary',
            'validation_errors',
        ]);

        // Property: Parsed data should match original response data
        expect($parsed['recommended_training'])->toBe($response->recommendedTraining);
        expect($parsed['reasoning'])->toBe($response->reasoning);
        expect($parsed['expected_gains'])->toBe($response->expectedGains);
        expect($parsed['alternatives'])->toBe($response->alternatives);
    })->repeat(100);

    it('extracts recommended training from response', function () {
        // Create a response with random training type
        $trainingType = ['speed', 'stamina', 'power', 'guts', 'wit'][rand(0, 4)];
        $response = new TrainingAdviceResponse(
            recommendedTraining: $trainingType,
            reasoning: 'Test reasoning',
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Recommended training should be extracted correctly
        expect($parsed['recommended_training'])->toBe($trainingType);
        expect($parsed['recommended_training'])->toBeString();
        expect($parsed['recommended_training'])->not->toBeEmpty();
    })->repeat(100);

    it('extracts reasoning from response', function () {
        // Create a response with random reasoning
        $reasoning = 'Reasoning '.uniqid().' with length '.rand(20, 100);
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: $reasoning,
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Reasoning should be extracted correctly
        expect($parsed['reasoning'])->toBe($reasoning);
        expect($parsed['reasoning'])->toBeString();
        expect($parsed['reasoning'])->not->toBeEmpty();
    })->repeat(100);

    it('extracts expected gains from response', function () {
        // Create a response with random expected gains
        $expectedGains = [
            'speed' => rand(5, 30),
            'stamina' => rand(5, 30),
            'power' => rand(5, 30),
        ];
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: $expectedGains,
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Expected gains should be extracted correctly
        expect($parsed['expected_gains'])->toBe($expectedGains);
        expect($parsed['expected_gains'])->toBeArray();
        expect($parsed['expected_gains'])->not->toBeEmpty();

        // Property: Each gain value should be an integer
        foreach ($parsed['expected_gains'] as $stat => $gain) {
            expect($gain)->toBeInt();
            expect($gain)->toBeGreaterThan(0);
        }
    })->repeat(100);

    it('extracts alternatives from response', function () {
        // Create a response with random alternatives
        $alternatives = [
            ['type' => 'rest', 'reason' => 'Low energy'],
            ['type' => 'stamina', 'reason' => 'Alternative option'],
        ];
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: ['speed' => 10],
            alternatives: $alternatives
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Alternatives should be extracted correctly
        expect($parsed['alternatives'])->toBe($alternatives);
        expect($parsed['alternatives'])->toBeArray();
    })->repeat(100);

    it('includes summary in parsed response', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning for speed training',
            expectedGains: ['speed' => 15, 'stamina' => 5],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Summary should be present
        expect($parsed)->toHaveKey('summary');
        expect($parsed['summary'])->toBeString();
    })->repeat(50);

    it('includes validation errors in parsed response', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Validation errors should be present
        expect($parsed)->toHaveKey('validation_errors');
        expect($parsed['validation_errors'])->toBeArray();
    })->repeat(50);

    it('parses response consistently across multiple calls', function () {
        // Create a response
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response multiple times
        $parsed1 = $service->parseResponse($response);
        $parsed2 = $service->parseResponse($response);
        $parsed3 = $service->parseResponse($response);

        // Property: Multiple parses should produce identical results
        expect($parsed1)->toBe($parsed2);
        expect($parsed2)->toBe($parsed3);
    })->repeat(50);

    it('handles responses with empty alternatives', function () {
        // Create a response with no alternatives
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: ['speed' => 10],
            alternatives: []
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Alternatives should be an empty array
        expect($parsed['alternatives'])->toBeArray();
        expect($parsed['alternatives'])->toBeEmpty();
    })->repeat(50);

    it('preserves data types during parsing', function () {
        // Create a response with specific data types
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Test reasoning',
            expectedGains: [
                'speed' => 15,
                'stamina' => 10,
            ],
            alternatives: [
                ['type' => 'rest', 'reason' => 'Low energy'],
            ]
        );

        // Create service instance
        $service = new TrainingAdvisorService(
            new \App\Models\Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Parse response
        $parsed = $service->parseResponse($response);

        // Property: Data types should be preserved
        expect($parsed['recommended_training'])->toBeString();
        expect($parsed['reasoning'])->toBeString();
        expect($parsed['expected_gains'])->toBeArray();
        expect($parsed['alternatives'])->toBeArray();

        // Property: Numeric values should remain numeric
        foreach ($parsed['expected_gains'] as $gain) {
            expect($gain)->toBeInt();
        }
    })->repeat(50);
});
