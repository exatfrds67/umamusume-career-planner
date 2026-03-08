@extends('layouts.app')

@section('title', $supportCard->name . ' - Support Cards - ' . config('app.name'))

@php
    $allStatsZero = ($supportCard->speed_bonus + $supportCard->stamina_bonus + $supportCard->power_bonus + $supportCard->guts_bonus + $supportCard->wit_bonus) === 0;
    $hasTrainingBonuses = $supportCard->training_effect_bonus > 0 || $supportCard->event_effect_bonus > 0 || $supportCard->event_recovery_bonus > 0 || $supportCard->friendship_bonus > 0;
    $hasUniqueEffects = !empty($supportCard->unique_effects) && is_array($supportCard->unique_effects) && count($supportCard->unique_effects) > 0;
    $hasSkillHints = !empty($supportCard->skill_hints_provided) && is_array($supportCard->skill_hints_provided) && count($supportCard->skill_hints_provided) > 0;
    $hasStrategicNotes = !empty($supportCard->strategic_notes) && is_array($supportCard->strategic_notes) && count($supportCard->strategic_notes) > 0;
@endphp

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('support-cards.index') }}"
                class="inline-flex items-center text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300 focus:outline-hidden focus:ring-2 focus:ring-primary-500 rounded-md px-1">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Support Cards
            </a>
        </div>

        <!-- Card Header -->
        <div
            class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 overflow-hidden">
            <div class="md:flex">
                <!-- Card Image -->
                <div
                    class="md:w-1/3 h-64 md:h-auto bg-linear-to-br from-{{ $supportCard->card_type ? ($supportCard->card_type === 'speed' ? 'blue' : ($supportCard->card_type === 'stamina' ? 'green' : ($supportCard->card_type === 'power' ? 'red' : ($supportCard->card_type === 'guts' ? 'orange' : ($supportCard->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray' }}-400 to-{{ $supportCard->card_type ? ($supportCard->card_type === 'speed' ? 'blue' : ($supportCard->card_type === 'stamina' ? 'green' : ($supportCard->card_type === 'power' ? 'red' : ($supportCard->card_type === 'guts' ? 'orange' : ($supportCard->card_type === 'wit' ? 'purple' : 'pink'))))) : 'gray' }}-600 relative">
                    @if ($supportCard->artwork_url)
                        <img src="{{ $supportCard->artwork_url }}" alt="{{ $supportCard->name }}" loading="lazy"
                            decoding="async" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-32 h-32 text-white opacity-50" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Card Info -->
                <div class="md:w-2/3 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">{{ $supportCard->name }}</h1>
                            @if ($supportCard->character_name)
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $supportCard->character_name }}</p>
                            @endif
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <x-support-card-rarity-badge :rarity="$supportCard->rarity" />
                            <x-support-card-tier-badge :tier="$supportCard->meta_tier" />
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <x-support-card-type-badge :type="$supportCard->card_type" />
                        @if ($supportCard->is_limited)
                            <span
                                class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                Limited
                            </span>
                        @endif
                        <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-700 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            {{ ucfirst($supportCard->server_availability) }} Server
                        </span>
                        @if ($supportCard->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:text-green-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:text-red-400">
                                Inactive
                            </span>
                        @endif
                    </div>

                    @if ($supportCard->flavor_text)
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 italic mb-4">
                            "{{ $supportCard->flavor_text }}"
                        </p>
                    @endif

                    <!-- Card Details Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg text-sm">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Max Level</div>
                            <div class="font-semibold text-neutral-900 dark:text-white">{{ $supportCard->max_level }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Max Limit Break</div>
                            <div class="font-semibold text-neutral-900 dark:text-white">{{ $supportCard->max_limit_break }}</div>
                        </div>
                        @if ($supportCard->usage_rate)
                            <div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Usage Rate</div>
                                <div class="font-semibold text-neutral-900 dark:text-white">{{ number_format($supportCard->usage_rate, 1) }}%</div>
                            </div>
                        @endif
                        @if ($supportCard->win_rate_contribution)
                            <div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Win Rate Contribution</div>
                                <div class="font-semibold text-neutral-900 dark:text-white">{{ number_format($supportCard->win_rate_contribution, 1) }}%</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <!-- Stat Bonuses (full visual) -->
        <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Stat Bonuses</h2>
            <div class="grid grid-cols-5 gap-4">
                @foreach (['speed' => ['Speed', 'blue'], 'stamina' => ['Stamina', 'green'], 'power' => ['Power', 'red'], 'guts' => ['Guts', 'orange'], 'wit' => ['Wit', 'purple']] as $stat => [$label, $color])
                    @php $val = $supportCard->{$stat . '_bonus'}; @endphp
                    <div class="text-center p-3 rounded-lg {{ $val > 0 ? 'bg-' . $color . '-50 dark:bg-' . $color . '-900/20 ring-1 ring-' . $color . '-200 dark:ring-' . $color . '-800' : 'bg-neutral-50 dark:bg-neutral-700/50' }}">
                        <div class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">{{ $label }}</div>
                        <div class="text-xl font-bold {{ $val > 0 ? 'text-' . $color . '-600 dark:text-' . $color . '-400' : 'text-neutral-400 dark:text-neutral-500' }}">
                            {{ $val > 0 ? '+' . $val : '0' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Training & Effect Bonuses -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Training Bonuses</h2>
                <div class="space-y-3">
                    @foreach ([
                        'Training Effect' => ['training_effect_bonus', '%'],
                        'Event Recovery' => ['event_recovery_bonus', '%'],
                        'Event Effect' => ['event_effect_bonus', '%'],
                        'Friendship' => ['friendship_bonus', ''],
                    ] as $label => [$field, $suffix])
                        @php $val = $supportCard->{$field}; @endphp
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                            <span class="text-sm font-semibold {{ $val > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500' }}">
                                {{ $val > 0 ? '+' . $val . $suffix : '0' . $suffix }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Performance Data -->
            <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Performance & Meta</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Meta Tier</span>
                        <x-support-card-tier-badge :tier="$supportCard->meta_tier" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Usage Rate</span>
                        <span class="text-sm font-semibold {{ $supportCard->usage_rate ? 'text-neutral-900 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                            {{ $supportCard->usage_rate ? number_format($supportCard->usage_rate, 1) . '%' : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Win Rate Contribution</span>
                        <span class="text-sm font-semibold {{ $supportCard->win_rate_contribution ? 'text-neutral-900 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                            {{ $supportCard->win_rate_contribution ? number_format($supportCard->win_rate_contribution, 1) . '%' : 'N/A' }}
                        </span>
                    </div>
                    @if (!empty($supportCard->performance_data) && is_array($supportCard->performance_data))
                        @foreach ($supportCard->performance_data as $key => $value)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                <span class="text-sm font-semibold text-neutral-900 dark:text-white">{{ is_numeric($value) ? number_format($value, 1) : $value }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Unique Effects -->
        @if (!empty($supportCard->unique_effects) && is_array($supportCard->unique_effects) && count($supportCard->unique_effects) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Unique Effects</h2>
                <ul class="space-y-2">
                    @foreach ($supportCard->unique_effects as $effect)
                        <li class="flex items-start gap-2">
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs shrink-0 mt-0.5">
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $effect }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Skill Hints Provided -->
        @if (!empty($supportCard->skill_hints_provided) && is_array($supportCard->skill_hints_provided) && count($supportCard->skill_hints_provided) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Skill Hints Provided</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($supportCard->skill_hints_provided as $skill)
                        <span
                            class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-sm font-medium text-primary-800 dark:text-primary-400 ring-1 ring-inset ring-primary-200 dark:ring-primary-800">
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Guaranteed Events -->
        @if (!empty($supportCard->guaranteed_events) && is_array($supportCard->guaranteed_events) && count($supportCard->guaranteed_events) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Guaranteed Events</h2>
                <ul class="space-y-2">
                    @foreach ($supportCard->guaranteed_events as $event)
                        <li class="flex items-start gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 text-xs shrink-0 mt-0.5">
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </span>
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                @if (is_array($event))
                                    {{ json_encode($event) }}
                                @else
                                    {{ $event }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Special Conditions -->
        @if (!empty($supportCard->special_conditions) && is_array($supportCard->special_conditions) && count($supportCard->special_conditions) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Special Conditions</h2>
                <ul class="space-y-2">
                    @foreach ($supportCard->special_conditions as $condition)
                        <li class="flex items-start gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            </span>
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                @if (is_array($condition))
                                    {{ json_encode($condition) }}
                                @else
                                    {{ $condition }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Strategic Notes -->
        @if (!empty($supportCard->strategic_notes) && is_array($supportCard->strategic_notes) && count($supportCard->strategic_notes) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Strategic Notes</h2>
                <ul class="space-y-2">
                    @foreach ($supportCard->strategic_notes as $note)
                        <li class="flex items-start gap-2">
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            </span>
                            <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $note }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Deck Synergies -->
        @if (!empty($supportCard->deck_synergies) && is_array($supportCard->deck_synergies) && count($supportCard->deck_synergies) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Deck Synergies</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($supportCard->deck_synergies as $synergy)
                        <span
                            class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-3 py-1 text-sm font-medium text-indigo-800 dark:text-indigo-400 ring-1 ring-inset ring-indigo-200 dark:ring-indigo-800">
                            {{ is_array($synergy) ? json_encode($synergy) : $synergy }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recommended Scenarios -->
        @if (!empty($supportCard->recommended_scenarios) && is_array($supportCard->recommended_scenarios) && count($supportCard->recommended_scenarios) > 0)
            <div
                class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Recommended Scenarios</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($supportCard->recommended_scenarios as $scenario)
                        <span
                            class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1 text-sm font-medium text-green-800 dark:text-green-400 ring-1 ring-inset ring-green-200 dark:ring-green-800">
                            {{ str_replace('_', ' ', ucwords(is_array($scenario) ? json_encode($scenario) : $scenario, '_')) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Acquisition & Availability -->
        <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Acquisition & Availability</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Server Availability</div>
                    <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($supportCard->server_availability) }}</div>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Limited</div>
                    <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $supportCard->is_limited ? 'Yes' : 'No' }}</div>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Status</div>
                    <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $supportCard->is_active ? 'Active' : 'Inactive' }}</div>
                </div>
                @if ($supportCard->release_date)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Release Date</div>
                        <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $supportCard->release_date->format('M d, Y') }}</div>
                    </div>
                @endif
                @if ($supportCard->availability_end)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Availability End</div>
                        <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $supportCard->availability_end->format('M d, Y') }}</div>
                    </div>
                @endif
                @if (!empty($supportCard->acquisition_methods) && is_array($supportCard->acquisition_methods))
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Acquisition Methods</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($supportCard->acquisition_methods as $method)
                                <span class="inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-700 px-2.5 py-0.5 text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    {{ is_array($method) ? json_encode($method) : ucwords(str_replace('_', ' ', $method)) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Technical Details -->
        <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Card Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Card ID</div>
                    <div class="font-mono text-neutral-900 dark:text-white">{{ $supportCard->id }}</div>
                </div>
                @if ($supportCard->internal_id)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Internal ID</div>
                        <div class="font-mono text-neutral-900 dark:text-white text-xs break-all">{{ $supportCard->internal_id }}</div>
                    </div>
                @endif
                @if ($supportCard->external_source_id)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">External Source ID</div>
                        <div class="font-mono text-neutral-900 dark:text-white">{{ $supportCard->external_source_id }}</div>
                    </div>
                @endif
                @if ($supportCard->external_source)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">External Source</div>
                        <div class="text-neutral-900 dark:text-white">{{ ucfirst($supportCard->external_source) }}</div>
                    </div>
                @endif
                @if ($supportCard->gametora_id)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">GameTora ID</div>
                        <div class="font-mono text-neutral-900 dark:text-white text-xs">{{ $supportCard->gametora_id }}</div>
                    </div>
                @endif
                @if ($supportCard->chara_id)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Character ID</div>
                        <div class="font-mono text-neutral-900 dark:text-white">{{ $supportCard->chara_id }}</div>
                    </div>
                @endif
                @if ($supportCard->character_internal_id)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Character Internal ID</div>
                        <div class="font-mono text-neutral-900 dark:text-white text-xs break-all">{{ $supportCard->character_internal_id }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Max Level</div>
                    <div class="text-neutral-900 dark:text-white">{{ $supportCard->max_level }}</div>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Max Limit Break</div>
                    <div class="text-neutral-900 dark:text-white">{{ $supportCard->max_limit_break }}</div>
                </div>
                @if ($supportCard->created_at)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Added</div>
                        <div class="text-neutral-900 dark:text-white">{{ $supportCard->created_at->format('M d, Y H:i') }}</div>
                    </div>
                @endif
                @if ($supportCard->updated_at)
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Last Updated</div>
                        <div class="text-neutral-900 dark:text-white">{{ $supportCard->updated_at->format('M d, Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Metadata (raw JSON) -->
        @if (!empty($supportCard->card_metadata) && is_array($supportCard->card_metadata) && count($supportCard->card_metadata) > 0)
            <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Additional Metadata</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @foreach ($supportCard->card_metadata as $key => $value)
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                            <div class="text-neutral-900 dark:text-white">
                                @if (is_array($value))
                                    <pre class="text-xs font-mono bg-neutral-50 dark:bg-neutral-900 p-2 rounded overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Artwork Variants -->
        @if (!empty($supportCard->artwork_variants) && is_array($supportCard->artwork_variants) && count($supportCard->artwork_variants) > 0)
            <div class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Artwork Variants</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($supportCard->artwork_variants as $variant)
                        @if (is_string($variant))
                            <div class="aspect-3/4 rounded-lg overflow-hidden bg-neutral-100 dark:bg-neutral-700">
                                <img src="{{ $variant }}" alt="Artwork variant" loading="lazy" decoding="async" class="w-full h-full object-cover">
                            </div>
                        @elseif (is_array($variant) && isset($variant['url']))
                            <div class="space-y-1">
                                <div class="aspect-3/4 rounded-lg overflow-hidden bg-neutral-100 dark:bg-neutral-700">
                                    <img src="{{ $variant['url'] }}" alt="{{ $variant['label'] ?? 'Artwork variant' }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                </div>
                                @if (isset($variant['label']))
                                    <p class="text-xs text-center text-neutral-500 dark:text-neutral-400">{{ $variant['label'] }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
