<x-admin-layout title="Queue Monitor">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Queue Monitor</h1>
            <x-admin-confirm-action
                :action="route('admin.queue.restart')"
                title="Restart Queue Workers"
                message="Restarting workers may drop in-flight jobs. Are you sure?"
                variant="warning"
                confirmText="Restart Workers"
                buttonLabel="Restart Workers"
                buttonClass="rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" />
        </div>

        {{-- Connection & Horizon Status --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Redis Connection --}}
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Redis Connection</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        @if ($redisInfo['connected'])
                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/20 dark:text-green-200">Connected</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/20 dark:text-red-200">Disconnected</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Queue Driver</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $redisInfo['driver'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Host</span>
                        <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $redisInfo['host'] }}:{{ $redisInfo['port'] }}</span>
                    </div>
                    @if (!empty($redisInfo['server_info']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Redis Version</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $redisInfo['server_info']['redis_version'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Memory Used</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $redisInfo['server_info']['used_memory_human'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Uptime</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ($redisInfo['server_info']['uptime_days'] ?? -1) >= 0 ? $redisInfo['server_info']['uptime_days'] . ' days' : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Connected Clients</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ($redisInfo['server_info']['connected_clients'] ?? -1) >= 0 ? $redisInfo['server_info']['connected_clients'] : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Commands Processed</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ($redisInfo['server_info']['total_commands_processed'] ?? -1) >= 0 ? number_format($redisInfo['server_info']['total_commands_processed']) : 'N/A' }}</span>
                        </div>
                        @php
                            $hits = $redisInfo['server_info']['keyspace_hits'] ?? 0;
                            $misses = $redisInfo['server_info']['keyspace_misses'] ?? 0;
                            $total = $hits + $misses;
                            $hitRate = $total > 0 ? round(($hits / $total) * 100, 1) : -1;
                        @endphp
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Keyspace Hit Rate</span>
                            <span class="text-sm font-medium {{ $hitRate >= 90 ? 'text-green-600 dark:text-green-400' : ($hitRate >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $hitRate >= 0 ? $hitRate . '%' : 'N/A' }}
                                @if ($total > 0)
                                    <span class="text-gray-400 dark:text-gray-500">({{ number_format($hits) }}/{{ number_format($total) }})</span>
                                @endif
                            </span>
                        </div>
                    @endif
                    @if (!$redisInfo['connected'] && isset($redisInfo['error']))
                        <div class="mt-2 rounded bg-red-50 p-2 dark:bg-red-900/10">
                            <p class="text-xs text-red-700 dark:text-red-300">{{ $redisInfo['error'] }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Horizon Status --}}
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Horizon Status</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        @if ($horizonStatus['running'])
                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/20 dark:text-green-200">Active</span>
                        @elseif ($horizonStatus['status'] === 'error')
                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/20 dark:text-red-200">Error</span>
                        @else
                            <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">Inactive</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Master Supervisors</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $horizonStatus['master_count'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Supervisors</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $horizonStatus['supervisor_count'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Horizon Keys in Redis</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $redisInfo['horizon_keys'] }}</span>
                    </div>
                    @if ($horizonStatus['error'])
                        <div class="mt-2 rounded bg-red-50 p-2 dark:bg-red-900/10">
                            <p class="text-xs text-red-700 dark:text-red-300">{{ $horizonStatus['error'] }}</p>
                        </div>
                    @endif
                    @if (!$horizonStatus['running'])
                        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/10">
                            <p class="text-xs font-medium text-amber-800 dark:text-amber-200">Horizon requires WSL2</p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                Start Horizon via WSL: <code class="rounded bg-amber-100 px-1 py-0.5 font-mono dark:bg-amber-900/30">wsl php artisan horizon</code>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Horizon Metrics --}}
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Horizon Job Metrics</h2>
            @if ($horizonMetrics['error'] && !$horizonMetrics['available'])
                <div class="rounded bg-yellow-50 p-3 dark:bg-yellow-900/10">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">Metrics unavailable: {{ $horizonMetrics['error'] }}</p>
                </div>
            @endif
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $horizonMetrics['recent'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Recent</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold {{ $horizonMetrics['pending'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">{{ $horizonMetrics['pending'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $horizonMetrics['completed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold {{ $horizonMetrics['failed'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $horizonMetrics['failed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Failed</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $horizonMetrics['throughput'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Throughput</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $horizonMetrics['jobs_per_minute'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jobs/min</p>
                </div>
            </div>
        </div>

        {{-- Redis Queue Sizes --}}
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Redis Queue Sizes</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @forelse ($redisInfo['queue_sizes'] as $queueName => $size)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ ucfirst($queueName) }}</h3>
                        <p class="mt-1 text-2xl font-bold {{ $size > 0 ? 'text-amber-600 dark:text-amber-400' : ($size < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white') }}">
                            {{ $size >= 0 ? $size : 'Error' }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">jobs queued</p>
                    </div>
                @empty
                    <div class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400">
                        No queue data available. Redis may not be connected.
                    </div>
                @endforelse
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Queued (Redis)</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $redisInfo['total_queued'] >= 0 ? $redisInfo['total_queued'] : 'N/A' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">DB Pending (jobs table)</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['db_pending_count'] }}</span>
            </div>
        </div>

        {{-- Queue Workload (from Horizon) --}}
        @if (count($queueWorkload) > 0)
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Queue Workload (Horizon)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <caption class="sr-only">Queue workload from Horizon</caption>
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Queue</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Length</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Wait (sec)</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Processes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach ($queueWorkload as $workload)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $workload['name'] ?? 'unknown' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm {{ ($workload['length'] ?? 0) > 0 ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $workload['length'] ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $workload['wait'] ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $workload['processes'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Horizon Job Throughput by Job Class --}}
        @if (!empty($horizonMetrics['job_throughputs']))
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Throughput by Job Class</h2>
                <div class="space-y-2">
                    @foreach ($horizonMetrics['job_throughputs'] as $jobName => $throughput)
                        <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-2 dark:border-gray-700">
                            <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $jobName }}</span>
                            <span class="text-sm font-bold {{ $throughput > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $throughput }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Database Stats Summary --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed Jobs (DB)</h3>
                <p class="mt-2 text-3xl font-bold {{ $stats['failed_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $stats['failed_count'] }}</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Jobs (DB)</h3>
                <p class="mt-2 text-3xl font-bold {{ $stats['db_pending_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">{{ $stats['db_pending_count'] }}</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Batches (DB)</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['batch_count'] }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2">
            <x-admin-confirm-action
                :action="route('admin.queue.retry-all')"
                title="Retry All Failed Jobs"
                message="This will re-queue all failed jobs for processing. Continue?"
                variant="info"
                confirmText="Retry All"
                buttonLabel="Retry All Failed"
                buttonClass="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2" />

            <x-admin-confirm-action
                :action="route('admin.queue.flush')"
                title="Clear All Failed Jobs"
                message="This will permanently delete all failed job records. This action cannot be undone."
                variant="danger"
                confirmText="Clear All"
                buttonLabel="Clear All Failed"
                buttonClass="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" />
        </div>

        {{-- Available Job Classes --}}
        @if (count($availableJobs) > 0)
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Registered Job Classes</h2>
                <div class="space-y-2">
                    @foreach ($availableJobs as $job)
                        <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-2 dark:border-gray-700">
                            <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $job['name'] }}</span>
                            @if ($job['implements_should_queue'])
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/20 dark:text-blue-200">ShouldQueue</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Sync</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Job Batches --}}
        @if (count($jobBatches) > 0)
            <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Job Batches</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <caption class="sr-only">Job batches</caption>
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Progress</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Failed</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @foreach ($jobBatches as $batch)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $batch->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full rounded-full {{ $batch->progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $batch->progress }}%"></div>
                                        </div>
                                        <span class="text-gray-900 dark:text-white">{{ $batch->progress }}%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{{ $batch->total_jobs }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm {{ $batch->pending_jobs > 0 ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $batch->pending_jobs }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm {{ $batch->failed_jobs > 0 ? 'font-bold text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $batch->failed_jobs }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <time datetime="{{ $batch->created_at_formatted }}">{{ $batch->created_at_formatted }}</time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Failed Jobs --}}
        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Failed Jobs</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <caption class="sr-only">Failed jobs</caption>
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            ID</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Queue</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Failed At</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($failedJobs as $job)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $job->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $job->queue }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <time datetime="{{ $job->failed_at }}">{{ $job->failed_at }}</time></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.queue.retry', $job->id) }}"
                                        class="inline"
                                        onsubmit="return confirm('Retry this failed job?')">
                                        @csrf
                                        <button type="submit"
                                            aria-label="Retry job {{ $job->id }}"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400">Retry</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.delete', $job->id) }}"
                                        class="inline"
                                        onsubmit="return confirm('Delete this failed job? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            aria-label="Delete job {{ $job->id }}"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No failed jobs.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $failedJobs->links() }}
        </div>
    </div>
</x-admin-layout>
