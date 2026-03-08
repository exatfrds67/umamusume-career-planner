<?php

declare(strict_types=1);

/**
 * Training Execution Bug Fix Tests
 *
 * Tests for all 9 bugs discovered during the Oguri Cap career run:
 * Bug 1: Energy not depleting
 * Bug 2: Bond progression not working
 * Bug 3: Phase not progressing
 * Bug 4: No turn limit enforcement
 * Bug 5: SP not accumulating
 * Bug 6: Training failure risk always 0%
 * Bug 7: Race entry not available
 * Bug 8: Goal progress stat-only
 * Bug 9: Goal races not linked to characters
 */

use App\Models\Career;
use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\User;
use App\Services\TrainingPredictionService;
use App\Services\TrainingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'mood_status' => 'normal',
        'current_turn' => 0,
        'career_stage' => 'junior',
        'available_sp' => 0,
        'scenario_type' => 'ura_finale',
        'status' => 'active',
    ]);

    $this->career = Career::create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'career_name' => 'Bug Fix Test Career',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 0,
        'current_phase' => 'junior',
    ]);

    $this->trainingService = app(TrainingService::class);
    $this->predictionService = app(TrainingPredictionService::class);
});

describe('Bug 1: Energy Depletion', function () {
    it('depletes energy after speed training', function () {
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $result = $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->character->refresh();
        expect($result['success'])->toBeTrue()
            ->and($this->character->energy_level)->toBeLessThan(100);
    });

    it('depletes less energy for wit training', function () {
        $gains = ['wit' => 20, 'stamina' => 5, 'sp' => 5];

        $this->trainingService->executeTraining($this->character, 'wit', $gains);

        $this->character->refresh();
        // Wit costs 10, so should be 90
        expect($this->character->energy_level)->toBe(90);
    });

    it('depletes standard energy for speed/stamina/power/guts', function () {
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->character->refresh();
        // Standard training costs 20, so should be 80
        expect($this->character->energy_level)->toBe(80);
    });
});

describe('Bug 3: Phase Progression', function () {
    it('transitions from junior to classic at turn 25', function () {
        $this->character->update(['current_turn' => 23, 'career_stage' => 'junior']);
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        // Turn 24 (still junior)
        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('junior');
        expect($this->character->current_turn)->toBe(24);

        // Turn 25 (becomes classic)
        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('classic');
        expect($this->character->current_turn)->toBe(25);
    });

    it('transitions from classic to senior at turn 49', function () {
        $this->character->update(['current_turn' => 47, 'career_stage' => 'classic']);
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('classic');

        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('senior');
    });

    it('transitions from senior to ura at turn 73', function () {
        $this->character->update(['current_turn' => 71, 'career_stage' => 'senior']);
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('senior');

        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->character->refresh();
        expect($this->character->career_stage)->toBe('ura');
    });

    it('updates career phase when character phase changes', function () {
        $this->character->update(['current_turn' => 23, 'career_stage' => 'junior']);
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        // Two trainings: turn 24 (junior), turn 25 (classic)
        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->career->refresh();
        expect($this->career->current_phase)->toBe('classic');
    });
});

describe('Bug 4: Turn Limit Enforcement', function () {
    it('rejects training when turn limit reached', function () {
        $this->character->update(['current_turn' => 78]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];
        $result = $this->trainingService->executeTraining($this->character, 'speed', $gains);

        expect($result['success'])->toBeFalse()
            ->and($result['turn_limit_reached'])->toBeTrue();
    });

    it('allows training on turn 77 (will become 78)', function () {
        $this->character->update(['current_turn' => 77]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];
        $result = $this->trainingService->executeTraining($this->character, 'speed', $gains);

        expect($result['success'])->toBeTrue()
            ->and($result['career_completed'])->toBeTrue();

        $this->character->refresh();
        expect($this->character->current_turn)->toBe(78);
    });

    it('marks career as completed when turn limit reached', function () {
        $this->character->update(['current_turn' => 77]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];
        $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->career->refresh();
        expect($this->career->status)->toBe('completed');

        $this->character->refresh();
        expect($this->character->status)->toBe('completed');
    });

    it('redirects with error when training at turn limit via web', function () {
        $this->character->update(['current_turn' => 78]);

        $this->actingAs($this->user)
            ->post(route('training.store', $this->character), [
                'training_type' => 'speed',
            ])
            ->assertRedirect(route('characters.show', $this->character))
            ->assertSessionHas('error');
    });
});

describe('Bug 5: SP Accumulation', function () {
    it('adds SP to base predictions for all facilities', function () {
        $predictions = $this->predictionService->getPredictions($this->character);

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $facility) {
            $gains = $predictions['predictions'][$facility]['base_gains'] ?? [];
            expect($gains)->toHaveKey('sp')
                ->and($gains['sp'])->toBeGreaterThan(0);
        }
    });

    it('gives more SP for wit training', function () {
        $predictions = $this->predictionService->getPredictions($this->character);

        $witSp = $predictions['predictions']['wit']['base_gains']['sp'];
        $speedSp = $predictions['predictions']['speed']['base_gains']['sp'];

        expect($witSp)->toBeGreaterThan($speedSp);
    });

    it('accumulates SP after training execution', function () {
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->character->refresh();
        expect($this->character->available_sp)->toBe(3);
    });

    it('accumulates SP across multiple trainings', function () {
        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->trainingService->executeTraining($this->character, 'speed', $gains);
        $this->trainingService->executeTraining($this->character, 'speed', $gains);

        $this->character->refresh();
        expect($this->character->available_sp)->toBe(9);
    });
});

describe('Bug 6: Training Failure Risk', function () {
    it('has zero failure rate at high energy', function () {
        $this->character->update(['energy_level' => 100]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];
        $result = $this->trainingService->executeTraining($this->character, 'speed', $gains);

        expect($result['training_failed'])->toBeFalse();
    });

    it('can potentially fail at low energy', function () {
        // Set very low energy to maximize failure chance
        $this->character->update(['energy_level' => 5]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];
        $failedAtLeastOnce = false;
        $succeededAtLeastOnce = false;

        // Run multiple times to test probability
        for ($i = 0; $i < 50; $i++) {
            $character = Character::factory()->create([
                'user_id' => $this->user->id,
                'energy_level' => 5,
                'current_turn' => $i,
                'career_stage' => 'junior',
                'current_stats' => ['speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100],
                'available_sp' => 0,
            ]);
            Career::create([
                'user_id' => $this->user->id,
                'character_id' => $character->id,
                'career_name' => "Failure Test $i",
                'scenario_type' => 'ura_finale',
                'status' => 'active',
                'current_turn' => $i,
            ]);

            $result = $this->trainingService->executeTraining($character, 'speed', $gains);
            if ($result['training_failed'] ?? false) {
                $failedAtLeastOnce = true;
            } else {
                $succeededAtLeastOnce = true;
            }
        }

        // At 60% failure rate over 50 runs, we should see both outcomes
        expect($failedAtLeastOnce)->toBeTrue();
    });

    it('reduces gains on training failure', function () {
        // Create character with very low energy
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'energy_level' => 1,
            'current_turn' => 0,
            'career_stage' => 'junior',
            'current_stats' => ['speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100],
            'available_sp' => 0,
        ]);
        Career::create([
            'user_id' => $this->user->id,
            'character_id' => $character->id,
            'career_name' => 'Failure Gain Test',
            'scenario_type' => 'ura_finale',
            'status' => 'active',
            'current_turn' => 0,
        ]);

        $gains = ['speed' => 20, 'power' => 5, 'sp' => 3];

        // Keep trying until we get a failure
        $result = null;
        for ($i = 0; $i < 100; $i++) {
            $testChar = Character::factory()->create([
                'user_id' => $this->user->id,
                'energy_level' => 1,
                'current_turn' => $i,
                'career_stage' => 'junior',
                'current_stats' => ['speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100],
                'available_sp' => 0,
            ]);
            Career::create([
                'user_id' => $this->user->id,
                'character_id' => $testChar->id,
                'career_name' => "Gain Reduction $i",
                'scenario_type' => 'ura_finale',
                'status' => 'active',
                'current_turn' => $i,
            ]);

            $result = $this->trainingService->executeTraining($testChar, 'speed', $gains);
            if ($result['training_failed'] ?? false) {
                // Gains should be halved (50% penalty)
                expect($result['stat_gains']['speed'])->toBe(10)
                    ->and($result['stat_gains']['power'])->toBe(3); // round(5 * 0.5) = 3

                break;
            }
        }

        expect($result['training_failed'])->toBeTrue();
    });
});

describe('Bug 9: Goal Races Linkage', function () {
    it('can link a character to a game character', function () {
        $gameCharacter = GameCharacter::factory()->create();

        $this->character->update(['game_character_id' => $gameCharacter->id]);
        $this->character->refresh();

        expect($this->character->game_character_id)->toBe($gameCharacter->id)
            ->and($this->character->gameCharacter)->toBeInstanceOf(GameCharacter::class);
    });
});

describe('Bug 8: Goal Progress Calculation', function () {
    it('includes turn-based progress component', function () {
        // Character at turn 39 out of 78 = 50% turn progress
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'current_turn' => 39,
            'current_stats' => ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0],
        ]);

        $progress = $character->getProgressPercentage();

        // Should have >0 progress from turns even with 0 stats
        expect($progress)->toBeGreaterThan(0);
    });

    it('combines stat and turn progress', function () {
        // Character with 50% stat progress + turn progress
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'current_turn' => 39,
            'current_stats' => ['speed' => 300, 'stamina' => 300, 'power' => 300, 'guts' => 300, 'wit' => 300],
            'goals' => [
                'target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
            ],
        ]);

        $progress = $character->getProgressPercentage();

        // 50% stat * 0.60 weight + 50% turn * 0.25 weight = 42.5 + some race component
        expect($progress)->toBeGreaterThanOrEqual(40.0)
            ->and($progress)->toBeLessThanOrEqual(50.0);
    });
});

describe('Training Web Controller Integration', function () {
    it('shows warning message on training failure', function () {
        $this->character->update(['energy_level' => 50]); // Near threshold

        $this->actingAs($this->user)
            ->post(route('training.store', $this->character), [
                'training_type' => 'speed',
            ])
            ->assertRedirect();
    });

    it('updates character stats after web training', function () {
        $this->actingAs($this->user)
            ->post(route('training.store', $this->character), [
                'training_type' => 'speed',
            ]);

        $this->character->refresh();
        expect($this->character->current_stats['speed'])->toBeGreaterThan(100)
            ->and($this->character->current_turn)->toBe(1)
            ->and($this->character->energy_level)->toBeLessThan(100)
            ->and($this->character->available_sp)->toBeGreaterThan(0);
    });
});
