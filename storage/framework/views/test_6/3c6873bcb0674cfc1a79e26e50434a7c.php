<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['servers' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['servers' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="server-status-indicator space-y-3" x-data="serverStatusIndicator()" x-init="initialize()">

    
    <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">MCP Servers</span>
        <button @click="refreshStatus()" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
            :class="{ 'animate-spin': isRefreshing }" aria-label="Refresh server status">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>

    
    <div class="space-y-2">
        <template x-for="server in servers" :key="server.name">
            <div class="p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                :class="{
                                    'animate-ping bg-green-400': server.status === 'healthy',
                                    'bg-yellow-400': server.status === 'degraded',
                                    'bg-red-400': server.status === 'unhealthy'
                                }"
                                x-show="server.status === 'healthy'"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2"
                                :class="{
                                    'bg-green-500': server.status === 'healthy',
                                    'bg-yellow-500': server.status === 'degraded',
                                    'bg-red-500': server.status === 'unhealthy',
                                    'bg-gray-500': server.status === 'offline'
                                }"></span>
                        </span>

                        
                        <span class="font-medium text-sm text-gray-900 dark:text-white"
                            x-text="server.display_name"></span>
                    </div>

                    
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                        :class="{
                            'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300': server
                                .status === 'healthy',
                            'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300': server
                                .status === 'degraded',
                            'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300': server.status === 'unhealthy',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': server
                                .status === 'offline'
                        }"
                        x-text="server.status"></span>
                </div>

                
                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-center justify-between">
                        <span>Response Time:</span>
                        <span class="font-medium"
                            x-text="server.response_time ? `${server.response_time}ms` : 'N/A'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Uptime:</span>
                        <span class="font-medium" x-text="server.uptime || 'N/A'"></span>
                    </div>
                    <template x-if="server.last_check">
                        <div class="flex items-center justify-between">
                            <span>Last Check:</span>
                            <span class="font-medium" x-text="formatTime(server.last_check)"></span>
                        </div>
                    </template>
                </div>

                
                <template x-if="server.error_message">
                    <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded text-xs text-red-700 dark:text-red-300">
                        <span x-text="server.error_message"></span>
                    </div>
                </template>

                
                <template x-if="server.capabilities && server.capabilities.length > 0">
                    <div class="mt-2 flex flex-wrap gap-1">
                        <template x-for="capability in server.capabilities" :key="capability">
                            <span
                                class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full"
                                x-text="capability"></span>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        
        <template x-if="servers.length === 0">
            <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
                <p>No MCP servers configured</p>
            </div>
        </template>
    </div>

    
    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-700 dark:text-gray-300">Overall Health:</span>
            <span class="font-medium"
                :class="{
                    'text-green-600 dark:text-green-400': overallHealth === 'healthy',
                    'text-yellow-600 dark:text-yellow-400': overallHealth === 'degraded',
                    'text-red-600 dark:text-red-400': overallHealth === 'unhealthy'
                }"
                x-text="overallHealth"></span>
        </div>
        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
            <span x-text="`${healthyCount}/${servers.length} servers healthy`"></span>
        </div>
    </div>
</div>

<script>
    function serverStatusIndicator() {
        return {
            servers: [],
            isRefreshing: false,
            overallHealth: 'unknown',
            healthyCount: 0,

            initialize() {
                this.refreshStatus();
                // Auto-refresh every 5 seconds
                setInterval(() => this.refreshStatus(), 5000);
            },

            async refreshStatus() {
                this.isRefreshing = true;

                try {
                    const response = await fetch('/api/ai/chat/server-status');
                    const data = await response.json();

                    if (data.servers) {
                        this.servers = Object.entries(data.servers).map(([name, server]) => ({
                            name: name,
                            display_name: server.display_name || name,
                            status: server.status || 'offline',
                            response_time: server.response_time,
                            uptime: server.uptime,
                            last_check: server.last_check,
                            error_message: server.error_message,
                            capabilities: server.capabilities || []
                        }));

                        this.calculateOverallHealth();
                    }
                } catch (error) {
                    console.error('Failed to fetch server status:', error);
                } finally {
                    this.isRefreshing = false;
                }
            },

            calculateOverallHealth() {
                if (this.servers.length === 0) {
                    this.overallHealth = 'unknown';
                    this.healthyCount = 0;
                    return;
                }

                this.healthyCount = this.servers.filter(s => s.status === 'healthy').length;
                const healthyPercentage = (this.healthyCount / this.servers.length) * 100;

                if (healthyPercentage === 100) {
                    this.overallHealth = 'healthy';
                } else if (healthyPercentage >= 50) {
                    this.overallHealth = 'degraded';
                } else {
                    this.overallHealth = 'unhealthy';
                }
            },

            formatTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // seconds

                if (diff < 60) return `${diff}s ago`;
                if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
                if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
                return `${Math.floor(diff / 86400)}d ago`;
            }
        };
    }
</script>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/ai/server-status-indicator.blade.php ENDPATH**/ ?>