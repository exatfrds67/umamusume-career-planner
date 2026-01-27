<?php

use App\Models\Character;
use App\Models\Factor;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

it('can display factor management page', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.factors.manage', $this->character));

    $response->assertOk()
        ->assertViewIs('characters.factors.manage')
        ->assertViewHas('character', $this->character);
});

it('can create a blue factor', function () {
    $factorData = [
        'factor_type' => 'blue_stats',
        'factor_name' => 'Speed Factor',
        'star_level' => '2_star',
        'stat_type' => 'speed',
        'source_parent' => 'main_parent_1',
        'source_character_name' => 'Test Parent',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('characters.factors.store', $this->character), $factorData);

    $response->assertRedirect(route('characters.factors.manage', $this->character))
        ->assertSessionHas('success', 'Factor added successfully!');

    $this->assertDatabaseHas('ucp_factors', [
        'character_id' => $this->character->id,
        'factor_type' => 'blue_stats',
        'factor_name' => 'Speed Factor',
        'star_level' => '2_star',
        'stat_type' => 'speed',
        'source_parent' => 'main_parent_1',
        'source_character_name' => 'Test Parent',
        'is_active' => true,
    ]);
});

it('can create a red factor', function () {
    $factorData = [
        'factor_type' => 'red_aptitudes',
        'factor_name' => 'Distance Aptitude Factor',
        'star_level' => '3_star',
        'aptitude_type' => 'sprint',
        'source_parent' => 'main_parent_2',
        'source_character_name' => 'Test Mother',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('characters.factors.store', $this->character), $factorData);

    $response->assertRedirect(route('characters.factors.manage', $this->character))
        ->assertSessionHas('success', 'Factor added successfully!');

    $this->assertDatabaseHas('ucp_factors', [
        'character_id' => $this->character->id,
        'factor_type' => 'red_aptitudes',
        'factor_name' => 'Distance Aptitude Factor',
        'star_level' => '3_star',
        'aptitude_type' => 'sprint',
        'source_parent' => 'main_parent_2',
        'source_character_name' => 'Test Mother',
        'is_active' => true,
    ]);
});

it('can toggle factor active status', function () {
    $factor = Factor::factory()->create([
        'character_id' => $this->character->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->patch(route('characters.factors.toggle', [$this->character, $factor]));

    $response->assertRedirect(route('characters.factors.manage', $this->character))
        ->assertSessionHas('success', 'Factor deactivated successfully!');

    $this->assertDatabaseHas('ucp_factors', [
        'id' => $factor->id,
        'is_active' => false,
    ]);
});

it('can delete a factor', function () {
    $factor = Factor::factory()->create([
        'character_id' => $this->character->id,
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('characters.factors.destroy', [$this->character, $factor]));

    $response->assertRedirect(route('characters.factors.manage', $this->character))
        ->assertSessionHas('success', 'Factor deleted successfully!');

    $this->assertDatabaseMissing('ucp_factors', [
        'id' => $factor->id,
    ]);
});

it('prevents unauthorized users from managing factors', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get(route('characters.factors.manage', $this->character));

    $response->assertForbidden();
});

it('validates required fields when creating factors', function () {
    $response = $this->actingAs($this->user)
        ->post(route('characters.factors.store', $this->character), []);

    $response->assertSessionHasErrors(['factor_type', 'factor_name', 'star_level', 'source_parent']);
});

it('validates stat type is required for blue factors', function () {
    $factorData = [
        'factor_type' => 'blue_stats',
        'factor_name' => 'Speed Factor',
        'star_level' => '2_star',
        'source_parent' => 'main_parent_1',
        // Missing stat_type
    ];

    $response = $this->actingAs($this->user)
        ->post(route('characters.factors.store', $this->character), $factorData);

    $response->assertRedirect()
        ->assertSessionHas('error', 'Failed to add factor. Please try again.');
});
