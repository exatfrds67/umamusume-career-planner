<div class="inline-block {{ $sizeClasses() }} {{ $colorClasses() }} rounded-lg font-bold shadow-xs">
    @if($isRaceDay)
        <span class="flex items-center gap-1">
            <span aria-hidden="true">🏁</span> {{ $label() }}
        </span>
    @else
        <span class="flex items-center gap-1">
            <span aria-hidden="true">📅</span>
            @if($showCountdown)
                {{ $label() }}
            @else
                Race upcoming
            @endif
        </span>
    @endif
</div>