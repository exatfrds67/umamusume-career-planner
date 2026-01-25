<?php

declare(strict_types=1);

use App\Services\ExternalAPI\ResponseValidator;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->validator = new ResponseValidator;
});

describe('ResponseValidator - Schema Initialization', function () {
    it('initializes with predefined schemas', function () {
        $schemas = $this->validator->getSchemas();

        expect($schemas)->toBeArray()
            ->and($schemas)->toHaveKey('umapyoi_characters')
            ->and($schemas)->toHaveKey('umapyoi_character')
            ->and($schemas)->toHaveKey('umapyoi_support_cards')
            ->and($schemas)->toHaveKey('umapyoi_support_card')
            ->and($schemas)->toHaveKey('umapyoi_skills')
            ->and($schemas)->toHaveKey('umapyoi_skill')
            ->and($schemas)->toHaveKey('umapyoi_news');
    });

    it('can add custom schema', function () {
        $customSchema = [
            'wrapper_key' => 'custom_data',
            'is_array' => false,
            'item_schema' => [
                'required' => ['id', 'name'],
                'types' => [
                    'id' => 'integer',
                    'name' => 'string',
                ],
            ],
        ];

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Schema added/updated') &&
                    $context['response_type'] === 'custom_type';
            });

        $this->validator->addSchema('custom_type', $customSchema);

        $schemas = $this->validator->getSchemas();

        expect($schemas)->toHaveKey('custom_type')
            ->and($schemas['custom_type'])->toBe($customSchema);
    });

    it('can update existing schema', function () {
        $updatedSchema = [
            'wrapper_key' => 'updated_characters',
            'is_array' => true,
            'item_schema' => [
                'required' => ['id', 'name', 'new_field'],
            ],
        ];

        Log::shouldReceive('info')->once();

        $this->validator->addSchema('umapyoi_characters', $updatedSchema);

        $schemas = $this->validator->getSchemas();

        expect($schemas['umapyoi_characters'])->toBe($updatedSchema);
    });
});

describe('ResponseValidator - Valid Responses', function () {
    it('validates valid character array response', function () {
        $response = [
            'characters' => [
                [
                    'id' => 1,
                    'name_en' => 'Silence Suzuka',
                    'name_jp' => 'サイレンススズカ',
                    'category_label' => 'Speed',
                    'thumb_img' => 'https://example.com/image1.png',
                ],
                [
                    'id' => 2,
                    'name_en' => 'Tokai Teio',
                    'name_jp' => 'トウカイテイオー',
                    'category_label' => 'Runner',
                    'thumb_img' => 'https://example.com/image2.png',
                ],
            ],
        ];

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBeArray()
            ->and($result['data'])->toHaveCount(2);
    });

    it('validates valid single character response', function () {
        $response = [
            'character' => [
                'id' => 1,
                'name' => 'Silence Suzuka',
                'title' => 'Silent Runner',
                'rarity' => 3,
            ],
        ];

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBeArray()
            ->and($result['data']['id'])->toBe(1);
    });

    it('validates valid support card response', function () {
        $response = [
            'support_card' => [
                'id' => 100,
                'name' => 'Test Card',
                'rarity' => 'SSR',
                'type' => 'Speed',
            ],
        ];

        $result = $this->validator->validate('umapyoi_support_card', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data']['rarity'])->toBe('SSR');
    });

    it('validates response with nullable fields', function () {
        $response = [
            'character' => [
                'id' => 1,
                'name' => 'Test Character',
                'title' => null,
                'rarity' => null,
            ],
        ];

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('validates empty array response', function () {
        $response = [
            'characters' => [],
        ];

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBeArray()
            ->and($result['data'])->toBeEmpty();
    });
});

describe('ResponseValidator - Invalid Responses', function () {
    it('fails validation when wrapper key is missing', function () {
        $response = [
            'wrong_key' => [
                ['id' => 1, 'name' => 'Test'],
            ],
        ];

        // No Log::warning expected - validation fails early before logging

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Missing wrapper key: characters')
            ->and($result['data'])->toBeNull();
    });

    it('fails validation when required field is missing', function () {
        $response = [
            'character' => [
                'id' => 1,
                // Missing 'name' field
            ],
        ];

        Log::shouldReceive('warning')->once();

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Missing required field: name')
            ->and($result['data'])->toBeNull();
    });

    it('fails validation when field type is incorrect', function () {
        $response = [
            'character' => [
                'id' => 'not_an_integer', // Should be integer
                'name' => 'Test Character',
            ],
        ];

        Log::shouldReceive('warning')->once();

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toHaveCount(1)
            ->and($result['errors'][0])->toContain("Field 'id' has incorrect type")
            ->and($result['data'])->toBeNull();
    });

    it('fails validation when enum value is invalid', function () {
        $response = [
            'support_card' => [
                'id' => 100,
                'name' => 'Test Card',
                'rarity' => 'INVALID', // Should be SSR, SR, or R
            ],
        ];

        Log::shouldReceive('warning')->once();

        $result = $this->validator->validate('umapyoi_support_card', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toHaveCount(1)
            ->and($result['errors'][0])->toContain("Field 'rarity' has invalid value")
            ->and($result['data'])->toBeNull();
    });

    it('fails validation when data is not an array for array response', function () {
        $response = [
            'characters' => 'not_an_array',
        ];

        // No Log::warning expected - validation fails early before logging

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Expected array data')
            ->and($result['data'])->toBeNull();
    });

    it('validates each item in array and reports all errors', function () {
        $response = [
            'characters' => [
                [
                    'id' => 1,
                    'name_en' => 'Valid Character',
                ],
                [
                    'id' => 'invalid', // Wrong type
                    // Missing 'name_en' field
                ],
                [
                    'id' => 3,
                    'name_en' => 'Another Valid',
                ],
            ],
        ];

        Log::shouldReceive('warning')->once();

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toHaveCount(2)
            ->and($result['errors'][0])->toContain('Item 1:')
            ->and($result['errors'][1])->toContain('Item 1:')
            ->and($result['data'])->toBeNull();
    });
});

describe('ResponseValidator - Schema Without Wrapper Key', function () {
    it('validates response without wrapper key', function () {
        $customSchema = [
            'is_array' => false,
            'item_schema' => [
                'required' => ['id', 'name'],
                'types' => [
                    'id' => 'integer',
                    'name' => 'string',
                ],
            ],
        ];

        Log::shouldReceive('info')->once();

        $this->validator->addSchema('no_wrapper', $customSchema);

        $response = [
            'id' => 1,
            'name' => 'Test',
        ];

        $result = $this->validator->validate('no_wrapper', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBe($response);
    });
});

describe('ResponseValidator - Unknown Response Type', function () {
    it('returns valid for unknown response type with warning', function () {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'No schema found') &&
                    $context['response_type'] === 'unknown_type';
            });

        $response = ['any' => 'data'];

        $result = $this->validator->validate('unknown_type', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBe($response);
    });
});

describe('ResponseValidator - Range Validation', function () {
    it('validates value ranges when schema includes ranges', function () {
        $customSchema = [
            'is_array' => false,
            'item_schema' => [
                'required' => ['id', 'value'],
                'types' => [
                    'id' => 'integer',
                    'value' => 'integer',
                ],
                'ranges' => [
                    'value' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
            ],
        ];

        Log::shouldReceive('info')->once();

        $this->validator->addSchema('with_ranges', $customSchema);

        $validResponse = [
            'id' => 1,
            'value' => 50,
        ];

        $result = $this->validator->validate('with_ranges', $validResponse);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('fails validation when value is below minimum', function () {
        $customSchema = [
            'is_array' => false,
            'item_schema' => [
                'required' => ['id', 'value'],
                'types' => [
                    'id' => 'integer',
                    'value' => 'integer',
                ],
                'ranges' => [
                    'value' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once();

        $this->validator->addSchema('with_ranges', $customSchema);

        $invalidResponse = [
            'id' => 1,
            'value' => -10,
        ];

        $result = $this->validator->validate('with_ranges', $invalidResponse);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toHaveCount(1)
            ->and($result['errors'][0])->toContain('below minimum');
    });

    it('fails validation when value exceeds maximum', function () {
        $customSchema = [
            'is_array' => false,
            'item_schema' => [
                'required' => ['id', 'value'],
                'types' => [
                    'id' => 'integer',
                    'value' => 'integer',
                ],
                'ranges' => [
                    'value' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once();

        $this->validator->addSchema('with_ranges', $customSchema);

        $invalidResponse = [
            'id' => 1,
            'value' => 150,
        ];

        $result = $this->validator->validate('with_ranges', $invalidResponse);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toHaveCount(1)
            ->and($result['errors'][0])->toContain('exceeds maximum');
    });
});

describe('ResponseValidator - Complex Nested Validation', function () {
    it('validates complex nested structures', function () {
        $response = [
            'characters' => [
                [
                    'id' => 1,
                    'name_en' => 'Test Character',
                    'name_jp' => 'テストキャラクター',
                    'category_label' => 'Speed',
                ],
            ],
        ];

        $result = $this->validator->validate('umapyoi_characters', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['data'])->toBeArray()
            ->and($result['data'][0]['name_en'])->toBe('Test Character');
    });
});

describe('ResponseValidator - Edge Cases', function () {
    it('handles item that is not an array', function () {
        $customSchema = [
            'wrapper_key' => 'data',
            'is_array' => false,
            'item_schema' => [
                'required' => ['id'],
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once();

        $this->validator->addSchema('test_schema', $customSchema);

        // The wrapper key points to a non-array value
        $response = ['data' => 'not_an_array'];

        $result = $this->validator->validate('test_schema', $response);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Item must be an array');
    });

    it('handles null values in nullable fields correctly', function () {
        $response = [
            'character' => [
                'id' => 1,
                'name' => 'Test',
                'title' => null,
                'rarity' => null,
            ],
        ];

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeTrue()
            ->and($result['data']['title'])->toBeNull()
            ->and($result['data']['rarity'])->toBeNull();
    });

    it('handles missing optional fields', function () {
        $response = [
            'character' => [
                'id' => 1,
                'name' => 'Test',
                // Optional fields not included
            ],
        ];

        $result = $this->validator->validate('umapyoi_character', $response);

        expect($result['valid'])->toBeTrue();
    });
});
