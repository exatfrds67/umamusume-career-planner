<x-admin-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>

        <!-- Environment Info -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Environment Information</h2>
            <dl class="grid grid-cols-2 gap-4">
                @foreach ($environment as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <!-- System Health -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">System Health</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($health as $service => $status)
                    <div class="rounded-lg border p-4 dark:border-gray-700">
                        <h3 class="mb-2 font-medium text-gray-900 dark:text-white">{{ ucfirst($service) }}</h3>
                        <span
                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                            {{ $status['status'] === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200' : '' }}
                            {{ $status['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' : '' }}
                            {{ $status['status'] === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' : '' }}
                            {{ $status['status'] === 'not_configured' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}">
                            {{ ucfirst($status['status']) }}
                        </span>
                        @if (isset($status['message']))
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $status['message'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cache Management -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Cache Management</h2>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.system-settings.clear-cache') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="all">
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Clear All
                        Caches</button>
                </form>
                <form method="POST" action="{{ route('admin.system-settings.clear-cache') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Config</button>
                </form>
                <form method="POST" action="{{ route('admin.system-settings.clear-cache') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="route">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Routes</button>
                </form>
                <form method="POST" action="{{ route('admin.system-settings.clear-cache') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="view">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Clear
                        Views</button>
                </form>
            </div>
        </div>

        <!-- Optimization -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Application Optimization</h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.system-settings.optimize') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Optimize
                        Application</button>
                </form>
                <form method="POST" action="{{ route('admin.system-settings.clear-optimization') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">Clear
                        Optimization</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
