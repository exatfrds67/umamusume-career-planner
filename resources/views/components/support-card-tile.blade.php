@props(['card'])

@php
    $allStatsZero = ($card->speed_bonus + $card->stamina_bonus + $card->power_bonus + $card->guts_bonus + $card->wit_bonus) === 0;
    $trainingBonuses = collect([
        'Train' => $card->training_effect_bonus,
        'Event' => $card->event_effect_bonus,
        'Recover' => $card->event_recovery_bonus,
        'Friend' => $card->friendship_bonus,
    ])->filter(fn($v) => $v > 0);
    $typeLabels = [
        'speed' => 'Speed',
        'stamina' => 'Stamina',
        'power' => 'Power',
        'guts' => 'Guts',
        'wit' => 'Wit',
        'friend' => 'Friend',
    ];
    $typeColors = [
        'speed' => 'blue',
        'stamina' => 'green',
        'power' => 'red',
        'guts' => 'orange',
        'wit' => 'purple',
        'friend' => 'pink',
    ];
    $primaryColor = $typeColors[$card->card_type] ?? 'gray';
@endphp

<a href="{{ route('support-cards.show', $card) }}"
    class="card bg-white dark:bg-neutral-800 rounded-lg shadow-xs hover:shadow-md transition-all border border-neutral-200 dark:border-neutral-700 overflow-hidden group focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 flex flex-col"
    aria-label="{{ $card->name }} - {{ $card->rarity }} {{ ucfirst($card->card_type) }} card{{ $card->meta_tier ? ', Tier ' . $card->meta_tier : '' }}">
    {{-- Card Image/Placeholder --}}
    <div
        class="relative aspect-video bg-linear-to-br from-{{ $primaryColor }}-400 to-{{ $primaryColor }}-600">
        @if ($card->artwork_url)
            <img src="{{ $card->artwork_url }}" alt="" loading="lazy" decoding="async"
                width="400" height="225"
                class="w-full h-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-20 h-20 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-2 left-2 flex gap-2">
            <x-support-card-rarity-badge :rarity="$card->rarity" />
            <x-support-card-tier-badge :tier="$card->meta_tier" />
        </div>

        @if ($card->is_limited)
            <div class="absolute top-2 right-2">
                <span
                    class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                    Limited
                </span>
            </div>
        @endif
    </div>

    {{-- Card Info --}}
    <div class="p-4 flex flex-col grow">
        <div class="mb-2">
            <h3
                class="text-sm font-semibold text-neutral-900 dark:text-white line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $card->name }}
            </h3>
            @if ($card->character_name)
                <p class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-1">
                    {{ $card->character_name }}
                </p>
            @endif
        </div>

        {{-- Type Badge + Tier inline --}}
        <div class="flex items-center gap-2 mb-3">
            <x-support-card-type-badge :type="$card->card_type" />
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
                Tier {{ $card->meta_tier }}
            </span>
        </div>

        {{-- Stat Bonuses --}}
        @if (! $allStatsZero)
            <div class="grid grid-cols-5 gap-1 text-xs mb-2" role="list" aria-label="Stat bonuses">
                @foreach (['speed' => 'Spd', 'stamina' => 'Sta', 'power' => 'Pow', 'guts' => 'Gut', 'wit' => 'Wit'] as $stat => $abbr)
                    @php $val = $card->{$stat . '_bonus'}; @endphp
                    <div class="text-center rounded py-1 {{ $val > 0 ? 'bg-primary-50 dark:bg-primary-900/30' : 'bg-neutral-50 dark:bg-neutral-700/50' }}" role="listitem">
                        <div class="text-[10px] text-neutral-500 dark:text-neutral-400 leading-none mb-0.5">{{ $abbr }}</div>
                        <div class="font-semibold leading-none {{ $val > 0 ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-400 dark:text-neutral-500' }}">
                            {{ $val > 0 ? '+' . $val : '0' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Training type emphasis when no stat data --}}
            <div class="flex items-center gap-1.5 text-xs mb-2 px-2 py-1.5 rounded bg-{{ $primaryColor }}-50 dark:bg-{{ $primaryColor }}-900/20 text-{{ $primaryColor }}-700 dark:text-{{ $primaryColor }}-400">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="font-medium">{{ $typeLabels[$card->card_type] ?? ucfirst($card->card_type) }} Training</span>
            </div>
        @endif

        {{-- Training Bonuses (compact row, only if any > 0) --}}
        @if ($trainingBonuses->isNotEmpty())
            <div class="flex flex-wrap gap-1 text-[10px] mb-2">
                @foreach ($trainingBonuses as $label => $val)
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400">
                        {{ $label }} +{{ $val }}{{ in_array($label, ['Train', 'Event', 'Recover']) ? '%' : '' }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Usage Rate & Win Rate (if available) --}}
        @if ($card->usage_rate || $card->win_rate_contribution)
            <div class="flex gap-2 text-[10px] mb-2">
                @if ($card->usage_rate)
                    <span class="text-neutral-500 dark:text-neutral-400">
                        Use: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ number_format($card->usage_rate, 1) }}%</span>
                    </span>
                @endif
                @if ($card->win_rate_contribution)
                    <span class="text-neutral-500 dark:text-neutral-400">
                        Win: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ number_format($card->win_rate_contribution, 1) }}%</span>
                    </span>
                @endif
            </div>
        @endif

        {{-- Skill Hints (first 3 as compact pills) --}}
        @if (!empty($card->skill_hints_provided) && is_array($card->skill_hints_provided) && count($card->skill_hints_provided) > 0)
            <div class="flex flex-wrap gap-1 text-[10px] mb-2">
                @foreach (array_slice($card->skill_hints_provided, 0, 3) as $skill)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium">
                        {{ $skill }}
                    </span>
                @endforeach
                @if (count($card->skill_hints_provided) > 3)
                    <span class="text-neutral-400 dark:text-neutral-500">+{{ count($card->skill_hints_provided) - 3 }}</span>
                @endif
            </div>
        @endif

        {{-- Unique Effects Preview (first effect only) --}}
        @if (!empty($card->unique_effects) && is_array($card->unique_effects) && count($card->unique_effects) > 0)
            <p class="text-[10px] text-blue-600 dark:text-blue-400 line-clamp-1 italic mb-2">
                {{ $card->unique_effects[0] }}
            </p>
        @endif

        {{-- Spacer to push footer badges to bottom --}}
        <div class="grow"></div>

        {{-- Always-visible status bar --}}
        <div class="flex flex-wrap items-center gap-1.5 pt-2 mt-auto border-t border-neutral-100 dark:border-neutral-700/50">
            {{-- Server Availability --}}
            <span class="inline-flex items-center gap-0.5 text-[10px] px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ ucfirst($card->server_availability) }}
            </span>
            {{-- Active / Inactive --}}
            @if ($card->is_active)
                <span class="inline-flex items-center gap-0.5 text-[10px] px-1.5 py-0.5 rounded bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-0.5 text-[10px] px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500" aria-hidden="true"></span>
                    Inactive
                </span>
            @endif

        </div>
    </div>
</a>
