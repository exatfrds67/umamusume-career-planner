@extends('layouts.app')

@section('title', $character->name)

@section('content')
    @php
        /** @var \App\Models\Character $character */
    @endphp
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb Navigation -->
        <x-breadcrumb :items="[['label' => 'Characters', 'url' => route('characters.index')], ['label' => $character->name]]" />

        <!-- Header / Action Buttons -->
        <div class="flex items-center justify-end">
            <div class="flex space-x-3">
                <form action="{{ route('characters.toggle-pin', $character) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="btn {{ $character->isPinnedBy(Auth::id()) ? 'btn-primary' : 'btn-secondary' }}"
                        aria-label="{{ $character->isPinnedBy(Auth::id()) ? 'Unpin ' . $character->name : 'Pin ' . $character->name . ' for quick access' }}">
                        @if ($character->isPinnedBy(Auth::id()))
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                            </svg>
                            Pinned
                        @else
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            Pin
                        @endif
                    </button>
                </form>

                <form action="{{ route('characters.rest', $character) }}" method="POST" class="inline"
                    onsubmit="return confirm('Rest this turn? This action will advance the game state.')">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-green-700 dark:text-green-400"
                        aria-label="Perform rest action for {{ $character->name }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        Rest
                    </button>
                </form>

                <form action="{{ route('characters.next-turn', $character) }}" method="POST" class="inline"
                    onsubmit="return confirm('Advance to the next turn? This action cannot be undone.')">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-blue-700 dark:text-blue-400"
                        aria-label="Advance to next training turn for {{ $character->name }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                        Next Turn
                    </button>
                </form>

                <a href="{{ route('characters.edit', $character) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Character
                </a>
            </div>
        </div>

        <!-- Character Overview Card -->
        <section class="card overflow-visible rounded-xl" role="region" aria-labelledby="character-overview-heading">
            <div class="p-6 md:p-8 relative overflow-hidden">
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50 pointer-events-none"
                    aria-hidden="true">
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-start relative">
                    <!-- Avatar -->
                    <div class="shrink-0 relative">
                        <x-character-portrait :image="$character->avatar_url" :alt="$character->name" size="xl" />
                    </div>

                    <!-- Details -->
                    <div class="flex-1 space-y-4 w-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h1 id="character-overview-heading"
                                        class="text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                                        {{ $character->name }}</h1>
                                    <x-ui.grade-badge :grade="$character->getStatGrade($character->current_stats['speed'] ?? 0)" size="sm" />
                                    @if ($character->isPinnedBy(Auth::id()))
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
                                            title="Pinned character">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z" />
                                            </svg>
                                            Pinned
                                        </span>
                                    @endif
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-neutral-600 dark:text-neutral-400 font-medium">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $character->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup' }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-secondary-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Turn {{ $character->current_turn }} ({{ ucfirst($character->career_stage) }})
                                    </span>
                                </div>
                            </div>

                            <!-- AI Advisor (Desktop Position) -->
                            <div class="hidden lg:block w-80">
                                <x-dashboard.ai-advisor-card class="shadow-xs border-0" :lastTip="$aiTip" />
                            </div>
                        </div>

                        <!-- Quick Progress Bars -->
                        <div
                            class="max-w-2xl bg-white/50 dark:bg-neutral-800/50 rounded-xl p-4 border border-neutral-100 dark:border-neutral-700/50 backdrop-blur-xs"
                            aria-label="Character status indicators">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-neutral-500 dark:text-neutral-400">Energy Level</span>
                                        <span
                                            class="{{ $character->energy_level < 30 ? 'text-red-500' : 'text-green-500' }}"
                                            aria-label="Energy level: {{ $character->energy_level }}%">{{ $character->energy_level }}%</span>
                                    </div>
                                    <x-ui.progress-bar :value="$character->energy_level" :max="100"
                                        color="{{ $character->energy_level < 30 ? 'bg-red-500' : 'bg-green-500' }}"
                                        size="sm" :show-text="false"
                                        aria-label="Energy level {{ $character->energy_level }} percent" />
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-neutral-500 dark:text-neutral-400">Goal Progress</span>
                                        <span class="text-primary-500"
                                            aria-label="Goal progress: {{ $character->getProgressPercentage() }}%">{{ $character->getProgressPercentage() }}%</span>
                                    </div>
                                    <x-ui.progress-bar :value="$character->getProgressPercentage()" :max="100" color="bg-primary-500"
                                        size="sm" :show-text="false"
                                        aria-label="Goal progress {{ $character->getProgressPercentage() }} percent" />
                                </div>
                            </div>
                        </div>

                        <!-- AI Advisor (Mobile Position) -->
                        <div class="lg:hidden mt-4">
                            <x-dashboard.ai-advisor-card :lastTip="$aiTip" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left Column: Stats (Takes 2 columns) -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Detailed Stats Card -->
                <section class="card rounded-lg" role="region" aria-labelledby="stats-heading">
                    <header
                        class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50 flex items-center justify-between">
                        <h2 id="stats-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Current Statistics
                        </h2>
                        <span
                            class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-700 px-2 py-1 rounded">Updated
                            {{ $character->updated_at->diffForHumans() }}</span>
                    </header>
                    <div class="card-body">
                        <div class="space-y-4">
                            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                @php
                                    $val = $character->getStat($stat);
                                    $target = $character->goals['target_stats'][$stat] ?? 0;
                                @endphp
                                <x-stat-bar :stat="$stat" :current="$val" :target="$target" />
                            @endforeach
                        </div>
                    </div>
                </section>
                <!-- Support Deck -->
                <section class="card rounded-lg" role="region" aria-labelledby="support-deck-heading">
                    <header
                        class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50 flex items-center justify-between">
                        <h2 id="support-deck-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Support Deck
                        </h2>
                        <a href="{{ route('characters.deck-builder', $character) }}" class="btn btn-sm btn-primary"
                            aria-label="Manage Support Deck">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Manage Deck
                        </a>
                    </header>
                    <div class="card-body">
                        @if ($character->supportCards->count() > 0)
                            <div class="space-y-2">
                                @foreach ($character->supportCards as $supportCard)
                                    @php
                                        $card = $supportCard->supportCard;
                                        $bondLevel = $supportCard->friendship_level ?? 0;
                                        $bondMax = 100;
                                        $bondPercentage = ($bondLevel / $bondMax) * 100;
                                    @endphp
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700">
                                        <div
                                            class="shrink-0 w-10 h-10 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($card->card_type ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                                {{ $card->name ?? 'Unknown Card' }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div
                                                    class="flex-1 h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden"
                                                    role="progressbar"
                                                    aria-valuenow="{{ $bondLevel }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="{{ $bondMax }}"
                                                    aria-label="Bond with {{ $card->name ?? 'support card' }}: {{ $bondLevel }} of {{ $bondMax }}">
                                                    <div class="h-full bg-primary-500 rounded-full transition-all"
                                                        @style(['width' => $bondPercentage . '%'])></div>
                                                </div>
                                                <span
                                                    class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums"
                                                    aria-hidden="true">{{ $bondLevel }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-sm text-neutral-500 py-6">
                                <svg class="mx-auto h-10 w-10 text-neutral-400 mb-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="mb-3">No support cards equipped.</p>
                                <a href="{{ route('characters.deck-builder', $character) }}"
                                    class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                                    aria-label="Add support cards for {{ $character->name }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Support Cards
                                </a>
                            </div>
                        @endif
                    </div>
                </section>

                @if ($character->gameCharacter && $character->gameCharacter->goalRaces->isNotEmpty())
                    <!-- Goal Races -->
                    <section class="card rounded-lg" role="region" aria-labelledby="goal-races-heading">
                        <div class="p-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
                            <h2 id="goal-races-heading" class="text-lg font-bold text-neutral-900 dark:text-white">
                                Goal Races
                            </h2>
                            <span
                                class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                {{ $character->gameCharacter->goalRaces->count() }}
                                {{ Str::plural('race', $character->gameCharacter->goalRaces->count()) }}
                            </span>
                        </div>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-700/50">
                            @foreach ($character->gameCharacter->goalRaces->sortBy('pivot.priority') as $race)
                                <div class="flex items-center gap-3 px-4 py-3">
                                    {{-- Grade Badge --}}
                                    <span @class([
                                        'inline-flex items-center justify-center rounded-md px-2 py-0.5 text-xs font-bold min-w-[32px]',
                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $race->grade === 'G1',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' => $race->grade === 'G2',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $race->grade === 'G3',
                                        'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => ! in_array($race->grade, ['G1', 'G2', 'G3']),
                                    ])>
                                        {{ $race->grade }}
                                    </span>

                                    {{-- Race Info --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-neutral-900 dark:text-white truncate">
                                            {{ $race->name_en }}
                                        </div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400 flex items-center gap-2 flex-wrap">
                                            <span>{{ $race->distance_meters }}m</span>
                                            <span class="text-neutral-300 dark:text-neutral-600">&middot;</span>
                                            <span>{{ ucfirst($race->distance_category ?? 'medium') }}</span>
                                            @if ($race->venue)
                                                <span class="text-neutral-300 dark:text-neutral-600">&middot;</span>
                                                <span>{{ $race->venue }}</span>
                                            @endif
                                            @if ($race->phase)
                                                <span class="text-neutral-300 dark:text-neutral-600">&middot;</span>
                                                <span>{{ ucfirst($race->phase) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Priority indicator --}}
                                    @if ($race->pivot->priority)
                                        <span
                                            class="shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-neutral-100 dark:bg-neutral-700 text-xs font-medium text-neutral-600 dark:text-neutral-300"
                                            title="Priority {{ $race->pivot->priority }}">
                                            {{ $race->pivot->priority }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if ($character->gameCharacter->goalRaces->whereNotNull('pivot.notes')->where('pivot.notes', '!=', '')->isNotEmpty())
                            <div class="px-4 py-2 bg-amber-50 dark:bg-amber-900/10 border-t border-neutral-200 dark:border-neutral-700">
                                @foreach ($character->gameCharacter->goalRaces->sortBy('pivot.priority') as $race)
                                    @if ($race->pivot->notes)
                                        <p class="text-xs text-amber-700 dark:text-amber-300">
                                            <span class="font-semibold">{{ $race->name_en }}:</span>
                                            {{ $race->pivot->notes }}
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                {{-- 
                    Recent Careers / History Section
                    
                    This section is intentionally commented out as the careers tracking system
                    is planned for a future release. The Career model and relationships exist,
                    but the full career history UI requires additional work:
                    - Career completion workflow
                    - Final grade calculation
                    - Historical statistics aggregation
                    
                    See: docs/future-implements/comprehensive_future_features.md
                --}}
                {{--
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Training Careers</h3>
                    </div>
                    @if ($character->careers->count() > 0)
                        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach ($character->careers as $career)
                                <div
                                    class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            {{ $career->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup' }}
                                        </div>
                                        <div class="text-xs text-neutral-500">{{ $career->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <x-ui.grade-badge :grade="$career->final_grade ?? 'E'" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-neutral-500 dark:text-neutral-400">
                            No careers completed yet. Start training to build history!
                        </div>
                    @endif
                </div>
                --}}
            </div>

            <!-- Right Column: Stats Overview & Aptitudes -->
            <div class="space-y-6">
                <!-- Stats Overview -->
                <section class="card rounded-lg" role="region" aria-labelledby="stats-visualization-heading">
                    <header class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50">
                        <h2 id="stats-visualization-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Stats
                            Overview</h2>
                    </header>
                    <div class="card-body flex justify-center items-center py-4">
                        <x-stat-radar-chart :stats="[
                            'speed' => $character->getStat('speed'),
                            'stamina' => $character->getStat('stamina'),
                            'power' => $character->getStat('power'),
                            'guts' => $character->getStat('guts'),
                            'wit' => $character->getStat('wit'),
                        ]" size="sm" />
                    </div>
                </section>

                <!-- Aptitudes -->
                <section class="card rounded-lg" role="region" aria-labelledby="aptitudes-heading">
                    <header class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50">
                        <h2 id="aptitudes-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Aptitudes</h2>
                    </header>
                    <div class="card-body space-y-6">
                        @php
                            $aptitudeGroups = [
                                'Distance' => $character->aptitudes->whereNotNull('distance_type'),
                                'Surface' => $character->aptitudes->whereNotNull('surface_type'),
                                'Running Style' => $character->aptitudes->whereNotNull('running_style'),
                            ];
                        @endphp

                        @foreach ($aptitudeGroups as $groupName => $aptitudes)
                            @if ($aptitudes->count() > 0)
                                <div>
                                    <h4 class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-3">
                                        {{ $groupName }}</h4>
                                    <div class="space-y-2">
                                        @foreach ($aptitudes as $aptitude)
                                            @php
                                                $name =
                                                    $aptitude->distance_type ??
                                                    ($aptitude->surface_type ?? $aptitude->running_style);
                                                $name = ucfirst(str_replace('_', ' ', $name));
                                            @endphp
                                            <x-aptitude-display :type="$name" :grade="$aptitude->grade" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($character->aptitudes->isEmpty())
                            <div class="text-center text-sm text-neutral-500 py-4">No aptitude data available.</div>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        <!-- Skills, Race Schedule, and Inherited Factors Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Skills -->
            <section class="card rounded-lg" role="region" aria-labelledby="skills-heading">
                <header
                    class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50 flex items-center justify-between">
                    <h2 id="skills-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Skills</h2>
                    <a href="{{ route('skills.index', ['character' => $character->id]) }}" class="btn btn-sm btn-primary"
                        aria-label="Manage Skills">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Manage Skills
                    </a>
                </header>
                <div class="p-2">
                    @if ($character->skills->count() > 0)
                        <div class="space-y-1">
                            @foreach ($character->skills->take(10) as $skill)
                                <div
                                    class="flex items-center justify-between p-2 rounded hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-yellow-400" aria-hidden="true"></div>
                                        <span
                                            class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ $skill->name }}</span>
                                    </div>
                                    @if ($skill->pivot && $skill->pivot->final_sp_cost)
                                        <span
                                            class="text-xs font-mono text-neutral-500">{{ $skill->pivot->final_sp_cost }}pt</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-sm text-neutral-500 py-6">
                            <svg class="mx-auto h-10 w-10 text-neutral-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <p class="mb-3">No skills acquired yet.</p>
                            <a href="{{ route('skills.index', ['character' => $character->id]) }}"
                                class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                                aria-label="Browse skills for {{ $character->name }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Browse Skills
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Race Schedule -->
            <section class="card rounded-lg" role="region" aria-labelledby="race-schedule-heading-bottom">
                <header
                    class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50 flex items-center justify-between">
                    <h2 id="race-schedule-heading-bottom" class="text-lg font-bold text-neutral-900 dark:text-white">Race
                        Schedule</h2>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Upcoming</span>
                </header>
                @php
                    $raceSchedule = $character->race_schedule ?? [];
                    $upcomingRaces = is_array($raceSchedule) ? array_slice($raceSchedule, 0, 5) : [];
                @endphp
                <div class="card-body">
                    @if (count($upcomingRaces) > 0)
                        <div class="space-y-2">
                            @foreach ($upcomingRaces as $race)
                                @php
                                    $raceName = $race['name'] ?? 'Unknown Race';
                                    $raceGrade = $race['grade'] ?? 'G1';
                                    $raceTurn = $race['turn'] ?? 0;
                                    $readiness = $race['readiness'] ?? 'unknown';
                                    $readinessColors = [
                                        'excellent' =>
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'good' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        'fair' =>
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'poor' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'unknown' => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-400',
                                    ];
                                    $readinessColor = $readinessColors[$readiness] ?? $readinessColors['unknown'];
                                @endphp
                                <div
                                    class="flex items-center justify-between p-2 rounded bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                            {{ $raceName }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $raceGrade }} • Turn
                                            {{ $raceTurn }}</div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $readinessColor }} ml-2">
                                        {{ ucfirst($readiness) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-sm text-neutral-500 py-6">
                            <svg class="mx-auto h-10 w-10 text-neutral-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2">No races scheduled yet.</p>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Inherited Factors -->
            <section class="card rounded-lg" role="region" aria-labelledby="inherited-factors-heading">
                <header
                    class="card-header bg-transparent border-b border-neutral-200/50 dark:border-neutral-700/50 flex items-center justify-between">
                    <h2 id="inherited-factors-heading" class="text-lg font-bold text-neutral-900 dark:text-white">Inherited
                        Factors</h2>
                    @can('update', $character)
                        <a href="{{ route('characters.factors.manage', $character) }}" class="btn btn-sm btn-primary"
                            aria-label="Manage Inherited Factors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Manage
                        </a>
                    @endcan
                </header>
                <div class="card-body">
                    @if ($character->factors->count() > 0)
                        @php
                            $factorsByType = $character->factors->groupBy('factor_type');
                            $factorTypeLabels = [
                                'blue_stats' => 'Stat Bonuses',
                                'red_aptitudes' => 'Aptitude Upgrades',
                                'green_unique_skills' => 'Unique Skills',
                                'white_normal_skills' => 'Normal Skills',
                            ];
                            $factorTypeColors = [
                                'blue_stats' => 'text-blue-600 dark:text-blue-400',
                                'red_aptitudes' => 'text-red-600 dark:text-red-400',
                                'green_unique_skills' => 'text-green-600 dark:text-green-400',
                                'white_normal_skills' => 'text-neutral-600 dark:text-neutral-400',
                            ];
                        @endphp

                        <div class="space-y-4">
                            @foreach ($factorsByType as $type => $factors)
                                <div>
                                    <h4
                                        class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                                        <div
                                            class="w-2 h-2 rounded-full bg-{{ str_replace('_stats', '', str_replace('_aptitudes', '', str_replace('_unique_skills', '', str_replace('_normal_skills', '', $type)))) }}-500"
                                            aria-hidden="true">
                                        </div>
                                        {{ $factorTypeLabels[$type] ?? ucfirst($type) }}
                                        <span class="text-neutral-500">({{ $factors->count() }})</span>
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach ($factors as $factor)
                                            <div
                                                class="flex items-center justify-between p-2.5 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700 {{ !$factor->is_active ? 'opacity-50' : '' }}">
                                                <div class="flex items-center gap-2">
                                                    <!-- Star Level -->
                                                    <div class="flex items-center" aria-hidden="true">
                                                        @for ($i = 1; $i <= 3; $i++)
                                                            <svg class="w-3 h-3 {{ $i <= (int) str_replace('_star', '', $factor->star_level) ? 'text-yellow-400' : 'text-neutral-300 dark:text-neutral-600' }}"
                                                                fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                    <span class="sr-only">{{ (int) str_replace('_star', '', $factor->star_level) }} star{{ (int) str_replace('_star', '', $factor->star_level) !== 1 ? 's' : '' }}</span>

                                                    <!-- Factor Name -->
                                                    <span class="text-sm font-medium text-neutral-900 dark:text-white">
                                                        {{ $factor->factor_name }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <!-- Factor Value/Bonus -->
                                                    @if ($factor->factor_type === 'blue_stats')
                                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                                            +{{ $factor->getStatBonus() }}
                                                        </span>
                                                    @elseif ($factor->factor_type === 'red_aptitudes')
                                                        <span class="text-xs font-bold text-red-600 dark:text-red-400">
                                                            +{{ (int) str_replace('_star', '', $factor->star_level) }}
                                                            grade{{ (int) str_replace('_star', '', $factor->star_level) > 1 ? 's' : '' }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="text-xs font-bold {{ $factorTypeColors[$factor->factor_type] ?? 'text-neutral-600 dark:text-neutral-400' }}">
                                                            {{ (int) str_replace('_star', '', $factor->star_level) }}★
                                                        </span>
                                                    @endif

                                                    <!-- Active Status -->
                                                    @if (!$factor->is_active)
                                                        <span
                                                            class="text-xs text-neutral-500 bg-neutral-200 dark:bg-neutral-700 px-1.5 py-0.5 rounded">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Factor Summary -->
                        @php
                            $activeFactors = $character->factors->where('is_active', true);
                            $totalStatBonuses = $activeFactors
                                ->where('factor_type', 'blue_stats')
                                ->reduce(function ($carry, $factor) {
                                    return $carry + $factor->getStatBonus();
                                }, 0);
                            $totalAptitudeUpgrades = $activeFactors
                                ->where('factor_type', 'red_aptitudes')
                                ->reduce(function ($carry, $factor) {
                                    return $carry + (int) str_replace('_star', '', $factor->star_level);
                                }, 0);
                            $uniqueSkills = $activeFactors->where('factor_type', 'green_unique_skills')->count();
                            $normalSkills = $activeFactors->where('factor_type', 'white_normal_skills')->count();
                        @endphp

                        @if ($activeFactors->count() > 0)
                            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                                <h4 class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-2">Active
                                    Bonuses</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @if ($totalStatBonuses > 0)
                                        <div class="flex justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                            <span class="text-blue-700 dark:text-blue-300">Total Stat Bonus</span>
                                            <span
                                                class="font-bold text-blue-800 dark:text-blue-200">+{{ $totalStatBonuses }}</span>
                                        </div>
                                    @endif
                                    @if ($totalAptitudeUpgrades > 0)
                                        <div class="flex justify-between p-2 bg-red-50 dark:bg-red-900/20 rounded">
                                            <span class="text-red-700 dark:text-red-300">Aptitude Upgrades</span>
                                            <span
                                                class="font-bold text-red-800 dark:text-red-200">+{{ $totalAptitudeUpgrades }}</span>
                                        </div>
                                    @endif
                                    @if ($uniqueSkills > 0)
                                        <div class="flex justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                            <span class="text-green-700 dark:text-green-300">Unique Skills</span>
                                            <span
                                                class="font-bold text-green-800 dark:text-green-200">{{ $uniqueSkills }}</span>
                                        </div>
                                    @endif
                                    @if ($normalSkills > 0)
                                        <div class="flex justify-between p-2 bg-neutral-50 dark:bg-neutral-800 rounded">
                                            <span class="text-neutral-700 dark:text-neutral-300">Normal Skills</span>
                                            <span
                                                class="font-bold text-neutral-800 dark:text-neutral-200">{{ $normalSkills }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-sm text-neutral-500 py-8">
                            <svg class="mx-auto h-10 w-10 text-neutral-400 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mb-2">No inherited factors.</p>
                            @can('update', $character)
                                <a href="{{ route('characters.factors.manage', $character) }}"
                                    class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                                    Add Factors
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
