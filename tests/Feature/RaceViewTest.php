<?php

namespace Tests\Feature;

use App\Models\Race;
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

    public function test_race_calendar_has_semantic_structure(): void
    {
        $user = User::factory()->create();
        // Create races to ensure list is rendered
        Race::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        // Check for semantic list
        $response->assertSee('role="list"', false);
        $response->assertSee('role="listitem"', false);
        // Check for aside
        $response->assertSee('<aside', false);
        // Check for accessible headings
        $response->assertSee('<h1', false);
        $response->assertSee('<h2', false);
    }
}
