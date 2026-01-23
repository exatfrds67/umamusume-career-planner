@extends('layouts.guest')

@section('title', 'Help & Support')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">Help & Support</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                    Find guidance on getting started, common workflows, and accessibility resources.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Getting Started</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Learn the core workflow: create a character, plan training, and review race readiness.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-button href="{{ route('about') }}" variant="primary" size="sm">Read Overview</x-button>
                        <x-button href="{{ route('demo') }}" variant="outline" size="sm">View Demo</x-button>
                    </div>
                </x-card>

                <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Accessibility</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Review our WCAG 2.2 AA statement and available keyboard shortcuts.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-button href="{{ route('accessibility.statement') }}" variant="secondary" size="sm">Accessibility Statement</x-button>
                        <x-button href="{{ route('keyboard.shortcuts') }}" variant="outline" size="sm">Keyboard Shortcuts</x-button>
                    </div>
                </x-card>

                <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Privacy & Data</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Understand how your data is stored locally and how export options work.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-button href="{{ route('privacy.policy') }}" variant="secondary" size="sm">Privacy Policy</x-button>
                        <x-button href="{{ route('terms.service') }}" variant="outline" size="sm">Terms of Service</x-button>
                    </div>
                </x-card>

                <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Feedback</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Share ideas or report issues to help us improve the planner.
                    </p>
                    <div class="mt-4">
                        <x-button href="{{ route('feedback.create') }}" variant="primary" size="sm">Send Feedback</x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </section>
@endsection
