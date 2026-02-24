<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Notification Settings Livewire Component
 *
 * Provides controls for push notification preferences:
 * race reminders, training alerts, sync notifications,
 * and quiet hours configuration.
 *
 * requirements: INT-WS-03, NFR-PWA-04
 */
class NotificationSettings extends Component
{
    public bool $raceReminders = true;

    public bool $trainingAlerts = true;

    public bool $syncNotifications = true;

    public string $quietHoursStart = '22:00';

    public string $quietHoursEnd = '08:00';

    public bool $saved = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $preferences = $user->notification_preferences ?? [];

            $this->raceReminders = $preferences['race_reminders'] ?? true;
            $this->trainingAlerts = $preferences['training_alerts'] ?? true;
            $this->syncNotifications = $preferences['sync_notifications'] ?? true;
            $this->quietHoursStart = $preferences['quiet_hours_start'] ?? '22:00';
            $this->quietHoursEnd = $preferences['quiet_hours_end'] ?? '08:00';
        }
    }

    public function savePreferences(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $preferences = [
            'race_reminders' => $this->raceReminders,
            'training_alerts' => $this->trainingAlerts,
            'sync_notifications' => $this->syncNotifications,
            'quiet_hours_start' => $this->quietHoursStart,
            'quiet_hours_end' => $this->quietHoursEnd,
        ];

        $user->update(['notification_preferences' => $preferences]);

        $this->saved = true;
        $this->dispatch('notification-preferences-updated', preferences: $preferences);
    }

    public function resetDefaults(): void
    {
        $this->raceReminders = true;
        $this->trainingAlerts = true;
        $this->syncNotifications = true;
        $this->quietHoursStart = '22:00';
        $this->quietHoursEnd = '08:00';

        $this->savePreferences();
    }

    public function render(): View
    {
        return view('livewire.settings.notification-settings');
    }
}
