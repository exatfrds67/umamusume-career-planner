<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CareerPlan;
use App\Models\Character;
use App\Services\CareerPlanTimelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateCareerPlan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $jobId,
        public string $planId,
        public int $characterId,
        public array $options = [],
    ) {
        $this->onQueue('ai');
    }

    public function handle(CareerPlanTimelineService $timelineService): void
    {
        Cache::put($this->jobCacheKey(), [
            'status' => 'processing',
            'plan_id' => $this->planId,
        ], now()->addDay());

        $character = Character::query()
            ->with(['skillAcquisitions'])
            ->findOrFail($this->characterId);

        $careerPlan = CareerPlan::query()->findOrFail($this->planId);
        $goal = isset($this->options['goal']) && is_string($this->options['goal'])
            ? $this->options['goal']
            : null;
        $timelineOptions = isset($this->options['options']) && is_array($this->options['options'])
            ? $this->options['options']
            : [];

        $planPayload = $timelineService->build(
            $character,
            $goal,
            $timelineOptions
        );

        $planPayload['plan_id'] = $this->planId;

        $careerPlan->update([
            'goal' => $this->options['goal'] ?? $careerPlan->goal,
            'plan' => $planPayload,
        ]);

        Cache::put($this->jobCacheKey(), [
            'status' => 'completed',
            'plan_id' => $this->planId,
        ], now()->addDay());
    }

    public function failed(?\Throwable $exception): void
    {
        $careerPlan = CareerPlan::query()->find($this->planId);

        if ($careerPlan !== null) {
            $plan = $careerPlan->plan;
            $plan['status'] = 'failed';
            $plan['error'] = $exception?->getMessage() ?? 'Unknown error';
            $careerPlan->plan = $plan;
            $careerPlan->save();
        }

        Cache::put($this->jobCacheKey(), [
            'status' => 'failed',
            'plan_id' => $this->planId,
            'error' => $exception?->getMessage(),
        ], now()->addDay());
    }

    private function jobCacheKey(): string
    {
        return "career-plan-job:{$this->jobId}";
    }
}
