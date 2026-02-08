<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIDashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_dashboard_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai.dashboard'));

        $response->assertOk();
        $response->assertSee('AI Management Dashboard');
    }

    public function test_ai_dashboard_has_semantic_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai.dashboard'));

        $response->assertOk();
        // Check for sections and aria labels
        $response->assertSee('aria-label="Summary Statistics"', false);
        $response->assertSee('aria-labelledby="mcp-server-status-title"', false);
        $response->assertSee('aria-labelledby="performance-comparison-title"', false);
        $response->assertSee('aria-labelledby="cost-summary-title"', false);

        // Check for headings
        $response->assertSee('id="mcp-server-status-title"', false);
        $response->assertSee('id="performance-comparison-title"', false);
        $response->assertSee('id="cost-summary-title"', false);

        // Check for progressbar role
        $response->assertSee('role="progressbar"', false);
        $response->assertSee('aria-label="Budget status"', false);
    }
}
