<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Models\SkillBuild;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\UcpSkillsSeeder']);
});

describe('Skill Build API', function (): void {

    describe('GET /api/skills/build-templates', function (): void {
        it('returns auto-generated templates from skills', function (): void {
            $response = $this->getJson('/api/skills/build-templates');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data',
                ]);
        });

        it('returns database templates when they exist', function (): void {
            SkillBuild::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'is_template' => true,
            ]);

            $response = $this->getJson('/api/skills/build-templates');

            $response->assertSuccessful()
                ->assertJsonCount(2, 'data');
        });
    });

    describe('GET /api/skills/saved-builds', function (): void {
        it('returns saved builds for authenticated user', function (): void {
            SkillBuild::factory()->count(3)->create([
                'user_id' => $this->user->id,
                'is_template' => false,
            ]);

            $response = $this->getJson('/api/skills/saved-builds');

            $response->assertSuccessful()
                ->assertJsonCount(3, 'data');
        });

        it('does not return other users builds', function (): void {
            $otherUser = User::factory()->create();
            SkillBuild::factory()->count(2)->create([
                'user_id' => $otherUser->id,
                'is_template' => false,
            ]);

            $response = $this->getJson('/api/skills/saved-builds');

            $response->assertSuccessful()
                ->assertJsonCount(0, 'data');
        });
    });

    describe('POST /api/skills/save-build', function (): void {
        it('saves a new build', function (): void {
            $skills = Skill::where('is_active', true)->limit(3)->pluck('id')->toArray();

            $response = $this->postJson('/api/skills/save-build', [
                'name' => 'Test Build',
                'skill_ids' => $skills,
                'category' => 'Speed',
                'tags' => ['speed', 'test'],
            ]);

            $response->assertCreated()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.name', 'Test Build');

            $this->assertDatabaseHas('ucp_skill_builds', [
                'name' => 'Test Build',
                'user_id' => $this->user->id,
            ]);
        });

        it('validates required fields', function (): void {
            $response = $this->postJson('/api/skills/save-build', []);

            $response->assertUnprocessable();
        });
    });

    describe('POST /api/skills/build-optimization', function (): void {
        it('returns optimization data for selected skills', function (): void {
            $skills = Skill::where('is_active', true)->limit(3)->pluck('id')->toArray();

            $response = $this->postJson('/api/skills/build-optimization', [
                'skill_ids' => $skills,
            ]);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'summary',
                        'efficiency_score',
                        'synergy_rating',
                        'total_sp_cost',
                        'optimized_cost',
                        'recommendations',
                    ],
                ]);
        });

        it('validates skill_ids are required', function (): void {
            $response = $this->postJson('/api/skills/build-optimization', []);

            $response->assertUnprocessable();
        });
    });

    describe('DELETE /api/skills/builds/{id}', function (): void {
        it('deletes own build', function (): void {
            $build = SkillBuild::factory()->create([
                'user_id' => $this->user->id,
                'is_template' => false,
            ]);

            $response = $this->deleteJson("/api/skills/builds/{$build->id}");

            $response->assertSuccessful();
            $this->assertDatabaseMissing('ucp_skill_builds', ['id' => $build->id]);
        });

        it('cannot delete another users build', function (): void {
            $otherUser = User::factory()->create();
            $build = SkillBuild::factory()->create([
                'user_id' => $otherUser->id,
                'is_template' => false,
            ]);

            $response = $this->deleteJson("/api/skills/builds/{$build->id}");

            $response->assertNotFound();
        });

        it('cannot delete template builds', function (): void {
            $build = SkillBuild::factory()->template()->create([
                'user_id' => $this->user->id,
            ]);

            $response = $this->deleteJson("/api/skills/builds/{$build->id}");

            $response->assertNotFound();
        });
    });
});
