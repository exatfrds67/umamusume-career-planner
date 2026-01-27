<?php

use App\Models\Character;
use App\Models\Skill;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('displays skill details modal with properly formatted effects', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'available_sp' => 500,
    ]);

    Skill::factory()->create([
        'name' => 'Test Skill',
        'effects' => [
            'activation' => 'final_straight',
            'effect' => 'speed_boost',
            'power' => 'medium',
        ],
        'description' => 'Test skill description',
        'base_sp_cost' => 100,
    ]);

    $response = actingAs($user)->get(route('skills.index', ['character' => $character->id]));

    $response->assertOk();
    $response->assertSee('skillManagement(');
    $response->assertSee('showSkillModal');
    $response->assertSee('selectedSkill');
    $response->assertSee('viewSkillDetails');
    $response->assertSee('selectedSkill.effects.activation');
    $response->assertSee('selectedSkill.effects.effect');
    $response->assertSee('selectedSkill.effects.power');
});

it('handles skill effects as both object and string', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
    ]);

    Skill::factory()->create([
        'effects' => [
            'activation' => 'start',
            'effect' => 'acceleration',
            'power' => 'high',
        ],
    ]);

    Skill::factory()->create([
        'effects' => [],
        'description' => 'Simple effect description',
    ]);

    $response = actingAs($user)->get(route('skills.index', ['character' => $character->id]));

    $response->assertOk();
    $response->assertSee('selectedSkill.effects.activation', false);
    $response->assertSee('selectedSkill.effects.effect', false);
    $response->assertSee('selectedSkill.effects.power', false);
});
