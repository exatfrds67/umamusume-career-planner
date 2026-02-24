<?php

declare(strict_types=1);

use App\Livewire\Admin\ApmDashboard;
use App\Models\User;
use App\Notifications\PerformanceAlertNotification;
use App\Services\PerformanceAlertingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
    $this->regularUser = User::factory()->create(['is_admin' => false]);
});

// --- Route Access Tests ---

it('requires authentication to access APM dashboard', function (): void {
    $response = $this->get(route('admin.apm'));

    $response->assertRedirect();
});

it('denies access to non-admin users', function (): void {
    $response = $this->actingAs($this->regularUser)->get(route('admin.apm'));

    $response->assertForbidden();
});

it('allows admin users to access APM dashboard', function (): void {
    $response = $this->actingAs($this->adminUser)->get(route('admin.apm'));

    $response->assertSuccessful();
});

it('displays APM Dashboard heading', function (): void {
    $response = $this->actingAs($this->adminUser)->get(route('admin.apm'));

    $response->assertSuccessful();
    $response->assertSee('APM Dashboard');
});

// --- Livewire Component Tests ---

it('renders the ApmDashboard livewire component', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('APM Dashboard');
});

it('displays health score section', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('Health Score');
});

it('displays key metric cards', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('Request Throughput')
        ->assertSee('Avg Response Time')
        ->assertSee('Error Rate')
        ->assertSee('Cache Hit Rate');
});

it('displays system and database metrics sections', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('System Metrics')
        ->assertSee('Database Metrics');
});

it('displays alert statistics section', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('Alert Statistics');
});

it('displays configured thresholds table', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSuccessful()
        ->assertSee('Configured Thresholds')
        ->assertSee('Response Time')
        ->assertSee('Error Rate')
        ->assertSee('Cache Hit Rate')
        ->assertSee('Memory Usage');
});

it('can change time range', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSet('timeRange', '24h')
        ->call('setTimeRange', '1h')
        ->assertSet('timeRange', '1h')
        ->call('setTimeRange', '7d')
        ->assertSet('timeRange', '7d');
});

it('rejects invalid time range', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSet('timeRange', '24h')
        ->call('setTimeRange', 'invalid')
        ->assertSet('timeRange', '24h');
});

it('can refresh metrics', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->call('refreshMetrics')
        ->assertSuccessful();
});

it('can run alert check', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->call('runAlertCheck')
        ->assertSuccessful();
});

it('loads thresholds from config', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(ApmDashboard::class)
        ->assertSet('thresholds.response_time_warning', 1000.0)
        ->assertSet('thresholds.response_time_critical', 3000.0)
        ->assertSet('thresholds.error_rate_warning', 5.0)
        ->assertSet('thresholds.error_rate_critical', 10.0)
        ->assertSet('thresholds.cache_hit_warning', 70.0)
        ->assertSet('thresholds.cache_hit_critical', 50.0);
});

// --- Notification Tests ---

it('sends notification to admin users on warning alert', function (): void {
    Notification::fake();

    /** @var PerformanceAlertingService $alertingService */
    $alertingService = app(PerformanceAlertingService::class);

    $alertingService->createAlert(
        'response_time',
        'warning',
        'Test warning alert',
        ['value' => 1500, 'threshold' => 1000]
    );

    Notification::assertSentTo($this->adminUser, PerformanceAlertNotification::class);
    Notification::assertNotSentTo($this->regularUser, PerformanceAlertNotification::class);
});

it('sends notification to admin users on critical alert', function (): void {
    Notification::fake();

    /** @var PerformanceAlertingService $alertingService */
    $alertingService = app(PerformanceAlertingService::class);

    $alertingService->createAlert(
        'error_rate',
        'critical',
        'Test critical alert',
        ['value' => 15, 'threshold' => 10]
    );

    Notification::assertSentTo($this->adminUser, PerformanceAlertNotification::class);
});

it('does not send notification for info severity alerts', function (): void {
    Notification::fake();

    /** @var PerformanceAlertingService $alertingService */
    $alertingService = app(PerformanceAlertingService::class);

    $alertingService->createAlert(
        'response_time',
        'info',
        'Test info alert',
        ['value' => 500]
    );

    Notification::assertNotSentTo($this->adminUser, PerformanceAlertNotification::class);
});

it('notification includes correct data in database channel', function (): void {
    $alert = [
        'id' => 'test_alert_123',
        'type' => 'response_time',
        'severity' => 'warning',
        'message' => 'Response time exceeded threshold',
        'context' => ['value' => 1500, 'threshold' => 1000],
        'timestamp' => now()->toIso8601String(),
    ];

    $notification = new PerformanceAlertNotification($alert);
    $data = $notification->toArray($this->adminUser);

    expect($data)
        ->toHaveKey('type', 'performance_alert')
        ->toHaveKey('severity', 'warning')
        ->toHaveKey('alert_type', 'response_time')
        ->toHaveKey('action_url');

    expect($data['message'])->toContain('Response time exceeded threshold');
});

it('notification includes mail channel when configured', function (): void {
    config(['apm.alerting.channels.mail.enabled' => true]);

    $alert = [
        'type' => 'error_rate',
        'severity' => 'critical',
        'message' => 'Error rate critical',
        'context' => [],
    ];

    $notification = new PerformanceAlertNotification($alert);
    $channels = $notification->via($this->adminUser);

    expect($channels)->toContain('mail')->toContain('database');
});

it('notification includes slack channel when configured', function (): void {
    config(['apm.alerting.channels.slack.enabled' => true]);

    $alert = [
        'type' => 'cache_hit_rate',
        'severity' => 'warning',
        'message' => 'Cache hit rate low',
        'context' => [],
    ];

    $notification = new PerformanceAlertNotification($alert);
    $channels = $notification->via($this->adminUser);

    expect($channels)->toContain('slack')->toContain('database');
});

// --- Admin Layout Navigation ---

it('shows APM link in admin navigation', function (): void {
    $response = $this->actingAs($this->adminUser)->get(route('admin.apm'));

    $response->assertSuccessful();
    $response->assertSee('APM');
});
