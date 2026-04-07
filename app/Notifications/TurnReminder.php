<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CareerPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TurnReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>|null  $nextAction
     * @param  array<string, mixed>  $preferences
     */
    public function __construct(
        public CareerPlan $plan,
        public ?array $nextAction,
        public array $preferences = [],
        public bool $isFinished = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (($this->preferences['email'] ?? false) === true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isFinished) {
            return (new MailMessage)
                ->subject('Your Umamusume Career Plan Is Complete')
                ->line('You have reached the end of your locked career plan.')
                ->action('View Character', url("/characters/{$this->plan->character_id}"));
        }

        $action = $this->nextAction['action'] ?? [];
        $reasoningRaw = is_array($action) ? ($action['reasoning'] ?? null) : null;
        $reasoning = is_string($reasoningRaw) && $reasoningRaw !== ''
            ? $reasoningRaw
            : 'Time to take the next recommended action.';

        return (new MailMessage)
            ->subject('Next Step In Your Career Plan')
            ->line("Turn {$this->plan->current_turn}: {$reasoning}")
            ->action('View Plan', url("/characters/{$this->plan->character_id}#career-plan-visualizer"));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->isFinished) {
            return [
                'type' => 'career_plan_complete',
                'title' => 'Career Plan Complete',
                'message' => 'You have completed your locked career plan.',
                'plan_id' => $this->plan->id,
                'character_id' => $this->plan->character_id,
            ];
        }

        return [
            'type' => 'career_plan_turn_reminder',
            'title' => 'Next Career Plan Step',
            'message' => 'A new turn is ready in your locked career plan.',
            'turn' => $this->plan->current_turn,
            'plan_id' => $this->plan->id,
            'character_id' => $this->plan->character_id,
            'action' => $this->nextAction['action'] ?? [],
        ];
    }
}
