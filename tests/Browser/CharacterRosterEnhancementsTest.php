<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('persists compact mode toggle in localStorage on roster page', function () {
    Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Compact Toggle Character',
    ]);

    $this->actingAs($this->user);

    $page = visit('/characters');

    $page->assertSee('My Characters')
        ->click('[data-testid="compact-toggle"]')
        ->assertNoJavaScriptErrors()
        ->wait(1);

    $storedValue = $page->script("window.localStorage.getItem('characters:compact-mode')");
    $compactModeValue = is_array($storedValue) ? (string) ($storedValue[0] ?? '') : (string) $storedValue;

    expect($compactModeValue)->toContain('t');
})->group('browser', 'roster', 'interaction');

it('shows alphabetical grouping and pagination controls for large rosters', function () {
    Character::factory()->count(55)->create([
        'user_id' => $this->user->id,
        'is_seeded' => false,
    ]);

    $this->actingAs($this->user);

    $page = visit('/characters');

    $page->assertSee('Showing 50 of 55 characters')
        ->assertPresent('[data-testid="roster-pagination"]')
        ->assertNoJavaScriptErrors();

    $groupHeaders = $page->script("(() => {
        const headers = document.querySelectorAll('[id^=\"heading-\"]');

        return headers.length;
    })();");
    $groupHeaderCount = is_array($groupHeaders) ? (int) ($groupHeaders[0] ?? 0) : (int) $groupHeaders;

    expect($groupHeaderCount)->toBeGreaterThan(0);
})->group('browser', 'roster', 'pagination');

it('reveals back-to-top control after scrolling down', function () {
    Character::factory()->count(55)->create([
        'user_id' => $this->user->id,
        'is_seeded' => false,
    ]);

    $this->actingAs($this->user);

    $page = visit('/characters');

    $page->script('window.scrollTo(0, 1200)');
    $page->wait(2);

    $visible = $page->script("(() => {
        const el = document.querySelector('[data-testid=\"back-to-top\"]');
        if (!el) {
            return false;
        }

        return window.getComputedStyle(el).display !== 'none';
    })();");
    $isVisible = is_array($visible) ? (bool) ($visible[0] ?? false) : (bool) $visible;

    expect($isVisible)->toBeTrue();
})->group('browser', 'roster', 'accessibility');

it('switches variant details from the roster variant selector', function () {
    Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Variant Character',
        'status' => 'active',
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
    ]);

    Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Variant Character',
        'status' => 'completed',
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 600,
            'guts' => 500,
            'wit' => 400,
        ],
    ]);

    $this->actingAs($this->user);

    $page = visit('/characters');

    $page->assertSee('Showing 1 of 1 characters')
        ->assertSee('Versions')
        ->assertNoJavaScriptErrors()
        ->wait(1);

    $switchResult = $page->script("(() => {
        const selector = document.querySelector('[data-testid=\"variant-switcher\"]');
        if (!selector || selector.options.length < 2) {
            return [false, null];
        }

        const before = selector.value;
        selector.selectedIndex = 1;
        selector.dispatchEvent(new Event('change', { bubbles: true }));

        return [selector.value !== before, selector.value];
    })();");

    $didSwitch = (bool) (is_array($switchResult) ? ($switchResult[0] ?? false) : false);

    expect($didSwitch)->toBeTrue();

    $page->wait(1)
        ->assertNoJavaScriptErrors();
})->group('browser', 'roster', 'interaction');

it('supports keyboard focus on compact toggle and variant selector', function () {
    Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Keyboard Focus Character',
    ]);

    Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Keyboard Focus Character',
    ]);

    $this->actingAs($this->user);

    $page = visit('/characters');

    $focusChecks = $page->script("(() => {
        const compactToggle = document.querySelector('[data-testid=\"compact-toggle\"]');
        const variantSelector = document.querySelector('[data-testid=\"variant-switcher\"]');
        if (!compactToggle || !variantSelector) {
            return [false, false];
        }

        compactToggle.focus();
        const compactFocused = document.activeElement === compactToggle;

        variantSelector.focus();
        const variantFocused = document.activeElement === variantSelector;

        return [compactFocused, variantFocused];
    })();");

    $checks = is_array($focusChecks) ? $focusChecks : [false, false];

    expect((bool) ($checks[0] ?? false))->toBeTrue();
    expect((bool) ($checks[1] ?? false))->toBeTrue();

    $page->assertNoJavaScriptErrors();
})->group('browser', 'roster', 'accessibility');
