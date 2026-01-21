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
     * @param  array<string, mixed>  $response
     * @return array{valid: bool, errors: array<string>, data: mixed}
     */
    public function validate(string $responseType, array $response): array
    {
        $schema = $this->schemas[$responseType] ?? null;

        if (! $schema) {
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

        // Validate response structure
        if (isset($schema['wrapper_key'])) {
            if (! isset($response[$schema['wrapper_key']])) {
                $errors[] = "Missing wrapper key: {$schema['wrapper_key']}";

                return [
                    'valid' => false,
                    'errors' => $errors,
                    'data' => null,
                ];
            }

            $data = $response[$schema['wrapper_key']];
        } else {
            $data = $response;
        }

        // Validate data type
        if (isset($schema['is_array']) && $schema['is_array']) {
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
                $itemErrors = $this->validateItem($item, $schema['item_schema'] ?? []);

                foreach ($itemErrors as $error) {
                    $errors[] = "Item {$index}: {$error}";
                }
            }
        } else {
            // Validate single item
            $errors = $this->validateItem($data, $schema['item_schema'] ?? []);
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
     * @param  mixed  $item
     * @param  array<string, mixed>  $schema
     * @return array<string>
     */
    protected function validateItem($item, array $schema): array
    {
        $errors = [];

        if (! is_array($item)) {
            $errors[] = 'Item must be an array';

            return $errors;
        }

        // Check required fields
        if (isset($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (! isset($item[$field]) && ! array_key_exists($field, $item)) {
                    $errors[] = "Missing required field: {$field}";
                }
            }
        }

        // Check field types
        if (isset($schema['types'])) {
            foreach ($schema['types'] as $field => $expectedType) {
                if (isset($item[$field])) {
                    $actualType = gettype($item[$field]);

                    // Handle nullable types
                    if (str_ends_with($expectedType, '|null') && $item[$field] === null) {
                        continue;
                    }

                    $expectedType = str_replace('|null', '', $expectedType);

                    if ($actualType !== $expectedType) {
                        $errors[] = "Field '{$field}' has incorrect type. Expected: {$expectedType}, Got: {$actualType}";
                    }
                }
            }
        }

        // Check enum values
        if (isset($schema['enums'])) {
            foreach ($schema['enums'] as $field => $allowedValues) {
                if (isset($item[$field])) {
                    if (! in_array($item[$field], $allowedValues, true)) {
                        $errors[] = "Field '{$field}' has invalid value. Allowed: ".implode(', ', $allowedValues);
                    }
                }
            }
        }

        // Check value ranges
        if (isset($schema['ranges'])) {
            foreach ($schema['ranges'] as $field => $range) {
                if (isset($item[$field])) {
                    $value = $item[$field];

                    if (isset($range['min']) && $value < $range['min']) {
                        $errors[] = "Field '{$field}' value {$value} is below minimum {$range['min']}";
                    }

                    if (isset($range['max']) && $value > $range['max']) {
                        $errors[] = "Field '{$field}' value {$value} exceeds maximum {$range['max']}";
                    }
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
