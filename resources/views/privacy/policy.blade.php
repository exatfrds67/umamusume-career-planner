@extends('layouts.guest')

@section('title', 'Privacy Policy')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-neutral-900 dark:text-white">Privacy Policy</h1>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    Your data remains local-first by default. We prioritize privacy and transparency.
                </p>

                <div class="mt-10 space-y-8">
                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Local Data Storage</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Core career data is stored locally within your environment. Cloud services are opt-in and can be disabled.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Analytics</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Anonymous usage metrics help improve performance and stability. You can disable analytics at any time.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">AI Providers</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Local AI is the default. Optional cloud providers require explicit consent and show cost estimates.
                        </p>
                    </x-card>

                    <div class="flex flex-wrap gap-3">
                        <x-button href="{{ route('settings.index') }}" variant="primary">Manage Privacy Settings</x-button>
                        <x-button href="{{ route('help.index') }}" variant="outline">Back to Help</x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
