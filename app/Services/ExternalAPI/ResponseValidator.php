<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Log;

/**
 * Response Validator for External API Responses
 *
 * Validates API responses against expected schemas and provides
 * detailed error reporting for malformed data.
 *
 * Requirements: 14.1, 14.3, Task 1.2.3
 */
class ResponseValidator
{
    /**
     * Validation schemas for different response types
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $schemas = [];

    public function __construct()
    {
        $this->initializeSchemas();
    }

    /**
     * Validate API response against schema
     *
     * @param  array<string, mixed>|mixed  $response
     * @return array{valid: bool, errors: array<string>, data: mixed}
     */
    public function validate(string $responseType, mixed $response): array
    {
        $schema = $this->schemas[$responseType] ?? null;

        if (! is_array($schema)) {
            Log::warning('[ResponseValidator] No schema found for response type', [
                'response_type' => $responseType,
            ]);

            return [
                'valid' => true,
                'errors' => [],
                'data' => $response,
            ];
        }

        $errors = [];
        $wrapperKey = isset($schema['wrapper_key']) && is_string($schema['wrapper_key']) ? $schema['wrapper_key'] : null;

        // Validate response structure
        if ($wrapperKey !== null) {
            if (! is_array($response) || ! isset($response[$wrapperKey])) {
                $errors[] = "Missing wrapper key: {$wrapperKey}";

                return [
                    'valid' => false,
                    'errors' => $errors,
                    'data' => null,
                ];
            }

            $data = $response[$wrapperKey];
        } else {
            $data = $response;
        }

        $itemSchema = isset($schema['item_schema']) && is_array($schema['item_schema']) ? $schema['item_schema'] : [];

        // Validate data type
        if (isset($schema['is_array']) && $schema['is_array'] === true) {
            if (! is_array($data)) {
                $errors[] = 'Expected array data';

                return [
                    'valid' => false,
                    'errors' => $errors,
                    'data' => null,
                ];
            }

            // Validate each item in array
            foreach ($data as $index => $item) {
                $itemErrors = $this->validateItem($item, $itemSchema);

                foreach ($itemErrors as $error) {
                    $errors[] = "Item {$index}: {$error}";
                }
            }
        } else {
            // Validate single item
            $errors = $this->validateItem($data, $itemSchema);
        }

        $valid = empty($errors);

        if (! $valid) {
            Log::warning('[ResponseValidator] Validation failed', [
                'response_type' => $responseType,
                'errors' => $errors,
            ]);
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $valid ? $data : null,
        ];
    }

    /**
     * Validate a single item against schema
     *
     * @param  array<string, mixed>  $schema
     * @return array<string>
     */
    protected function validateItem(mixed $item, array $schema): array
    {
        $errors = [];

        if (! is_array($item)) {
            $errors[] = 'Item must be an array';

            return $errors;
        }

        // Check required fields
        $required = isset($schema['required']) && is_array($schema['required']) ? $schema['required'] : [];
        foreach ($required as $field) {
            if (! is_string($field)) {
                continue;
            }
            if (! isset($item[$field]) && ! array_key_exists($field, $item)) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Check field types
        $types = isset($schema['types']) && is_array($schema['types']) ? $schema['types'] : [];
        foreach ($types as $field => $expectedType) {
            if (! is_string($field) || ! is_string($expectedType)) {
                continue;
            }
            if (isset($item[$field])) {
                $fieldValue = $item[$field];
                $actualType = gettype($fieldValue);

                // Handle nullable types
                if (str_ends_with($expectedType, '|null') && $actualType === 'NULL') {
                    continue;
                }

                $expectedType = str_replace('|null', '', $expectedType);

                if ($actualType !== $expectedType) {
                    $errors[] = "Field '{$field}' has incorrect type. Expected: {$expectedType}, Got: {$actualType}";
                }
            }
        }

        // Check enum values
        $enums = isset($schema['enums']) && is_array($schema['enums']) ? $schema['enums'] : [];
        foreach ($enums as $field => $allowedValues) {
            if (! is_string($field) || ! is_array($allowedValues)) {
                continue;
            }
            if (isset($item[$field])) {
                if (! in_array($item[$field], $allowedValues, true)) {
                    $stringValues = array_map(static fn ($v): string => is_scalar($v) ? (string) $v : 'complex', $allowedValues);
                    $errors[] = "Field '{$field}' has invalid value. Allowed: ".implode(', ', $stringValues);
                }
            }
        }

        // Check value ranges
        $ranges = isset($schema['ranges']) && is_array($schema['ranges']) ? $schema['ranges'] : [];
        foreach ($ranges as $field => $range) {
            if (! is_string($field) || ! is_array($range)) {
                continue;
            }
            if (isset($item[$field])) {
                $value = $item[$field];
                $valueStr = is_scalar($value) ? (string) $value : 'N/A';

                if (isset($range['min']) && is_numeric($value) && is_numeric($range['min']) && $value < $range['min']) {
                    $minStr = (string) $range['min'];
                    $errors[] = "Field '{$field}' value {$valueStr} is below minimum {$minStr}";
                }

                if (isset($range['max']) && is_numeric($value) && is_numeric($range['max']) && $value > $range['max']) {
                    $maxStr = (string) $range['max'];
                    $errors[] = "Field '{$field}' value {$valueStr} exceeds maximum {$maxStr}";
                }
            }
        }

        return $errors;
    }

    /**
     * Initialize validation schemas
     */
    protected function initializeSchemas(): void
    {
        $this->schemas = [
            'umapyoi_characters' => [
                'wrapper_key' => 'characters',
                'is_array' => true,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'title' => 'string|null',
                        'rarity' => 'integer|null',
                    ],
                ],
            ],
            'umapyoi_character' => [
                'wrapper_key' => 'character',
                'is_array' => false,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'title' => 'string|null',
                        'rarity' => 'integer|null',
                    ],
                ],
            ],
            'umapyoi_support_cards' => [
                'wrapper_key' => 'support_cards',
                'is_array' => true,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'rarity' => 'string|null',
                        'type' => 'string|null',
                    ],
                    'enums' => [
                        'rarity' => ['SSR', 'SR', 'R'],
                    ],
                ],
            ],
            'umapyoi_support_card' => [
                'wrapper_key' => 'support_card',
                'is_array' => false,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'rarity' => 'string|null',
                        'type' => 'string|null',
                    ],
                    'enums' => [
                        'rarity' => ['SSR', 'SR', 'R'],
                    ],
                ],
            ],
            'umapyoi_skills' => [
                'wrapper_key' => 'skills',
                'is_array' => true,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'description' => 'string|null',
                        'effect' => 'string|null',
                    ],
                ],
            ],
            'umapyoi_skill' => [
                'wrapper_key' => 'skill',
                'is_array' => false,
                'item_schema' => [
                    'required' => ['id', 'name'],
                    'types' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'description' => 'string|null',
                        'effect' => 'string|null',
                    ],
                ],
            ],
            'umapyoi_news' => [
                'wrapper_key' => 'news',
                'is_array' => true,
                'item_schema' => [
                    'required' => ['id', 'title'],
                    'types' => [
                        'id' => 'integer',
                        'title' => 'string',
                        'content' => 'string|null',
                        'published_at' => 'string|null',
                    ],
                ],
            ],
        ];
    }

    /**
     * Add or update a validation schema
     *
     * @param  array<string, mixed>  $schema
     */
    public function addSchema(string $responseType, array $schema): void
    {
        $this->schemas[$responseType] = $schema;

        Log::info('[ResponseValidator] Schema added/updated', [
            'response_type' => $responseType,
        ]);
    }

    /**
     * Get all registered schemas
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }
}
