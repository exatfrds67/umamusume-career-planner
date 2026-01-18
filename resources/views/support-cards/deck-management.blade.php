@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="deckManagement()" x-init="init()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Support Card Deck Management - {{ $character->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Build and optimize your 6-card deck (5 owned + 1 friend card)
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <a href="{{ route('characters.show', $character) }}" class="btn btn-outline">
                    <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Character
                </a>
            </div>
        </div>

        <!-- Deck Analysis Dashboard -->
        @if ($deckAnalysis)
            <x-deck-analysis-dashboard :analysis="$deckAnalysis" :character="$character" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Deck Slots (Left/Top) -->
            <div class="lg:col-span-2 space-y-4">
                <x-deck-slots :character="$character" :currentDeck="$currentDeck" />

                <!-- Friendship Overview -->
                @if ($friendshipOverview)
                    <x-friendship-overview :overview="$friendshipOverview" />
                @endif
            </div>

            <!-- Card Library & Filters (Right/Bottom) -->
            <div class="space-y-4">
                <x-card-library :availableCards="$availableCards" :cardsByTier="$cardsByTier" />

                <!-- Deck Statistics -->
                @if ($deckStatistics)
                    <x-deck-statistics :statistics="$deckStatistics" />
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/deck-management.js') }}"></script>
    @endpush
@endsection
