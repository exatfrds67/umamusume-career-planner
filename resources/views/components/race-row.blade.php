@props(['race'])

@php
    $badgeVariant = match ($race->grade) {
        'G1'     => 'grade-g1',
        'G2'     => 'grade-g2',
        'G3'     => 'grade-g3',
        'OP'     => 'grade-op',
        'Pre-OP' => 'grade-preop',
        default  => 'grade-debut',
    };
@endphp

<article class="group flex flex-col md:grid md:grid-cols-[4rem_6.5rem_1fr_auto_max-content] items-start md:items-center gap-2 md:gap-4 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors" aria-label="Race {{ $race->name_en }}">
    {{-- Column 1: Grade --}}
    <div class="shrink-0 pt-0.5 md:pt-0">
        <x-badge :variant="$badgeVariant" class="w-[52px] justify-center text-xs font-bold tabular-nums">
            {{ $race->grade }}
        </x-badge>
    </div>

    {{-- Column 2: Date (Desktop) --}}
    @if ($race->month_label)
    <div class="hidden md:block text-sm font-medium text-neutral-600 dark:text-neutral-400">
        {{ $race->month_label }}
    </div>
    @else
    <div class="hidden md:block"></div>
    @endif

    {{-- Column 3: Race Name & JP Name --}}
    <div class="flex flex-col min-w-0 w-full mt-1 md:mt-0">
        <div class="flex items-center gap-2">
            <a href="{{ route('races.show', $race->slug) }}"
                class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate block">
                <h3 class="text-[15px] leading-tight font-bold text-neutral-900 dark:text-neutral-100 truncate">
                    {{ $race->name_en }}
                </h3>
            </a>
            {{-- Mobile Date indicator --}}
            @if ($race->month_label)
            <span class="md:hidden shrink-0 text-[11px] font-medium text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded">
                {{ $race->month_label }}
            </span>
            @endif
        </div>

        @if ($race->name_jp || $race->is_ura_finale)
        <div class="text-[11px] text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5 mt-0.5 truncate">
            @if ($race->is_ura_finale)
            <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-0.5 shrink-0">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                URA Finale
            </span>
            @if($race->name_jp)<span class="text-neutral-300 dark:text-neutral-600 shrink-0">•</span>@endif
            @endif
            @if ($race->name_jp)
            <span class="truncate">{{ $race->name_jp }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- Column 4: Distance, Surface, Venue --}}
    <div class="flex items-center gap-2 mt-2 md:mt-0 text-sm text-neutral-700 dark:text-neutral-300 md:justify-end shrink-0 w-full md:w-auto">
        <span class="flex items-center gap-1 min-w-[3.5rem] {{ $race->surface === 'turf' ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-500' }}" title="{{ $race->surface === 'turf' ? 'Turf' : 'Dirt' }}">
            @if($race->surface === 'turf')
            <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500/20 border border-emerald-500/50 shadow-[inset_0_0_2px_rgba(16,185,129,0.3)] shrink-0" aria-hidden="true"></span>
            <span class="text-xs font-semibold uppercase tracking-wider">Turf</span>
            @else
            <span class="w-2.5 h-2.5 rounded-sm bg-amber-700/20 border border-amber-700/50 shadow-[inset_0_0_2px_rgba(180,83,9,0.3)] shrink-0" aria-hidden="true"></span>
            <span class="text-xs font-semibold uppercase tracking-wider">Dirt</span>
            @endif
        </span>
        
        <div class="h-3.5 w-px bg-neutral-300 dark:bg-neutral-600"></div>
        
        <span class="font-medium font-mono tabular-nums text-[13px]">{{ number_format($race->distance_meters) }}m</span>
        
        @if($race->distance_category)
        <span class="bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-widest hidden sm:inline-block">
            {{ str_replace('_', ' ', $race->distance_category) }}
        </span>
        @endif

        @if ($race->venue)
        <div class="h-3.5 w-px bg-neutral-300 dark:bg-neutral-600 hidden sm:block"></div>
        <span class="hidden sm:inline-block text-neutral-500 dark:text-neutral-400 text-xs">{{ $race->venue }}</span>
        @endif
    </div>

    {{-- Column 5: Fans --}}
    <div class="absolute right-4 top-3 md:relative md:top-0 md:right-0 flex items-center justify-end md:w-[6rem] shrink-0">
        @if($race->fan_requirement > 0)
        <span class="inline-flex items-center gap-1 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200/50 dark:border-amber-700/30 px-2 py-1.5 md:py-1 rounded-full whitespace-nowrap" title="Fan Requirement">
            <svg class="h-3 w-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            {{ number_format($race->fan_requirement) }}
        </span>
        @else
        <span class="text-[11px] text-neutral-400 dark:text-neutral-500 italic hidden md:inline-block">No req.</span>
        @endif
    </div>
</article>
