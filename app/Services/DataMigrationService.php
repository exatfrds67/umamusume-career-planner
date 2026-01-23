<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SupportCard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Data Migration Service
 *
 * Handles legacy data conversion, batch import processing with progress tracking,
 * data validation and cleaning, and conflict resolution for duplicate data.
 *
 * Requirements: 23.3, 23.4
 */
class DataMigrationService
{
    /**
     * Supported legacy formats
     */
    public const LEGACY_FORMATS = [
        'v1_json' => 'Legacy JSON Format (v1)',
        'v1_csv' => 'Legacy CSV Format (v1)',
        'google_sheets' => 'Google Sheets Export',
        'excel' => 'Excel Export',
        'custom' => 'Custom Format',
    ];

    /**
     * Batch processing status constants
     */
    public const BATCH_STATUS_PENDING = 'pending';

    public const BATCH_STATUS_PROCESSING = 'processing';

    public const BATCH_STATUS_COMPLETED = 'completed';

    public const BATCH_STATUS_FAILED = 'failed';

    public const BATCH_STATUS_CANCELLED = 'cancelled';

    /**
     * Conflict resolution strategies
     */
    public const CONFLICT_STRATEGY_SKIP = 'skip';

    public const CONFLICT_STRATEGY_OVERWRITE = 'overwrite';

    public const CONFLICT_STRATEGY_MERGE = 'merge';

    public const CONFLICT_STRATEGY_RENAME = 'rename';

    /**
     * Stat validation ranges
     */
    private const STAT_MIN = 0;

    private const STAT_MAX = 1200;

    /**
     * Valid aptitude grades
     */
    private const VALID_GRADES = ['G', 'G+', 'F', 'F+', 'E', 'E+', 'D', 'D+', 'C', 'C+', 'B', 'B+', 'A', 'A+', 'S', 'SS'];

    /**
     * Valid scenario types
     */
    private const VALID_SCENARIOS = ['ura_finale', 'unity_cup'];

    /**
     * Cache prefix for batch operations
     */
    private const BATCH_CACHE_PREFIX = 'migration_batch_';

    /**
     * Default batch size for processing
     */
    private const DEFAULT_BATCH_SIZE = 50;

    public function __construct(
        private readonly DataImportService $importService
    ) {}

    /**
     * Detect legacy format from data
     *
     * @param  string  $content  Raw content to analyze
     * @return array{format: string, confidence: float, details: array<string, mixed>}
     */
    public function detectLegacyFormat(): array
        $content = trim($content);

        if (empty($content)) {
            return [
                'format' => 'unknown',
                'confidence' => 0.0,
                'details' => ['error' => 'Empty content provided'],
            ];
        }

        // Try JSON detection
        if (str_starts_with($content, '{') || str_starts_with($content, '[')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) {
                return $this->detectJsonVersion($decoded);
            }
        }

        // Try CSV detection
        $lines = explode("\n", $content);
        if (count($lines) > 1) {
            $firstLineCommas = substr_count($lines[0], ',');
            $firstLineTabs = substr_count($lines[0], "\t");

            if ($firstLineCommas > 2) {
                return $this->detectCsvVersion($lines);
            }

            if ($firstLineTabs > 2) {
                return [
                    'format' => 'google_sheets',
                    'confidence' => 0.8,
                    'details' => [
                        'type' => 'tsv',
                        'columns' => $firstLineTabs + 1,
                        'rows' => count($lines),
                    ],
                ];
            }
        }

        // Check for Excel-like patterns
        if (preg_match('/^[\w\s]+\t[\w\s]+\t/m', $content)) {
            return [
                'format' => 'excel',
                'confidence' => 0.7,
                'details' => ['type' => 'tab_separated'],
            ];
        }

        return [
            'format' => 'custom',
            'confidence' => 0.5,
            'details' => ['type' => 'unrecognized'],
        ];
    }

    /**
     * Detect JSON format version
     *
     * @param  array<int|string, mixed>  $data
     * @return array{format: string, confidence: float, details: array<string, mixed>}
     */
    private function detectJsonVersion(): array
        // Check for v1 format markers
        $hasV1Markers = false;
        $hasCurrentMarkers = false;

        // Normalize to array of records
        $records = isset($data[0]) ? $data : [$data];

        foreach ($records as $record) {
            // V1 format used different field names
            if (isset($record['trainee_name']) || isset($record['stat_speed']) || isset($record['career_run'])) {
                $hasV1Markers = true;
            }

            // Current format uses standardized names
            if (isset($record['name']) || isset($record['speed']) || isset($record['scenario_type'])) {
                $hasCurrentMarkers = true;
            }
        }

        if ($hasV1Markers && ! $hasCurrentMarkers) {
            return [
                'format' => 'v1_json',
                'confidence' => 0.95,
                'details' => [
                    'version' => '1.0',
                    'record_count' => count($records),
                    'detected_fields' => array_keys($records[0] ?? []),
                ],
            ];
        }

        return [
            'format' => 'v1_json',
            'confidence' => $hasV1Markers ? 0.7 : 0.5,
            'details' => [
                'version' => $hasV1Markers ? '1.0' : 'current',
                'record_count' => count($records),
                'detected_fields' => array_keys($records[0] ?? []),
            ],
        ];
    }

    /**
     * Detect CSV format version
     *
     * @param  array<int, string>  $lines
     * @return array{format: string, confidence: float, details: array<string, mixed>}
     */
    private function detectCsvVersion(): array
        $firstLine = $lines[0] ?? '';
        $headers = str_getcsv($firstLine);
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim(\is_string($h) ? $h : '')), $headers);

        // V1 CSV format markers
        $v1Headers = ['trainee_name', 'stat_speed', 'stat_stamina', 'career_run', 'run_number'];
        $v1Matches = count(array_intersect($normalizedHeaders, $v1Headers));

        // Current format headers
        $currentHeaders = ['name', 'speed', 'stamina', 'power', 'guts', 'wit', 'scenario_type'];
        $currentMatches = count(array_intersect($normalizedHeaders, $currentHeaders));

        if ($v1Matches > $currentMatches) {
            return [
                'format' => 'v1_csv',
                'confidence' => min(0.95, 0.5 + ($v1Matches * 0.1)),
                'details' => [
                    'version' => '1.0',
                    'headers' => $headers,
                    'row_count' => count($lines) - 1,
                    'v1_matches' => $v1Matches,
                ],
            ];
        }

        return [
            'format' => 'v1_csv',
            'confidence' => 0.6,
            'details' => [
                'version' => 'current',
                'headers' => $headers,
                'row_count' => count($lines) - 1,
            ],
        ];
    }

    /**
     * Convert legacy data format to current format
     *
     * @param  string  $content  Raw legacy content
     * @param  string  $sourceFormat  Source format identifier
     * @param  string  $targetType  Target data type (character, career, etc.)
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>, warnings: array<int, string>}
     */
    public function convertLegacyFormat(): array
        $errors = [];
        $warnings = [];
        $convertedData = [];

        try {
            $rawData = match ($sourceFormat) {
                'v1_json' => $this->parseV1Json($content),
                'v1_csv' => $this->parseV1Csv($content),
                'google_sheets' => $this->parseGoogleSheets($content),
                'excel' => $this->parseExcel($content),
                'custom' => $this->parseCustomFormat($content, $targetType),
                default => throw new \InvalidArgumentException("Unsupported format: {$sourceFormat}"),
            };

            if (! $rawData['success']) {
                return [
                    'success' => false,
                    'data' => [],
                    'errors' => $rawData['errors'],
                    'warnings' => [],
                ];
            }

            // Transform each record to current format
            foreach ($rawData['data'] as $index => $record) {
                $transformed = $this->transformRecord($record, $sourceFormat, $targetType);

                if ($transformed['success']) {
                    $convertedData[] = $transformed['data'];
                    if (! empty($transformed['warnings'])) {
                        $warnings = array_merge($warnings, array_map(
                            fn ($w) => "Record {$index}: {$w}",
                            $transformed['warnings']
                        ));
                    }
                } else {
                    $errors[] = "Record {$index}: ".implode(', ', $transformed['errors']);
                }
            }

            return [
                'success' => count($convertedData) > 0,
                'data' => $convertedData,
                'errors' => $errors,
                'warnings' => $warnings,
                'statistics' => [
                    'total_records' => count($rawData['data']),
                    'converted_records' => count($convertedData),
                    'failed_records' => count($rawData['data']) - count($convertedData),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[DataMigrationService] Legacy format conversion failed', [
                'source_format' => $sourceFormat,
                'target_type' => $targetType,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'errors' => [$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    /**
     * Parse V1 JSON format
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseV1Json(): array
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! \is_array($data)) {
            return [
                'success' => false,
                'data' => [],
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
            ];
        }

        // Normalize to array of records
        if (! isset($data[0]) && ! empty($data)) {
            $data = [$data];
        }

        /** @var array<int, array<string, mixed>> $data */
        return [
            'success' => true,
            'data' => $data,
            'errors' => [],
        ];
    }

    /**
     * Parse V1 CSV format
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseV1Csv(): array
        $lines = array_filter(explode("\n", $content), fn ($line) => trim($line) !== '');

        if (count($lines) < 2) {
            return [
                'success' => false,
                'data' => [],
                'errors' => ['CSV must have at least a header row and one data row'],
            ];
        }

        $firstLine = array_shift($lines);
        $headers = str_getcsv(\is_string($firstLine) ? $firstLine : '');
        $headers = array_map(fn ($h) => strtolower(trim(\is_string($h) ? $h : '')), $headers);

        $data = [];
        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) === count($headers)) {
                $combined = array_combine($headers, $values);
                if (\is_array($combined)) {
                    $data[] = $combined;
                }
            }
        }

        return [
            'success' => true,
            'data' => $data,
            'errors' => [],
        ];
    }

    /**
     * Parse Google Sheets export (TSV format)
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseGoogleSheets(): array
        // Convert tabs to commas and use CSV parser
        $csvContent = str_replace("\t", ',', $content);

        return $this->parseV1Csv($csvContent);
    }

    /**
     * Parse Excel export
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseExcel(): array
        // Excel exports are typically tab-separated
        return $this->parseGoogleSheets($content);
    }

    /**
     * Parse custom format with intelligent field detection
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseCustomFormat(): array
        // Use the import service's intelligent parsing
        $result = $this->importService->parseText($content, $targetType);

        return [
            'success' => $result['success'],
            'data' => $result['data'],
            'errors' => $result['errors'],
        ];
    }

    /**
     * Transform a record from legacy format to current format
     *
     * @param  array<string, mixed>  $record
     * @return array{success: bool, data: array<string, mixed>, errors: array<int, string>, warnings: array<int, string>}
     */
    private function transformRecord(): array
        $transformed = [];
        $warnings = [];
        $errors = [];

        // Field mapping for V1 formats
        $fieldMappings = $this->getFieldMappings($sourceFormat, $targetType);

        foreach ($fieldMappings as $oldField => $newField) {
            if (isset($record[$oldField])) {
                $value = $record[$oldField];

                // Apply transformations
                $transformedValue = $this->transformFieldValue($oldField, $newField, $value, $targetType);

                if ($transformedValue['success']) {
                    $transformed[$newField] = $transformedValue['value'];
                    if (! empty($transformedValue['warning'])) {
                        $warnings[] = $transformedValue['warning'];
                    }
                } else {
                    $errors[] = $transformedValue['error'];
                }
            }
        }

        // Copy any fields that don't need transformation
        foreach ($record as $key => $value) {
            $normalizedKey = $this->normalizeFieldName($key);
            if (! isset($transformed[$normalizedKey]) && ! array_key_exists($key, $fieldMappings)) {
                $transformed[$normalizedKey] = $value;
            }
        }

        // Validate the transformed record
        $validation = $this->importService->validateRecord($transformed, $targetType);

        if (! $validation['valid']) {
            return [
                'success' => false,
                'data' => [],
                'errors' => array_merge($errors, $validation['errors']),
                'warnings' => $warnings,
            ];
        }

        return [
            'success' => true,
            'data' => $transformed,
            'errors' => [],
            'warnings' => $warnings,
        ];
    }

    /**
     * Get field mappings for legacy format conversion
     *
     * @return array<string, string>
     */
    private function getFieldMappings(): array
        $baseMappings = [
            // V1 character field mappings
            'trainee_name' => 'name',
            'stat_speed' => 'speed',
            'stat_stamina' => 'stamina',
            'stat_power' => 'power',
            'stat_guts' => 'guts',
            'stat_wit' => 'wit',
            'stat_intelligence' => 'wit',
            'scenario' => 'scenario_type',
            'mode' => 'scenario_type',
            'energy' => 'energy_level',
            'mood' => 'mood_status',
            'condition' => 'mood_status',

            // V1 career field mappings
            'career_run' => 'career_name',
            'run_name' => 'career_name',
            'run_number' => 'current_turn',
            'turn' => 'current_turn',
            'final_speed' => 'final_speed',
            'final_stamina' => 'final_stamina',
            'final_power' => 'final_power',
            'final_guts' => 'final_guts',
            'final_wit' => 'final_wit',

            // V1 training session mappings
            'training' => 'training_type',
            'type' => 'training_type',
            'speed_gain' => 'speed_gain',
            'stamina_gain' => 'stamina_gain',
            'power_gain' => 'power_gain',
            'guts_gain' => 'guts_gain',
            'wit_gain' => 'wit_gain',
            'sp_gained' => 'sp_gain',

            // V1 skill mappings
            'skill_name' => 'name',
            'skill_type' => 'skill_type',
            'sp_cost' => 'base_sp_cost',
            'cost' => 'base_sp_cost',

            // V1 support card mappings
            'card_name' => 'name',
            'card_rarity' => 'rarity',
            'card_type' => 'specialization',
            'lb_level' => 'limit_break_level',
            'limit_break' => 'limit_break_level',
        ];

        return $baseMappings;
    }

    /**
     * Transform a field value during conversion
     *
     * @return array{success: bool, value: mixed, warning: string|null, error?: string}
     */
    private function transformFieldValue(): array
        // Handle stat values
        if (in_array($newField, ['speed', 'stamina', 'power', 'guts', 'wit', 'energy_level'])) {
            if (! is_numeric($value)) {
                $valueStr = \is_string($value) ? $value : 'non-numeric';

                return [
                    'success' => false,
                    'value' => null,
                    'warning' => null,
                    'error' => "Invalid numeric value for {$newField}: {$valueStr}",
                ];
            }

            $numValue = (int) $value;

            // Clamp stats to valid range
            if ($newField !== 'energy_level') {
                if ($numValue < self::STAT_MIN || $numValue > self::STAT_MAX) {
                    $clampedValue = max(self::STAT_MIN, min(self::STAT_MAX, $numValue));

                    return [
                        'success' => true,
                        'value' => $clampedValue,
                        'warning' => "Stat {$newField} clamped from {$numValue} to {$clampedValue}",
                    ];
                }
            } else {
                // Energy level is 0-100
                if ($numValue < 0 || $numValue > 100) {
                    $clampedValue = max(0, min(100, $numValue));

                    return [
                        'success' => true,
                        'value' => $clampedValue,
                        'warning' => "Energy level clamped from {$numValue} to {$clampedValue}",
                    ];
                }
            }

            return ['success' => true, 'value' => $numValue, 'warning' => null];
        }

        // Handle scenario type normalization
        if ($newField === 'scenario_type') {
            $normalized = $this->normalizeScenarioType($value);
            if ($normalized === null) {
                $valueStr = \is_string($value) ? $value : 'invalid';

                return [
                    'success' => false,
                    'value' => null,
                    'warning' => null,
                    'error' => "Invalid scenario type: {$valueStr}",
                ];
            }

            return ['success' => true, 'value' => $normalized, 'warning' => null];
        }

        // Handle mood status normalization
        if ($newField === 'mood_status') {
            $normalized = $this->normalizeMoodStatus($value);

            return ['success' => true, 'value' => $normalized, 'warning' => null];
        }

        // Handle rarity normalization
        if ($newField === 'rarity' && $targetType === 'support_card') {
            $valueStr = \is_string($value) ? $value : '';
            $normalized = strtoupper(trim($valueStr));
            if (! in_array($normalized, ['SSR', 'SR', 'R'])) {
                return [
                    'success' => false,
                    'value' => null,
                    'warning' => null,
                    'error' => "Invalid rarity: {$valueStr}",
                ];
            }

            return ['success' => true, 'value' => $normalized, 'warning' => null];
        }

        // Default: return value as-is
        return ['success' => true, 'value' => $value, 'warning' => null];
    }

    /**
     * Normalize scenario type value
     */
    private function normalizeScenarioType(mixed $value): ?string
    {
        $value = strtolower(trim((string) $value));

        $mappings = [
            'ura' => 'ura_finale',
            'ura_finale' => 'ura_finale',
            'ura finale' => 'ura_finale',
            'unity' => 'unity_cup',
            'unity_cup' => 'unity_cup',
            'unity cup' => 'unity_cup',
            'team' => 'unity_cup',
        ];

        return $mappings[$value] ?? null;
    }

    /**
     * Normalize mood status value
     */
    private function normalizeMoodStatus(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        $mappings = [
            'excellent' => 'great',
            'very good' => 'great',
            'great' => 'great',
            'good' => 'good',
            'ok' => 'normal',
            'normal' => 'normal',
            'average' => 'normal',
            'bad' => 'bad',
            'poor' => 'bad',
            'awful' => 'awful',
            'terrible' => 'awful',
        ];

        return $mappings[$value] ?? 'normal';
    }

    /**
     * Normalize field name to snake_case
     */
    private function normalizeFieldName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^a-zA-Z0-9\s_]/', '', $name);
        if ($name === null) {
            $name = '';
        }

        return Str::snake($name);
    }

    /**
     * Start a batch import process
     *
     * @param  array<int, array<string, mixed>>  $data  Data to import
     * @param  string  $importType  Type of data being imported
     * @param  int  $userId  User ID
     * @param  array<string, mixed>  $options  Batch options
     * @return array{batch_id: string, status: string, total_records: int}
     */
    public function startBatchImport(): array
        $batchId = Str::uuid()->toString();
        $batchSize = $options['batch_size'] ?? self::DEFAULT_BATCH_SIZE;
        $conflictStrategy = $options['conflict_strategy'] ?? self::CONFLICT_STRATEGY_SKIP;

        $batchData = [
            'batch_id' => $batchId,
            'user_id' => $userId,
            'import_type' => $importType,
            'status' => self::BATCH_STATUS_PENDING,
            'total_records' => count($data),
            'processed_records' => 0,
            'successful_records' => 0,
            'failed_records' => 0,
            'skipped_records' => 0,
            'conflict_strategy' => $conflictStrategy,
            'batch_size' => $batchSize,
            'data' => $data,
            'errors' => [],
            'conflicts' => [],
            'imported_ids' => [],
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'completed_at' => null,
        ];

        // Store batch data in cache
        Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchData, now()->addHours(24));

        Log::info('[DataMigrationService] Batch import started', [
            'batch_id' => $batchId,
            'user_id' => $userId,
            'import_type' => $importType,
            'total_records' => count($data),
        ]);

        return [
            'batch_id' => $batchId,
            'status' => self::BATCH_STATUS_PENDING,
            'total_records' => count($data),
        ];
    }

    /**
     * Process a batch import
     *
     * @param  string  $batchId  Batch ID
     * @return array{success: bool, status: string, progress: array<string, mixed>, error?: string}
     */
    public function processBatch(): array
        /** @var array<string, mixed>|null $batchData */
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! is_array($batchData)) {
            return [
                'success' => false,
                'status' => 'not_found',
                'progress' => [],
                'error' => 'Batch not found',
            ];
        }

        if ($batchData['status'] === self::BATCH_STATUS_COMPLETED) {
            return [
                'success' => true,
                'status' => self::BATCH_STATUS_COMPLETED,
                'progress' => $this->getProgressData($batchData),
            ];
        }

        if ($batchData['status'] === self::BATCH_STATUS_CANCELLED) {
            return [
                'success' => false,
                'status' => self::BATCH_STATUS_CANCELLED,
                'progress' => $this->getProgressData($batchData),
            ];
        }

        // Update status to processing
        $batchData['status'] = self::BATCH_STATUS_PROCESSING;
        $batchData['updated_at'] = now()->toIso8601String();
        Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchData, now()->addHours(24));

        try {
            $result = $this->processNextBatch($batchData);
            $batchDataResult = (is_array($result) && isset($result['batch_data']) ? $result['batch_data'] : null);
            if (! is_array($batchDataResult)) {
                $batchDataResult = $batchData;
            }

            Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchDataResult, now()->addHours(24));

            return [
                'success' => true,
                'status' => $batchDataResult['status'] ?? self::BATCH_STATUS_PROCESSING,
                'progress' => $this->getProgressData($batchDataResult),
            ];
        } catch (\Exception $e) {
            $batchData['status'] = self::BATCH_STATUS_FAILED;
            $batchData['errors'][] = $e->getMessage();
            $batchData['updated_at'] = now()->toIso8601String();
            Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchData, now()->addHours(24));

            Log::error('[DataMigrationService] Batch processing failed', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => self::BATCH_STATUS_FAILED,
                'progress' => $this->getProgressData($batchData),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process the next batch of records
     *
     * @param  array<string, mixed>  $batchData
     * @return array<string, mixed>
     */
    private function processNextBatch(): array
        $startIndex = $batchData['processed_records'];
        $endIndex = min($startIndex + $batchData['batch_size'], $batchData['total_records']);
        $recordsToProcess = array_slice($batchData['data'], $startIndex, $endIndex - $startIndex);

        DB::beginTransaction();

        try {
            foreach ($recordsToProcess as $index => $record) {
                $recordIndex = $startIndex + $index;

                // Check for conflicts
                $conflict = $this->detectConflict($record, $batchData['import_type'], $batchData['user_id']);

                if ($conflict['has_conflict']) {
                    $resolution = $this->resolveConflict(
                        $record,
                        $conflict,
                        $batchData['conflict_strategy'],
                        $batchData['import_type'],
                        $batchData['user_id']
                    );

                    if ($resolution['action'] === 'skip') {
                        $batchData['skipped_records']++;
                        $batchData['conflicts'][] = [
                            'index' => $recordIndex,
                            'record' => $record,
                            'existing' => $conflict['existing_record'],
                            'resolution' => 'skipped',
                        ];
                    } elseif ($resolution['success']) {
                        $batchData['successful_records']++;
                        $batchData['imported_ids'][] = $resolution['id'];
                    } else {
                        $batchData['failed_records']++;
                        $batchData['errors'][] = "Record {$recordIndex}: ".($resolution['error'] ?? 'Unknown error');
                    }
                } else {
                    // No conflict, import directly
                    $importResult = $this->importService->executeImport(
                        [$record],
                        $batchData['import_type'],
                        $batchData['user_id']
                    );

                    if ($importResult['success'] && $importResult['imported'] > 0) {
                        $batchData['successful_records']++;
                        $batchData['imported_ids'] = array_merge(
                            $batchData['imported_ids'],
                            $importResult['imported_ids']
                        );
                    } else {
                        $batchData['failed_records']++;
                        if (! empty($importResult['errors'])) {
                            $batchData['errors'][] = "Record {$recordIndex}: ".implode(', ', $importResult['errors']);
                        }
                    }
                }

                $batchData['processed_records']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Update status
        if ($batchData['processed_records'] >= $batchData['total_records']) {
            $batchData['status'] = self::BATCH_STATUS_COMPLETED;
            $batchData['completed_at'] = now()->toIso8601String();
        }

        $batchData['updated_at'] = now()->toIso8601String();

        return ['batch_data' => $batchData];
    }

    /**
     * Get batch import status
     *
     * @param  string  $batchId  Batch ID
     * @return array<string, mixed>|null
     */
    public function getBatchStatus(string $batchId): ?array
    {
        /** @var array<string, mixed>|null $batchData */
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! \is_array($batchData)) {
            return null;
        }

        return [
            'batch_id' => $batchData['batch_id'] ?? '',
            'status' => $batchData['status'] ?? '',
            'import_type' => $batchData['import_type'] ?? '',
            'progress' => $this->getProgressData($batchData),
            'errors' => array_slice($batchData['errors'] ?? [], -10), // Last 10 errors
            'conflicts' => array_slice($batchData['conflicts'] ?? [], -10), // Last 10 conflicts
            'started_at' => $batchData['started_at'] ?? '',
            'updated_at' => $batchData['updated_at'],
            'completed_at' => $batchData['completed_at'],
        ];
    }

    /**
     * Get progress data from batch
     */
    private function getProgressData(): array
        $total = $batchData['total_records'];
        $processed = $batchData['processed_records'];

        return [
            'total_records' => $total,
            'processed_records' => $processed,
            'successful_records' => $batchData['successful_records'],
            'failed_records' => $batchData['failed_records'],
            'skipped_records' => $batchData['skipped_records'],
            'percentage' => $total > 0 ? round(($processed / $total) * 100, 2) : 0,
            'remaining_records' => $total - $processed,
        ];
    }

    /**
     * Cancel a batch import
     *
     * @param  string  $batchId  Batch ID
     */
    public function cancelBatch(string $batchId): bool
    {
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! $batchData) {
            return false;
        }

        if (in_array($batchData['status'], [self::BATCH_STATUS_COMPLETED, self::BATCH_STATUS_CANCELLED])) {
            return false;
        }

        $batchData['status'] = self::BATCH_STATUS_CANCELLED;
        $batchData['updated_at'] = now()->toIso8601String();
        Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchData, now()->addHours(24));

        Log::info('[DataMigrationService] Batch import cancelled', [
            'batch_id' => $batchId,
            'processed_records' => $batchData['processed_records'],
        ]);

        return true;
    }

    /**
     * Detect conflicts with existing data
     *
     * @param  array  $record  Record to check
     * @param  string  $importType  Type of data
     * @param  int  $userId  User ID
     * @return array{has_conflict: bool, existing_record: array|null, conflict_type: string|null}
     */
    public function detectConflict(): array
        $existingRecord = null;
        $conflictType = null;

        switch ($importType) {
            case 'character':
                if (isset($record['name'])) {
                    $existing = Character::query()->where('user_id', $userId)
                        ->where('name', $record['name'])
                        ->first();

                    if ($existing) {
                        $existingRecord = $existing->toArray();
                        $conflictType = 'duplicate_name';
                    }
                }
                break;

            case 'career':
                if (isset($record['career_name'])) {
                    $existing = Career::query()->where('user_id', $userId)
                        ->where('career_name', $record['career_name'])
                        ->first();

                    if ($existing) {
                        $existingRecord = $existing->toArray();
                        $conflictType = 'duplicate_name';
                    }
                }
                break;

            case 'skill':
                if (isset($record['name'])) {
                    $existing = Skill::query()->where('name', $record['name'])->first();

                    if ($existing) {
                        $existingRecord = $existing->toArray();
                        $conflictType = 'duplicate_skill';
                    }
                }
                break;

            case 'support_card':
                if (isset($record['name'])) {
                    $existing = SupportCard::query()->where('name', $record['name'])->first();

                    if ($existing) {
                        $existingRecord = $existing->toArray();
                        $conflictType = 'duplicate_card';
                    }
                }
                break;
        }

        return [
            'has_conflict' => $existingRecord !== null,
            'existing_record' => $existingRecord,
            'conflict_type' => $conflictType,
        ];
    }

    /**
     * Resolve a conflict based on strategy
     *
     * @param  array  $record  New record
     * @param  array  $conflict  Conflict details
     * @param  string  $strategy  Resolution strategy
     * @param  string  $importType  Type of data
     * @param  int  $userId  User ID
     * @return array{success: bool, action: string, id: int|null, error: string|null}
     */
    public function resolveConflict(): array
        switch ($strategy) {
            case self::CONFLICT_STRATEGY_SKIP:
                return [
                    'success' => true,
                    'action' => 'skip',
                    'id' => null,
                    'error' => null,
                ];

            case self::CONFLICT_STRATEGY_OVERWRITE:
                return $this->overwriteRecord($record, $conflict['existing_record'], $importType, $userId);

            case self::CONFLICT_STRATEGY_MERGE:
                return $this->mergeRecords($record, $conflict['existing_record'], $importType, $userId);

            case self::CONFLICT_STRATEGY_RENAME:
                return $this->renameAndImport($record, $importType, $userId);

            default:
                return [
                    'success' => false,
                    'action' => 'error',
                    'id' => null,
                    'error' => "Unknown conflict strategy: {$strategy}",
                ];
        }
    }

    /**
     * Overwrite existing record with new data
     */
    /**
     * @return array{success: bool, action: string, id: int|null, error: string|null}
     */
    private function overwriteRecord(): array
        try {
            $model = match ($importType) {
                'character' => Character::query()->find($existing['id']),
                'career' => Career::query()->find($existing['id']),
                'skill' => Skill::query()->find($existing['id']),
                'support_card' => SupportCard::query()->find($existing['id']),
                default => null,
            };

            if (! $model) {
                return [
                    'success' => false,
                    'action' => 'overwrite',
                    'id' => null,
                    'error' => 'Existing record not found',
                ];
            }

            // Update with new data
            $model->fill($record);
            $model->save();

            return [
                'success' => true,
                'action' => 'overwrite',
                'id' => $model->id,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'action' => 'overwrite',
                'id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Merge new record with existing record
     */
    /**
     * @return array{success: bool, action: string, id: int|null, error: string|null}
     */
    private function mergeRecords(): array
        try {
            $model = match ($importType) {
                'character' => Character::query()->find($existing['id']),
                'career' => Career::query()->find($existing['id']),
                'skill' => Skill::query()->find($existing['id']),
                'support_card' => SupportCard::query()->find($existing['id']),
                default => null,
            };

            if (! $model) {
                return [
                    'success' => false,
                    'action' => 'merge',
                    'id' => null,
                    'error' => 'Existing record not found',
                ];
            }

            // Merge: only update fields that are not null in new record
            // and are either null or different in existing record
            foreach ($record as $key => $value) {
                if ($value !== null && $value !== '') {
                    // For stats, take the higher value
                    if (in_array($key, ['speed', 'stamina', 'power', 'guts', 'wit'])) {
                        $existingValue = $model->$key ?? 0;
                        $model->$key = max($existingValue, (int) $value);
                    } else {
                        $model->$key = $value;
                    }
                }
            }

            $model->save();

            return [
                'success' => true,
                'action' => 'merge',
                'id' => $model->id,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'action' => 'merge',
                'id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Rename record and import as new
     */
    /**
     * @return array{success: bool, action: string, id: int|null, error: string|null}
     */
    private function renameAndImport(): array
        // Add suffix to name to make it unique
        $nameField = match ($importType) {
            'character', 'skill', 'support_card' => 'name',
            'career' => 'career_name',
            default => 'name',
        };

        if (isset($record[$nameField])) {
            $originalName = $record[$nameField];
            $counter = 1;

            do {
                $record[$nameField] = "{$originalName} ({$counter})";
                $conflict = $this->detectConflict($record, $importType, $userId);
                $counter = ($counter ?? 0) + 1;
            } while ($conflict['has_conflict'] && $counter < 100);

            if ($counter >= 100) {
                return [
                    'success' => false,
                    'action' => 'rename',
                    'id' => null,
                    'error' => 'Could not find unique name after 100 attempts',
                ];
            }
        }

        // Import with new name
        $result = $this->importService->executeImport([$record], $importType, $userId);

        return [
            'success' => $result['success'] && $result['imported'] > 0,
            'action' => 'rename',
            'id' => $result['imported_ids'][0] ?? null,
            'error' => $result['success'] ? null : implode(', ', $result['errors']),
        ];
    }

    /**
     * Get pending conflicts for manual resolution
     *
     * @param  string  $batchId  Batch ID
     */
    public function getPendingConflicts(): array
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! is_array($batchData)) {
            return [];
        }

        $conflicts = $batchData['conflicts'] ?? [];
        if (! is_array($conflicts)) {
            return [];
        }

        return array_filter($conflicts, fn ($c) => $c['resolution'] === 'pending');
    }

    /**
     * Resolve a specific conflict manually
     *
     * @param  string  $batchId  Batch ID
     * @param  int  $conflictIndex  Index of the conflict
     * @param  string  $resolution  Resolution action (skip, overwrite, merge, rename)
     * @return array{success: bool, message: string}
     */
    public function resolveConflictManually(): array
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! is_array($batchData)) {
            return ['success' => false, 'message' => 'Batch not found'];
        }

        if (! isset($batchData['conflicts'][$conflictIndex])) {
            return ['success' => false, 'message' => 'Conflict not found'];
        }

        $conflict = $batchData['conflicts'][$conflictIndex];
        $record = $conflict['record'];

        $result = $this->resolveConflict(
            $record,
            ['existing_record' => $conflict['existing']],
            $resolution,
            $batchData['import_type'],
            $batchData['user_id']
        );

        // Update conflict status
        $batchData['conflicts'][$conflictIndex]['resolution'] = $result['action'];
        $batchData['conflicts'][$conflictIndex]['resolved_at'] = now()->toIso8601String();

        if ($result['success'] && $result['action'] !== 'skip') {
            $batchData['successful_records']++;
            if ($result['id']) {
                $batchData['imported_ids'][] = $result['id'];
            }
        }

        Cache::put(self::BATCH_CACHE_PREFIX.$batchId, $batchData, now()->addHours(24));

        return [
            'success' => $result['success'],
            'message' => $result['success']
                ? "Conflict resolved with action: {$result['action']}"
                : "Failed to resolve conflict: {$result['error']}",
        ];
    }

    /**
     * Validate data before import with detailed error reporting
     *
     * @param  array  $data  Data to validate
     * @param  string  $importType  Type of data
     * @return array{valid: bool, records: array, summary: array}
     */
    public function validateData(): array
        $validRecords = [];
        $invalidRecords = [];
        $warnings = [];

        foreach ($data as $index => $record) {
            // Clean the record first to normalize values
            $cleaned = $this->cleanRecord($record, $importType);

            // Then validate the cleaned record
            $validation = $this->importService->validateRecord($cleaned['data'], $importType);

            if ($validation['valid']) {
                $validRecords[] = [
                    'index' => $index,
                    'original' => $record,
                    'cleaned' => $cleaned['data'],
                    'warnings' => $cleaned['warnings'],
                ];

                if (! empty($cleaned['warnings'])) {
                    $warnings = array_merge($warnings, array_map(
                        fn ($w) => "Record {$index}: {$w}",
                        $cleaned['warnings']
                    ));
                }
            } else {
                $invalidRecords[] = [
                    'index' => $index,
                    'record' => $record,
                    'errors' => $validation['errors'],
                ];
            }
        }

        return [
            'valid' => count($invalidRecords) === 0,
            'records' => [
                'valid' => $validRecords,
                'invalid' => $invalidRecords,
            ],
            'summary' => [
                'total' => count($data),
                'valid_count' => count($validRecords),
                'invalid_count' => count($invalidRecords),
                'warning_count' => count($warnings),
            ],
            'warnings' => $warnings,
        ];
    }

    /**
     * Clean a record by normalizing and sanitizing values
     *
     * @param  array  $record  Record to clean
     * @param  string  $importType  Type of data
     * @return array{data: array, warnings: array}
     */
    public function cleanRecord(): array
        $cleaned = [];
        $warnings = [];

        foreach ($record as $key => $value) {
            $normalizedKey = $this->normalizeFieldName($key);

            // Trim string values
            if (is_string($value)) {
                $value = trim($value);
            }

            // Handle empty strings
            if ($value === '') {
                $value = null;
            }

            // Clean stat values
            if (in_array($normalizedKey, ['speed', 'stamina', 'power', 'guts', 'wit'])) {
                if ($value !== null) {
                    $numValue = (int) $value;
                    if ($numValue < self::STAT_MIN) {
                        $warnings[] = "Stat {$normalizedKey} was negative ({$numValue}), set to 0";
                        $numValue = self::STAT_MIN;
                    } elseif ($numValue > self::STAT_MAX) {
                        $warnings[] = "Stat {$normalizedKey} exceeded max ({$numValue}), capped at ".self::STAT_MAX;
                        $numValue = self::STAT_MAX;
                    }
                    $value = $numValue;
                }
            }

            // Clean energy level
            if ($normalizedKey === 'energy_level' && $value !== null) {
                $numValue = (int) $value;
                if ($numValue < 0 || $numValue > 100) {
                    $warnings[] = "Energy level was out of range ({$numValue}), clamped to 0-100";
                    $value = max(0, min(100, $numValue));
                } else {
                    $value = $numValue;
                }
            }

            // Normalize scenario type
            if ($normalizedKey === 'scenario_type' && $value !== null) {
                $normalized = $this->normalizeScenarioType($value);
                if ($normalized === null) {
                    $warnings[] = "Invalid scenario type '{$value}', defaulting to 'ura_finale'";
                    $value = 'ura_finale';
                } else {
                    $value = $normalized;
                }
            }

            // Normalize mood status
            if ($normalizedKey === 'mood_status' && $value !== null) {
                $value = $this->normalizeMoodStatus($value);
            }

            $cleaned[$normalizedKey] = $value;
        }

        return [
            'data' => $cleaned,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get transformation rules for a specific import type
     *
     * @param  string  $importType  Type of data
     */
    public function getTransformationRules(): array
        $baseRules = [
            'field_mappings' => $this->getFieldMappings('v1_json', $importType),
            'stat_range' => [
                'min' => self::STAT_MIN,
                'max' => self::STAT_MAX,
            ],
            'valid_grades' => self::VALID_GRADES,
            'valid_scenarios' => self::VALID_SCENARIOS,
        ];

        $typeSpecificRules = match ($importType) {
            'character' => [
                'required_fields' => ['name'],
                'optional_fields' => ['speed', 'stamina', 'power', 'guts', 'wit', 'scenario_type', 'energy_level', 'mood_status'],
                'stat_fields' => ['speed', 'stamina', 'power', 'guts', 'wit'],
            ],
            'career' => [
                'required_fields' => [],
                'optional_fields' => ['career_name', 'scenario_type', 'current_turn', 'status', 'final_speed', 'final_stamina', 'final_power', 'final_guts', 'final_wit'],
                'stat_fields' => ['final_speed', 'final_stamina', 'final_power', 'final_guts', 'final_wit'],
            ],
            'training_session' => [
                'required_fields' => ['turn_number', 'training_type'],
                'optional_fields' => ['speed_gain', 'stamina_gain', 'power_gain', 'guts_gain', 'wit_gain', 'sp_gain', 'energy_cost'],
                'stat_fields' => [],
            ],
            'skill' => [
                'required_fields' => ['name'],
                'optional_fields' => ['skill_type', 'rarity', 'base_sp_cost'],
                'stat_fields' => [],
            ],
            'support_card' => [
                'required_fields' => ['name'],
                'optional_fields' => ['rarity', 'specialization', 'limit_break_level'],
                'stat_fields' => [],
            ],
            default => [
                'required_fields' => [],
                'optional_fields' => [],
                'stat_fields' => [],
            ],
        };

        return array_merge($baseRules, $typeSpecificRules);
    }

    /**
     * Generate migration summary report
     *
     * @param  string  $batchId  Batch ID
     */
    public function generateMigrationReport(): array
        $batchData = Cache::get(self::BATCH_CACHE_PREFIX.$batchId);

        if (! $batchData) {
            return ['error' => 'Batch not found'];
        }

        return [
            'batch_id' => $batchId,
            'import_type' => $batchData['import_type'],
            'status' => $batchData['status'],
            'summary' => [
                'total_records' => $batchData['total_records'],
                'successful' => $batchData['successful_records'],
                'failed' => $batchData['failed_records'],
                'skipped' => $batchData['skipped_records'],
                'success_rate' => $batchData['total_records'] > 0
                    ? round(($batchData['successful_records'] / $batchData['total_records']) * 100, 2)
                    : 0,
            ],
            'timing' => [
                'started_at' => $batchData['started_at'],
                'completed_at' => $batchData['completed_at'],
                'duration_seconds' => $batchData['completed_at']
                    ? now()->parse($batchData['completed_at'])->diffInSeconds(now()->parse($batchData['started_at']))
                    : null,
            ],
            'errors' => $batchData['errors'],
            'conflicts_resolved' => count(array_filter($batchData['conflicts'], fn ($c) => $c['resolution'] !== 'pending')),
            'conflicts_pending' => count(array_filter($batchData['conflicts'], fn ($c) => $c['resolution'] === 'pending')),
            'imported_ids' => $batchData['imported_ids'],
        ];
    }
}
