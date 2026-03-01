<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;

class QueueController extends Controller
{
    /**
     * Display queue monitor with real metrics from Redis and Horizon.
     */
    public function index(): View
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20);

        $redisInfo = $this->getRedisInfo();
        $horizonStatus = $this->getHorizonStatus();
        $horizonMetrics = $this->getHorizonMetrics();
        $queueWorkload = $this->getQueueWorkload();
        $jobBatches = $this->getJobBatches();
        $availableJobs = $this->getAvailableJobClasses();

        $stats = [
            'failed_count' => DB::table('failed_jobs')->count(),
            'db_pending_count' => DB::table('jobs')->count(),
            'batch_count' => DB::table('job_batches')->count(),
        ];

        return view('admin.queue.index', compact(
            'failedJobs',
            'stats',
            'redisInfo',
            'horizonStatus',
            'horizonMetrics',
            'queueWorkload',
            'jobBatches',
            'availableJobs',
        ));
    }

    /**
     * Get Redis connection info and queue sizes.
     *
     * @return array<string, mixed>
     */
    private function getRedisInfo(): array
    {
        try {
            $redis = Redis::connection();
            $redis->ping();

            $prefix = config('database.redis.options.prefix', '');
            $queueConnection = config('queue.default', 'database');

            $queueSizes = [];
            $totalQueuedJobs = 0;

            $queueNames = ['default', 'high', 'low', 'notifications'];
            foreach ($queueNames as $queueName) {
                $size = 0;
                try {
                    $key = "queues:{$queueName}";
                    $size = (int) Redis::llen($key);
                    if ($size === 0) {
                        $size = (int) Redis::zcard("{$key}:delayed");
                        $reserved = (int) Redis::zcard("{$key}:reserved");
                        $size += $reserved;
                    }
                } catch (\Exception) {
                    $size = -1;
                }
                $queueSizes[$queueName] = $size;
                $totalQueuedJobs += max(0, $size);
            }

            $horizonKeys = 0;
            try {
                $horizonPrefixConfig = config('horizon.prefix', 'horizon:');
                $horizonPrefix = is_string($horizonPrefixConfig) ? $horizonPrefixConfig : 'horizon:';
                // Use rawCommand to bypass Laravel's Redis key prefix, since Horizon
                // writes its keys without the application's Redis prefix.
                $client = Redis::connection()->client();
                if (is_object($client) && method_exists($client, 'rawCommand')) {
                    $rawKeys = $client->rawCommand('KEYS', "{$horizonPrefix}*");
                    $horizonKeys = is_array($rawKeys) ? count($rawKeys) : 0;
                }
            } catch (\Exception) {
                // Horizon may not be storing data yet
            }

            $info = [];
            try {
                $serverInfo = Redis::info();
                if (is_array($serverInfo)) {
                    $redisVersion = $serverInfo['redis_version'] ?? 'unknown';
                    $usedMemoryHuman = $serverInfo['used_memory_human'] ?? 'unknown';

                    $info = [
                        'redis_version' => is_string($redisVersion) ? $redisVersion : 'unknown',
                        'uptime_days' => $this->toInt($serverInfo['uptime_in_days'] ?? null, -1),
                        'connected_clients' => $this->toInt($serverInfo['connected_clients'] ?? null, -1),
                        'used_memory_human' => is_string($usedMemoryHuman) ? $usedMemoryHuman : 'unknown',
                        'total_commands_processed' => $this->toInt($serverInfo['total_commands_processed'] ?? null, -1),
                        'keyspace_hits' => $this->toInt($serverInfo['keyspace_hits'] ?? null, -1),
                        'keyspace_misses' => $this->toInt($serverInfo['keyspace_misses'] ?? null, -1),
                    ];
                }
            } catch (\Exception) {
                // Redis INFO may not be available
            }

            return [
                'connected' => true,
                'driver' => $queueConnection,
                'host' => config('database.redis.default.host', '127.0.0.1'),
                'port' => config('database.redis.default.port', 6379),
                'queue_sizes' => $queueSizes,
                'total_queued' => $totalQueuedJobs,
                'horizon_keys' => $horizonKeys,
                'server_info' => $info,
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
                'driver' => config('queue.default', 'database'),
                'host' => config('database.redis.default.host', '127.0.0.1'),
                'port' => config('database.redis.default.port', 6379),
                'queue_sizes' => [],
                'total_queued' => -1,
                'horizon_keys' => 0,
                'server_info' => [],
            ];
        }
    }

    /**
     * Get Horizon supervisor and master status.
     *
     * @return array<string, mixed>
     */
    private function getHorizonStatus(): array
    {
        try {
            /** @var MasterSupervisorRepository $masters */
            $masters = app(MasterSupervisorRepository::class);

            /** @var SupervisorRepository $supervisors */
            $supervisors = app(SupervisorRepository::class);

            $masterNames = $masters->names();
            $masterDetails = [];
            foreach ($masterNames as $name) {
                $detail = $masters->find($name);
                if ($detail) {
                    $masterDetails[] = $detail;
                }
            }

            $supervisorNames = $supervisors->names();
            $supervisorDetails = [];
            foreach ($supervisorNames as $name) {
                $detail = $supervisors->find($name);
                if ($detail) {
                    $supervisorDetails[] = $detail;
                }
            }

            $isRunning = count($masterNames) > 0;

            return [
                'running' => $isRunning,
                'status' => $isRunning ? 'active' : 'inactive',
                'master_count' => count($masterNames),
                'supervisor_count' => count($supervisorNames),
                'masters' => $masterDetails,
                'supervisors' => $supervisorDetails,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'running' => false,
                'status' => 'error',
                'master_count' => 0,
                'supervisor_count' => 0,
                'masters' => [],
                'supervisors' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Horizon job metrics (counts, throughput).
     *
     * @return array<string, mixed>
     */
    private function getHorizonMetrics(): array
    {
        try {
            /** @var JobRepository $jobs */
            $jobs = app(JobRepository::class);

            /** @var MetricsRepository $metrics */
            $metrics = app(MetricsRepository::class);

            $recentCount = $jobs->countRecent();
            $failedCount = $jobs->countFailed();
            $pendingCount = $jobs->countPending();
            $completedCount = $jobs->countCompleted();
            $recentlyFailedCount = $jobs->countRecentlyFailed();

            $throughput = $metrics->throughput();
            $jobsPerMinute = $metrics->jobsProcessedPerMinute();
            $measuredQueues = $metrics->measuredQueues();
            $measuredJobs = $metrics->measuredJobs();

            $queueThroughputs = [];
            foreach ($measuredQueues as $queue) {
                $queueThroughputs[$queue] = $metrics->throughputForQueue($queue);
            }

            $jobThroughputs = [];
            foreach ($measuredJobs as $job) {
                $shortName = class_basename($job);
                $jobThroughputs[$shortName] = $metrics->throughputForJob($job);
            }

            return [
                'available' => true,
                'recent' => $recentCount,
                'failed' => $failedCount,
                'pending' => $pendingCount,
                'completed' => $completedCount,
                'recently_failed' => $recentlyFailedCount,
                'throughput' => $throughput,
                'jobs_per_minute' => $jobsPerMinute,
                'queue_throughputs' => $queueThroughputs,
                'job_throughputs' => $jobThroughputs,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'recent' => 0,
                'failed' => 0,
                'pending' => 0,
                'completed' => 0,
                'recently_failed' => 0,
                'throughput' => 0,
                'jobs_per_minute' => 0,
                'queue_throughputs' => [],
                'job_throughputs' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue workload from Horizon.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getQueueWorkload(): array
    {
        try {
            /** @var WorkloadRepository $workload */
            $workload = app(WorkloadRepository::class);

            return $workload->get();
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Get job batches from database.
     *
     * @return array<int, object>
     */
    private function getJobBatches(): array
    {
        try {
            $batches = DB::table('job_batches')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function (object $batch): object {
                    $createdAtRaw = property_exists($batch, 'created_at') ? $batch->created_at : null;
                    $finishedAtRaw = property_exists($batch, 'finished_at') ? $batch->finished_at : null;
                    $totalJobsRaw = property_exists($batch, 'total_jobs') ? $batch->total_jobs : null;
                    $pendingJobsRaw = property_exists($batch, 'pending_jobs') ? $batch->pending_jobs : null;

                    $createdAtTimestamp = is_numeric($createdAtRaw) ? (int) $createdAtRaw : null;
                    $finishedAtTimestamp = is_numeric($finishedAtRaw) ? (int) $finishedAtRaw : null;
                    $totalJobs = is_numeric($totalJobsRaw) ? (int) $totalJobsRaw : 0;
                    $pendingJobs = is_numeric($pendingJobsRaw) ? (int) $pendingJobsRaw : 0;

                    $batch->created_at_formatted = $createdAtTimestamp !== null
                        ? date('Y-m-d H:i:s', $createdAtTimestamp)
                        : 'N/A';
                    $batch->finished_at_formatted = $finishedAtTimestamp !== null
                        ? date('Y-m-d H:i:s', $finishedAtTimestamp)
                        : null;
                    $batch->progress = $totalJobs > 0
                        ? round((($totalJobs - $pendingJobs) / $totalJobs) * 100)
                        : 0;

                    return $batch;
                })
                ->toArray();

            /** @var array<int, object> $batches */
            return $batches;
        } catch (\Exception) {
            return [];
        }
    }

    private function toInt(mixed $value, int $default = 0): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Get available job classes in the application.
     *
     * @return array<int, array{class: string, name: string, implements_should_queue: bool}>
     */
    private function getAvailableJobClasses(): array
    {
        $jobsPath = app_path('Jobs');
        if (! is_dir($jobsPath)) {
            return [];
        }

        $jobs = [];
        /** @var \DirectoryIterator $file */
        foreach (new \DirectoryIterator($jobsPath) as $file) {
            if ($file->isDot() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = 'App\\Jobs\\'.$file->getBasename('.php');
            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                $jobs[] = [
                    'class' => $className,
                    'name' => $file->getBasename('.php'),
                    'implements_should_queue' => $reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class),
                ];
            }
        }

        return $jobs;
    }

    /**
     * Retry a failed job.
     */
    public function retry(string $id): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', 'Job queued for retry.');
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs queued for retry.');
    }

    /**
     * Delete a failed job.
     */
    public function delete(string $id): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return back()->with('success', 'Failed job deleted.');
    }

    /**
     * Clear all failed jobs.
     */
    public function flush(): RedirectResponse
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs cleared.');
    }

    /**
     * Restart queue workers.
     */
    public function restart(): RedirectResponse
    {
        Artisan::call('queue:restart');

        return back()->with('success', 'Queue workers will restart after completing current jobs.');
    }
}
