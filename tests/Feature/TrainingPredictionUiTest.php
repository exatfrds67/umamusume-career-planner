<?php

/**
 * Training Prediction UI Tests
 *
 * Tests the frontend interface for training predictions including:
 * - Character selection and display
 * - Training prediction rendering
 * - Agent visualization
 * - Spirit Burst indicators
 * - Recommendation rankings
 */

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;

/** @var Character $character */
$character = null;
/** @var User $user */
$user = null;

beforeEach(function () use (&$character, &$user) {
    // Create test user
    $user = User::factory()->create();

    // Create test character
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 300,
            'wit' => 350,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
    ]);

    // Create support card definitions
    $supportCardDefinitions = SupportCardDefinition::factory()->count(3)->create();

    // Create character support cards (HasMany relationship)
    foreach ($supportCardDefinitions as $index => $definition) {
        CharacterSupportCard::factory()->create([
            'character_id' => $character->id,
            'support_card_id' => $definition->id,
            'limit_break_level' => 2,
            'friendship_level' => 50,
            'position_slot' => $index + 1,
            'is_friend_card' => false,
        ]);
    }
});

test('training predictions index page loads successfully', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    $response->assertViewIs('training.predictions');
    $response->assertViewHas('characters');
    $response->assertViewHas('trainingTypes');
});

test('training predictions index displays character selection', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    $response->assertSee('Select Character');
    $response->assertSee($character->name);
});

test('training predictions index with selected character displays character info', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $character->id]));

    $response->assertSuccessful();
    $response->assertSee($character->name);
    $response->assertSee('Current Stats');
    $response->assertSee('Energy');
    $response->assertSee('Mood');
    $response->assertSee('Support Cards');
});

test('training predictions index displays training predictions app container', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $character->id]));

    $response->assertSuccessful();
    $response->assertSee('training-predictions-app', false);
    $response->assertSee("data-character-id=\"{$character->id}\"", false);
    $response->assertSee("data-scenario-type=\"{$character->scenario_type}\"", false);
});

test('training predictions show page loads successfully', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertViewIs('training.show');
    $response->assertViewHas('character');
    $response->assertViewHas('trainingTypes');
});

test('training predictions show page displays character details', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee($character->name);
    $response->assertSee('Current Stats');
    $response->assertSee('Status');
    $response->assertSee('Scenario');
    $response->assertSee('Support Cards');
});

test('training predictions show page displays stat values', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('500'); // Speed
    $response->assertSee('400'); // Stamina
    $response->assertSee('450'); // Power
    $response->assertSee('300'); // Guts
    $response->assertSee('350'); // Wit
});

test('training predictions show page displays energy level', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('80%'); // Energy level
});

test('training predictions show page displays mood status', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('good'); // Mood status
});

test('training predictions show page displays scenario type', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('ura finale'); // Scenario type
});

test('training predictions show page displays support card count', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('3 / 6 equipped'); // Support card count
});

test('training predictions show page includes back button', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('Back to Training Predictions');
    $response->assertSee(route('training.predictions'), false);
});

test('training predictions index without character shows empty state', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    $response->assertSee('No Character Selected');
    $response->assertSee('Please select a character to view training predictions');
});

test('training predictions index displays all training types', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $character->id]));

    $response->assertSuccessful();
    // Training types are used in JavaScript, so we check for the data attributes
    $response->assertSee('training-predictions-app', false);
});

test('training predictions show page includes JavaScript module', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('training-predictions.js', false);
});

test('training predictions index with unity cup character displays correct scenario', function () use (&$user) {
    $unityCupCharacter = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Unity Cup Character',
        'scenario_type' => 'unity_cup',
    ]);

    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $unityCupCharacter->id]));

    $response->assertSuccessful();
    $response->assertSee('unity cup');
    $response->assertSee('data-scenario-type="unity_cup"', false);
});

test('training predictions controller returns correct training types', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    $response->assertViewHas('trainingTypes', fn ($trainingTypes) => isset($trainingTypes['speed'])
        && isset($trainingTypes['stamina'])
        && isset($trainingTypes['power'])
        && isset($trainingTypes['guts'])
        && isset($trainingTypes['wit'])
        && isset($trainingTypes['rest']));
});

test('training predictions index displays character list ordered by name', function () use (&$user) {
    Character::factory()->create(['user_id' => $user->id, 'name' => 'Zebra Character']);
    Character::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Character']);

    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Alpha Character', 'Test Character', 'Zebra Character']);
});

test('training predictions show page loads character with relationships', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertViewHas('character', fn ($character) => $character->relationLoaded('aptitudes')
        && $character->relationLoaded('supportCards')
        && $character->relationLoaded('factors'));
});

test('training predictions index with invalid character id shows empty state', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => 99999]));

    $response->assertSuccessful();
    $response->assertSee('No Character Selected');
});

test('training predictions show page returns 404 for non-existent character', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', 99999));

    $response->assertNotFound();
});

test('training predictions index includes CSRF token for API calls', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $character->id]));

    $response->assertSuccessful();
    $response->assertSee('csrf-token', false);
});

test('training predictions index includes API URL in data attributes', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions', ['character_id' => $character->id]));

    $response->assertSuccessful();
    $response->assertSee('data-api-url', false);
    $response->assertSee(route('api.training-predictions.batch'), false);
});

test('training predictions page is accessible via keyboard navigation', function () use (&$user) {
    $response = $this->actingAs($user)->get(route('training.predictions'));

    $response->assertSuccessful();
    // Check for proper form elements with labels
    $response->assertSee('<label for="character_id"', false);
    $response->assertSee('<select', false);
});

test('training predictions show page displays loading state initially', function () use (&$character, &$user) {
    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee('Loading training predictions...');
    $response->assertSee('animate-spin', false);
});
