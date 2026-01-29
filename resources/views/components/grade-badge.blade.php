@props(['grade', 'size', 'showLabel', 'label'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    {{-- Grade Badge Circle --}}
    <div class="grade-badge {{ $getSizeClasses() }} {{ $getGradeColor() }} flex items-center justify-center rounded-full font-bold text-white shadow-md transition-transform hover:scale-110"
        title="{{ $getGradeDescription() }}">
        {{ $grade }}
    </div>

    {{-- Optional Label --}}
    @if ($showLabel)
        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ $label ?? $getGradeDescription() }}
        </span>
    @endif
</div>

<style>
    /* Grade Badge Styles (Verified - S is maximum, no SS) */
    .grade-s {
        background: linear-gradient(135deg, #a855f7, #9333ea);
        box-shadow: 0 4px 6px -1px rgba(168, 85, 247, 0.3);
    }

    .grade-a {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }

    .grade-b {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.3);
    }

    .grade-c {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        box-shadow: 0 4px 6px -1px rgba(20, 184, 166, 0.3);
    }

    .grade-d {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        box-shadow: 0 4px 6px -1px rgba(107, 114, 128, 0.3);
    }

    .grade-e {
        background: linear-gradient(135deg, #f97316, #ea580c);
        box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.3);
    }

    .grade-f {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
    }

    .grade-g {
        background: linear-gradient(135deg, #525252, #404040);
        box-shadow: 0 4px 6px -1px rgba(82, 82, 82, 0.3);
    }

    /* Dark mode adjustments */
    .dark .grade-badge {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }
</style>
