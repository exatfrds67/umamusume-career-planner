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
            return ['C', 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300'];
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
    <h2 id="char-overview-heading" class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">
        {{ $character->name }}
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Current Stats with Grade Badges --}}
        <details class="group md:open" open>
            <summary class="md:hidden mb-2 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-700 px-3 py-2 text-sm font-semibold text-neutral-900 dark:text-white">
                Current Stats
            </summary>
            <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3 flex items-center justify-between">
                Current Stats
                <span class="text-xs text-neutral-500">Soft Cap: 1200</span>
            </h3>
            @php $growthRates = $character->growth_rates ?? []; @endphp
            <div class="space-y-3">
                @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                    @php
                        $statValue = $character->current_stats[$stat] ?? 0;
                        $growthRate = (int) ($growthRates[$stat] ?? 0);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wide">
                                {{ $stat }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $growthRate > 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">
                                +{{ $growthRate }}%
                            </span>
                        </div>
                        <x-stat-bar :stat="$stat" :current="$statValue" :max="1200" :show-icon="false" :show-percentage="true" size="sm" />
                    </div>
                @endforeach
                <div class="pt-2 border-t border-neutral-200 dark:border-neutral-600">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Total</span>
                        <span class="text-sm font-bold text-neutral-900 dark:text-white">
                            {{ array_sum($character->current_stats ?? []) }}
                        </span>
                    </div>
                </div>
            </div>
            </div>
        </details>

        {{-- Facility Levels (Unity Cup) or Growth Rates (URA) --}}
        @if ($character->scenario_type === 'unity_cup')
            <details class="group md:open" open>
                <summary class="md:hidden mb-2 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-700 px-3 py-2 text-sm font-semibold text-neutral-900 dark:text-white">
                    Facility Levels
                </summary>
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3 flex items-center justify-between">
                    Facility Levels
                    <span class="text-xs text-neutral-500">1.0×-2.0×</span>
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
                                            class="w-2 h-2 rounded-full {{ $i <= $level ? 'bg-primary-500' : 'bg-neutral-300 dark:bg-neutral-600' }}">
                                        </div>
                                    @endfor
                                </div>
                                <span
                                    class="text-sm font-semibold text-neutral-900 dark:text-white">
                                    <span aria-hidden="true">{{ $multiplier }}</span>
                                    <span class="sr-only">Level {{ $level }}, {{ $multiplier }}</span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
            </details>
        @else
            <details class="group md:open" open>
                <summary class="md:hidden mb-2 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-700 px-3 py-2 text-sm font-semibold text-neutral-900 dark:text-white">
                    Growth Rates
                </summary>
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Growth Rates</h3>
                <div class="space-y-2">
                    @php $growthRates = $character->growth_rates ?? []; @endphp
                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                        @php $rate = $growthRates[$stat] ?? 0; @endphp
                        <div class="flex justify-between items-center">
                            <span
                                class="text-sm {{ $statColors[$stat] }} capitalize font-medium">{{ $stat }}</span>
                            <span
                                class="text-sm font-semibold {{ $rate > 0 ? 'text-green-600 dark:text-green-400' : 'text-neutral-900 dark:text-white' }}">
                                +{{ $rate }}%
                            </span>
                        </div>
                    @endforeach
                </div>
                </div>
            </details>
        @endif

        {{-- Support Cards Summary --}}
        <details class="group md:open" open>
            <summary class="md:hidden mb-2 cursor-pointer rounded-lg border border-neutral-200 dark:border-neutral-700 px-3 py-2 text-sm font-semibold text-neutral-900 dark:text-white">
                Support Cards
            </summary>
            <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3 flex items-center justify-between">
                Support Cards
                <a href="{{ route('characters.deck-builder', $character) }}" class="text-xs font-semibold text-primary-600 dark:text-primary-300 hover:underline">
                    Manage
                </a>
            </h3>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-300" aria-hidden="true" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-sm text-neutral-700 dark:text-neutral-300">
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
                        <span class="text-sm text-neutral-700 dark:text-neutral-300">
                            {{ $rainbowCount }} at friendship (80%+)
                        </span>
                    </div>
                @endif
                {{-- Card type distribution --}}
                <div class="flex flex-wrap gap-1 pt-2 border-t border-neutral-200 dark:border-neutral-600">
                    @foreach ($cardsByType as $type => $cards)
                        <span
                            class="px-2 py-0.5 rounded text-xs font-medium {{ $statColors[$type] ?? 'text-neutral-600' }} bg-neutral-100 dark:bg-neutral-700">
                            {{ ucfirst($type) }}: {{ $cards->count() }}
                        </span>
                    @endforeach
                </div>
            </div>
            </div>
        </details>
    </div>
</section>
