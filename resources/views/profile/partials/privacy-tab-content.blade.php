<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Privacy & Data Management</h2>

        <div class="space-y-6">
            <div class="space-y-4">
                <!-- Local-First Storage -->
                <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Local-First Storage</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Keep all data stored locally on your device
                        </p>
                    </div>
                    <input type="hidden" name="preferences[privacy][local_first]" value="0">
                    <input type="checkbox" name="preferences[privacy][local_first]" value="1"
                        {{ old('preferences.privacy.local_first', optional($user->preferences)['privacy']['local_first'] ?? false) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>

                <!-- Cloud AI Features -->
                <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Cloud AI Features</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Enable AWS Bedrock for advanced AI
                            recommendations</p>
                    </div>
                    <input type="hidden" name="ai_settings[enable_cloud_ai]" value="0">
                    <input type="checkbox" name="ai_settings[enable_cloud_ai]" value="1"
                        {{ old('ai_settings.enable_cloud_ai', optional($user->ai_settings)['enable_cloud_ai'] ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>

                <!-- External API Integration -->
                <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">External API Integration</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sync with umapyoi.net and UmamusumeDB</p>
                    </div>
                    <input type="hidden" name="mcp_settings[enable_external_apis]" value="0">
                    <input type="checkbox" name="mcp_settings[enable_external_apis]" value="1"
                        {{ old('mcp_settings.enable_external_apis', optional($user->mcp_settings)['enable_external_apis'] ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>

                <!-- Usage Analytics -->
                <div class="flex items-center justify-between py-3">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Usage Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Help improve the app with anonymous usage
                            data</p>
                    </div>
                    <input type="hidden" name="mcp_settings[enable_analytics]" value="0">
                    <input type="checkbox" name="mcp_settings[enable_analytics]" value="1"
                        {{ old('mcp_settings.enable_analytics', optional($user->mcp_settings)['enable_analytics'] ?? false) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Management -->
<div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Data Management</h2>

        <div class="space-y-4">
            <!-- Export My Data -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Export My Data</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Download all your data in JSON format</p>
                </div>
                <form action="{{ route('profile.export') }}" method="GET" class="inline">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        Export Data
                    </button>
                </form>
            </div>

            <!-- Import Data -->
            <div class="flex items-center justify-between py-3">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Import Data</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Import data from a previous export</p>
                </div>
                <a href="{{ route('import.index') }}" class="btn btn-sm btn-secondary">
                    Import Data
                </a>
            </div>
        </div>
    </div>
</div>
