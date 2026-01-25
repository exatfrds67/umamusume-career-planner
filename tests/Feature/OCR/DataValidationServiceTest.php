<?php

declare(strict_types=1);

use App\Services\OCR\DataValidationService;

beforeEach(function () {
    $this->validator = new DataValidationService;
});

describe('Character Stats Validation', function () {
    it('validates valid character stats', function () {
        $data = [
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
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('detects invalid stat values', function () {
        $data = [
            'stats' => [
                'speed' => 1500,
                'stamina' => -100,
                'power' => 680,
            ],
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid speed value: 1500 (must be 0-1200)')
            ->and($result['errors'])->toContain('Invalid stamina value: -100 (must be 0-1200)');
    });

    it('warns about low stats', function () {
        $data = [
            'stats' => [
                'speed' => 50,
                'stamina' => 80,
            ],
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['warnings'])->toContain('Unusually low speed value: 50')
            ->and($result['warnings'])->toContain('Unusually low stamina value: 80');
    });

    it('warns about near-maximum stats', function () {
        $data = [
            'stats' => [
                'speed' => 1150,
            ],
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['warnings'])->toContain('Near-maximum speed value: 1150');
    });

    it('validates energy level range', function () {
        $data = [
            'stats' => ['speed' => 850],
            'energy_level' => 150,
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid energy level: 150 (must be 0-100)');
    });

    it('warns about low energy', function () {
        $data = [
            'stats' => ['speed' => 850],
            'energy_level' => 20,
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['warnings'])->toContain('Low energy level: 20%');
    });

    it('validates mood status', function () {
        $data = [
            'stats' => ['speed' => 850],
            'mood_status' => 'invalid_mood',
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid mood status: invalid_mood (must be one of: great, good, normal, bad, awful)');
    });

    it('validates turn numbers', function () {
        $data = [
            'stats' => ['speed' => 850],
            'current_turn' => 50,
            'total_turns' => 30,
        ];

        $result = $this->validator->validateCharacterStats($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Current turn (50) exceeds total turns (30)');
    });
});

describe('Training Session Validation', function () {
    it('validates valid training session', function () {
        $data = [
            'training_type' => 'speed',
            'stat_gains' => [
                'speed' => 45,
                'power' => 20,
            ],
            'energy_cost' => 25,
            'has_skill_hint' => true,
        ];

        $result = $this->validator->validateTrainingSession($data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('detects invalid training type', function () {
        $data = [
            'training_type' => 'invalid_type',
        ];

        $result = $this->validator->validateTrainingSession($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid training type: invalid_type (must be one of: speed, stamina, power, guts, wit, rest, infirmary, outing)');
    });

    it('validates stat gain ranges', function () {
        $data = [
            'training_type' => 'speed',
            'stat_gains' => [
                'speed' => 250,
                'power' => -10,
            ],
        ];

        $result = $this->validator->validateTrainingSession($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid stat gain for speed: 250 (must be 0-200)')
            ->and($result['errors'])->toContain('Invalid stat gain for power: -10 (must be 0-200)');
    });

    it('warns about high stat gains', function () {
        $data = [
            'training_type' => 'speed',
            'stat_gains' => [
                'speed' => 150,
            ],
        ];

        $result = $this->validator->validateTrainingSession($data);

        expect($result['warnings'])->toContain('Unusually high speed gain: 150');
    });

    it('warns about high energy cost', function () {
        $data = [
            'training_type' => 'speed',
            'energy_cost' => 80,
        ];

        $result = $this->validator->validateTrainingSession($data);

        expect($result['warnings'])->toContain('High energy cost: 80%');
    });
});

describe('Race Result Validation', function () {
    it('validates valid race result', function () {
        $data = [
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

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('validates position range', function () {
        $data = [
            'position' => 25,
        ];

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid position: 25 (must be 1-18)');
    });

    it('validates race grade', function () {
        $data = [
            'race_grade' => 'G5',
        ];

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid race grade: G5 (must be one of: G1, G2, G3, OP, Pre-OP)');
    });

    it('validates distance range', function () {
        $data = [
            'distance' => 500,
        ];

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid distance: 500 (must be 1000-3600m)');
    });

    it('validates surface type', function () {
        $data = [
            'surface' => 'grass',
        ];

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid surface: grass (must be one of: turf, dirt)');
    });

    it('validates fans gained', function () {
        $data = [
            'fans_gained' => -1000,
        ];

        $result = $this->validator->validateRaceResult($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid fans gained: -1000 (must be 0-999999)');
    });
});

describe('Skill List Validation', function () {
    it('validates valid skill list', function () {
        $data = [
            'total_sp' => 450,
            'skills' => [
                [
                    'name' => 'スキル1',
                    'sp_cost' => 120,
                    'hint_level' => 2,
                    'is_acquired' => false,
                ],
                [
                    'name' => 'スキル2',
                    'sp_cost' => 180,
                    'hint_level' => 0,
                    'is_acquired' => true,
                ],
            ],
            'skill_count' => 2,
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('validates total SP range', function () {
        $data = [
            'total_sp' => 150000,
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Invalid total SP: 150000 (must be 0-99999)');
    });

    it('validates skill SP cost', function () {
        $data = [
            'skills' => [
                [
                    'name' => 'ExpensiveSkill',
                    'sp_cost' => 600,
                ],
            ],
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Skill #0: Invalid SP cost 600 (must be 0-500)');
    });

    it('validates hint level range', function () {
        $data = [
            'skills' => [
                [
                    'name' => 'TestSkill',
                    'sp_cost' => 120,
                    'hint_level' => 10,
                ],
            ],
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Skill #0: Invalid hint level 10 (must be 0-5)');
    });

    it('detects missing skill name', function () {
        $data = [
            'skills' => [
                [
                    'sp_cost' => 120,
                ],
            ],
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Skill #0: Missing skill name');
    });

    it('warns about skill count mismatch', function () {
        $data = [
            'skills' => [
                ['name' => 'Skill1', 'sp_cost' => 120],
                ['name' => 'Skill2', 'sp_cost' => 150],
            ],
            'skill_count' => 5,
        ];

        $result = $this->validator->validateSkillList($data);

        expect($result['warnings'])->toContain('Skill count mismatch: reported 5, found 2');
    });
});

describe('General Validation', function () {
    it('handles unknown screen type', function () {
        $result = $this->validator->validate([], 'unknown_type');

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Unknown screen type: unknown_type');
    });
});
