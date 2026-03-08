<div class="w-full" {{ $attributes }}>
    @if($showLabel || $showValues)
        <div class="flex items-center justify-between mb-1 text-sm">
            @if($showLabel)
                <span class="font-medium text-neutral-700 dark:text-neutral-300">
                    {{ $percentage() }}%
                </span>
            @endif
            
            @if($showValues)
                <span class="text-neutral-600 dark:text-neutral-400 tabular-nums">
                    {{ number_format($current) }} / {{ number_format($max) }}
                </span>
            @endif
        </div>
    @endif
    
    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden {{ $sizeClasses() }}">
        <div 
            class="{{ $colorClasses() }} {{ $sizeClasses() }} rounded-full {{ $animated ? 'transition-all duration-500 ease-out' : '' }}"
            style="width: {{ $percentage() }}%"
            role="progressbar"
            aria-valuenow="{{ $current }}"
            aria-valuemin="0"
            aria-valuemax="{{ $max }}"
            aria-label="Progress: {{ $percentage() }}%"
        ></div>
    </div>
</div>