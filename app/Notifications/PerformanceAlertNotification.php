<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Performance Alert Notification
 *
 * Sends performance alerts via email, Slack, and database channels
 * based on APM threshold violations.
 *
 * @see Requirements: NFR-O-01, NFR-O-04
 */
class PerformanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $alert
     */
    public function __construct(
        public array $alert
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        /** @var array<string, array<string, mixed>> $configChannels */
        $configChannels = config('apm.alerting.channels', []);

        $mailConfig = is_array($configChannels['mail'] ?? null) ? $configChannels['mail'] : [];
        if ($mailConfig['enabled'] ?? false) {
            $channels[] = 'mail';
        }

        $slackConfig = is_array($configChannels['slack'] ?? null) ? $configChannels['slack'] : [];
        if ($slackConfig['enabled'] ?? false) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severity = is_string($this->alert['severity'] ?? null) ? $this->alert['severity'] : 'warning';
        $message = is_string($this->alert['message'] ?? null) ? $this->alert['message'] : 'Performance alert triggered';
        $type = is_string($this->alert['type'] ?? null) ? $this->alert['type'] : 'unknown';

        $mailMessage = (new MailMessage)
            ->subject('['.strtoupper($severity).'] Performance Alert: '.ucfirst(str_replace('_', ' ', $type)))
            ->greeting('Performance Alert Triggered')
            ->line($message);

        /** @var array<string, mixed> $context */
        $context = is_array($this->alert['context'] ?? null) ? $this->alert['context'] : [];
        if ($context !== []) {
            $mailMessage->line('Details:');
            foreach ($context as $key => $value) {
                $mailMessage->line('- '.ucfirst(str_replace('_', ' ', (string) $key)).': '.(is_scalar($value) ? (string) $value : ''));
            }
        }

        $tsRaw = $this->alert['timestamp'] ?? null;
        $mailMessage->line('Timestamp: '.(is_scalar($tsRaw) ? (string) $tsRaw : now()->toIso8601String()));

        if ($severity === 'critical') {
            $mailMessage->error();
        }

        return $mailMessage
            ->action('View APM Dashboard', url('/admin/apm'))
            ->line('This is an automated performance monitoring alert.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $severity = is_string($this->alert['severity'] ?? null) ? $this->alert['severity'] : 'warning';
        $type = is_string($this->alert['type'] ?? null) ? $this->alert['type'] : 'unknown';

        $icons = [
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
        ];

        return [
            'type' => 'performance_alert',
            'icon' => $icons[$severity] ?? '🔔',
            'title' => 'Performance Alert: '.ucfirst(str_replace('_', ' ', $type)),
            'message' => is_string($this->alert['message'] ?? null) ? $this->alert['message'] : '',
            'detail' => 'Severity: '.ucfirst($severity),
            'severity' => $severity,
            'alert_type' => $type,
            'context' => is_array($this->alert['context'] ?? null) ? $this->alert['context'] : [],
            'action_url' => url('/admin/apm'),
            'action_label' => 'View APM Dashboard',
            'priority' => $severity === 'critical' ? 'urgent' : 'high',
        ];
    }
}
