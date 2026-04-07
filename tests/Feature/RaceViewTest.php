<?php

namespace Tests\Feature;

use App\Models\GameRace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_race_calendar_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        $response->assertSee('Race Calendar');
    }

    public function test_race_calendar_displays_game_race_catalog(): void
    {
        $user = User::factory()->create();

        GameRace::query()->create([
            'slug' => 'japan-derby',
            'name_en' => 'Japan Derby',
            'name_jp' => '日本ダービー',
            'grade' => 'G1',
            'phase' => 'classic',
            'surface' => 'turf',
            'distance_meters' => 2400,
            'distance_category' => 'long',
        ]);

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        $response->assertSee('Japan Derby');
        $response->assertSee('G1');
    }

    public function test_race_calendar_displays_surface_text_label(): void
    {
        $user = User::factory()->create();

        GameRace::query()->create([
            'slug' => 'test-surface-race',
            'name_en' => 'Test Surface Race',
            'grade' => 'G3',
            'phase' => 'junior',
            'surface' => 'turf',
            'distance_meters' => 1600,
            'distance_category' => 'mile',
        ]);

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        $response->assertSee('Surface: Turf');
    }

    public function test_race_calendar_has_semantic_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        $response->assertSee('<aside', false);
        $response->assertSee('<h1', false);
        $response->assertSee('data-testid="race-summary-bar"', false);
    }

    public function test_race_catalog_filters_by_grade(): void
    {
        $user = User::factory()->create();

        GameRace::query()->create([
            'slug' => 'arima-kinen',
            'name_en' => 'Arima Kinen',
            'grade' => 'G1',
            'phase' => 'all',
            'surface' => 'turf',
            'distance_meters' => 2500,
            'distance_category' => 'long',
        ]);

        GameRace::query()->create([
            'slug' => 'test-g3-race',
            'name_en' => 'Test G3 Race',
            'grade' => 'G3',
            'phase' => 'junior',
            'surface' => 'turf',
            'distance_meters' => 1600,
            'distance_category' => 'mile',
        ]);

        $response = $this->actingAs($user)->get(route('races.index', ['grade' => 'G1']));

        $response->assertOk();
        $response->assertSee('Arima Kinen');
        $response->assertDontSee('Test G3 Race');
    }

    public function test_race_show_page_loads(): void
    {
        $user = User::factory()->create();

        GameRace::query()->create([
            'slug' => 'hopeful-stakes',
            'name_en' => 'Hopeful Stakes',
            'grade' => 'G1',
            'phase' => 'junior',
            'surface' => 'turf',
            'distance_meters' => 2000,
            'distance_category' => 'medium',
            'venue' => 'Nakayama',
        ]);

        $response = $this->actingAs($user)->get(route('races.show', 'hopeful-stakes'));

        $response->assertOk();
        $response->assertSee('Hopeful Stakes');
        $response->assertSee('Nakayama');
        $response->assertSee('2,000');
    }
}
