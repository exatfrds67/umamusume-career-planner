<?php

declare(strict_types=1);

use App\Services\ExternalAPI\DataQualityScoringService;
use App\Services\ExternalAPI\DataValidationAgent;
use App\Services\ExternalAPI\DataValidationService;
use App\Services\MCP\MCPClientService;

beforeEach(function () {
    $this->mcpClient = mock(MCPClientService::class);
    $this->validationService = mock(DataValidationService::class);
    $this->qualityScoringService = mock(DataQualityScoringService::class);

    $this->qualityScoringService->shouldReceive('calculateQualityScore')
        ->andReturn([
            'overall_score' => 85.0,
            'grade' => 'B',
            'dimensions' => [
                'accuracy' => 90.0,
                'completeness' => 85.0,
                'consistency' => 80.0,
                'timeliness' => 85.0,
                'validity' => 85.0,
            ],
            'recommendations' => ['Data quality is good'],
        ]);

    $this->agent = new DataValidationAgent(
        $this->mcpClient,
        $this->validationService,
        $this->qualityScoringService
    );
});

describe('DataValidationAgent', function () {
    describe('validate', function () {
        it('validates valid character data successfully', function () {
            $data = [
                ['id' => 1, 'name' => 'Silence Suzuka'],
                ['id' => 2, 'name' => 'Tokai Teio'],
            ];

            $result = $this->agent->validate('umapyoi_characters', $data);

            expect($result['valid'])->toBeTrue();
            expect($result['score'])->toBeGreaterThan(0);
            expect($result['grade'])->toBeIn(['A', 'B', 'C']);
            expect($result['errors'])->toBeEmpty();
            expect($result['report'])->toBeArray();
        });

        it('detects missing required fields', function () {
            $data = [
                ['id' => 1], // Missing 'name' field
            ];

            $result = $this->agent->validate('umapyoi_characters', $data);

            expect($result['valid'])->toBeFalse();
            expect($result['errors'])->toContain('Required field missing: name');
        });

        it('detects incorrect field types', function () {
            $data = [
                ['id' => 'not-an-integer', 'name' => 'Test Character'],
            ];

            $result = $this->agent->validate('umapyoi_characters', $data);

            expect($result['valid'])->toBeFalse();
            expect($result['errors'])->not->toBeEmpty();
        });

        it('detects duplicate entries', function () {
            $data = [
                ['id' => 1, 'name' => 'Character 1'],
                ['id' => 1, 'name' => 'Character 2'], // Duplicate ID
            ];

            $result = $this->agent->validate('umapyoi_characters', $data);

            expect($result['errors'])->not->toBeEmpty();
        });

        it('validates support card data with rarity enum', function () {
            $data = [
                ['id' => 1, 'name' => 'Card 1', 'rarity' => 'SSR'],
                ['id' => 2, 'name' => 'Card 2', 'rarity' => 'SR'],
            ];

            $result = $this->agent->validate('umapyoi_support_cards', $data);

            expect($result['valid'])->toBeTrue();
        });

        it('detects invalid enum values', function () {
            $data = [
                ['id' => 1, 'name' => 'Card 1', 'rarity' => 'INVALID'],
            ];

            $result = $this->agent->validate('umapyoi_support_cards', $data);

            expect($result['valid'])->toBeFalse();
        });

        it('handles empty data with warning', function () {
            $result = $this->agent->validate('umapyoi_characters', []);

            // Empty data triggers schema validation which checks required fields
            // The validation may fail or pass depending on implementation
            expect($result['warnings'])->not->toBeEmpty();
            expect($result['report'])->toBeArray();
        });

        it('handles non-array data', function () {
            $result = $this->agent->validate('umapyoi_characters', 'not an array');

            expect($result['valid'])->toBeFalse();
            expect($result['errors'])->toContain('Data must be an array');
        });

        it('handles unknown data type with warning', function () {
            $data = [['id' => 1, 'name' => 'Test']];

            $result = $this->agent->validate('unknown_type', $data);

            expect($result['warnings'])->toContain('No schema definition found for data type: unknown_type');
        });
    });

    describe('validateBatch', function () {
        it('validates multiple data sets', function () {
            $dataSets = [
                ['type' => 'umapyoi_characters', 'data' => [['id' => 1, 'name' => 'Test']]],
                ['type' => 'umapyoi_support_cards', 'data' => [['id' => 1, 'name' => 'Card', 'rarity' => 'SSR']]],
            ];

            $result = $this->agent->validateBatch($dataSets);

            expect($result['success'])->toBeTrue();
            expect($result['results'])->toHaveCount(2);
            expect($result['summary']['total_items'])->toBe(2);
            expect($result['summary']['successful'])->toBe(2);
        });

        it('handles mixed valid and invalid data', function () {
            $dataSets = [
                ['type' => 'umapyoi_characters', 'data' => [['id' => 1, 'name' => 'Valid']]],
                ['type' => 'umapyoi_characters', 'data' => [['id' => 'invalid']]],
            ];

            $result = $this->agent->validateBatch($dataSets);

            expect($result['success'])->toBeFalse();
            expect($result['summary']['failed'])->toBeGreaterThan(0);
        });
    });

    describe('registerSchema', function () {
        it('allows registering custom schemas', function () {
            $customSchema = [
                'required_fields' => ['custom_id', 'custom_name'],
                'field_types' => ['custom_id' => 'integer', 'custom_name' => 'string'],
            ];

            $this->agent->registerSchema('custom_type', $customSchema);

            $data = [['custom_id' => 1, 'custom_name' => 'Test']];
            $result = $this->agent->validate('custom_type', $data);

            expect($result['valid'])->toBeTrue();
        });
    });

    describe('getValidationStatistics', function () {
        it('tracks validation history', function () {
            $data = [['id' => 1, 'name' => 'Test']];

            $this->agent->validate('umapyoi_characters', $data);
            $this->agent->validate('umapyoi_characters', $data);

            $stats = $this->agent->getValidationStatistics();

            expect($stats['total'])->toBe(2);
            expect($stats['passed'])->toBe(2);
            expect($stats['pass_rate'])->toBe(100.0);
        });
    });

    describe('getValidationHistory', function () {
        it('returns validation history', function () {
            $data = [['id' => 1, 'name' => 'Test']];

            $this->agent->validate('umapyoi_characters', $data);

            $history = $this->agent->getValidationHistory();

            expect($history)->toHaveCount(1);
            expect($history[0]['data_type'])->toBe('umapyoi_characters');
        });
    });

    describe('validation report', function () {
        it('generates comprehensive report', function () {
            $data = [['id' => 1, 'name' => 'Test']];

            $result = $this->agent->validate('umapyoi_characters', $data);

            expect($result['report'])->toHaveKey('summary');
            expect($result['report'])->toHaveKey('sections');
            expect($result['report'])->toHaveKey('recommendations');
            expect($result['report']['summary']['data_type'])->toBe('umapyoi_characters');
        });
    });
});
