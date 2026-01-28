@extends('layouts.app')

@section('content')
    <main class="space-y-6">
        <!-- Breadcrumbs & Header -->
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <nav aria-label="Breadcrumb">
                    <a href="{{ route('races.index') }}" class="btn btn-secondary btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Calendar
                    </a>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $race->race_name }}
                    <span class="ml-2 px-2.5 py-0.5 rounded text-sm font-bold 
                        {{ $race->race_grade === 'G1' ? 'bg-yellow-100 text-yellow-800' : 
                           ($race->race_grade === 'G2' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}"
                           aria-label="Grade {{ $race->race_grade }}">
                        {{ $race->race_grade }}
                    </span>
                </h1>
            </div>
            @if($race->turn_number)
                <div class="text-sm font-medium text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full"
                     role="status" aria-label="Current Turn">
                    Turn {{ $race->turn_number }}
                </div>
            @endif
        </header>

        <!-- Race Info Card -->
        <section class="card bg-white dark:bg-gray-800 overflow-hidden" aria-labelledby="race-info-title">
            <h2 id="race-info-title" class="sr-only">Race Information</h2>
            <div class="md:flex">
                <div class="p-8 md:w-1/2 bg-linear-to-br from-primary-600 to-primary-800 text-white flex flex-col justify-center">
                    <div class="uppercase tracking-wide text-sm font-semibold text-primary-200">Course Details</div>
                    <div class="mt-2 text-3xl font-extrabold" aria-label="Distance">{{ $race->distance_meters }}m</div>
                    <dl class="mt-1 text-xl text-primary-100 flex items-center gap-2">
                         <div class="flex items-center">
                            <dt class="sr-only">Surface</dt>
                            <dd>{{ $race->surface }}</dd>
                         </div>
                         <span aria-hidden="true">•</span>
                         <div class="flex items-center">
                            <dt class="sr-only">Category</dt>
                            <dd>{{ $race->distance_category }}</dd>
                         </div>
                         <span aria-hidden="true">•</span>
                         <div class="flex items-center">
                            <dt class="sr-only">Weather</dt>
                            <dd>{{ $race->weather ?? 'Unknown Weather' }}</dd>
                         </div>
                    </dl>
                    @if($race->race_conditions)
                        <div class="mt-6">
                            <h3 class="text-sm font-bold text-primary-200 uppercase">Conditions</h3>
                            <ul class="mt-2 space-y-1 text-sm">
                                @foreach($race->race_conditions as $condition)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $condition }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <div class="p-8 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Character Snapshot</h3>
                    
                    @if($race->character)
                        @php
                            $avatarPath = null;
                            if ($race->character) {
                                $attributes = $race->character->getAttributes();
                                $avatarPath = $attributes['avatar_path'] ?? null;
                            }
                        @endphp
                        <article class="flex items-center gap-4 mb-6">
                            @if($avatarPath)
                                <img src="{{ $avatarPath }}" alt="{{ $race->character->name }}" loading="lazy" decoding="async" class="w-16 h-16 rounded-full object-cover">
                            @else
                                <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400" aria-hidden="true">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $race->character->name }}</h4>
                                <dl class="text-xs text-gray-500 flex gap-2">
                                    <div class="flex gap-1">
                                        <dt>Condition:</dt>
                                        <dd>{{ $race->character_condition ?? 'Unknown' }}</dd>
                                    </div>
                                    <span aria-hidden="true">|</span>
                                    <div class="flex gap-1">
                                        <dt>Mood:</dt>
                                        <dd>{{ $race->motivation ?? 'Normal' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </article>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-5 gap-2 text-center" role="list" aria-label="Character Stats">
                            @foreach(['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                @php $val = $race->{$stat . '_at_race'} ?? 0; @endphp
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2" role="listitem">
                                    <dt class="text-xs text-gray-500 uppercase">{{ $stat }}</dt>
                                    <dd class="font-bold text-gray-900 dark:text-white">{{ $val }}</dd>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500 italic">No character data recorded for this race.</div>
                    @endif

                    @if($race->finish_position)
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700" role="status">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">Race Result</h4>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-4xl font-extrabold 
                                    {{ $race->finish_position == 1 ? 'text-yellow-500' : 'text-gray-900 dark:text-white' }}"
                                    aria-label="Position {{ $race->finish_position }}">
                                    {{ $race->finish_position }}
                                </span>
                                <span class="text-gray-500 font-medium text-lg">
                                    {{ \Illuminate\Support\Number::ordinal($race->finish_position) }} Place
                                </span>
                            </div>
                            @if($race->finish_time)
                                <p class="text-sm text-gray-500 mt-1">Time: {{ $race->finish_time }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Preparation & Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($race->preparation_strategy)
                <section class="card bg-white dark:bg-gray-800 h-full" aria-labelledby="prep-strategy-title">
                    <div class="card-header">
                        <h3 id="prep-strategy-title" class="font-medium">Preparation Strategy</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            @foreach($race->preparation_strategy as $strat)
                                <li>{{ $strat }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if($race->performance_analysis)
                 <section class="card bg-white dark:bg-gray-800 h-full" aria-labelledby="perf-analysis-title">
                    <div class="card-header">
                        <h3 id="perf-analysis-title" class="font-medium">Performance Analysis</h3>
                    </div>
                    <div class="card-body">
                         <div class="prose dark:prose-invert text-sm">
                             @if(is_array($race->performance_analysis))
                                <ul class="list-disc list-inside">
                                    @foreach($race->performance_analysis as $analysis)
                                        <li>{{ $analysis }}</li>
                                    @endforeach
                                </ul>
                             @else
                                {{ $race->performance_analysis }}
                             @endif
                         </div>
                    </div>
                </section>
            @endif
        </div>
    </main>
@endsection
