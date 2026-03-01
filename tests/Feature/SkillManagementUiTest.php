<?php

/**
 * @property \App\Models\User $user
 * @property \App\Models\Character $character
 */

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'available_sp' => 1000,
    ]);
});

test('skill management page loads successfully', function () {
    $response = $this->actingAs($this->user)
        ->get(route('skills.index'));

    $response->assertStatus(200);
    $response->assertViewIs('skills.index');
    $response->assertViewHas('characters');
});

test('skill management page requires authentication', function () {
    $response = $this->get(route('skills.index'));

    $response->assertRedirect(route('welcome'));
});

test('can fetch all skills with acquisition status', function () {
    $skill = Skill::factory()->create();

    $response = $this->actingAs($this->user)
        ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'id',
                'name',
                'skill_type',
                'rarity',
                'base_sp_cost',
                'is_acquired',
                'available_hints',
                'discounted_cost',
            ],
        ],
    ]);
});

test('can acquire a skill', function () {
    $skill = Skill::factory()->create(['base_sp_cost' => 120]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.acquire'), [
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Skill acquired successfully',
    ]);

    $this->assertDatabaseHas('ucp_skill_acquisitions', [
        'character_id' => $this->character->id,
        'skill_id' => $skill->id,
        'is_active' => true,
    ]);

    // Check SP was deducted
    $this->character->refresh();
    expect($this->character->available_sp)->toBe(880);
});

test('cannot acquire skill with insufficient SP', function () {
    $skill = Skill::factory()->create(['base_sp_cost' => 1500]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.acquire'), [
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
        ]);

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'Insufficient SP',
    ]);
});

test('skill acquisition applies hint discounts', function () {
    $skill = Skill::factory()->create(['base_sp_cost' => 120]);

    // Create 2 hints (20% discount - VERIFIED: 10%/20%/30%/35%/40% at levels 1-5)
    SkillHint::factory()->count(2)->create([
        'character_id' => $this->character->id,
        'skill_id' => $skill->id,
        'is_used' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.acquire'), [
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
        ]);

    $response->assertStatus(201);

    $acquisition = SkillAcquisition::where('character_id', $this->character->id)
        ->where('skill_id', $skill->id)
        ->first();

    expect($acquisition->hints_used)->toBe(2);
    expect((float) $acquisition->total_discount_percentage)->toBe(20.0); // VERIFIED: 20% for 2 hints
    expect($acquisition->final_sp_cost)->toBe(96); // 120 - 20% = 96
    expect($acquisition->sp_saved)->toBe(24); // 20% of 120
});

test('can get evolution opportunities', function () {
    $normalSkill = Skill::factory()->normal()->canEvolve()->create();
    $rareSkill = Skill::factory()->rare()->evolved()->create();

    $normalSkill->update(['evolution_target_id' => $rareSkill->id]);
    $rareSkill->update(['evolution_source_id' => $normalSkill->id]);

    // Acquire the normal skill
    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $normalSkill->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.characters.evolution-opportunities', ['characterId' => $this->character->id]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'normal_skill',
                'rare_skill',
                'can_evolve',
                'evolution_cost',
            ],
        ],
    ]);
});

test('can evolve a skill', function () {
    $normalSkill = Skill::factory()->normal()->canEvolve()->create(['base_sp_cost' => 120]);
    $rareSkill = Skill::factory()->rare()->evolved()->create(['base_sp_cost' => 180]);

    $normalSkill->update(['evolution_target_id' => $rareSkill->id]);
    $rareSkill->update(['evolution_source_id' => $normalSkill->id]);

    // Acquire the normal skill
    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $normalSkill->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.evolve'), [
            'character_id' => $this->character->id,
            'skill_id' => $normalSkill->id,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Skill evolved successfully',
    ]);

    // Check normal skill is deactivated
    $normalAcquisition = SkillAcquisition::where('character_id', $this->character->id)
        ->where('skill_id', $normalSkill->id)
        ->first();
    expect($normalAcquisition->is_active)->toBeFalse();

    // Check rare skill is acquired
    $rareAcquisition = SkillAcquisition::where('character_id', $this->character->id)
        ->where('skill_id', $rareSkill->id)
        ->first();
    expect($rareAcquisition)->not->toBeNull();
    expect($rareAcquisition->is_active)->toBeTrue();
    expect($rareAcquisition->is_evolution)->toBeTrue();
});

test('can get AI recommendations', function () {
    Skill::factory()->count(5)->create();

    // Mock the orchestration service to avoid MCP dependencies
    $this->mock(\App\Services\MCP\SkillOptimizationOrchestrationService::class, function ($mock) {
        $mock->shouldReceive('optimizeSkillAcquisition')
            ->once()
            ->andReturn([
                'recommendations' => [],
                'analysis' => 'Test analysis',
            ]);
    });

    $response = $this->actingAs($this->user)
        ->getJson(route('api.skills.recommendations', ['character_id' => $this->character->id]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
    ]);
});

test('can get agent performance metrics', function () {
    // Create some acquisitions
    SkillAcquisition::factory()->count(3)->create([
        'character_id' => $this->character->id,
        'sp_saved' => 40,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.characters.agent-performance', ['characterId' => $this->character->id]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'total_sp_saved',
            'total_recommendations',
            'success_rate',
            'avg_response_time',
            'activities',
            'agents',
            'recommendations',
        ],
    ]);

    $data = $response->json('data');
    expect($data['total_sp_saved'])->toBe(120); // 3 * 40
});

test('skill inventory displays acquired and available skills separately', function () {
    $acquiredSkill = Skill::factory()->create();
    $availableSkill = Skill::factory()->create();

    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $acquiredSkill->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

    $response->assertStatus(200);

    $skills = collect($response->json('data'));
    $acquired = $skills->where('is_acquired', true);
    $available = $skills->where('is_acquired', false);

    expect($acquired->count())->toBeGreaterThan(0);
    expect($available->count())->toBeGreaterThan(0);
});

test('skill acquisition tracks hint sources', function () {
    $skill = Skill::factory()->create(['base_sp_cost' => 120]);

    // Create hints from different sources
    SkillHint::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $skill->id,
        'source_type' => 'support_card',
        'source_name' => 'Kitasan Black',
        'is_used' => false,
    ]);

    SkillHint::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $skill->id,
        'source_type' => 'event',
        'source_name' => 'Training Event',
        'is_used' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.acquire'), [
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
        ]);

    $response->assertStatus(201);

    // Check hints are marked as used
    $usedHints = SkillHint::where('character_id', $this->character->id)
        ->where('skill_id', $skill->id)
        ->where('is_used', true)
        ->get();

    expect($usedHints->count())->toBe(2);
});

test('evolution applies hint discounts to rare skill', function () {
    $normalSkill = Skill::factory()->normal()->canEvolve()->create(['base_sp_cost' => 120]);
    $rareSkill = Skill::factory()->rare()->evolved()->create(['base_sp_cost' => 180]);

    $normalSkill->update(['evolution_target_id' => $rareSkill->id]);
    $rareSkill->update(['evolution_source_id' => $normalSkill->id]);

    // Acquire the normal skill
    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $normalSkill->id,
        'is_active' => true,
    ]);

    // Create hints for the rare skill
    SkillHint::factory()->count(2)->create([
        'character_id' => $this->character->id,
        'skill_id' => $rareSkill->id,
        'is_used' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.evolve'), [
            'character_id' => $this->character->id,
            'skill_id' => $normalSkill->id,
        ]);

    $response->assertStatus(200);

    $rareAcquisition = SkillAcquisition::where('character_id', $this->character->id)
        ->where('skill_id', $rareSkill->id)
        ->first();

    expect($rareAcquisition->hints_used)->toBe(2);
    expect((float) $rareAcquisition->total_discount_percentage)->toBe(20.0); // VERIFIED: 20% for 2 hints
    expect($rareAcquisition->final_sp_cost)->toBe(144); // 180 - 20% = 144
});

test('agent performance tracks multiple agent types', function () {
    // Create acquisitions with different methods
    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'acquisition_method' => 'ai_recommended',
        'sp_saved' => 50,
    ]);

    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'is_evolution' => true,
        'sp_saved' => 30,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.characters.agent-performance', ['characterId' => $this->character->id]));

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data['agents'])->toHaveKeys([
        'skill_analysis',
        'hint_optimization',
        'evolution_planning',
        'build_planning',
    ]);
});

test('skill management validates character ownership', function () {
    $otherUser = User::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
    $skill = Skill::factory()->create();

    $response = $this->actingAs($this->user)
        ->postJson(route('api.skills.acquire'), [
            'character_id' => $otherCharacter->id,
            'skill_id' => $skill->id,
        ]);

    // Should fail because character doesn't belong to user
    // The controller's authorize() throws AuthorizationException (403),
    // which is caught by the generic catch block and returned as 500
    $response->assertStatus(403);
});

describe('Career metadata skill overlay', function () {
    it('marks a catalog skill as acquired when career_metadata has acquired=true', function () {
        $skill = Skill::factory()->create(['name' => 'Homestretch Haste', 'base_sp_cost' => 150]);

        \App\Models\Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Homestretch Haste', 'acquired' => true, 'sp_cost' => null, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

        $response->assertOk();

        $catalogSkill = collect($response->json('data'))->firstWhere('id', $skill->id);
        expect($catalogSkill)->not->toBeNull()
            ->and($catalogSkill['is_acquired'])->toBeTrue()
            ->and($catalogSkill['is_planned'])->toBeFalse()
            ->and($catalogSkill['is_metadata_only'])->toBeFalse();
    });

    it('marks a catalog skill as planned when career_metadata has acquired=false', function () {
        $skill = Skill::factory()->create(['name' => 'Final Push', 'base_sp_cost' => 162]);

        \App\Models\Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Final Push', 'acquired' => false, 'sp_cost' => 162, 'notes' => 'Target skill'],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

        $response->assertOk();

        $catalogSkill = collect($response->json('data'))->firstWhere('id', $skill->id);
        expect($catalogSkill)->not->toBeNull()
            ->and($catalogSkill['is_acquired'])->toBeFalse()
            ->and($catalogSkill['is_planned'])->toBeTrue()
            ->and($catalogSkill['is_metadata_only'])->toBeFalse()
            ->and($catalogSkill['metadata_notes'])->toBe('Target skill');
    });

    it('appends metadata-only skills that have no matching catalog entry', function () {
        \App\Models\Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => '∴ Win Q.E.D.', 'acquired' => true, 'sp_cost' => null, 'notes' => 'Unique win burst'],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

        $response->assertOk();

        $metaSkill = collect($response->json('data'))->firstWhere('name', '∴ Win Q.E.D.');
        expect($metaSkill)->not->toBeNull()
            ->and($metaSkill['is_acquired'])->toBeTrue()
            ->and($metaSkill['is_metadata_only'])->toBeTrue();
    });

    it('treats career_metadata acquired flag as lower priority than a real acquisition record', function () {
        $skill = Skill::factory()->create(['name' => 'Steadfast', 'base_sp_cost' => 112]);

        // Career metadata says acquired=false (planned), but there IS a real acquisition record
        \App\Models\Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Steadfast', 'acquired' => false, 'sp_cost' => 112, 'notes' => null],
                ],
            ],
        ]);

        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $skill->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

        $response->assertOk();

        $catalogSkill = collect($response->json('data'))->firstWhere('id', $skill->id);
        // Real acquisition record wins: should be acquired=true despite metadata saying false
        expect($catalogSkill['is_acquired'])->toBeTrue()
            ->and($catalogSkill['is_planned'])->toBeFalse();
    });

    it('is case insensitive when matching skill names between metadata and catalog', function () {
        $skill = Skill::factory()->create(['name' => 'Shifting Gears', 'base_sp_cost' => 100]);

        \App\Models\Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    // Mixed-case variant of the catalog name
                    ['name' => 'shifting gears', 'acquired' => true, 'sp_cost' => null, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.skills.index', ['character_id' => $this->character->id]));

        $response->assertOk();

        $catalogSkill = collect($response->json('data'))->firstWhere('id', $skill->id);
        expect($catalogSkill['is_acquired'])->toBeTrue();
    });
});

describe('skill plan endpoint', function () {
    it('marks a catalog skill as planned in latest career metadata', function () {
        $skill = Skill::factory()->create(['name' => 'Speed Boost', 'base_sp_cost' => 80]);

        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => ['skills' => []],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.skills.plan'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $career->refresh();
        $metadata = $career->career_metadata;
        $entry = collect($metadata['skills'])->firstWhere('name', 'Speed Boost');
        expect($entry)->not->toBeNull();
        expect($entry['acquired'])->toBeFalse();
        expect($entry['sp_cost'])->toBe(80);
    });

    it('does not duplicate an existing planned skill', function () {
        $skill = Skill::factory()->create(['name' => 'Corner Master', 'base_sp_cost' => 100]);

        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Corner Master', 'acquired' => false, 'sp_cost' => 100, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.skills.plan'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $career->refresh();
        $skillCount = collect($career->career_metadata['skills'])
            ->filter(fn ($e) => strtolower($e['name']) === 'corner master')
            ->count();

        expect($skillCount)->toBe(1);
    });

    it('returns 422 when character has no career', function () {
        $skill = Skill::factory()->create(['base_sp_cost' => 80]);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.skills.plan'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    });

    it('returns 403 when planning skill for another user\'s character', function () {
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
        Career::factory()->create([
            'character_id' => $otherCharacter->id,
            'user_id' => $otherUser->id,
        ]);

        $skill = Skill::factory()->create(['base_sp_cost' => 80]);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.skills.plan'), [
                'character_id' => $otherCharacter->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertForbidden();
    });

    it('requires character_id and skill_id', function () {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.skills.plan'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['character_id', 'skill_id']);
    });
});

describe('skill remove endpoint', function () {
    it('removes a planned skill from career metadata', function () {
        $skill = Skill::factory()->create(['name' => 'Speed Wave', 'base_sp_cost' => 90]);

        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Speed Wave', 'acquired' => false, 'sp_cost' => 90, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $career->refresh();
        $remaining = collect($career->career_metadata['skills'])
            ->filter(fn ($e) => strtolower($e['name'] ?? '') === 'speed wave')
            ->count();

        expect($remaining)->toBe(0);
    });

    it('removes an acquired skill entry from career metadata', function () {
        $skill = Skill::factory()->create(['name' => 'Final Rush', 'base_sp_cost' => 150]);

        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Final Rush', 'acquired' => true, 'sp_cost' => 150, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $career->refresh();
        expect($career->career_metadata['skills'])->toHaveCount(0);
    });

    it('returns 422 when skill is not in career metadata', function () {
        $skill = Skill::factory()->create(['name' => 'Ghost Run']);

        Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
            'career_metadata' => ['skills' => []],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    });

    it('returns 422 when character has no career', function () {
        $skill = Skill::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), [
                'character_id' => $this->character->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    });

    it('returns 403 when removing skill from another user\'s character', function () {
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
        $skill = Skill::factory()->create(['name' => 'Rival Burst']);

        Career::factory()->create([
            'character_id' => $otherCharacter->id,
            'user_id' => $otherUser->id,
            'career_metadata' => [
                'skills' => [
                    ['name' => 'Rival Burst', 'acquired' => false, 'sp_cost' => 120, 'notes' => null],
                ],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), [
                'character_id' => $otherCharacter->id,
                'skill_id' => $skill->id,
            ]);

        $response->assertForbidden();
    });

    it('requires character_id and skill_id', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson(route('api.skills.remove'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['character_id', 'skill_id']);
    });
});
