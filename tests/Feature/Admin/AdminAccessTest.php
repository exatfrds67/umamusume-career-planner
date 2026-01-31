<?php

use App\Models\User;

test('non-admin users cannot access admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin users can access admin routes', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('guests cannot access admin routes', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('welcome'));
});

test('admin can view system settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.system-settings.index'))
        ->assertOk()
        ->assertSee('System Settings');
});

test('admin can view logs', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.logs.index'))
        ->assertOk()
        ->assertSee('Application Logs');
});

test('admin can view database maintenance', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.database.maintenance'))
        ->assertOk()
        ->assertSee('Database Maintenance');
});

test('admin can view queue monitor', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('Queue Monitor');
});
