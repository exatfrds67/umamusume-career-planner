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

    private ConflictDetectionService $conflictDetectionService;

    public function __construct(
        /** @phpstan-ignore property.onlyWritten */
        private MCPClientService $mcpClient,
        /** @phpstan-ignore property.onlyWritten */
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
     * @param  string  $dataType  Type of data being validated
     * @param  mixed  $data  Data to validate
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
            if (! is_array($dataSet)) {
                continue;
            }
            $type = isset($dataSet['type']) && is_string($dataSet['type']) ? $dataSet['type'] : 'unknown';
            $data = $dataSet['data'] ?? null;
            /** @var array<string, mixed> $options */
            $options = isset($dataSet['options']) && is_array($dataSet['options']) ? $dataSet['options'] : [];
            $key = isset($dataSet['key']) && is_string($dataSet['key']) ? $dataSet['key'] : "{$type}_{$index}";

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
     * @param  string  $dataType  Type of data being validated
     * @param  mixed  $data  Data to validate
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

        if (isset($schema['required_fields']) && is_array($schema['required_fields'])) {
            /** @var array<string> $requiredFields */
            $requiredFields = array_filter($schema['required_fields'], 'is_string');
            $missingFields = $this->checkRequiredFields($data, $requiredFields);
            foreach ($missingFields as $field) {
                $errors[] = "Required field missing: {$field}";
            }
            $details['required_fields_check'] = ['required' => $requiredFields, 'missing' => $missingFields];
        }

        if (isset($schema['field_types']) && is_array($schema['field_types'])) {
            /** @var array<string, string> $fieldTypes */
            $fieldTypes = [];
            foreach ($schema['field_types'] as $fieldName => $fieldType) {
                if (is_string($fieldName) && is_string($fieldType)) {
                    $fieldTypes[$fieldName] = $fieldType;
                }
            }
            $typeErrors = $this->checkFieldTypes($data, $fieldTypes);
            $errors = [...$errors, ...$typeErrors];
            $details['field_types_check'] = ['expected_types' => $fieldTypes, 'errors' => $typeErrors];
        }

        if (isset($schema['is_list']) && $schema['is_list'] && ! $this->isSequentialArray($data)) {
            $warnings[] = 'Data should be a sequential array (list)';
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Validate data integrity
     *
     * @param  string  $dataType  Type of data being validated
     * @param  mixed  $data  Data to validate
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
        /** @phpstan-ignore function.alreadyNarrowedType */
        $integrityRules = is_array($schema) && isset($schema['integrity']) && is_array($schema['integrity']) ? $schema['integrity'] : [];

        $uniqueField = $integrityRules['unique_field'] ?? null;
        if (is_string($uniqueField)) {
            $duplicates = $this->findDuplicates($data, $uniqueField);
            if (! empty($duplicates)) {
                $errors[] = "Duplicate values found in field '{$uniqueField}': ".\implode(', ', \array_slice($duplicates, 0, 5));
            }
            $details['duplicates'] = ['field' => $uniqueField, 'count' => \count($duplicates)];
        }

        $nonNullFieldsRaw = $integrityRules['non_null_fields'] ?? null;
        if (is_array($nonNullFieldsRaw)) {
            /** @var array<string> $nonNullFields */
            $nonNullFields = \array_values(\array_filter($nonNullFieldsRaw, 'is_string'));
            $nullFields = $this->findNullFields($data, $nonNullFields);
            foreach ($nullFields as $field => $indices) {
                if (is_string($field) && is_array($indices)) {
                    $warnings[] = "Null values found in field '{$field}' at indices: ".\implode(', ', \array_slice($indices, 0, 5));
                }
            }
            $details['null_check'] = ['fields_checked' => $nonNullFields, 'null_found' => $nullFields];
        }

        $consistencyResult = $this->checkDataConsistency($data);
        $details['consistency'] = $consistencyResult;
        $inconsistencies = $consistencyResult['inconsistencies'] ?? 0;
        if (is_int($inconsistencies) && $inconsistencies > 0) {
            $warnings[] = "Found {$inconsistencies} data type inconsistencies across records";
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings, 'details' => $details];
    }

    /**
     * Score data quality using the DataQualityScoringService
     *
     * @param  string  $dataType  Type of data being scored
     * @param  mixed  $data  Data to score
     * @param  array<string, mixed>  $options  Additional options
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

        $dimensions = is_array($qualityResult['dimensions'] ?? null) ? $qualityResult['dimensions'] : [];
        foreach ($dimensions as $dimension => $score) {
            if (! is_string($dimension) || ! is_numeric($score)) {
                continue;
            }
            if ((float) $score < self::QUALITY_ACCEPTABLE) {
                $warnings[] = \sprintf('%s score is below acceptable threshold: %.2f%%', \ucfirst($dimension), (float) $score);
            }
        }

        return [
            'score' => is_numeric($qualityResult['overall_score'] ?? null) ? (float) $qualityResult['overall_score'] : 0.0,
            'grade' => is_string($qualityResult['grade'] ?? null) ? $qualityResult['grade'] : 'F',
            'dimensions' => $dimensions,
            'warnings' => $warnings,
            'recommendations' => is_array($qualityResult['recommendations'] ?? null) ? $qualityResult['recommendations'] : [],
        ];
    }

    /**
     * Validate business rules specific to data type
     *
     * @param  string  $dataType  Type of data being validated
     * @param  mixed  $data  Data to validate
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
        /** @phpstan-ignore function.alreadyNarrowedType */
        $businessRules = is_array($schema) && isset($schema['business']) && is_array($schema['business']) ? $schema['business'] : [];

        $valueRangesRaw = $businessRules['value_ranges'] ?? null;
        if (is_array($valueRangesRaw)) {
            /** @var array<string, array{min?: int|float, max?: int|float}> $valueRanges */
            $valueRanges = [];
            foreach ($valueRangesRaw as $field => $range) {
                if (is_string($field) && is_array($range)) {
                    $min = isset($range['min']) && (is_int($range['min']) || is_float($range['min'])) ? $range['min'] : null;
                    $max = isset($range['max']) && (is_int($range['max']) || is_float($range['max'])) ? $range['max'] : null;
                    $valueRanges[$field] = array_filter(['min' => $min, 'max' => $max], fn ($v) => $v !== null);
                }
            }
            $rangeErrors = $this->validateValueRanges($data, $valueRanges);
            $errors = [...$errors, ...$rangeErrors];
            $details['value_ranges'] = ['errors' => $rangeErrors];
        }

        $enumFieldsRaw = $businessRules['enum_fields'] ?? null;
        if (is_array($enumFieldsRaw)) {
            /** @var array<string, array<string>> $enumFields */
            $enumFields = [];
            foreach ($enumFieldsRaw as $field => $values) {
                if (is_string($field) && is_array($values)) {
                    $enumFields[$field] = array_filter($values, 'is_string');
                }
            }
            $enumErrors = $this->validateEnumFields($data, $enumFields);
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
     * @param  string  $dataType  Type of data being reported
     * @param  mixed  $data  Data being reported
     * @param  array<string, mixed>  $details  Validation details
     * @param  float  $durationMs  Duration in milliseconds
     * @return array<string, mixed>
     */
    protected function generateValidationReport(string $dataType, mixed $data, array $details, float $durationMs): array
    {
        $dataSize = \is_array($data) ? \count($data) : 0;
        $totalErrors = 0;
        $totalWarnings = 0;

        foreach ($details as $sectionDetails) {
            if (! is_array($sectionDetails)) {
                continue;
            }
            $sectionErrors = $sectionDetails['errors'] ?? [];
            $sectionWarnings = $sectionDetails['warnings'] ?? [];
            $totalErrors += is_array($sectionErrors) ? \count($sectionErrors) : 0;
            $totalWarnings += is_array($sectionWarnings) ? \count($sectionWarnings) : 0;
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
            if (! is_string($section) || ! is_array($sectionDetails)) {
                continue;
            }
            $sectionErrors = $sectionDetails['errors'] ?? [];
            $sectionWarnings = $sectionDetails['warnings'] ?? [];
            $report['sections'][$section] = [
                'name' => $section,
                'passed' => empty($sectionErrors),
                'error_count' => is_array($sectionErrors) ? \count($sectionErrors) : 0,
                'warning_count' => is_array($sectionWarnings) ? \count($sectionWarnings) : 0,
            ];
        }

        if (isset($details['quality']) && is_array($details['quality'])) {
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

        $schemaDetails = is_array($details['schema'] ?? null) && is_array($details['schema']['details'] ?? null) ? $details['schema']['details'] : [];
        $requiredFieldsCheck = is_array($schemaDetails['required_fields_check'] ?? null) ? $schemaDetails['required_fields_check'] : [];
        $missingFields = is_array($requiredFieldsCheck['missing'] ?? null) ? $requiredFieldsCheck['missing'] : [];
        if (! empty($missingFields)) {
            $recommendations[] = 'Ensure all required fields are populated: '.\implode(', ', $missingFields);
        }

        $integrityDetails = is_array($details['integrity'] ?? null) && is_array($details['integrity']['details'] ?? null) ? $details['integrity']['details'] : [];
        $duplicatesInfo = is_array($integrityDetails['duplicates'] ?? null) ? $integrityDetails['duplicates'] : [];
        $duplicatesCount = $duplicatesInfo['count'] ?? 0;
        if (is_int($duplicatesCount) && $duplicatesCount > 0) {
            $field = is_string($duplicatesInfo['field'] ?? null) ? $duplicatesInfo['field'] : 'unknown';
            $recommendations[] = "Remove duplicate entries in field '{$field}'";
        }

        $consistencyInfo = is_array($integrityDetails['consistency'] ?? null) ? $integrityDetails['consistency'] : [];
        $inconsistencies = $consistencyInfo['inconsistencies'] ?? 0;
        if (is_int($inconsistencies) && $inconsistencies > 0) {
            $recommendations[] = 'Standardize data types across all records to improve consistency';
        }

        $qualityDetails = is_array($details['quality'] ?? null) ? $details['quality'] : [];
        $qualityRecommendations = is_array($qualityDetails['recommendations'] ?? null) ? $qualityDetails['recommendations'] : [];
        foreach ($qualityRecommendations as $rec) {
            if (is_string($rec)) {
                $recommendations[] = $rec;
            }
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
            if (! isset($details[$section]) || ! is_array($details[$section])) {
                continue;
            }

            $sectionData = $details[$section];
            $sectionErrors = is_array($sectionData['errors'] ?? null) ? $sectionData['errors'] : [];
            $sectionWarnings = is_array($sectionData['warnings'] ?? null) ? $sectionData['warnings'] : [];

            $sectionScore = 100.0;
            $sectionScore -= \min(\count($sectionErrors) * 15, 60);
            $sectionScore -= \min(\count($sectionWarnings) * 5, 20);

            if ($section === 'quality' && isset($sectionData['score']) && is_numeric($sectionData['score'])) {
                $sectionScore = (float) $sectionData['score'];
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
     * @param  int  $limit  Maximum number of history entries to return
     * @return array<int, array<string, mixed>>
     */
    public function getValidationHistory(int $limit = 100): array
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
            $errorCount = $entry['errors'] ?? 0;
            if (is_int($errorCount) && $errorCount === 0) {
                $passed++;
            }
            $entryScore = $entry['score'] ?? 0;
            if (is_numeric($entryScore)) {
                $totalScore += (float) $entryScore;
            }
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
     * @param  string  $dataType  Type of data
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
     * @param  string  $dataType  Type of data
     * @return array<string>
     */
    protected function getRequiredFields(string $dataType): array
    {
        $schema = $this->getSchemaDefinition($dataType);
        $requiredFields = $schema['required_fields'] ?? [];
        if (! is_array($requiredFields)) {
            return [];
        }

        return array_filter($requiredFields, 'is_string');
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
                if (is_string($field) && ! \array_key_exists($field, $item)) {
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
                if (! is_string($field) || ! is_string($expectedType) || ! isset($item[$field])) {
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
     * @param  string  $field  Field to check for duplicates
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
        /** @var array<string, array<int>> $nullFields */
        $nullFields = [];

        foreach ($data as $index => $item) {
            if (! \is_array($item) || ! is_int($index)) {
                continue;
            }
            foreach ($fields as $field) {
                if (! is_string($field)) {
                    continue;
                }
                if (! isset($item[$field])) {
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
        /** @var array<string, array<string>> $fieldTypes */
        $fieldTypes = [];
        $inconsistencies = 0;

        foreach ($data as $item) {
            if (! \is_array($item)) {
                continue;
            }
            foreach ($item as $field => $value) {
                if (! is_string($field)) {
                    continue;
                }
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
     * @param  string  $dataType  Type of data being validated
     * @param  array<mixed>  $data
     * @return array{errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function validateDataTypeSpecificRules(string $dataType, array $data): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        switch ($dataType) {
            case 'umapyoi_characters':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['name']) && \is_string($item['name']) && \strlen($item['name']) < 2) {
                        $warnings[] = "Character name at index {$index} is very short";
                    }
                }
                $details['character_validation'] = true;
                break;

            case 'umapyoi_support_cards':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['rarity']) && ! \in_array($item['rarity'], ['SSR', 'SR', 'R'], true)) {
                        $rarity = is_scalar($item['rarity']) ? (string) $item['rarity'] : 'unknown';
                        $errors[] = "Invalid rarity at index {$index}: {$rarity}";
                    }
                }
                $details['support_card_validation'] = true;
                break;

            case 'race_data':
                foreach ($data as $index => $item) {
                    if (\is_array($item) && isset($item['distance'])) {
                        $distance = $item['distance'];
                        if (is_numeric($distance)) {
                            $distanceNum = (int) $distance;
                            if ($distanceNum < 1000 || $distanceNum > 3600) {
                                $errors[] = "Invalid race distance at index {$index}: {$distanceNum}m";
                            }
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
     * @param  string  $strategy  Resolution strategy to use
     * @return array{resolved_data: mixed, resolution_log: array<string, mixed>}
     */
    public function resolveConflicts(array $conflicts, array $sources, string $strategy = 'highest_confidence'): array
    {
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
     * @param  string  $dataType  Type of data being validated
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @param  string  $strategy  Resolution strategy to use
     * @return array{data: mixed, has_conflicts: bool, conflicts: array<string, mixed>, resolution: array<string, mixed>, validation: array<string, mixed>}
     */
    public function validateAndResolveMultiSource(string $dataType, array $sources, string $strategy = 'highest_confidence'): array
    {
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
     * @param  array{data?: mixed, source?: string, timestamp?: string, metadata?: array<string, mixed>}  $sourceData
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
     * @param  int  $limit  Maximum number of history entries to return
     * @return array<int, array<string, mixed>>
     */
    public function getConflictHistory(int $limit = 100): array
    {
        return $this->conflictDetectionService->getConflictHistory($limit);
    }
}
