@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $topStatus = [
            'currentTurn' => $metrics['currentTurn'] ?? null,
            'maxTurns' => $metrics['maxTurns'] ?? null,
            'spAvailable' => $selectedCharacter?->available_sp,
            'storageMode' => null,
        ];
    @endphp

    <div class="space-y-6 animate-fade-in">
        <!-- Page Header with Welcome Message -->
        <div class="glass-card rounded-xl p-6 md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 sm:truncate sm:text-3xl sm:tracking-tight">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm">
                    @if ($hasCharacters && $selectedCharacter)
                        Managing: <span
                            class="font-medium text-primary-600 dark:text-primary-400">{{ $selectedCharacter->name }}</span>
                    @else
                        Welcome! Create your first character to get started.
                    @endif
                </p>
            </div>
            <div class="mt-4 flex items-center gap-4 md:ml-4 md:mt-0">
                @if ($hasCharacters)
                    <!-- Character Selector -->
                    <div class="relative">
                        <label for="character-selector" class="sr-only">Select character</label>
                        <select id="character-selector"
                            class="form-select rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm pr-10 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            aria-label="Select character" data-url="{{ route('dashboard') }}"
                            onchange="window.location.href = this.dataset.url + '?character=' + this.value;">
                            @foreach ($characters as $character)
                                <option value="{{ $character->id }}"
                                    {{ $selectedCharacter && $selectedCharacter->id === $character->id ? 'selected' : '' }}>
                                    {{ $character->name }} - {{ ucfirst($character->scenario_type ?? 'Unknown') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <a href="{{ route('characters.create') }}"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Character
                </a>
            </div>
        </div>

        @if (!$hasCharacters)
            <!-- Empty State -->
            <x-dashboard.empty-state />
        @else
            <!-- Key Metrics Overview -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 animate-fade-in-delay-1">
                <!-- Current Turn -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Current Turn
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            {{ $metrics['currentTurn'] }} / {{ $metrics['maxTurns'] }}
                                        </div>
                                        <div
                                            class="ml-2 flex items-baseline text-sm font-semibold {{ $metrics['trackStatus'] === 'Ahead' ? 'text-success-600 dark:text-success-400' : ($metrics['trackStatus'] === 'On Track' ? 'text-primary-600 dark:text-primary-400' : 'text-warning-600 dark:text-warning-400') }}">
                                            {{ $metrics['trackStatus'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Grade -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-secondary-600 dark:text-secondary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Overall Grade
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            {{ $metrics['overallGrade'] }}</div>
                                        <div
                                            class="ml-2 flex items-baseline text-sm font-semibold text-primary-600 dark:text-primary-400">
                                            Target: {{ $metrics['targetGrade'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skills Acquired -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Skills
                                        Acquired</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold">
                                            {{ $metrics['skillsAcquired'] }} / {{ $metrics['targetSkills'] }}
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold">
                                            SP: {{ $metrics['skillPoints'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Race -->
                <div class="glass-card-inner overflow-hidden rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium truncate">Next Race</dt>
                                    <dd class="flex items-baseline">
                                        @if ($metrics['nextRace'])
                                            <div class="text-sm font-semibold">
                                                {{ $metrics['nextRace'] }}</div>
                                            <div
                                                class="ml-2 flex items-baseline text-xs font-semibold text-warning-600 dark:text-warning-400">
                                                {{ $metrics['turnsUntilRace'] }} turns
                                            </div>
                                        @else
                                            <div class="text-sm font-semibold">No race
                                                scheduled</div>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main 2-Column Grid (per WF-001 wireframe) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-delay-2">

                <!-- Left Column: Progress & Schedule (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Turn Counter Widget -->
                    <div class="glass-card rounded-xl p-6">
                        <x-turn-counter :current="$metrics['currentTurn']" :total="$metrics['maxTurns']" />
                    </div>

                    <!-- Goals Widget -->
                    <x-dashboard.goals-widget :shortTermGoal="$goals['shortTerm']['goal']" :shortTermProgress="$goals['shortTerm']['progress']" :longTermGoal="$goals['longTerm']['goal']" :longTermProgress="$goals['longTerm']['progress']"
                        :characterId="$selectedCharacter?->id" />

                    <!-- Training Suggestions -->
                    <x-dashboard.training-suggestions :suggestions="$trainingSuggestions" />

                    <!-- Upcoming Races -->
                    <x-dashboard.upcoming-races :races="$races" />

                    <!-- Recent Results Timeline -->
                    <x-dashboard.recent-results :results="$recentResults" />
                </div>

                <!-- Right Column: Stats & Advisories (1/3 width) -->
                <div class="flex flex-col gap-6 h-full">
                    <!-- Character Stats Card -->
                    <x-dashboard.stats-snapshot :stats="$stats" :character="$selectedCharacter" />

                    <!-- Mood/Energy Widget -->
                    <x-dashboard.mood-energy-widget :mood="$moodEnergy['mood']" :energy="$moodEnergy['energy']" :maxEnergy="$moodEnergy['maxEnergy']" />

                    <!-- AI Advisor Card -->
                    <x-dashboard.ai-advisor-card class="flex-1" />
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="animate-fade-in-delay-3">
                <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="{{ $selectedCharacter ? route('training.predictions.show', $selectedCharacter) : route('training.predictions') }}"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17 9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Start Training</span>
                        <span class="mt-1 block text-xs">Begin your next training
                            session</span>
                    </a>
                    <a href="{{ route('races.index') }}"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">View Races</span>
                        <span class="mt-1 block text-xs">Check race schedule &
                            results</span>
                    </a>
                    <a href="{{ route('skills.index') }}"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Manage Skills</span>
                        <span class="mt-1 block text-xs">View & optimize skill
                            loadout</span>
                    </a>
                    <a href="{{ route('support-cards.index') }}"
                        class="glass-card-inner relative group block w-full rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 hover:shadow-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25" />
                        </svg>
                        <span class="mt-2 block text-sm font-semibold">Support Deck</span>
                        <span class="mt-1 block text-xs">Build & optimize your deck</span>
                    </a>
                </div>
            </div>

            <!-- Additional Resources -->
            <div class="glass-card-alt rounded-lg p-6 animate-fade-in-delay-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Need Help Getting Started?
                        </h3>
                        <p class="text-sm">Check out our guides and tutorials to optimize
                            your training strategy.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('about') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/50 dark:text-primary-300 dark:hover:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
