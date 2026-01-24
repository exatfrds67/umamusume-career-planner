<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

/**
 * Data Validation Service with MCP Tool Chaining
 *
 * Implements comprehensive data validation workflows using MCP tool chaining
 * for accuracy verification, schema validation, and data integrity checks.
 *
 * Requirements: 14.3, 14.4, 56.3, Task 4.4.4
 */
class DataValidationService
{
    /**
     * Validation rules cache
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $validationRules = [];

    /**
     * Validation history
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $validationHistory = [];

    public function __construct(
        protected MCPClientService $mcpClient
    ) {
        $this->initializeValidationRules();
    }

    /**
     * Validate data with MCP tool chaining
     *
     * @return array{valid: bool, errors: array<string>, warnings: array<string>, score: float, details: array<string, mixed>}
     */
    public function validateData(string $dataType, mixed $data): array
    {
        Log::info('[DataValidation] Starting validation', [
            'data_type' => $dataType,
            'data_size' => is_array($data) ? count($data) : 0,
        ]);

        $startTime = microtime(true);

        // Get validation rules for data type
        $rules = $this->getValidationRules($dataType);

        if (empty($rules)) {
            Log::warning('[DataValidation] No validation rules found', [
                'data_type' => $dataType,
            ]);

            return [
                'valid' => true,
                'errors' => [],
                'warnings' => ['No validation rules defined'],
                'score' => 50.0,
                'details' => [],
            ];
        }

        // Execute validation workflow
        $validationResult = $this->executeValidationWorkflow($data, $rules);

        // Calculate validation score
        $score = $this->calculateValidationScore($validationResult);

        // Record validation history
        $this->recordValidationHistory($dataType, $validationResult, $score);

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[DataValidation] Validation completed', [
            'data_type' => $dataType,
            'valid' => $validationResult['valid'],
            'errors_count' => count($validationResult['errors']),
            'warnings_count' => count($validationResult['warnings']),
            'score' => $score,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'valid' => $validationResult['valid'],
            'errors' => $validationResult['errors'],
            'warnings' => $validationResult['warnings'],
            'score' => $score,
            'details' => $validationResult['details'],
        ];
    }

    /**
     * Execute validation workflow with MCP tool chaining
     *
     * @param  array<string, mixed>  $rules
     * @return array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}
     */
    protected function executeValidationWorkflow(mixed $data, array $rules): array
    {
        $errors = [];
        $warnings = [];
        $details = [];

        // Extract rules with type guards
        /** @var array<string, mixed> $schemaRules */
        $schemaRules = isset($rules['schema']) && is_array($rules['schema']) ? $rules['schema'] : [];
        /** @var array<string, mixed> $integrityRules */
        $integrityRules = isset($rules['integrity']) && is_array($rules['integrity']) ? $rules['integrity'] : [];
        /** @var array<string, mixed> $businessRulesConfig */
        $businessRulesConfig = isset($rules['business']) && is_array($rules['business']) ? $rules['business'] : [];
        /** @var array<string, mixed> $qualityRules */
        $qualityRules = isset($rules['quality']) && is_array($rules['quality']) ? $rules['quality'] : [];

        // Step 1: Schema validation
        $schemaValidation = $this->validateSchema($data, $schemaRules);

        if (! $schemaValidation['valid']) {
            $errors = array_merge($errors, $schemaValidation['errors']);
        }

        $warnings = array_merge($warnings, $schemaValidation['warnings']);
        $details['schema_validation'] = $schemaValidation;

        // Step 2: Data integrity checks
        $integrityChecks = $this->validateDataIntegrity($data, $integrityRules);

        if (! $integrityChecks['valid']) {
            $errors = array_merge($errors, $integrityChecks['errors']);
        }

        $warnings = array_merge($warnings, $integrityChecks['warnings']);
        $details['integrity_checks'] = $integrityChecks;

        // Step 3: Business rules validation
        $businessRulesResult = $this->validateBusinessRules($data, $businessRulesConfig);

        if (! $businessRulesResult['valid']) {
            $errors = array_merge($errors, $businessRulesResult['errors']);
        }

        $warnings = array_merge($warnings, $businessRulesResult['warnings']);
        $details['business_rules'] = $businessRulesResult;

        // Step 4: Data quality checks
        $qualityChecks = $this->validateDataQuality($data, $qualityRules);

        $warnings = array_merge($warnings, $qualityChecks['warnings']);
        $details['quality_checks'] = $qualityChecks;

        return [
            'valid' => empty($errors),
            'errors' => array_unique($errors),
            'warnings' => array_unique($warnings),
            'details' => $details,
        ];
    }

    /**
     * Validate data schema
     *
     * @param  array<string, mixed>  $schemaRules
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    protected function validateSchema(mixed $data, array $schemaRules = []): array
    {
        $errors = [];
        $warnings = [];

        if (empty($schemaRules)) {
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        // Check if data is array
        if (! is_array($data)) {
            $errors[] = 'Data must be an array';

            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Check if this is list data
        $isList = isset($schemaRules['is_list']) && $schemaRules['is_list'];

        if ($isList) {
            // Validate array structure for list data
            if (! $this->isSequentialArray($data)) {
                $warnings[] = 'Data should be a sequential array (list)';
            }

            // Validate each item in the list
            foreach ($data as $index => $item) {
                if (! is_array($item)) {
                    $errors[] = "Item at index {$index} is not an array";

                    continue;
                }

                // Validate required fields for each item
                $requiredFields = $schemaRules['required_fields'] ?? [];
                if (is_array($requiredFields)) {
                    foreach ($requiredFields as $field) {
                        if (! is_scalar($field)) {
                            continue;
                        }
                        $fieldStr = (string) $field;
                        if (! isset($item[$fieldStr]) && ! array_key_exists($fieldStr, $item)) {
                            $errors[] = "Required field missing: {$fieldStr} at index {$index}";
                        }
                    }
                }

                // Validate field types for each item
                $fieldTypes = $schemaRules['field_types'] ?? [];
                if (is_array($fieldTypes)) {
                    foreach ($fieldTypes as $field => $expectedType) {
                        if (! is_scalar($expectedType)) {
                            continue;
                        }
                        $fieldStr = (string) $field;
                        $expectedTypeStr = (string) $expectedType;
                        if (isset($item[$fieldStr])) {
                            $actualType = gettype($item[$fieldStr]);

                            if ($actualType !== $expectedTypeStr) {
                                $errors[] = "Field '{$fieldStr}' has incorrect type. Expected: {$expectedTypeStr}, Got: {$actualType} at index {$index}";
                            }
                        }
                    }
                }
            }
        } else {
            // Validate single record
            // Validate required fields
            $requiredFields = $schemaRules['required_fields'] ?? [];
            if (is_array($requiredFields)) {
                foreach ($requiredFields as $field) {
                    if (! is_scalar($field)) {
                        continue;
                    }
                    $fieldStr = (string) $field;
                    if (! isset($data[$fieldStr]) && ! array_key_exists($fieldStr, $data)) {
                        $errors[] = "Required field missing: {$fieldStr}";
                    }
                }
            }

            // Validate field types
            $fieldTypes = $schemaRules['field_types'] ?? [];
            if (is_array($fieldTypes)) {
                foreach ($fieldTypes as $field => $expectedType) {
                    if (! is_scalar($expectedType)) {
                        continue;
                    }
                    $fieldStr = (string) $field;
                    $expectedTypeStr = (string) $expectedType;
                    if (isset($data[$fieldStr])) {
                        $actualType = gettype($data[$fieldStr]);

                        if ($actualType !== $expectedTypeStr) {
                            $errors[] = "Field '{$fieldStr}' has incorrect type. Expected: {$expectedTypeStr}, Got: {$actualType}";
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate data integrity
     *
     * @param  array<string, mixed>  $integrityRules
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    protected function validateDataIntegrity(mixed $data, array $integrityRules = []): array
    {
        $errors = [];
        $warnings = [];

        if (empty($integrityRules) || ! is_array($data)) {
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        // Check for duplicate entries
        if (isset($integrityRules['unique_field'])) {
            $uniqueField = $integrityRules['unique_field'];
            if (is_string($uniqueField)) {
                $values = array_column($data, $uniqueField);
                $duplicates = array_diff_assoc($values, array_unique($values));

                if (! empty($duplicates)) {
                    $errors[] = "Duplicate values found in field: {$uniqueField}";
                }
            }
        }

        // Check for null values in critical fields
        $nonNullFields = $integrityRules['non_null_fields'] ?? [];
        if (is_array($nonNullFields)) {
            foreach ($nonNullFields as $field) {
                if (! is_scalar($field)) {
                    continue;
                }
                $fieldStr = (string) $field;
                foreach ($data as $index => $item) {
                    if (is_array($item) && ! isset($item[$fieldStr])) {
                        $warnings[] = "Null value found in critical field '{$fieldStr}' at index {$index}";
                    }
                }
            }
        }

        // Check for empty arrays
        if (isset($integrityRules['check_empty']) && $integrityRules['check_empty']) {
            if (empty($data)) {
                $warnings[] = 'Data array is empty';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate business rules
     *
     * @param  array<string, mixed>  $businessRules
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    protected function validateBusinessRules(mixed $data, array $businessRules = []): array
    {
        $errors = [];
        $warnings = [];

        if (empty($businessRules) || ! is_array($data)) {
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        }

        // Validate value ranges
        $valueRanges = $businessRules['value_ranges'] ?? [];
        if (is_array($valueRanges)) {
            foreach ($valueRanges as $field => $range) {
                if (! is_array($range)) {
                    continue;
                }
                $fieldStr = (string) $field;
                foreach ($data as $index => $item) {
                    if (is_array($item) && isset($item[$fieldStr])) {
                        $value = $item[$fieldStr];

                        $minVal = $range['min'] ?? null;
                        $maxVal = $range['max'] ?? null;

                        if (is_numeric($minVal) && is_numeric($value) && $value < $minVal) {
                            $errors[] = "Value in field '{$fieldStr}' at index {$index} is below minimum ({$minVal})";
                        }

                        if (is_numeric($maxVal) && is_numeric($value) && $value > $maxVal) {
                            $errors[] = "Value in field '{$fieldStr}' at index {$index} exceeds maximum ({$maxVal})";
                        }
                    }
                }
            }
        }

        // Validate enum values
        $enumFields = $businessRules['enum_fields'] ?? [];
        if (is_array($enumFields)) {
            foreach ($enumFields as $field => $allowedValues) {
                $fieldStr = (string) $field;
                if (! is_array($allowedValues)) {
                    continue;
                }
                foreach ($data as $index => $item) {
                    if (is_array($item) && isset($item[$fieldStr])) {
                        if (! in_array($item[$fieldStr], $allowedValues, true)) {
                            $errors[] = "Invalid value in field '{$fieldStr}' at index {$index}. Allowed: ".implode(', ', $allowedValues);
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate data quality
     *
     * @param  array<string, mixed>  $qualityRules
     * @return array{valid: bool, warnings: array<string>, metrics: array<string, mixed>}
     */
    protected function validateDataQuality(mixed $data, array $qualityRules = []): array
    {
        $warnings = [];
        $metrics = [];

        if (! is_array($data)) {
            return ['valid' => true, 'warnings' => [], 'metrics' => []];
        }

        // Calculate completeness
        $completeness = $this->calculateCompleteness($data, $qualityRules);
        $metrics['completeness'] = $completeness;

        if ($completeness < 80) {
            $warnings[] = sprintf('Data completeness is low: %.2f%%', $completeness);
        }

        // Calculate consistency
        $consistency = $this->calculateConsistency($data, $qualityRules);
        $metrics['consistency'] = $consistency;

        if ($consistency < 90) {
            $warnings[] = sprintf('Data consistency is low: %.2f%%', $consistency);
        }

        // Check data freshness
        $freshnessField = $qualityRules['freshness_field'] ?? null;
        if (is_string($freshnessField)) {
            $freshness = $this->calculateFreshness($data, $freshnessField);
            $metrics['freshness'] = $freshness;

            if ($freshness < 70) {
                $warnings[] = sprintf('Data freshness is low: %.2f%%', $freshness);
            }
        }

        return [
            'valid' => true,
            'warnings' => $warnings,
            'metrics' => $metrics,
        ];
    }

    /**
     * Calculate data completeness percentage
     *
     * @param  array<mixed>  $data
     * @param  array<string, mixed>  $qualityRules
     */
    protected function calculateCompleteness(array $data, array $qualityRules): float
    {
        if (empty($data)) {
            return 0.0;
        }

        $requiredFields = $qualityRules['required_fields'] ?? [];

        if (! is_array($requiredFields) || empty($requiredFields)) {
            return 100.0;
        }

        $totalFields = count($requiredFields) * count($data);
        $filledFields = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($requiredFields as $field) {
                if (! is_scalar($field)) {
                    continue;
                }
                $fieldStr = (string) $field;
                if (isset($item[$fieldStr]) && $item[$fieldStr] !== '') {
                    $filledFields++;
                }
            }
        }

        return $totalFields > 0 ? ($filledFields / $totalFields) * 100 : 0.0;
    }

    /**
     * Calculate data consistency percentage
     *
     * @param  array<mixed>  $data
     * @param  array<string, mixed>  $qualityRules
     */
    protected function calculateConsistency(array $data, array $qualityRules): float
    {
        if (empty($data)) {
            return 100.0;
        }

        // Check for consistent data types across records
        $fieldTypes = [];
        $inconsistencies = 0;
        $totalChecks = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                $type = gettype($value);

                if (! isset($fieldTypes[$field])) {
                    $fieldTypes[$field] = $type;
                } elseif ($fieldTypes[$field] !== $type) {
                    $inconsistencies++;
                }

                $totalChecks++;
            }
        }

        return $totalChecks > 0 ? (($totalChecks - $inconsistencies) / $totalChecks) * 100 : 100.0;
    }

    /**
     * Calculate data freshness percentage
     *
     * @param  array<mixed>  $data
     */
    protected function calculateFreshness(array $data, string $freshnessField): float
    {
        if (empty($data)) {
            return 0.0;
        }

        $now = now();
        $totalAge = 0;
        $count = 0;

        foreach ($data as $item) {
            if (! is_array($item) || ! isset($item[$freshnessField])) {
                continue;
            }

            try {
                $timestampValue = $item[$freshnessField];
                if (! is_string($timestampValue) && ! is_int($timestampValue) && ! $timestampValue instanceof \DateTimeInterface) {
                    continue;
                }
                $timestamp = \Carbon\Carbon::parse($timestampValue);
                $ageInDays = $now->diffInDays($timestamp);

                // Freshness decreases with age (100% at 0 days, 0% at 30+ days)
                $freshness = max(0, 100 - ($ageInDays * 3.33));
                $totalAge += $freshness;
                $count++;
            } catch (\Exception $e) {
                // Invalid timestamp, skip
                continue;
            }
        }

        return $count > 0 ? $totalAge / $count : 0.0;
    }

    /**
     * Calculate validation score
     *
     * @param  array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}  $validationResult
     */
    protected function calculateValidationScore(array $validationResult): float
    {
        $baseScore = 100.0;

        // Deduct points for errors (10 points each, max 50 points)
        $errorPenalty = min(count($validationResult['errors']) * 10, 50);

        // Deduct points for warnings (2 points each, max 20 points)
        $warningPenalty = min(count($validationResult['warnings']) * 2, 20);

        // Adjust based on quality metrics
        $qualityBonus = 0.0;

        $qualityChecks = $validationResult['details']['quality_checks'] ?? null;
        if (is_array($qualityChecks) && isset($qualityChecks['metrics']) && is_array($qualityChecks['metrics'])) {
            $metrics = $qualityChecks['metrics'];

            $completeness = is_numeric($metrics['completeness'] ?? null) ? (float) $metrics['completeness'] : 0.0;
            $consistency = is_numeric($metrics['consistency'] ?? null) ? (float) $metrics['consistency'] : 0.0;

            // Bonus for high quality (up to 10 points)
            if ($completeness > 90 && $consistency > 95) {
                $qualityBonus = 10.0;
            } elseif ($completeness > 80 && $consistency > 90) {
                $qualityBonus = 5.0;
            }
        }

        $finalScore = max(0, $baseScore - $errorPenalty - $warningPenalty + $qualityBonus);

        return round($finalScore, 2);
    }

    /**
     * Record validation history
     *
     * @param  array{valid: bool, errors: array<string>, warnings: array<string>, details: array<string, mixed>}  $validationResult
     */
    protected function recordValidationHistory(string $dataType, array $validationResult, float $score): void
    {
        $this->validationHistory[] = [
            'data_type' => $dataType,
            'valid' => $validationResult['valid'],
            'errors_count' => count($validationResult['errors']),
            'warnings_count' => count($validationResult['warnings']),
            'score' => $score,
            'timestamp' => now()->toIso8601String(),
        ];

        // Keep only last 100 entries
        if (count($this->validationHistory) > 100) {
            array_shift($this->validationHistory);
        }
    }

    /**
     * Get validation rules for data type
     *
     * @return array<string, mixed>
     */
    protected function getValidationRules(string $dataType): array
    {
        return $this->validationRules[$dataType] ?? [];
    }

    /**
     * Initialize validation rules
     */
    protected function initializeValidationRules(): void
    {
        $this->validationRules = [
            'umapyoi_characters' => [
                'schema' => [
                    'required_fields' => ['id', 'name'],
                    'field_types' => [
                        'id' => 'integer',
                        'name' => 'string',
                    ],
                    'is_list' => true,
                ],
                'integrity' => [
                    'unique_field' => 'id',
                    'non_null_fields' => ['name'],
                    'check_empty' => true,
                ],
                'business' => [
                    'value_ranges' => [],
                    'enum_fields' => [],
                ],
                'quality' => [
                    'required_fields' => ['id', 'name'],
                ],
            ],
            'umapyoi_support_cards' => [
                'schema' => [
                    'required_fields' => ['id', 'name', 'rarity'],
                    'field_types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'rarity' => 'string',
                    ],
                    'is_list' => true,
                ],
                'integrity' => [
                    'unique_field' => 'id',
                    'non_null_fields' => ['name', 'rarity'],
                    'check_empty' => true,
                ],
                'business' => [
                    'enum_fields' => [
                        'rarity' => ['SSR', 'SR', 'R'],
                    ],
                ],
                'quality' => [
                    'required_fields' => ['id', 'name', 'rarity'],
                ],
            ],
            'umamusumedb_meta' => [
                'schema' => [
                    'required_fields' => ['card_id', 'tier'],
                    'field_types' => [
                        'card_id' => 'integer',
                        'tier' => 'string',
                    ],
                    'is_list' => true,
                ],
                'integrity' => [
                    'unique_field' => 'card_id',
                    'non_null_fields' => ['tier'],
                    'check_empty' => true,
                ],
                'business' => [
                    'enum_fields' => [
                        'tier' => ['SS', 'S', 'A', 'B', 'C'],
                    ],
                ],
                'quality' => [
                    'required_fields' => ['card_id', 'tier'],
                ],
            ],
        ];
    }

    /**
     * Check if array is sequential (list)
     *
     * @param  array<mixed>  $array
     */
    protected function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Get validation history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getValidationHistory(int $limit = 100): array
    {
        return array_slice($this->validationHistory, -$limit);
    }

    /**
     * Get validation statistics
     *
     * @return array{total_validations: int, successful: int, failed: int, avg_score: float}
     */
    public function getValidationStatistics(): array
    {
        $total = count($this->validationHistory);
        $successful = 0;
        $totalScore = 0.0;

        foreach ($this->validationHistory as $entry) {
            if ($entry['valid']) {
                $successful++;
            }

            $score = $entry['score'] ?? 0;
            $totalScore += is_numeric($score) ? (float) $score : 0.0;
        }

        return [
            'total_validations' => $total,
            'successful' => $successful,
            'failed' => $total - $successful,
            'avg_score' => $total > 0 ? round($totalScore / $total, 2) : 0.0,
        ];
    }
}
