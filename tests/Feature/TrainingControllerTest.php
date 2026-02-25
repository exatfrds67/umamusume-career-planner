<?php

declare(strict_types=1);

/**
 * Training Controller Tests
 *
 * Tests for the TrainingController's index() and store() methods.
 */

use App\Models\Career;
use App\Models\Character;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 200,
            'stamina' => 150,
            'power' => 180,
            'guts' => 120,
            'wit' => 160,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
    ]);
    $this->career = Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'status' => 'active',
    ]);
});

describe('TrainingController@index', function () {
    it('displays the training page for an authorized user', function () {
        $response = $this->actingAs($this->user)
            ->get(route('training.index', $this->character));

        $response->assertSuccessful()
            ->assertViewHas('character')
            ->assertViewHas('trainingData');
    });

    it('returns training data for all five facilities', function () {
        $response = $this->actingAs($this->user)
            ->get(route('training.index', $this->character));

        $response->assertSuccessful();

        $trainingData = $response->viewData('trainingData');
        expect($trainingData)->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);

        foreach ($trainingData as $type => $data) {
            expect($data)->toHaveKeys(['gains', 'failure_rate', 'energy_cost']);
        }
    });

    it('denies access to another user character', function () {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('training.index', $this->character));

        $response->assertForbidden();
    });

    it('requires authentication', function () {
        $response = $this->get(route('training.index', $this->character));

        $response->assertRedirect();
    });
});

describe('TrainingController@store', function () {
    it('executes training and redirects with success', function () {
        $response = $this->actingAs($this->user)
            ->post(route('training.store', $this->character), [
                'training_type' => 'speed',
            ]);

        $response->assertRedirect(route('training.index', $this->character))
            ->assertSessionHas('success');
    });

    it('validates training type is required', function () {
        $response = $this->actingAs($this->user)
            ->post(route('training.store', $this->character), []);

        $response->assertSessionHasErrors('training_type');
    });

    it('validates training type must be a valid facility', function () {
        $response = $this->actingAs($this->user)
            ->post(route('training.store', $this->character), [
                'training_type' => 'invalid',
            ]);

        $response->assertSessionHasErrors('training_type');
    });

    it('denies store to unauthorized user', function () {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->post(route('training.store', $this->character), [
                'training_type' => 'speed',
            ]);

        $response->assertForbidden();
    });
});
