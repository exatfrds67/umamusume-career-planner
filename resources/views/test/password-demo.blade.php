@extends('layouts.guest')

@section('title', 'Password Toggle Demo - Umamusume Career Planner')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="glass-card rounded-2xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Password Toggle Demo
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Test the password visibility toggle functionality
                    </p>
                </div>

                <div class="space-y-6">
                    <x-password-input id="demo-password-1" name="demo-password-1" label="Password Field 1"
                        placeholder="Enter your password" value="TestPassword123" />

                    <x-password-input id="demo-password-2" name="demo-password-2" label="Password Field 2"
                        placeholder="Confirm your password" value="AnotherPassword456" />

                    <x-password-input id="demo-password-3" name="demo-password-3" label="Password Without Toggle"
                        placeholder="No toggle button" :showToggle="false" value="NoTogglePassword" />

                    <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                            How to use:
                        </h3>
                        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li>• Click the eye icon to toggle password visibility</li>
                            <li>• Use Tab + Enter or Space to toggle with keyboard</li>
                            <li>• Each field toggles independently</li>
                            <li>• Works in both light and dark modes</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}"
                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                            ← Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
