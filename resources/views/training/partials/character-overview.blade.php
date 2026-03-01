{{-- WF-004: Character Overview with Stats, Facility Levels, Support Cards --}}
@php
    $statColors = [
        'speed' => 'text-stat-speed-600 dark:text-stat-speed-400',
        'stamina' => 'text-stat-stamina-600 dark:text-stat-stamina-400',
        'power' => 'text-stat-power-600 dark:text-stat-power-400',
        'guts' => 'text-stat-guts-600 dark:text-stat-guts-400',
        'wit' => 'text-stat-wit-600 dark:text-stat-wit-400',
    ];
    $getGrade = function ($value) {
        if ($value >= 1200) {
            return ['SS', 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'];
        }
        if ($value >= 1100) {
            return ['S', 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'];
        }
        if ($value >= 901) {
            return ['A', 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'];
        }
        if ($value >= 701) {
            return ['B', 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'];
        }
        if ($value >= 501) {
            return ['C', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'];
        }
        if ($value >= 301) {
            return ['D', 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'];
        }
        return ['E', 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'];
    };
    $getMultiplier = function ($level) {
        return match ((int) $level) {
            1 => '1.00×',
            2 => '1.25×',
            3 => '1.50×',
            4 => '1.75×',
            5 => '2.00×',
            default => '1.00×',
        };
    };
@endphp

<section class="card rounded-xl p-6 mb-6 animate-fade-in-delay-2" aria-labelledby="char-overview-heading">
    <h2 id="char-overview-heading" class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
        {{ $character->name }}
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Current Stats with Grade Badges --}}
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                Current Stats
                <span class="text-xs text-gray-500">Soft Cap: 1200</span>
            </h3>
            <div class="space-y-2">
                @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                    @php
                        $statValue = $character->current_stats[$stat] ?? 0;
                        $grade = $getGrade($statValue);
                        $isOverCap = $statValue > 1200;
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-sm {{ $statColors[$stat] }} capitalize font-medium">{{ $stat }}</span>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm font-semibold text-gray-900 dark:text-white {{ $isOverCap ? 'text-yellow-600 dark:text-yellow-400' : '' }}">
                                {{ $statValue }}
                                @if ($isOverCap)
                                    <span class="text-xs text-yellow-600 inline-flex items-center"
                                        aria-label="Above soft cap — gains reduced to +50 max">
                                        <svg class="w-4 h-4 ml-1" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span class="sr-only">Above soft cap — gains reduced to +50 max</span>
                                    </span>
                                @endif
                            </span>
                            <span
                                class="px-1.5 py-0.5 rounded text-xs font-medium {{ $grade[1] }}">{{ $grade[0] }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ array_sum($character->current_stats ?? []) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Facility Levels (Unity Cup) or Growth Rates (URA) --}}
        @if ($character->scenario_type === 'unity_cup')
            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                    Facility Levels
                    <span class="text-xs text-gray-500">1.0×-2.0×</span>
                </h3>
                <div class="space-y-2">
                    @php $facilityLevels = $character->facility_levels ?? []; @endphp
                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                        @php
                            $level = $facilityLevels[$stat] ?? 1;
                            $multiplier = $getMultiplier($level);
                        @endphp
                        <div class="flex justify-between items-center">
                            <span
                                class="text-sm {{ $statColors[$stat] }} capitalize font-medium">{{ $stat }}</span>
                            <div class="flex items-center gap-2">
                                <div class="flex gap-0.5" aria-hidden="true">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div
                                            class="w-2 h-2 rounded-full {{ $i <= $level ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                        </div>
                                    @endfor
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white">
                                    <span aria-hidden="true">{{ $multiplier }}</span>
                                    <span class="sr-only">Level {{ $level }}, {{ $multiplier }}</span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Growth Rates</h3>
                <div class="space-y-2">
                    @php $growthRates = $character->growth_rates ?? []; @endphp
                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                        @php $rate = $growthRates[$stat] ?? 0; @endphp
                        <div class="flex justify-between items-center">
                            <span
                                class="text-sm {{ $statColors[$stat] }} capitalize font-medium">{{ $stat }}</span>
                            <span
                                class="text-sm font-semibold {{ $rate > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                +{{ $rate }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Support Cards Summary --}}
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Support Cards</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-300" aria-hidden="true" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $character->supportCards->count() }}/6 cards equipped
                    </span>
                </div>
                @php
                    $rainbowCount = $character->supportCards->filter(fn($c) => ($c->bond_level ?? 0) >= 80)->count();
                    $cardsByType = $character->supportCards->groupBy(fn($c) => $c->supportCard->card_type ?? 'unknown');
                @endphp
                @if ($rainbowCount > 0)
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" /></svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $rainbowCount }} at friendship (80%+)
                        </span>
                    </div>
                @endif
                {{-- Card type distribution --}}
                <div class="flex flex-wrap gap-1 pt-2 border-t border-gray-200 dark:border-gray-600">
                    @foreach ($cardsByType as $type => $cards)
                        <span
                            class="px-2 py-0.5 rounded text-xs font-medium {{ $statColors[$type] ?? 'text-gray-600' }} bg-gray-100 dark:bg-gray-700">
                            {{ ucfirst($type) }}: {{ $cards->count() }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
