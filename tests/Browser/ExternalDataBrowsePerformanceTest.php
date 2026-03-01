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

    $page->wait(2)
        ->assertSee('Characters')
        ->assertSee('Support Cards')
        ->assertSee('Skills')
        ->assertSee('News & Updates');
})->group('performance', 'browser', 'external-data');

it('switches between tabs without errors', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser')
        ->assertSourceHas('Support Cards')
        ->assertSourceHas('Skills')
        ->assertSourceHas('Characters')
        ->assertSourceHas('Search support cards...')
        ->assertSourceHas('Search skills...')
        ->assertSourceHas('Search characters...');
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

    $page->wait(2)
        ->assertSee('External Data Browser')
        ->assertPresent('[placeholder="Search characters..."]')
        ->type('[placeholder="Search characters..."]', 'Character')
        ->assertNoJavaScriptErrors();
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

    $page->wait(2)->assertSee('External Data Browser');
})->group('performance', 'browser', 'external-data', 'dark-mode');
