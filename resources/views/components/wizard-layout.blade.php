{{--
Component: WizardLayout
Purpose: Multi-step form container with progress indicator
Props:
  - steps (array, required): Array of step labels
  - currentStep (int, required): Current step index (0-based)
  - title (string, optional): Wizard title
Usage:
  <x-wizard-layout :steps="['Info', 'Goals', 'Review']" :current-step="1">
      <x-slot:header>Character Creation</x-slot:header>
      <form>...</form>
  </x-wizard-layout>
Accessibility: WCAG 2.2 AA compliant, ARIA progress indicators, keyboard navigation
--}}

@props([
    'steps' => [],
    'currentStep' => 0,
    'title' => null,
])

@php
    $totalSteps = count($steps);
    $progressPercent = $totalSteps > 0 ? (($currentStep + 1) / $totalSteps) * 100 : 0;
@endphp

<div 
    {{ $attributes->merge([
        'class' => 'wizard-layout w-full max-w-4xl mx-auto',
    ]) }}
    role="region"
    aria-label="{{ $title ?? 'Multi-step form' }}"
>
    {{-- Wizard Header --}}
    @if($title || isset($header))
        <div class="wizard-header mb-6">
            @if(isset($header))
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $header }}</h2>
            @elseif($title)
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            @endif
        </div>
    @endif

    {{-- Progress Bar --}}
    <div class="wizard-progress mb-8" role="progressbar" aria-valuenow="{{ $currentStep + 1 }}" aria-valuemin="1" aria-valuemax="{{ $totalSteps }}" aria-label="Step {{ $currentStep + 1 }} of {{ $totalSteps }}">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Step {{ $currentStep + 1 }} of {{ $totalSteps }}
            </span>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ round($progressPercent) }}%
            </span>
        </div>
        <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div 
                class="h-full bg-primary-600 rounded-full transition-all duration-300 ease-out"
                style="width: {{ $progressPercent }}%"
            ></div>
        </div>
    </div>

    {{-- Step Indicators --}}
    @if(count($steps) > 0)
        <nav class="wizard-steps mb-8" aria-label="Wizard steps">
            <ol class="flex items-center justify-between">
                @foreach($steps as $index => $stepLabel)
                    @php
                        $isCompleted = $index < $currentStep;
                        $isCurrent = $index === $currentStep;
                        $isPending = $index > $currentStep;
                        
                        $stepClasses = match(true) {
                            $isCompleted => 'bg-primary-600 text-white',
                            $isCurrent => 'bg-primary-600 text-white ring-2 ring-primary-300 ring-offset-2',
                            default => 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                        };
                        
                        $labelClasses = match(true) {
                            $isCompleted => 'text-primary-600 dark:text-primary-400',
                            $isCurrent => 'text-primary-600 dark:text-primary-400 font-semibold',
                            default => 'text-gray-500 dark:text-gray-400',
                        };
                    @endphp
                    
                    <li class="flex flex-col items-center flex-1 {{ $index < count($steps) - 1 ? 'relative' : '' }}">
                        {{-- Step Circle --}}
                        <div 
                            class="flex items-center justify-center w-10 h-10 rounded-full {{ $stepClasses }} text-sm font-medium transition-all duration-200"
                            aria-current="{{ $isCurrent ? 'step' : 'false' }}"
                        >
                            @if($isCompleted)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="sr-only">Completed:</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        
                        {{-- Step Label --}}
                        <span class="mt-2 text-xs sm:text-sm {{ $labelClasses }} text-center max-w-[80px] truncate">
                            {{ $stepLabel }}
                        </span>
                        
                        {{-- Connector Line --}}
                        @if($index < count($steps) - 1)
                            <div class="absolute top-5 left-1/2 w-full h-0.5 {{ $isCompleted ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}" style="transform: translateX(50%); width: calc(100% - 2.5rem);"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    {{-- Main Content --}}
    <div class="wizard-content bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        {{ $slot }}
    </div>

    {{-- Footer Navigation (optional slot) --}}
    @if(isset($footer))
        <div class="wizard-footer mt-6 flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
