<?php

use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

describe('Admin User Authorization', function () {
    test('admin can view any character', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($admin)->allows('view', $character))->toBeTrue();
    });

    test('admin can update any character', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($admin)->allows('update', $character))->toBeTrue();
    });

    test('admin can delete any character', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($admin)->allows('delete', $character))->toBeTrue();
    });

    test('admin can restore any character', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($admin)->allows('restore', $character))->toBeTrue();
    });

    test('admin can force delete any character', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($admin)->allows('forceDelete', $character))->toBeTrue();
    });

    test('admin can view any characters', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);

        expect(Gate::forUser($admin)->allows('viewAny', Character::class))->toBeTrue();
    });

    test('admin can create characters', function () {
        $admin = User::factory()->create(['email' => 'admin@umamusume.local', 'is_admin' => true]);

        expect(Gate::forUser($admin)->allows('create', Character::class))->toBeTrue();
    });
});

describe('Regular User Authorization', function () {
    test('user can view their own character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        expect(Gate::forUser($user)->allows('view', $character))->toBeTrue();
    });

    test('user cannot view other users character', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($user)->allows('view', $character))->toBeFalse();
    });

    test('user can update their own character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        expect(Gate::forUser($user)->allows('update', $character))->toBeTrue();
    });

    test('user cannot update other users character', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($user)->allows('update', $character))->toBeFalse();
    });

    test('user can delete their own character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        expect(Gate::forUser($user)->allows('delete', $character))->toBeTrue();
    });

    test('user cannot delete other users character', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        expect(Gate::forUser($user)->allows('delete', $character))->toBeFalse();
    });

    test('user can view any characters', function () {
        $user = User::factory()->create();

        expect(Gate::forUser($user)->allows('viewAny', Character::class))->toBeTrue();
    });

    test('user can create characters', function () {
        $user = User::factory()->create();

        expect(Gate::forUser($user)->allows('create', Character::class))->toBeTrue();
    });
});
