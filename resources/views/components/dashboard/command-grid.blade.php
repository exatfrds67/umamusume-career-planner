@props([
    'actions' => [],
])

@php
    $accentClasses = [
        'primary' => 'from-[#E879A0] to-[#7C3AED] text-white shadow-[0_18px_40px_rgba(124,58,237,0.18)]',
        'secondary' => 'from-[#0F766E] to-[#14B8A6] text-white shadow-[0_18px_40px_rgba(20,184,166,0.16)]',
        'success' => 'from-[#10B981] to-[#22C55E] text-white shadow-[0_18px_40px_rgba(16,185,129,0.16)]',
        'warning' => 'from-[#F59E0B] to-[#F97316] text-white shadow-[0_18px_40px_rgba(245,158,11,0.16)]',
        'neutral' => 'from-[#150D35] to-[#0A0620] text-white shadow-[0_18px_40px_rgba(10,6,32,0.24)]',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden']) }}>
    <div class="px-5 py-5 sm:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Command Grid</h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-300">
                    Quick routes for the next turn, the next race, and the next decision.
                </p>
            </div>

            <x-badge variant="neutral">{{ count($actions) }} actions</x-badge>
        </div>

        @if (empty($actions))
            <div class="mt-5 rounded-2xl border border-dashed border-neutral-300 bg-white/70 p-5 text-sm text-neutral-600 dark:border-neutral-700 dark:bg-neutral-800/60 dark:text-neutral-300">
                No quick actions are available yet.
            </div>
        @else
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3" role="list" aria-label="Dashboard command grid">
                @foreach ($actions as $action)
                    @php
                        $variant = $action['variant'] ?? 'primary';
                        $title = (string) ($action['title'] ?? 'Action');
                        $href = (string) ($action['href'] ?? '#');
                        $accent = $accentClasses[$variant] ?? $accentClasses['primary'];
                    @endphp

                    <a href="{{ $href }}"
                        class="group flex min-h-36 flex-col justify-between rounded-2xl border border-neutral-200 bg-white/85 p-4 transition hover:-translate-y-0.5 hover:border-[#C4B5FD] hover:shadow-[0_20px_50px_rgba(124,58,237,0.12)] dark:border-neutral-700 dark:bg-neutral-800/70 dark:hover:border-[#7C3AED]/55"
                        role="listitem"
                        aria-label="{{ $title }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-2">
                                <span class="inline-flex items-center rounded-full bg-[#F9F5FF] px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-[#7C3AED] dark:bg-white/10 dark:text-white/80">
                                    {{ $action['eyebrow'] ?? 'Quick Action' }}
                                </span>
                                <div>
                                    <p class="text-base font-bold text-neutral-900 transition group-hover:text-[#7C3AED] dark:text-white dark:group-hover:text-[#F9A8D4]">
                                        {{ $title }}
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-neutral-600 dark:text-neutral-300">
                                        {{ $action['description'] ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br {{ $accent }} text-xl">
                                {{ $action['symbol'] ?? '•' }}
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 text-xs font-semibold text-neutral-500 dark:text-neutral-400">
                            <span>{{ $action['hint'] ?? 'Open this panel' }}</span>
                            <span class="text-[#7C3AED] transition group-hover:text-[#E879A0] dark:text-[#C4B5FD]">Open</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
