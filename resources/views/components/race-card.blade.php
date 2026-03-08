@props(['race', 'readiness', 'winProb', 'isEntered', 'clickable', 'size'])

<div {{ $attributes->merge([
    'class' =>
        'relative rounded-lg border-2 transition-all duration-200 ' .
        $getSizeClasses() .
        ' ' .
        ($isEntered
            ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-950'
            : 'border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800') .
        ' ' .
        ($clickable ? 'cursor-pointer hover:shadow-lg hover:scale-[1.02]' : ''),
]) }}
    role="{{ $clickable ? 'button' : 'article' }}" tabindex="{{ $clickable ? '0' : '-1' }}">
    {{-- Race Grade Badge --}}
    <div
        class="absolute -top-3 -right-3 px-3 py-1 rounded-full {{ $getGradeColorClasses() }} font-bold text-sm shadow-lg"
        aria-label="Grade {{ $race['grade'] ?? 'OP' }}">
        {{ $race['grade'] ?? 'OP' }}
    </div>

    {{-- Entered Badge --}}
    @if ($isEntered)
        <div
            class="absolute -top-3 -left-3 px-3 py-1 rounded-full bg-blue-500 text-white font-bold text-xs shadow-lg flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            Entered
        </div>
    @endif

    {{-- Race Name --}}
    <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2 pr-12">
        {{ $race['name'] ?? 'Race Name' }}
    </h3>

    {{-- Race Details --}}
    <div class="grid grid-cols-2 gap-2 mb-3">
        {{-- Distance --}}
        <div class="flex items-center gap-1.5 text-sm text-neutral-600 dark:text-neutral-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span>{{ $race['distance'] ?? '2000' }}m</span>
        </div>

        {{-- Track Type --}}
        <div class="flex items-center gap-1.5 text-sm text-neutral-600 dark:text-neutral-400">
            <span>{{ $getTrackIcon() }}</span>
            <span class="capitalize">{{ $race['track'] ?? 'Turf' }}</span>
        </div>

        {{-- Running Style --}}
        @if (isset($race['style']))
            <div class="flex items-center gap-1.5 text-sm text-neutral-600 dark:text-neutral-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                        clip-rule="evenodd" />
                </svg>
                <span class="capitalize">{{ $race['style'] }}</span>
            </div>
        @endif

        {{-- Turn --}}
        @if (isset($race['turn']))
            <div class="flex items-center gap-1.5 text-sm text-neutral-600 dark:text-neutral-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd" />
                </svg>
                <span>Turn {{ $race['turn'] }}</span>
            </div>
        @endif
    </div>

    {{-- Readiness & Win Probability --}}
    @if ($readiness !== null || $winProb !== null)
        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3 mt-3 space-y-2">
            {{-- Readiness --}}
            @if ($readiness !== null)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Readiness</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300 {{ $readiness >= 80 ? 'bg-green-500' : ($readiness >= 60 ? 'bg-amber-500' : 'bg-red-500') }}"
                                style="width: {{ $readiness }}%"
                                role="progressbar"
                                aria-valuenow="{{ $readiness }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-label="Readiness: {{ $readiness }}%"></div>
                        </div>
                        <span class="text-sm font-bold {{ $getReadinessColorClasses() }} min-w-15 text-right">
                            {{ $readiness }}%
                        </span>
                    </div>
                </div>
            @endif

            {{-- Win Probability --}}
            @if ($winProb !== null)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Win Chance</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                            <div class="h-full bg-linear-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-300"
                                style="width: {{ $winProb }}%"
                                role="progressbar"
                                aria-valuenow="{{ number_format($winProb, 1) }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-label="Win chance: {{ number_format($winProb, 1) }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400 min-w-15 text-right">
                            {{ number_format($winProb, 1) }}%
                        </span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Weather/Conditions --}}
    @if (isset($race['weather']) || isset($race['condition']))
        <div class="mt-3 flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            @if (isset($race['weather']))
                <span class="flex items-center gap-1">
                    <span aria-hidden="true">{{ match ($race['weather']) {
                        'sunny' => '☀️',
                        'cloudy' => '☁️',
                        'rainy' => '🌧️',
                        'snowy' => '❄️',
                        default => '🌤️',
                    } }}</span>
                    {{ ucfirst($race['weather']) }}
                </span>
            @endif

            @if (isset($race['condition']))
                <span class="flex items-center gap-1">
                    •
                    {{ ucfirst($race['condition']) }} Track
                </span>
            @endif
        </div>
    @endif
</div>
