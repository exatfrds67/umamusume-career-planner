<?php

use App\Models\Skill;
use App\Models\SkillBuild;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->skills = Skill::query()
        ->where('is_active', true)
        ->limit(5)
        ->get();

    if ($this->skills->isEmpty()) {
        $this->skills = collect();
        for ($i = 0; $i < 3; $i++) {
            $this->skills->push(Skill::factory()->create([
                'is_active' => true,
                'base_sp_cost' => fake()->numberBetween(100, 300),
            ]));
        }
    }
});

it('returns build templates', function () {
    $response = $this->getJson('/api/skills/build-templates');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data',
        ])
        ->assertJsonPath('success', true);
});

it('returns saved builds for authenticated user', function () {
    SkillBuild::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'is_template' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/skills/saved-builds');

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect(count($data))->toBeGreaterThanOrEqual(2);
});

it('returns empty array for unauthenticated saved builds', function () {
    $response = $this->getJson('/api/skills/saved-builds');

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data', []);
});

it('optimizes a skill build', function () {
    $skillIds = $this->skills->pluck('id')->toArray();

    if (empty($skillIds)) {
        $this->markTestSkipped('No active skills available');
    }

    $response = $this->postJson('/api/skills/build-optimization', [
        'skill_ids' => $skillIds,
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

it('rejects optimization with empty skills', function () {
    $response = $this->postJson('/api/skills/build-optimization', [
        'skill_ids' => [],
    ]);

    $response->assertUnprocessable();
});

it('saves a custom build', function () {
    $skillIds = $this->skills->pluck('id')->toArray();

    if (empty($skillIds)) {
        $this->markTestSkipped('No active skills available');
    }

    $response = $this->actingAs($this->user)
        ->postJson('/api/skills/save-build', [
            'name' => 'My Speed Build',
            'skill_ids' => $skillIds,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);
});

it('deletes a user build', function () {
    $build = SkillBuild::factory()->create([
        'user_id' => $this->user->id,
        'is_template' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/skills/builds/{$build->id}");

    $response->assertSuccessful();
});

it('prevents deleting template builds', function () {
    $templateBuild = SkillBuild::factory()->template()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/skills/builds/{$templateBuild->id}");

    $response->assertNotFound();
});

it('prevents deleting another user build', function () {
    $otherUser = User::factory()->create();
    $build = SkillBuild::factory()->create([
        'user_id' => $otherUser->id,
        'is_template' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/skills/builds/{$build->id}");

    $response->assertNotFound();
});
