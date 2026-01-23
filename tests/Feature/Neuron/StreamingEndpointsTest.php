<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use App\Services\Neuron\RaceStrategyService;
use App\Services\Neuron\SkillRecommendationService;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Support\Facades\Log;

/**
 * Streaming Endpoints Feature Tests
 *
 * Tests Server-Sent Events (SSE) streaming endpoints for AI agent responses.
 * Validates Requirements 8.1, 8.2, 8.4, 8.5
 *
 * **Validates: Requirements 8.1, 8.2, 8.4, 8.5**
 */
beforeEach(function () {
    // Create test user and character
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

describe('Training Advisor Streaming Endpoint', function () {
    it('returns proper SSE headers', function () {
        // Mock the service to return a simple generator
        $this->mock(TrainingAdvisorService::class, function ($mock) {
            $mock->shouldReceive('validateTrainingOptions')
                ->andReturn([]);
            $mock->shouldReceive('getAdviceStreaming')
                ->andReturn((function () {
                    yield 'Test chunk 1';
                    yield 'Test chunk 2';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => [
                    ['type' => 'speed', 'level' => 5],
                ],
            ]);

        // Verify SSE headers (allow charset in Content-Type)
        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('text/event-stream');
        $cacheControl = $response->headers->get('Cache-Control');
        expect($cacheControl)->toContain('no-cache');
        $response->assertHeader('X-Accel-Buffering', 'no');
    });

    it('streams chunks progressively in SSE format', function () {
        // Mock the service to return test chunks
        $this->mock(TrainingAdvisorService::class, function ($mock) {
            $mock->shouldReceive('validateTrainingOptions')
                ->andReturn([]);
            $mock->shouldReceive('getAdviceStreaming')
                ->andReturn((function () {
                    yield 'First chunk';
                    yield 'Second chunk';
                    yield 'Third chunk';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => [
                    ['type' => 'speed', 'level' => 5],
                ],
            ]);

        $response->assertSuccessful();

        // Get the streamed content
        $content = $response->streamedContent();

        // Verify SSE format: data: {json}\n\n
        expect($content)->toContain('data: ');
        expect($content)->toContain('"chunk":"First chunk"');
        expect($content)->toContain('"chunk":"Second chunk"');
        expect($content)->toContain('"chunk":"Third chunk"');
        expect($content)->toContain('"done":true');
    });

    it('handles authentication errors with standard JSON format', function () {
        $response = $this->postJson('/api/neuron/training-advisor/advice/stream', [
            'character_id' => $this->character->id,
            'training_options' => [],
        ]);

        // Sanctum middleware returns standard 401 JSON response, not SSE
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    });

    it('handles validation errors with standard JSON format', function () {
        // Form Request validation returns standard JSON, not SSE
        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => 'invalid', // Invalid type
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['training_options']);
    });

    it('handles character not found with standard JSON format', function () {
        // Form Request validation catches non-existent character, returns standard JSON
        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => 99999,
                'training_options' => [],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['character_id']);
        $response->assertJson([
            'message' => 'The specified character does not exist.',
        ]);
    });

    it('handles general exceptions during streaming with fallback error message', function () {
        // Mock the service to throw a general exception
        $this->mock(TrainingAdvisorService::class, function ($mock) {
            $mock->shouldReceive('validateTrainingOptions')
                ->andReturn([]);
            $mock->shouldReceive('getAdviceStreaming')
                ->andThrow(new \Exception('AI service unavailable'));
        });

        Log::shouldReceive('error')->once();

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => [],
            ]);

        $response->assertSuccessful();
        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('text/event-stream');

        $content = $response->streamedContent();
        expect($content)->toContain('data: ');
        expect($content)->toContain('"error"');
        expect($content)->toContain('Unable to generate training advice');
    });
});

describe('Race Strategy Streaming Endpoint', function () {
    it('returns proper SSE headers', function () {
        // Mock the service
        $this->mock(RaceStrategyService::class, function ($mock) {
            $mock->shouldReceive('validateRaceData')
                ->andReturn([]);
            $mock->shouldReceive('getStrategyStreaming')
                ->andReturn((function () {
                    yield 'Strategy chunk';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/race-strategy/strategy/stream', [
                'character_id' => $this->character->id,
                'race_data' => [
                    'race_name' => 'Test Race',
                    'distance' => 2000,
                ],
            ]);

        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('text/event-stream');
        $cacheControl = $response->headers->get('Cache-Control');
        expect($cacheControl)->toContain('no-cache');
        $response->assertHeader('X-Accel-Buffering', 'no');
    });

    it('streams chunks progressively in SSE format', function () {
        $this->mock(RaceStrategyService::class, function ($mock) {
            $mock->shouldReceive('validateRaceData')
                ->andReturn([]);
            $mock->shouldReceive('getStrategyStreaming')
                ->andReturn((function () {
                    yield 'Strategy part 1';
                    yield 'Strategy part 2';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/race-strategy/strategy/stream', [
                'character_id' => $this->character->id,
                'race_data' => ['race_name' => 'Test'],
            ]);

        $response->assertSuccessful();

        $content = $response->streamedContent();
        expect($content)->toContain('"chunk":"Strategy part 1"');
        expect($content)->toContain('"chunk":"Strategy part 2"');
        expect($content)->toContain('"done":true');
    });

    it('handles errors with SSE format fallback', function () {
        $this->mock(RaceStrategyService::class, function ($mock) {
            $mock->shouldReceive('validateRaceData')
                ->andReturn([]);
            $mock->shouldReceive('getStrategyStreaming')
                ->andThrow(new \Exception('Service error'));
        });

        Log::shouldReceive('error')->once();

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/race-strategy/strategy/stream', [
                'character_id' => $this->character->id,
                'race_data' => ['race_name' => 'Test'],
            ]);

        $response->assertSuccessful();
        $content = $response->streamedContent();
        expect($content)->toContain('"error"');
    });
});

describe('Skill Recommendation Streaming Endpoint', function () {
    it('returns proper SSE headers', function () {
        $this->mock(SkillRecommendationService::class, function ($mock) {
            $mock->shouldReceive('validateSkillContext')
                ->andReturn([]);
            $mock->shouldReceive('getRecommendationsStreaming')
                ->andReturn((function () {
                    yield 'Skill recommendation';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/skill-recommendation/recommendations/stream', [
                'character_id' => $this->character->id,
                'skill_context' => [
                    'available_sp' => 100,
                ],
            ]);

        $contentType = $response->headers->get('Content-Type');
        expect($contentType)->toContain('text/event-stream');
        $cacheControl = $response->headers->get('Cache-Control');
        expect($cacheControl)->toContain('no-cache');
        $response->assertHeader('X-Accel-Buffering', 'no');
    });

    it('streams chunks progressively in SSE format', function () {
        $this->mock(SkillRecommendationService::class, function ($mock) {
            $mock->shouldReceive('validateSkillContext')
                ->andReturn([]);
            $mock->shouldReceive('getRecommendationsStreaming')
                ->andReturn((function () {
                    yield 'Recommendation 1';
                    yield 'Recommendation 2';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/skill-recommendation/recommendations/stream', [
                'character_id' => $this->character->id,
                'skill_context' => ['available_sp' => 100],
            ]);

        $response->assertSuccessful();

        $content = $response->streamedContent();
        expect($content)->toContain('"chunk":"Recommendation 1"');
        expect($content)->toContain('"chunk":"Recommendation 2"');
        expect($content)->toContain('"done":true');
    });

    it('handles errors with SSE format fallback', function () {
        $this->mock(SkillRecommendationService::class, function ($mock) {
            $mock->shouldReceive('validateSkillContext')
                ->andReturn([]);
            $mock->shouldReceive('getRecommendationsStreaming')
                ->andThrow(new \Exception('Service error'));
        });

        Log::shouldReceive('error')->once();

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/skill-recommendation/recommendations/stream', [
                'character_id' => $this->character->id,
                'skill_context' => ['available_sp' => 100],
            ]);

        $response->assertSuccessful();
        $content = $response->streamedContent();
        expect($content)->toContain('"error"');
    });
});

describe('SSE Format Validation', function () {
    it('ensures all chunks end with double newline', function () {
        $this->mock(TrainingAdvisorService::class, function ($mock) {
            $mock->shouldReceive('validateTrainingOptions')
                ->andReturn([]);
            $mock->shouldReceive('getAdviceStreaming')
                ->andReturn((function () {
                    yield 'Chunk 1';
                    yield 'Chunk 2';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => [],
            ]);

        $content = $response->streamedContent();

        // Each SSE message should end with \n\n
        $messages = explode("\n\n", trim($content));
        foreach ($messages as $message) {
            if (! empty($message)) {
                expect($message)->toStartWith('data: ');
            }
        }
    });

    it('ensures chunks contain valid JSON', function () {
        $this->mock(TrainingAdvisorService::class, function ($mock) {
            $mock->shouldReceive('validateTrainingOptions')
                ->andReturn([]);
            $mock->shouldReceive('getAdviceStreaming')
                ->andReturn((function () {
                    yield 'Test chunk';
                })());
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/neuron/training-advisor/advice/stream', [
                'character_id' => $this->character->id,
                'training_options' => [],
            ]);

        $content = $response->streamedContent();

        // Extract JSON from SSE messages
        preg_match_all('/data: (.+)/', $content, $matches);
        foreach ($matches[1] as $jsonString) {
            $decoded = json_decode($jsonString, true);
            expect($decoded)->toBeArray();
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        }
    });
});
