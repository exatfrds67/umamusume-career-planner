@extends('layouts.app')

@section('title', $gameRace->name_en)

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[
        ['label' => 'Races', 'url' => route('races.index')],
        ['label' => $gameRace->name_en],
    ]" />

    <div class="space-y-6">
        {{-- Header --}}
        @php
            $gradeColors = [
                'G1'     => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'G2'     => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                'G3'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'OP'     => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'Pre-OP' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
                'Debut'  => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            ];
            $phaseLabels = [
                'junior'  => 'Junior (Year 1)',
                'classic' => 'Classic (Year 2)',
                'senior'  => 'Senior (Year 3+)',
                'all'     => 'All Phases',
            ];
        @endphp

        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $gradeColors[$gameRace->grade] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        <span class="sr-only">Grade: </span>{{ $gameRace->grade }}
                    </span>
                    @if ($gameRace->is_ura_finale)
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                            <span aria-hidden="true">⭐</span><span class="sr-only">Special: </span> URA Finale
                        </span>
                    @endif
                    @if (isset($phaseLabels[$gameRace->phase]))
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $phaseLabels[$gameRace->phase] }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $gameRace->name_en }}</h1>
                @if ($gameRace->name_jp)
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $gameRace->name_jp }}</p>
                @endif
            </div>
            <a href="{{ route('races.index') }}"
                class="btn btn-secondary text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">
                ← Back to Races
            </a>
        </header>

        {{-- Course Info Card --}}
        <section class="card bg-white dark:bg-gray-800 overflow-hidden" aria-labelledby="course-info-heading">
            <h2 id="course-info-heading" class="sr-only">Course Information</h2>
            <div class="md:flex">
                {{-- Left panel: key metrics --}}
                <div class="md:w-2/5 p-8 bg-linear-to-br from-primary-600 to-primary-800 text-white flex flex-col justify-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-primary-200">Course Details</p>
                    <div class="mt-2 text-5xl font-extrabold" aria-label="Distance">
                        {{ number_format($gameRace->distance_meters) }}<span class="text-2xl font-medium ml-1">m</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-3 text-primary-100 text-sm">
                        <span>{{ ucfirst($gameRace->surface) }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ ucwords(str_replace('_', ' ', $gameRace->distance_category)) }}</span>
                        @if ($gameRace->hand)
                            <span aria-hidden="true">·</span>
                            <span>{{ ucfirst($gameRace->hand) }}-handed</span>
                        @endif
                    </div>
                    @if ($gameRace->venue || $gameRace->season)
                        <div class="mt-4 space-y-1 text-sm text-primary-200">
                            @if ($gameRace->venue)
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $gameRace->venue }}
                                </div>
                            @endif
                            @if ($gameRace->season)
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                    </svg>
                                    {{ ucfirst($gameRace->season) }}
                                    @if ($gameRace->month_label)
                                        — {{ $gameRace->month_label }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Right panel: requirements & rewards --}}
                <div class="md:w-3/5 p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Fan Requirement --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Fan Requirement</h3>
                        @if ($gameRace->fan_requirement)
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($gameRace->fan_requirement) }}
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">fans</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">None</p>
                        @endif
                    </div>

                    {{-- Stat Requirements --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Stat Requirements</h3>
                        @if ($gameRace->stat_requirements && count($gameRace->stat_requirements) > 0)
                            <dl class="space-y-1">
                                @foreach ($gameRace->stat_requirements as $stat => $value)
                                    <div class="flex items-center justify-between text-sm">
                                        <dt class="capitalize text-gray-600 dark:text-gray-400">{{ $stat }}</dt>
                                        <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($value) }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">None recorded</p>
                        @endif
                    </div>

                    {{-- Fans Reward --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Fans Reward (Win)</h3>
                        @if ($gameRace->fans_reward)
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                +{{ number_format($gameRace->fans_reward) }}
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">fans</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">—</p>
                        @endif
                    </div>

                    {{-- SP Reward --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">SP Reward</h3>
                        @if ($gameRace->sp_reward)
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                +{{ number_format($gameRace->sp_reward) }}
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">SP</span>
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">—</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Year in Scenario --}}
        @if ($gameRace->year_in_scenario)
            <section class="card bg-white dark:bg-gray-800 p-6" aria-labelledby="scenario-heading">
                <h2 id="scenario-heading" class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Scenario Timing</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    Available in Year {{ $gameRace->year_in_scenario }} of the scenario.
                    @if ($gameRace->month_label)
                        Held in <strong>{{ $gameRace->month_label }}</strong>.
                    @endif
                </p>
            </section>
        @endif

        {{-- Notes --}}
        @if ($gameRace->notes)
            <section class="card bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-6" aria-labelledby="notes-heading">
                <h2 id="notes-heading" class="flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Notes
                </h2>
                <p class="text-sm text-amber-900 dark:text-amber-200">{{ $gameRace->notes }}</p>
            </section>
        @endif

        {{-- Back link --}}
        <div>
            <a href="{{ route('races.index') }}" class="btn btn-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 rounded">
                ← Back to Race Calendar
            </a>
        </div>
    </div>
@endsection
