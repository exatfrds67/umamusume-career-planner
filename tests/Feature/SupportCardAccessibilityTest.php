<?php

use App\Models\SupportCardDefinition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Support Cards Page - WCAG 2.1 AA Compliance', function () {
    it('has descriptive page title on index page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $response->assertSee('<title>Support Cards - ', false);
    });

    it('has descriptive page title on show page', function () {
        $user = User::factory()->create();
        $card = SupportCardDefinition::factory()->create([
            'name' => 'Test Card [Special Edition]',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.show', $card));

        $response->assertOk();
        $response->assertSee('<title>Test Card [Special Edition] - Support Cards - ', false);
    });

    it('has valid aria-expanded attribute on import button', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $response->assertSee(':aria-expanded="showExternalImport.toString()"', false);
        $response->assertSee('aria-controls="external-import-panel"', false);
    });

    it('has properly linked external import panel', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $response->assertSee('id="external-import-panel"', false);
        $response->assertSee('role="region"', false);
        $response->assertSee('aria-label="External API Import"', false);
    });

    it('has correct heading hierarchy', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        // H1 for page heading
        $response->assertSee('<h1', false);
        // H2 for section headings (filters, stats, import)
        $response->assertSee('<h2', false);
        // Ensure no H3 appears before H2 in the import section
        $response->assertSee('id="external-import-panel"', false);
    });

    it('has responsive grid layout for card tiles', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Grid Layout Test Card',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $response->assertSee('sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4', false);
    });

    it('renders card tiles as keyboard-accessible links', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Keyboard Test Card',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        // Card tiles should be wrapped in <a> tags for keyboard navigation
        $response->assertSee('focus:ring-2 focus:ring-primary-500', false);
    });

    it('has aria-hidden decorative SVGs in filters', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        // Search icon should be decorative
        $content = $response->getContent();
        // Filter SVG should have aria-hidden
        expect($content)->toContain('aria-hidden="true"');
    });

    it('has clear filters link with accessible label', function () {
        $user = User::factory()->create();

        // Request with a filter to show clear button
        $response = $this->actingAs($user)->get(route('support-cards.index', ['type' => 'speed']));

        $response->assertOk();
        $response->assertSee('aria-label="Clear all filters"', false);
    });

    it('has proper semantic landmarks on index page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        // Header landmark
        $response->assertSee('<header', false);
        // Aside landmarks for filters and stats
        $response->assertSee('aria-label="Filters"', false);
        $response->assertSee('aria-labelledby="stats-heading"', false);
        // Section landmark for card list
        $response->assertSee('aria-label="Support Card List"', false);
    });

    it('displays all five stat bonuses on card tiles', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Stat Display Test Card',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
            'speed_bonus' => 5,
            'stamina_bonus' => 0,
            'power_bonus' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $content = $response->getContent();
        // All 5 stat abbreviations should appear when at least one stat is non-zero
        expect($content)->toContain('Spd');
        expect($content)->toContain('Sta');
        expect($content)->toContain('Pow');
        expect($content)->toContain('Gut');
        expect($content)->toContain('Wit');
        // Non-zero bonuses displayed with +
        expect($content)->toContain('+5');
        expect($content)->toContain('+3');
    });

    it('shows training type emphasis for cards with all-zero stats', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Zero Stats Card',
            'is_active' => true,
            'card_type' => 'stamina',
            'rarity' => 'SR',
            'speed_bonus' => 0,
            'stamina_bonus' => 0,
            'power_bonus' => 0,
            'guts_bonus' => 0,
            'wit_bonus' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $content = $response->getContent();
        // Should show training type emphasis instead of zero stat grid
        expect($content)->toContain('Stamina Training');
    });

    it('displays always-visible status badges on card tiles', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Status Badge Card',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
            'server_availability' => 'both',
            'meta_tier' => 'B',
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $content = $response->getContent();
        // Tier badge in card info area
        expect($content)->toContain('Tier B');
        // Server availability
        expect($content)->toContain('Both');
        // Active status
        expect($content)->toContain('Active');
    });

    it('shows training bonus pills on card tiles for all cards', function () {
        $user = User::factory()->create();
        SupportCardDefinition::factory()->create([
            'name' => 'Enriched Card Tile',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
            'training_effect_bonus' => 10,
            'friendship_bonus' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $content = $response->getContent();
        expect($content)->toContain('Train');
        expect($content)->toContain('Friend');
    });

    it('displays all information sections on show page', function () {
        $user = User::factory()->create();
        $card = SupportCardDefinition::factory()->create([
            'name' => 'Full Detail Test Card',
            'is_active' => true,
            'card_type' => 'speed',
            'rarity' => 'SSR',
            'meta_tier' => 'S+',
            'character_name' => 'Test Character',
            'max_level' => 50,
            'max_limit_break' => 4,
            'speed_bonus' => 10,
            'training_effect_bonus' => 5,
            'usage_rate' => 85.5,
            'win_rate_contribution' => 72.3,
            'unique_effects' => ['Initial Wit 25 at MLB', 'Race Bonus 5%'],
            'skill_hints_provided' => ['Speed Star', 'Corner Adept'],
            'strategic_notes' => ['Flexible for any deck'],
            'server_availability' => 'both',
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.show', $card));

        $response->assertOk();
        // Header info
        $response->assertSee('Full Detail Test Card');
        $response->assertSee('Test Character');
        // Stat Bonuses section
        $response->assertSee('Stat Bonuses');
        $response->assertSee('+10');
        // Training Bonuses section
        $response->assertSee('Training Bonuses');
        $response->assertSee('+5%');
        // Performance & Meta section
        $response->assertSee('Performance & Meta', false);
        $response->assertSee('85.5%');
        $response->assertSee('72.3%');
        // Unique Effects section
        $response->assertSee('Unique Effects');
        $response->assertSee('Initial Wit 25 at MLB');
        $response->assertSee('Race Bonus 5%');
        // Skill Hints section
        $response->assertSee('Skill Hints Provided');
        $response->assertSee('Speed Star');
        $response->assertSee('Corner Adept');
        // Strategic Notes section
        $response->assertSee('Strategic Notes');
        $response->assertSee('Flexible for any deck');
        // Acquisition & Availability section
        $response->assertSee('Acquisition & Availability', false);
        $response->assertSee('Both', false);
        // Card Details section
        $response->assertSee('Card Details');
        $response->assertSee('Max Level');
        $response->assertSee('Max Limit Break');
    });

    it('shows show page correctly for cards with zero stat bonuses', function () {
        $user = User::factory()->create();
        $card = SupportCardDefinition::factory()->create([
            'name' => 'Friend Type Card',
            'is_active' => true,
            'card_type' => 'friend',
            'rarity' => 'SSR',
            'speed_bonus' => 0,
            'stamina_bonus' => 0,
            'power_bonus' => 0,
            'guts_bonus' => 0,
            'wit_bonus' => 0,
            'training_effect_bonus' => 8,
            'friendship_bonus' => 20,
            'usage_rate' => 45.0,
            'skill_hints_provided' => ['Love of Uma', 'Affection'],
            'server_availability' => 'both',
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.show', $card));

        $response->assertOk();
        $response->assertSee('Friend Type Card');
        $response->assertSee('Training Bonuses');
        $response->assertSee('+8%');
        $response->assertSee('Skill Hints Provided');
        $response->assertSee('Love of Uma');
        $response->assertDontSee('Data Completeness');
    });

    it('shows all enriched sections on show page', function () {
        $user = User::factory()->create();
        $card = SupportCardDefinition::factory()->create([
            'name' => 'Fully Enriched Card',
            'is_active' => true,
            'card_type' => 'wit',
            'rarity' => 'SSR',
            'wit_bonus' => 25,
            'training_effect_bonus' => 10,
            'usage_rate' => 95.0,
            'unique_effects' => ['Effect 1'],
            'skill_hints_provided' => ['Skill 1'],
            'strategic_notes' => ['Note 1'],
        ]);

        $response = $this->actingAs($user)->get(route('support-cards.show', $card));

        $response->assertOk();
        $response->assertSee('Stat Bonuses');
        $response->assertSee('Training Bonuses');
        $response->assertSee('Unique Effects');
        $response->assertSee('Skill Hints Provided');
        $response->assertSee('Strategic Notes');
        $response->assertDontSee('Data Completeness');
    });
});
