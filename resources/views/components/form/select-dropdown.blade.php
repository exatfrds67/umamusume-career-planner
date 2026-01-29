{{--
Component: SelectDropdown
Purpose: Dropdown select with keyboard navigation and accessibility
Props:
  - name (string, required): Select name attribute
  - label (string, optional): Label text
  - options (array, required): Array of options [{value: '', label: ''}] or associative [value => label]
  - value (mixed, optional): Selected value
  - placeholder (string, optional): Placeholder option text
  - error (string, optional): Error message
  - hint (string, optional): Help text
  - required (bool, optional): Whether field is required
  - disabled (bool, optional): Whether field is disabled
Usage:
  <x-form.select-dropdown name="grade" label="Grade" :options="['S' => 'S Rank', 'A' => 'A Rank']" />
Accessibility: WCAG 2.2 AA compliant, keyboard navigation, screen reader friendly
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Select an option',
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $selectId = $name . '-select-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    $selectedValue = old($name, $value);
    
    $selectClasses = 'block w-full rounded-lg border px-4 py-2.5 text-sm transition-colors duration-150 ' .
        'appearance-none bg-no-repeat bg-right pr-10 cursor-pointer ' .
        'focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-error-500 text-error-900 focus:border-error-500 focus:ring-error-500/20 bg-error-50 dark:bg-error-900/10 dark:text-error-400 dark:border-error-500' 
            : 'border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-gray-800'
        ) .
        ($disabled ? ' opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-700' : '');
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
    
    // Normalize options to [{value, label}] format
    $normalizedOptions = collect($options)->map(function ($item, $key) {
        if (is_array($item) && isset($item['value'])) {
            return $item;
        }
        return ['value' => $key, 'label' => $item];
    })->values()->all();
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'form-group']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-error-500 ml-0.5" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif
    
    {{-- Select Wrapper --}}
    <div class="relative">
        <select
            {{ $attributes->except('class') }}
            name="{{ $name }}"
            id="{{ $selectId }}"
            @if($required) required aria-required="true" @endif
            @if($disabled) disabled aria-disabled="true" @endif
            @if($hasError) aria-invalid="true" @endif
            @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
            class="{{ $selectClasses }}"
        >
            @if($placeholder)
                <option value="" disabled {{ $selectedValue === null || $selectedValue === '' ? 'selected' : '' }}>
                    {{ $placeholder }}
                </option>
            @endif
            
            @foreach($normalizedOptions as $option)
                <option 
                    value="{{ $option['value'] }}" 
                    {{ (string)$selectedValue === (string)$option['value'] ? 'selected' : '' }}
                >
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
        
        {{-- Dropdown Icon --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
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
    
    {{-- Hint Text --}}
    @if($hint && !$hasError)
        <p id="{{ $hintId }}" class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif
</div>
