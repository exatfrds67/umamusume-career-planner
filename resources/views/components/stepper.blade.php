@props([
    'steps' => [],          // Array of step labels: ['Character', 'Goals', 'Skills']
    'current' => 0,         // Current step index (0-based)
    'completed' => [],      // Array of completed step indices
    'variant' => 'default', // 'default' | 'compact' | 'numbered'
])

@php
    $stepCount = count($steps);
    $isCompact = $variant === 'compact';
    $isNumbered = $variant === 'numbered';
    
    $containerClasses = "flex items-center justify-between " . ($isCompact ? 'gap-1' : 'gap-4');
@endphp

<nav {{ $attributes->merge(['class' => $containerClasses, 'aria-label' => 'Progress']) }}>
    @foreach($steps as $index => $label)
        @php
            $isCurrent = $index === $current;
            $isCompleted = in_array($index, $completed) || $index < $current;
            $isPending = $index > $current;
            
            // Step indicator classes
            $indicatorClasses = "flex items-center justify-center rounded-full transition-all duration-300 ";
            $indicatorClasses .= $isCompact ? 'w-8 h-8 ' : 'w-10 h-10 ';
            
            if ($isCurrent) {
                $indicatorClasses .= 'bg-blue-600 dark:bg-blue-500 text-white ring-4 ring-blue-100 dark:ring-blue-900/50';
            } elseif ($isCompleted) {
                $indicatorClasses .= 'bg-green-600 dark:bg-green-500 text-white';
            } else {
                $indicatorClasses .= 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400';
            }
            
            // Label classes
            $labelClasses = "text-sm font-medium transition-colors duration-300 ";
            if ($isCurrent) {
                $labelClasses .= 'text-blue-600 dark:text-blue-400';
            } elseif ($isCompleted) {
                $labelClasses .= 'text-green-600 dark:text-green-400';
            } else {
                $labelClasses .= 'text-neutral-500 dark:text-neutral-400';
            }
            
            // Connector line classes
            $connectorClasses = "flex-1 h-0.5 mx-2 transition-colors duration-300 ";
            if ($index < $current) {
                $connectorClasses .= 'bg-green-600 dark:bg-green-500';
            } else {
                $connectorClasses .= 'bg-neutral-200 dark:bg-neutral-700';
            }
        @endphp
        
        <div class="flex flex-col items-center {{ $isCompact ? 'gap-1' : 'gap-2' }}" role="listitem">
            {{-- Step Indicator --}}
            <div class="{{ $indicatorClasses }}" 
                 aria-current="{{ $isCurrent ? 'step' : 'false' }}"
                 aria-label="Step {{ $index + 1 }}: {{ $label }} {{ $isCompleted ? '(Completed)' : ($isCurrent ? '(Current)' : '(Pending)') }}">
                
                @if($isCompleted && !$isNumbered)
                    {{-- Checkmark icon for completed steps --}}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    {{-- Step number --}}
                    <span class="{{ $isCompact ? 'text-xs' : 'text-sm' }} font-semibold">
                        {{ $index + 1 }}
                    </span>
                @endif
            </div>
            
            {{-- Step Label (hidden on compact variant for mobile) --}}
            @if(!$isCompact || $isCurrent)
                <span class="{{ $labelClasses }} {{ $isCompact ? 'hidden sm:block' : '' }}">
                    {{ $label }}
                </span>
            @endif
        </div>
        
        {{-- Connector Line (not after last step) --}}
        @if($index < $stepCount - 1)
            <div class="{{ $connectorClasses }}" aria-hidden="true"></div>
        @endif
    @endforeach
</nav>
