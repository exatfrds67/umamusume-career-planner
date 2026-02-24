<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\DeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a user requests account deletion.
 * Confirms the request and reminds them of the 30-day grace period.
 */
class DeletionConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DeletionRequest $deletionRequest,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $gracePeriodEnd = $this->deletionRequest->grace_period_ends_at->format('F j, Y');

        return (new MailMessage)
            ->subject('Account Deletion Request Confirmation')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We have received your request to delete your account.')
            ->line("Your account and all associated data will be permanently deleted after **{$gracePeriodEnd}**.")
            ->line('You have 30 days to cancel this request if you change your mind.')
            ->action('Cancel Deletion', route('privacy.dashboard'))
            ->line('If you did not make this request, please cancel it immediately and change your password.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deletion_confirmation',
            'icon' => '🗑️',
            'title' => 'Account Deletion Requested',
            'message' => 'Your account is scheduled for deletion on '.$this->deletionRequest->grace_period_ends_at->format('F j, Y').'.',
            'detail' => 'You can cancel this request within 30 days.',
            'action_url' => route('privacy.dashboard'),
            'action_label' => 'Manage Deletion',
            'priority' => 'high',
        ];
    }
}
