@extends('layouts.guest')

@section('title', 'Feedback')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-neutral-900 dark:text-white">Send Feedback</h1>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    We welcome ideas, bug reports, and feature requests to improve the planner.
                </p>

                <div class="mt-10 space-y-6">
                    <x-card class="bg-white dark:bg-neutral-900 p-6 border border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">Preferred Channels</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Use GitHub Issues for detailed reports, or contact the team directly.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <x-button href="https://github.com/exatfrds67/umamusume-career-planner/issues" variant="primary" size="sm">Open GitHub Issue</x-button>
                            <x-button href="{{ route('help.index') }}" variant="outline" size="sm">Back to Help</x-button>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </section>
@endsection
