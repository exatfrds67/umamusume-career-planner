<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('critical alert badge is present in navigation header', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Check for the rendered badge button with the dispatch event
    $response->assertSee("@click=\"\$dispatch('open-advisory-panel'", false);
    $response->assertSee('Critical Alert Badge', false);
});

test('critical alert badge shows correct alert count in navigation', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Default alert count should be 0, so badge should show "No critical alerts"
    $response->assertSee('No critical alerts', false);
});

test('critical alert badge dispatches open advisory panel event on click', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Check that the badge has the Alpine.js event dispatcher
    $response->assertSee("@click=\"\$dispatch('open-advisory-panel'", false);
    $response->assertSee("section: 'alerts'", false);
});

test('critical alert badge is accessible with proper aria labels', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Badge should have proper accessibility attributes
    $response->assertSee('aria-label', false);
    $response->assertSee('sr-only', false);
});

test('critical alert badge icon is visible', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Check for the alert triangle icon SVG path
    $response->assertSee('M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z', false);
});

test('critical alert badge has correct styling classes', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertSuccessful();
    // Check for transition and color classes
    $response->assertSee('transition-colors duration-200', false);
    $response->assertSee('text-neutral-400 hover:text-neutral-500', false);
});
