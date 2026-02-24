<?php

use App\Livewire\Settings\NotificationSettings;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('renders the notification settings component', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.settings.notification-settings');
});

it('loads default notification settings on mount', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->assertSet('raceReminders', true)
        ->assertSet('trainingAlerts', true)
        ->assertSet('syncNotifications', true)
        ->assertSet('quietHoursStart', '22:00')
        ->assertSet('quietHoursEnd', '08:00');
});

it('loads saved notification settings on mount', function () {
    $this->user->update([
        'notification_preferences' => [
            'race_reminders' => false,
            'training_alerts' => false,
            'sync_notifications' => false,
            'quiet_hours_start' => '23:00',
            'quiet_hours_end' => '07:00',
        ],
    ]);

    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->assertSet('raceReminders', false)
        ->assertSet('trainingAlerts', false)
        ->assertSet('syncNotifications', false)
        ->assertSet('quietHoursStart', '23:00')
        ->assertSet('quietHoursEnd', '07:00');
});

it('saves race reminders preference', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('raceReminders', false)
        ->call('savePreferences')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->notification_preferences['race_reminders'])->toBeFalse();
});

it('saves training alerts preference', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('trainingAlerts', false)
        ->call('savePreferences')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->notification_preferences['training_alerts'])->toBeFalse();
});

it('saves sync notifications preference', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('syncNotifications', false)
        ->call('savePreferences')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->notification_preferences['sync_notifications'])->toBeFalse();
});

it('saves quiet hours preferences', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('quietHoursStart', '21:00')
        ->set('quietHoursEnd', '09:00')
        ->call('savePreferences')
        ->assertSet('saved', true);

    $this->user->refresh();
    expect($this->user->notification_preferences['quiet_hours_start'])->toBe('21:00');
    expect($this->user->notification_preferences['quiet_hours_end'])->toBe('09:00');
});

it('resets all settings to defaults', function () {
    $this->user->update([
        'notification_preferences' => [
            'race_reminders' => false,
            'training_alerts' => false,
            'sync_notifications' => false,
            'quiet_hours_start' => '20:00',
            'quiet_hours_end' => '10:00',
        ],
    ]);

    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->assertSet('raceReminders', false)
        ->assertSet('trainingAlerts', false)
        ->call('resetDefaults')
        ->assertSet('raceReminders', true)
        ->assertSet('trainingAlerts', true)
        ->assertSet('syncNotifications', true)
        ->assertSet('quietHoursStart', '22:00')
        ->assertSet('quietHoursEnd', '08:00');

    $this->user->refresh();
    expect($this->user->notification_preferences['race_reminders'])->toBeTrue();
    expect($this->user->notification_preferences['training_alerts'])->toBeTrue();
    expect($this->user->notification_preferences['quiet_hours_start'])->toBe('22:00');
});

it('dispatches notification-preferences-updated event on save', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('raceReminders', false)
        ->call('savePreferences')
        ->assertDispatched('notification-preferences-updated');
});

it('persists multiple settings changes', function () {
    Livewire::actingAs($this->user)
        ->test(NotificationSettings::class)
        ->set('raceReminders', false)
        ->set('trainingAlerts', false)
        ->set('quietHoursStart', '23:30')
        ->call('savePreferences');

    $this->user->refresh();
    expect($this->user->notification_preferences['race_reminders'])->toBeFalse();
    expect($this->user->notification_preferences['training_alerts'])->toBeFalse();
    expect($this->user->notification_preferences['quiet_hours_start'])->toBe('23:30');
});

it('requires authentication to access notifications settings page', function () {
    $this->get('/settings/notifications')
        ->assertRedirect('/');
});

it('allows authenticated users to access notifications settings page', function () {
    $this->actingAs($this->user)
        ->get('/settings/notifications')
        ->assertSuccessful();
});
