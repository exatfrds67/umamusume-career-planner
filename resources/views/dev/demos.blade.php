@extends('layouts.guest')

@section('title', 'Developer Demos - Umamusume Career Planner')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="glass-card rounded-2xl p-8">
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-primary-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Developer Demos
                        </h1>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Interactive demonstrations of implemented features for development and testing purposes
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password Toggle Demo -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Password Visibility Toggle
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Test the password "peek" functionality with eye icon toggles. Demonstrates the reusable password
                            input component with accessibility features.
                        </p>
                        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div>✓ Multiple password fields</div>
                            <div>✓ Independent toggle controls</div>
                            <div>✓ Keyboard accessibility</div>
                            <div>✓ Dark mode support</div>
                        </div>
                        <a href="{{ route('demo.password-toggle') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            View Demo
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>

                    <!-- Remember Me Demo -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-green-600 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Remember Me Functionality
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Interactive demonstration of the "Remember Me" checkbox functionality. Shows how the feature
                            works and its implementation status.
                        </p>
                        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div>✓ Database token storage</div>
                            <div>✓ Persistent authentication</div>
                            <div>✓ Form validation</div>
                            <div>✓ Security implementation</div>
                        </div>
                        <a href="{{ route('demo.remember-me') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            View Demo
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>

                    <!-- External Data Cache Demo -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                External Data Browser
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Browse external API data with offline caching functionality. Demonstrates the database fallback
                            system for external data sources.
                        </p>
                        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div>✓ API data caching</div>
                            <div>✓ Offline functionality</div>
                            <div>✓ Cache status indicators</div>
                            <div>✓ Database fallback</div>
                        </div>
                        <a href="{{ route('external-data.browse') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            View Browser
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>

                    <!-- Authentication Pages -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Authentication Pages
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Test the actual login and register pages with the implemented password toggle and remember me
                            functionality.
                        </p>
                        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div>✓ Password visibility toggle</div>
                            <div>✓ Remember me checkbox</div>
                            <div>✓ Form validation</div>
                            <div>✓ Responsive design</div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center px-3 py-2 border border-indigo-600 text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Login Page
                            </a>
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Register Page
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Developer Information -->
                <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        🛠️ Developer Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700 dark:text-gray-300">
                        <div>
                            <h4 class="font-semibold mb-2">Available Artisan Commands:</h4>
                            <div class="space-y-1 font-mono text-xs bg-gray-100 dark:bg-gray-700 p-3 rounded">
                                <div>php artisan external:cache</div>
                                <div>php artisan external:demo-offline</div>
                                <div>php artisan external:test-fallback</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Demo Routes:</h4>
                            <div class="space-y-1 font-mono text-xs bg-gray-100 dark:bg-gray-700 p-3 rounded">
                                <div>/demo/password-toggle</div>
                                <div>/demo/remember-me</div>
                                <div>/external-data/browse</div>
                                <div>/dev/demos (this page)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="text-center mt-8">
                    <a href="{{ route('welcome') }}"
                        class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
