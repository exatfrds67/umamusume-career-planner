<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\OCR\DataIntegrationService;
use App\Services\OCR\DataTransformationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->transformer = new DataTransformationService;
    $this->service = new DataIntegrationService($this->transformer);

    // Create test user and character
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
    ]);
    $this->career = Career::factory()->create([
        'character_id' => $this->character->id,
    ]);
});

describe('Character Stats Import', function () {
    it('imports character stats successfully', function () {
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
            ],
            'confidence' => 0.95,
            'extracted_at' => now()->toIso8601String(),
        ];

        $result = $this->service->importCharacterStats($this->character->id, $extractedData);

        expect($result['success'])->toBeTrue();
        expect($result['character_id'])->toBe($this->character->id);
        expect($result['updated_fields'])->toContain('current_stats');
        expect($result['updated_fields'])->toContain('energy_level');
        expect($result['updated_fields'])->toContain('mood_status');

        $this->character->refresh();
        expect($this->character->energy_level)->toBe(75);
        expect($this->character->mood_status)->toBe('good');

        $stats = json_decode($this->character->current_stats, true);
        expect($stats['speed'])->toBe(850);
    });

    it('handles character not found', function () {
        $extractedData = [
            'data' => ['stats' => ['speed' => 850]],
            'confidence' => 0.95,
        ];

        $result = $this->service->importCharacterStats(99999, $extractedData);

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Failed to import character stats');
    });

    it('handles empty data gracefully', function () {
        $extractedData = [
            'data' => [],
            'confidence' => 0.0,
        ];

        $result = $this->service->importCharacterStats($this->character->id, $extractedData);

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('No valid data to update');
    });
});

describe('Training Session Import', function () {
    it('imports training session successfully', function () {
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
            'extracted_at' => now()->toIso8601String(),
        ];

        $result = $this->service->importTrainingSession($this->career->id, $extractedData);

        expect($result['success'])->toBeTrue();
        expect($result['training_session_id'])->toBeInt();

        $trainingSession = TrainingSession::find($result['training_session_id']);
        expect($trainingSession)->not->toBeNull();
        expect($trainingSession->career_id)->toBe($this->career->id);
        expect($trainingSession->training_type)->toBe('speed');
        expect($trainingSession->energy_cost)->toBe(25);
        expect($trainingSession->spirit_burst)->toBeFalse();

        $statGains = json_decode($trainingSession->stat_gains, true);
        expect($statGains['speed'])->toBe(45);
    });

    it('handles career not found', function () {
        $extractedData = [
            'data' => ['training_type' => 'speed'],
            'confidence' => 0.85,
        ];

        $result = $this->service->importTrainingSession(99999, $extractedData);

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Failed to import training session');
    });
});

describe('Race Result Import', function () {
    it('imports race result successfully', function () {
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
            'extracted_at' => now()->toIso8601String(),
        ];

        $result = $this->service->importRaceResult($this->career->id, $extractedData);

        expect($result['success'])->toBeTrue();
        expect($result['race_id'])->toBeInt();

        $race = Race::find($result['race_id']);
        expect($race)->not->toBeNull();
        expect($race->career_id)->toBe($this->career->id);
        expect($race->race_name)->toBe('日本ダービー');
        expect($race->race_grade)->toBe('G1');
        expect($race->distance)->toBe(2400);
        expect($race->surface)->toBe('turf');
        expect($race->final_position)->toBe(1);

        $performance = json_decode($race->performance, true);
        expect($performance['outcome'])->toBe('victory');
        expect($performance['fans_gained'])->toBe(15000);
    });
});

describe('Skill List Import', function () {
    it('imports skill list successfully', function () {
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
            'extracted_at' => now()->toIso8601String(),
        ];

        $result = $this->service->importSkillList($this->character->id, $extractedData);

        expect($result['success'])->toBeTrue();
        expect($result['skills_processed'])->toBe(2);

        $skills = Skill::where('character_id', $this->character->id)->get();
        expect($skills)->toHaveCount(2);

        $skill1 = $skills->firstWhere('skill_name', 'スキル1');
        expect($skill1)->not->toBeNull();
        expect($skill1->sp_cost)->toBe(120);
        expect($skill1->hint_count)->toBe(2);
        expect($skill1->is_acquired)->toBeFalse();

        $skill2 = $skills->firstWhere('skill_name', 'スキル2');
        expect($skill2)->not->toBeNull();
        expect($skill2->is_acquired)->toBeTrue();
    });

    it('updates existing skills', function () {
        // Create existing skill
        $existingSkill = Skill::create([
            'character_id' => $this->character->id,
            'skill_name' => 'ExistingSkill',
            'skill_type' => 'speed',
            'sp_cost' => 100,
            'hint_count' => 0,
            'is_acquired' => false,
        ]);

        $extractedData = [
            'data' => [
                'skills' => [
                    [
                        'name' => 'ExistingSkill',
                        'sp_cost' => 120,
                        'hint_level' => 2,
                        'is_acquired' => true,
                        'skill_type' => 'speed',
                    ],
                ],
            ],
            'confidence' => 0.88,
        ];

        $result = $this->service->importSkillList($this->character->id, $extractedData);

        expect($result['success'])->toBeTrue();

        $existingSkill->refresh();
        expect($existingSkill->sp_cost)->toBe(120);
        expect($existingSkill->hint_count)->toBe(2);
        expect($existingSkill->is_acquired)->toBeTrue();
    });

    it('skips skills with empty names', function () {
        $extractedData = [
            'data' => [
                'skills' => [
                    ['name' => '', 'sp_cost' => 120],
                    ['name' => 'ValidSkill', 'sp_cost' => 150],
                ],
            ],
            'confidence' => 0.5,
        ];

        $result = $this->service->importSkillList($this->character->id, $extractedData);

        expect($result['success'])->toBeTrue();
        expect($result['skills_processed'])->toBe(1);

        $skills = Skill::where('character_id', $this->character->id)->get();
        expect($skills)->toHaveCount(1);
        expect($skills->first()->skill_name)->toBe('ValidSkill');
    });
});

describe('Batch Import', function () {
    it('processes multiple imports successfully', function () {
        $imports = [
            [
                'screen_type' => 'character_stats',
                'target_id' => $this->character->id,
                'extracted_data' => [
                    'data' => [
                        'stats' => ['speed' => 850],
                        'energy_level' => 75,
                    ],
                    'confidence' => 0.95,
                ],
            ],
            [
                'screen_type' => 'training_session',
                'target_id' => $this->career->id,
                'extracted_data' => [
                    'data' => [
                        'training_type' => 'speed',
                        'stat_gains' => ['speed' => 45],
                        'energy_cost' => 25,
                    ],
                    'confidence' => 0.85,
                ],
            ],
        ];

        $result = $this->service->batchImport($imports);

        expect($result['success'])->toBeTrue();
        expect($result['processed'])->toBe(2);
        expect($result['failed'])->toBe(0);
        expect($result['results'])->toHaveCount(2);
    });

    it('handles partial failures in batch', function () {
        $imports = [
            [
                'screen_type' => 'character_stats',
                'target_id' => 99999, // Invalid ID
                'extracted_data' => [
                    'data' => ['stats' => ['speed' => 850]],
                    'confidence' => 0.95,
                ],
            ],
            [
                'screen_type' => 'training_session',
                'target_id' => $this->career->id,
                'extracted_data' => [
                    'data' => [
                        'training_type' => 'speed',
                        'stat_gains' => ['speed' => 45],
                        'energy_cost' => 25,
                    ],
                    'confidence' => 0.85,
                ],
            ],
        ];

        $result = $this->service->batchImport($imports);

        expect($result['success'])->toBeFalse();
        expect($result['processed'])->toBe(1);
        expect($result['failed'])->toBe(1);
    });
});
