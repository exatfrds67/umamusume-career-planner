<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingDetailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_detail_page_loads(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

        $response->assertOk();
        $response->assertSee($character->name);
    }

    public function test_training_detail_has_semantic_structure(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

        $response->assertOk();

        // Check for main and header
        $response->assertSee('<main', false);
        $response->assertSee('<header', false);

        // Check for sections with aria labels
        $response->assertSee('aria-labelledby="stats-heading"', false);
        $response->assertSee('aria-labelledby="status-heading"', false);
        $response->assertSee('aria-labelledby="scenario-heading"', false);
        $response->assertSee('aria-labelledby="support-cards-heading"', false);

        // Check for progressbar role
        $response->assertSee('role="progressbar"', false);
        $response->assertSee('aria-labelledby="energy-label"', false);

        // Check for breadcrumb nav
        $response->assertSee('<nav', false);
        $response->assertSee('aria-label="Breadcrumb"', false);
    }
}
