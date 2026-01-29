<div class="inline-block {{ $sizeClasses() }} {{ $colorClasses() }} rounded-lg font-bold shadow-sm">
    @if($isRaceDay)
        <span class="flex items-center gap-1">
            🏁 {{ $label() }}
        </span>
    @else
        <span class="flex items-center gap-1">
            📅
            @if($showCountdown)
                {{ $label() }}
            @else
                Race upcoming
            @endif
        </span>
    @endif
</div>