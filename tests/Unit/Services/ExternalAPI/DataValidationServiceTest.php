<?php

declare(strict_types=1);

use App\Services\ExternalAPI\DataValidationService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->mcpClient = Mockery::mock(MCPClientService::class);
    $this->service = new DataValidationService($this->mcpClient);
});

afterEach(function () {
    Mockery::close();
});

describe('DataValidationService - Schema Validation', function () {
    it('validates data with correct schema', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
            ['id' => 2, 'name' => 'Character 2'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['score'])->toBeGreaterThanOrEqual(80);
    });

    it('detects missing required fields', function () {
        $data = [
            ['id' => 1], // Missing 'name'
            ['name' => 'Character 2'], // Missing 'id'
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty()
            ->and($result['score'])->toBeLessThan(100);
    });

    it('detects incorrect field types', function () {
        $data = [
            ['id' => 'not_an_integer', 'name' => 'Character 1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['valid'])->toBeFalse();

        $hasTypeError = false;
        foreach ($result['errors'] as $error) {
            if (preg_match('/has incorrect type.*at index/i', $error)) {
                $hasTypeError = true;
                break;
            }
        }

        expect($hasTypeError)->toBeTrue();
    });

    it('validates enum fields', function () {
        $data = [
            ['id' => 1, 'name' => 'Card 1', 'rarity' => 'INVALID'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_support_cards', $data);

        expect($result['valid'])->toBeFalse();

        $hasEnumError = false;
        foreach ($result['errors'] as $error) {
            if (preg_match('/Invalid value in field.*at index/i', $error)) {
                $hasEnumError = true;
                break;
            }
        }

        expect($hasEnumError)->toBeTrue();
    });

    it('accepts valid enum values', function () {
        $data = [
            ['id' => 1, 'name' => 'Card 1', 'rarity' => 'SSR'],
            ['id' => 2, 'name' => 'Card 2', 'rarity' => 'SR'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_support_cards', $data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toHaveCount(0);
    });
});

describe('DataValidationService - Data Integrity', function () {
    it('detects duplicate IDs', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
            ['id' => 1, 'name' => 'Character 2'], // Duplicate ID
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['valid'])->toBeFalse();

        $hasDuplicateError = false;
        foreach ($result['errors'] as $error) {
            if (preg_match('/Duplicate values found/i', $error)) {
                $hasDuplicateError = true;
                break;
            }
        }

        expect($hasDuplicateError)->toBeTrue();
    });

    it('warns about null values in critical fields', function () {
        $data = [
            ['id' => 1, 'name' => null],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['warnings'])->not->toBeEmpty();
    });

    it('warns about empty data arrays', function () {
        $data = [];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        $hasEmptyWarning = false;
        foreach ($result['warnings'] as $warning) {
            if (preg_match('/array is empty/i', $warning)) {
                $hasEmptyWarning = true;
                break;
            }
        }

        expect($hasEmptyWarning)->toBeTrue();
    });
});

describe('DataValidationService - Business Rules', function () {
    it('validates value ranges', function () {
        // This would require custom validation rules with ranges
        // For now, test that the service handles business rules
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result)->toHaveKey('details')
            ->and($result['details'])->toHaveKey('business_rules');
    });
});

describe('DataValidationService - Data Quality', function () {
    it('calculates completeness score', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
            ['id' => 2, 'name' => 'Character 2'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['details']['quality_checks']['metrics'])->toHaveKey('completeness')
            ->and($result['details']['quality_checks']['metrics']['completeness'])->toBeFloat();
    });

    it('calculates consistency score', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
            ['id' => 2, 'name' => 'Character 2'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['details']['quality_checks']['metrics'])->toHaveKey('consistency')
            ->and($result['details']['quality_checks']['metrics']['consistency'])->toBeFloat();
    });

    it('warns about low completeness', function () {
        $data = [
            ['id' => 1], // Missing name
            ['id' => 2], // Missing name
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        $hasCompletenessWarning = false;
        foreach ($result['warnings'] as $warning) {
            if (preg_match('/completeness is low/i', $warning)) {
                $hasCompletenessWarning = true;
                break;
            }
        }

        expect($hasCompletenessWarning)->toBeTrue();
    });
});

describe('DataValidationService - Validation Score', function () {
    it('calculates high score for valid data', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
            ['id' => 2, 'name' => 'Character 2'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['score'])->toBeGreaterThanOrEqual(90);
    });

    it('calculates low score for invalid data', function () {
        $data = [
            ['id' => 'invalid'], // Missing name, wrong type
            ['name' => 'Character 2'], // Missing id
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['score'])->toBeLessThan(80);
    });

    it('penalizes errors more than warnings', function () {
        $dataWithErrors = [
            ['id' => 'invalid'], // Type error + missing name
        ];

        $dataWithWarnings = [
            ['id' => 1, 'name' => null], // Warning only
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $resultErrors = $this->service->validateData('umapyoi_characters', $dataWithErrors);
        $resultWarnings = $this->service->validateData('umapyoi_characters', $dataWithWarnings);

        // Errors should result in lower score compared to warnings
        expect($resultErrors['score'])->toBeLessThan($resultWarnings['score']);
    });
});

describe('DataValidationService - Validation History', function () {
    it('records validation history', function () {
        $data = [
            ['id' => 1, 'name' => 'Character 1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $this->service->validateData('umapyoi_characters', $data);

        $history = $this->service->getValidationHistory();

        expect($history)->not->toBeEmpty()
            ->and($history[0])->toHaveKey('data_type')
            ->and($history[0])->toHaveKey('valid')
            ->and($history[0])->toHaveKey('score')
            ->and($history[0])->toHaveKey('timestamp');
    });

    it('limits history to 100 entries', function () {
        Log::shouldReceive('info')->atLeast()->once();

        // Add 150 validations
        for ($i = 0; $i < 150; $i++) {
            $this->service->validateData('umapyoi_characters', [['id' => $i, 'name' => "Char {$i}"]]);
        }

        $history = $this->service->getValidationHistory(200);

        expect($history)->toHaveCount(100);
    });
});

describe('DataValidationService - Validation Statistics', function () {
    it('calculates validation statistics', function () {
        Log::shouldReceive('info')->atLeast()->once();

        $this->service->validateData('umapyoi_characters', [['id' => 1, 'name' => 'Valid']]);
        $this->service->validateData('umapyoi_characters', [['id' => 'invalid']]);

        $stats = $this->service->getValidationStatistics();

        expect($stats)->toHaveKey('total_validations')
            ->and($stats)->toHaveKey('successful')
            ->and($stats)->toHaveKey('failed')
            ->and($stats)->toHaveKey('avg_score')
            ->and($stats['total_validations'])->toBe(2);
    });
});

describe('DataValidationService - Unknown Data Type', function () {
    it('handles unknown data type gracefully', function () {
        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once();

        $result = $this->service->validateData('unknown_type', []);

        expect($result['valid'])->toBeTrue()
            ->and($result['warnings'])->toContain('No validation rules defined')
            ->and($result['score'])->toBe(50.0);
    });
});

describe('DataValidationService - Edge Cases', function () {
    it('handles non-array data', function () {
        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', 'not_an_array');

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Data must be an array');
    });

    it('handles empty arrays', function () {
        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', []);

        $hasEmptyWarning = false;
        foreach ($result['warnings'] as $warning) {
            if (preg_match('/array is empty/i', $warning)) {
                $hasEmptyWarning = true;
                break;
            }
        }

        expect($hasEmptyWarning)->toBeTrue();
    });

    it('handles mixed valid and invalid data', function () {
        $data = [
            ['id' => 1, 'name' => 'Valid Character'],
            ['id' => 'invalid'], // Invalid
            ['id' => 3, 'name' => 'Another Valid'],
        ];

        Log::shouldReceive('info')->atLeast()->once();

        $result = $this->service->validateData('umapyoi_characters', $data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty()
            ->and($result['score'])->toBeGreaterThan(0)
            ->and($result['score'])->toBeLessThan(100);
    });
});
