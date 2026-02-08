<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckBuilderViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_deck_builder_page_loads(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('characters.deck-builder', $character));

        $response->assertStatus(200);
        $response->assertViewIs('support-cards.deck-builder');
    }

    public function test_deck_builder_has_semantic_structure(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('characters.deck-builder', $character));

        $response->assertStatus(200);

        // Check for semantic tags
        $response->assertSee('<main', false);
        $response->assertSee('<header', false);
        $response->assertSee('<section', false);
        $response->assertSee('<aside', false); // Optional, or use section for library

        // Check for accessibility attributes (using aria-labelledby pattern)
        // Check specific sections
        $response->assertSee('id="slots-heading"', false);
        $response->assertSee('aria-labelledby="slots-heading"', false);
        $response->assertSee('id="library-heading"', false);
        $response->assertSee('aria-labelledby="library-heading"', false);
        $response->assertSee('id="stats-heading"', false);
        $response->assertSee('aria-labelledby="stats-heading"', false);
    }
}
