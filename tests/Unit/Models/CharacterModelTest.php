<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Career;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Factor;
use App\Models\SkillAcquisition;
use App\Models\User;

describe('Character Model', function (): void {
    describe('relationships', function (): void {
        it('belongs to a user', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            expect($character->user)->toBeInstanceOf(User::class)
                ->and($character->user->id)->toBe($user->id);
        });

        it('has many careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Career::factory()->count(3)->create(['character_id' => $character->id]);

            expect($character->careers)->toHaveCount(3)
                ->and($character->careers->first())->toBeInstanceOf(Career::class);
        });

        it('has many aptitudes', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Aptitude::factory()->count(2)->create(['character_id' => $character->id]);

            expect($character->aptitudes)->toHaveCount(2)
                ->and($character->aptitudes->first())->toBeInstanceOf(Aptitude::class);
        });

        it('has many factors', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            Factor::factory()->count(5)->create(['character_id' => $character->id]);

            expect($character->factors)->toHaveCount(5)
                ->and($character->factors->first())->toBeInstanceOf(Factor::class);
        });

        it('has many skill acquisitions', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            SkillAcquisition::factory()->count(4)->create(['character_id' => $character->id]);

            expect($character->skillAcquisitions)->toHaveCount(4)
                ->and($character->skillAcquisitions->first())->toBeInstanceOf(SkillAcquisition::class);
        });

        it('has many support cards through pivot', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            CharacterSupportCard::factory()->count(6)->create(['character_id' => $character->id]);

            expect($character->characterSupportCards)->toHaveCount(6)
                ->and($character->characterSupportCards->first())->toBeInstanceOf(CharacterSupportCard::class);
        });
    });

    describe('attributes', function (): void {
        it('has valid scenario type', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'scenario_type' => 'ura_finale',
            ]);

            expect($character->scenario_type)->toBeIn(['ura_finale', 'unity_cup']);
        });

        it('has valid stat values', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'speed_stat' => 500,
                'stamina_stat' => 400,
                'power_stat' => 300,
                'guts_stat' => 200,
                'wit_stat' => 100,
            ]);

            expect($character->speed_stat)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1200);
            expect($character->stamina_stat)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1200);
            expect($character->power_stat)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1200);
            expect($character->guts_stat)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1200);
            expect($character->wit_stat)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1200);
        });

        it('has valid energy level', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'energy_level' => 75,
            ]);

            expect($character->energy_level)->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        });

        it('has valid mood status', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'mood_status' => 'good',
            ]);

            expect($character->mood_status)->toBeIn(['awful', 'bad', 'normal', 'good', 'great']);
        });
    });

    describe('scopes', function (): void {
        it('filters by user', function (): void {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            Character::factory()->count(3)->create(['user_id' => $user1->id]);
            Character::factory()->count(2)->create(['user_id' => $user2->id]);

            $characters = Character::where('user_id', $user1->id)->get();

            expect($characters)->toHaveCount(3);
        });

        it('filters by scenario type', function (): void {
            $user = User::factory()->create();

            Character::factory()->count(2)->create([
                'user_id' => $user->id,
                'scenario_type' => 'ura_finale',
            ]);

            Character::factory()->count(3)->create([
                'user_id' => $user->id,
                'scenario_type' => 'unity_cup',
            ]);

            $uraCharacters = Character::where('scenario_type', 'ura_finale')->get();

            expect($uraCharacters)->toHaveCount(2);
        });
    });

    describe('accessors', function (): void {
        it('calculates total stats', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'speed_stat' => 100,
                'stamina_stat' => 100,
                'power_stat' => 100,
                'guts_stat' => 100,
                'wit_stat' => 100,
            ]);

            $totalStats = $character->speed_stat
                + $character->stamina_stat
                + $character->power_stat
                + $character->guts_stat
                + $character->wit_stat;

            expect($totalStats)->toBe(500);
        });
    });

    describe('factory', function (): void {
        it('creates valid character with factory', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            expect($character)->toBeInstanceOf(Character::class)
                ->and($character->name)->not->toBeEmpty()
                ->and($character->user_id)->toBe($user->id);
        });

        it('creates character with specific scenario', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'scenario_type' => 'unity_cup',
            ]);

            expect($character->scenario_type)->toBe('unity_cup');
        });
    });
});
