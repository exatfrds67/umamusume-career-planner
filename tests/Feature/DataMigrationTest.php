<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DataMigrationService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->migrationService = app(DataMigrationService::class);
});

describe('DataMigrationService', function () {
    describe('Legacy Format Detection', function () {
        it('detects v1 JSON format', function () {
            $content = json_encode([
                ['trainee_name' => 'Test Character', 'stat_speed' => 800, 'stat_stamina' => 750],
            ]);

            $result = $this->migrationService->detectLegacyFormat($content);

            expect($result['format'])->toBe('v1_json');
            expect($result['confidence'])->toBeGreaterThan(0.5);
        });

        it('detects CSV format', function () {
            $content = "name,speed,stamina,power\nTest Character,800,750,700\nAnother,600,550,500";

            $result = $this->migrationService->detectLegacyFormat($content);

            expect($result['format'])->toContain('csv');
            expect($result['confidence'])->toBeGreaterThan(0.5);
        });

        it('detects Google Sheets TSV format', function () {
            $content = "name\tspeed\tstamina\tpower\nTest Character\t800\t750\t700";

            $result = $this->migrationService->detectLegacyFormat($content);

            expect($result['format'])->toBe('google_sheets');
        });

        it('returns unknown for empty content', function () {
            $result = $this->migrationService->detectLegacyFormat('');

            expect($result['format'])->toBe('unknown');
            expect($result['confidence'])->toBe(0.0);
        });
    });

    describe('Legacy Format Conversion', function () {
        it('converts v1 JSON format to current format', function () {
            $content = json_encode([
                ['trainee_name' => 'Test Character', 'stat_speed' => 800, 'stat_stamina' => 750],
            ]);

            $result = $this->migrationService->convertLegacyFormat($content, 'v1_json', 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(1);
            expect($result['data'][0]['name'])->toBe('Test Character');
            expect($result['data'][0]['speed'])->toBe(800);
            expect($result['data'][0]['stamina'])->toBe(750);
        });

        it('converts v1 CSV format to current format', function () {
            $content = "trainee_name,stat_speed,stat_stamina\nTest Character,800,750";

            $result = $this->migrationService->convertLegacyFormat($content, 'v1_csv', 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'])->toHaveCount(1);
            expect($result['data'][0]['name'])->toBe('Test Character');
        });

        it('clamps stat values to valid range', function () {
            $content = json_encode([
                ['trainee_name' => 'Test', 'stat_speed' => 1500], // Over max
            ]);

            $result = $this->migrationService->convertLegacyFormat($content, 'v1_json', 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'][0]['speed'])->toBe(1200); // Clamped to max
            expect($result['warnings'])->not->toBeEmpty();
        });

        it('normalizes scenario type values', function () {
            $content = json_encode([
                ['trainee_name' => 'Test', 'scenario' => 'URA'],
            ]);

            $result = $this->migrationService->convertLegacyFormat($content, 'v1_json', 'character');

            expect($result['success'])->toBeTrue();
            expect($result['data'][0]['scenario_type'])->toBe('ura_finale');
        });

        it('returns errors for invalid format', function () {
            $result = $this->migrationService->convertLegacyFormat('invalid json', 'v1_json', 'character');

            expect($result['success'])->toBeFalse();
            expect($result['errors'])->not->toBeEmpty();
        });
    });

    describe('Data Validation', function () {
        it('validates character data correctly', function () {
            $data = [
                ['name' => 'Valid Character', 'speed' => 800, 'stamina' => 750],
                ['name' => 'Another Valid', 'power' => 700],
            ];

            $result = $this->migrationService->validateData($data, 'character');

            expect($result['valid'])->toBeTrue();
            expect($result['summary']['valid_count'])->toBe(2);
            expect($result['summary']['invalid_count'])->toBe(0);
        });

        it('identifies invalid records', function () {
            $data = [
                ['name' => 'Valid Character', 'speed' => 800],
                ['speed' => 800], // Missing required name
            ];

            $result = $this->migrationService->validateData($data, 'character');

            expect($result['valid'])->toBeFalse();
            expect($result['summary']['invalid_count'])->toBe(1);
            expect($result['records']['invalid'])->toHaveCount(1);
        });

        it('cleans and normalizes data during validation', function () {
            $data = [
                ['name' => '  Test Character  ', 'speed' => '800', 'scenario_type' => 'URA'],
            ];

            $result = $this->migrationService->validateData($data, 'character');

            expect($result['valid'])->toBeTrue();
            $cleanedRecord = $result['records']['valid'][0]['cleaned'];
            expect($cleanedRecord['name'])->toBe('Test Character');
            expect($cleanedRecord['speed'])->toBe(800);
            expect($cleanedRecord['scenario_type'])->toBe('ura_finale');
        });
    });

    describe('Batch Import Processing', function () {
        it('starts a batch import successfully', function () {
            $data = [
                ['name' => 'Character 1', 'speed' => 800],
                ['name' => 'Character 2', 'speed' => 750],
            ];

            $result = $this->migrationService->startBatchImport($data, 'character', $this->user->id);

            expect($result['batch_id'])->not->toBeEmpty();
            expect($result['status'])->toBe(DataMigrationService::BATCH_STATUS_PENDING);
            expect($result['total_records'])->toBe(2);
        });

        it('retrieves batch status correctly', function () {
            $data = [['name' => 'Test', 'speed' => 800]];
            $batch = $this->migrationService->startBatchImport($data, 'character', $this->user->id);

            $status = $this->migrationService->getBatchStatus($batch['batch_id']);

            expect($status)->not->toBeNull();
            expect($status['batch_id'])->toBe($batch['batch_id']);
            expect($status['status'])->toBe(DataMigrationService::BATCH_STATUS_PENDING);
            expect($status['progress']['total_records'])->toBe(1);
        });

        it('returns null for non-existent batch', function () {
            $status = $this->migrationService->getBatchStatus('non-existent-id');

            expect($status)->toBeNull();
        });

        it('cancels a batch import', function () {
            $data = [['name' => 'Test', 'speed' => 800]];
            $batch = $this->migrationService->startBatchImport($data, 'character', $this->user->id);

            $cancelled = $this->migrationService->cancelBatch($batch['batch_id']);

            expect($cancelled)->toBeTrue();

            $status = $this->migrationService->getBatchStatus($batch['batch_id']);
            expect($status['status'])->toBe(DataMigrationService::BATCH_STATUS_CANCELLED);
        });

        it('cannot cancel completed batch', function () {
            $data = [['name' => 'Test', 'speed' => 800]];
            $batch = $this->migrationService->startBatchImport($data, 'character', $this->user->id);

            // Process the batch to completion
            $this->migrationService->processBatch($batch['batch_id']);

            $cancelled = $this->migrationService->cancelBatch($batch['batch_id']);

            expect($cancelled)->toBeFalse();
        });
    });

    describe('Conflict Detection', function () {
        it('detects duplicate character names', function () {
            // Create existing character
            \App\Models\Character::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Existing Character',
            ]);

            $record = ['name' => 'Existing Character', 'speed' => 800];
            $conflict = $this->migrationService->detectConflict($record, 'character', $this->user->id);

            expect($conflict['has_conflict'])->toBeTrue();
            expect($conflict['conflict_type'])->toBe('duplicate_name');
            expect($conflict['existing_record'])->not->toBeNull();
        });

        it('returns no conflict for new records', function () {
            $record = ['name' => 'New Character', 'speed' => 800];
            $conflict = $this->migrationService->detectConflict($record, 'character', $this->user->id);

            expect($conflict['has_conflict'])->toBeFalse();
            expect($conflict['existing_record'])->toBeNull();
        });
    });

    describe('Conflict Resolution', function () {
        it('skips duplicate records with skip strategy', function () {
            $existing = \App\Models\Character::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Existing',
            ]);

            $record = ['name' => 'Existing', 'speed' => 900];
            $conflict = ['existing_record' => $existing->toArray()];

            $result = $this->migrationService->resolveConflict(
                $record,
                $conflict,
                DataMigrationService::CONFLICT_STRATEGY_SKIP,
                'character',
                $this->user->id
            );

            expect($result['success'])->toBeTrue();
            expect($result['action'])->toBe('skip');
        });

        it('renames and imports with rename strategy', function () {
            $existing = \App\Models\Character::factory()->create([
                'user_id' => $this->user->id,
                'name' => 'Existing',
            ]);

            $record = ['name' => 'Existing', 'speed' => 900];
            $conflict = ['existing_record' => $existing->toArray()];

            $result = $this->migrationService->resolveConflict(
                $record,
                $conflict,
                DataMigrationService::CONFLICT_STRATEGY_RENAME,
                'character',
                $this->user->id
            );

            expect($result['success'])->toBeTrue();
            expect($result['action'])->toBe('rename');
            expect($result['id'])->not->toBeNull();

            // Verify new character was created with different name
            $newCharacter = \App\Models\Character::find($result['id']);
            expect($newCharacter->name)->toContain('Existing (');
        });
    });

    describe('Migration Report', function () {
        it('generates migration report for completed batch', function () {
            $data = [['name' => 'Test', 'speed' => 800]];
            $batch = $this->migrationService->startBatchImport($data, 'character', $this->user->id);

            // Process the batch
            $this->migrationService->processBatch($batch['batch_id']);

            $report = $this->migrationService->generateMigrationReport($batch['batch_id']);

            expect($report['batch_id'])->toBe($batch['batch_id']);
            expect($report['status'])->toBe(DataMigrationService::BATCH_STATUS_COMPLETED);
            expect($report['summary'])->toHaveKeys(['total_records', 'successful', 'failed', 'skipped', 'success_rate']);
            expect($report['timing'])->toHaveKeys(['started_at', 'completed_at']);
        });

        it('returns error for non-existent batch', function () {
            $report = $this->migrationService->generateMigrationReport('non-existent');

            expect($report)->toHaveKey('error');
        });
    });

    describe('Transformation Rules', function () {
        it('returns transformation rules for character type', function () {
            $rules = $this->migrationService->getTransformationRules('character');

            expect($rules)->toHaveKeys(['field_mappings', 'stat_range', 'valid_grades', 'valid_scenarios']);
            expect($rules['required_fields'])->toContain('name');
            expect($rules['stat_fields'])->toContain('speed');
        });

        it('returns transformation rules for career type', function () {
            $rules = $this->migrationService->getTransformationRules('career');

            expect($rules)->toHaveKeys(['field_mappings', 'stat_range']);
            expect($rules['stat_fields'])->toContain('final_speed');
        });
    });
});

describe('MigrationController API', function () {
    beforeEach(function () {
        $this->actingAs($this->user, 'sanctum');
    });

    it('converts legacy data via API', function () {
        $response = $this->postJson('/api/migration/convert', [
            'content' => json_encode([['trainee_name' => 'Test', 'stat_speed' => 800]]),
            'source_format' => 'v1_json',
            'target_type' => 'character',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['converted_data', 'source_format', 'target_type'],
        ]);
    });

    it('detects format via API', function () {
        $response = $this->postJson('/api/migration/detect-format', [
            'content' => json_encode([['trainee_name' => 'Test']]),
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['format', 'confidence', 'details'],
        ]);
    });

    it('starts batch import via API', function () {
        $response = $this->postJson('/api/migration/batch', [
            'data' => [['name' => 'Test', 'speed' => 800]],
            'import_type' => 'character',
            'conflict_strategy' => 'skip',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['batch_id', 'status', 'total_records'],
        ]);
    });

    it('gets batch status via API', function () {
        // Start a batch first
        $startResponse = $this->postJson('/api/migration/batch', [
            'data' => [['name' => 'Test', 'speed' => 800]],
            'import_type' => 'character',
        ]);

        $batchId = $startResponse->json('data.batch_id');

        $response = $this->getJson("/api/migration/batch/{$batchId}/status");

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['batch_id', 'status', 'progress'],
        ]);
    });

    it('validates data via API', function () {
        $response = $this->postJson('/api/migration/validate', [
            'data' => [['name' => 'Test', 'speed' => 800]],
            'import_type' => 'character',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['valid', 'summary', 'valid_records', 'invalid_records'],
        ]);
    });

    it('returns 404 for non-existent batch', function () {
        $response = $this->getJson('/api/migration/batch/non-existent-id/status');

        $response->assertNotFound();
    });

    it('gets transformation rules via API', function () {
        $response = $this->getJson('/api/migration/transformation-rules?import_type=character');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['import_type', 'rules'],
        ]);
    });

    it('requires authentication for migration endpoints', function () {
        // Create a fresh test instance without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/migration/convert', [
            'content' => '{}',
            'target_type' => 'character',
        ]);

        $response->assertUnauthorized();
    });
});
