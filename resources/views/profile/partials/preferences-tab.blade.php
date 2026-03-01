<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Application Preferences</h2>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Theme -->
            <div>
                <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Theme
                </label>
                <select name="preferences[theme]" id="theme"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="light"
                        {{ old('preferences.theme', $user->preferences['theme'] ?? 'auto') === 'light' ? 'selected' : '' }}>
                        Light</option>
                    <option value="dark"
                        {{ old('preferences.theme', $user->preferences['theme'] ?? 'auto') === 'dark' ? 'selected' : '' }}>
                        Dark</option>
                    <option value="auto"
                        {{ old('preferences.theme', $user->preferences['theme'] ?? 'auto') === 'auto' ? 'selected' : '' }}>
                        Auto (System)</option>
                </select>
            </div>

            <!-- Language -->
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Language
                </label>
                <select name="preferences[language]" id="language"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="en"
                        {{ old('preferences.language', $user->preferences['language'] ?? 'en') === 'en' ? 'selected' : '' }}>
                        English</option>
                    <option value="ja"
                        {{ old('preferences.language', $user->preferences['language'] ?? 'en') === 'ja' ? 'selected' : '' }}>
                        日本語 (Japanese)</option>
                </select>
            </div>

            <!-- Timezone -->
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Timezone
                </label>
                <select name="preferences[timezone]" id="timezone"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="UTC"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>
                        UTC</option>
                    <option value="America/New_York"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>
                        Eastern Time (US & Canada)</option>
                    <option value="America/Chicago"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'America/Chicago' ? 'selected' : '' }}>
                        Central Time (US & Canada)</option>
                    <option value="America/Denver"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'America/Denver' ? 'selected' : '' }}>
                        Mountain Time (US & Canada)</option>
                    <option value="America/Los_Angeles"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>
                        Pacific Time (US & Canada)</option>
                    <option value="Asia/Tokyo"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'Asia/Tokyo' ? 'selected' : '' }}>
                        Tokyo</option>
                    <option value="Europe/London"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'Europe/London' ? 'selected' : '' }}>
                        London</option>
                    <option value="Europe/Paris"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'Europe/Paris' ? 'selected' : '' }}>
                        Paris</option>
                    <option value="Australia/Sydney"
                        {{ old('preferences.timezone', $user->preferences['timezone'] ?? 'UTC') === 'Australia/Sydney' ? 'selected' : '' }}>
                        Sydney</option>
                </select>
            </div>

            <!-- Time Format -->
            <div>
                <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Time Format
                </label>
                <select name="preferences[time_format]" id="time_format"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="12h"
                        {{ old('preferences.time_format', $user->preferences['time_format'] ?? '12h') === '12h' ? 'selected' : '' }}>
                        12-hour (2:30 PM)</option>
                    <option value="24h"
                        {{ old('preferences.time_format', $user->preferences['time_format'] ?? '12h') === '24h' ? 'selected' : '' }}>
                        24-hour (14:30)</option>
                </select>
            </div>

            <!-- Accessibility Settings -->
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-900 dark:text-white mb-4">Accessibility</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="high_contrast" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                High Contrast Mode
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Increase contrast for better visibility
                            </p>
                        </div>
                        <input type="hidden" name="accessibility_settings[high_contrast]" value="0">
                        <input type="checkbox" name="accessibility_settings[high_contrast]" id="high_contrast"
                            value="1"
                            {{ old('accessibility_settings.high_contrast', $user->accessibility_settings['high_contrast'] ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="large_text" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Large Text
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Increase font size throughout the app
                            </p>
                        </div>
                        <input type="hidden" name="accessibility_settings[large_text]" value="0">
                        <input type="checkbox" name="accessibility_settings[large_text]" id="large_text" value="1"
                            {{ old('accessibility_settings.large_text', $user->accessibility_settings['large_text'] ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="reduce_motion" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reduce Motion
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Minimize animations and transitions</p>
                        </div>
                        <input type="hidden" name="accessibility_settings[reduce_motion]" value="0">
                        <input type="checkbox" name="accessibility_settings[reduce_motion]" id="reduce_motion"
                            value="1"
                            {{ old('accessibility_settings.reduce_motion', $user->accessibility_settings['reduce_motion'] ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <button type="button" onclick="window.location.reload()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>
