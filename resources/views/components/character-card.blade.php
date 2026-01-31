@props(['character', 'showStats', 'showAptitudes', 'size', 'selectable'])

<div
    {{ $attributes->merge(['class' => 'character-card ' . $getSizeClasses() . ' rounded-xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 ' . ($selectable ? 'cursor-pointer' : '')]) }}>
    {{-- Character Portrait --}}
    <div
        class="relative aspect-3/4 overflow-hidden bg-linear-to-br from-neutral-200 to-neutral-300 dark:from-neutral-700 dark:to-neutral-800">
        @if ($getAvatarUrl())
            <img src="{{ $getAvatarUrl() }}" alt="{{ $getName() }}" class="w-full h-full object-cover" loading="lazy" />
        @else
            <div class="w-full h-full flex items-center justify-center text-neutral-400 dark:text-neutral-600">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        @endif

        {{-- Grade Badge Overlay --}}
        <div class="absolute top-3 right-3">
            <x-grade-badge :grade="$getGrade()" size="lg" />
        </div>

        {{-- Selectable Overlay --}}
        @if ($selectable)
            <div
                class="absolute inset-0 bg-primary-600/0 hover:bg-primary-600/20 transition-colors duration-200 flex items-center justify-center">
                <div class="opacity-0 hover:opacity-100 transition-opacity duration-200">
                    <div class="bg-white dark:bg-neutral-800 rounded-full p-3 shadow-lg">
                        <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Character Info --}}
    <div class="p-4 bg-white dark:bg-neutral-800">
        {{-- Name --}}
        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2 truncate">
            {{ $getName() }}
        </h3>

        {{-- Stats Display --}}
        @if ($showStats)
            <div class="space-y-2">
                @foreach ($getStats() as $stat => $value)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-600 dark:text-neutral-400 capitalize">{{ $stat }}</span>
                        <span
                            class="font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">{{ number_format($value) }}</span>
                    </div>
                @endforeach
                <div
                    class="pt-2 border-t border-neutral-200 dark:border-neutral-700 flex items-center justify-between text-sm font-bold">
                    <span class="text-neutral-700 dark:text-neutral-300">Total</span>
                    <span
                        class="text-primary-600 dark:text-primary-400 tabular-nums">{{ number_format($getTotalStats()) }}</span>
                </div>
            </div>
        @endif

        {{-- Aptitudes Display --}}
        @if ($showAptitudes && isset($character['aptitudes']))
            <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-700">
                <div class="flex flex-wrap gap-2">
                    @foreach ($character['aptitudes'] as $type => $grade)
                        <x-grade-badge :grade="$grade" size="sm" show-label :label="ucfirst($type)" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/character-card.css'])
    @endpush
@endonce
