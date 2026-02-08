<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Browser-based performance tests for External Data Browse page.
 *
 * Tests page load, tab navigation, and rendering behavior
 * using Pest v4 browser API.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->alpineSelector = 'document.querySelector(\'[x-data*="externalDataBrowser"]\')';
});

it('loads the external data browse page successfully', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser');
})->group('performance', 'browser', 'external-data');

it('renders tab navigation elements', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('Characters')
        ->assertSee('Support Cards')
        ->assertSee('Skills')
        ->assertSee('News & Updates');
})->group('performance', 'browser', 'external-data');

it('switches between tabs without errors', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'support-cards'");
    $page->wait(0.5);
    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'skills'");
    $page->wait(0.5);
    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'news'");
    $page->wait(0.5);
    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'characters'");
    $page->wait(0.5);

    $activeTab = $page->script("Alpine.\$data({$this->alpineSelector}).activeTab");
    expect($activeTab)->toBe('characters');
})->group('performance', 'browser', 'external-data');

it('renders search input for characters tab', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertPresent('[placeholder="Search characters..."]');
})->group('performance', 'browser', 'external-data');

it('renders sort dropdown for characters tab', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser')
        ->assertSee('Sort:')
        ->assertSourceHas('ID (Low to High)');
})->group('performance', 'browser', 'external-data');

it('handles injected data rendering', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        var testChars = [];
        for (var i = 1; i <= 5; i++) {
            testChars.push({
                id: i,
                name_en: 'Character ' + i,
                name_jp: 'キャラ' + i,
                category_label_en: 'Short',
                color_main: '#3b82f6',
                thumb_img: null
            });
        }
        c.characters = testChars;
        c.filteredCharacters = testChars;
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('Character 1')
        ->assertSee('Character 5');
})->group('performance', 'browser', 'external-data');

it('renders correctly on mobile viewport', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse')
        ->on()->mobile();

    $page->assertSee('External Data Browser');
})->group('performance', 'browser', 'external-data', 'responsive');

it('renders correctly in dark mode', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse')
        ->inDarkMode();

    $page->assertSee('External Data Browser');
})->group('performance', 'browser', 'external-data', 'dark-mode');
