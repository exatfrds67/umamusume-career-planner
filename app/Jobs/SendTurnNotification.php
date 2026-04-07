<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CareerPlan;
use App\Notifications\TurnReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTurnNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $planId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $plan = CareerPlan::query()->with('user')->find($this->planId);
        if ($plan === null || ! $plan->is_locked) {
            return;
        }

        $next = $plan->getNextAction();

        if ($plan->user === null) {
            return;
        }

        $plan->user->notify(new TurnReminder(
            $plan,
            $next,
            $plan->notificationPreferences(),
            $next === null
        ));
    }
}
