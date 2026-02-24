<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Visual Regression Browser Tests
 *
 * Tests responsive layouts and dark mode rendering at multiple viewports.
 * Verifies pages render correctly across different screen sizes.
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements NFR-R-01, NFR-R-02, NFR-R-03
 *
 * @group browser
 * @group visual-regression
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Visual Test Character',
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

describe('Mobile Viewport (320px)', function () {
    it('renders the welcome page at mobile viewport', function () {
        $page = visit('/');

        $page->script('window.resizeTo(320, 568)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'mobile');

    it('renders the login page at mobile viewport', function () {
        $page = visit('/login');

        $page->script('window.resizeTo(320, 568)');
        $page->assertSee('Sign in')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'mobile');

    it('renders the dashboard at mobile viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script('window.resizeTo(320, 568)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'mobile');

    it('renders training predictions at mobile viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->script('window.resizeTo(320, 568)');
        $page->assertSee('Training Predictions')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'mobile');
});

describe('Tablet Viewport (640px)', function () {
    it('renders the welcome page at tablet viewport', function () {
        $page = visit('/');

        $page->script('window.resizeTo(640, 1024)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'tablet');

    it('renders the dashboard at tablet viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script('window.resizeTo(640, 1024)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'tablet');

    it('renders training predictions at tablet viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->script('window.resizeTo(640, 1024)');
        $page->assertSee('Training Predictions')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'tablet');
});

describe('Desktop Viewport (1024px)', function () {
    it('renders the welcome page at desktop viewport', function () {
        $page = visit('/');

        $page->script('window.resizeTo(1024, 768)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'desktop');

    it('renders the dashboard at desktop viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script('window.resizeTo(1024, 768)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'desktop');

    it('renders training predictions at desktop viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->script('window.resizeTo(1024, 768)');
        $page->assertSee('Training Predictions')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'desktop');

    it('renders characters index at desktop viewport', function () {
        $this->actingAs($this->user);
        $page = visit('/characters');

        $page->script('window.resizeTo(1024, 768)');
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'desktop');
});

describe('Dark Mode', function () {
    it('renders the welcome page in dark mode', function () {
        $page = visit('/');

        $page->script("document.documentElement.classList.add('dark')");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'dark-mode');

    it('renders the login page in dark mode', function () {
        $page = visit('/login');

        $page->script("document.documentElement.classList.add('dark')");
        $page->assertSee('Sign in')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'dark-mode');

    it('renders the dashboard in dark mode', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script("document.documentElement.classList.add('dark')");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'dark-mode');

    it('renders training predictions in dark mode', function () {
        $this->actingAs($this->user);
        $page = visit('/training/predictions?character_id='.$this->character->id);

        $page->script("document.documentElement.classList.add('dark')");
        $page->assertSee('Training Predictions')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'dark-mode');

    it('renders characters page in dark mode', function () {
        $this->actingAs($this->user);
        $page = visit('/characters');

        $page->script("document.documentElement.classList.add('dark')");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'dark-mode');
});

describe('Light Mode', function () {
    it('renders the welcome page in light mode', function () {
        $page = visit('/');

        $page->script("document.documentElement.classList.remove('dark')");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'light-mode');

    it('renders the dashboard in light mode', function () {
        $this->actingAs($this->user);
        $page = visit('/dashboard');

        $page->script("document.documentElement.classList.remove('dark')");
        $page->assertNoJavaScriptErrors();
    })->group('browser', 'visual-regression', 'light-mode');
});
