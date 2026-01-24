<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Data Import Service
 *
 * Handles flexible data import with intelligent parsing for various formats.
 * Supports copy/paste text, CSV, and JSON imports with validation and preview.
 *
 * Requirements: 23.1, 23.2
 */
class DataImportService
{
    /**
     * Supported import types
     */
    public const IMPORT_TYPES = [
        'character' => 'Character Data',
        'career' => 'Career Data',
        'training_session' => 'Training Sessions',
        'skill' => 'Skills',
        'support_card' => 'Support Cards',
    ];

    /**
     * Supported file formats
     */
    public const SUPPORTED_FORMATS = ['csv', 'json', 'txt'];

    /**
     * Stat validation ranges
     */
    private const STAT_MIN = 0;

    private const STAT_MAX = 1200;

    /**
     * Valid scenario types
     */
    private const VALID_SCENARIOS = ['ura_finale', 'unity_cup'];

    /**
     * Parse text input with intelligent format detection
     *
     * @param  string  $text  Raw text input
     * @param  string  $importType  Type of data being imported
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>}
     */
    public function parseText(string $text, string $importType): array
    {
        $text = trim($text);

        if (empty($text)) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'unknown',
                'errors' => ['Input text is empty'],
            ];
        }

        // Try to detect format
        $format = $this->detectFormat($text);

        $result = match ($format) {
            'json' => $this->parseJson($text, $importType),
            'csv' => $this->parseCsv($text, $importType),
            'tsv' => $this->parseTsv($text, $importType),
            'key_value' => $this->parseKeyValue($text, $importType),
            default => $this->parseStructuredText($text, $importType),
        };

        return [
            'success' => (bool) ($result['success'] ?? false),
            'data' => isset($result['data']) && is_array($result['data']) ? $result['data'] : [],
            'format' => (string) ($result['format'] ?? $format),
            'errors' => isset($result['errors']) && is_array($result['errors']) ? $result['errors'] : [],
        ];
    }

    /**
     * Detect the format of input text
     */
    private function detectFormat(string $text): string
    {
        $text = trim($text);

        // Check for JSON
        if (str_starts_with($text, '{') || str_starts_with($text, '[')) {
            json_decode($text);
            if (json_last_error() === JSON_ERROR_NONE) {
                return 'json';
            }
        }

        // Check for CSV (comma-separated with consistent columns)
        $lines = explode("\n", $text);
        if (count($lines) > 1) {
            $firstLineCommas = substr_count($lines[0], ',');
            $secondLineCommas = substr_count($lines[1], ',');
            if ($firstLineCommas > 0 && $firstLineCommas === $secondLineCommas) {
                return 'csv';
            }
        }

        // Check for TSV (tab-separated)
        if (str_contains($text, "\t")) {
            $firstLineTabs = substr_count($lines[0], "\t");
            if ($firstLineTabs > 0 && count($lines) > 1) {
                $secondLineTabs = substr_count($lines[1], "\t");
                if ($firstLineTabs === $secondLineTabs) {
                    return 'tsv';
                }
            }
        }

        // Check for key-value pairs (key: value or key = value)
        if (preg_match('/^[\w\s]+[:=]\s*.+$/m', $text)) {
            return 'key_value';
        }

        return 'structured';
    }

    /**
     * Parse JSON input
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>, total_records?: int, valid_records?: int}
     */
    public function parseJson(string $text, string $importType): array
    {
        /** @var array<string> $errors */
        $errors = [];
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'json',
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
            ];
        }

        // Normalize to array of records
        if (is_array($data) && ! isset($data[0]) && ! empty($data)) {
            $data = [$data];
        }

        if (! is_array($data)) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'json',
                'errors' => ['Invalid JSON structure'],
            ];
        }

        /** @var array<int, array<string, mixed>> $validatedData */
        $validatedData = [];
        foreach ($data as $index => $record) {
            if (! is_array($record)) {
                $errors[] = "Record {$index}: Not a valid record array";

                continue;
            }
            /** @var array<string, mixed> $recordArray */
            $recordArray = $record;
            $validation = $this->validateRecord($recordArray, $importType);
            if (! $validation['valid']) {
                $errors[] = 'Record '.(string) $index.': '.implode(', ', $validation['errors']);
            } else {
                $validatedData[] = $this->normalizeRecord($recordArray, $importType);
            }
        }

        return [
            'success' => empty($errors),
            'data' => $validatedData,
            'format' => 'json',
            'errors' => $errors,
            'total_records' => count($data),
            'valid_records' => count($validatedData),
        ];
    }

    /**
     * Parse CSV input
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>, headers?: array<string>, total_records?: int, valid_records?: int}
     */
    public function parseCsv(string $text, string $importType): array
    {
        /** @var array<string> $errors */
        $errors = [];
        $lines = array_filter(explode("\n", $text), fn ($line) => trim($line) !== '');

        if (count($lines) < 2) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'csv',
                'errors' => ['CSV must have at least a header row and one data row'],
            ];
        }

        // Parse header
        $headerLine = array_shift($lines);
        $rawHeaders = str_getcsv($headerLine ?? '');
        /** @var array<string> $headers */
        $headers = array_map(fn ($h) => $this->normalizeHeader($h ?? ''), $rawHeaders);

        /** @var array<int, array<string, mixed>> $validatedData */
        $validatedData = [];
        foreach ($lines as $index => $line) {
            $values = str_getcsv($line);

            if (count($values) !== count($headers)) {
                $errors[] = 'Row '.($index + 2).': Column count mismatch';

                continue;
            }

            /** @var array<string, mixed> $record */
            $record = array_combine($headers, $values);
            $validation = $this->validateRecord($record, $importType);

            if (! $validation['valid']) {
                $errors[] = 'Row '.($index + 2).': '.implode(', ', $validation['errors']);
            } else {
                $validatedData[] = $this->normalizeRecord($record, $importType);
            }
        }

        return [
            'success' => empty($errors),
            'data' => $validatedData,
            'format' => 'csv',
            'errors' => $errors,
            'headers' => $headers,
            'total_records' => count($lines),
            'valid_records' => count($validatedData),
        ];
    }

    /**
     * Parse TSV (tab-separated values) input
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>, headers?: array<string>, total_records?: int, valid_records?: int}
     */
    public function parseTsv(string $text, string $importType): array
    {
        // Convert TSV to CSV format and use CSV parser
        $csvText = str_replace("\t", ',', $text);
        $result = $this->parseCsv($csvText, $importType);
        $result['format'] = 'tsv';

        return $result;
    }

    /**
     * Parse key-value pair format
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>, total_records?: int, valid_records?: int}
     */
    public function parseKeyValue(string $text, string $importType): array
    {
        /** @var array<string> $errors */
        $errors = [];
        /** @var array<string, mixed> $record */
        $record = [];

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Match key: value or key = value patterns
            if (preg_match('/^([\w\s]+)[:=]\s*(.+)$/i', $line, $matches)) {
                $key = $this->normalizeHeader(trim($matches[1]));
                $value = trim($matches[2]);
                $record[$key] = $value;
            }
        }

        if (empty($record)) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'key_value',
                'errors' => ['No valid key-value pairs found'],
            ];
        }

        $validation = $this->validateRecord($record, $importType);
        if (! $validation['valid']) {
            $errors = $validation['errors'];
        }

        return [
            'success' => empty($errors),
            'data' => empty($errors) ? [$this->normalizeRecord($record, $importType)] : [],
            'format' => 'key_value',
            'errors' => $errors,
            'total_records' => 1,
            'valid_records' => empty($errors) ? 1 : 0,
        ];
    }

    /**
     * Parse structured text with intelligent field detection
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, format: string, errors: array<string>, total_records?: int, valid_records?: int}
     */
    public function parseStructuredText(string $text, string $importType): array
    {
        /** @var array<string> $errors */
        $errors = [];
        /** @var array<string, mixed> $record */
        $record = [];

        // Try to extract common patterns based on import type
        $record = match ($importType) {
            'character' => $this->extractCharacterData($text),
            'career' => $this->extractCareerData($text),
            'training_session' => $this->extractTrainingData($text),
            'skill' => $this->extractSkillData($text),
            'support_card' => $this->extractSupportCardData($text),
            default => [],
        };

        if (empty($record)) {
            return [
                'success' => false,
                'data' => [],
                'format' => 'structured',
                'errors' => ['Could not parse structured text. Please use CSV, JSON, or key-value format.'],
            ];
        }

        $validation = $this->validateRecord($record, $importType);
        if (! $validation['valid']) {
            $errors = $validation['errors'];
        }

        return [
            'success' => empty($errors),
            'data' => empty($errors) ? [$this->normalizeRecord($record, $importType)] : [],
            'format' => 'structured',
            'errors' => $errors,
            'total_records' => 1,
            'valid_records' => empty($errors) ? 1 : 0,
        ];
    }

    /**
     * Extract character data from structured text
     *
     * @return array<string, mixed>
     */
    private function extractCharacterData(string $text): array
    {
        $data = [];

        // Extract name
        if (preg_match('/(?:name|character|trainee)[:=\s]+([^\n,]+)/i', $text, $matches)) {
            $data['name'] = trim($matches[1]);
        }

        // Extract stats (Speed, Stamina, Power, Guts, Wit)
        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($stats as $stat) {
            if (preg_match('/'.$stat.'[:=\s]+(\d+)/i', $text, $matches)) {
                $data[$stat] = (int) $matches[1];
            }
        }

        // Extract scenario type
        if (preg_match('/(?:scenario|mode)[:=\s]+(ura[_\s]?finale|unity[_\s]?cup)/i', $text, $matches)) {
            $data['scenario_type'] = Str::snake(strtolower($matches[1]));
        }

        // Extract energy level
        if (preg_match('/(?:energy|stamina[_\s]?level)[:=\s]+(\d+)/i', $text, $matches)) {
            $data['energy_level'] = (int) $matches[1];
        }

        // Extract mood
        if (preg_match('/(?:mood|condition)[:=\s]+(great|good|normal|bad|awful)/i', $text, $matches)) {
            $data['mood_status'] = strtolower($matches[1]);
        }

        return $data;
    }

    /**
     * Extract career data from structured text
     *
     * @return array<string, mixed>
     */
    private function extractCareerData(string $text): array
    {
        $data = [];

        // Extract career name
        if (preg_match('/(?:career[_\s]?name|run[_\s]?name)[:=\s]+([^\n,]+)/i', $text, $matches)) {
            $data['career_name'] = trim($matches[1]);
        }

        // Extract scenario type
        if (preg_match('/(?:scenario|mode)[:=\s]+(ura[_\s]?finale|unity[_\s]?cup)/i', $text, $matches)) {
            $data['scenario_type'] = Str::snake(strtolower($matches[1]));
        }

        // Extract turn number
        if (preg_match('/(?:turn|current[_\s]?turn)[:=\s]+(\d+)/i', $text, $matches)) {
            $data['current_turn'] = (int) $matches[1];
        }

        // Extract final stats
        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($stats as $stat) {
            if (preg_match('/(?:final[_\s]?)?'.$stat.'[:=\s]+(\d+)/i', $text, $matches)) {
                $data['final_'.$stat] = (int) $matches[1];
            }
        }

        // Extract status
        if (preg_match('/(?:status)[:=\s]+(active|completed|abandoned)/i', $text, $matches)) {
            $data['status'] = strtolower($matches[1]);
        }

        return $data;
    }

    /**
     * Extract training session data from structured text
     *
     * @return array<string, mixed>
     */
    private function extractTrainingData(string $text): array
    {
        $data = [];

        // Extract turn number
        if (preg_match('/(?:turn)[:=\s]+(\d+)/i', $text, $matches)) {
            $data['turn_number'] = (int) $matches[1];
        }

        // Extract training type
        if (preg_match('/(?:training[_\s]?type|type)[:=\s]+(speed|stamina|power|guts|wit|rest)/i', $text, $matches)) {
            $data['training_type'] = strtolower($matches[1]);
        }

        // Extract stat gains
        $stats = ['speed', 'stamina', 'power', 'guts', 'wit', 'sp'];
        foreach ($stats as $stat) {
            if (preg_match('/'.$stat.'[_\s]?(?:gain|bonus)?[:=\s]+([+-]?\d+)/i', $text, $matches)) {
                $data[$stat.'_gain'] = (int) $matches[1];
            }
        }

        // Extract energy cost
        if (preg_match('/(?:energy[_\s]?cost|energy)[:=\s]+([+-]?\d+)/i', $text, $matches)) {
            $data['energy_cost'] = (int) $matches[1];
        }

        return $data;
    }

    /**
     * Extract skill data from structured text
     *
     * @return array<string, mixed>
     */
    private function extractSkillData(string $text): array
    {
        $data = [];

        // Extract skill name
        if (preg_match('/(?:skill[_\s]?name|name)[:=\s]+([^\n,]+)/i', $text, $matches)) {
            $data['name'] = trim($matches[1]);
        }

        // Extract skill type
        if (preg_match('/(?:skill[_\s]?type|type)[:=\s]+(speed|stamina|power|guts|wit|recovery|debuff|passive)/i', $text, $matches)) {
            $data['skill_type'] = strtolower($matches[1]);
        }

        // Extract rarity
        if (preg_match('/(?:rarity)[:=\s]+(normal|rare|unique)/i', $text, $matches)) {
            $data['rarity'] = strtolower($matches[1]);
        }

        // Extract SP cost
        if (preg_match('/(?:sp[_\s]?cost|cost)[:=\s]+(\d+)/i', $text, $matches)) {
            $data['base_sp_cost'] = (int) $matches[1];
        }

        return $data;
    }

    /**
     * Extract support card data from structured text
     *
     * @return array<string, mixed>
     */
    private function extractSupportCardData(string $text): array
    {
        $data = [];

        // Extract card name
        if (preg_match('/(?:card[_\s]?name|name)[:=\s]+([^\n,]+)/i', $text, $matches)) {
            $data['name'] = trim($matches[1]);
        }

        // Extract rarity
        if (preg_match('/(?:rarity)[:=\s]+(ssr|sr|r)/i', $text, $matches)) {
            $data['rarity'] = strtoupper($matches[1]);
        }

        // Extract specialization
        if (preg_match('/(?:specialization|type)[:=\s]+(speed|stamina|power|guts|wit|pal)/i', $text, $matches)) {
            $data['specialization'] = strtolower($matches[1]);
        }

        // Extract limit break level
        if (preg_match('/(?:limit[_\s]?break|lb)[:=\s]+(\d)/i', $text, $matches)) {
            $data['limit_break_level'] = (int) $matches[1];
        }

        return $data;
    }

    /**
     * Validate a record based on import type
     *
     * @param  array<string, mixed>  $record
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateRecord(array $record, string $importType): array
    {
        $rules = $this->getValidationRules($importType);
        $validator = Validator::make($record, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        // Additional custom validation
        $customErrors = $this->customValidation($record, $importType);
        if (! empty($customErrors)) {
            return [
                'valid' => false,
                'errors' => $customErrors,
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Get validation rules for import type
     *
     * @return array<string, string>
     */
    private function getValidationRules(string $importType): array
    {
        return match ($importType) {
            'character' => [
                'name' => 'required|string|max:255',
                'speed' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'stamina' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'power' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'guts' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'wit' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'scenario_type' => 'nullable|string|in:'.implode(',', self::VALID_SCENARIOS),
                'energy_level' => 'nullable|integer|min:0|max:100',
                'mood_status' => 'nullable|string|in:great,good,normal,bad,awful',
            ],
            'career' => [
                'career_name' => 'nullable|string|max:255',
                'scenario_type' => 'nullable|string|in:'.implode(',', self::VALID_SCENARIOS),
                'current_turn' => 'nullable|integer|min:1|max:78',
                'status' => 'nullable|string|in:active,completed,abandoned',
                'final_speed' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'final_stamina' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'final_power' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'final_guts' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
                'final_wit' => 'nullable|integer|min:'.self::STAT_MIN.'|max:'.self::STAT_MAX,
            ],
            'training_session' => [
                'turn_number' => 'required|integer|min:1|max:78',
                'training_type' => 'required|string|in:speed,stamina,power,guts,wit,rest',
                'speed_gain' => 'nullable|integer',
                'stamina_gain' => 'nullable|integer',
                'power_gain' => 'nullable|integer',
                'guts_gain' => 'nullable|integer',
                'wit_gain' => 'nullable|integer',
                'sp_gain' => 'nullable|integer',
                'energy_cost' => 'nullable|integer',
            ],
            'skill' => [
                'name' => 'required|string|max:255',
                'skill_type' => 'nullable|string|in:speed,stamina,power,guts,wit,recovery,debuff,passive',
                'rarity' => 'nullable|string|in:normal,rare,unique',
                'base_sp_cost' => 'nullable|integer|min:0|max:500',
            ],
            'support_card' => [
                'name' => 'required|string|max:255',
                'rarity' => 'nullable|string|in:SSR,SR,R',
                'specialization' => 'nullable|string|in:speed,stamina,power,guts,wit,pal',
                'limit_break_level' => 'nullable|integer|min:0|max:4',
            ],
            default => [],
        };
    }

    /**
     * Custom validation logic
     *
     * @param  array<string, mixed>  $record
     * @return array<string>
     */
    private function customValidation(array $record, string $importType): array
    {
        $errors = [];

        if ($importType === 'character') {
            // Check if at least one stat is provided
            $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
            $hasStats = false;
            foreach ($stats as $stat) {
                if (isset($record[$stat]) && is_numeric($record[$stat])) {
                    $hasStats = true;
                    break;
                }
            }
            if (! $hasStats && ! isset($record['name'])) {
                $errors[] = 'Character must have a name or at least one stat value';
            }
        }

        return $errors;
    }

    /**
     * Normalize header names to snake_case
     */
    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $cleaned = preg_replace('/[^a-zA-Z0-9\s_]/', '', $header);
        $header = Str::snake($cleaned ?? '');

        return strtolower($header);
    }

    /**
     * Normalize record data
     *
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function normalizeRecord(array $record, string $importType): array
    {
        $normalized = [];

        foreach ($record as $key => $value) {
            $normalizedKey = $this->normalizeHeader($key);

            // Type casting based on field
            if (in_array($normalizedKey, ['speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'energy_level', 'current_turn', 'turn_number', 'base_sp_cost', 'limit_break_level'])) {
                $normalized[$normalizedKey] = is_numeric($value) ? (int) $value : null;
            } elseif (str_ends_with($normalizedKey, '_gain') || str_ends_with($normalizedKey, '_cost')) {
                $normalized[$normalizedKey] = is_numeric($value) ? (int) $value : null;
            } elseif (str_starts_with($normalizedKey, 'final_')) {
                $normalized[$normalizedKey] = is_numeric($value) ? (int) $value : null;
            } else {
                $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
            }
        }

        return $normalized;
    }

    /**
     * Generate import preview with validation results
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<string, mixed>
     */
    public function generatePreview(array $data, string $importType): array
    {
        $preview = [
            'import_type' => $importType,
            'import_type_label' => self::IMPORT_TYPES[$importType] ?? $importType,
            'total_records' => count($data),
            'valid_records' => 0,
            'invalid_records' => 0,
            'records' => [],
            'field_mapping' => $this->getFieldMapping($importType),
            'warnings' => [],
        ];

        foreach ($data as $index => $record) {
            $validation = $this->validateRecord($record, $importType);
            $recordPreview = [
                'index' => $index,
                'data' => $record,
                'valid' => $validation['valid'],
                'errors' => $validation['errors'],
            ];

            if ($validation['valid']) {
                $preview['valid_records']++;
            } else {
                $preview['invalid_records']++;
            }

            $preview['records'][] = $recordPreview;
        }

        // Add warnings for potential issues
        if ($preview['invalid_records'] > 0) {
            $preview['warnings'][] = "{$preview['invalid_records']} record(s) have validation errors and will be skipped during import.";
        }

        return $preview;
    }

    /**
     * Get field mapping for import type
     *
     * @return array<string, array<string, bool|int|string|array<string>>>
     */
    public function getFieldMapping(string $importType): array
    {
        return match ($importType) {
            'character' => [
                'name' => ['label' => 'Character Name', 'required' => true, 'type' => 'string'],
                'speed' => ['label' => 'Speed', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'stamina' => ['label' => 'Stamina', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'power' => ['label' => 'Power', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'guts' => ['label' => 'Guts', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'wit' => ['label' => 'Wit', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'scenario_type' => ['label' => 'Scenario Type', 'required' => false, 'type' => 'enum', 'options' => ['ura_finale', 'unity_cup']],
                'energy_level' => ['label' => 'Energy Level', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 100],
                'mood_status' => ['label' => 'Mood', 'required' => false, 'type' => 'enum', 'options' => ['great', 'good', 'normal', 'bad', 'awful']],
            ],
            'career' => [
                'career_name' => ['label' => 'Career Name', 'required' => false, 'type' => 'string'],
                'scenario_type' => ['label' => 'Scenario Type', 'required' => false, 'type' => 'enum', 'options' => ['ura_finale', 'unity_cup']],
                'current_turn' => ['label' => 'Current Turn', 'required' => false, 'type' => 'integer', 'min' => 1, 'max' => 78],
                'status' => ['label' => 'Status', 'required' => false, 'type' => 'enum', 'options' => ['active', 'completed', 'abandoned']],
                'final_speed' => ['label' => 'Final Speed', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'final_stamina' => ['label' => 'Final Stamina', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'final_power' => ['label' => 'Final Power', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'final_guts' => ['label' => 'Final Guts', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
                'final_wit' => ['label' => 'Final Wit', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 1200],
            ],
            'training_session' => [
                'turn_number' => ['label' => 'Turn Number', 'required' => true, 'type' => 'integer', 'min' => 1, 'max' => 78],
                'training_type' => ['label' => 'Training Type', 'required' => true, 'type' => 'enum', 'options' => ['speed', 'stamina', 'power', 'guts', 'wit', 'rest']],
                'speed_gain' => ['label' => 'Speed Gain', 'required' => false, 'type' => 'integer'],
                'stamina_gain' => ['label' => 'Stamina Gain', 'required' => false, 'type' => 'integer'],
                'power_gain' => ['label' => 'Power Gain', 'required' => false, 'type' => 'integer'],
                'guts_gain' => ['label' => 'Guts Gain', 'required' => false, 'type' => 'integer'],
                'wit_gain' => ['label' => 'Wit Gain', 'required' => false, 'type' => 'integer'],
                'sp_gain' => ['label' => 'SP Gain', 'required' => false, 'type' => 'integer'],
                'energy_cost' => ['label' => 'Energy Cost', 'required' => false, 'type' => 'integer'],
            ],
            'skill' => [
                'name' => ['label' => 'Skill Name', 'required' => true, 'type' => 'string'],
                'skill_type' => ['label' => 'Skill Type', 'required' => false, 'type' => 'enum', 'options' => ['speed', 'stamina', 'power', 'guts', 'wit', 'recovery', 'debuff', 'passive']],
                'rarity' => ['label' => 'Rarity', 'required' => false, 'type' => 'enum', 'options' => ['normal', 'rare', 'unique']],
                'base_sp_cost' => ['label' => 'SP Cost', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 500],
            ],
            'support_card' => [
                'name' => ['label' => 'Card Name', 'required' => true, 'type' => 'string'],
                'rarity' => ['label' => 'Rarity', 'required' => false, 'type' => 'enum', 'options' => ['SSR', 'SR', 'R']],
                'specialization' => ['label' => 'Specialization', 'required' => false, 'type' => 'enum', 'options' => ['speed', 'stamina', 'power', 'guts', 'wit', 'pal']],
                'limit_break_level' => ['label' => 'Limit Break', 'required' => false, 'type' => 'integer', 'min' => 0, 'max' => 4],
            ],
            default => [],
        };
    }

    /**
     * Execute import of validated data
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<string, mixed>
     */
    public function executeImport(array $data, string $importType, int $userId): array
    {
        $results = [
            'success' => true,
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
            'imported_ids' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $record) {
                try {
                    $id = match ($importType) {
                        'character' => $this->importCharacter($record, $userId),
                        'career' => $this->importCareer($record, $userId),
                        'training_session' => $this->importTrainingSession($record, $userId),
                        'skill' => $this->importSkill($record),
                        'support_card' => $this->importSupportCard($record),
                        default => throw new \InvalidArgumentException("Unknown import type: {$importType}"),
                    };

                    $results['imported']++;
                    $results['imported_ids'][] = $id;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Record {$index}: ".$e->getMessage();
                    Log::warning('[DataImportService] Import record failed', [
                        'index' => $index,
                        'import_type' => $importType,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($results['failed'] > 0 && $results['imported'] === 0) {
                DB::rollBack();
                $results['success'] = false;
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $results['success'] = false;
            $results['errors'][] = 'Import failed: '.$e->getMessage();
            Log::error('[DataImportService] Import transaction failed', [
                'import_type' => $importType,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Import a character record
     *
     * @param  array<string, mixed>  $record
     */
    private function importCharacter(array $record, int $userId): int
    {
        $stats = [
            'speed' => $record['speed'] ?? 0,
            'stamina' => $record['stamina'] ?? 0,
            'power' => $record['power'] ?? 0,
            'guts' => $record['guts'] ?? 0,
            'wit' => $record['wit'] ?? 0,
        ];

        // Default stat priorities based on scenario type
        $statPriorities = $record['stat_priorities'] ?? [
            'speed' => 5,
            'stamina' => 4,
            'power' => 3,
            'guts' => 1,
            'wit' => 2,
        ];

        $character = Character::create([
            'user_id' => $userId,
            'uuid' => (string) Str::uuid(),
            'name' => $record['name'],
            'scenario_type' => $record['scenario_type'] ?? 'ura_finale',
            'career_stage' => $record['career_stage'] ?? 'junior',
            'current_turn' => $record['current_turn'] ?? 1,
            'current_stats' => $stats,
            'stat_priorities' => $statPriorities,
            'energy_level' => $record['energy_level'] ?? 100,
            'mood_status' => $record['mood_status'] ?? 'normal',
            'status' => 'active',
        ]);

        return $character->id;
    }

    /**
     * Import a career record
     *
     * @param  array<string, mixed>  $record
     */
    private function importCareer(array $record, int $userId): int
    {
        // Find or create character for this career
        $characterId = $record['character_id'] ?? null;

        if (! $characterId) {
            // Create a placeholder character if none specified
            $character = Character::create([
                'user_id' => $userId,
                'uuid' => (string) Str::uuid(),
                'name' => $record['career_name'] ?? 'Imported Career',
                'scenario_type' => $record['scenario_type'] ?? 'ura_finale',
                'career_stage' => 'junior',
                'current_turn' => 1,
                'current_stats' => ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0],
                'stat_priorities' => ['speed' => 5, 'stamina' => 4, 'power' => 3, 'guts' => 1, 'wit' => 2],
                'energy_level' => 100,
                'mood_status' => 'normal',
                'status' => 'active',
            ]);
            $characterId = $character->id;
        }

        $career = Career::create([
            'character_id' => $characterId,
            'user_id' => $userId,
            'career_name' => $record['career_name'] ?? null,
            'scenario_type' => $record['scenario_type'] ?? 'ura_finale',
            'status' => $record['status'] ?? 'active',
            'current_turn' => $record['current_turn'] ?? 1,
            'final_speed' => $record['final_speed'] ?? null,
            'final_stamina' => $record['final_stamina'] ?? null,
            'final_power' => $record['final_power'] ?? null,
            'final_guts' => $record['final_guts'] ?? null,
            'final_wit' => $record['final_wit'] ?? null,
        ]);

        return $career->id;
    }

    /**
     * Import a training session record
     *
     * @param  array<string, mixed>  $record
     */
    private function importTrainingSession(array $record, int $userId): int
    {
        $careerId = $record['career_id'] ?? null;

        if (! $careerId) {
            throw new \InvalidArgumentException('Training session requires a career_id');
        }

        // Verify career belongs to user
        $career = Career::where('id', $careerId)->where('user_id', $userId)->first();
        if (! $career) {
            throw new \InvalidArgumentException('Career not found or does not belong to user');
        }

        $statGains = [
            'speed' => $record['speed_gain'] ?? 0,
            'stamina' => $record['stamina_gain'] ?? 0,
            'power' => $record['power_gain'] ?? 0,
            'guts' => $record['guts_gain'] ?? 0,
            'wit' => $record['wit_gain'] ?? 0,
        ];

        $session = TrainingSession::create([
            'career_id' => $careerId,
            'turn_number' => $record['turn_number'],
            'training_type' => $record['training_type'],
            'stat_gains' => $statGains,
            'sp_gained' => $record['sp_gain'] ?? 0,
            'energy_cost' => $record['energy_cost'] ?? 0,
        ]);

        return $session->id;
    }

    /**
     * Import a skill record
     *
     * @param  array<string, mixed>  $record
     */
    private function importSkill(array $record): int
    {
        // Check if skill already exists
        $existingSkill = Skill::where('name', $record['name'])->first();
        if ($existingSkill) {
            // Update existing skill
            $existingSkill->update([
                'skill_type' => $record['skill_type'] ?? $existingSkill->skill_type,
                'rarity' => $record['rarity'] ?? $existingSkill->rarity,
                'base_sp_cost' => $record['base_sp_cost'] ?? $existingSkill->base_sp_cost,
            ]);

            return $existingSkill->id;
        }

        $skill = Skill::create([
            'name' => $record['name'],
            'internal_id' => Str::slug(is_string($record['name']) ? $record['name'] : ''),
            'skill_type' => $record['skill_type'] ?? 'passive',
            'rarity' => $record['rarity'] ?? 'normal',
            'base_sp_cost' => $record['base_sp_cost'] ?? 120,
            'is_active' => true,
        ]);

        return $skill->id;
    }

    /**
     * Import a support card record
     *
     * @param  array<string, mixed>  $record
     */
    private function importSupportCard(array $record): int
    {
        // Check if support card already exists
        $existingCard = SupportCard::where('name', $record['name'])->first();
        if ($existingCard) {
            return $existingCard->id;
        }

        $card = SupportCard::create([
            'name' => $record['name'],
            'internal_id' => Str::slug(is_string($record['name']) ? $record['name'] : ''),
            'rarity' => $record['rarity'] ?? 'SR',
            'specialization' => $record['specialization'] ?? 'speed',
            'is_active' => true,
        ]);

        return $card->id;
    }

    /**
     * Get import templates for different formats
     *
     * @return array<string, array<string, string|false>>
     */
    public function getTemplates(): array
    {
        return [
            'character' => [
                'csv' => "name,speed,stamina,power,guts,wit,scenario_type,energy_level,mood_status\nSilence Suzuka,800,600,700,500,650,ura_finale,100,good",
                'json' => json_encode([
                    'name' => 'Silence Suzuka',
                    'speed' => 800,
                    'stamina' => 600,
                    'power' => 700,
                    'guts' => 500,
                    'wit' => 650,
                    'scenario_type' => 'ura_finale',
                    'energy_level' => 100,
                    'mood_status' => 'good',
                ], JSON_PRETTY_PRINT),
                'key_value' => "Name: Silence Suzuka\nSpeed: 800\nStamina: 600\nPower: 700\nGuts: 500\nWit: 650\nScenario: ura_finale\nEnergy: 100\nMood: good",
            ],
            'career' => [
                'csv' => "career_name,scenario_type,current_turn,status,final_speed,final_stamina,final_power,final_guts,final_wit\nMy First Run,ura_finale,78,completed,1100,900,850,700,800",
                'json' => json_encode([
                    'career_name' => 'My First Run',
                    'scenario_type' => 'ura_finale',
                    'current_turn' => 78,
                    'status' => 'completed',
                    'final_speed' => 1100,
                    'final_stamina' => 900,
                    'final_power' => 850,
                    'final_guts' => 700,
                    'final_wit' => 800,
                ], JSON_PRETTY_PRINT),
            ],
            'training_session' => [
                'csv' => "turn_number,training_type,speed_gain,stamina_gain,power_gain,guts_gain,wit_gain,sp_gain,energy_cost\n1,speed,15,0,5,0,0,10,20",
                'json' => json_encode([
                    'turn_number' => 1,
                    'training_type' => 'speed',
                    'speed_gain' => 15,
                    'stamina_gain' => 0,
                    'power_gain' => 5,
                    'guts_gain' => 0,
                    'wit_gain' => 0,
                    'sp_gain' => 10,
                    'energy_cost' => 20,
                ], JSON_PRETTY_PRINT),
            ],
            'skill' => [
                'csv' => "name,skill_type,rarity,base_sp_cost\nGo with the Flow,speed,normal,120",
                'json' => json_encode([
                    'name' => 'Go with the Flow',
                    'skill_type' => 'speed',
                    'rarity' => 'normal',
                    'base_sp_cost' => 120,
                ], JSON_PRETTY_PRINT),
            ],
            'support_card' => [
                'csv' => "name,rarity,specialization,limit_break_level\nKitasan Black,SSR,speed,4",
                'json' => json_encode([
                    'name' => 'Kitasan Black',
                    'rarity' => 'SSR',
                    'specialization' => 'speed',
                    'limit_break_level' => 4,
                ], JSON_PRETTY_PRINT),
            ],
        ];
    }

    /**
     * Get import history for a user
     *
     * @return Collection<int, mixed>
     */
    public function getImportHistory(int $userId, int $limit = 20): Collection
    {
        // This would typically query an import_history table
        // For now, return empty collection as the table doesn't exist yet
        return collect([]);
    }
}
