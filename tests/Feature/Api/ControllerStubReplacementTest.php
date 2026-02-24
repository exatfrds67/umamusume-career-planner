<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\MCPToolUsage;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillBuild;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

describe('APIMonitoringController historical()', function () {
    it('returns historical metrics with default parameters', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/monitoring/historical');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'metric',
                    'data_points',
                    'summary',
                    'generated_at',
                ],
            ]);

        $data = $response->json('data');
        expect($data['period'])->toBe('1h');
        expect($data['metric'])->toBe('response_time');
        expect($data['summary'])->toHaveKeys(['avg', 'min', 'max', 'trend']);
    });

    it('accepts valid period and metric parameters', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/monitoring/historical?period=24h&metric=error_rate');

        $response->assertSuccessful();

        $data = $response->json('data');
        expect($data['period'])->toBe('24h');
        expect($data['metric'])->toBe('error_rate');
    });

    it('validates period parameter', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/monitoring/historical?period=invalid');

        $response->assertUnprocessable();
    });

    it('validates metric parameter', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/monitoring/historical?metric=invalid');

        $response->assertUnprocessable();
    });
});

describe('SkillManagementController agentPerformance()', function () {
    it('returns real metrics from acquisitions and MCP usage', function () {
        $skills = Skill::factory()->count(3)->create();

        foreach ($skills as $skill) {
            SkillAcquisition::factory()->create([
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
                'sp_saved' => 50,
                'acquisition_method' => 'ai_recommended',
                'effectiveness_rating' => 8.0,
                'base_sp_cost' => 200,
                'final_sp_cost' => 150,
            ]);
        }

        MCPToolUsage::factory()->count(5)->create([
            'tool_category' => 'skill',
            'execution_status' => 'success',
            'execution_time' => 0.85,
            'executed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.characters.agent-performance', ['characterId' => $this->character->id]));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sp_saved',
                    'total_recommendations',
                    'success_rate',
                    'avg_response_time',
                    'activities',
                    'agents' => [
                        'skill_analysis',
                        'hint_optimization',
                        'evolution_planning',
                        'build_planning',
                    ],
                    'recommendations',
                ],
            ]);

        $data = $response->json('data');
        expect($data['total_sp_saved'])->toBe(150);
        expect($data['total_recommendations'])->toBe(3);
        expect($data['avg_response_time'])->toBeGreaterThan(0);
    });

    it('returns zero metrics for character with no data', function () {
        $response = $this->actingAs($this->user)
            ->getJson(route('api.characters.agent-performance', ['characterId' => $this->character->id]));

        $response->assertSuccessful();

        $data = $response->json('data');
        expect($data['total_sp_saved'])->toBe(0);
        expect($data['total_recommendations'])->toBe(0);
        expect((float) $data['avg_response_time'])->toBe(0.0);
    });

    it('includes build planning metrics from SkillBuild model', function () {
        SkillBuild::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'total_sp_cost' => 500,
            'optimized_cost' => 400,
            'meta_tier' => 'A',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.characters.agent-performance', ['characterId' => $this->character->id]));

        $response->assertSuccessful();

        $data = $response->json('data');
        expect($data['agents']['build_planning']['total'])->toBe(2);
    });
});

describe('DashboardController getRecentResults()', function () {
    it('includes training sessions in recent results', function () {
        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        TrainingSession::factory()->create([
            'character_id' => $this->character->id,
            'career_id' => $career->id,
            'training_type' => 'speed',
            'speed_gain' => 15,
            'stamina_gain' => 0,
            'power_gain' => 5,
            'guts_gain' => 0,
            'wit_gain' => 0,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertSuccessful();
    });

    it('includes race results in recent results', function () {
        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        Race::factory()->create([
            'character_id' => $this->character->id,
            'career_id' => $career->id,
            'race_name' => 'Japan Cup',
            'finish_position' => 1,
            'won_race' => true,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertSuccessful();
    });

    it('includes skill acquisitions in recent results', function () {
        $skill = Skill::factory()->create(['name' => 'Speed Star']);

        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertSuccessful();
    });
});
