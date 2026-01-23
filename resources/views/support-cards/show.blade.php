@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('support-cards.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Support Cards
            </a>
        </div>

        <!-- Card Header -->
        <div
            class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
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
                                stroke="currentColor">
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
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $supportCard->name }}</h1>
                            @if ($supportCard->character_name)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $supportCard->character_name }}</p>
                            @endif
                        </div>
                        <div class="flex gap-2">
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
                    </div>

                    @if ($supportCard->flavor_text)
                        <p class="text-sm text-gray-600 dark:text-gray-400 italic mb-4">
                            "{{ $supportCard->flavor_text }}"
                        </p>
                    @endif

                    <!-- Usage Stats -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Usage Rate</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($supportCard->usage_rate, 1) }}%</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Win Rate Contribution</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($supportCard->win_rate_contribution, 1) }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats & Bonuses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stat Bonuses -->
            <div
                class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stat Bonuses</h2>
                <div class="space-y-3">
                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                        @php $bonus = $supportCard->{$stat.'_bonus'}; @endphp
                        @if ($bonus > 0)
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($stat) }}</span>
                                <span
                                    class="text-sm font-semibold text-primary-600 dark:text-primary-400">+{{ $bonus }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if ($supportCard->friendship_bonus > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Friendship</span>
                            <span
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400">+{{ $supportCard->friendship_bonus }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Training Bonuses -->
            <div
                class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Training Bonuses</h2>
                <div class="space-y-3">
                    @if ($supportCard->training_effect_bonus > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Training Effect</span>
                            <span
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400">+{{ $supportCard->training_effect_bonus }}%</span>
                        </div>
                    @endif
                    @if ($supportCard->event_recovery_bonus > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Event Recovery</span>
                            <span
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400">+{{ $supportCard->event_recovery_bonus }}%</span>
                        </div>
                    @endif
                    @if ($supportCard->event_effect_bonus > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Event Effect</span>
                            <span
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400">+{{ $supportCard->event_effect_bonus }}%</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Skill Hints -->
        @if (!empty($supportCard->skill_hints_provided))
            <div
                class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Skill Hints Provided</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($supportCard->skill_hints_provided as $skill)
                        <span
                            class="inline-flex items-center rounded-full bg-primary-100 px-3 py-1 text-sm font-medium text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recommended Scenarios -->
        @if (!empty($supportCard->recommended_scenarios))
            <div
                class="card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recommended Scenarios</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($supportCard->recommended_scenarios as $scenario)
                        <span
                            class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            {{ str_replace('_', ' ', ucwords($scenario, '_')) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
