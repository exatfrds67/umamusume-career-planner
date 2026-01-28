<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RacePreparationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_race_preparation_page_loads()
    {
        try {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $race = Race::factory()->create([
                'character_id' => $character->id,
                'race_name' => 'Tokyo Yushun (Japanese Derby)',
                'race_grade' => 'G1',
                'distance_meters' => 2400,
            ]);
        } catch (\Throwable $e) {
            dump($e->getMessage());
            throw $e;
        }

        $response = $this->actingAs($user)->get(route('races.show', $race));

        $response->assertOk();
        $response->assertSee($race->name);
        $response->assertSee('2400m');
    }

    public function test_race_preparation_has_semantic_structure()
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $race = Race::factory()->create([
            'character_id' => $character->id,
            'preparation_strategy' => ['Rest before race', 'Study opponents'],
            'performance_analysis' => ['Good stamina recommended'],
        ]);

        $response = $this->actingAs($user)->get(route('races.show', $race));

        $response->assertOk();

        // Check for main and header
        $response->assertSee('<main', false);
        $response->assertSee('<header', false);

        // Check for breadcrumb nav
        $response->assertSee('<nav', false);
        $response->assertSee('aria-label="Breadcrumb"', false);

        // Check for sections with aria labels
        $response->assertSee('aria-labelledby="race-info-title"', false);
        $response->assertSee('aria-labelledby="prep-strategy-title"', false);
        $response->assertSee('aria-labelledby="perf-analysis-title"', false);

        // Check for articles
        $response->assertSee('<article', false);

        // Check for Definition Lists
        $response->assertSee('<dl', false);
        $response->assertSee('<dt', false);
        $response->assertSee('<dd', false);
    }
}
