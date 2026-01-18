@extends('layouts.app')

@section('title', 'Your Profile')

@section('content')
    <div class="space-y-6" x-data="profileManager()">
        <!-- Page Header -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Profile</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Manage your account settings, preferences, and privacy controls
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-2 overflow-x-auto scrollbar-hide" aria-label="Profile sections">
                <button @click="activeTab = 'account'"
                    :class="activeTab === 'account' ?
                        'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                        'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                    role="tab" :aria-selected="activeTab === 'account'">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Account
                    </span>
                </button>
                <button @click="activeTab = 'preferences'"
                    :class="activeTab === 'preferences' ?
                        'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                        'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                    role="tab" :aria-selected="activeTab === 'preferences'">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Preferences
                    </span>
                </button>
                <button @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ?
                        'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                        'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                    role="tab" :aria-selected="activeTab === 'notifications'">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notifications
                    </span>
                </button>
                <button @click="activeTab = 'privacy'"
                    :class="activeTab === 'privacy' ?
                        'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                        'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                    role="tab" :aria-selected="activeTab === 'privacy'">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Privacy & Data
                    </span>
                </button>
                <button @click="activeTab = 'security'"
                    :class="activeTab === 'security' ?
                        'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                        'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                    role="tab" :aria-selected="activeTab === 'security'">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Security
                    </span>
                </button>
            </nav>
        </div>

        <!-- Account Tab -->
        <div x-show="activeTab === 'account'" x-transition role="tabpanel">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Profile Information</h2>
                    <form @submit.prevent="saveProfile" class="space-y-6">
                        <div class="flex items-center gap-4">
                            <img class="h-20 w-20 rounded-full bg-gray-50 ring-2 ring-gray-200 dark:ring-gray-700"
                                src="https://ui-avatars.com/api/?name=User&background=3b82f6&color=fff&size=128"
                                alt="User avatar">
                            <div>
                                <button type="button"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                    Change Avatar
                                </button>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG or GIF. Max 2MB.</p>
                            </div>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Display Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" x-model="profile.name" required
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="email" id="email" name="email" x-model="profile.email" required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <span
                                    class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Verified
                                </span>
                            </div>
                        </div>
                        <div>
                            <label for="bio"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
                            <textarea id="bio" name="bio" rows="3" x-model="profile.bio"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                placeholder="Tell us about yourself..."></textarea>
                        </div>
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Account Statistics</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Characters Created</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Training Sessions</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Races Completed</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preferences Tab -->
        <div x-show="activeTab === 'preferences'" x-transition role="tabpanel">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Application Preferences</h2>
                    <form @submit.prevent="savePreferences" class="space-y-6">
                        <div>
                            <label for="theme"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Theme</label>
                            <select id="theme" name="theme" x-model="preferences.theme"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="auto">Auto (System)</option>
                            </select>
                        </div>
                        <div>
                            <label for="language"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
                            <select id="language" name="language" x-model="preferences.language"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="en">English</option>
                                <option value="ja">日本語 (Japanese)</option>
                            </select>
                        </div>
                        <div>
                            <label for="timezone"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                            <select id="timezone" name="timezone" x-model="preferences.timezone"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="UTC">UTC</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                            </select>
                        </div>
                        <div>
                            <label for="textSize"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Text Size</label>
                            <select id="textSize" name="textSize" x-model="preferences.textSize"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                                <option value="xlarge">Extra Large</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div x-show="activeTab === 'notifications'" x-transition role="tabpanel">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notification Settings</h2>
                    <form @submit.prevent="saveNotifications" class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="emailNotifications"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Email
                                        Notifications</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Receive email updates about your
                                        account</p>
                                </div>
                                <input type="checkbox" id="emailNotifications" x-model="notifications.email"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="trainingReminders"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Training
                                        Reminders</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Get notified about optimal training
                                        times</p>
                                </div>
                                <input type="checkbox" id="trainingReminders" x-model="notifications.trainingReminders"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="raceAlerts"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Race Alerts</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Notifications for upcoming races
                                    </p>
                                </div>
                                <input type="checkbox" id="raceAlerts" x-model="notifications.raceAlerts"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="aiRecommendations"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">AI
                                        Recommendations</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Get AI-powered strategy suggestions
                                    </p>
                                </div>
                                <input type="checkbox" id="aiRecommendations" x-model="notifications.aiRecommendations"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Save Notifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Privacy & Data Tab -->
        <div x-show="activeTab === 'privacy'" x-transition role="tabpanel">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Privacy & Data Management</h2>
                    <form @submit.prevent="savePrivacy" class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="localStorage"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Local-First
                                        Storage</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Keep all data stored locally on
                                        your device</p>
                                </div>
                                <input type="checkbox" id="localStorage" x-model="privacy.localStorage" checked disabled
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="cloudAI"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Cloud AI
                                        Features</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable AWS Bedrock for advanced AI
                                        recommendations</p>
                                </div>
                                <input type="checkbox" id="cloudAI" x-model="privacy.cloudAI"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="externalAPIs"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">External API
                                        Integration</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sync with umapyoi.net and
                                        UmamusumeDB</p>
                                </div>
                                <input type="checkbox" id="externalAPIs" x-model="privacy.externalAPIs"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <label for="analytics"
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Usage
                                        Analytics</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Help improve the app with anonymous
                                        usage data</p>
                                </div>
                                <input type="checkbox" id="analytics" x-model="privacy.analytics"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data Management</h3>
                            <div class="flex gap-3">
                                <button type="button" @click="exportData"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                    Export My Data
                                </button>
                                <button type="button" @click="importData"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                    Import Data
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                Save Privacy Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div x-show="activeTab === 'security'" x-transition role="tabpanel">
            <div class="space-y-6">
                <!-- Change Password -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h2>
                        <form @submit.prevent="changePassword" class="space-y-4">
                            <div>
                                <label for="currentPassword"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current
                                    Password</label>
                                <input type="password" id="currentPassword" x-model="security.currentPassword"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="newPassword"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New
                                    Password</label>
                                <input type="password" id="newPassword" x-model="security.newPassword"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="confirmPassword"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New
                                    Password</label>
                                <input type="password" id="confirmPassword" x-model="security.confirmPassword"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Active Sessions</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Current Session</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Windows • Chrome • Last active:
                                        Now</div>
                                </div>
                                <span class="text-xs text-green-600 dark:text-green-400 font-medium">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border-2 border-red-200 dark:border-red-900">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Delete Account</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Permanently delete your account
                                        and all data</div>
                                </div>
                                <button type="button" @click="confirmDeleteAccount"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function profileManager() {
        return {
            activeTab: 'account',
            loading: false,
            profile: {
                name: '{{ $user->name }}',
                email: '{{ $user->email }}',
                bio: ''
            },
                preferences: {!! json_encode(
                    $user->preferences ?? [
                        'theme' => 'auto',
                        'language' => 'en',
                        'timezone' => 'UTC',
                        'textSize' => 'medium',
                    ],
                ) !!},
                notifications: {
                    email: true,
                    trainingReminders: true,
                    raceAlerts: true,
                    aiRecommendations: false
                },
                privacy: {
                    localStorage: true,
                    cloudAI: false,
                    externalAPIs: true,
                    analytics: false
                },
                security: {
                    currentPassword: '',
                    newPassword: '',
                    confirmPassword: ''
                },

                async saveProfile() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.profile.name,
                                email: this.profile.email
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccess('Profile updated successfully!');
                        } else {
                            this.showError(data.message || 'Failed to update profile');
                        }
                    } catch (error) {
                        this.showError('An error occurred while updating your profile');
                        console.error('Profile update error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async savePreferences() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                preferences: this.preferences
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccess('Preferences saved successfully!');
                        } else {
                            this.showError(data.message || 'Failed to save preferences');
                        }
                    } catch (error) {
                        this.showError('An error occurred while saving preferences');
                        console.error('Preferences save error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async saveNotifications() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                preferences: {
                                    notifications: this.notifications
                                }
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccess('Notification settings saved successfully!');
                        } else {
                            this.showError(data.message || 'Failed to save notification settings');
                        }
                    } catch (error) {
                        this.showError('An error occurred while saving notification settings');
                        console.error('Notifications save error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async savePrivacy() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                preferences: {
                                    privacy: this.privacy
                                }
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccess('Privacy settings saved successfully!');
                        } else {
                            this.showError(data.message || 'Failed to save privacy settings');
                        }
                    } catch (error) {
                        this.showError('An error occurred while saving privacy settings');
                        console.error('Privacy save error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async changePassword() {
                    if (this.security.newPassword !== this.security.confirmPassword) {
                        this.showError('Passwords do not match!');
                        return;
                    }

                    if (this.security.newPassword.length < 8) {
                        this.showError('Password must be at least 8 characters long');
                        return;
                    }

                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.password.change') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                current_password: this.security.currentPassword,
                                password: this.security.newPassword,
                                password_confirmation: this.security.confirmPassword
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showSuccess('Password changed successfully!');
                            this.security = {
                                currentPassword: '',
                                newPassword: '',
                                confirmPassword: ''
                            };
                        } else {
                            this.showError(data.message || 'Failed to change password');
                        }
                    } catch (error) {
                        this.showError('An error occurred while changing password');
                        console.error('Password change error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                resetForm() {
                    // Reset to original values
                    this.profile = {
                        name: '{{ $user->name }}',
                        email: '{{ $user->email }}',
                        bio: ''
                    };
                    this.preferences = {!! json_encode(
                        $user->preferences ?? [
                            'theme' => 'auto',
                            'language' => 'en',
                            'timezone' => 'UTC',
                            'textSize' => 'medium',
                        ],
                    ) !!};
                },

                async exportData() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.export') }}', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const blob = new Blob([JSON.stringify(data, null, 2)], {
                                type: 'application/json'
                            });
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `profile-export-${new Date().toISOString().split('T')[0]}.json`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            this.showSuccess('Data exported successfully!');
                        } else {
                            this.showError('Failed to export data');
                        }
                    } catch (error) {
                        this.showError('An error occurred while exporting data');
                        console.error('Export error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                importData() {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'application/json';
                    input.onchange = async (e) => {
                        const file = e.target.files[0];
                        if (!file) return;

                        try {
                            const text = await file.text();
                            const data = JSON.parse(text);
                            console.log('Imported data:', data);
                            this.showSuccess('Data imported successfully! (Feature coming soon)');
                        } catch (error) {
                            this.showError('Invalid JSON file');
                            console.error('Import error:', error);
                        }
                    };
                    input.click();
                },

                async confirmDeleteAccount() {
                    const confirmation = prompt('To delete your account, type DELETE in all caps:');

                    if (confirmation !== 'DELETE') {
                        if (confirmation !== null) {
                            this.showError('Account deletion cancelled. You must type DELETE exactly.');
                        }
                        return;
                    }

                    const password = prompt('Enter your password to confirm:');
                    if (!password) {
                        this.showError('Password is required to delete your account');
                        return;
                    }

                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('profile.destroy') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                password: password,
                                confirmation: 'DELETE'
                            })
                        });

                        if (response.ok) {
                            window.location.href = '{{ route('welcome') }}';
                        } else {
                            const data = await response.json();
                            this.showError(data.message || 'Failed to delete account');
                        }
                    } catch (error) {
                        this.showError('An error occurred while deleting account');
                        console.error('Account deletion error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                showSuccess(message) {
                    // You can replace this with a toast notification library
                    alert(message);
                },

                showError(message) {
                    // You can replace this with a toast notification library
                    alert('Error: ' + message);
                }
            };
        }
</script>
@endpush
