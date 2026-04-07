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

        $topSuggestion = $trainingSuggestions[0] ?? null;
        $riskLabels = [
            'none' => 'No Risk',
            'low' => 'Low Risk',
            'medium' => 'Medium Risk',
            'high' => 'High Risk',
        ];

        $nextRaceReadiness = null;
        if (!empty($nextRaceRequirements ?? [])) {
            $readinessScores = [];
            foreach ($nextRaceRequirements as $statKey => $requiredValue) {
                if (!is_numeric($requiredValue) || (int) $requiredValue <= 0) {
                    continue;
                }

                $normalizedStat = $statKey === 'wisdom' ? 'wit' : $statKey;
                $currentValue = (int) ($stats[$normalizedStat] ?? 0);
                $requiredInt = (int) $requiredValue;
                $readinessScores[] = (int) min(100, round(($currentValue / $requiredInt) * 100));
            }

            if (!empty($readinessScores)) {
                $nextRaceReadiness = (int) round(array_sum($readinessScores) / count($readinessScores));
            }
        }
    @endphp

    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Dashboard']]" />

    <div class="page-stack animate-fade-in">
        <!-- Page Header with Welcome Message -->
        <div class="page-hero">
            <div class="page-hero__content">
                <div class="min-w-0 flex-1">
                <div class="page-hero__eyebrow">
                    <span>Career Overview</span>
                </div>
                <h2 class="page-hero__title sm:truncate">
                    Dashboard
                </h2>
                <p class="page-hero__body text-sm sm:text-base">
                    @if ($hasCharacters && $selectedCharacter)
                        Managing: <span
                            class="font-medium text-primary-600 dark:text-primary-400">{{ $selectedCharacter->name }}</span>
                    @else
                        Welcome! Create your first character to get started.
                    @endif
                </p>
                <div class="page-hero__meta">
                    <span class="hero-chip">
                        <span class="font-semibold">Turns</span>
                        <span>{{ $metrics['currentTurn'] ?? '0' }}/{{ $metrics['maxTurns'] ?? '72' }}</span>
                    </span>
                    @if ($hasCharacters && $selectedCharacter)
                        <span class="hero-chip">
                            <span class="font-semibold">SP</span>
                            <span>{{ number_format($selectedCharacter->available_sp ?? 0) }}</span>
                        </span>
                    @endif
                </div>
            </div>
            <div class="page-hero__actions">
                @if ($hasCharacters)
                    <!-- Character Selector -->
                    <div class="relative">
                        <span id="char-select-nav-desc" class="sr-only">Changing this selection immediately navigates to that character's dashboard.</span>
                        <label for="character-selector" class="sr-only">Select character</label>
                        <select id="character-selector"
                            class="form-select min-w-64 rounded-xl border-neutral-300/80 bg-white/90 pr-10 text-sm shadow-xs focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:border-neutral-600 dark:bg-neutral-800/90"
                            aria-label="Select character" aria-describedby="char-select-nav-desc" data-url="{{ route('dashboard') }}"
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
                    class="btn btn-primary btn-md rounded-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Character
                </a>
            </div>
            </div>
        </div>

        @if (!$hasCharacters)
            <!-- Empty State -->
            <x-dashboard.empty-state />
        @else
            <section class="space-y-4 animate-fade-in-delay-1" aria-labelledby="primary-decision-heading">
                <div class="section-kicker">
                    <span>Primary Decision</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <h3 id="primary-decision-heading" class="text-lg font-semibold text-neutral-900 dark:text-white">Primary Decision Zone</h3>
                    <a href="{{ route('training.predictions') }}" class="btn btn-secondary btn-sm">
                        Review Full Predictions
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <article class="panel-surface rounded-xl p-5" aria-labelledby="next-best-action-title">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary-700 dark:text-primary-300">What To Do Now</p>
                        <h4 id="next-best-action-title" class="mt-2 text-base font-semibold text-neutral-900 dark:text-white">Next Best Action</h4>
                        @if ($topSuggestion)
                            <p class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $topSuggestion['action'] }}</p>
                            <p class="mt-1 text-sm text-neutral-700 dark:text-neutral-300">{{ $topSuggestion['gains'] ?? 'Focus on the highest projected gain this turn.' }}</p>
                            <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">
                                Risk: {{ $riskLabels[$topSuggestion['risk'] ?? 'none'] ?? 'No Risk' }}. This recommendation is based on current stats, mood, and energy.
                            </p>
                        @else
                            <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">No recommendation available yet. Generate a prediction to get your next best action.</p>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('training.predictions') }}" class="btn btn-primary btn-sm">Start Recommended Training</a>
                        </div>
                    </article>

                    <article class="status-strip p-5" aria-labelledby="turn-status-title">
                        <p class="status-strip__item-label">Why It Matters</p>
                        <h4 id="turn-status-title" class="mt-2 text-base font-semibold text-neutral-900 dark:text-white">Turn Progress</h4>
                        <p class="mt-2 text-3xl font-extrabold leading-none text-neutral-950 dark:text-white">{{ $metrics['currentTurn'] }} / {{ $metrics['maxTurns'] }}</p>
                        <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">Status: <span class="font-semibold">{{ $metrics['trackStatus'] }}</span></p>
                        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">Staying on track protects your race readiness and skill pacing for upcoming turns.</p>
                    </article>

                    <article class="status-strip p-5" aria-labelledby="race-readiness-title">
                        <p class="status-strip__item-label">Upcoming Constraint</p>
                        <h4 id="race-readiness-title" class="mt-2 text-base font-semibold text-neutral-900 dark:text-white">Next Race Readiness</h4>
                        @if ($nextRaceReadiness !== null)
                            <p class="mt-2 text-3xl font-extrabold leading-none text-neutral-950 dark:text-white">{{ $nextRaceReadiness }}%</p>
                            <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">{{ $nextRaceName ?? 'Next race' }}</p>
                        @elseif($metrics['nextRace'])
                            <p class="mt-2 text-base font-semibold text-neutral-900 dark:text-white">{{ $metrics['nextRace'] }}</p>
                            <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">{{ $metrics['turnsUntilRace'] }} turns remaining</p>
                        @else
                            <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">No race scheduled yet.</p>
                        @endif
                        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">Use stat and mood context below to increase confidence before race day.</p>
                    </article>
                </div>
            </section>

            <!-- Key Metrics Overview -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 animate-fade-in-delay-1" aria-label="Top dashboard metrics">
                <!-- Current Turn -->
                <div class="metric-card metric-card--primary">
                    <div class="metric-card__body">
                        <div class="metric-card__row">
                            <div class="metric-card__icon shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div class="w-0 flex-1">
                                <dl>
                                    <dt class="metric-card__eyebrow truncate">Current Turn</dt>
                                    <dd class="flex items-baseline flex-wrap gap-x-2">
                                        <div class="metric-card__value whitespace-nowrap">
                                            {{ $metrics['currentTurn'] }} / {{ $metrics['maxTurns'] }}
                                        </div>
                                        <div class="metric-card__meta">
                                        <div class="metric-badge {{ $metrics['trackStatus'] === 'Ahead' ? 'metric-badge--success' : ($metrics['trackStatus'] === 'On Track' ? 'metric-badge--primary' : 'metric-badge--warning') }}">
                                            {{ $metrics['trackStatus'] }}
                                        </div>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Grade -->
                <div class="metric-card metric-card--secondary">
                    <div class="metric-card__body">
                        <div class="metric-card__row">
                            <div class="metric-card__icon shrink-0">
                                <svg class="h-6 w-6 text-secondary-600 dark:text-secondary-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            </div>
                            <div class="w-0 flex-1">
                                <dl>
                                    <dt class="metric-card__eyebrow truncate">Overall Grade</dt>
                                    <dd class="flex items-baseline">
                                        <div class="metric-card__value">
                                            {{ $metrics['overallGrade'] }}</div>
                                        <div class="ml-2 metric-badge metric-badge--primary">
                                            Target: {{ $metrics['targetGrade'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Race -->
                <div class="metric-card metric-card--warning">
                    <div class="metric-card__body">
                        <div class="metric-card__row">
                            <div class="metric-card__icon shrink-0">
                                <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                </svg>
                            </div>
                            <div class="w-0 flex-1">
                                <dl>
                                    <dt class="metric-card__eyebrow truncate">Next Race</dt>
                                    <dd class="flex items-baseline">
                                        @if ($metrics['nextRace'])
                                            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                                                {{ $metrics['nextRace'] }}</div>
                                            <div class="ml-2 metric-badge metric-badge--warning">
                                                {{ $metrics['turnsUntilRace'] }} turns
                                            </div>
                                        @else
                                            <div class="text-sm font-semibold text-neutral-900 dark:text-white">No race
                                                scheduled</div>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="space-y-4 animate-fade-in-delay-2" aria-labelledby="secondary-context-heading">
                <div class="section-kicker">
                    <span>Context</span>
                </div>
                <h3 id="secondary-context-heading" class="text-lg font-semibold text-neutral-900 dark:text-white">Secondary Context Zone</h3>

                <!-- Main 2-Column Grid (per WF-001 wireframe) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left Column: Progress & Schedule (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Goals Widget -->
                        <x-dashboard.goals-widget :shortTermGoal="$goals['shortTerm']['goal']" :shortTermProgress="$goals['shortTerm']['progress']" :longTermGoal="$goals['longTerm']['goal']" :longTermProgress="$goals['longTerm']['progress']"
                            :characterId="$selectedCharacter?->id" />

                        <!-- Training Suggestions -->
                        <livewire:dashboard.training-suggestion-panel :suggestions="$trainingSuggestions" />

                        <!-- Upcoming Races -->
                        <x-dashboard.upcoming-races :races="$races" />

                        <!-- Recent Results Timeline -->
                        <x-dashboard.recent-results :results="$recentResults" />
                    </div>

                    <!-- Right Column: Stats & Advisories (1/3 width) -->
                    <div class="flex flex-col gap-6 h-full">
                        <!-- Character Stats Card -->
                        <x-dashboard.stats-snapshot :stats="$stats" :character="$selectedCharacter" :raceRequirements="$nextRaceRequirements ?? []" :nextRaceName="$nextRaceName ?? null" />

                        <!-- Mood/Energy Widget -->
                        <x-dashboard.mood-energy-widget :mood="$moodEnergy['mood']" :energy="$moodEnergy['energy']" :maxEnergy="$moodEnergy['maxEnergy']" />

                        <!-- AI Advisor Card -->
                        <x-dashboard.ai-advisor-card class="flex-1" />
                    </div>
                </div>
            </section>

            <!-- Analytics & Quick Actions -->
            <div class="space-y-8">
            <!-- Insights -->
            <section class="animate-fade-in-delay-3" x-data="{ insightsOpen: (typeof window !== 'undefined' ? window.innerWidth >= 1024 : false) }" aria-labelledby="tertiary-insights-heading">
                <div class="section-kicker">
                    <span>Insights</span>
                </div>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 id="tertiary-insights-heading" class="text-lg font-semibold text-neutral-900 dark:text-white">Tertiary Insights Zone</h3>
                    <button type="button" class="btn btn-secondary btn-sm lg:hidden" @click="insightsOpen = !insightsOpen"
                        :aria-expanded="insightsOpen.toString()" aria-controls="dashboard-insights-content">
                        <span x-text="insightsOpen ? 'Hide Insights' : 'Show Insights'"></span>
                    </button>
                </div>

                <div id="dashboard-insights-content" x-show="insightsOpen" x-collapse>
                    {{-- Phase 5: Analytics Section --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-start">
                        {{-- Stat Progression Chart --}}
                            <x-line-chart title="Stat Progression" :data="$statProgression" :labels="$progressionLabels" :colors="['#3B82F6', '#10B981', '#F59E0B']"
                            :dataset-labels="['Total Stats']"
                            height="h-72" />

                        {{-- Fan Count Hierarchy --}}
                            <x-class-pyramid title="Race Grade Distribution" :grades="$raceGrades" variant="pyramid" />
                    </div>

                    {{-- Activity Timeline --}}
                    <x-activity-timeline title="Recent Activity" :events="$recentActivity ?? []" variant="timeline" class="mb-6" />
                </div>
            </section>

            <div class="animate-fade-in-delay-3">
                <div class="section-kicker">
                    <span>Actions</span>
                </div>
                <h3 class="text-lg font-semibold mb-2 text-neutral-900 dark:text-white">Quick Actions</h3>
                <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-300">Secondary shortcuts. Use the primary decision zone above for your recommended next move.</p>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="{{ $selectedCharacter ? route('training.predictions.show', $selectedCharacter) : route('training.predictions') }}"
                        class="action-card action-card--training relative group block w-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <span class="action-card__icon mx-auto">
                        <svg class="h-8 w-8"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17 9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                        </span>
                        <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Start Training</span>
                        <span class="mt-1 block text-xs text-neutral-600 dark:text-neutral-300">Begin your next training
                            session</span>
                    </a>
                    <a href="{{ route('races.index') }}"
                        class="action-card action-card--races relative group block w-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <span class="action-card__icon mx-auto">
                        <svg class="h-8 w-8"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                        </span>
                        <span class="block text-sm font-semibold text-neutral-900 dark:text-white">View Races</span>
                        <span class="mt-1 block text-xs text-neutral-600 dark:text-neutral-300">Check race schedule &
                            results</span>
                    </a>
                    <a href="{{ route('skills.index') }}"
                        class="action-card action-card--skills relative group block w-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <span class="action-card__icon mx-auto">
                        <svg class="h-8 w-8"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        </span>
                        <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Manage Skills</span>
                        <span class="mt-1 block text-xs text-neutral-600 dark:text-neutral-300">View & optimize skill
                            loadout</span>
                    </a>
                    <a href="{{ route('support-cards.index') }}"
                        class="action-card action-card--support relative group block w-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <span class="action-card__icon mx-auto">
                        <svg class="h-8 w-8"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25" />
                        </svg>
                        </span>
                        <span class="block text-sm font-semibold text-neutral-900 dark:text-white">Support Deck</span>
                        <span class="mt-1 block text-xs text-neutral-600 dark:text-neutral-300">Build &amp; optimize your deck</span>
                    </a>
                </div>
            </div>
            </div>{{-- /space-y-8 --}}

            <!-- Additional Resources -->
            <div class="glass-card-alt rounded-xl p-6 animate-fade-in-delay-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Need Help Getting Started?
                        </h3>
                        <p class="text-sm">Check out our guides and tutorials to optimize
                            your training strategy.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('about') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/50 dark:text-primary-300 dark:hover:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                            aria-label="Learn more about training strategy guides and tutorials">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
