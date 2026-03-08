@props(['settings' => []])

<div class="rounded-lg border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
    <h3 class="mb-6 text-lg font-semibold text-neutral-900 dark:text-white">MCP Settings & Controls</h3>

    <!-- MCP Server Preferences -->
    <div class="mb-6">
        <h4 class="mb-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300">MCP Server Preferences</h4>
        <div class="space-y-3">
            <template x-for="(server, name) in settings.servers" :key="name">
                <div
                    class="flex items-center justify-between rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" :id="'server-' + name" :name="'server_' + name" x-model="server.enabled"
                            @change="$dispatch('toggle-server', { serverName: name, enabled: server.enabled })"
                            class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:ring-offset-neutral-800">
                        <label :for="'server-' + name" class="flex-1 cursor-pointer">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white" x-text="server.name"></p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400" x-text="server.description"></p>
                        </label>
                    </div>
                    <button @click="$dispatch('configure-server', { serverName: name })"
                        class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-700 dark:hover:text-neutral-300"
                        title="Configure Server">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Agent Configuration -->
    <div class="mb-6">
        <h4 class="mb-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Default Agent Configuration</h4>
        <div class="space-y-4">
            <!-- Training Agent -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Training Optimization
                    Agent</label>
                <select x-model="settings.agents.training"
                    @change="$dispatch('update-agent-config', { type: 'training', value: settings.agents.training })"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                    <option value="auto">Auto-select (Recommended)</option>
                    <option value="ollama">Ollama (Local)</option>
                    <option value="bedrock">AWS Bedrock (Cloud)</option>
                    <option value="agent">MCP Agent</option>
                </select>
            </div>

            <!-- Career Planning Agent -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Career Planning Agent</label>
                <select x-model="settings.agents.career"
                    @change="$dispatch('update-agent-config', { type: 'career', value: settings.agents.career })"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                    <option value="auto">Auto-select (Recommended)</option>
                    <option value="ollama">Ollama (Local)</option>
                    <option value="bedrock">AWS Bedrock (Cloud)</option>
                    <option value="agent">MCP Agent</option>
                </select>
            </div>

            <!-- Race Strategy Agent -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Race Strategy Agent</label>
                <select x-model="settings.agents.race"
                    @change="$dispatch('update-agent-config', { type: 'race', value: settings.agents.race })"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                    <option value="auto">Auto-select (Recommended)</option>
                    <option value="ollama">Ollama (Local)</option>
                    <option value="bedrock">AWS Bedrock (Cloud)</option>
                    <option value="agent">MCP Agent</option>
                </select>
            </div>

            <!-- Skill Optimization Agent -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Skill Optimization
                    Agent</label>
                <select x-model="settings.agents.skill"
                    @change="$dispatch('update-agent-config', { type: 'skill', value: settings.agents.skill })"
                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                    <option value="auto">Auto-select (Recommended)</option>
                    <option value="ollama">Ollama (Local)</option>
                    <option value="bedrock">AWS Bedrock (Cloud)</option>
                    <option value="agent">MCP Agent</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Budget Limits -->
    <div class="mb-6">
        <h4 class="mb-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Budget Limits</h4>
        <div class="space-y-4">
            <!-- Daily Budget -->
            <div>
                <label for="daily-budget-input" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Daily
                    Budget Limit</label>
                <div class="mt-1 flex items-center space-x-2">
                    <span class="text-neutral-500 dark:text-neutral-400">$</span>
                    <input type="number" id="daily-budget-input" name="daily_budget" x-model="settings.budget.daily"
                        @change="$dispatch('update-budget-limit', { period: 'daily', value: settings.budget.daily })"
                        step="0.01" min="0"
                        class="block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                </div>
            </div>

            <!-- Monthly Budget -->
            <div>
                <label for="monthly-budget-input"
                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Monthly Budget Limit</label>
                <div class="mt-1 flex items-center space-x-2">
                    <span class="text-neutral-500 dark:text-neutral-400">$</span>
                    <input type="number" id="monthly-budget-input" name="monthly_budget"
                        x-model="settings.budget.monthly"
                        @change="$dispatch('update-budget-limit', { period: 'monthly', value: settings.budget.monthly })"
                        step="0.01" min="0"
                        class="block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm">
                </div>
            </div>

            <!-- Budget Alert Threshold -->
            <div>
                <label for="alert-threshold-input" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Alert Threshold (<span x-text="settings.budget.alert_threshold"></span>%)
                </label>
                <input type="range" id="alert-threshold-input" name="alert_threshold"
                    x-model="settings.budget.alert_threshold"
                    @change="$dispatch('update-budget-threshold', { value: settings.budget.alert_threshold })"
                    min="50" max="100" step="5" class="mt-2 w-full">
                <div class="mt-1 flex justify-between text-xs text-neutral-500 dark:text-neutral-400">
                    <span>50%</span>
                    <span>75%</span>
                    <span>100%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Preferences -->
    <div class="mb-6">
        <h4 class="mb-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Performance Preferences</h4>
        <div class="space-y-3">
            <!-- Auto-fallback -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Auto-fallback to Cloud</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Automatically use AWS Bedrock when local
                        processing is slow</p>
                </div>
                <input type="checkbox" id="auto-fallback-toggle" name="auto_fallback"
                    x-model="settings.performance.auto_fallback"
                    @change="$dispatch('update-performance-setting', { key: 'auto_fallback', value: settings.performance.auto_fallback })"
                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:ring-offset-neutral-800">
            </div>

            <!-- Parallel Processing -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Parallel Agent Processing</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Run multiple agents simultaneously for faster
                        results</p>
                </div>
                <input type="checkbox" id="parallel-processing-toggle" name="parallel_processing"
                    x-model="settings.performance.parallel_processing"
                    @change="$dispatch('update-performance-setting', { key: 'parallel_processing', value: settings.performance.parallel_processing })"
                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:ring-offset-neutral-800">
            </div>

            <!-- Cache Responses -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Cache AI Responses</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Cache similar requests to reduce costs and
                        improve speed</p>
                </div>
                <input type="checkbox" id="cache-responses-toggle" name="cache_responses"
                    x-model="settings.performance.cache_responses"
                    @change="$dispatch('update-performance-setting', { key: 'cache_responses', value: settings.performance.cache_responses })"
                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:ring-offset-neutral-800">
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex space-x-3 border-t border-neutral-200 pt-4 dark:border-neutral-700">
        <button @click="$dispatch('save-settings')"
            class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800">
            Save Settings
        </button>
        <button @click="$dispatch('reset-settings')"
            class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:focus:ring-offset-neutral-800">
            Reset to Defaults
        </button>
    </div>
</div>
