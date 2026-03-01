<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create(['is_admin' => false]);
});

test('admin can view queue monitor with redis info', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('Queue Monitor')
        ->assertSee('Redis Connection')
        ->assertSee('Horizon Status')
        ->assertSee('Horizon Job Metrics')
        ->assertSee('Redis Queue Sizes')
        ->assertSee('Failed Jobs');
});

test('queue monitor shows redis connection status', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSeeText('Status')
        ->assertSeeText('Queue Driver')
        ->assertSeeText('Host');
});

test('queue monitor shows horizon status section', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('Horizon Status')
        ->assertSee('Master Supervisors')
        ->assertSee('Supervisors');
});

test('queue monitor shows horizon metrics', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('Recent')
        ->assertSee('Pending')
        ->assertSee('Completed')
        ->assertSee('Throughput')
        ->assertSee('Jobs/min');
});

test('queue monitor shows registered job classes', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('Registered Job Classes')
        ->assertSee('WarmCacheJob')
        ->assertSee('SyncExternalDataJob');
});

test('non-admin cannot view queue monitor', function () {
    $this->actingAs($this->user)
        ->get(route('admin.queue.index'))
        ->assertForbidden();
});

test('guest cannot view queue monitor', function () {
    $this->get(route('admin.queue.index'))
        ->assertRedirect(route('welcome'));
});

test('admin can retry all failed jobs', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.queue.retry-all'))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('admin can flush failed jobs', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.queue.flush'))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('admin can restart queue workers', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.queue.restart'))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('queue monitor shows wsl2 instruction when horizon is inactive', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.queue.index'))
        ->assertOk()
        ->assertSee('wsl php artisan horizon');
});
