<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameRace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RacePreparationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_race_preparation_page_loads(): void
    {
        $user = User::factory()->create();
        Character::factory()->create(['user_id' => $user->id]);
        $race = GameRace::factory()->create([
            'slug' => 'tokyo-yushun-japanese-derby',
            'name_en' => 'Tokyo Yushun (Japanese Derby)',
            'grade' => 'G1',
            'distance_meters' => 2400,
        ]);

        $response = $this->actingAs($user)->get(route('races.show', $race->slug));

        $response->assertOk();
        $response->assertSee($race->name_en);
        $response->assertSee('2,400', false);
    }

    public function test_race_preparation_has_semantic_structure(): void
    {
        $user = User::factory()->create();
        Character::factory()->create(['user_id' => $user->id]);
        $race = GameRace::factory()->create([
            'slug' => 'test-semantic-race',
            'name_en' => 'Semantic Structure Test Race',
            'name_jp' => 'セマンティック構造テストレース',
            'year_in_scenario' => 2,
            'stat_requirements' => ['speed' => 500, 'stamina' => 450],
        ]);

        $response = $this->actingAs($user)->get(route('races.show', $race->slug));

        $response->assertOk();

        // Check for main and header
        $response->assertSee('<main', false);
        $response->assertSee('<header', false);

        // Check for breadcrumb nav
        $response->assertSee('<nav', false);
        $response->assertSee('aria-label="Breadcrumb"', false);

        // Check for sections with aria labels present in the current race page
        $response->assertSee('aria-labelledby="course-info-heading"', false);
        $response->assertSee('aria-labelledby="scenario-heading"', false);

        // Check for Definition Lists
        $response->assertSee('<dl', false);
        $response->assertSee('<dt', false);
        $response->assertSee('<dd', false);
    }
}
