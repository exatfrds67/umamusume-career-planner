@extends('layouts.app')

@section('title', 'Career Reports')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Analytics & Reports', 'url' => route('reports.index')], ['label' => 'Reports']]" />

    <div class="container mx-auto px-4 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Career Reports</h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                View comprehensive reports with insights and recommendations for your careers.
            </p>
        </div>

        {{-- Quick Access: Recent Careers --}}
        @if ($recentCareers->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Recent Careers</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    @foreach ($recentCareers as $career)
                        <a href="{{ route('reports.career', $career) }}"
                            class="block p-4 bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 hover:shadow-md hover:border-primary-500 transition-all duration-200">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                        {{ $career->character?->name ?? 'Unknown' }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ ucfirst(str_replace('_', ' ', $career->scenario_type ?? 'Unknown')) }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Characters with Careers --}}
        <div class="space-y-6">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200">All Characters</h2>

            @forelse($characterGroups as $characterGroup)
                @php
                    /** @var \App\Models\Character $character */
                    $character = $characterGroup['default_character'];
                    /** @var \Illuminate\Support\Collection<int, \App\Models\Career> $careers */
                    $careers = $characterGroup['careers'];
                    $careerCount = $characterGroup['career_count'];
                    $variantCount = $characterGroup['variant_count'];
                @endphp
                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    {{-- Character Header --}}
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 border-b border-neutral-200 dark:border-neutral-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    @if ($character->avatar_url)
                                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" loading="lazy"
                                            decoding="async" class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <span class="text-xl font-bold text-primary-600 dark:text-primary-400">
                                            {{ substr($character->name, 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $character->name }}
                                    </h3>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ $careerCount }} career(s)
                                    </p>
                                    @if ($variantCount > 1)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Includes {{ $variantCount }} character variants
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('reports.character', $character) }}"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Character Report
                            </a>
                        </div>
                    </div>

                    {{-- Career List --}}
                    @if ($careers->isNotEmpty())
                        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($careers as $career)
                                <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/30 transition-colors duration-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="shrink-0">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $career->completed_at ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                    {{ $career->completed_at ? 'Completed' : 'In Progress' }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                                    {{ ucfirst(str_replace('_', ' ', $career->scenario_type ?? 'Unknown')) }}
                                                </p>
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                    {{ $career->character?->name ?? 'Unknown Character' }} •
                                                    Turn {{ $career->current_turn ?? 0 }} • Started
                                                    {{ $career->created_at?->diffForHumans() ?? 'Unknown' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('reports.career', $career) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View Report
                                            </a>
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open"
                                                    :aria-expanded="open"
                                                    aria-haspopup="true"
                                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Export
                                                </button>
                                                <div x-show="open" @click.away="open = false" @keydown.escape.window="open = false" x-transition
                                                    role="menu" aria-orientation="vertical"
                                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-neutral-800 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-700 z-10">
                                                    <a href="{{ route('reports.export.json', $career) }}" role="menuitem"
                                                        class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                                        Export as JSON
                                                    </a>
                                                    <a href="{{ route('reports.export.csv', $career) }}" role="menuitem"
                                                        class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                                        Export as CSV
                                                    </a>
                                                    <a href="{{ route('reports.export.pdf', $career) }}" role="menuitem"
                                                        class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                                        Export as PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <p class="text-neutral-500 dark:text-neutral-400">No careers found for this character.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No characters found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Create a character to start generating career reports.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('characters.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Create Character
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
