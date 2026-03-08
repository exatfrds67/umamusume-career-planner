@extends('layouts.guest')

@section('title', 'Keyboard Shortcuts')

@section('content')
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-neutral-900 dark:text-white">Keyboard Shortcuts</h1>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    Use these shortcuts to navigate quickly throughout the application.
                </p>

                <div class="mt-10">
                    <x-keyboard-shortcuts-help />
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button href="{{ route('accessibility.statement') }}" variant="secondary">Accessibility Statement</x-button>
                    <x-button href="{{ route('help.index') }}" variant="outline">Back to Help</x-button>
                </div>
            </div>
        </div>
    </section>
@endsection
