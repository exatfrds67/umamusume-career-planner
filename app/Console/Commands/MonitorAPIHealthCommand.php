<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ExternalAPI\APIAlertingService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\GracefulDegradationService;
use Illuminate\Console\Command;

/**
 * Monitor API Health Command
 *
 * Monitors external API health status and triggers alerts when needed.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 *
 * @phpstan-type ApiHealthStatus array{
 *     status: string,
 *     available: bool,
 *     response_time_ms?: float|int,
 *     failure_count?: int,
 *     circuit_breaker_open?: bool,
 *     message?: string
 * }
 */
class MonitorAPIHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:monitor-health
                            {--continuous : Run continuously with interval}
                            {--interval=60 : Interval in seconds for continuous monitoring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor external API health status and trigger alerts';

    public function __construct(
        protected APIHealthMonitorService $healthMonitor,
        protected GracefulDegradationService $degradationService,
        protected APIAlertingService $alertingService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $continuous = $this->option('continuous');
        $interval = (int) $this->option('interval');

        if ($continuous) {
            $this->info('Starting continuous API health monitoring...');
            $this->info("Interval: {$interval} seconds");
            $this->info('Press Ctrl+C to stop');
            $this->newLine();

            while ($this->shouldContinueMonitoring()) {
                $this->performHealthCheck();
                sleep($interval);
            }
        } else {
            $this->performHealthCheck();
        }

        return Command::SUCCESS;
    }

    /**
     * Perform health check
     */
    protected function performHealthCheck(): void
    {
        $this->info('['.now()->format('Y-m-d H:i:s').'] Checking API health...');

        /** @var array{overall_status: string, umapyoi: ApiHealthStatus, umamusumedb: ApiHealthStatus} $health */
        $health = $this->healthMonitor->checkAllAPIs();

        // Display overall status
        $this->displayOverallStatus($health);

        // Display individual API status
        $this->displayAPIStatus('umapyoi', $health['umapyoi']);
        $this->displayAPIStatus('umamusumedb', $health['umamusumedb']);

        // Check for degradation and trigger alerts
        $this->checkDegradation($health);

        $this->newLine();
    }

    /**
     * Display overall status
     *
     * @param  array{overall_status: string}  $health
     */
    protected function displayOverallStatus(array $health): void
    {
        $status = (string) $health['overall_status'];

        $statusColor = match ($status) {
            'healthy' => 'green',
            'degraded' => 'yellow',
            'unhealthy' => 'red',
            default => 'gray',
        };

        $this->line("Overall Status: <fg={$statusColor}>".strtoupper($status).'</fg>');
    }

    /**
     * Display API status
     *
     * @param  ApiHealthStatus  $status
     */
    protected function displayAPIStatus(string $apiName, array $status): void
    {
        $statusText = is_string($status['status'] ?? null) ? $status['status'] : 'unknown';
        $available = ($status['available'] ?? false) ? 'Yes' : 'No';
        $responseTime = $status['response_time_ms'] ?? null;
        $responseTimeMs = is_numeric($responseTime) ? (float) $responseTime : null;
        $failureCount = is_numeric($status['failure_count'] ?? null) ? (int) $status['failure_count'] : 0;
        $circuitOpen = (bool) ($status['circuit_breaker_open'] ?? false);

        $statusColor = match ($statusText) {
            'healthy' => 'green',
            'degraded' => 'yellow',
            'unhealthy', 'error', 'circuit_open' => 'red',
            default => 'gray',
        };

        $this->line("  {$apiName}:");
        $this->line("    Status: <fg={$statusColor}>".strtoupper($statusText).'</fg>');
        $this->line("    Available: {$available}");

        if ($responseTimeMs !== null) {
            $this->line('    Response Time: '.round($responseTimeMs, 2).'ms');
        }

        if ($failureCount > 0) {
            $this->line("    <fg=yellow>Failure Count: {$failureCount}</fg>");
        }

        if ($circuitOpen) {
            $this->line('    <fg=red>Circuit Breaker: OPEN</fg>');
        }

        $this->line('    Message: '.(is_string($status['message'] ?? null) ? $status['message'] : ''));
    }

    /**
     * Check for degradation and trigger alerts
     *
     * @param  array{umapyoi: ApiHealthStatus, umamusumedb: ApiHealthStatus}  $health
     */
    protected function checkDegradation(array $health): void
    {
        foreach (['umapyoi', 'umamusumedb'] as $apiName) {
            $status = $health[$apiName];
            $statusCode = is_string($status['status'] ?? null) ? $status['status'] : 'unknown';
            $statusMessage = is_string($status['message'] ?? null) ? $status['message'] : '';

            // Check if API is degraded or unhealthy
            if (in_array($statusCode, ['degraded', 'unhealthy', 'error'], true)) {
                // Enable degradation mode
                if (! $this->degradationService->isDegradationModeActive($apiName)) {
                    $this->degradationService->enableDegradationMode(
                        $apiName,
                        $statusMessage
                    );

                    // Send alert
                    $this->alertingService->sendHealthDegradationAlert(
                        $apiName,
                        $statusCode,
                        $statusMessage
                    );

                    $this->warn("  Degradation mode enabled for {$apiName}");
                }
            }

            // Check if circuit breaker opened
            if ($status['circuit_breaker_open'] ?? false) {
                $this->alertingService->sendCircuitBreakerAlert(
                    $apiName,
                    is_numeric($status['failure_count'] ?? null) ? (int) $status['failure_count'] : 0
                );

                $this->error("  Circuit breaker opened for {$apiName}");
            }

            // Check if API recovered
            if ($statusCode === 'healthy' && $this->degradationService->isDegradationModeActive($apiName)) {
                $this->degradationService->disableDegradationMode($apiName);

                $this->alertingService->sendRecoveryAlert(
                    $apiName,
                    is_numeric($status['response_time_ms'] ?? null) ? (float) $status['response_time_ms'] : 0.0
                );

                $this->info("  {$apiName} has recovered!");
            }
        }
    }

    protected function shouldContinueMonitoring(): bool
    {
        return (bool) $this->option('continuous');
    }
}
