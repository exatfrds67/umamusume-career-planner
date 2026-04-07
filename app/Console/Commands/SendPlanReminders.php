<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendTurnNotification;
use App\Models\CareerPlan;
use Illuminate\Console\Command;

class SendPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'career-plans:send-reminders
                            {--plan= : Dispatch reminders for a specific plan ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch reminder jobs for locked career plans that still have remaining turns';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = CareerPlan::query()
            ->where('is_locked', true)
            ->whereNotNull('locked_at');

        $planId = $this->option('plan');
        if (is_string($planId) && $planId !== '') {
            $query->where('id', $planId);
        }

        $plans = $query->get()->filter(function (CareerPlan $plan): bool {
            if (! $plan->isCompleted()) {
                return false;
            }

            $totalTurnsRaw = is_array($plan->plan) ? ($plan->plan['total_turns'] ?? null) : null;
            $totalTurns = is_numeric($totalTurnsRaw) ? (int) $totalTurnsRaw : 0;

            return $totalTurns > 0 && $plan->current_turn <= $totalTurns;
        });

        if ($plans->isEmpty()) {
            $this->info('No eligible locked career plans found for reminders.');

            return self::SUCCESS;
        }

        foreach ($plans as $plan) {
            SendTurnNotification::dispatch($plan->id);
        }

        $this->info("Queued reminder jobs for {$plans->count()} career plan(s).");

        return self::SUCCESS;
    }
}
