<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    Cache::flush();
});

// =========================================================================
// REPORTS INDEX TESTS
// =========================================================================

describe('Reports Index', function () {
    it('displays reports index page for authenticated user', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->for($character)->for($this->user)->create();

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('characters');
        $response->assertViewHas('recentCareers');
    });

    it('redirects unauthenticated users to welcome', function () {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('welcome'));
    });

    it('shows only user own characters', function () {
        $otherUser = User::factory()->create();
        Character::factory()->for($this->user)->create(['name' => 'Own Character']);
        Character::factory()->for($otherUser)->create(['name' => 'Other Character']);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Own Character');
        $response->assertDontSee('Other Character');
    });
});

// =========================================================================
// CAREER REPORT VIEW TESTS
// =========================================================================

describe('Career Report View', function () {
    it('displays career report for authorized user', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.career', $career));

        $response->assertStatus(200);
        $response->assertViewIs('reports.career');
        $response->assertViewHas('career');
        $response->assertViewHas('report');
    });

    it('denies access to other users career report', function () {
        $otherUser = User::factory()->create();
        $character = Character::factory()->for($otherUser)->create();
        $career = Career::factory()->for($character)->for($otherUser)->create();

        $response = $this->actingAs($this->user)->get(route('reports.career', $career));

        $response->assertStatus(403);
    });

    it('shows report with all sections', function () {
        $character = Character::factory()->for($this->user)->create(['name' => 'Test Character']);
        $career = Career::factory()->for($character)->for($this->user)->create([
            'scenario_type' => 'ura_finale',
        ]);

        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
        ]);

        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.career', $career));

        $response->assertStatus(200);
        $response->assertSee('Test Character');
        $response->assertSee('Executive Summary');
        $response->assertSee('Performance Metrics');
        $response->assertSee('Key Insights');
        $response->assertSee('Recommendations');
    });
});

// =========================================================================
// CHARACTER REPORT VIEW TESTS
// =========================================================================

describe('Character Report View', function () {
    it('displays character report for authorized user', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->count(2)->for($character)->for($this->user)->create();

        $response = $this->actingAs($this->user)->get(route('reports.character', $character));

        $response->assertStatus(200);
        $response->assertViewIs('reports.character');
        $response->assertViewHas('character');
        $response->assertViewHas('report');
    });

    it('denies access to other users character report', function () {
        $otherUser = User::factory()->create();
        $character = Character::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)->get(route('reports.character', $character));

        $response->assertStatus(403);
    });
});

// =========================================================================
// EXPORT TESTS
// =========================================================================

describe('Export Functionality', function () {
    it('exports career report as JSON', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export.json', $career));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        expect($data)->toHaveKey('report_metadata');
        expect($data)->toHaveKey('executive_summary');
    });

    it('exports career report as CSV', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export.csv', $career));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    });

    it('exports career report as PDF view', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.export.pdf', $career));

        $response->assertStatus(200);
        $response->assertViewIs('reports.pdf');
        $response->assertViewHas('pdfData');
    });

    it('denies export access to unauthorized users', function () {
        $otherUser = User::factory()->create();
        $character = Character::factory()->for($otherUser)->create();
        $career = Career::factory()->for($character)->for($otherUser)->create();

        $response = $this->actingAs($this->user)->get(route('reports.export.json', $career));
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->get(route('reports.export.csv', $career));
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->get(route('reports.export.pdf', $career));
        $response->assertStatus(403);
    });
});

// =========================================================================
// API ENDPOINT TESTS
// =========================================================================

describe('API Endpoints', function () {
    it('returns career report data via API', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('reports.api.career', $career));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'report' => [
                'report_metadata',
                'executive_summary',
                'performance_overview',
            ],
        ]);
    });

    it('returns character report data via API', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->for($character)->for($this->user)->create();

        $response = $this->actingAs($this->user)->getJson(route('reports.api.character', $character));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'report' => [
                'character_info',
                'career_history',
                'aggregate_statistics',
            ],
        ]);
    });

    it('clears cache via API', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        $response = $this->actingAs($this->user)->postJson(route('reports.api.clear-cache', $career));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Report cache cleared successfully.',
        ]);
    });

    it('returns 403 for unauthorized API access', function () {
        $otherUser = User::factory()->create();
        $character = Character::factory()->for($otherUser)->create();
        $career = Career::factory()->for($character)->for($otherUser)->create();

        $response = $this->actingAs($this->user)->getJson(route('reports.api.career', $career));
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->getJson(route('reports.api.character', $character));
        $response->assertStatus(403);
    });
});
