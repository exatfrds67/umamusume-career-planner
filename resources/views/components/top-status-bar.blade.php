@props([
    'currentTurn' => null,
    'maxTurns' => null,
    'spAvailable' => null,
    'storageMode' => null,
])

@php
    $turnValue = $currentTurn !== null ? (string) $currentTurn : '—';
    $turnMax = $maxTurns !== null ? (string) $maxTurns : '—';
    $spValue = $spAvailable !== null ? number_format((int) $spAvailable) : '—';
    $modeValue = $storageMode ? ucfirst((string) $storageMode) : '—';
    $modeVariant = match (strtolower((string) $storageMode)) {
        'local' => 'warning',
        'account' => 'primary',
        default => 'secondary',
    };
@endphp

<div id="topStatusBar" {{ $attributes->merge(['class' => 'w-full border-b border-gray-200/80 dark:border-gray-700/80 bg-white/70 dark:bg-gray-800/70 backdrop-blur-md']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2 text-xs sm:text-sm">
        <div class="flex items-center gap-3">
            <span class="text-gray-500 dark:text-gray-400">Turn</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $turnValue }}</span>
            <span class="text-gray-500 dark:text-gray-400">/</span>
            <span class="text-gray-600 dark:text-gray-300">{{ $turnMax }}</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-gray-500 dark:text-gray-400">SP</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $spValue }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-gray-500 dark:text-gray-400">Storage</span>
            <x-badge :variant="$modeVariant">{{ $modeValue }}</x-badge>
        </div>
    </div>
</div>
