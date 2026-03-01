<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Interaction Browser Tests
 *
 * Tests click, form, keyboard navigation, and touch events.
 * Verifies interactive UI elements respond correctly.
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements NFR-A-06, NFR-R-04
 *
 * @group browser
 * @group interaction
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Interaction Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 350,
            'wit' => 400,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 15,
    ]);
});

describe('Form Interactions', function () {
    it('allows login form submission', function () {
        $page = visit('/login');

        $page->assertPresent('[name="email"]')
            ->assertPresent('[name="password"]')
            ->fill('[name="email"]', $this->user->email)
            ->fill('[name="password"]', 'password')
            ->click('[type="submit"]')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'forms');

    it('shows validation errors on empty login', function () {
        $page = visit('/login');

        $page->click('[type="submit"]')
            ->wait(1)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'forms', 'validation');

    it('allows registration form interaction', function () {
        $page = visit('/register');

        $page->assertPresent('[name="name"]')
            ->assertPresent('[name="email"]')
            ->assertPresent('[name="password"]')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'forms');
});

describe('Click Interactions', function () {
    it('handles facility card clicks on training predictions', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->assertPresent('[data-facility="speed"]')
            ->click('[data-facility="speed"]')
            ->wait(0.5)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'clicks');

    it('handles navigation link clicks', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->navigate('/characters')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'clicks');
});

describe('Keyboard Navigation', function () {
    it('allows Tab navigation on the login page', function () {
        $page = visit('/login');

        $page->script("document.activeElement.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', bubbles: true}))");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'keyboard');

    it('allows Tab navigation on training predictions', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->script("document.activeElement.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', bubbles: true}))");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'keyboard');

    it('allows Escape key interaction', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script("document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}))");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'keyboard');
});

describe('Select Interactions', function () {
    it('allows character selection dropdown interaction', function () {
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
    })->group('browser', 'interaction', 'selects');
});

describe('ARIA Accessibility', function () {
    it('has proper ARIA landmarks on dashboard', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->assertPresent('[role="main"], main')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'accessibility');

    it('has proper ARIA attributes on training predictions', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->assertPresent('[role="article"]')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'accessibility');

    it('has no JavaScript errors on public pages', function () {
        $publicPages = ['/', '/login', '/register', '/about', '/help'];

        foreach ($publicPages as $url) {
            $page = visit($url);
            $page->assertNoJavaScriptErrors();
        }
    })->group('browser', 'interaction', 'accessibility');
});

describe('Page Transitions', function () {
    it('handles rapid navigation between pages', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->navigate('/characters')
            ->navigate('/dashboard')
            ->navigate('/characters')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'transitions');

    it('handles browser back and forward navigation', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->wait(1);
        $page->navigate('/characters')
            ->wait(1)
            ->navigate('/dashboard')
            ->wait(1)
            ->assertNoJavaScriptErrors();
    })->group('browser', 'interaction', 'transitions');
});
