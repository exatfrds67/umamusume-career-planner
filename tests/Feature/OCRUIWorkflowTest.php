<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\OCRExtraction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * OCR UI and Workflow Tests
 *
 * Tests for Task 5.1.5: Comprehensive OCR UI and workflow
 * Requirements: Requirement 23.5
 */
beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->user->refresh(); // Ensure user is persisted
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

it('displays OCR upload page with character selection', function () {
    actingAs($this->user);

    $response = get(route('ocr.upload'));

    $response->assertStatus(200);
    $response->assertViewIs('ocr.upload');
    $response->assertViewHas('characters');
    $response->assertSee('Screenshot OCR Processing');
    $response->assertSee('Drag and drop screenshots here');
});

it('shows OCR system status on upload page', function () {
    actingAs($this->user);

    $response = get(route('ocr.upload'));

    $response->assertStatus(200);
    $response->assertSee('OCR System Status');
    $response->assertSee('ocr-status-card');
});

it('displays extraction results page', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
        'parsed_data' => [
            'stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 600,
                'guts' => 500,
                'wit' => 400,
            ],
        ],
        'confidence_score' => 0.85,
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertViewIs('ocr.results');
    $response->assertViewHas('extraction');
    $response->assertSee('OCR Extraction Results');
    $response->assertSee('85.0%'); // Confidence score
});

it('shows character stats form for character_stats extraction', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
        'parsed_data' => [
            'stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 600,
                'guts' => 500,
                'wit' => 400,
            ],
        ],
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Character Stats');
    $response->assertSee('Speed');
    $response->assertSee('Stamina');
    $response->assertSee('Power');
    $response->assertSee('Guts');
    $response->assertSee('Wit');
});

it('shows training session form for training_session extraction', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'training_session',
        'status' => 'processed',
        'parsed_data' => [
            'training_type' => 'speed',
            'stat_gains' => [
                'speed' => 50,
                'power' => 20,
            ],
        ],
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Training Type');
    $response->assertSee('Stat Gains');
});

it('shows race result form for race_result extraction', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'race_result',
        'status' => 'processed',
        'parsed_data' => [
            'race_name' => 'Japan Cup',
            'position' => 1,
            'race_grade' => 'G1',
        ],
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Race Name');
    $response->assertSee('Final Position');
    $response->assertSee('Race Grade');
});

it('shows skill list form for skill_list extraction', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'skill_list',
        'status' => 'processed',
        'parsed_data' => [
            'total_sp' => 500,
            'skills' => [
                ['name' => 'Test Skill', 'sp_cost' => 120, 'hint_level' => 2],
            ],
        ],
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Total Skill Points');
    $response->assertSee('Extracted Skills');
});

it('displays validation warnings for low confidence extractions', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
        'parsed_data' => [
            'stats' => ['speed' => 800],
            'errors' => ['Only 1 stats extracted (expected 5)'],
        ],
        'confidence_score' => 0.45,
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('⚠️ Review recommended');
    $response->assertSee('Validation Warnings');
});

it('allows importing extracted data', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
        'parsed_data' => [
            'stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 600,
                'guts' => 500,
                'wit' => 400,
            ],
        ],
    ]);

    $response = post(route('ocr.import', $extraction->id), [
        'action' => 'import',
        'character_id' => $this->character->id,
        'stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 600,
            'guts' => 500,
            'wit' => 400,
        ],
    ]);

    $response->assertRedirect(route('ocr.upload'));
    $response->assertSessionHas('success');
});

it('allows saving extraction as draft', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
    ]);

    $response = post(route('ocr.import', $extraction->id), [
        'action' => 'save_draft',
        'stats' => [
            'speed' => 800,
        ],
    ]);

    $response->assertRedirect(route('ocr.upload'));
    $response->assertSessionHas('success', 'Data saved as draft successfully');

    $extraction->refresh();
    expect($extraction->status)->toBe('draft');
});

it('prevents unauthorized access to other users extractions', function () {
    actingAs($this->user);

    $otherUser = User::factory()->create();
    $extraction = OCRExtraction::factory()
        ->for($otherUser, 'user')
        ->create();

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    // The page title is always shown, but the extraction data should not be visible
    $response->assertSee('No extraction found');
    // Should not see the extraction form or action buttons (check for button elements, not sidebar links)
    $response->assertDontSee('<button type="submit" name="action" value="import"', false);
    $response->assertDontSee('<button type="submit" name="action" value="save_draft"', false);
});

it('displays raw OCR text in collapsible section', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'extracted_text' => 'Speed: 800\nStamina: 700\nPower: 600',
        'status' => 'processed',
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Raw OCR Text');
    $response->assertSee('Speed: 800');
});

it('shows screenshot preview on results page', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'image_path' => 'ocr-uploads/test.jpg',
        'status' => 'processed',
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('Screenshot Preview');
});

it('includes accessibility attributes in forms', function () {
    actingAs($this->user);

    $extraction = OCRExtraction::factory()->create([
        'user_id' => $this->user->id,
        'data_type' => 'character_stats',
        'status' => 'processed',
        'parsed_data' => ['stats' => ['speed' => 800]],
    ]);

    $response = get(route('ocr.results', $extraction->id));

    $response->assertStatus(200);
    $response->assertSee('aria-label');
});
