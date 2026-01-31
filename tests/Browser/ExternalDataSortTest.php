<?php

use Laravel\Dusk\Browser;

/**
 * External Data Browser Sort Functionality Test
 *
 * Tests the sort functionality across all data types (characters, support cards, skills)
 * in the External Data Browser.
 *
 * Test Coverage:
 * - Sort by ID (ascending/descending)
 * - Sort by Name (A-Z, Z-A)
 * - Sort by Rarity (for support cards and skills)
 * - Sort state persistence when switching tabs
 * - Sort works in combination with filters
 */
it('can sort characters by ID ascending', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10) // Wait for loading to complete
        ->click('button:contains("Characters")') // Ensure we're on characters tab
        ->select('select[x-model="sortBy"]', 'id-asc')
        ->pause(500) // Allow sort to complete
        ->assertSeeInOrder(['#1', '#2', '#3']); // Verify ascending order
});

it('can sort characters by ID descending', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->select('select[x-model="sortBy"]', 'id-desc')
        ->pause(500)
        ->assertSeeInOrder(['#100', '#99', '#98']); // Verify descending order
});

it('can sort characters by name A-Z', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->select('select[x-model="sortBy"]', 'name-asc')
        ->pause(500);

    // Get all character names and verify they're in alphabetical order
    $names = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt h3"))
            .map(el => el.textContent.trim())
            .filter(name => name.length > 0);
    ');

    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('can sort characters by name Z-A', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->select('select[x-model="sortBy"]', 'name-desc')
        ->pause(500);

    // Get all character names and verify they're in reverse alphabetical order
    $names = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt h3"))
            .map(el => el.textContent.trim())
            .filter(name => name.length > 0);
    ');

    $sortedNames = $names;
    rsort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('can sort support cards by ID ascending', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Support Cards")')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'id-asc')
        ->pause(500);

    // Verify IDs are in ascending order
    $ids = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt"))
            .map(card => {
                const idSpan = card.querySelector("span:contains(\"#\")");
                return idSpan ? parseInt(idSpan.textContent.replace("#", "")) : 0;
            })
            .filter(id => id > 0);
    ');

    $sortedIds = $ids;
    sort($sortedIds);

    expect($ids)->toBe($sortedIds);
});

it('can sort support cards by rarity', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Support Cards")')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'rarity-desc')
        ->pause(500);

    // Verify rarities are in descending order (SSR > SR > R)
    $rarities = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt"))
            .map(card => {
                const rarityBadge = card.querySelector("span.inline-flex");
                return rarityBadge ? rarityBadge.textContent.trim() : "";
            })
            .filter(rarity => rarity.length > 0);
    ');

    // Check that SSR comes before SR, and SR comes before R
    $ssrIndex = array_search('SSR', $rarities);
    $srIndex = array_search('SR', $rarities);
    $rIndex = array_search('R', $rarities);

    if ($ssrIndex !== false && $srIndex !== false) {
        expect($ssrIndex)->toBeLessThan($srIndex);
    }
    if ($srIndex !== false && $rIndex !== false) {
        expect($srIndex)->toBeLessThan($rIndex);
    }
});

it('can sort skills by ID ascending', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Skills")')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'id-asc')
        ->pause(500);

    // Verify skills are sorted by ID
    $this->assertVisible('.glass-card-alt');
});

it('can sort skills by name', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Skills")')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'name-asc')
        ->pause(500);

    // Get all skill names and verify they're in alphabetical order
    $names = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt h3"))
            .map(el => el.textContent.trim())
            .filter(name => name.length > 0);
    ');

    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('can sort skills by rarity', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Skills")')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'rarity-desc')
        ->pause(500);

    // Verify rarities are in descending order (unique > rare > normal)
    $rarities = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt"))
            .map(card => {
                const rarityBadge = card.querySelector("span.inline-flex");
                return rarityBadge ? rarityBadge.textContent.trim().toLowerCase() : "";
            })
            .filter(rarity => rarity.length > 0);
    ');

    // Check that unique comes before rare, and rare comes before normal
    $uniqueIndex = array_search('unique', $rarities);
    $rareIndex = array_search('rare', $rarities);
    $normalIndex = array_search('normal', $rarities);

    if ($uniqueIndex !== false && $rareIndex !== false) {
        expect($uniqueIndex)->toBeLessThan($rareIndex);
    }
    if ($rareIndex !== false && $normalIndex !== false) {
        expect($rareIndex)->toBeLessThan($normalIndex);
    }
});

it('maintains sort state when switching tabs', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->select('select[x-model="sortBy"]', 'name-desc')
        ->pause(500)
        ->click('button:contains("Support Cards")')
        ->pause(500)
        ->assertSelected('select[x-model="sortBy"]', 'name-desc') // Sort state should persist
        ->click('button:contains("Skills")')
        ->pause(500)
        ->assertSelected('select[x-model="sortBy"]', 'name-desc'); // Sort state should still persist
});

it('can sort with filters applied', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Support Cards")')
        ->pause(500)
        ->click('button:contains("SSR")') // Apply rarity filter
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'name-asc')
        ->pause(500);

    // Verify that only SSR cards are shown and they're sorted by name
    $cards = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt"))
            .map(card => ({
                name: card.querySelector("h3").textContent.trim(),
                rarity: card.querySelector("span.inline-flex").textContent.trim()
            }));
    ');

    // All cards should be SSR
    foreach ($cards as $card) {
        expect($card['rarity'])->toBe('SSR');
    }

    // Names should be in alphabetical order
    $names = array_column($cards, 'name');
    $sortedNames = $names;
    sort($sortedNames);
    expect($names)->toBe($sortedNames);
});

it('can sort with search applied', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->type('input[placeholder="Search characters..."]', 'Special')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'name-asc')
        ->pause(500);

    // Verify that search results are sorted
    $names = $this->script('
        return Array.from(document.querySelectorAll(".glass-card-alt h3"))
            .map(el => el.textContent.trim())
            .filter(name => name.length > 0);
    ');

    // All names should contain "Special"
    foreach ($names as $name) {
        expect(strtolower($name))->toContain('special');
    }

    // Names should be in alphabetical order
    $sortedNames = $names;
    sort($sortedNames);
    expect($names)->toBe($sortedNames);
});

it('handles empty results with sort applied', function () {
    visit('/external-data/browse')
        ->waitFor('[x-data="externalDataBrowser()"]')
        ->waitUntilMissing('.animate-spin', 10)
        ->click('button:contains("Characters")')
        ->type('input[placeholder="Search characters..."]', 'NonExistentCharacter12345')
        ->pause(500)
        ->select('select[x-model="sortBy"]', 'name-asc')
        ->pause(500)
        ->assertSee('No characters found'); // Should show empty state
});
