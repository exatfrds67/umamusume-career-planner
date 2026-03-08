{{-- WF-004: Status Bar - Energy, Mood, Turn, Fail% --}}
<section class="status-strip p-4 mb-6 animate-fade-in-delay-2" aria-labelledby="status-bar-heading">
    <h2 id="status-bar-heading" class="sr-only">Current Status</h2>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6 flex-wrap">
            {{-- Energy Gauge --}}
            <div class="flex items-center gap-2">
                <span class="status-strip__item-label">Energy</span>
                <div class="flex items-center gap-2">
                    <div class="energy-rail"
                        role="progressbar"
                        aria-valuenow="{{ $character->energy_level }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="Energy level: {{ $character->energy_level }} out of 100">
                        @php
                            $energyColor = match (true) {
                                $character->energy_level >= 70 => 'bg-green-500',
                                $character->energy_level >= 40 => 'bg-yellow-500',
                                $character->energy_level >= 20 => 'bg-orange-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <div class="energy-fill {{ $energyColor }}"
                            style="width: {{ $character->energy_level }}%;"></div>
                    </div>
                    <span class="status-strip__item-value text-sm font-semibold">{{ $character->energy_level }}/100</span>
                </div>
            </div>

            {{-- Mood Indicator --}}
            <div class="flex items-center gap-2">
                <span class="status-strip__item-label">Mood</span>
                <span class="status-strip__item-value text-sm font-semibold capitalize flex items-center gap-1">
                    @switch($character->mood_status)
                        @case('great')
                            <svg class="w-5 h-5 inline-block text-green-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Great <span class="text-xs text-green-500 dark:text-green-400">(+10%)</span>
                        @break

                        @case('good')
                            <svg class="w-5 h-5 inline-block text-blue-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Good <span class="text-xs text-blue-400 dark:text-blue-300">(+5%)</span>
                        @break

                        @case('normal')
                            <svg class="w-5 h-5 inline-block text-neutral-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6m-6-4h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Normal
                        @break

                        @case('bad')
                            <svg class="w-5 h-5 inline-block text-orange-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Bad <span class="text-xs text-orange-500 dark:text-orange-400">(-15%)</span>
                        @break

                        @case('awful')
                            <svg class="w-5 h-5 inline-block text-red-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Awful <span class="text-xs text-red-500 dark:text-red-400">(-30%)</span>
                        @break

                        @default
                            <svg class="w-5 h-5 inline-block text-neutral-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6m-6-4h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ $character->mood_status }}
                    @endswitch
                </span>
            </div>

            {{-- Turn Counter --}}
            <div class="flex items-center gap-2">
                <span class="status-strip__item-label">Turn</span>
                <span class="status-strip__item-value text-sm font-semibold">
                    {{ $character->current_turn ?? 1 }}/78
                </span>
            </div>

            {{-- Base Fail Rate --}}
            <div class="flex items-center gap-2">
                <span class="status-strip__item-label">Base Fail%</span>
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
