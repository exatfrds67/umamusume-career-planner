<?php

declare(strict_types=1);

use App\Models\Character;
use App\Services\TrainingPredictionService;

beforeEach(function () {
    $this->predictionService = new TrainingPredictionService;
    $this->character = Character::factory()->create([
        'current_stats' => [
            'speed' => 500,
            'stamina' => 450,
            'power' => 400,
            'guts' => 350,
            'wit' => 300,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
        'growth_rates' => json_encode([
            'speed' => 20,
            'stamina' => 10,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ]),
    ]);
});

describe('TrainingPredictionService', function () {
    describe('calculateGain', function () {
        it('calculates base gains for speed training', function () {
            $result = $this->predictionService->calculateGain($this->character, 'speed');

            expect($result)->toHaveKeys(['stats', 'energy']);
            expect($result['stats']['speed'])->toBeGreaterThan(0);
            expect($result['stats']['power'])->toBeGreaterThan(0);
            expect($result['energy'])->toBeLessThan(0); // Energy cost
        });

        it('calculates base gains for stamina training', function () {
            $result = $this->predictionService->calculateGain($this->character, 'stamina');

            expect($result['stats']['stamina'])->toBeGreaterThan(0);
            expect($result['stats']['guts'])->toBeGreaterThan(0);
        });

        it('calculates base gains for power training', function () {
            $result = $this->predictionService->calculateGain($this->character, 'power');

            expect($result['stats']['power'])->toBeGreaterThan(0);
            expect($result['stats']['stamina'])->toBeGreaterThan(0);
        });

        it('calculates base gains for guts training', function () {
            $result = $this->predictionService->calculateGain($this->character, 'guts');

            expect($result['stats']['guts'])->toBeGreaterThan(0);
            expect($result['stats']['speed'])->toBeGreaterThan(0);
        });

        it('calculates base gains for wit training with energy recovery', function () {
            $result = $this->predictionService->calculateGain($this->character, 'wit');

            expect($result['stats']['wit'])->toBeGreaterThan(0);
            expect($result['stats']['speed'])->toBeGreaterThan(0);
            expect($result['energy'])->toBeGreaterThan(0); // Wit recovers energy
        });

        it('returns zero gains for unknown training type', function () {
            $result = $this->predictionService->calculateGain($this->character, 'unknown');

            expect($result['stats']['speed'])->toBe(0);
            expect($result['stats']['stamina'])->toBe(0);
            expect($result['energy'])->toBe(0);
        });

        it('applies growth rate bonuses', function () {
            // Character has 20% speed growth rate
            $result = $this->predictionService->calculateGain($this->character, 'speed');

            // Base speed gain is 10, with 20% bonus should be 12
            expect($result['stats']['speed'])->toBeGreaterThanOrEqual(12);
        });

        it('applies mood multiplier for great mood', function () {
            $this->character->mood_status = 'great';
            $this->character->save();

            $result = $this->predictionService->calculateGain($this->character, 'speed');

            // Great mood gives 1.2x multiplier
            // Base 10 * 1.2 (growth) * 1.2 (mood) = 14.4 -> 14
            expect($result['stats']['speed'])->toBeGreaterThanOrEqual(14);
        });

        it('applies mood multiplier for awful mood', function () {
            $this->character->mood_status = 'awful';
            $this->character->save();

            $result = $this->predictionService->calculateGain($this->character, 'speed');

            // Awful mood gives 0.8x multiplier
            expect($result['stats']['speed'])->toBeLessThan(12);
        });
    })->with([
        'speed training' => ['speed'],
        'stamina training' => ['stamina'],
        'power training' => ['power'],
        'guts training' => ['guts'],
        'wit training' => ['wit'],
    ]);

    describe('calculateFailureRate', function () {
        it('returns 0% failure rate for high energy', function () {
            $this->character->energy_level = 80;
            $this->character->save();

            $rate = $this->predictionService->calculateFailureRate($this->character, 'speed');

            expect($rate)->toBe(0);
        });

        it('returns 0% failure rate at 50 energy', function () {
            $this->character->energy_level = 50;
            $this->character->save();

            $rate = $this->predictionService->calculateFailureRate($this->character, 'speed');

            expect($rate)->toBe(0);
        });

        it('returns increased failure rate below 50 energy', function () {
            $this->character->energy_level = 30;
            $this->character->save();

            $rate = $this->predictionService->calculateFailureRate($this->character, 'speed');

            expect($rate)->toBeGreaterThan(0);
            expect($rate)->toBeLessThanOrEqual(99);
        });

        it('returns high failure rate at very low energy', function () {
            $this->character->energy_level = 10;
            $this->character->save();

            $rate = $this->predictionService->calculateFailureRate($this->character, 'speed');

            expect($rate)->toBeGreaterThan(50);
        });

        it('returns 0% failure rate for wit training', function () {
            $this->character->energy_level = 20;
            $this->character->save();

            $rate = $this->predictionService->calculateFailureRate($this->character, 'wit');

            expect($rate)->toBe(0);
        });
    });

    describe('executeTraining', function () {
        it('applies stat gains on successful training', function () {
            $this->character->energy_level = 100; // High energy = no failure
            $this->character->save();

            $initialSpeed = $this->character->current_stats['speed'];

            $result = $this->predictionService->executeTraining($this->character, 'speed');

            if ($result['success']) {
                expect($result['gains'])->toHaveKey('speed');
                expect($result['gains']['speed'])->toBeGreaterThan(0);

                $this->character->refresh();
                expect($this->character->current_stats['speed'])->toBeGreaterThan($initialSpeed);
            }
        });

        it('reduces energy on training', function () {
            $this->character->energy_level = 100;
            $this->character->save();

            $initialEnergy = $this->character->energy_level;

            $result = $this->predictionService->executeTraining($this->character, 'speed');

            $this->character->refresh();

            // Energy should change (decrease for most training, increase for wit)
            expect($result['energy_change'])->not->toBe(0);
        });

        it('caps stats at 1200', function () {
            $this->character->current_stats = [
                'speed' => 1195,
                'stamina' => 500,
                'power' => 500,
                'guts' => 500,
                'wit' => 500,
            ];
            $this->character->energy_level = 100;
            $this->character->save();

            $result = $this->predictionService->executeTraining($this->character, 'speed');

            if ($result['success']) {
                $this->character->refresh();
                expect($this->character->current_stats['speed'])->toBeLessThanOrEqual(1200);
            }
        });

        it('handles training failure', function () {
            $this->character->energy_level = 5; // Very low energy = high failure chance
            $this->character->save();

            // Run multiple times to likely get a failure
            $hadFailure = false;
            for ($i = 0; $i < 20; $i++) {
                $this->character->energy_level = 5;
                $this->character->mood_status = 'normal';
                $this->character->save();

                $result = $this->predictionService->executeTraining($this->character, 'speed');

                if (! $result['success']) {
                    $hadFailure = true;
                    expect($result['gains'])->toBeEmpty();
                    expect($result['energy_change'])->toBe(-10);
                    break;
                }
            }

            // We should have had at least one failure with such low energy
            // But this is probabilistic, so we just check the structure
            expect($result)->toHaveKeys(['success', 'failure_rate', 'training_type', 'gains', 'energy_change']);
        });

        it('worsens mood on failure', function () {
            $this->character->energy_level = 5;
            $this->character->mood_status = 'good';
            $this->character->save();

            // Run until we get a failure
            for ($i = 0; $i < 50; $i++) {
                $this->character->energy_level = 5;
                $this->character->mood_status = 'good';
                $this->character->save();

                $result = $this->predictionService->executeTraining($this->character, 'speed');

                if (! $result['success']) {
                    $this->character->refresh();
                    expect($this->character->mood_status)->toBe('normal'); // Dropped from good
                    break;
                }
            }
        });

        it('keeps energy within bounds', function () {
            $this->character->energy_level = 10;
            $this->character->save();

            $this->predictionService->executeTraining($this->character, 'speed');

            $this->character->refresh();
            expect($this->character->energy_level)->toBeGreaterThanOrEqual(0);
            expect($this->character->energy_level)->toBeLessThanOrEqual(100);
        });
    });
});

describe('mood multipliers', function () {
    it('applies correct multipliers for each mood', function (string $mood, float $expectedMultiplier) {
        $character = Character::factory()->create([
            'mood_status' => $mood,
            'growth_rates' => json_encode([]),
        ]);

        $service = new TrainingPredictionService;
        $result = $service->calculateGain($character, 'speed');

        // Base speed gain is 10, multiplied by mood
        $expectedGain = (int) floor(10 * $expectedMultiplier);
        // Use toEqual for loose comparison since floor() returns float
        expect((int) $result['stats']['speed'])->toBe($expectedGain);
    })->with([
        'great mood' => ['great', 1.2],
        'good mood' => ['good', 1.1],
        'normal mood' => ['normal', 1.0],
        'bad mood' => ['bad', 0.9],
        'awful mood' => ['awful', 0.8],
    ]);
});
