<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * External Data Browser Error Scenario Tests
 *
 * Tests error handling, partial failures, and retry functionality
 * for the External Data Browser component using Pest v4 browser API.
 *
 * Error states are injected via Alpine.js script() since Http::fake()
 * cannot intercept browser-initiated client-side API calls.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('displays the external data browser page', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(8)
        ->assertSee('External Data Browser');
})->group('browser', 'external-data', 'error-handling');

it('displays error banner when API errors are injected', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)->assertSee('External Data Browser');

    $page->wait(8)
        ->assertSourceHas('Failed to load characters')
        ->assertSourceHas('Retry All Failed');
})->group('browser', 'external-data', 'error-handling');

it('shows API unavailable banner when apiAvailable is false', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('External API Unavailable');
})->group('browser', 'external-data', 'error-handling');

it('shows retry all button when errors exist', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('Retry All Failed');
})->group('browser', 'external-data', 'error-handling');

it('shows individual retry button for failed endpoint', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('Failed to load characters')
        ->assertSourceHas('Retry');
})->group('browser', 'external-data', 'error-handling');

it('maintains loading state properties in Alpine component', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('x-data="externalDataBrowser()"')
        ->assertSourceHas('Refresh Data');
})->group('browser', 'external-data', 'error-handling');

it('preserves data when error is injected after load', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertPresent('[placeholder="Search characters..."]')
        ->assertSourceHas('Failed to load support cards');
})->group('browser', 'external-data', 'error-handling');

it('handles multiple endpoint errors simultaneously', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('External API Unavailable')
        ->assertSourceHas('Retry All Failed')
        ->assertSourceHas('Failed to load characters')
        ->assertSourceHas('Failed to load support cards')
        ->assertSourceHas('Failed to load skills')
        ->assertSourceHas('Failed to load news');
})->group('browser', 'external-data', 'error-handling');

it('works correctly in dark mode with errors', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse')
        ->inDarkMode();

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertSourceHas('Failed to load characters');
})->group('browser', 'external-data', 'error-handling', 'dark-mode');
