<?php

namespace App\Notifications;

use App\Models\Career;
use App\Models\Race;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RaceReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Career $career,
        public Race $race,
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
            'type' => 'race_ready',
            'icon' => '🏇',
            'title' => 'Race Available',
            'message' => "{$this->race->race_name} is available for {$characterName}!",
            'detail' => $this->race->race_grade
                ? "Grade: {$this->race->race_grade} | Distance: {$this->race->distance_meters}m"
                : 'Check race details for requirements.',
            'career_id' => $this->career->id,
            'race_id' => $this->race->id,
            'action_url' => route('careers.show', $this->career->id),
            'action_label' => 'View Race',
            'priority' => 'high',
        ];
    }
}
