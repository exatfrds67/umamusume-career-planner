<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CriticalAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $alertTitle,
        public string $alertMessage,
        public string $severity = 'critical',
        public string $actionUrl = '',
        public string $actionLabel = 'View Details',
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $icons = [
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
        ];

        return [
            'type' => 'critical_alert',
            'icon' => $icons[$this->severity] ?? '🚨',
            'title' => $this->alertTitle,
            'message' => $this->alertMessage,
            'detail' => 'Severity: '.ucfirst($this->severity),
            'severity' => $this->severity,
            'action_url' => $this->actionUrl ?: route('dashboard'),
            'action_label' => $this->actionLabel,
            'priority' => $this->severity === 'critical' ? 'urgent' : 'high',
        ];
    }
}
