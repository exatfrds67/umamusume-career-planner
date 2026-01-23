@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\Character $character */
    @endphp
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header / Back Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('characters.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Characters
            </a>
            <div class="flex space-x-3">
                <form action="{{ route('characters.rest', $character) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-green-700 dark:text-green-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        Rest
                    </button>
                </form>

                <form action="{{ route('characters.next-turn', $character) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-blue-700 dark:text-blue-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="glass-card overflow-visible rounded-xl">
            <div class="p-6 md:p-8 relative overflow-hidden">
                <!-- Background Decoration -->
                <div
                    class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50 pointer-events-none">
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-start relative">
                    <!-- Avatar -->
                    <div class="shrink-0 relative">
                        @php
                            $normalizedName = strtolower(str_replace(' ', '-', $character->name));
                            $availableAvatars = [
                                'agnes-tachyon',
                                'gold-ship',
                                'narita-brian',
                                'tokai-teio',
                                'vodka',
                                'daiwa-scarlet',
                                'el-condor-pasa',
                                'haru-urara',
                                'maruzensky',
                                'oguri-cap',
                            ];
                            $avatarClass = in_array($normalizedName, $availableAvatars)
                                ? "character-avatar-{$normalizedName}"
                                : 'character-avatar-default';
                        @endphp
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white dark:border-gray-800 shadow-xl bg-cover bg-center bg-linear-to-br {{ $avatarClass }}"
                            aria-label="{{ $character->name }} avatar" role="img">
                        </div>
                        <span class="absolute bottom-2 right-2 flex h-5 w-5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span
                                class="relative inline-flex rounded-full h-5 w-5 bg-green-500 border-2 border-white dark:border-gray-800"></span>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 space-y-4 w-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                                        {{ $character->name }}</h1>
                                    <x-ui.grade-badge :grade="$character->getStatGrade($character->current_stats['speed'] ?? 0)" size="sm" />
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
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
                                <x-dashboard.ai-advisor-card class="shadow-sm border-0" :lastTip="$aiTip" />
                            </div>
                        </div>

                        <!-- Quick Progress Bars -->
                        <div
                            class="max-w-2xl bg-white/50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50 backdrop-blur-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-gray-500 dark:text-gray-400">Energy Level</span>
                                        <span
                                            class="{{ $character->energy_level < 30 ? 'text-red-500' : 'text-green-500' }}">{{ $character->energy_level }}%</span>
                                    </div>
                                    <x-ui.progress-bar :value="$character->energy_level" :max="100"
                                        color="{{ $character->energy_level < 30 ? 'bg-red-500' : 'bg-green-500' }}"
                                        size="sm" :show-text="false" />
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-semibold uppercase tracking-wider">
                                        <span class="text-gray-500 dark:text-gray-400">Goal Progress</span>
                                        <span class="text-primary-500">{{ $character->getProgressPercentage() }}%</span>
                                    </div>
                                    <x-ui.progress-bar :value="$character->getProgressPercentage()" :max="100" color="bg-primary-500"
                                        size="sm" :show-text="false" />
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
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Stats -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Detailed Stats Card -->
                <div class="glass-card-alt rounded-lg">
                    <div
                        class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Current Statistics</h3>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Updated
                            {{ $character->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="space-y-6">
                            @foreach (['speed' => 'blue', 'stamina' => 'orange', 'power' => 'red', 'guts' => 'pink', 'wit' => 'green'] as $stat => $color)
                                @php
                                    $val = $character->getStat($stat);
                                    $target = $character->goals['target_stats'][$stat] ?? 0;
                                    $max = 1200;
                                    $percentage = ($val / $max) * 100;
                                    $targetPercentage = $target > 0 ? min(100, ($target / $max) * 100) : 0;
                                @endphp
                                <div class="group">
                                    <div class="flex items-end justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-16 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ ucfirst($stat) }}</span>
                                            <x-ui.grade-badge :grade="$character->getStatGrade($val)" size="sm" />
                                            <span
                                                class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $val }}</span>
                                        </div>
                                        @if ($target > 0)
                                            <div class="text-xs text-gray-400">Target: <span
                                                    class="font-medium text-gray-600 dark:text-gray-300">{{ $target }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="relative h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <!-- Target Marker -->
                                        @if ($target > 0)
                                            <div class="absolute top-0 bottom-0 w-0.5 bg-gray-400 dark:bg-gray-500 z-10"
                                                @style(['left' => $targetPercentage . '%'])></div>
                                        @endif

                                        <!-- Fill -->
                                        <div class="absolute top-0 left-0 bottom-0 rounded-full transition-all duration-500 ease-out stat-bar-{{ $stat }}"
                                            @style(['width' => $percentage . '%'])></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Race Schedule -->
                <div class="glass-card-alt rounded-lg">
                    <div
                        class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Race Schedule</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Upcoming Races</span>
                    </div>
                    @php
                        $raceSchedule = $character->race_schedule ?? [];
                        $upcomingRaces = is_array($raceSchedule) ? array_slice($raceSchedule, 0, 5) : [];
                    @endphp
                    @if (count($upcomingRaces) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Race</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Grade</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Turn</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Readiness</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($upcomingRaces as $race)
                                        @php
                                            $raceName = $race['name'] ?? 'Unknown Race';
                                            $raceGrade = $race['grade'] ?? 'G1';
                                            $raceTurn = $race['turn'] ?? 0;
                                            $readiness = $race['readiness'] ?? 'unknown';
                                            $readinessColors = [
                                                'excellent' =>
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'good' =>
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                'fair' =>
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'poor' =>
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                'unknown' =>
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
                                            ];
                                            $readinessColor =
                                                $readinessColors[$readiness] ?? $readinessColors['unknown'];
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $raceName }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $raceGrade }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Turn
                                                {{ $raceTurn }}</td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $readinessColor }}">
                                                    {{ ucfirst($readiness) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2">No races scheduled yet.</p>
                        </div>
                    @endif
                </div>

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
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Training Careers</h3>
                    </div>
                    @if ($character->careers->count() > 0)
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($character->careers as $career)
                                <div
                                    class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $career->scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup' }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $career->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <x-ui.grade-badge :grade="$career->final_grade ?? 'E'" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No careers completed yet. Start training to build history!
                        </div>
                    @endif
                </div>
                --}}
            </div>

            <!-- Right Column: Aptitudes & Skills -->
            <div class="space-y-6">
                <!-- Aptitudes -->
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Aptitudes</h3>
                    </div>
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
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                        {{ $groupName }}</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach ($aptitudes as $aptitude)
                                            @php
                                                $name =
                                                    $aptitude->distance_type ??
                                                    ($aptitude->surface_type ?? $aptitude->running_style);
                                                $name = ucfirst(str_replace('_', ' ', $name));
                                            @endphp
                                            <div
                                                class="flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                <span class="text-sm dark:text-gray-300">{{ $name }}</span>
                                                <span
                                                    class="font-bold text-gray-900 dark:text-white">{{ $aptitude->grade }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($character->aptitudes->isEmpty())
                            <div class="text-center text-sm text-gray-500 py-4">No aptitude data available.</div>
                        @endif
                    </div>
                </div>

                <!-- Skills -->
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Skills</h3>
                    </div>
                    <div class="p-2">
                        @if ($character->skills->count() > 0)
                            <div class="space-y-1">
                                @foreach ($character->skills->take(10) as $skill)
                                    <div
                                        class="flex items-center justify-between p-2.5 rounded hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $skill->skill_name }}</span>
                                        </div>
                                        @if ($skill->pivot && $skill->pivot->sp_cost)
                                            <span
                                                class="text-xs font-mono text-gray-400">{{ $skill->pivot->sp_cost }}pt</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-sm text-gray-500 py-8">
                                No skills acquired yet.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Support Deck -->
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Support Deck</h3>
                    </div>
                    <div class="card-body">
                        @if ($character->supportCards->count() > 0)
                            <div class="space-y-3">
                                @foreach ($character->supportCards as $supportCard)
                                    @php
                                        $card = $supportCard->supportCardDefinition;
                                        $bondLevel = $supportCard->bond_level ?? 0;
                                        $bondMax = 100;
                                        $bondPercentage = ($bondLevel / $bondMax) * 100;
                                    @endphp
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                                        <div
                                            class="shrink-0 w-10 h-10 rounded-full bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($card->card_type ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $card->name ?? 'Unknown Card' }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div
                                                    class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div class="h-full bg-primary-500 rounded-full transition-all"
                                                        @style(['width' => $bondPercentage . '%'])></div>
                                                </div>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">{{ $bondLevel }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-sm text-gray-500 py-8">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                No support cards equipped.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Inherited Factors -->
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Inherited Factors</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $inheritedFactors = $character->inherited_factors ?? [];
                            $legacyParents = $character->legacy_parents ?? [];
                        @endphp
                        @if (!empty($legacyParents) && is_array($legacyParents))
                            <div class="space-y-3 mb-4">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Parents</h4>
                                @foreach ($legacyParents as $parent)
                                    @php
                                        $parentName = is_array($parent) ? $parent['name'] ?? 'Unknown' : $parent;
                                    @endphp
                                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $parentName }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($inheritedFactors) && is_array($inheritedFactors))
                            <div class="space-y-2">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Factor Bonuses
                                </h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($inheritedFactors as $factor => $value)
                                        <div
                                            class="flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                            <span
                                                class="text-xs text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $factor)) }}</span>
                                            <span
                                                class="text-xs font-bold text-primary-600 dark:text-primary-400">+{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center text-sm text-gray-500 py-8">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                No inherited factors.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
