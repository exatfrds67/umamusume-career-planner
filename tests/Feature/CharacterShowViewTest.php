<?php

use App\Models\Character;
use App\Models\Factor;
use App\Models\GameCharacter;
use App\Models\User;

it('can display character show page without description attribute error', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    // Create a factor without description attribute
    Factor::factory()->create([
        'character_id' => $character->id,
        'factor_type' => 'blue_stats',
        'factor_name' => 'Speed Factor',
        'stat_type' => 'speed',
        'stat_bonus' => 12,
        'star_level' => '2_star',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertViewIs('characters.show')
        ->assertViewHas('character', $character)
        ->assertSee($character->name)
        ->assertSee('Speed Factor'); // Should display the factor name without error
});

it('displays factors correctly in character show view', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    // Create multiple types of factors
    Factor::factory()->create([
        'character_id' => $character->id,
        'factor_type' => 'blue_stats',
        'factor_name' => 'Speed Boost',
        'stat_type' => 'speed',
        'stat_bonus' => 21,
        'star_level' => '3_star',
        'is_active' => true,
    ]);

    Factor::factory()->create([
        'character_id' => $character->id,
        'factor_type' => 'red_aptitudes',
        'factor_name' => 'Distance Aptitude',
        'aptitude_type' => 'sprint',
        'star_level' => '2_star',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertSee('Speed Boost')
        ->assertSee('Distance Aptitude')
        ->assertSee('Inherited Factors')
        ->assertSee('Stat Bonuses')
        ->assertSee('Aptitude Upgrades');
});

it('renders a fallback portrait for a variant character without a stored avatar', function () {
    $user = User::factory()->create();
    GameCharacter::factory()->create([
        'name_en' => 'Vodka',
        'image_path' => 'images/trainee_images/vodka.png',
    ]);

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Vodka (Star)',
        'avatar_url' => null,
        'game_character_id' => null,
    ]);

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertSee('/images/trainee_images/vodka.png')
        ->assertSee('Vodka (Star)');
});
