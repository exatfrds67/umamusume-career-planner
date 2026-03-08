@extends('layouts.app')

@section('title', 'Design System Demo')

@section('content')
    <div class="space-y-8">
        <div class="glass-card rounded-xl p-6">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Design System Demo</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                Core UI components and styling references for rapid layout validation.
            </p>
        </div>

        <x-card class="bg-white dark:bg-neutral-800 p-6 border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Buttons & Links</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-button variant="primary">Primary Action</x-button>
                <x-button variant="secondary">Secondary Action</x-button>
                <x-button variant="outline">Outline Action</x-button>
                <x-button href="{{ route('demo') }}" variant="outline">Open Demo</x-button>
            </div>
        </x-card>

        <x-card class="bg-white dark:bg-neutral-800 p-6 border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Form Elements</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="demo-text">Text Input</label>
                    <input id="demo-text" type="text" class="form-input" placeholder="Sample input">
                </div>
                <div>
                    <label class="form-label" for="demo-select">Select</label>
                    <select id="demo-select" class="form-select">
                        <option>Option A</option>
                        <option>Option B</option>
                    </select>
                </div>
            </div>
        </x-card>

        <x-card class="bg-white dark:bg-neutral-800 p-6 border border-neutral-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Badges</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-badge>Default</x-badge>
                <x-badge variant="success">Success</x-badge>
                <x-badge variant="warning">Warning</x-badge>
                <x-badge variant="error">Error</x-badge>
            </div>
        </x-card>
    </div>
@endsection
