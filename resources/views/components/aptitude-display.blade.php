<div class="inline-flex {{ $layoutClasses() }}" {{ $attributes }}>
    @if($showLabel)
        <span class="text-sm text-neutral-600 dark:text-neutral-400">
            {{ $typeLabel() }}
        </span>
    @endif
    
    <div class="flex items-center gap-1">
        <span class="font-bold text-lg {{ $gradeColor() }}">
            {{ $grade }}
        </span>
        
        @if($bonus > 0)
            <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                +{{ $bonus }}%
            </span>
        @endif
    </div>
</div>