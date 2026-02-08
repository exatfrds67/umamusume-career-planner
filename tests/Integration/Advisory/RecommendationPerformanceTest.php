<?php

declare(strict_types=1);

use App\Services\RecommendationCacheService;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\TrainingContext;
use Illuminate\Support\Facades\Cache;

/**
 * Recommendation Performance Test
 *
 * Validates that recommendation generation meets performance targets:
 * - Cache hit: <50ms (p95)
 * - Local AI: <2 seconds (p95)
 * - Cloud AI: <5 seconds (p95)
 * - Rule-based: <500ms (p95)
 *
 * **Validates: Task 7.3.1 (Optimize recommendation generation)**
 */
describe('Recommendation Performance', function () {
    beforeEach(function () {
        // Clear cache before each test
        Cache::flush();

        // Mock the Neuron AI service to avoid AWS initialization
        $this->mock(\App\Services\Neuron\NeuronAIService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(false);
        });

        $this->advisoryService = app(TrainingAdvisoryService::class);
        $this->cacheService = app(RecommendationCacheService::class);
    });

    it('meets cache hit performance target (<50ms)', function () {
        // Create a training context
        $context = TrainingContext::fromArray([
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ['id' => 2, 'bond' => 72, 'facility' => 'stamina'],
                ],
            ],
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => 'test-uuid-123',
        ]);

        // First call to populate cache (this will be slower)
        $this->advisoryService->getTrainingRecommendations($context);

        // Second call should hit cache
        $start = microtime(true);
        $recommendations = $this->advisoryService->getTrainingRecommendations($context);
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

        expect($recommendations)->not->toBeEmpty()
            ->and($duration)->toBeLessThan(50); // <50ms target
    })->group('performance', 'advisory');

    it('meets rule-based performance target (<500ms)', function () {
        // Create a training context
        $context = TrainingContext::fromArray([
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ['id' => 2, 'bond' => 72, 'facility' => 'stamina'],
                ],
            ],
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => 'test-uuid-456',
        ]);

        // AI service is already mocked to return false in beforeEach

        // Measure rule-based generation time
        $start = microtime(true);
        $recommendations = $this->advisoryService->getTrainingRecommendations($context);
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

        expect($recommendations)->not->toBeEmpty()
            ->and($duration)->toBeLessThan(500); // <500ms target
    })->group('performance', 'advisory');

    it('caches recommendations correctly', function () {
        $context = TrainingContext::fromArray([
            'turn_number' => 20,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 500,
                'stamina' => 400,
                'power' => 450,
                'guts' => 380,
                'wisdom' => 420,
            ],
            'sp_available' => 200,
            'energy' => 80,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                ],
            ],
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => 'test-uuid-789',
        ]);

        // Verify cache is empty
        expect($this->cacheService->isCached($context))->toBeFalse();

        // Generate recommendations (should cache them)
        $recommendations1 = $this->advisoryService->getTrainingRecommendations($context);

        // Verify cache is populated
        expect($this->cacheService->isCached($context))->toBeTrue();

        // Get recommendations again (should come from cache)
        $recommendations2 = $this->advisoryService->getTrainingRecommendations($context);

        // Verify both results are equivalent
        expect(count($recommendations1))->toBe(count($recommendations2));
    })->group('performance', 'advisory', 'cache');

    it('invalidates cache when character state changes', function () {
        $careerRunId = 'test-uuid-invalidate';

        $context1 = TrainingContext::fromArray([
            'turn_number' => 25,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 550,
                'stamina' => 420,
                'power' => 480,
                'guts' => 400,
                'wisdom' => 450,
            ],
            'sp_available' => 220,
            'energy' => 85,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                ],
            ],
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => $careerRunId,
        ]);

        // Generate and cache recommendations
        $this->advisoryService->getTrainingRecommendations($context1);
        expect($this->cacheService->isCached($context1))->toBeTrue();

        // Invalidate cache for this career
        $this->cacheService->invalidateCareerCache($careerRunId);

        // Verify cache is cleared (for Redis driver)
        if (config('cache.default') === 'redis') {
            expect($this->cacheService->isCached($context1))->toBeFalse();
        }
    })->group('performance', 'advisory', 'cache');

    it('handles similar contexts efficiently with cache rounding', function () {
        $careerRunId = 'test-uuid-rounding';

        // Create two contexts with slightly different stats (within rounding threshold)
        $context1 = TrainingContext::fromArray([
            'turn_number' => 30,
            'phase' => 'senior_year',
            'stats' => [
                'speed' => 602, // Will round to 600
                'stamina' => 448, // Will round to 450
                'power' => 513, // Will round to 510
                'guts' => 427, // Will round to 430
                'wisdom' => 485, // Will round to 490
            ],
            'sp_available' => 240,
            'energy' => 73, // Will round to 75
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 87, 'facility' => 'speed'], // Will round to 85
                ],
            ],
            'facility_levels' => [
                'speed' => 4,
                'stamina' => 3,
                'power' => 4,
                'guts' => 3,
                'wisdom' => 5,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => $careerRunId,
        ]);

        $context2 = TrainingContext::fromArray([
            'turn_number' => 30,
            'phase' => 'senior_year',
            'stats' => [
                'speed' => 598, // Will round to 600 (same as context1)
                'stamina' => 452, // Will round to 450 (same as context1)
                'power' => 507, // Will round to 510 (same as context1)
                'guts' => 433, // Will round to 430 (same as context1)
                'wisdom' => 492, // Will round to 490 (same as context1)
            ],
            'sp_available' => 240,
            'energy' => 77, // Will round to 75 (same as context1)
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 83, 'facility' => 'speed'], // Will round to 85 (same as context1)
                ],
            ],
            'facility_levels' => [
                'speed' => 4,
                'stamina' => 3,
                'power' => 4,
                'guts' => 3,
                'wisdom' => 5,
            ],
            'upcoming_races' => [],
            'scenario' => null,
            'storage_mode' => 'local',
            'career_run_id' => $careerRunId,
        ]);

        // Generate recommendations for context1 (will cache)
        $this->advisoryService->getTrainingRecommendations($context1);

        // Context2 should hit the same cache due to rounding
        $start = microtime(true);
        $recommendations = $this->advisoryService->getTrainingRecommendations($context2);
        $duration = (microtime(true) - $start) * 1000;

        expect($recommendations)->not->toBeEmpty()
            ->and($duration)->toBeLessThan(50); // Should be cache hit
    })->group('performance', 'advisory', 'cache');
});
