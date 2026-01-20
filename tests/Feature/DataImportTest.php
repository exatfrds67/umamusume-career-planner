<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DataImportService;

/**
 * Data Import Feature Tests
 *
 * Tests for the flexible data import interface (Task 5.3.1)
 * Requirements: 23.1, 23.2
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->importService = app(DataImportService::class);
});

describe('DataImportService', function () {
    describe('Text Parsing', function () {
        it('detects JSON format correctly', function () {
            $jsonText = '{"name": "Test Character", "speed": 800}';
            $result = $this->importService->parseText($jsonText, 'character');

            expect($result['format'])->toBe('json');
            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(1);
        });

        it('detects CSV format correctly', function () {
            $csvText = "name,speed,stamina,power,guts,wit\nTest Character,800,600,700,500,650";
            $result = $this->importService->parseText($csvText, 'character');

            expect($result['format'])->toBe('csv');
            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(1);
        });

        it('detects key-value format correctly', function () {
            $kvText = "Name: Test Character\nSpeed: 800\nStamina: 600";
            $result = $this->importService->parseText($kvText, 'character');

            expect($result['format'])->toBe('key_value');
            expect($result['success'])->toBeTrue();
        });

        it('returns error for empty input', function () {
            $result = $this->importService->parseText('', 'character');

            expect($result['success'])->toBeFalse();
            expect($result['errors'])->toContain('Input text is empty');
        });
    });

    describe('JSON Parsing', function () {
        it('parses valid JSON character data', function () {
            $json = json_encode([
                'name' => 'Silence Suzuka',
                'speed' => 800,
                'stamina' => 600,
                'power' => 700,
                'guts' => 500,
                'wit' => 650,
            ]);

            $result = $this->importService->parseJson($json, 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'][0]['name'])->toBe('Silence Suzuka');
            expect($result['data'][0]['speed'])->toBe(800);
        });

        it('parses JSON array of records', function () {
            $json = json_encode([
                ['name' => 'Character 1', 'speed' => 800],
                ['name' => 'Character 2', 'speed' => 900],
            ]);

            $result = $this->importService->parseJson($json, 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(2);
        });

        it('returns error for invalid JSON', function () {
            $result = $this->importService->parseJson('invalid json', 'character');

            expect($result['success'])->toBeFalse();
            expect($result['errors'][0])->toContain('Invalid JSON');
        });
    });

    describe('CSV Parsing', function () {
        it('parses valid CSV character data', function () {
            $csv = "name,speed,stamina,power,guts,wit\nSilence Suzuka,800,600,700,500,650";

            $result = $this->importService->parseCsv($csv, 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'][0]['name'])->toBe('Silence Suzuka');
            expect($result['data'][0]['speed'])->toBe(800);
        });

        it('handles multiple CSV rows', function () {
            $csv = "name,speed\nCharacter 1,800\nCharacter 2,900\nCharacter 3,1000";

            $result = $this->importService->parseCsv($csv, 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(3);
        });

        it('returns error for CSV with only header', function () {
            $csv = 'name,speed,stamina';

            $result = $this->importService->parseCsv($csv, 'character');

            expect($result['success'])->toBeFalse();
            expect($result['errors'])->toContain('CSV must have at least a header row and one data row');
        });
    });

    describe('Validation', function () {
        it('validates character stat ranges', function () {
            $record = [
                'name' => 'Test',
                'speed' => 1500, // Over max of 1200
            ];

            $result = $this->importService->validateRecord($record, 'character');

            expect($result['valid'])->toBeFalse();
        });

        it('validates required fields', function () {
            $record = [
                'speed' => 800,
                // Missing required 'name' field
            ];

            $result = $this->importService->validateRecord($record, 'character');

            expect($result['valid'])->toBeFalse();
        });

        it('accepts valid character data', function () {
            $record = [
                'name' => 'Valid Character',
                'speed' => 800,
                'stamina' => 600,
                'power' => 700,
                'guts' => 500,
                'wit' => 650,
                'scenario_type' => 'ura_finale',
            ];

            $result = $this->importService->validateRecord($record, 'character');

            expect($result['valid'])->toBeTrue();
        });

        it('validates scenario type enum', function () {
            $record = [
                'name' => 'Test',
                'scenario_type' => 'invalid_scenario',
            ];

            $result = $this->importService->validateRecord($record, 'character');

            expect($result['valid'])->toBeFalse();
        });

        it('validates mood status enum', function () {
            $record = [
                'name' => 'Test',
                'mood_status' => 'invalid_mood',
            ];

            $result = $this->importService->validateRecord($record, 'character');

            expect($result['valid'])->toBeFalse();
        });
    });

    describe('Preview Generation', function () {
        it('generates preview with validation results', function () {
            $data = [
                ['name' => 'Valid Character', 'speed' => 800],
                ['name' => 'Invalid Character', 'speed' => 1500], // Invalid
            ];

            $preview = $this->importService->generatePreview($data, 'character');

            expect($preview['total_records'])->toBe(2);
            expect($preview['valid_records'])->toBe(1);
            expect($preview['invalid_records'])->toBe(1);
            expect($preview['records'])->toHaveCount(2);
        });

        it('includes field mapping in preview', function () {
            $data = [['name' => 'Test', 'speed' => 800]];

            $preview = $this->importService->generatePreview($data, 'character');

            expect($preview['field_mapping'])->toHaveKey('name');
            expect($preview['field_mapping'])->toHaveKey('speed');
        });
    });

    describe('Templates', function () {
        it('provides templates for all import types', function () {
            $templates = $this->importService->getTemplates();

            expect($templates)->toHaveKey('character');
            expect($templates)->toHaveKey('career');
            expect($templates)->toHaveKey('training_session');
            expect($templates)->toHaveKey('skill');
            expect($templates)->toHaveKey('support_card');
        });

        it('provides CSV and JSON templates for each type', function () {
            $templates = $this->importService->getTemplates();

            foreach ($templates as $type => $formats) {
                expect($formats)->toHaveKey('csv');
                expect($formats)->toHaveKey('json');
            }
        });
    });

    describe('Field Mapping', function () {
        it('returns field mapping for character type', function () {
            $mapping = $this->importService->getFieldMapping('character');

            expect($mapping)->toHaveKey('name');
            expect($mapping['name']['required'])->toBeTrue();
            expect($mapping)->toHaveKey('speed');
            expect($mapping['speed']['type'])->toBe('integer');
        });

        it('returns field mapping for career type', function () {
            $mapping = $this->importService->getFieldMapping('career');

            expect($mapping)->toHaveKey('career_name');
            expect($mapping)->toHaveKey('scenario_type');
            expect($mapping)->toHaveKey('status');
        });

        it('returns field mapping for training_session type', function () {
            $mapping = $this->importService->getFieldMapping('training_session');

            expect($mapping)->toHaveKey('turn_number');
            expect($mapping['turn_number']['required'])->toBeTrue();
            expect($mapping)->toHaveKey('training_type');
        });
    });
});

describe('Import API Endpoints', function () {
    describe('Preview Endpoint', function () {
        it('requires authentication', function () {
            $response = $this->postJson('/api/import/preview', [
                'import_type' => 'character',
                'content' => '{"name": "Test"}',
            ]);

            $response->assertUnauthorized();
        });

        it('validates import type is required', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/import/preview', [
                    'content' => '{"name": "Test"}',
                ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['import_type']);
        });

        it('validates content or file is required', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/import/preview', [
                    'import_type' => 'character',
                ]);

            $response->assertUnprocessable();
        });

        it('returns preview for valid JSON input', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/import/preview', [
                    'import_type' => 'character',
                    'content' => json_encode(['name' => 'Test Character', 'speed' => 800]),
                ]);

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'preview' => [
                        'import_type',
                        'total_records',
                        'valid_records',
                        'invalid_records',
                        'records',
                    ],
                    'format_detected',
                ],
            ]);
        });

        it('returns preview for valid CSV input', function () {
            $csv = "name,speed,stamina\nTest Character,800,600";

            $response = $this->actingAs($this->user)
                ->postJson('/api/import/preview', [
                    'import_type' => 'character',
                    'content' => $csv,
                ]);

            $response->assertSuccessful();
            $response->assertJsonPath('data.format_detected', 'csv');
        });
    });

    describe('Execute Endpoint', function () {
        it('requires authentication', function () {
            $response = $this->postJson('/api/import/execute', [
                'import_type' => 'character',
                'data' => [['name' => 'Test']],
            ]);

            $response->assertUnauthorized();
        });

        it('validates data is required', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/import/execute', [
                    'import_type' => 'character',
                ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['data']);
        });

        it('imports valid character data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/import/execute', [
                    'import_type' => 'character',
                    'data' => [
                        [
                            'name' => 'Imported Character',
                            'speed' => 800,
                            'stamina' => 600,
                            'power' => 700,
                            'guts' => 500,
                            'wit' => 650,
                        ],
                    ],
                ]);

            $response->assertSuccessful();
            $response->assertJsonPath('data.imported', 1);

            $this->assertDatabaseHas('ucp_characters', [
                'name' => 'Imported Character',
                'user_id' => $this->user->id,
            ]);
        });
    });

    describe('Templates Endpoint', function () {
        it('returns all templates without type filter', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/import/templates');

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'data' => [
                    'templates',
                    'import_types',
                ],
            ]);
        });

        it('returns specific template with type filter', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/import/templates?type=character');

            $response->assertSuccessful();
            $response->assertJsonPath('data.type', 'character');
            $response->assertJsonStructure([
                'data' => [
                    'templates' => ['csv', 'json'],
                    'field_mapping',
                ],
            ]);
        });
    });

    describe('History Endpoint', function () {
        it('requires authentication', function () {
            $response = $this->getJson('/api/import/history');

            $response->assertUnauthorized();
        });

        it('returns import history for authenticated user', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/import/history');

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'success',
                'data' => ['history'],
            ]);
        });
    });
});

describe('Import Web Interface', function () {
    it('displays import page for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->get('/import');

        $response->assertSuccessful();
        $response->assertViewIs('import.index');
        $response->assertViewHas('importTypes');
        $response->assertViewHas('supportedFormats');
    });

    it('redirects unauthenticated users to login', function () {
        $response = $this->get('/import');

        // Unauthenticated users are redirected (either to login or home)
        $response->assertRedirect();
    });
});
