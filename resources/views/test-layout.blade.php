@extends('layouts.app')

@section('title', 'Layout Test')

@section('content')
    <div class="space-y-8">
        <div class="glass-card rounded-xl p-6">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Layout Test</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                Quick sanity check for layout structure, spacing, and component styling.
            </p>
        </div>

        <x-card class="bg-white dark:bg-neutral-800 p-6 border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Buttons</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-button variant="primary">Primary</x-button>
                <x-button variant="secondary">Secondary</x-button>
                <x-button variant="outline">Outline</x-button>
                <x-button disabled>Disabled</x-button>
            </div>
        </x-card>

        <x-card class="bg-white dark:bg-neutral-800 p-6 border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Badges</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-badge>Primary</x-badge>
                <x-badge variant="success">Success</x-badge>
                <x-badge variant="warning">Warning</x-badge>
                <x-badge variant="error">Error</x-badge>
            </div>
        </x-card>
    </div>
@endsection
