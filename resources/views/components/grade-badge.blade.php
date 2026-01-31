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

@once
    @push('styles')
        @vite(['resources/css/components/grade-badge.css'])
    @endpush
@endonce
