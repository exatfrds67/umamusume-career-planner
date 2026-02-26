<?php

declare(strict_types=1);

namespace App\Services\Testing;

use Illuminate\Support\Facades\Storage;

/**
 * Coverage Reporter Service
 *
 * Tracks visited routes, errors, performance metrics, and generates
 * comprehensive HTML and JSON reports of browser test traversal.
 */
class CoverageReporter
{
    /** @var array<int, array<string, mixed>> */
    private array $results = [];

    /** @var array<int, array<string, mixed>> */
    private array $errors = [];

    /** @var array<int, float> */
    private array $performance = [];

    private string $reportId;

    private float $startTime;

    public function __construct()
    {
        $this->reportId = date('Y-m-d_H-i-s');
        $this->startTime = microtime(true);
    }

    /**
     * Record a visited page result.
     *
     * @param  string  $category  'public', 'auth', or 'admin'
     * @param  array<string, mixed>  $data  {success: bool, statusCode: int|null, loadTime: float, error: string|null}
     */
    public function recordVisit(string $url, string $category, array $data): void
    {
        $this->results[] = [
            'url' => $url,
            'category' => $category,
            'success' => $data['success'] ?? false,
            'statusCode' => $data['statusCode'] ?? null,
            'loadTime' => $data['loadTime'] ?? 0.0,
            'error' => $data['error'] ?? null,
            'timestamp' => now()->toDateTimeString(),
            'screenshotPath' => $data['screenshotPath'] ?? null,
        ];

        // Track performance
        if (isset($data['loadTime'])) {
            $this->performance[] = $data['loadTime'];
        }

        // Track errors
        if (! empty($data['error'])) {
            $this->errors[] = [
                'url' => $url,
                'category' => $category,
                'error' => $data['error'],
                'timestamp' => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * Get summary statistics.
     *
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        $totalVisited = count($this->results);
        $successful = count(array_filter($this->results, fn ($r) => $r['success']));
        $failed = $totalVisited - $successful;

        $avgLoadTime = count($this->performance) > 0
            ? array_sum($this->performance) / count($this->performance)
            : 0;

        $maxLoadTime = count($this->performance) > 0 ? max($this->performance) : 0;
        $minLoadTime = count($this->performance) > 0 ? min($this->performance) : 0;

        return [
            'total_visited' => $totalVisited,
            'successful' => $successful,
            'failed' => $failed,
            'pass_rate' => $totalVisited > 0 ? round(($successful / $totalVisited) * 100, 2) : 0,
            'total_errors' => count($this->errors),
            'avg_load_time' => round($avgLoadTime, 3),
            'max_load_time' => round($maxLoadTime, 3),
            'min_load_time' => round($minLoadTime, 3),
            'duration' => round(microtime(true) - $this->startTime, 2),
        ];
    }

    /**
     * Generate HTML report.
     *
     * @return string Path to generated report
     */
    public function generateHtmlReport(): string
    {
        $summary = $this->getSummary();
        $reportPath = "test-reports/traversal-{$this->reportId}.html";

        $html = $this->buildHtml($summary);

        Storage::disk('local')->put($reportPath, $html);

        // Also create a "latest" symlink/copy
        Storage::disk('local')->put('test-reports/traversal-latest.html', $html);

        return storage_path("app/{$reportPath}");
    }

    /**
     * Generate JSON report.
     *
     * @return string Path to generated report
     */
    public function generateJsonReport(): string
    {
        $reportPath = "test-reports/traversal-{$this->reportId}.json";

        $data = [
            'report_id' => $this->reportId,
            'generated_at' => now()->toDateTimeString(),
            'summary' => $this->getSummary(),
            'results' => $this->results,
            'errors' => $this->errors,
        ];

        Storage::disk('local')->put($reportPath, json_encode($data, JSON_PRETTY_PRINT) ?: '');

        return storage_path("app/{$reportPath}");
    }

    /**
     * Build HTML report content.
     *
     * @param  array<string, mixed>  $summary
     */
    private function buildHtml(array $summary): string
    {
        $passRate = $summary['pass_rate'];
        $statusColor = $passRate >= 95 ? '#10b981' : ($passRate >= 80 ? '#f59e0b' : '#ef4444');
        $reportId = $this->reportId;
        $generatedAt = $summary['generated_at'] ?? now()->toDateTimeString();
        $totalVisited = $summary['total_visited'];
        $successful = $summary['successful'];
        $failed = $summary['failed'];
        $totalErrors = $summary['total_errors'];
        $avgLoadTime = $summary['avg_load_time'];
        $maxLoadTime = $summary['max_load_time'];
        $duration = $summary['duration'];

        $resultsHtml = $this->buildResultsTable();
        $errorsHtml = $this->buildErrorsTable();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browser Traversal Test Report - {$reportId}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; padding: 2rem; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 2rem; color: #111827; margin-bottom: 0.5rem; }
        .subtitle { color: #6b7280; font-size: 0.875rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
        .stat { background: #f9fafb; padding: 1rem; border-radius: 6px; }
        .stat-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; margin-bottom: 0.25rem; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #111827; }
        .stat-unit { font-size: 0.875rem; color: #6b7280; margin-left: 0.25rem; }
        .section { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2 { font-size: 1.25rem; color: #111827; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f9fafb; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
        td { padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb; font-size: 0.875rem; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-public { background: #dbeafe; color: #1e40af; }
        .badge-auth { background: #fef3c7; color: #92400e; }
        .badge-admin { background: #fce7f3; color: #831843; }
        .url-cell { font-family: 'Courier New', monospace; color: #4b5563; }
        .error-text { color: #dc2626; font-size: 0.875rem; }
        .pass-rate { font-size: 3rem; font-weight: 700; color: {$statusColor}; text-align: center; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Browser Traversal Test Report</h1>
            <div class="subtitle">Report ID: {$reportId} | Generated: {$generatedAt}</div>
            
            <div class="pass-rate">{$passRate}% Pass Rate</div>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-label">Total Visited</div>
                    <div class="stat-value">{$totalVisited}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Successful</div>
                    <div class="stat-value" style="color: #10b981;">{$successful}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Failed</div>
                    <div class="stat-value" style="color: #ef4444;">{$failed}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Total Errors</div>
                    <div class="stat-value">{$totalErrors}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Avg Load Time</div>
                    <div class="stat-value">{$avgLoadTime}<span class="stat-unit">s</span></div>
                </div>
                <div class="stat">
                    <div class="stat-label">Max Load Time</div>
                    <div class="stat-value">{$maxLoadTime}<span class="stat-unit">s</span></div>
                </div>
                <div class="stat">
                    <div class="stat-label">Test Duration</div>
                    <div class="stat-value">{$duration}<span class="stat-unit">s</span></div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📊 All Visited Pages</h2>
            {$resultsHtml}
        </div>

        {$errorsHtml}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build results table HTML.
     */
    private function buildResultsTable(): string
    {
        if (empty($this->results)) {
            return '<p style="color: #6b7280;">No results recorded.</p>';
        }

        $rows = '';
        foreach ($this->results as $result) {
            $statusBadge = $result['success']
                ? '<span class="badge badge-success">✓ Success</span>'
                : '<span class="badge badge-error">✗ Failed</span>';

            $categoryBadge = match ($result['category']) {
                'public' => '<span class="badge badge-public">Public</span>',
                'auth' => '<span class="badge badge-auth">Auth</span>',
                'admin' => '<span class="badge badge-admin">Admin</span>',
                default => '<span class="badge">'.$result['category'].'</span>',
            };

            $error = $result['error'] ? '<div class="error-text">'.$result['error'].'</div>' : '';

            $rows .= <<<HTML
                <tr>
                    <td>{$statusBadge}</td>
                    <td class="url-cell">{$result['url']}</td>
                    <td>{$categoryBadge}</td>
                    <td>{$result['statusCode']}</td>
                    <td>{$result['loadTime']}s</td>
                    <td>{$error}</td>
                </tr>
HTML;
        }

        return <<<HTML
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Category</th>
                        <th>HTTP Code</th>
                        <th>Load Time</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
HTML;
    }

    /**
     * Build errors table HTML.
     */
    private function buildErrorsTable(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $rows = '';
        foreach ($this->errors as $error) {
            $rows .= <<<HTML
                <tr>
                    <td class="url-cell">{$error['url']}</td>
                    <td><span class="badge badge-{$error['category']}">{$error['category']}</span></td>
                    <td class="error-text">{$error['error']}</td>
                    <td>{$error['timestamp']}</td>
                </tr>
HTML;
        }

        return <<<HTML
            <div class="section">
                <h2>🚨 Errors Detected</h2>
                <table>
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Category</th>
                            <th>Error</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
            </div>
HTML;
    }

    /**
     * Get all recorded results.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get all recorded errors.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
