<?php $__env->startSection('content'); ?>
    <div class="space-y-6 animate-fade-in">
        <!-- Page Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your application preferences, privacy settings, and account configuration.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <button type="button"
                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Reset to Defaults
                </button>
            </div>
        </div>

        <!-- Settings Search -->
        <div class="relative">
            <label for="settings-search" class="sr-only">Search settings</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <input type="search" id="settings-search"
                class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 dark:text-white bg-white dark:bg-gray-800 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                placeholder="Search settings...">
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <!-- Settings Navigation -->
            <div class="lg:col-span-1">
                <nav class="space-y-1 sticky top-20" aria-label="Settings navigation" x-data="{ active: 'account' }">
                    <a href="#account" @click.prevent="active = 'account'"
                        :class="active === 'account' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Account
                    </a>
                    <a href="#privacy" @click.prevent="active = 'privacy'"
                        :class="active === 'privacy' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Privacy & Data
                    </a>
                    <a href="#ai" @click.prevent="active = 'ai'"
                        :class="active === 'ai' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        AI Configuration
                    </a>
                    <a href="#appearance" @click.prevent="active = 'appearance'"
                        :class="active === 'appearance' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                        </svg>
                        Appearance
                    </a>
                    <a href="#accessibility" @click.prevent="active = 'accessibility'"
                        :class="active === 'accessibility' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Accessibility
                    </a>
                    <a href="#notifications" @click.prevent="active = 'notifications'"
                        :class="active === 'notifications' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        Notifications
                    </a>
                    <a href="#gameplay" @click.prevent="active = 'gameplay'"
                        :class="active === 'gameplay' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Gameplay
                    </a>
                    <a href="#advanced" @click.prevent="active = 'advanced'"
                        :class="active === 'advanced' ?
                            'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' :
                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <svg class="shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Advanced
                    </a>
                </nav>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Account Settings -->
                <div id="account" class="bg-white dark:bg-gray-800 shadow rounded-lg animate-fade-in-delay-1">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Account Settings
                        </h3>
                        <form class="space-y-6">
                            <!-- Profile Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Profile Information
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="display-name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Display Name
                                        </label>
                                        <input type="text" id="display-name" value="User"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Email Address
                                        </label>
                                        <input type="email" id="email" value="user@example.com"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="language"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Language
                                        </label>
                                        <select id="language"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>English</option>
                                            <option>日本語 (Japanese)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="timezone"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Timezone
                                        </label>
                                        <select id="timezone"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option>UTC</option>
                                            <option selected>Asia/Tokyo</option>
                                            <option>America/New_York</option>
                                            <option>Europe/London</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Settings -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Security</h4>
                                <div class="space-y-3">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Change Password
                                    </button>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="two-factor"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Two-Factor Authentication
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Add an extra layer of security
                                            </p>
                                        </div>
                                        <button type="button" id="two-factor"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable two-factor authentication</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Management -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Account Management
                                </h4>
                                <div class="space-y-3">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Export Account Data
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-error-300 dark:border-error-600 shadow-sm text-sm leading-4 font-medium rounded-md text-error-700 dark:text-error-300 bg-white dark:bg-gray-700 hover:bg-error-50 dark:hover:bg-error-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-500">
                                        Delete Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Privacy & Data Control -->
                <div id="privacy" class="bg-white dark:bg-gray-800 shadow rounded-lg animate-fade-in-delay-2">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Privacy & Data Control
                        </h3>
                        <div class="space-y-6">
                            <!-- Data Sharing Preferences -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data Sharing
                                    Preferences</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    All features are opt-in. Your data stays local by default.
                                </p>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="analytics"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Anonymous Analytics
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Help improve the app with usage data
                                            </p>
                                        </div>
                                        <button type="button" id="analytics"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable anonymous analytics</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="cloud-backup"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Cloud Backup
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Backup your data to the cloud
                                            </p>
                                        </div>
                                        <button type="button" id="cloud-backup"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable cloud backup</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="cloud-ai"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Cloud AI Processing
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Use AWS Bedrock for complex AI tasks
                                            </p>
                                        </div>
                                        <button type="button" id="cloud-ai"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable cloud AI processing</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- External Service Integration -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">External Services
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="umapyoi"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                umapyoi.net API
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Community game data integration
                                            </p>
                                        </div>
                                        <button type="button" id="umapyoi"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable umapyoi.net integration</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="umamusumedb"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                UmamusumeDB.com
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Calculator tools and meta analysis
                                            </p>
                                        </div>
                                        <button type="button" id="umamusumedb"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable UmamusumeDB integration</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Management -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data Management</h4>
                                <div class="space-y-3">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Export All Data (JSON)
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Clear Local Cache
                                    </button>
                                    <div class="mt-2">
                                        <a href="<?php echo e(route('privacy.policy')); ?>"
                                            class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                            View Privacy Policy →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- AI Configuration -->
                <div id="ai" class="bg-white dark:bg-gray-800 shadow rounded-lg animate-fade-in-delay-3">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            AI Configuration
                        </h3>
                        <div class="space-y-6">
                            <!-- AI Model Selection -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">AI Model Selection
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="ai-provider"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Preferred AI Provider
                                        </label>
                                        <select id="ai-provider"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>Ollama (Local - Privacy First)</option>
                                            <option>AWS Bedrock (Cloud - Advanced)</option>
                                            <option>Hybrid (Auto-select based on task)</option>
                                        </select>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Local models run on your device. Cloud models require internet and may incur
                                            costs.
                                        </p>
                                    </div>
                                    <div>
                                        <label for="ai-model"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Model Preference
                                        </label>
                                        <select id="ai-model"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <optgroup label="Local Models (Ollama)">
                                                <option selected>Llama 3.3 (Recommended)</option>
                                                <option>Mistral</option>
                                                <option>Qwen 2.5</option>
                                            </optgroup>
                                            <optgroup label="Cloud Models (AWS Bedrock)">
                                                <option>Claude 4.5 Opus ($5/$25)</option>
                                                <option>Claude 4.5 Sonnet ($3/$15)</option>
                                                <option>Claude 4.5 Haiku ($1/$5)</option>
                                                <option>Nova 2 Lite ($0.00125)</option>
                                                <option>Nova 2 Pro (Preview)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Budget Management -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Budget Management
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label for="daily-limit"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Daily Limit
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" id="daily-limit" value="0.50" step="0.10"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pl-7 pr-12 focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="weekly-limit"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Weekly Limit
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" id="weekly-limit" value="5.00" step="0.50"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pl-7 pr-12 focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="monthly-limit"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Monthly Limit
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" id="monthly-limit" value="10.00" step="1.00"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pl-7 pr-12 focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Current month usage: $0.00 / $10.00
                                </p>
                            </div>

                            <!-- AI Behavior -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">AI Behavior</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="recommendation-frequency"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Recommendation Frequency
                                        </label>
                                        <select id="recommendation-frequency"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>Per Turn (Automatic)</option>
                                            <option>Per Session</option>
                                            <option>Manual Only</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="explanation-detail"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Explanation Detail Level
                                        </label>
                                        <select id="explanation-detail"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option>Brief</option>
                                            <option selected>Detailed</option>
                                            <option>Expert</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="show-model"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Show Which Model is Used
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Display model name in responses
                                            </p>
                                        </div>
                                        <button type="button" id="show-model"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Show model transparency</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Appearance Settings -->
                <div id="appearance" class="bg-white dark:bg-gray-800 shadow rounded-lg animate-fade-in-delay-4">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Appearance
                        </h3>
                        <div class="space-y-6">
                            <!-- Theme Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Theme
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button type="button"
                                        class="relative flex flex-col items-center justify-center rounded-lg border-2 border-primary-500 bg-white p-4 focus:outline-none">
                                        <svg class="h-8 w-8 text-gray-900 mb-2" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">Light</span>
                                        <span
                                            class="pointer-events-none absolute -inset-px rounded-lg border-2 border-primary-500"
                                            aria-hidden="true"></span>
                                    </button>
                                    <button type="button"
                                        class="relative flex flex-col items-center justify-center rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-4 hover:border-gray-400 focus:outline-none">
                                        <svg class="h-8 w-8 text-gray-900 dark:text-white mb-2" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Dark</span>
                                    </button>
                                    <button type="button"
                                        class="relative flex flex-col items-center justify-center rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-4 hover:border-gray-400 focus:outline-none">
                                        <svg class="h-8 w-8 text-gray-900 dark:text-white mb-2" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">System</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Layout Preferences -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Layout</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="compact-mode"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Compact Mode
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Reduce spacing for more content
                                            </p>
                                        </div>
                                        <button type="button" id="compact-mode"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable compact mode</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="animations"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Animations
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Enable smooth transitions
                                            </p>
                                        </div>
                                        <button type="button" id="animations"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable animations</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div>
                                        <label for="font-size"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Font Size: <span id="font-size-value">100%</span>
                                        </label>
                                        <input type="range" id="font-size" min="100" max="200"
                                            value="100" step="10"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accessibility Settings -->
                <div id="accessibility" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Accessibility (WCAG 2.2 AA Compliant)
                        </h3>
                        <div class="space-y-6">
                            <!-- Visual Accessibility -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Visual</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="high-contrast"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                High Contrast Mode
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Increase contrast for better visibility
                                            </p>
                                        </div>
                                        <button type="button" id="high-contrast"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable high contrast mode</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="reduced-motion"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Reduced Motion
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Minimize animations and transitions
                                            </p>
                                        </div>
                                        <button type="button" id="reduced-motion"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable reduced motion</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div>
                                        <label for="colorblind-mode"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Color Blind Mode
                                        </label>
                                        <select id="colorblind-mode"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>None</option>
                                            <option>Deuteranopia (Red-Green)</option>
                                            <option>Protanopia (Red-Green)</option>
                                            <option>Tritanopia (Blue-Yellow)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Screen Reader Support -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Screen Reader</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="screen-reader-opt"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Screen Reader Optimization
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Enhanced descriptions for assistive tech
                                            </p>
                                        </div>
                                        <button type="button" id="screen-reader-opt"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable screen reader optimization</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div>
                                        <a href="<?php echo e(route('keyboard.shortcuts')); ?>"
                                            class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                            View Keyboard Shortcuts →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Notification Settings -->
                <div id="notifications" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Notifications
                        </h3>
                        <div class="space-y-6">
                            <!-- Notification Types -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Notification Types
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="training-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Training Recommendations
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                AI-powered training suggestions
                                            </p>
                                        </div>
                                        <button type="button" id="training-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable training notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="race-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Race Deadlines
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Upcoming race reminders
                                            </p>
                                        </div>
                                        <button type="button" id="race-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable race notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="goal-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Goal Progress Updates
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Milestone achievements
                                            </p>
                                        </div>
                                        <button type="button" id="goal-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable goal notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="budget-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Budget Alerts
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                AI spending limit warnings
                                            </p>
                                        </div>
                                        <button type="button" id="budget-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable budget notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Channels -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Channels</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="in-app-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                In-App Notifications
                                            </label>
                                        </div>
                                        <button type="button" id="in-app-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable in-app notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="sound-notif"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Sound Notifications
                                            </label>
                                        </div>
                                        <button type="button" id="sound-notif"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable sound notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quiet Hours -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Quiet Hours</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="quiet-start"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Start Time
                                        </label>
                                        <input type="time" id="quiet-start" value="22:00"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="quiet-end"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            End Time
                                        </label>
                                        <input type="time" id="quiet-end" value="08:00"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gameplay Preferences -->
                <div id="gameplay" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Gameplay Preferences
                        </h3>
                        <div class="space-y-6">
                            <!-- Training Preferences -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Training</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="default-facility"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Default Training Facility
                                        </label>
                                        <select id="default-facility"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>Speed</option>
                                            <option>Stamina</option>
                                            <option>Power</option>
                                            <option>Guts</option>
                                            <option>Wisdom</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="auto-accept"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Auto-Accept AI Recommendations
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Automatically apply AI suggestions
                                            </p>
                                        </div>
                                        <button type="button" id="auto-accept"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable auto-accept recommendations</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Skill Management -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Skills</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="skill-hints"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Skill Hint Notifications
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Alert when hints reduce SP cost
                                            </p>
                                        </div>
                                        <button type="button" id="skill-hints"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable skill hint notifications</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="sp-budget"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                SP Budget Alerts
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Warn when approaching SP limit
                                            </p>
                                        </div>
                                        <button type="button" id="sp-budget"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable SP budget alerts</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Support Cards -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Support Cards</h4>
                                <div>
                                    <label for="card-sorting"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Card Sorting
                                    </label>
                                    <select id="card-sorting"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                        <option>Rarity (Highest First)</option>
                                        <option selected>Meta Tier</option>
                                        <option>Type (Speed/Stamina/etc.)</option>
                                        <option>Friendship Level</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div id="advanced" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                            Advanced Settings
                        </h3>
                        <div class="space-y-6">
                            <!-- Data & Performance -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data & Performance
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="auto-save"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Auto-Save Frequency
                                        </label>
                                        <select id="auto-save"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>Every Turn</option>
                                            <option>Every 5 Turns</option>
                                            <option>Manual Only</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="backup-frequency"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Backup Frequency
                                        </label>
                                        <select id="backup-frequency"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                            <option selected>Daily</option>
                                            <option>Weekly</option>
                                            <option>Monthly</option>
                                            <option>Manual Only</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="lazy-loading"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Lazy Loading
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Load content as needed for better performance
                                            </p>
                                        </div>
                                        <button type="button" id="lazy-loading"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-primary-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="true">
                                            <span class="sr-only">Enable lazy loading</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Developer Options -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Developer Options
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="debug-mode"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Debug Mode
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Show detailed error messages
                                            </p>
                                        </div>
                                        <button type="button" id="debug-mode"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable debug mode</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="error-reporting"
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Error Reporting
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Send crash reports to developers
                                            </p>
                                        </div>
                                        <button type="button" id="error-reporting"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 dark:bg-gray-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                            role="switch" aria-checked="false">
                                            <span class="sr-only">Enable error reporting</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end gap-3">
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/settings/index.blade.php ENDPATH**/ ?>