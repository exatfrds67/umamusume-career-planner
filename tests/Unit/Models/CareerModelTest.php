<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

describe('Career Model', function (): void {
    describe('relationships', function (): void {
        it('belongs to a character', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            expect($career->character)->toBeInstanceOf(Character::class)
                ->and($career->character->id)->toBe($character->id);
        });

        it('has many training sessions', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(10)->create(['career_id' => $career->id]);

            expect($career->trainingSessions)->toHaveCount(10)
                ->and($career->trainingSessions->first())->toBeInstanceOf(TrainingSession::class);
        });

        it('has many races', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->count(5)->create(['career_id' => $career->id]);

            expect($career->races)->toHaveCount(5)
                ->and($career->races->first())->toBeInstanceOf(Race::class);
        });
    });

    describe('attributes', function (): void {
        it('has valid scenario type', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
            ]);

            expect($career->scenario_type)->toBeIn(['ura_finale', 'unity_cup']);
        });

        it('has valid status', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'active',
            ]);

            expect($career->status)->toBeIn(['planning', 'active', 'completed', 'abandoned']);
        });

        it('tracks current turn', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'current_turn' => 30,
            ]);

            expect($career->current_turn)->toBe(30)
                ->and($career->current_turn)->toBeGreaterThanOrEqual(0)
                ->and($career->current_turn)->toBeLessThanOrEqual(78);
        });
    });

    describe('scopes', function (): void {
        it('filters active careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Career::factory()->count(2)->create([
                'character_id' => $character->id,
                'status' => 'active',
            ]);

            Career::factory()->count(3)->create([
                'character_id' => $character->id,
                'status' => 'completed',
            ]);

            $activeCareers = Career::where('status', 'active')->get();

            expect($activeCareers)->toHaveCount(2);
        });

        it('filters completed careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Career::factory()->count(2)->create([
                'character_id' => $character->id,
                'status' => 'active',
            ]);

            Career::factory()->count(3)->create([
                'character_id' => $character->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $completedCareers = Career::where('status', 'completed')->get();

            expect($completedCareers)->toHaveCount(3);
        });
    });

    describe('timestamps', function (): void {
        it('tracks started_at timestamp', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'started_at' => now(),
            ]);

            expect($career->started_at)->not->toBeNull();
        });

        it('tracks completed_at timestamp', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            expect($career->completed_at)->not->toBeNull();
        });
    });

    describe('performance analysis', function (): void {
        it('stores performance analysis as JSON', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $analysis = [
                'final_stats' => [
                    'speed' => 800,
                    'stamina' => 700,
                    'power' => 600,
                    'guts' => 500,
                    'wit' => 400,
                ],
                'goals_achieved' => true,
            ];

            $career = Career::factory()->create([
                'character_id' => $character->id,
                'performance_analysis' => $analysis,
            ]);

            expect($career->performance_analysis)->toBeArray()
                ->and($career->performance_analysis['final_stats']['speed'])->toBe(800)
                ->and($career->performance_analysis['goals_achieved'])->toBeTrue();
        });
    });

    describe('factory', function (): void {
        it('creates valid career with factory', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            expect($career)->toBeInstanceOf(Career::class)
                ->and($career->character_id)->toBe($character->id);
        });
    });
});
