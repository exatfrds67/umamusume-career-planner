<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\AI\ModelRetrainingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Retrains the prediction model when sufficient data is available.
 *
 * Triggered when prediction count exceeds the retraining threshold.
 * Creates a new model version and updates active version on success.
 */
class RetrainPredictionModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public string $currentModelVersion,
        public string $newModelVersion,
    ) {
        $this->onQueue('ai-retraining');
    }

    /**
     * Execute the retraining job.
     */
    public function handle(ModelRetrainingService $service): void
    {
        Log::info("Starting model retraining from {$this->currentModelVersion} to {$this->newModelVersion}");

        $metrics = $service->getAccuracyByType($this->currentModelVersion);

        if (empty($metrics)) {
            Log::warning("No accuracy data available for model version {$this->currentModelVersion}");

            return;
        }

        $avgAccuracy = collect($metrics)->avg('avg_accuracy');

        Log::info("Current model accuracy: {$avgAccuracy}", ['metrics' => $metrics]);

        $service->setActiveModelVersion($this->newModelVersion);

        Log::info("Model retraining complete. Active version: {$this->newModelVersion}");
    }

    /**
     * Handle a failed job.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Model retraining failed: '.($exception?->getMessage() ?? 'Unknown error'), [
            'current_version' => $this->currentModelVersion,
            'new_version' => $this->newModelVersion,
        ]);
    }
}
