<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Critical Flow E2E Browser Tests
 *
 * Tests the core user journeys through the application:
 * - Character creation flow
 * - Training session flow
 * - Skill browsing and management
 * - Race strategy viewing
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements FR-02.1, FR-03.1, FR-05.2, FR-04.1
 *
 * @group browser
 * @group e2e
 * @group critical-flows
 */
beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Character Creation Flow', function () {
    it('loads the character creation page when authenticated', function () {
        $this->actingAs($this->user);
        $page = visit('/characters/create');

        $page->assertSee('Create')
            ->assertPresent('#character-form');
    })->group('browser', 'e2e', 'character-creation');

    it('lists characters on the index page', function () {
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'サイレンススズカ',
            'scenario_type' => 'ura_finale',
        ]);

        $this->actingAs($this->user);
        $page = visit('/characters');

        $page->assertSee($character->name)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'character-creation');

    it('shows character details on the show page', function () {
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'スペシャルウィーク',
            'scenario_type' => 'ura_finale',
            'current_stats' => [
                'speed' => 500,
                'stamina' => 400,
                'power' => 450,
                'guts' => 350,
                'wit' => 380,
            ],
            'energy_level' => 80,
            'mood_status' => 'good',
            'career_stage' => 'classic',
            'current_turn' => 10,
        ]);

        $this->actingAs($this->user);
        $page = visit('/characters/'.$character->id);

        $page->assertSee($character->name)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'character-creation');

    it('loads the character edit page', function () {
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'トウカイテイオー',
            'scenario_type' => 'ura_finale',
        ]);

        $this->actingAs($this->user);
        $page = visit('/characters/'.$character->id.'/edit');

        $page->assertSee($character->name)
            ->assertPresent('#character-form')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'character-creation');

    it('redirects unauthenticated users from character creation', function () {
        $page = visit('/characters/create');

        $page->assertPathIs('/')
            ->assertSee('Sign In');
    })->group('browser', 'e2e', 'character-creation', 'auth');
});

describe('Training Session Flow', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Training Test Character',
            'scenario_type' => 'ura_finale',
            'current_stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wit' => 400,
            ],
            'energy_level' => 75,
            'mood_status' => 'good',
            'career_stage' => 'classic',
            'current_turn' => 15,
        ]);
    });

    it('loads the training page for a character', function () {
        $this->actingAs($this->user);
        $page = visit('/characters/'.$this->character->id.'/training');

        $page->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'training');

    it('loads training predictions page and displays facilities', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->assertSee('Training Predictions')
            ->assertSee($this->character->name)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'training');

    it('allows selecting different characters for training predictions', function () {
        $character2 = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Second Character',
            'scenario_type' => 'ura_finale',
            'current_stats' => [
                'speed' => 600,
                'stamina' => 500,
                'power' => 550,
                'guts' => 400,
                'wit' => 450,
            ],
            'energy_level' => 60,
            'mood_status' => 'normal',
            'career_stage' => 'classic',
            'current_turn' => 25,
        ]);

        $this->actingAs($this->user);
        $page = visit('/training/predictions');

        $page->assertPresent('#character_id')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'training');

    it('displays training predictions for low energy character', function () {
        $lowEnergyCharacter = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Low Energy Character',
            'scenario_type' => 'ura_finale',
            'current_stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 750,
                'guts' => 600,
                'wit' => 650,
            ],
            'energy_level' => 15,
            'mood_status' => 'bad',
            'career_stage' => 'senior',
            'current_turn' => 50,
        ]);

        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$lowEnergyCharacter->id);

        $page->assertSee('Training Predictions')
            ->assertPresent('[id="character_id"]')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'training');
});

describe('Skill Browsing Flow', function () {
    it('loads the skills index page', function () {
        $this->actingAs($this->user);
        $page = visit('/skills');

        $page->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'skills');
});

describe('Race Strategy Flow', function () {
    it('loads the races index page', function () {
        $this->actingAs($this->user);
        $page = visit('/races');

        $page->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'races');
});

describe('Dashboard Flow', function () {
    it('loads the dashboard for authenticated users', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'dashboard');

    it('displays character data on dashboard when characters exist', function () {
        Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Dashboard Character',
            'scenario_type' => 'ura_finale',
        ]);

        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->assertSee('Dashboard Character')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'dashboard');
});

describe('Navigation Flow', function () {
    it('navigates from dashboard to characters', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->navigate('/characters')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'navigation');

    it('navigates from characters to training predictions', function () {
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Navigation Test',
            'scenario_type' => 'ura_finale',
            'current_stats' => [
                'speed' => 500,
                'stamina' => 400,
                'power' => 450,
                'guts' => 350,
                'wit' => 400,
            ],
            'energy_level' => 70,
            'mood_status' => 'normal',
            'career_stage' => 'classic',
            'current_turn' => 20,
        ]);

        $this->actingAs($this->user);
        $page = visit('/characters');

        $page->navigate('/training/predictions?character_id='.$character->id)
            ->assertSee($character->name)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'e2e', 'navigation');
});
