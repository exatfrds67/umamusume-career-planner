@extends('layouts.app')

@section('title', $character->name . ' — Synergy Analysis')

@section('content')
    @php
        /** @var \App\Models\Character $character */
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb Navigation -->
        <x-breadcrumb :items="[
            ['label' => 'Characters', 'url' => route('characters.index')],
            ['label' => $character->name, 'url' => route('characters.show', $character)],
            ['label' => 'Synergy Analysis'],
        ]" />

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Synergy Build Analysis</h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Multi-layer synergy evaluation for <span class="font-medium">{{ $character->name }}</span>
                </p>
            </div>
            <a href="{{ route('characters.show', $character) }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Character
            </a>
        </div>

        <!-- Synergy Build Planner (Full Mode) -->
        <livewire:synergy-build-planner :characterId="$character->id" />
    </div>
@endsection
