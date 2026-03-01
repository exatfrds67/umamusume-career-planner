<x-admin-layout title="Logs">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Application Logs</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.logs.download') }}"
                    aria-label="Download log file"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Download Logs</a>
                <x-admin-confirm-action
                    :action="route('admin.logs.clear')"
                    title="Clear Old Logs"
                    message="This will permanently delete old log entries. Recent logs will be preserved."
                    variant="danger"
                    confirmText="Clear Logs"
                    buttonLabel="Clear Old Logs"
                    buttonClass="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" />
            </div>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400">Log file size: {{ $fileSize }}</div>

        <!-- Filters -->
        <form method="GET" role="search" aria-label="Filter log entries" class="flex gap-4">
            <label for="log-search" class="sr-only">Search logs</label>
            <input type="text" name="search" id="log-search" value="{{ request('search') }}" placeholder="Search logs..."
                class="flex-1 rounded-md border-gray-300 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <label for="log-level-filter" class="sr-only">Log level filter</label>
            <select name="level" id="log-level-filter"
                class="rounded-md border-gray-300 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Levels</option>
                <option value="error" {{ request('level') == 'error' ? 'selected' : '' }}>Error</option>
                <option value="warning" {{ request('level') == 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="info" {{ request('level') == 'info' ? 'selected' : '' }}>Info</option>
                <option value="debug" {{ request('level') == 'debug' ? 'selected' : '' }}>Debug</option>
            </select>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Filter</button>
        </form>

        <!-- Logs -->
        <div class="space-y-2">
            @forelse ($logs as $log)
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' => $log['level'] === 'ERROR',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' => $log['level'] === 'WARNING',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200' => $log['level'] === 'INFO',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => $log['level'] === 'DEBUG',
                                ])>
                                    {{ $log['level'] }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400"><time datetime="{{ $log['timestamp'] }}">{{ $log['timestamp'] }}</time></span>
                            </div>
                            <p class="mt-2 text-sm text-gray-900 dark:text-white">{{ $log['message'] }}</p>
                            @if ($log['context'])
                                <pre aria-label="Log context" class="mt-2 overflow-x-auto rounded bg-gray-100 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ trim($log['context']) }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-lg bg-white p-8 text-center shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">No logs found.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
