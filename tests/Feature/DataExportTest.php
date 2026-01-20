<?php

declare(strict_types=1);

/**
 * Data Export Feature Tests
 *
 * Tests for the comprehensive data export functionality (Task 5.3.2)
 * Requirements: 23.2
 */

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use App\Services\DataExportService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('DataExportService', function () {
    it('exports character data to JSON format', function () {
        // Create test character
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
            'current_stats' => ['speed' => 800, 'stamina' => 700, 'power' => 600, 'guts' => 500, 'wit' => 400],
        ]);

        $service = new DataExportService;
        $result = $service->generateExport('character', $this->user->id);

        expect($result['success'])->toBeTrue()
            ->and($result['count'])->toBeGreaterThanOrEqual(1)
            ->and($result['data'])->toBeArray();
    });

    it('exports career data with filters', function () {
        $character = Character::factory()->create(['user_id' => $this->user->id]);
        Career::factory()->create([
            'user_id' => $this->user->id,
            'character_id' => $character->id,
            'scenario_type' => 'ura_finale',
            'status' => 'completed',
        ]);

        $service = new DataExportService;
        $result = $service->generateExport('career', $this->user->id, [
            'scenario_type' => 'ura_finale',
            'status' => 'completed',
        ]);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toBeArray();
    });

    it('converts data to CSV format correctly', function () {
        $service = new DataExportService;
        $data = [
            ['id' => 1, 'name' => 'Test', 'value' => 100],
            ['id' => 2, 'name' => 'Test 2', 'value' => 200],
        ];

        $csv = $service->toCsv($data, 'character');

        expect($csv)->toContain('id,name,value')
            ->and($csv)->toContain('1,Test,100')
            ->and($csv)->toContain('2,"Test 2",200');
    });

    it('converts data to JSON format correctly', function () {
        $service = new DataExportService;
        $data = ['name' => 'Test', 'value' => 100];

        $json = $service->toJson($data);
        $decoded = json_decode($json, true);

        expect($decoded)->toBe($data);
    });

    it('generates PDF HTML content', function () {
        $service = new DataExportService;
        $data = [
            ['id' => 1, 'name' => 'Test Character', 'scenario_type' => 'ura_finale'],
        ];

        $html = $service->toPdf($data, 'character');

        expect($html)->toContain('<!DOCTYPE html>')
            ->and($html)->toContain('Character Data')
            ->and($html)->toContain('Umamusume Career Planner');
    });

    it('returns export templates', function () {
        $service = new DataExportService;
        $templates = $service->getTemplates();

        expect($templates)->toBeArray()
            ->and($templates)->toHaveKey('career_summary')
            ->and($templates)->toHaveKey('full_export')
            ->and($templates['career_summary'])->toHaveKey('name')
            ->and($templates['career_summary'])->toHaveKey('description')
            ->and($templates['career_summary'])->toHaveKey('types');
    });

    it('returns field mapping for export types', function () {
        $service = new DataExportService;

        $characterMapping = $service->getFieldMapping('character');
        $careerMapping = $service->getFieldMapping('career');

        expect($characterMapping)->toBeArray()
            ->and($characterMapping)->toHaveKey('name')
            ->and($characterMapping['name'])->toHaveKey('label')
            ->and($careerMapping)->toBeArray()
            ->and($careerMapping)->toHaveKey('career_name');
    });

    it('generates full backup export', function () {
        $character = Character::factory()->create(['user_id' => $this->user->id]);

        $service = new DataExportService;
        $result = $service->generateExport('full_backup', $this->user->id);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('export_info')
            ->and($result['data'])->toHaveKey('characters')
            ->and($result['data'])->toHaveKey('careers')
            ->and($result['data']['export_info'])->toHaveKey('version')
            ->and($result['data']['export_info'])->toHaveKey('exported_at');
    });
});

describe('ExportController API', function () {
    it('generates export via API', function () {
        Character::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'character',
            'format' => 'json',
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'export_type',
                    'format',
                    'record_count',
                    'content',
                ],
            ]);
    });

    it('validates export type', function () {
        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'invalid_type',
            'format' => 'json',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['export_type']);
    });

    it('validates export format', function () {
        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'character',
            'format' => 'invalid_format',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    });

    it('returns export templates', function () {
        $response = $this->getJson('/api/export/templates');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'templates',
                    'export_types',
                    'supported_formats',
                ],
            ]);
    });

    it('returns export history', function () {
        $response = $this->getJson('/api/export/history');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'history',
                ],
            ]);
    });

    it('previews export data', function () {
        Character::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/export/preview', [
            'export_type' => 'character',
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'export_type',
                    'total_records',
                    'preview_records',
                    'preview',
                    'field_mapping',
                ],
            ]);
    });

    it('schedules automated export', function () {
        $response = $this->postJson('/api/export/schedule', [
            'export_type' => 'character',
            'format' => 'json',
            'frequency' => 'daily',
            'time' => '09:00',
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'schedule_id',
                    'config',
                ],
            ]);
    });

    it('validates schedule frequency', function () {
        $response = $this->postJson('/api/export/schedule', [
            'export_type' => 'character',
            'frequency' => 'invalid_frequency',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    });

    it('returns scheduled exports', function () {
        $response = $this->getJson('/api/export/schedules');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'schedules',
                ],
            ]);
    });

    it('deletes scheduled export', function () {
        // First create a schedule
        $createResponse = $this->postJson('/api/export/schedule', [
            'export_type' => 'character',
            'format' => 'json',
            'frequency' => 'daily',
        ]);

        $scheduleId = $createResponse->json('data.schedule_id');

        // Then delete it
        $response = $this->deleteJson("/api/export/schedule/{$scheduleId}");

        $response->assertSuccessful()
            ->assertJson(['success' => true]);
    });
});

describe('Export Filters', function () {
    it('filters by scenario type', function () {
        Character::factory()->create([
            'user_id' => $this->user->id,
            'scenario_type' => 'ura_finale',
        ]);
        Character::factory()->create([
            'user_id' => $this->user->id,
            'scenario_type' => 'unity_cup',
        ]);

        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'character',
            'format' => 'json',
            'filters' => [
                'scenario_type' => 'ura_finale',
            ],
        ]);

        $response->assertSuccessful();
        $content = json_decode($response->json('data.content'), true);

        foreach ($content as $character) {
            expect($character['scenario_type'])->toBe('ura_finale');
        }
    });

    it('filters by date range', function () {
        Character::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);
        Character::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'character',
            'format' => 'json',
            'filters' => [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
            ],
        ]);

        $response->assertSuccessful();
    });

    it('validates date range order', function () {
        $response = $this->postJson('/api/export/generate', [
            'export_type' => 'character',
            'filters' => [
                'date_from' => now()->toDateString(),
                'date_to' => now()->subDays(7)->toDateString(),
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['filters.date_to']);
    });
});

describe('Export View', function () {
    it('displays export page', function () {
        $response = $this->get('/export');

        $response->assertSuccessful()
            ->assertViewIs('export.index')
            ->assertViewHas('exportTypes')
            ->assertViewHas('supportedFormats')
            ->assertViewHas('templates');
    });
});
