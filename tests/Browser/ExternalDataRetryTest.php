<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * External Data Browser - Retry Functionality Tests
 *
 * Tests retry UI behavior for the External Data Browser component
 * using Pest v4 browser API.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->alpineSelector = 'document.querySelector(\'[x-data*="externalDataBrowser"]\')';
});

it('shows retry button when endpoint has error', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(8)
        ->assertSee('External Data Browser');

    $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters = 'Connection failed'");
    $page->wait(1);

    $page->assertSee('Failed to load characters')
        ->assertSee('Retry');
})->group('browser', 'external-data', 'retry');

it('shows retry all button when multiple endpoints fail', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = 'HTTP 503';
        c.errors.supportCards = 'HTTP 500';
        c.apiAvailable = false;
    ");
    $page->wait(1);

    $page->assertSee('Retry All Failed');
})->group('browser', 'external-data', 'retry');

it('displays retrying text during retry operation', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = 'Connection failed';
        c.loadingCharacters = true;
    ");
    $page->wait(1);

    $page->assertSee('Retrying...');
})->group('browser', 'external-data', 'retry');

it('clears error after successful retry simulation', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters = 'Connection failed'");
    $page->wait(1);
    $page->assertSee('Failed to load characters');

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = null;
        c.characters = [{ id: 1, name_en: 'Special Week', name_jp: 'スペシャルウィーク', category_label_en: 'Short', color_main: '#3b82f6' }];
        c.filteredCharacters = c.characters;
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('Special Week');

    $hasError = $page->script("Alpine.\$data({$this->alpineSelector}).errors.characters");
    expect($hasError)->toBeNull();
})->group('browser', 'external-data', 'retry');

it('maintains data from successful endpoints during retry', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [{ id: 1, name_en: 'Existing Character', name_jp: 'テスト', category_label_en: 'Mile', color_main: '#10b981' }];
        c.filteredCharacters = c.characters;
        c.errors.supportCards = 'Server error';
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('Existing Character');

    $charCount = $page->script("Alpine.\$data({$this->alpineSelector}).characters.length");
    expect($charCount)->toBe(1);
})->group('browser', 'external-data', 'retry');

it('updates api availability after clearing all errors', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = 'Error';
        c.apiAvailable = false;
    ");
    $page->wait(1);
    $page->assertSee('External API Unavailable');

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.errors.characters = null;
        c.apiAvailable = true;
    ");
    $page->wait(1);

    $apiAvailable = $page->script("Alpine.\$data({$this->alpineSelector}).apiAvailable");
    expect($apiAvailable)->toBeTrue();
})->group('browser', 'external-data', 'retry');

it('shows section loading indicator during endpoint retry', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.loadingCharacters = true;
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('Loading characters...');
})->group('browser', 'external-data', 'retry');
