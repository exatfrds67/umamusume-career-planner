@props(['server'])

<div
    class="rounded-lg border border-gray-200 bg-white p-4 shadow-xs transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between">
        <!-- Server Info -->
        <div class="flex items-center space-x-4">
            <!-- Status Indicator -->
            <div class="relative flex h-12 w-12 items-center justify-center rounded-full"
                :class="{
                    'bg-green-100 dark:bg-green-900': server.status === 'healthy',
                    'bg-yellow-100 dark:bg-yellow-900': server.status === 'degraded',
                    'bg-red-100 dark:bg-red-900': server.status === 'unhealthy',
                    'bg-gray-100 dark:bg-gray-700': server.status === 'disabled'
                }">
                <!-- Pulse animation for healthy servers -->
                <span x-show="server.status === 'healthy'"
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>

                <svg class="relative h-6 w-6"
                    :class="{
                        'text-green-600 dark:text-green-300': server.status === 'healthy',
                        'text-yellow-600 dark:text-yellow-300': server.status === 'degraded',
                        'text-red-600 dark:text-red-300': server.status === 'unhealthy',
                        'text-gray-600 dark:text-gray-400': server.status === 'disabled'
                    }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01">
                    </path>
                </svg>
            </div>

            <!-- Server Details -->
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="server.name"></h4>
                <div class="mt-1 flex items-center space-x-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400" x-text="server.type"></span>
                    <span class="text-gray-400">•</span>
                    <span class="capitalize"
                        :class="{
                            'text-green-600 dark:text-green-400': server.status === 'healthy',
                            'text-yellow-600 dark:text-yellow-400': server.status === 'degraded',
                            'text-red-600 dark:text-red-400': server.status === 'unhealthy',
                            'text-gray-600 dark:text-gray-400': server.status === 'disabled'
                        }"
                        x-text="server.status"></span>
                </div>
            </div>
        </div>

        <!-- Connection Status Badge -->
        <div class="flex flex-col items-end space-y-2">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': server.is_connected,
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': !server.is_connected
                }">
                <span x-text="server.is_connected ? 'Connected' : 'Disconnected'"></span>
            </span>

            <!-- Response Time -->
            <span x-show="server.avg_response_time" class="text-xs text-gray-500 dark:text-gray-400">
                <span x-text="server.avg_response_time"></span>ms avg
            </span>
        </div>
    </div>

    <!-- Server Metrics -->
    <div x-show="server.status !== 'disabled'"
        class="mt-4 grid grid-cols-3 gap-4 border-t border-gray-200 pt-4 dark:border-gray-700">
        <!-- Uptime -->
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Uptime</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white" x-text="server.uptime_percentage + '%'">
            </p>
        </div>

        <!-- Requests (24h) -->
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Requests (24h)</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white" x-text="server.requests_24h || 0"></p>
        </div>

        <!-- Failures -->
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Failures</p>
            <p class="mt-1 text-sm font-semibold"
                :class="{
                    'text-green-600 dark:text-green-400': server.consecutive_failures === 0,
                    'text-red-600 dark:text-red-400': server.consecutive_failures > 0
                }"
                x-text="server.consecutive_failures || 0"></p>
        </div>
    </div>

    <!-- Error Message (if any) -->
    <div x-show="server.last_error" class="mt-3 rounded-md bg-red-50 p-3 dark:bg-red-900/20">
        <p class="text-xs text-red-800 dark:text-red-200" x-text="server.last_error"></p>
    </div>
</div>
