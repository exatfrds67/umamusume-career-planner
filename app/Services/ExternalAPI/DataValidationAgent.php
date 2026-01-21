<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

/**
 * Data Validation Agent for External API Integration
 *
 * Coordinates data validation operations using MCP strands-agents server for
 * schema validation, integrity checks, data quality scoring, and validation reporting.
 *
 * Features:
 * - Schema validation against expected data structures
 * - Data integrity checks (required fields, data types, value ranges)
 * - Data quality scoring based on completeness and accuracy
 * - Comprehensive validation reporting with detailed findings
 * - MCP agent coordination for complex validation workflows
 * - Integration with DataValidationService and DataQualityScoringService
 *
 * Requirements: 14.4 (MCP Subagent Coordination for API Management)
 * Task: 3.2.1
 */
class DataValidationAgent
{
    public const SEVERITY_ERROR = 'error';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_INFO = 'info';

    public const VALIDATION_SCHEMA = 'schema';

    public const VALIDATION_INTEGRITY = 'integrity';

    public const VALIDATION_QUALITY = 'quality';

    public const VALIDATION_BUSINESS = 'business';

    private const QUALITY_EXCELLENT = 90.0;

    private const QUALITY_GOOD = 75.0;

    private const QUALITY_ACCEPTABLE = 60.0;

    private const MAX_BATCH_SIZE = 100;

    /** @var array<string, array<string, mixed>> */
    private array $schemaDefinitions = [];

    /** @var array<int, array<string, mixed>> */
    private array $validationHistory = [];

    private ?ConflictDetectionService $conflictDetectionService = null;

    public function __construct(
        private MCPClientService $mcpClient,
        private DataValidationService $validationService,
        private DataQualityScoringService $qualityScoringService,
        ?ConflictDetectionService $conflictDetectionService = null
    ) {
        $this->conflictDetectionService = $conflictDetectionService ?? new ConflictDetectionService;
        $this->initializeSchemaDefinitions();
    }

    /**
     * Validate data against schema and integrity rules
     *
     * @param  array<string, mixed>  $options  Additional validation options
     * @return array{valid: bool, score: float, grade: string, errors: array<string>, warnings: array<string>, report: array<string, mixed>}
     */
    public function validate(string $dataType, mixed $data, array $options = []): array
    {
        $startTime = microtime(true);

        Log::info('[DataValidationAgent] Starting validation', [
            'data_type' => $dataType,
            'data_size' => \is_array($data) ? \count($data) : 0,
        ]);

        $errors = [];
        $warnings = [];
        $details = [];

        $schemaResult = $this->validateSchema($dataType, $data);
        $errors = [...$errors, ...$schemaResult['errors']];
        $warnings = [...$warnings, ...$schemaResult['warnings']];
        $details['schema'] = $schemaResult;

        $integrityResult = $this->validateIntegrity($dataType, $data);
        $errors = [...$errors, ...$integrityResult['errors']];
        $warnings = [...$warnings, ...$integrityResult['warnings']];
        $details['integrity'] = $integrityResult;

        $qualityResult = $this->scoreDataQuality($dataType, $data, $options);
        $warnings = [...$warnings, ...$qualityResult['warnings']];
        $details['quality'] = $qualityResult;

        if ($options['validate_business_rules'] ?? true) {
            $businessResult = $this->validateBusinessRules($dataType, $data);
            $errors = [...$errors, ...$businessResult['errors']];
            $warnings = [...$warnings, ...$businessResult['warnings']];
            $details['business'] = $businessResult;
        }

        $overallScore = $this->calculateOverallScore($details);
        $grade = $this->determineGrade($overallScore);
        $duration = (microtime(true) - $startTime) * 1000;
        $report = $this->generateValidationReport($dataType, $data, $details, $duration);
        $this->recordValidationHistory($dataType, $overallScore, $grade, \count($errors), \count($warnings));

        Log::info('[DataValidationAgent] Validation completed', [
            'data_type' => $dataType,
            'valid' => empty($errors),
            'score' => $overallScore,
            'grade' => $grade,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'valid' => empty($errors),
            'score' => $overallScore,
            'grade' => $grade,
            'errors' => array_unique($errors),
            'warnings' => array_unique($warnings),
            'report' => $report,
        ];
    }

    /**
     * Validate multiple data sets in batch
     *
     * @param  array<array{type: string, data: mixed, options?: array<string, mixed>}>  $dataSets
     * @return array{success: bool, results: array<string, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function validateBatch(array $dataSets): array
    {
        $startTime = microtime(true);

        if (\count($dataSets) > self::MAX_BATCH_SIZE) {
            $dataSets = \array_slice($dataSets, 0, self::MAX_BATCH_SIZE);
        }

        $results = [];
        $successCount = 0;
        $failureCount = 0;
        $totalScore = 0.0;

        foreach ($dataSets as $index => $dataSet) {
            $type = $dataSet['type'] ?? 'unknown';
            $data = $dataSet['data'] ?? null;
            $options = $dataSet['options'] ?? [];
            $key = $dataSet['key'] ?? "{$type}_{$index}";

            try {
                $result = $this->validate($type, $data, $options);
                $results[$key] = $result;
                $result['valid'] ? $successCount++ : $failureCount++;
                $totalScore += $result['score'];
            } catch (\Exception $e) {
                $failureCount++;
                $results[$key] = [
                    'valid' => false,
                    'score' => 0.0,
                    'grade' => 'F',
                    'errors' => [$e->getMessage()],
                    'warnings' => [],
                    'report' => ['error' => $e->getMessage()],
                ];
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;
        $totalItems = \count($dataSets);
        $avgScore = $totalItems > 0 ? $totalScore / $totalItems : 0.0;

        return [
            'success' => $failureCount === 0,
            'results' => $results,
            'summary' => [
                'total_items' => $totalItems,
                'successful' => $successCount,
                'failed' => $failureCount,
                'success_rate' => $totalItems > 0 ? round(($successCount / $totalItems) * 100, 2) : 0,
                'average_score' => round($avgScore, 2),
                'average_grade' => $this->determineGrade($avgScore),
                'duration_ms' => round($duration, 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Validate data against schema definition
     *
     * @return array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function validateSchema(string $dataType, mixed $data): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        $schema = $this->getSchemaDefinition($dataType);

        if (empty($schema)) {
            $warnings[] = "No schema definition found for data type: {$dataType}";

            return ['valid' => true, 'errors' => [], 'warnings' => $warnings, 'details' => ['schema_found' => false]];
        }

        if (! \is_array($data)) {
            return ['valid' => false, 'errors' => ['Data must be an array'], 'warnings' => [], 'details' => []];
        }

        if (isset($schema['required_fields'])) {
            $missingFields = $this->checkRequiredFields($data, $schema['required_fields']);
            foreach ($missingFields as $field) {
                $errors[] = "Required field missing: {$field}";
            }
            $details['required_fields_check'] = ['required' => $schema['required_fields'], 'missing' => $missingFields];
        }

        if (isset($schema['field_types'])) {
            $typeErrors = $this->checkFieldTypes($data, $schema['field_types']);
            $errors = [...$errors, ...$typeErrors];
            $details['field_types_check'] = ['expected_types' => $schema['field_types'], 'errors' => $typeErrors];
        }

        if (isset($schema['is_list']) && $schema['is_list'] && ! $this->isSequentialArray($data)) {
            $warnings[] = 'Data should be a sequential array (list)';
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Validate data integrity
     *
     * @return array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function validateIntegrity(string $dataType, mixed $data): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        if (! \is_array($data)) {
            return ['valid' => true, 'errors' => [], 'warnings' => ['Cannot perform integrity checks on non-array data'], 'details' => []];
        }

        if (empty($data)) {
            $warnings[] = 'Data array is empty';

            return ['valid' => true, 'errors' => [], 'warnings' => $warnings, 'details' => ['empty_check' => false]];
        }

        $schema = $this->getSchemaDefinition($dataType);
        $integrityRules = $schema['integrity'] ?? [];

        if (isset($integrityRules['unique_field'])) {
            $uniqueField = $integrityRules['unique_field'];
            $duplicates = $this->findDuplicates($data, $uniqueField);
            if (! empty($duplicates)) {
                $errors[] = "Duplicate values found in field '{$uniqueField}': ".\implode(', ', \array_slice($duplicates, 0, 5));
            }
            $details['duplicates'] = ['field' => $uniqueField, 'count' => \count($duplicates)];
        }

        if (isset($integrityRules['non_null_fields'])) {
            $nullFields = $this->findNullFields($data, $integrityRules['non_null_fields']);
            foreach ($nullFields as $field => $indices) {
                $warnings[] = "Null values found in field '{$field}' at indices: ".\implode(', ', \array_slice($indices, 0, 5));
            }
            $details['null_check'] = ['fields_checked' => $integrityRules['non_null_fields'], 'null_found' => $nullFields];
        }

        $consistencyResult = $this->checkDataConsistency($data);
        $details['consistency'] = $consistencyResult;
        if ($consistencyResult['inconsistencies'] > 0) {
            $warnings[] = "Found {$consistencyResult['inconsistencies']} data type inconsistencies across records";
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Score data quality using the DataQualityScoringService
     *
     * @param  array<string, mixed>  $options
     * @return array{score: float, grade: string, dimensions: array<string, float>, warnings: array<string>, recommendations: array<string>}
     */
    protected function scoreDataQuality(string $dataType, mixed $data, array $options = []): array
    {
        $metadata = [
            'required_fields' => $this->getRequiredFields($dataType),
            'data_age_hours' => $options['data_age_hours'] ?? 0,
            'validation_errors' => $options['validation_errors'] ?? [],
            'conflicts' => $options['conflicts'] ?? [],
        ];

        $qualityResult = $this->qualityScoringService->calculateQualityScore($dataType, $data, $metadata);
        $warnings = [];

        foreach ($qualityResult['dimensions'] as $dimension => $score) {
            if ($score < self::QUALITY_ACCEPTABLE) {
                $warnings[] = \sprintf('%s score is below acceptable threshold: %.2f%%', \ucfirst($dimension), $score);
            }
        }

        return [
            'score' => $qualityResult['overall_score'],
            'grade' => $qualityResult['grade'],
            'dimensions' => $qualityResult['dimensions'],
            'warnings' => $warnings,
            'recommendations' => $qualityResult['recommendations'],
        ];
    }

    /**
     * Validate business rules specific to data type
     *
     * @return array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function validateBusinessRules(string $dataType, mixed $data): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        if (! \is_array($data)) {
            return ['valid' => true, 'errors' => [], 'warnings' => [], 'details' => []];
        }

        $schema = $this->getSchemaDefinition($dataType);
        $businessRules = $schema['business'] ?? [];

        if (isset($businessRules['value_ranges'])) {
            $rangeErrors = $this->validateValueRanges($data, $businessRules['value_ranges']);
            $errors = [...$errors, ...$rangeErrors];
            $details['value_ranges'] = ['errors' => $rangeErrors];
        }

        if (isset($businessRules['enum_fields'])) {
            $enumErrors = $this->validateEnumFields($data, $businessRules['enum_fields']);
            $errors = [...$errors, ...$enumErrors];
            $details['enum_fields'] = ['errors' => $enumErrors];
        }

        $specificResult = $this->validateDataTypeSpecificRules($dataType, $data);
        $errors = [...$errors, ...$specificResult['errors']];
        $warnings = [...$warnings, ...$specificResult['warnings']];
        $details['type_specific'] = $specificResult['details'];

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Generate comprehensive validation report
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    protected function generateValidationReport(string $dataType, mixed $data, array $details, float $durationMs): array
    {
        $dataSize = \is_array($data) ? \count($data) : 0;
        $totalErrors = 0;
        $totalWarnings = 0;

        foreach ($details as $sectionDetails) {
            $totalErrors += \count($sectionDetails['errors'] ?? []);
            $totalWarnings += \count($sectionDetails['warnings'] ?? []);
        }

        $report = [
            'summary' => [
                'data_type' => $dataType,
                'data_size' => $dataSize,
                'total_errors' => $totalErrors,
                'total_warnings' => $totalWarnings,
                'validation_passed' => $totalErrors === 0,
                'duration_ms' => round($durationMs, 2),
                'timestamp' => now()->toIso8601String(),
            ],
            'sections' => [],
            'recommendations' => $this->generateRecommendations($details),
        ];

        foreach ($details as $section => $sectionDetails) {
            $report['sections'][$section] = [
                'name' => $section,
                'passed' => empty($sectionDetails['errors'] ?? []),
                'error_count' => \count($sectionDetails['errors'] ?? []),
                'warning_count' => \count($sectionDetails['warnings'] ?? []),
            ];
        }

        if (isset($details['quality'])) {
            $report['quality_metrics'] = [
                'overall_score' => $details['quality']['score'] ?? 0,
                'grade' => $details['quality']['grade'] ?? 'N/A',
                'dimensions' => $details['quality']['dimensions'] ?? [],
            ];
        }

        return $report;
    }

    /**
     * Generate recommendations based on validation findings
     *
     * @param  array<string, mixed>  $details
     * @return array<string>
     */
    protected function generateRecommendations(array $details): array
    {
        $recommendations = [];

        if (isset($details['schema']['details']['required_fields_check']['missing'])) {
            $missing = $details['schema']['details']['required_fields_check']['missing'];
            if (! empty($missing)) {
                $recommendations[] = 'Ensure all required fields are populated: '.\implode(', ', $missing);
            }
        }

        if (isset($details['integrity']['details']['duplicates']['count']) && $details['integrity']['details']['duplicates']['count'] > 0) {
            $field = $details['integrity']['details']['duplicates']['field'];
            $recommendations[] = "Remove duplicate entries in field '{$field}'";
        }

        if (isset($details['integrity']['details']['consistency']['inconsistencies']) && $details['integrity']['details']['consistency']['inconsistencies'] > 0) {
            $recommendations[] = 'Standardize data types across all records to improve consistency';
        }

        if (isset($details['quality']['recommendations'])) {
            $recommendations = [...$recommendations, ...$details['quality']['recommendations']];
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Data validation passed all checks. Continue current data quality practices.';
        }

        return array_unique($recommendations);
    }

    /**
     * Calculate overall validation score
     *
     * @param  array<string, mixed>  $details
     */
    protected function calculateOverallScore(array $details): float
    {
        $weights = ['schema' => 0.30, 'integrity' => 0.25, 'quality' => 0.30, 'business' => 0.15];
        $totalWeight = 0.0;
        $weightedScore = 0.0;

        foreach ($weights as $section => $weight) {
            if (! isset($details[$section])) {
                continue;
            }

            $sectionScore = 100.0;
            $sectionScore -= \min(\count($details[$section]['errors'] ?? []) * 15, 60);
            $sectionScore -= \min(\count($details[$section]['warnings'] ?? []) * 5, 20);

            if ($section === 'quality' && isset($details[$section]['score'])) {
                $sectionScore = $details[$section]['score'];
            }

            $weightedScore += \max(0, $sectionScore) * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedScore / $totalWeight, 2) : 0.0;
    }

    /** Determine grade based on score */
    protected function determineGrade(float $score): string
    {
        return match (true) {
            $score >= self::QUALITY_EXCELLENT => 'A',
            $score >= self::QUALITY_GOOD => 'B',
            $score >= self::QUALITY_ACCEPTABLE => 'C',
            $score >= 50.0 => 'D',
            default => 'F',
        };
    }

    /** Record validation in history */
    protected function recordValidationHistory(string $dataType, float $score, string $grade, int $errorCount, int $warningCount): void
    {
        $this->validationHistory[] = [
            'data_type' => $dataType,
            'score' => $score,
            'grade' => $grade,
            'errors' => $errorCount,
            'warnings' => $warningCount,
            'timestamp' => now()->toIso8601String(),
        ];

        if (\count($this->validationHistory) > 100) {
            \array_shift($this->validationHistory);
        }
    }

    /**
     * Get validation history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getValidationHistory(int $limit = 10): array
    {
        return \array_slice($this->validationHistory, -$limit);
    }

    /**
     * Get validation statistics
     *
     * @return array{total: int, passed: int, failed: int, avg_score: float, pass_rate: float}
     */
    public function getValidationStatistics(): array
    {
        $total = \count($this->validationHistory);
        $passed = 0;
        $totalScore = 0.0;

        foreach ($this->validationHistory as $entry) {
            if ($entry['errors'] === 0) {
                $passed++;
            }
            $totalScore += $entry['score'];
        }

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'avg_score' => $total > 0 ? round($totalScore / $total, 2) : 0.0,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0.0,
        ];
    }

    /** Initialize schema definitions for known data types */
    protected function initializeSchemaDefinitions(): void
    {
        $this->schemaDefinitions = [
            'umapyoi_characters' => [
                'required_fields' => ['id', 'name'],
                'field_types' => ['id' => 'integer', 'name' => 'string'],
                'is_list' => true,
                'integrity' => ['unique_field' => 'id', 'non_null_fields' => ['name']],
                'business' => ['value_ranges' => ['id' => ['min' => 1]]],
            ],
            'umapyoi_support_cards' => [
                'required_fields' => ['id', 'name', 'rarity'],
                'field_types' => ['id' => 'integer', 'name' => 'string', 'rarity' => 'string'],
                'is_list' => true,
                'integrity' => ['unique_field' => 'id', 'non_null_fields' => ['name', 'rarity']],
                'business' => ['enum_fields' => ['rarity' => ['SSR', 'SR', 'R']]],
            ],
            'umapyoi_skills' => [
                'required_fields' => ['id', 'name'],
                'field_types' => ['id' => 'integer', 'name' => 'string', 'sp_cost' => 'integer'],
                'is_list' => true,
                'integrity' => ['unique_field' => 'id', 'non_null_fields' => ['name']],
                'business' => ['value_ranges' => ['sp_cost' => ['min' => 0, 'max' => 500]]],
            ],
            'umamusumedb_meta' => [
                'required_fields' => ['card_id', 'tier'],
                'field_types' => ['card_id' => 'integer', 'tier' => 'string'],
                'is_list' => true,
                'integrity' => ['unique_field' => 'card_id', 'non_null_fields' => ['tier']],
                'business' => ['enum_fields' => ['tier' => ['SS', 'S', 'A', 'B', 'C', 'D', 'F']]],
            ],
            'race_data' => [
                'required_fields' => ['id', 'name', 'distance'],
                'field_types' => ['id' => 'integer', 'name' => 'string', 'distance' => 'integer'],
                'is_list' => true,
                'integrity' => ['unique_field' => 'id', 'non_null_fields' => ['name', 'distance']],
                'business' => [
                    'value_ranges' => ['distance' => ['min' => 1000, 'max' => 3600]],
                    'enum_fields' => ['surface' => ['turf', 'dirt'], 'direction' => ['left', 'right']],
                ],
            ],
        ];
    }

    /**
     * Get schema definition for a data type
     *
     * @return array<string, mixed>
     */
    protected function getSchemaDefinition(string $dataType): array
    {
        return $this->schemaDefinitions[$dataType] ?? [];
    }

    /**
     * Register a custom schema definition
     *
     * @param  array<string, mixed>  $schema
     */
    public function registerSchema(string $dataType, array $schema): void
    {
        $this->schemaDefinitions[$dataType] = $schema;
        Log::debug('[DataValidationAgent] Schema registered', ['data_type' => $dataType]);
    }

    /**
     * Get required fields for a data type
     *
     * @return array<string>
     */
    protected function getRequiredFields(string $dataType): array
    {
        return $this->getSchemaDefinition($dataType)['required_fields'] ?? [];
    }

    /**
     * Check required fields in data
     *
     * @param  array<mixed>  $data
     * @param  array<string>  $requiredFields
     * @return array<string>
     */
    protected function checkRequiredFields(array $data, array $requiredFields): array
    {
        $missing = [];
        $items = $this->isSequentialArray($data) && ! empty($data) ? [$data[0]] : [$data];

        foreach ($items as $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($requiredFields as $field) {
                if (! \array_key_exists($field, $item)) {
                    $missing[] = $field;
                }
            }
        }

        return array_unique($missing);
    }

    /**
     * Check field types in data
     *
     * @param  array<mixed>  $data
     * @param  array<string, string>  $expectedTypes
     * @return array<string>
     */
    protected function checkFieldTypes(array $data, array $expectedTypes): array
    {
        $errors = [];
        $items = $this->isSequentialArray($data) ? $data : [$data];

        foreach ($items as $index => $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($expectedTypes as $field => $expectedType) {
                if (! isset($item[$field])) {
                    continue;
                }
                $actualType = \gettype($item[$field]);
                if ($this->normalizeType($expectedType) !== $this->normalizeType($actualType)) {
                    $errors[] = "Field '{$field}' at index {$index} has incorrect type. Expected: {$expectedType}, Got: {$actualType}";
                }
            }
        }

        return $errors;
    }

    /** Normalize type name for comparison */
    protected function normalizeType(string $type): string
    {
        return match (\strtolower($type)) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'double',
            'bool', 'boolean' => 'boolean',
            'str', 'string' => 'string',
            default => \strtolower($type),
        };
    }

    /**
     * Check if array is sequential (list)
     *
     * @param  array<mixed>  $array
     */
    protected function isSequentialArray(array $array): bool
    {
        return empty($array) || \array_keys($array) === \range(0, \count($array) - 1);
    }

    /**
     * Find duplicate values in a field
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function findDuplicates(array $data, string $field): array
    {
        $values = [];
        $duplicates = [];

        foreach ($data as $item) {
            if (! \is_array($item) || ! isset($item[$field])) {
                continue;
            }
            $value = $item[$field];
            if (\in_array($value, $values, true)) {
                $duplicates[] = $value;
            } else {
                $values[] = $value;
            }
        }

        return array_unique($duplicates);
    }

    /**
     * Find null values in specified fields
     *
     * @param  array<mixed>  $data
     * @param  array<string>  $fields
     * @return array<string, array<int>>
     */
    protected function findNullFields(array $data, array $fields): array
    {
        $nullFields = [];

        foreach ($data as $index => $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($fields as $field) {
                if (! isset($item[$field]) || $item[$field] === null) {
                    if (! isset($nullFields[$field])) {
                        $nullFields[$field] = [];
                    }
                    $nullFields[$field][] = $index;
                }
            }
        }

        return $nullFields;
    }

    /**
     * Check data consistency across records
     *
     * @param  array<mixed>  $data
     * @return array{inconsistencies: int, field_types: array<string, array<string>>}
     */
    protected function checkDataConsistency(array $data): array
    {
        $fieldTypes = [];
        $inconsistencies = 0;

        foreach ($data as $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($item as $field => $value) {
                $type = \gettype($value);
                if (! isset($fieldTypes[$field])) {
                    $fieldTypes[$field] = [$type];
                } elseif (! \in_array($type, $fieldTypes[$field], true)) {
                    $fieldTypes[$field][] = $type;
                    $inconsistencies++;
                }
            }
        }

        return ['inconsistencies' => $inconsistencies, 'field_types' => $fieldTypes];
    }

    /**
     * Validate value ranges
     *
     * @param  array<mixed>  $data
     * @param  array<string, array{min?: int|float, max?: int|float}>  $ranges
     * @return array<string>
     */
    protected function validateValueRanges(array $data, array $ranges): array
    {
        $errors = [];
        $items = $this->isSequentialArray($data) ? $data : [$data];

        foreach ($items as $index => $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($ranges as $field => $range) {
                if (! isset($item[$field]) || ! \is_numeric($item[$field])) {
                    continue;
                }
                $value = $item[$field];
                if (isset($range['min']) && $value < $range['min']) {
                    $errors[] = "Field '{$field}' at index {$index} is below minimum ({$range['min']}): {$value}";
                }
                if (isset($range['max']) && $value > $range['max']) {
                    $errors[] = "Field '{$field}' at index {$index} exceeds maximum ({$range['max']}): {$value}";
                }
            }
        }

        return $errors;
    }

    /**
     * Validate enum fields
     *
     * @param  array<mixed>  $data
     * @param  array<string, array<string>>  $enumFields
     * @return array<string>
     */
    protected function validateEnumFields(array $data, array $enumFields): array
    {
        $errors = [];
        $items = $this->isSequentialArray($data) ? $data : [$data];

        foreach ($items as $index => $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($enumFields as $field => $allowedValues) {
                if (! isset($item[$field])) {
                    continue;
                }
                if (! \in_array($item[$field], $allowedValues, true)) {
                    $errors[] = "Invalid value in field '{$field}' at index {$index}. Allowed: ".\implode(', ', $allowedValues);
                }
            }
        }

        return $errors;
    }

    /**
     * Validate data type specific rules
     *
     * @param  array<mixed>  $data
     * @return array{errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function validateDataTypeSpecificRules(string $dataType, mixed $data): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        if (! \is_array($data)) {
            return ['errors' => [], 'warnings' => [], 'details' => []];
        }

        switch ($dataType) {
            case 'umapyoi_characters':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['name']) && \strlen($item['name']) < 2) {
                        $warnings[] = "Character name at index {$index} is very short";
                    }
                }
                $details['character_validation'] = true;
                break;

            case 'umapyoi_support_cards':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['rarity']) && ! \in_array($item['rarity'], ['SSR', 'SR', 'R'], true)) {
                        $errors[] = "Invalid rarity at index {$index}: {$item['rarity']}";
                    }
                }
                $details['support_card_validation'] = true;
                break;

            case 'race_data':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['distance'])) {
                        $distance = $item['distance'];
                        if ($distance < 1000 || $distance > 3600) {
                            $errors[] = "Invalid race distance at index {$index}: {$distance}m";
                        }
                    }
                }
                $details['race_validation'] = true;
                break;

            default:
                $details['generic_validation'] = true;
        }

        return ['errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Detect conflicts between multiple data sources
     *
     * Task: 3.2.2 - Implement conflict detection
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{has_conflicts: bool, conflicts: array<string, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function detectConflicts(array $sources): array
    {
        Log::info('[DataValidationAgent] Detecting conflicts between sources', [
            'sources_count' => \count($sources),
            'source_names' => array_keys($sources),
        ]);

        return $this->conflictDetectionService->detectConflicts($sources);
    }

    /**
     * Resolve conflicts using the specified strategy
     *
     * Task: 3.2.2 - Implement resolution strategies
     *
     * @param  array<string, array<string, mixed>>  $conflicts
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{resolved_data: mixed, resolution_log: array<string, mixed>}
     */
    public function resolveConflicts(
        array $conflicts,
        array $sources,
        string $strategy = ConflictDetectionService::STRATEGY_HIGHEST_CONFIDENCE
    ): array {
        Log::info('[DataValidationAgent] Resolving conflicts', [
            'conflict_count' => \count($conflicts),
            'strategy' => $strategy,
        ]);

        return $this->conflictDetectionService->resolveConflicts($conflicts, $sources, $strategy);
    }

    /**
     * Validate and resolve conflicts from multiple sources in one operation
     *
     * Task: 3.2.2 - Combined validation and conflict resolution
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{data: mixed, has_conflicts: bool, conflicts: array<string, mixed>, resolution: array<string, mixed>, validation: array<string, mixed>}
     */
    public function validateAndResolveMultiSource(
        string $dataType,
        array $sources,
        string $strategy = ConflictDetectionService::STRATEGY_HIGHEST_CONFIDENCE
    ): array {
        $startTime = microtime(true);

        Log::info('[DataValidationAgent] Starting multi-source validation and conflict resolution', [
            'data_type' => $dataType,
            'sources_count' => \count($sources),
        ]);

        // First, detect and resolve conflicts
        $conflictResult = $this->conflictDetectionService->validateAndResolve($sources, $strategy);

        // Then validate the resolved data
        $validationResult = $this->validate($dataType, $conflictResult['data']);

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[DataValidationAgent] Multi-source validation completed', [
            'data_type' => $dataType,
            'has_conflicts' => $conflictResult['has_conflicts'],
            'validation_passed' => $validationResult['valid'],
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'data' => $conflictResult['data'],
            'has_conflicts' => $conflictResult['has_conflicts'],
            'conflicts' => $conflictResult['conflicts'],
            'resolution' => $conflictResult['resolution'],
            'validation' => $validationResult,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Get confidence score for a data source
     *
     * Task: 3.2.2 - Add confidence scoring
     *
     * @param  array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}  $sourceData
     */
    public function getSourceConfidence(string $sourceName, array $sourceData = []): float
    {
        return $this->conflictDetectionService->calculateConfidence($sourceName, $sourceData);
    }

    /**
     * Get all source confidence scores
     *
     * @return array<string, float>
     */
    public function getAllSourceConfidences(): array
    {
        return $this->conflictDetectionService->getAllSourceConfidences();
    }

    /**
     * Get conflict detection statistics
     *
     * @return array{total: int, by_severity: array<string, int>, by_type: array<string, int>, avg_per_detection: float}
     */
    public function getConflictStatistics(): array
    {
        return $this->conflictDetectionService->getConflictStatistics();
    }

    /**
     * Get conflict history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConflictHistory(int $limit = 50): array
    {
        return $this->conflictDetectionService->getConflictHistory($limit);
    }
}
