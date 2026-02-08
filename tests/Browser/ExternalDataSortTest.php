<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * External Data Browser Sort Functionality Test
 *
 * Tests the sort functionality in the External Data Browser
 * using Pest v4 browser API. Test data is injected via Alpine.js script().
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->alpineSelector = 'document.querySelector(\'[x-data*="externalDataBrowser"]\')';
});

it('can sort characters by name ascending', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 3, name_en: 'Zenith', name_jp: 'ゼニス', category_label_en: 'Long', color_main: '#ef4444' },
            { id: 1, name_en: 'Alpha', name_jp: 'アルファ', category_label_en: 'Short', color_main: '#3b82f6' },
            { id: 2, name_en: 'Midway', name_jp: 'ミッドウェイ', category_label_en: 'Mile', color_main: '#10b981' },
        ];
        c.sortBy = 'name-asc';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $names = $page->script("Alpine.\$data({$this->alpineSelector}).filteredCharacters.map(c => c.name_en)");
    expect($names)->toBe(['Alpha', 'Midway', 'Zenith']);
})->group('browser', 'external-data', 'sort');

it('can sort characters by name descending', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 1, name_en: 'Alpha', name_jp: 'アルファ', category_label_en: 'Short', color_main: '#3b82f6' },
            { id: 2, name_en: 'Midway', name_jp: 'ミッドウェイ', category_label_en: 'Mile', color_main: '#10b981' },
            { id: 3, name_en: 'Zenith', name_jp: 'ゼニス', category_label_en: 'Long', color_main: '#ef4444' },
        ];
        c.sortBy = 'name-desc';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $names = $page->script("Alpine.\$data({$this->alpineSelector}).filteredCharacters.map(c => c.name_en)");
    expect($names)->toBe(['Zenith', 'Midway', 'Alpha']);
})->group('browser', 'external-data', 'sort');

it('can sort characters by ID ascending', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 30, name_en: 'Third', name_jp: 'サード', category_label_en: 'Long', color_main: '#ef4444' },
            { id: 10, name_en: 'First', name_jp: 'ファースト', category_label_en: 'Short', color_main: '#3b82f6' },
            { id: 20, name_en: 'Second', name_jp: 'セカンド', category_label_en: 'Mile', color_main: '#10b981' },
        ];
        c.sortBy = 'id-asc';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $ids = $page->script("Alpine.\$data({$this->alpineSelector}).filteredCharacters.map(c => c.id)");
    expect($ids)->toBe([10, 20, 30]);
})->group('browser', 'external-data', 'sort');

it('can sort characters by ID descending', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 10, name_en: 'First', name_jp: 'ファースト', category_label_en: 'Short', color_main: '#3b82f6' },
            { id: 20, name_en: 'Second', name_jp: 'セカンド', category_label_en: 'Mile', color_main: '#10b981' },
            { id: 30, name_en: 'Third', name_jp: 'サード', category_label_en: 'Long', color_main: '#ef4444' },
        ];
        c.sortBy = 'id-desc';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $ids = $page->script("Alpine.\$data({$this->alpineSelector}).filteredCharacters.map(c => c.id)");
    expect($ids)->toBe([30, 20, 10]);
})->group('browser', 'external-data', 'sort');

it('maintains sort state when switching tabs', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("Alpine.\$data({$this->alpineSelector}).sortBy = 'name-desc'");
    $page->wait(0.3);

    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'support-cards'");
    $page->wait(0.3);
    $page->script("Alpine.\$data({$this->alpineSelector}).activeTab = 'characters'");
    $page->wait(0.3);

    $sortBy = $page->script("Alpine.\$data({$this->alpineSelector}).sortBy");
    expect($sortBy)->toBe('name-desc');
})->group('browser', 'external-data', 'sort');

it('can sort with search filter applied', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 1, name_en: 'Special Week', name_jp: 'スペシャルウィーク', category_label_en: 'Short', color_main: '#3b82f6' },
            { id: 2, name_en: 'Silence Suzuka', name_jp: 'サイレンススズカ', category_label_en: 'Mile', color_main: '#10b981' },
            { id: 3, name_en: 'Special King', name_jp: 'スペシャルキング', category_label_en: 'Long', color_main: '#ef4444' },
            { id: 4, name_en: 'Tokai Teio', name_jp: 'トウカイテイオー', category_label_en: 'Medium', color_main: '#f59e0b' },
        ];
        c.searchTerm = 'Special';
        c.sortBy = 'name-asc';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $names = $page->script("Alpine.\$data({$this->alpineSelector}).filteredCharacters.map(c => c.name_en)");
    expect($names)->toBe(['Special King', 'Special Week']);
})->group('browser', 'external-data', 'sort');

it('shows empty state when search yields no results', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser');
    $page->wait(8);

    $page->script("
        var c = Alpine.\$data({$this->alpineSelector});
        c.characters = [
            { id: 1, name_en: 'Special Week', name_jp: 'スペシャルウィーク', category_label_en: 'Short', color_main: '#3b82f6' },
        ];
        c.searchTerm = 'NonExistentCharacter12345';
        c.filterData();
        c.loading = false;
    ");
    $page->wait(1);

    $page->assertSee('No characters found');
})->group('browser', 'external-data', 'sort');

it('renders sort dropdown options correctly', function () {
    $this->actingAs($this->user);
    $page = visit('/external-data/browse');

    $page->assertSee('External Data Browser')
        ->assertSourceHas('ID (Low to High)')
        ->assertSourceHas('ID (High to Low)')
        ->assertSourceHas('Name (A-Z)')
        ->assertSourceHas('Name (Z-A)');
})->group('browser', 'external-data', 'sort');
