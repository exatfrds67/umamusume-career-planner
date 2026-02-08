<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:api-performance {--clear-cache : Clear cache before testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test external API endpoint performance and verify response time targets';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('External API Performance Test');
        $this->info('============================');
        $this->newLine();

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->info('Clearing cache...');
            $this->call('cache:clear');
            $this->newLine();
        }

        // Define endpoints to test
        $endpoints = [
            'characters' => '/api/external/characters',
            'support-cards' => '/api/external/support-cards',
            'skills' => '/api/external/skills',
            'news' => '/api/external/news',
        ];

        /** @var array<string, array{success: bool, responseTime: int|float, cached?: bool, offlineMode?: bool, source?: string, dataCount?: int, statusCode?: int, error?: string}> */
        $results = [];
        $baseUrl = config('app.url');

        if (! \is_string($baseUrl)) {
            $this->error('Application URL is not configured properly.');

            return self::FAILURE;
        }

        // Test each endpoint
        foreach ($endpoints as $name => $path) {
            $this->info("Testing {$name}...");

            $startTime = microtime(true);

            try {
                $response = Http::timeout(10)->get($baseUrl.$path);

                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime) * 1000); // Convert to milliseconds

                /** @var array{success?: bool, cached?: bool, offline_mode?: bool, source?: string, data?: array<mixed>} */
                $data = $response->json() ?? [];
                $success = $response->successful() && ($data['success'] ?? false);
                $cached = (bool) ($data['cached'] ?? false);
                $offlineMode = (bool) ($data['offline_mode'] ?? false);
                $source = (string) ($data['source'] ?? 'unknown');
                $dataCount = \is_array($data['data'] ?? null) ? \count($data['data']) : 0;

                $results[$name] = [
                    'success' => $success,
                    'responseTime' => $responseTime,
                    'cached' => $cached,
                    'offlineMode' => $offlineMode,
                    'source' => $source,
                    'dataCount' => $dataCount,
                    'statusCode' => $response->status(),
                ];

                // Determine data source label
                $dataSource = $offlineMode ? 'offline' : ($cached ? 'cached' : 'live');

                // Determine target
                $target = $cached ? 1000 : 3000;
                $meetsTarget = $responseTime < $target;

                // Display result
                $status = $success ? ($meetsTarget ? '✓' : '⚠') : '✗';
                $color = $success ? ($meetsTarget ? 'green' : 'yellow') : 'red';

                $this->line(sprintf(
                    '  %s <fg=%s>%dms</> (%s, target: <%dms) - %d items from %s',
                    $status,
                    $color,
                    (int) $responseTime,
                    $dataSource,
                    $target,
                    $dataCount,
                    $source
                ));
            } catch (\Exception $e) {
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime) * 1000);

                $results[$name] = [
                    'success' => false,
                    'responseTime' => $responseTime,
                    'error' => $e->getMessage(),
                ];

                $this->line(sprintf(
                    '  ✗ <fg=red>%dms</> - Error: %s',
                    (int) $responseTime,
                    $e->getMessage()
                ));
            }

            $this->newLine();
        }

        // Calculate summary
        $successfulResults = array_filter($results, fn (array $r): bool => $r['success']);
        $responseTimes = array_column($successfulResults, 'responseTime');

        $avgResponseTime = \count($responseTimes) > 0
            ? round(array_sum($responseTimes) / \count($responseTimes))
            : 0;
        $minResponseTime = \count($responseTimes) > 0 ? min($responseTimes) : 0;
        $maxResponseTime = \count($responseTimes) > 0 ? max($responseTimes) : 0;

        // Check if all targets are met
        $allMeetTargets = true;
        foreach ($successfulResults as $result) {
            $target = ($result['cached'] ?? false) ? 1000 : 3000;
            if ($result['responseTime'] >= $target) {
                $allMeetTargets = false;
                break;
            }
        }

        // Display summary
        $this->info('Performance Summary');
        $this->info('==================');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Endpoints tested', \count($results)],
                ['Successful', \count($successfulResults)],
                ['Failed', \count($results) - \count($successfulResults)],
                ['Average response time', $avgResponseTime.'ms'],
                ['Min response time', $minResponseTime.'ms'],
                ['Max response time', $maxResponseTime.'ms'],
                ['All targets met', $allMeetTargets ? '✓ Yes' : '✗ No'],
            ]
        );

        // Return appropriate exit code
        if (\count($successfulResults) === 0) {
            $this->error('All endpoints failed!');

            return self::FAILURE;
        }

        if (! $allMeetTargets) {
            $this->warn('Some endpoints exceeded performance targets');

            return self::FAILURE;
        }

        $this->info('All performance targets met! ✓');

        return self::SUCCESS;
    }
}
