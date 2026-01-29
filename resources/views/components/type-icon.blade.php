<div class="flex items-center gap-2">
    <span 
        class="inline-flex items-center justify-center {{ $sizeClasses() }} {{ $colorClasses() }}"
        title="{{ $label() }}"
        aria-label="{{ $label() }} stat"
    >
        {{ $iconSymbol() }}
    </span>
    
    @if ($showLabel)
        <span class="text-sm font-medium {{ $colorClasses() }}">
            {{ $label() }}
        </span>
    @endif
</div>