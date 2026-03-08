@extends('layouts.guest')

@section('title', 'Terms of Service')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-neutral-900 dark:text-white">Terms of Service</h1>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    By using Umamusume Career Planner, you agree to the terms outlined below.
                </p>

                <div class="mt-10 space-y-8">
                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Acceptable Use</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Use the app responsibly and respect applicable game policies and local regulations.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Data Ownership</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            You retain ownership of your local data and can export it at any time.
                        </p>
                    </x-card>

                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Service Availability</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            We aim for high availability, but cannot guarantee uninterrupted access during maintenance or outages.
                        </p>
                    </x-card>

                    <div class="flex flex-wrap gap-3">
                        <x-button href="{{ route('privacy.policy') }}" variant="secondary">Privacy Policy</x-button>
                        <x-button href="{{ route('help.index') }}" variant="outline">Back to Help</x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
