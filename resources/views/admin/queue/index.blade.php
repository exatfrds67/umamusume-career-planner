<x-admin-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Queue Monitor</h1>
            <form method="POST" action="{{ route('admin.queue.restart') }}">
                @csrf
                <button type="submit" class="rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Restart
                    Workers</button>
            </form>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed Jobs</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['failed_count'] }}</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Jobs</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['jobs_count'] }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.queue.retry-all') }}">
                @csrf
                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Retry All
                    Failed</button>
            </form>
            <form method="POST" action="{{ route('admin.queue.flush') }}"
                onsubmit="return confirm('Clear all failed jobs?')">
                @csrf
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Clear All
                    Failed</button>
            </form>
        </div>

        <!-- Failed Jobs -->
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Failed Jobs</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            ID</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Queue</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Failed At</th>
                        <th
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
                                {{ $job->failed_at }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.queue.retry', $job->id) }}"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400">Retry</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.delete', $job->id) }}"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
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

        <!-- Pagination -->
        <div class="mt-4">
            {{ $failedJobs->links() }}
        </div>
    </div>
</x-admin-layout>
