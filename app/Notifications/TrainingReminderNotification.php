<?php

namespace App\Notifications;

use App\Models\Career;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Career $career,
        public int $currentTurn,
        public string $suggestedTraining = '',
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
        $characterName = $this->career->character->name ?? 'your character';

        return [
            'type' => 'training_reminder',
            'icon' => '🏃',
            'title' => 'Training Reminder',
            'message' => "Turn {$this->currentTurn}: Time to train {$characterName}!",
            'detail' => $this->suggestedTraining
                ? "Suggested: {$this->suggestedTraining}"
                : 'Check your training schedule for optimal gains.',
            'career_id' => $this->career->id,
            'turn' => $this->currentTurn,
            'action_url' => route('careers.show', $this->career->id),
            'action_label' => 'View Career',
            'priority' => 'normal',
        ];
    }
}
