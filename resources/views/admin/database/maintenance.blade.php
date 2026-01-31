<x-admin-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Database Maintenance</h1>

        <!-- Actions -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <form method="POST" action="{{ route('admin.database.optimize') }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Optimize Tables</button>
            </form>
            <form method="POST" action="{{ route('admin.database.backup') }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Create
                    Backup</button>
            </form>
            <form method="POST" action="{{ route('admin.database.migrate') }}"
                onsubmit="return confirm('Run migrations?')">
                @csrf
                <button type="submit"
                    class="w-full rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Run
                    Migrations</button>
            </form>
            <form method="POST" action="{{ route('admin.database.fresh') }}"
                onsubmit="return confirm('This will DELETE ALL DATA! Are you sure?')">
                @csrf
                <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Fresh
                    + Seed</button>
            </form>
        </div>

        <!-- Migration Status -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Migration Status</h2>
            <pre class="overflow-x-auto rounded bg-gray-100 p-4 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $migrationStatus['output'] }}</pre>
        </div>

        <!-- Tables -->
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Database Tables</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Table</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Rows</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Size</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Engine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @foreach ($tables as $table)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $table['name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($table['rows']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $table['size'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $table['engine'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
