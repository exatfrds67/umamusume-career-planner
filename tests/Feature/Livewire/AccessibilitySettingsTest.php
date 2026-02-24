<?php

use App\Livewire\Settings\AccessibilitySettings;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the accessibility settings component', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.settings.accessibility-settings');
});

it('loads default accessibility settings on mount', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->assertSet('reducedMotion', false)
        ->assertSet('highContrast', false)
        ->assertSet('fontSize', 'medium')
        ->assertSet('focusIndicators', true)
        ->assertSet('screenReaderOptimization', false)
        ->assertSet('colorBlindMode', 'none');
});

it('loads saved accessibility settings on mount', function () {
    $this->user->accessibility_settings = [
        'reduced_motion' => true,
        'high_contrast' => true,
        'font_size' => 'large',
        'focus_indicators' => false,
        'screen_reader_optimization' => true,
        'color_blind_mode' => 'deuteranopia',
    ];
    $this->user->save();

    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->assertSet('reducedMotion', true)
        ->assertSet('highContrast', true)
        ->assertSet('fontSize', 'large')
        ->assertSet('focusIndicators', false)
        ->assertSet('screenReaderOptimization', true)
        ->assertSet('colorBlindMode', 'deuteranopia');
});

it('saves reduced motion preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('reducedMotion', true)
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['reduced_motion'])->toBeTrue();
});

it('saves high contrast preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('highContrast', true)
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['high_contrast'])->toBeTrue();
});

it('saves font size preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('fontSize', 'large')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['font_size'])->toBe('large');
});

it('saves focus indicators preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('focusIndicators', false)
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['focus_indicators'])->toBeFalse();
});

it('saves screen reader optimization preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('screenReaderOptimization', true)
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['screen_reader_optimization'])->toBeTrue();
});

it('saves color blind mode preference', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('colorBlindMode', 'protanopia')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->accessibility_settings['color_blind_mode'])->toBe('protanopia');
});

it('resets all settings to defaults', function () {
    $this->user->accessibility_settings = [
        'reduced_motion' => true,
        'high_contrast' => true,
        'font_size' => 'extra-large',
        'focus_indicators' => false,
        'screen_reader_optimization' => true,
        'color_blind_mode' => 'tritanopia',
    ];
    $this->user->save();

    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->assertSet('reducedMotion', true)
        ->assertSet('highContrast', true)
        ->call('resetToDefaults')
        ->assertSet('reducedMotion', false)
        ->assertSet('highContrast', false)
        ->assertSet('fontSize', 'medium')
        ->assertSet('focusIndicators', true)
        ->assertSet('screenReaderOptimization', false)
        ->assertSet('colorBlindMode', 'none');

    $this->user->refresh();
    expect($this->user->accessibility_settings['reduced_motion'])->toBeFalse();
    expect($this->user->accessibility_settings['font_size'])->toBe('medium');
});

it('dispatches accessibility-updated event on save', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('highContrast', true)
        ->assertDispatched('accessibility-updated');
});

it('persists multiple settings changes', function () {
    Livewire::actingAs($this->user)
        ->test(AccessibilitySettings::class)
        ->set('reducedMotion', true)
        ->set('highContrast', true)
        ->set('fontSize', 'extra-large');

    $this->user->refresh();
    expect($this->user->accessibility_settings['reduced_motion'])->toBeTrue();
    expect($this->user->accessibility_settings['high_contrast'])->toBeTrue();
    expect($this->user->accessibility_settings['font_size'])->toBe('extra-large');
});

it('requires authentication to access settings page', function () {
    $this->get('/settings/accessibility')
        ->assertRedirect('/');
});

it('allows authenticated users to access settings page', function () {
    $this->actingAs($this->user)
        ->get('/settings/accessibility')
        ->assertSuccessful();
});
