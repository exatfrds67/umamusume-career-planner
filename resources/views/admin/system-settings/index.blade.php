<x-admin-layout title="System Settings">
    <div class="space-y-6">
        <div class="admin-page-hero">
            <div class="admin-page-hero__content">
                <div>
                    <div class="admin-page-hero__eyebrow">
                        <span>Infrastructure</span>
                    </div>
                    <h1 class="admin-page-hero__title">System Settings</h1>
                    <p class="admin-page-hero__body text-sm sm:text-base">Inspect environment health, clear caches, and control application optimization.</p>
                </div>
            </div>
        </div>

        <!-- Environment Info -->
        <div class="admin-surface p-6">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">Environment Information</h2>
            <dl class="grid grid-cols-2 gap-4">
                @foreach ($environment as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            {{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-neutral-900 dark:text-white">
                            {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <!-- System Health -->
        <div class="admin-surface p-6">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">System Health</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($health as $service => $status)
                    <div class="rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                        <h3 class="mb-2 font-medium text-neutral-900 dark:text-white">{{ ucfirst($service) }}</h3>
                        <span role="status"
                            aria-label="{{ ucfirst($service) }} status: {{ $status['status'] }}"
                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                            {{ $status['status'] === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200' : '' }}
                            {{ $status['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' : '' }}
                            {{ $status['status'] === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' : '' }}
                            {{ $status['status'] === 'not_configured' ? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200' : '' }}">
                            {{ ucfirst($status['status']) }}
                        </span>
                        @if (isset($status['message']))
                            <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">{{ $status['message'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cache Management -->
        <div class="admin-surface p-6">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">Cache Management</h2>
            <div class="flex flex-wrap gap-2">
                <x-admin-confirm-action
                    :action="route('admin.system-settings.clear-cache')"
                    :hiddenInputs="['type' => 'all']"
                    title="Clear All Caches"
                    message="This will clear all application caches including config, routes, and views. The application may be temporarily slower while caches rebuild."
                    variant="danger"
                    confirmText="Clear All"
                    buttonLabel="Clear All Caches"
                    buttonClass="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" />

                <x-admin-confirm-action
                    :action="route('admin.system-settings.clear-cache')"
                    :hiddenInputs="['type' => 'config']"
                    title="Clear Config Cache"
                    message="Clear the cached configuration. The config will be re-cached on next request."
                    variant="warning"
                    confirmText="Clear Config"
                    buttonLabel="Clear Config"
                    buttonClass="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" />

                <x-admin-confirm-action
                    :action="route('admin.system-settings.clear-cache')"
                    :hiddenInputs="['type' => 'route']"
                    title="Clear Route Cache"
                    message="Clear the cached routes. Routes will be re-cached on next request."
                    variant="warning"
                    confirmText="Clear Routes"
                    buttonLabel="Clear Routes"
                    buttonClass="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" />

                <x-admin-confirm-action
                    :action="route('admin.system-settings.clear-cache')"
                    :hiddenInputs="['type' => 'view']"
                    title="Clear View Cache"
                    message="Clear the compiled view cache. Views will be recompiled on next render."
                    variant="warning"
                    confirmText="Clear Views"
                    buttonLabel="Clear Views"
                    buttonClass="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" />
            </div>
        </div>

        <!-- Optimization -->
        <div class="admin-surface p-6">
            <h2 class="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">Application Optimization</h2>
            <div class="flex gap-2">
                <x-admin-confirm-action
                    :action="route('admin.system-settings.optimize')"
                    title="Optimize Application"
                    message="This will cache config, routes, and views for improved performance."
                    variant="info"
                    confirmText="Optimize"
                    buttonLabel="Optimize Application"
                    buttonClass="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2" />

                <x-admin-confirm-action
                    :action="route('admin.system-settings.clear-optimization')"
                    title="Clear Optimization"
                    message="This will remove all optimization caches. The application may be slower until re-optimized."
                    variant="warning"
                    confirmText="Clear Optimization"
                    buttonLabel="Clear Optimization"
                    buttonClass="rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2" />
            </div>
        </div>
    </div>
</x-admin-layout>
