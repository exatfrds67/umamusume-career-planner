<div wire:poll.{{ $refreshInterval }}s="refreshMetrics">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">APM Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Real-time application performance monitoring</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Time Range Selector --}}
            <div role="group" aria-label="Select time range" class="flex rounded-lg border border-gray-300 dark:border-gray-600">
                @foreach (['1h', '6h', '24h', '7d', '30d'] as $range)
                    <button wire:click="setTimeRange('{{ $range }}')"
                        aria-pressed="{{ $timeRange === $range ? 'true' : 'false' }}"
                        class="px-3 py-1.5 text-sm font-medium {{ $timeRange === $range ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }} {{ $loop->first ? 'rounded-l-lg' : '' }} {{ $loop->last ? 'rounded-r-lg' : '' }}">
                        {{ $range }}
                    </button>
                @endforeach
            </div>
            <button wire:click="runAlertCheck"
                aria-label="Run performance alert check now"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Run Check
            </button>
        </div>
    </div>

    {{-- Health Score Banner --}}
    @php
        $overallScore = is_numeric($healthScore['overall_score'] ?? null) ? (float) $healthScore['overall_score'] : 0;
        $status = is_string($healthScore['status'] ?? null) ? $healthScore['status'] : 'unknown';
        $statusColors = [
            'excellent' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'good' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'fair' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
            'poor' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
            'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        ];
        $bannerColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    @endphp
    <div class="mb-6 rounded-lg {{ $bannerColor }} p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl font-bold">{{ number_format($overallScore, 1) }}</span>
                <span class="text-sm font-medium uppercase">Health Score — {{ ucfirst($status) }}</span>
            </div>
            <div class="text-sm" wire:loading.class="opacity-50" aria-live="polite">
                <span wire:loading wire:target="refreshMetrics">Refreshing...</span>
                <span wire:loading.remove wire:target="refreshMetrics">Auto-refresh: {{ $refreshInterval }}s</span>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Request Throughput --}}
        @php
            $throughput = is_numeric($overviewMetrics['total_requests'] ?? null)
                ? (int) $overviewMetrics['total_requests']
                : 0;
        @endphp
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Request Throughput</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($throughput) }}
                    </p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Total requests tracked</p>
        </div>

        {{-- Average Response Time --}}
        @php
            $avgResponseTime = is_numeric($overviewMetrics['avg_response_time'] ?? null)
                ? (float) $overviewMetrics['avg_response_time']
                : 0;
            $rtWarning = is_numeric($thresholds['response_time_warning'] ?? null)
                ? (float) $thresholds['response_time_warning']
                : 1000;
            $rtCritical = is_numeric($thresholds['response_time_critical'] ?? null)
                ? (float) $thresholds['response_time_critical']
                : 3000;
            $rtColor =
                $avgResponseTime >= $rtCritical
                    ? 'text-red-600 dark:text-red-400'
                    : ($avgResponseTime >= $rtWarning
                        ? 'text-yellow-600 dark:text-yellow-400'
                        : 'text-green-600 dark:text-green-400');
        @endphp
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Response Time</p>
                    <p class="mt-1 text-2xl font-bold {{ $rtColor }}">{{ number_format($avgResponseTime, 0) }}ms
                    </p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Threshold: warn
                >{{ number_format($rtWarning, 0) }}ms, critical >{{ number_format($rtCritical, 0) }}ms</p>
        </div>

        {{-- Error Rate --}}
        @php
            $errorRate = is_numeric($overviewMetrics['error_rate'] ?? null)
                ? (float) $overviewMetrics['error_rate']
                : 0;
            $erWarning = is_numeric($thresholds['error_rate_warning'] ?? null)
                ? (float) $thresholds['error_rate_warning']
                : 5;
            $erCritical = is_numeric($thresholds['error_rate_critical'] ?? null)
                ? (float) $thresholds['error_rate_critical']
                : 10;
            $erColor =
                $errorRate >= $erCritical
                    ? 'text-red-600 dark:text-red-400'
                    : ($errorRate >= $erWarning
                        ? 'text-yellow-600 dark:text-yellow-400'
                        : 'text-green-600 dark:text-green-400');
        @endphp
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Error Rate</p>
                    <p class="mt-1 text-2xl font-bold {{ $erColor }}">{{ number_format($errorRate, 2) }}%</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/30">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Threshold: warn
                >{{ number_format($erWarning, 1) }}%, critical >{{ number_format($erCritical, 1) }}%</p>
        </div>

        {{-- Cache Hit Rate --}}
        @php
            $cacheHitRate = is_numeric($cacheMetrics['hit_rate'] ?? null) ? (float) $cacheMetrics['hit_rate'] : 0;
            $chWarning = is_numeric($thresholds['cache_hit_warning'] ?? null)
                ? (float) $thresholds['cache_hit_warning']
                : 70;
            $chCritical = is_numeric($thresholds['cache_hit_critical'] ?? null)
                ? (float) $thresholds['cache_hit_critical']
                : 50;
            $chColor =
                $cacheHitRate <= $chCritical
                    ? 'text-red-600 dark:text-red-400'
                    : ($cacheHitRate <= $chWarning
                        ? 'text-yellow-600 dark:text-yellow-400'
                        : 'text-green-600 dark:text-green-400');
        @endphp
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Hit Rate</p>
                    <p class="mt-1 text-2xl font-bold {{ $chColor }}">{{ number_format($cacheHitRate, 1) }}%</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Threshold: warn
                &lt;{{ number_format($chWarning, 0) }}%, critical &lt;{{ number_format($chCritical, 0) }}%</p>
        </div>
    </div>

    {{-- System & Database Metrics --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- System Metrics --}}
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">System Metrics</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Memory Usage</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format(is_numeric($systemMetrics['memory_usage_percent'] ?? null) ? (float) $systemMetrics['memory_usage_percent'] : 0, 1) }}%
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Memory Used</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ is_string($systemMetrics['memory_used'] ?? null) ? $systemMetrics['memory_used'] : 'N/A' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">PHP Version</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ is_string($systemMetrics['php_version'] ?? null) ? $systemMetrics['php_version'] : 'N/A' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Uptime</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format(is_numeric($systemMetrics['uptime_hours'] ?? null) ? (float) $systemMetrics['uptime_hours'] : 0, 1) }}h
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Database Metrics --}}
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Database Metrics</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Active Connections</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ is_numeric($databaseMetrics['active_connections'] ?? null) ? $databaseMetrics['active_connections'] : 'N/A' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Slow Queries</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ is_numeric($databaseMetrics['slow_queries'] ?? null) ? $databaseMetrics['slow_queries'] : 0 }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Avg Query Time</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format(is_numeric($databaseMetrics['avg_query_time'] ?? null) ? (float) $databaseMetrics['avg_query_time'] : 0, 2) }}ms
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Total Queries</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format(is_numeric($databaseMetrics['total_queries'] ?? null) ? (int) $databaseMetrics['total_queries'] : 0) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Alert Statistics & Recent Alerts --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Alert Statistics --}}
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Alert Statistics</h2>
            @php
                $totalAlerts = is_numeric($alertStatistics['total'] ?? null) ? (int) $alertStatistics['total'] : 0;
                $unacknowledged = is_numeric($alertStatistics['unacknowledged'] ?? null)
                    ? (int) $alertStatistics['unacknowledged']
                    : 0;
                $bySeverity = is_array($alertStatistics['by_severity'] ?? null) ? $alertStatistics['by_severity'] : [];
            @endphp
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Total Alerts</dt>
                    <dd class="text-sm font-bold text-gray-900 dark:text-white">{{ $totalAlerts }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Unacknowledged</dt>
                    <dd
                        class="text-sm font-bold {{ $unacknowledged > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ $unacknowledged }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Critical</dt>
                    <dd class="text-sm font-medium text-red-600 dark:text-red-400">
                        {{ is_numeric($bySeverity['critical'] ?? null) ? $bySeverity['critical'] : 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Warning</dt>
                    <dd class="text-sm font-medium text-yellow-600 dark:text-yellow-400">
                        {{ is_numeric($bySeverity['warning'] ?? null) ? $bySeverity['warning'] : 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Info</dt>
                    <dd class="text-sm font-medium text-blue-600 dark:text-blue-400">
                        {{ is_numeric($bySeverity['info'] ?? null) ? $bySeverity['info'] : 0 }}</dd>
                </div>
            </dl>
            @if ($totalAlerts > 0)
                <button wire:click="clearAlerts" wire:confirm="Are you sure you want to clear all alerts?"
                    class="mt-4 w-full rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20">
                    Clear All Alerts
                </button>
            @endif
        </div>

        {{-- Recent Alerts --}}
        <div class="rounded-lg bg-white p-5 shadow lg:col-span-2 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Recent Alerts</h2>
            @if (empty($recentAlerts))
                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No alerts recorded</p>
            @else
                <div class="max-h-96 space-y-2 overflow-y-auto">
                    <ul class="space-y-2" role="list">
                    @foreach ($recentAlerts as $alert)
                        @php
                            $alertSeverity = is_string($alert['severity'] ?? null) ? $alert['severity'] : 'info';
                            $alertMessage = is_string($alert['message'] ?? null) ? $alert['message'] : '';
                            $alertTimestamp = is_string($alert['timestamp'] ?? null) ? $alert['timestamp'] : '';
                            $alertId = is_string($alert['id'] ?? null) ? $alert['id'] : '';
                            $isAcknowledged = (bool) ($alert['acknowledged'] ?? false);
                            $severityBadge = match ($alertSeverity) {
                                'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                            };
                        @endphp
                        <li
                            class="flex items-start gap-3 rounded-lg border p-3 {{ $isAcknowledged ? 'border-gray-200 bg-gray-50 opacity-60 dark:border-gray-700 dark:bg-gray-900/30' : 'border-gray-200 dark:border-gray-700' }}">
                            <span
                                class="mt-0.5 inline-flex shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $severityBadge }}">
                                {{ ucfirst($alertSeverity) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">{{ $alertMessage }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $alertTimestamp ? \Carbon\Carbon::parse($alertTimestamp)->diffForHumans() : '' }}
                                    @if ($isAcknowledged)
                                        <span class="ml-2 text-green-600 dark:text-green-400">&#10003; Acknowledged</span>
                                    @endif
                                </p>
                            </div>
                            @if (!$isAcknowledged && $alertId)
                                <button wire:click="acknowledgeAlert('{{ $alertId }}')"
                                    class="shrink-0 rounded px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                                    aria-label="Acknowledge alert: {{ $alertMessage }}">
                                    Acknowledge
                                </button>
                            @endif
                        </li>
                    @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- Configured Thresholds --}}
    <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Configured Thresholds</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">Configured alerting thresholds</caption>
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th scope="col" class="pb-3 font-medium text-gray-500 dark:text-gray-400">Metric</th>
                        <th scope="col" class="pb-3 font-medium text-yellow-600 dark:text-yellow-400">Warning</th>
                        <th scope="col" class="pb-3 font-medium text-red-600 dark:text-red-400">Critical</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr>
                        <td class="py-2 text-gray-900 dark:text-white">Response Time</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['response_time_warning'] ?? null) ? (float) $thresholds['response_time_warning'] : 0, 0) }}ms
                        </td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['response_time_critical'] ?? null) ? (float) $thresholds['response_time_critical'] : 0, 0) }}ms
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-900 dark:text-white">Error Rate</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['error_rate_warning'] ?? null) ? (float) $thresholds['error_rate_warning'] : 0, 1) }}%
                        </td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['error_rate_critical'] ?? null) ? (float) $thresholds['error_rate_critical'] : 0, 1) }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-900 dark:text-white">Cache Hit Rate</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">less than </span>&lt;{{ number_format(is_numeric($thresholds['cache_hit_warning'] ?? null) ? (float) $thresholds['cache_hit_warning'] : 0, 0) }}%
                        </td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">less than </span>&lt;{{ number_format(is_numeric($thresholds['cache_hit_critical'] ?? null) ? (float) $thresholds['cache_hit_critical'] : 0, 0) }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-900 dark:text-white">Memory Usage</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['memory_warning'] ?? null) ? (float) $thresholds['memory_warning'] : 0, 0) }}%
                        </td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">
                            <span class="sr-only">greater than </span>&gt;{{ number_format(is_numeric($thresholds['memory_critical'] ?? null) ? (float) $thresholds['memory_critical'] : 0, 0) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
