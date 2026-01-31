{{-- WF-004: Status Bar - Energy, Mood, Turn, Fail% --}}
<section class="card rounded-xl p-4 mb-6 animate-fade-in-delay-2" aria-labelledby="status-bar-heading">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6 flex-wrap">
            {{-- Energy Gauge --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Energy:</span>
                <div class="flex items-center gap-2">
                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        @php
                            $energyColor = match (true) {
                                $character->energy_level >= 70 => 'bg-green-500',
                                $character->energy_level >= 40 => 'bg-yellow-500',
                                $character->energy_level >= 20 => 'bg-orange-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <div class="h-full transition-all duration-300 {{ $energyColor }}"
                            style="width: {{ $character->energy_level }}%"></div>
                    </div>
                    <span
                        class="text-sm font-semibold text-gray-900 dark:text-white">{{ $character->energy_level }}/100</span>
                </div>
            </div>

            {{-- Mood Indicator --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Mood:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white capitalize flex items-center gap-1">
                    @switch($character->mood_status)
                        @case('great')
                            <span class="text-green-500">😊</span> Great <span class="text-xs text-green-600">(+4%)</span>
                        @break

                        @case('good')
                            <span class="text-blue-500">🙂</span> Good <span class="text-xs text-blue-600">(+2%)</span>
                        @break

                        @case('normal')
                            <span class="text-gray-500">😐</span> Normal
                        @break

                        @case('bad')
                            <span class="text-orange-500">🙁</span> Bad <span class="text-xs text-orange-600">(-2%)</span>
                        @break

                        @case('awful')
                            <span class="text-red-500">😞</span> Awful <span class="text-xs text-red-600">(-4%)</span>
                        @break

                        @default
                            <span class="text-gray-500">😐</span> {{ $character->mood_status }}
                    @endswitch
                </span>
            </div>

            {{-- Turn Counter --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Turn:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $character->current_turn ?? 1 }}/78
                </span>
            </div>

            {{-- Base Fail Rate --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Base Fail%:</span>
                @php
                    $baseFailRate = match (true) {
                        $character->energy_level >= 70 => 0,
                        $character->energy_level >= 50 => 5,
                        $character->energy_level >= 30 => 15,
                        $character->energy_level >= 10 => 30,
                        default => 50,
                    };
                    $failColor = match (true) {
                        $baseFailRate <= 5 => 'text-green-600 dark:text-green-400',
                        $baseFailRate <= 15 => 'text-yellow-600 dark:text-yellow-400',
                        $baseFailRate <= 30 => 'text-orange-600 dark:text-orange-400',
                        default => 'text-red-600 dark:text-red-400',
                    };
                @endphp
                <span class="text-sm font-semibold {{ $failColor }}">{{ $baseFailRate }}%</span>
            </div>
        </div>

        {{-- Scenario Badge --}}
        <div
            class="px-3 py-1 rounded-full text-xs font-medium {{ $character->scenario_type === 'unity_cup' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' }}">
            {{ str_replace('_', ' ', ucwords($character->scenario_type)) }}
        </div>
    </div>
</section>
