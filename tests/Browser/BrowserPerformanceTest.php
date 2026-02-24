<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Browser Performance Tests
 *
 * Tests page load times and JavaScript execution across pages.
 * Verifies performance meets NFR targets.
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 * Validates: Requirements NFR-P-01, NFR-C-03 through NFR-C-08
 *
 * @group browser
 * @group performance
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Performance Test Character',
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

describe('Page Load Performance', function () {
    it('loads the welcome page without JavaScript errors', function () {
        $startTime = microtime(true);
        $page = visit('/');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the login page without JavaScript errors', function () {
        $startTime = microtime(true);
        $page = visit('/login');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertSee('Sign in')
            ->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the dashboard without JavaScript errors', function () {
        $this->actingAs($this->user);

        $startTime = microtime(true);
        $page = visit('/dashboard');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the training predictions page without JavaScript errors', function () {
        $this->actingAs($this->user);

        $startTime = microtime(true);
        $page = visit('/training/predictions?character_id='.$this->character->id);
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertSee('Training Predictions')
            ->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the characters index without JavaScript errors', function () {
        Character::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $startTime = microtime(true);
        $page = visit('/characters');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the skills page without JavaScript errors', function () {
        $this->actingAs($this->user);

        $startTime = microtime(true);
        $page = visit('/skills');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');

    it('loads the races page without JavaScript errors', function () {
        $this->actingAs($this->user);

        $startTime = microtime(true);
        $page = visit('/races');
        $loadTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($loadTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'page-load');
});

describe('Navigation Performance', function () {
    it('navigates between pages without JavaScript errors', function () {
        $this->actingAs($this->user);

        $page = visit('/dashboard');
        $page->assertNoJavaScriptErrors();

        $startTime = microtime(true);
        $page->navigate('/characters');
        $navTime = (microtime(true) - $startTime) * 1000;

        $page->assertNoJavaScriptErrors();
        expect($navTime)->toBeLessThan(15000);
    })->group('browser', 'performance', 'navigation');

    it('handles multiple sequential navigations', function () {
        $this->actingAs($this->user);

        $page = visit('/dashboard');

        $page->navigate('/characters')
            ->navigate('/skills')
            ->navigate('/races')
            ->navigate('/dashboard')
            ->assertNoJavaScriptErrors();
    })->group('browser', 'performance', 'navigation');
});

describe('JavaScript Execution', function () {
    it('has no console errors on public pages', function () {
        $publicPages = ['/', '/login', '/register'];

        foreach ($publicPages as $url) {
            $page = visit($url);
            $page->assertNoJavaScriptErrors();
        }
    })->group('browser', 'performance', 'javascript');

    it('has no console errors on authenticated pages', function () {
        $this->actingAs($this->user);

        $authenticatedPages = [
            '/dashboard',
            '/characters',
            '/skills',
            '/races',
        ];

        foreach ($authenticatedPages as $url) {
            $page = visit($url);
            $page->assertNoJavaScriptErrors();
        }
    })->group('browser', 'performance', 'javascript');
});

describe('Smoke Test - All Critical Pages', function () {
    it('loads all public pages without errors', function () {
        $publicPages = ['/', '/login', '/register', '/about', '/help'];

        foreach ($publicPages as $url) {
            $page = visit($url);
            $page->assertNoJavaScriptErrors();
        }
    })->group('browser', 'performance', 'smoke');

    it('loads all authenticated pages without errors', function () {
        $this->actingAs($this->user);

        $authenticatedPages = [
            '/dashboard',
            '/characters',
            '/characters/create',
            '/skills',
            '/races',
            '/settings',
        ];

        foreach ($authenticatedPages as $url) {
            $page = visit($url);
            $page->assertNoJavaScriptErrors();
        }
    })->group('browser', 'performance', 'smoke');
});
