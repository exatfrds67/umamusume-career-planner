{{--
Component: Toggle
Purpose: On/off switch with accessibility
Props:
  - name (string, required): Input name attribute
  - label (string, required): Toggle label text
  - checked (bool, optional): Whether toggle is on
  - error (string, optional): Error message
  - hint (string, optional): Help text
  - disabled (bool, optional): Whether toggle is disabled
  - size (string, optional): Toggle size (sm|md|lg), default md
Usage:
  <x-form.toggle name="notifications" label="Enable notifications" :checked="$notificationsEnabled" />
Accessibility: WCAG 2.2 AA compliant, role="switch", 44px touch target
--}}

@props([
    'name',
    'label',
    'checked' => false,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'size' => 'md',
])

@php
    $toggleId = $name . '-toggle-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    $isChecked = old($name, $checked);
    
    $sizes = [
        'sm' => ['track' => 'w-8 h-4', 'thumb' => 'h-3 w-3', 'translate' => 'translate-x-4'],
        'md' => ['track' => 'w-11 h-6', 'thumb' => 'h-5 w-5', 'translate' => 'translate-x-5'],
        'lg' => ['track' => 'w-14 h-7', 'thumb' => 'h-6 w-6', 'translate' => 'translate-x-7'],
    ];
    
    $sizeConfig = $sizes[$size] ?? $sizes['md'];
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
@endphp

<div 
    {{ $attributes->only('class')->merge(['class' => 'form-group']) }}
    x-data="{ enabled: {{ $isChecked ? 'true' : 'false' }} }"
>
    <div class="flex items-center justify-between gap-4">
        {{-- Label and Description --}}
        <div class="flex flex-col">
            <label for="{{ $toggleId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                {{ $label }}
            </label>
            
            {{-- Hint Text --}}
            @if($hint && !$hasError)
                <p id="{{ $hintId }}" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ $hint }}
                </p>
            @endif
        </div>
        
        {{-- Toggle Switch --}}
        <button
            type="button"
            id="{{ $toggleId }}"
            role="switch"
            :aria-checked="enabled.toString()"
            @click="enabled = !enabled"
            @if($disabled) disabled aria-disabled="true" @endif
            @if($hasError) aria-invalid="true" @endif
            @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
            :class="enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
            class="{{ $sizeConfig['track'] }} relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }} {{ $hasError ? 'ring-2 ring-error-500' : '' }}"
        >
            <span class="sr-only">{{ $label }}</span>
            <span
                aria-hidden="true"
                :class="enabled ? '{{ $sizeConfig['translate'] }}' : 'translate-x-0'"
                class="{{ $sizeConfig['thumb'] }} pointer-events-none inline-block transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            ></span>
        </button>
        
        {{-- Hidden Input for Form Submission --}}
        <input 
            type="hidden" 
            name="{{ $name }}" 
            :value="enabled ? '1' : '0'"
        />
    </div>
    
    {{-- Error Message --}}
    @if($hasError)
        <p id="{{ $errorId }}" class="mt-1.5 text-sm text-error-600 dark:text-error-400 flex items-center gap-1" role="alert">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
