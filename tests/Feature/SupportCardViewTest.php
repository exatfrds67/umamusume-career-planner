<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_cards_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        $response->assertSee('Support Cards');
    }

    public function test_support_cards_has_semantic_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('support-cards.index'));

        $response->assertOk();
        // Check for header
        $response->assertSee('<header', false);
        // Check for aside (Filters & Stats)
        $response->assertSee('<aside', false);
        $response->assertSee('aria-label="Filters"', false);
        $response->assertSee('aria-labelledby="stats-heading"', false);
        // Check for headings
        $response->assertSee('<h1', false);
        $response->assertSee('<h2', false);
    }
}
