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
    $this->alpineSelector = 'document.querySelector(\'[x-data*="externalDataBrowser"]\')';
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

    $page->assertSee('External Data Browser');

    // Wait for Alpine to initialize and API calls to settle
    $page->wait(8);

    // Inject error state into Alpine.js component
    $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters = 'API returned 503: Service Unavailable'");
    $page->wait(1);

    $page->assertSee('Failed to load characters')
        ->assertSee('API returned 503: Service Unavailable');
})->group('browser', 'external-data', 'error-handling');

it('shows API unavailable banner when apiAvailable is false', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).apiAvailable = false");
    $page->wait(1);

    $page->assertSee('External API Unavailable');
})->group('browser', 'external-data', 'error-handling');

it('shows retry all button when errors exist', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = 'Connection timeout';
        c.errors.supportCards = 'Server error';
        c.apiAvailable = false;
    ");
    $page->wait(1);

    $page->assertSee('Retry All Failed');
})->group('browser', 'external-data', 'error-handling');

it('shows individual retry button for failed endpoint', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters = 'HTTP 500: Internal Server Error'");
    $page->wait(1);

    $page->assertSee('Failed to load characters')
        ->assertSee('Retry');
})->group('browser', 'external-data', 'error-handling');

it('maintains loading state properties in Alpine component', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)->assertSee('External Data Browser');
    $page->wait(8);

    $hasLoadingProp = $page->script("typeof Alpine.\$data({$this->alpineSelector}).loading === 'boolean'");
    expect($hasLoadingProp)->toBeTrue();
})->group('browser', 'external-data', 'error-handling');

it('preserves data when error is injected after load', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [{ id: 1, name_en: 'Test Character', name_jp: 'テスト', category_label_en: 'Short', color_main: '#ff0000' }];
        c.filteredCharacters = c.characters;
        c.errors.supportCards = 'Failed to load';
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('Test Character');

    $charCount = $page->script("Alpine.\$data({$this->alpineSelector}).characters.length");
    expect($charCount)->toBe(1);
})->group('browser', 'external-data', 'error-handling');

it('handles multiple endpoint errors simultaneously', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = 'HTTP 503';
        c.errors.supportCards = 'HTTP 500';
        c.errors.skills = 'Timeout';
        c.errors.news = 'HTTP 404';
        c.apiAvailable = false;
    ");
    $page->wait(1);

    $page->assertSee('External API Unavailable')
        ->assertSee('Retry All Failed');

    $errorCount = $page->script("Object.values(Alpine.\$data({$this->alpineSelector}).errors).filter(e => e !== null).length");
    expect($errorCount)->toBe(4);
})->group('browser', 'external-data', 'error-handling');

it('works correctly in dark mode with errors', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse')
        ->inDarkMode();

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters = 'API unavailable'");
    $page->wait(1);

    $page->assertSee('Failed to load characters');
})->group('browser', 'external-data', 'error-handling', 'dark-mode');
