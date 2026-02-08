<?php

use App\Models\User;

test('admin user is identified correctly', function () {
    $adminUser = User::factory()->create([
        'email' => 'admin@umamusume.local',
        'is_admin' => true,
    ]);

    expect($adminUser->isAdmin())->toBeTrue();
});

test('regular user is not admin', function () {
    $regularUser = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    expect($regularUser->isAdmin())->toBeFalse();
});

test('admin user can be created with correct attributes', function () {
    $admin = User::factory()->create([
        'email' => 'admin@umamusume.local',
        'name' => 'Admin',
        'is_admin' => true,
    ]);

    expect($admin->email)->toBe('admin@umamusume.local')
        ->and($admin->name)->toBe('Admin')
        ->and($admin->isAdmin())->toBeTrue();
});
