@extends('layouts.guest')

@section('title', 'Accessibility Statement')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">Accessibility Statement</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                    Umamusume Career Planner targets WCAG 2.2 AA compliance and prioritizes inclusive design.
                </p>

                <div class="mt-10 space-y-8">
                    <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Accessibility Commitment</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            We design for keyboard-first navigation, clear focus states, and readable contrast across light and dark modes.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Assistive Technology</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Screen reader labels and semantic headings are applied throughout critical workflows.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Feedback</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            If you experience accessibility issues, please report them so we can improve.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <x-button href="{{ route('feedback.create') }}" variant="primary" size="sm">Report an Issue</x-button>
                            <x-button href="{{ route('keyboard.shortcuts') }}" variant="outline" size="sm">Keyboard Shortcuts</x-button>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </section>
@endsection
