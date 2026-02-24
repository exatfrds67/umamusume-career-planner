<?php

declare(strict_types=1);

use App\Collections\RecommendationCollection;
use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 350,
            'wisdom' => 300,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
    ]);
    $this->career = Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'status' => 'active',
        'current_turn' => 10,
        'current_phase' => 'junior',
    ]);

    $this->mock(TrainingAdvisoryService::class, function ($mock) {
        $mock->shouldReceive('getTrainingRecommendations')
            ->andReturn(new RecommendationCollection);
    });
});

it('returns available races from real Race model queries', function () {
    Race::factory()->count(3)->create([
        'career_id' => $this->career->id,
        'character_id' => $this->character->id,
        'turn_number' => 15,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.careers.available-races', $this->career->id));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_turn', 'current_phase', 'total_available'],
        ]);

    expect($response->json('meta.total_available'))->toBe(3);
    expect($response->json('meta.current_turn'))->toBe(10);
});

it('returns training predictions from TrainingCalculationService', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.careers.training-predictions', $this->career->id));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'predictions',
                'recommended',
                'career_turn',
                'career_phase',
            ],
        ]);

    expect($response->json('data.career_turn'))->toBe(10);
    expect($response->json('data.career_phase'))->toBe('junior');
    expect($response->json('data.predictions'))->toBeArray();
});

it('stores training session via TrainingService::executeTraining', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.careers.training-sessions.store', $this->career->id), [
            'training_type' => 'speed',
            'turn_number' => 10,
            'actual_gains' => [
                'speed' => 12,
                'stamina' => 2,
                'power' => 3,
                'guts' => 0,
                'wit' => 0,
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'success',
                'session',
                'stat_gains',
            ],
        ]);

    expect($response->json('data.success'))->toBeTrue();
    expect($response->json('data.stat_gains.speed'))->toBe(12);
});

it('stores bulk training sessions with real calculations', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.careers.training-sessions.bulk', $this->career->id), [
            'sessions' => [
                [
                    'training_type' => 'speed',
                    'turn_number' => 10,
                    'actual_gains' => ['speed' => 10, 'stamina' => 1, 'power' => 2, 'guts' => 0, 'wit' => 0],
                ],
                [
                    'training_type' => 'stamina',
                    'turn_number' => 11,
                    'actual_gains' => ['speed' => 0, 'stamina' => 12, 'power' => 1, 'guts' => 0, 'wit' => 0],
                ],
            ],
        ]);

    $response->assertStatus(201);
    expect($response->json('count'))->toBe(2);
    expect($response->json('results'))->toHaveCount(2);
});

it('stores race with real data from request', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.careers.races.store', $this->career->id), [
            'race_name' => 'Satsuki Sho',
            'race_grade' => 'G1',
            'turn_number' => 12,
            'distance_category' => 'intermediate',
            'distance_meters' => 2000,
            'surface' => 'turf',
            'running_style' => 'leading',
            'weather' => 'sunny',
            'track_condition' => 'good',
            'field_size' => 18,
            'finish_position' => 1,
            'won_race' => true,
            'sp_reward' => 45,
            'fans_gained' => 5000,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data']);

    $raceData = $response->json('data');
    expect($raceData['race_name'])->toBe('Satsuki Sho');
    expect($raceData['race_grade'])->toBe('G1');
    expect($raceData['finish_position'])->toBe(1);
    expect((bool) $raceData['won_race'])->toBeTrue();
    expect($raceData['sp_reward'])->toBe(45);

    $this->assertDatabaseHas('ucp_races', [
        'career_id' => $this->career->id,
        'race_name' => 'Satsuki Sho',
    ]);
});

it('returns patterns using CareerAnalyticsService', function () {
    $career2 = Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'status' => 'active',
        'current_turn' => 30,
        'current_phase' => 'classic',
    ]);

    TrainingSession::factory()->count(5)->create([
        'career_id' => $this->career->id,
        'character_id' => $this->character->id,
        'training_type' => 'speed',
    ]);

    TrainingSession::factory()->count(3)->create([
        'career_id' => $career2->id,
        'character_id' => $this->character->id,
        'training_type' => 'stamina',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.careers.patterns'), [
            'career_ids' => [$this->career->id, $career2->id],
        ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'common_strategies',
                'phase_efficiency',
                'success_factors',
                'per_character_analytics',
                'careers_analyzed',
            ],
        ]);

    expect($response->json('data.careers_analyzed'))->toBe(2);
    expect($response->json('data.common_strategies'))->toBeArray();
    expect($response->json('data.per_character_analytics'))->toBeArray();
});

it('returns recommendations with ai_recommendations key', function () {
    TrainingSession::factory()->count(5)->create([
        'career_id' => $this->career->id,
        'character_id' => $this->character->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.careers.recommendations'), [
            'career_ids' => [$this->career->id],
        ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'training_recommendations',
                'race_recommendations',
                'skill_recommendations',
                'ai_recommendations',
                'careers_analyzed',
            ],
        ]);

    expect($response->json('data.careers_analyzed'))->toBe(1);
    expect($response->json('data.training_recommendations'))->toBeArray();
    expect($response->json('data.race_recommendations'))->toBeArray();
    expect($response->json('data.ai_recommendations'))->toBeArray();
});

it('returns report with real average gains instead of hardcoded value', function () {
    TrainingSession::factory()->count(3)->create([
        'career_id' => $this->career->id,
        'character_id' => $this->character->id,
        'speed_gain' => 10,
        'stamina_gain' => 5,
        'power_gain' => 3,
        'guts_gain' => 2,
        'wit_gain' => 1,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.careers.report', $this->career->id));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'career_summary',
                'stat_progression',
                'training_analysis' => ['total_sessions', 'average_gains'],
                'race_performance',
            ],
        ]);

    expect($response->json('data.training_analysis.total_sessions'))->toBe(3);
    // Average gains should be 10+5+3+2+1 = 21 per session
    expect($response->json('data.training_analysis.average_gains'))->toEqual(21);
});

it('returns zero average gains when no training sessions exist', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.careers.report', $this->career->id));

    $response->assertSuccessful();

    expect($response->json('data.training_analysis.total_sessions'))->toBe(0);
    expect($response->json('data.training_analysis.average_gains'))->toBe(0);
});

it('prevents unauthorized access to career endpoints', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->getJson(route('api.v1.careers.available-races', $this->career->id));

    $response->assertForbidden();
});
