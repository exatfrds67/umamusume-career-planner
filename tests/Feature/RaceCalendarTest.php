<?php

declare(strict_types=1);

use App\Models\GameRace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Race Calendar Index', function () {
    it('loads the race calendar page for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Race Calendar');
    });

    it('redirects guests to the welcome page', function () {
        $this->get(route('races.index'))
            ->assertRedirect(route('welcome'));
    });

    it('displays races from the catalog', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Japan Derby', 'grade' => 'G1', 'season' => 'spring', 'year_in_scenario' => 2]);

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Japan Derby');
    });

    it('filters races by grade', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'G1 Race', 'grade' => 'G1', 'season' => 'spring']);
        GameRace::factory()->create(['name_en' => 'G3 Race', 'grade' => 'G3', 'season' => 'summer']);

        $this->actingAs($user)
            ->get(route('races.index', ['grade' => 'G1']))
            ->assertOk()
            ->assertSee('G1 Race')
            ->assertDontSee('G3 Race');
    });

    it('filters races by season', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Spring Race', 'season' => 'spring']);
        GameRace::factory()->create(['name_en' => 'Winter Race', 'season' => 'winter']);

        $this->actingAs($user)
            ->get(route('races.index', ['season' => 'spring']))
            ->assertOk()
            ->assertSee('Spring Race')
            ->assertDontSee('Winter Race');
    });

    it('filters races by surface', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Turf Race', 'surface' => 'turf']);
        GameRace::factory()->create(['name_en' => 'Dirt Race', 'surface' => 'dirt']);

        $this->actingAs($user)
            ->get(route('races.index', ['surface' => 'turf']))
            ->assertOk()
            ->assertSee('Turf Race')
            ->assertDontSee('Dirt Race');
    });

    it('filters races by distance category', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Sprint Race', 'distance_category' => 'sprint']);
        GameRace::factory()->create(['name_en' => 'Long Race', 'distance_category' => 'long']);

        $this->actingAs($user)
            ->get(route('races.index', ['distance' => 'sprint']))
            ->assertOk()
            ->assertSee('Sprint Race')
            ->assertDontSee('Long Race');
    });

    it('filters races by venue', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Tokyo Race', 'venue' => 'Tokyo']);
        GameRace::factory()->create(['name_en' => 'Kyoto Race', 'venue' => 'Kyoto']);

        $this->actingAs($user)
            ->get(route('races.index', ['venue' => 'Tokyo']))
            ->assertOk()
            ->assertSee('Tokyo Race')
            ->assertDontSee('Kyoto Race');
    });

    it('filters races by minimum fan requirement', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'High Fan Race', 'fan_requirement' => 1000]);
        GameRace::factory()->create(['name_en' => 'No Fan Race', 'fan_requirement' => 0]);

        $this->actingAs($user)
            ->get(route('races.index', ['min_fans' => 500]))
            ->assertOk()
            ->assertSee('High Fan Race')
            ->assertDontSee('No Fan Race');
    });

    it('can sort races by grade', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index', ['sort' => 'grade']))
            ->assertOk();
    });

    it('can sort races by distance', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index', ['sort' => 'distance']))
            ->assertOk();
    });

    it('can sort races by fan requirement', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index', ['sort' => 'fans']))
            ->assertOk();
    });

    it('ignores invalid sort values', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index', ['sort' => 'invalid_column; DROP TABLE game_races;--']))
            ->assertOk();
    });

    it('shows the empty state when no races match filters', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['grade' => 'G3', 'season' => 'spring']);

        $this->actingAs($user)
            ->get(route('races.index', ['grade' => 'G1']))
            ->assertOk()
            ->assertSee('No races found');
    });

    it('shows active filter chips when filters are applied', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['grade' => 'G1', 'season' => 'spring']);

        $this->actingAs($user)
            ->get(route('races.index', ['grade' => 'G1']))
            ->assertOk()
            ->assertSee('G1');
    });

    it('renders the race-row component for each race', function () {
        $user = User::factory()->create();

        GameRace::factory()->create(['name_en' => 'Takarazuka Kinen', 'grade' => 'G1', 'season' => 'summer']);

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Takarazuka Kinen');
    });

    it('renders explicit surface text labels for accessibility', function () {
        $user = User::factory()->create();

        GameRace::factory()->create([
            'name_en' => 'Turf Label Race',
            'surface' => 'turf',
            'season' => 'spring',
        ]);

        GameRace::factory()->create([
            'name_en' => 'Dirt Label Race',
            'surface' => 'dirt',
            'season' => 'summer',
        ]);

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Surface: Turf')
            ->assertSee('Surface: Dirt');
    });

    it('shows concise filtered result summary with active filters', function () {
        $user = User::factory()->create();

        GameRace::factory()->create([
            'name_en' => 'Filtered G1 Race',
            'grade' => 'G1',
            'season' => 'spring',
        ]);

        GameRace::factory()->create([
            'name_en' => 'Filtered G3 Race',
            'grade' => 'G3',
            'season' => 'spring',
        ]);

        $this->actingAs($user)
            ->get(route('races.index', ['grade' => 'G1']))
            ->assertOk()
            ->assertSee('Active filters:')
            ->assertSee('data-testid="race-summary-bar"', false)
            ->assertSee('Showing 1 of 1 races');
    });

    it('shows year quick navigation and summary context without filters', function () {
        $user = User::factory()->create();

        GameRace::factory()->create([
            'name_en' => 'Junior Spring Showcase',
            'year_in_scenario' => 1,
            'season' => 'spring',
        ]);

        GameRace::factory()->create([
            'name_en' => 'Senior Winter Showcase',
            'year_in_scenario' => 3,
            'season' => 'winter',
        ]);

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Jump to race year groups')
            ->assertSee('Year 1 — Junior')
            ->assertSee('Year 3+ — Senior')
            ->assertSee('data-testid="race-summary-bar"', false)
            ->assertSee('Showing 2 of 2 races');
    });

    it('renders distance category cue in race rows', function () {
        $user = User::factory()->create();

        GameRace::factory()->create([
            'name_en' => 'Category Cue Race',
            'distance_category' => 'medium',
            'season' => 'autumn',
        ]);

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Category: Medium');
    });

    it('passes multiple filters simultaneously', function () {
        $user = User::factory()->create();

        GameRace::factory()->create([
            'name_en' => 'Match Race',
            'grade' => 'G1',
            'season' => 'spring',
            'surface' => 'turf',
        ]);
        GameRace::factory()->create([
            'name_en' => 'No Match Race',
            'grade' => 'G1',
            'season' => 'winter',
            'surface' => 'turf',
        ]);

        $this->actingAs($user)
            ->get(route('races.index', ['grade' => 'G1', 'season' => 'spring']))
            ->assertOk()
            ->assertSee('Match Race')
            ->assertDontSee('No Match Race');
    });
});
