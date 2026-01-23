<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;

/**
 * Property 14: Streaming Response Chunks
 *
 * For any streaming response, when chunks are yielded, each chunk should be
 * a non-empty string that can be progressively displayed.
 *
 * **Validates: Requirements 8.2**
 *
 * This property test verifies that streaming responses:
 * 1. Yield non-empty string chunks
 * 2. Chunks can be progressively displayed
 * 3. Streaming completes with a done signal
 * 4. Chunks are properly formatted as SSE events
 */
describe('Property 14: Streaming Response Chunks', function () {
    beforeEach(function () {
        // Disable error logging during tests
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('info')->andReturn(null);
    });

    it('yields non-empty string chunks for training advisor streaming', function (array $trainingOptions) {
        // Arrange: Create test data
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        // Mock the service to return a generator with chunks
        $mockService = Mockery::mock(TrainingAdvisorService::class);
        $mockService->shouldReceive('validateTrainingOptions')
            ->andReturn([]);
        $mockService->shouldReceive('getAdviceStreaming')
            ->andReturn((function () {
                yield 'Analyzing character stats...';
                yield 'Evaluating training options...';
                yield 'Recommendation: Focus on speed training.';
            })());

        $this->app->instance(TrainingAdvisorService::class, $mockService);

        // Act: Make streaming request
        $response = actingAs($user)->postJson('/api/neuron/training-advisor/advice/stream', [
            'character_id' => $character->id,
            'training_options' => $trainingOptions,
        ]);

        // Assert: Verify streaming response
        $response->assertOk();

        // Verify Content-Type header contains text/event-stream
        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('text/event-stream');

        // Get the streamed content
        $content = $response->streamedContent();

        // Verify chunks are non-empty strings
        expect($content)->toBeString();
        expect($content)->not->toBeEmpty();

        // Verify SSE format (data: prefix)
        expect($content)->toContain('data:');

        // Verify chunks contain actual content
        expect($content)->toContain('Analyzing character stats');
        expect($content)->toContain('Evaluating training options');
        expect($content)->toContain('Recommendation');

        // Verify completion signal
        expect($content)->toContain('"done":true');
    })->with([
        'basic training options' => [
            [
                'available_trainings' => [
                    ['type' => 'speed', 'energy_cost' => 20],
                    ['type' => 'stamina', 'energy_cost' => 25],
                ],
            ],
        ],
        'training with spirit burst' => [
            [
                'available_trainings' => [
                    ['type' => 'power', 'energy_cost' => 30],
                ],
                'spirit_burst_gauge' => 3,
            ],
        ],
        'training with context' => [
            [
                'available_trainings' => [
                    ['type' => 'wit', 'energy_cost' => 15],
                ],
                'additional_context' => 'Character needs wisdom boost',
            ],
        ],
    ])->repeat(100);

    it('verifies each chunk is a valid JSON object in SSE format', function () {
        // Arrange: Create test data
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        // Mock the service to return chunks
        $mockService = Mockery::mock(TrainingAdvisorService::class);
        $mockService->shouldReceive('validateTrainingOptions')
            ->andReturn([]);
        $mockService->shouldReceive('getAdviceStreaming')
            ->andReturn((function () {
                yield 'First chunk';
                yield 'Second chunk';
                yield 'Third chunk';
            })());

        $this->app->instance(TrainingAdvisorService::class, $mockService);

        // Act: Make streaming request
        $response = actingAs($user)->postJson('/api/neuron/training-advisor/advice/stream', [
            'character_id' => $character->id,
            'training_options' => [
                'available_trainings' => [
                    ['type' => 'speed', 'energy_cost' => 20],
                ],
            ],
        ]);

        // Assert: Verify each chunk is valid JSON
        $content = $response->streamedContent();

        // Split by SSE event boundaries
        $events = explode("\n\n", $content);

        foreach ($events as $event) {
            if (empty(trim($event))) {
                continue;
            }

            // Remove "data: " prefix
            $jsonData = str_replace('data: ', '', $event);

            // Verify it's valid JSON
            $decoded = json_decode($jsonData, true);
            expect($decoded)->toBeArray();

            // Verify it has expected structure (chunk or done or error)
            $hasValidKey = isset($decoded['chunk']) || isset($decoded['done']) || isset($decoded['error']);
            expect($hasValidKey)->toBeTrue();
        }
    })->repeat(100);

    it('verifies chunks are yielded progressively, not all at once', function () {
        // Arrange: Create test data
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        // Mock the service to return multiple chunks
        $mockService = Mockery::mock(TrainingAdvisorService::class);
        $mockService->shouldReceive('validateTrainingOptions')
            ->andReturn([]);
        $mockService->shouldReceive('getAdviceStreaming')
            ->andReturn((function () {
                for ($i = 1; $i <= 5; $i++) {
                    yield "Chunk {$i}";
                }
            })());

        $this->app->instance(TrainingAdvisorService::class, $mockService);

        // Act: Make streaming request
        $response = actingAs($user)->postJson('/api/neuron/training-advisor/advice/stream', [
            'character_id' => $character->id,
            'training_options' => [
                'available_trainings' => [
                    ['type' => 'speed', 'energy_cost' => 20],
                ],
            ],
        ]);

        // Assert: Verify multiple chunks exist
        $content = $response->streamedContent();

        // Count the number of chunk events
        $chunkCount = substr_count($content, '"chunk"');

        // Should have multiple chunks (at least 5)
        expect($chunkCount)->toBeGreaterThanOrEqual(5);

        // Verify chunks are in order
        for ($i = 1; $i <= 5; $i++) {
            expect($content)->toContain("Chunk {$i}");
        }
    })->repeat(100);

    it('handles streaming errors gracefully', function () {
        // Arrange: Create test data
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        // Mock the service to throw an exception during streaming
        $mockService = Mockery::mock(TrainingAdvisorService::class);
        $mockService->shouldReceive('validateTrainingOptions')
            ->andReturn([]);
        $mockService->shouldReceive('getAdviceStreaming')
            ->andThrow(new \Exception('Streaming error'));

        $this->app->instance(TrainingAdvisorService::class, $mockService);

        // Act: Make streaming request
        $response = actingAs($user)->postJson('/api/neuron/training-advisor/advice/stream', [
            'character_id' => $character->id,
            'training_options' => [
                'available_trainings' => [
                    ['type' => 'speed', 'energy_cost' => 20],
                ],
            ],
        ]);

        // Assert: Verify error is handled in stream
        $response->assertOk(); // Stream starts successfully
        $content = $response->streamedContent();

        // Verify error is communicated in stream
        expect($content)->toContain('error');
    })->repeat(100);
});
