<?php

declare(strict_types=1);

use App\Services\OCR\DataTransformationService;

beforeEach(function () {
    $this->transformer = new DataTransformationService;
});

describe('Character Stats Transformation', function () {
    it('transforms character stats correctly', function () {
        $extractedData = [
            'data' => [
                'stats' => [
                    'speed' => 850,
                    'stamina' => 720,
                    'power' => 680,
                    'guts' => 450,
                    'wit' => 590,
                ],
                'energy_level' => 75,
                'mood_status' => 'good',
                'current_turn' => 24,
                'total_turns' => 78,
                'character_name' => 'サイレンススズカ',
            ],
            'confidence' => 0.95,
            'extracted_at' => '2025-01-14T10:00:00Z',
        ];

        $result = $this->transformer->transformCharacterStats($extractedData);

        expect($result['stats'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit'])
            ->and($result['stats']['speed'])->toBe(850)
            ->and($result['energy_level'])->toBe(75)
            ->and($result['mood_status'])->toBe('good')
            ->and($result['current_turn'])->toBe(24)
            ->and($result['confidence'])->toBe(0.95);
    });

    it('normalizes missing stats to null', function () {
        $extractedData = [
            'data' => [
                'stats' => [
                    'speed' => 850,
                    'stamina' => 720,
                ],
            ],
            'confidence' => 0.5,
        ];

        $result = $this->transformer->transformCharacterStats($extractedData);

        expect($result['stats']['speed'])->toBe(850)
            ->and($result['stats']['stamina'])->toBe(720)
            ->and($result['stats']['power'])->toBeNull()
            ->and($result['stats']['guts'])->toBeNull()
            ->and($result['stats']['wit'])->toBeNull();
    });

    it('prepares character update data', function () {
        $transformedData = [
            'stats' => [
                'speed' => 850,
                'stamina' => 720,
                'power' => 680,
                'guts' => 450,
                'wit' => 590,
            ],
            'energy_level' => 75,
            'mood_status' => 'good',
        ];

        $result = $this->transformer->prepareCharacterUpdate($transformedData);

        expect($result)->toHaveKey('current_stats')
            ->and($result)->toHaveKey('energy_level')
            ->and($result)->toHaveKey('mood_status')
            ->and($result['energy_level'])->toBe(75)
            ->and($result['mood_status'])->toBe('good');

        $stats = json_decode($result['current_stats'], true);
        expect($stats['speed'])->toBe(850);
    });
});

describe('Training Session Transformation', function () {
    it('transforms training session correctly', function () {
        $extractedData = [
            'data' => [
                'training_type' => 'speed',
                'stat_gains' => [
                    'speed' => 45,
                    'power' => 20,
                ],
                'energy_cost' => 25,
                'has_skill_hint' => true,
                'has_friendship' => true,
                'has_spirit_burst' => false,
            ],
            'confidence' => 0.85,
        ];

        $result = $this->transformer->transformTrainingSession($extractedData);

        expect($result['training_type'])->toBe('speed')
            ->and($result['stat_gains']['speed'])->toBe(45)
            ->and($result['stat_gains']['power'])->toBe(20)
            ->and($result['energy_cost'])->toBe(25)
            ->and($result['has_skill_hint'])->toBeTrue()
            ->and($result['has_friendship'])->toBeTrue()
            ->and($result['has_spirit_burst'])->toBeFalse();
    });

    it('prepares training session creation data', function () {
        $transformedData = [
            'training_type' => 'speed',
            'stat_gains' => [
                'speed' => 45,
                'power' => 20,
                'stamina' => null,
                'guts' => null,
                'wit' => null,
            ],
            'energy_cost' => 25,
            'has_skill_hint' => true,
            'has_friendship' => false,
            'has_spirit_burst' => false,
            'current_turn' => 24,
        ];

        $result = $this->transformer->prepareTrainingSessionCreate(1, $transformedData);

        expect($result['career_id'])->toBe(1)
            ->and($result['training_type'])->toBe('speed')
            ->and($result['energy_cost'])->toBe(25)
            ->and($result['spirit_burst'])->toBeFalse()
            ->and($result['turn_number'])->toBe(24);

        $statGains = json_decode($result['stat_gains'], true);
        expect($statGains['speed'])->toBe(45);
    });
});

describe('Race Result Transformation', function () {
    it('transforms race result correctly', function () {
        $extractedData = [
            'data' => [
                'race_name' => '日本ダービー',
                'race_grade' => 'G1',
                'position' => 1,
                'distance' => 2400,
                'distance_category' => 'medium',
                'surface' => 'turf',
                'fans_gained' => 15000,
                'skill_points_gained' => 120,
                'outcome' => 'victory',
            ],
            'confidence' => 0.92,
        ];

        $result = $this->transformer->transformRaceResult($extractedData);

        expect($result['race_name'])->toBe('日本ダービー')
            ->and($result['race_grade'])->toBe('G1')
            ->and($result['position'])->toBe(1)
            ->and($result['distance'])->toBe(2400)
            ->and($result['surface'])->toBe('turf')
            ->and($result['outcome'])->toBe('victory');
    });

    it('prepares race creation data', function () {
        $transformedData = [
            'race_name' => '日本ダービー',
            'race_grade' => 'G1',
            'position' => 1,
            'distance' => 2400,
            'distance_category' => 'medium',
            'surface' => 'turf',
            'fans_gained' => 15000,
            'skill_points_gained' => 120,
            'outcome' => 'victory',
        ];

        $result = $this->transformer->prepareRaceCreate(1, $transformedData);

        expect($result['career_id'])->toBe(1)
            ->and($result['race_name'])->toBe('日本ダービー')
            ->and($result['race_grade'])->toBe('G1')
            ->and($result['distance'])->toBe(2400)
            ->and($result['surface'])->toBe('turf')
            ->and($result['final_position'])->toBe(1);

        $performance = json_decode($result['performance'], true);
        expect($performance['outcome'])->toBe('victory')
            ->and($performance['fans_gained'])->toBe(15000);
    });
});

describe('Skill List Transformation', function () {
    it('transforms skill list correctly', function () {
        $extractedData = [
            'data' => [
                'total_sp' => 450,
                'skills' => [
                    [
                        'name' => 'スキル1',
                        'sp_cost' => 120,
                        'hint_level' => 2,
                        'is_acquired' => false,
                        'skill_type' => 'speed',
                    ],
                    [
                        'name' => 'スキル2',
                        'sp_cost' => 180,
                        'hint_level' => 0,
                        'is_acquired' => true,
                        'skill_type' => 'recovery',
                    ],
                ],
                'skill_count' => 2,
            ],
            'confidence' => 0.88,
        ];

        $result = $this->transformer->transformSkillList($extractedData);

        expect($result['total_sp'])->toBe(450)
            ->and($result['skill_count'])->toBe(2)
            ->and($result['skills'])->toHaveCount(2)
            ->and($result['skills'][0]['name'])->toBe('スキル1')
            ->and($result['skills'][0]['sp_cost'])->toBe(120)
            ->and($result['skills'][1]['is_acquired'])->toBeTrue();
    });

    it('normalizes skills with missing fields', function () {
        $extractedData = [
            'data' => [
                'skills' => [
                    [
                        'name' => 'IncompleteSkill',
                        'sp_cost' => 120,
                    ],
                ],
            ],
        ];

        $result = $this->transformer->transformSkillList($extractedData);

        expect($result['skills'][0]['hint_level'])->toBe(0)
            ->and($result['skills'][0]['is_acquired'])->toBeFalse()
            ->and($result['skills'][0]['skill_type'])->toBe('unknown');
    });
});
