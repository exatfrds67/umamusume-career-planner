{{--
Component: TextInput
Purpose: Text input field with validation states and accessibility
Props:
  - name (string, required): Input name attribute
  - label (string, optional): Label text
  - type (string, optional): Input type (text|email|password|number|tel|url), default text
  - value (string, optional): Input value
  - placeholder (string, optional): Placeholder text
  - error (string, optional): Error message
  - hint (string, optional): Help text
  - required (bool, optional): Whether field is required
  - disabled (bool, optional): Whether field is disabled
  - readonly (bool, optional): Whether field is readonly
Usage:
  <x-form.text-input name="email" label="Email" type="email" :error="$errors->first('email')" />
Accessibility: WCAG 2.2 AA compliant, aria-describedby for errors/hints, visible labels
--}}

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
    $inputId = $name . '-input-' . uniqid();
    $errorId = $name . '-error';
    $hintId = $name . '-hint';
    
    $hasError = !empty($error);
    
    $inputClasses = 'block w-full rounded-lg border px-4 py-2.5 text-sm transition-colors duration-150 ' .
        'focus:outline-hidden focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-error-500 text-error-900 placeholder-error-400 focus:border-error-500 focus:ring-error-500/20 bg-error-50 dark:bg-error-900/10 dark:text-error-400 dark:border-error-500' 
            : 'border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-neutral-800'
        ) .
        ($disabled ? ' opacity-50 cursor-not-allowed bg-neutral-100 dark:bg-neutral-700' : '') .
        ($readonly ? ' bg-neutral-50 dark:bg-neutral-700/50' : '');
    
    $ariaDescribedBy = collect([
        $hasError ? $errorId : null,
        $hint ? $hintId : null,
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'form-group']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-error-500 ml-0.5" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif
    
    {{-- Input --}}
    <input
        {{ $attributes->except('class') }}
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required aria-required="true" @endif
        @if($disabled) disabled aria-disabled="true" @endif
        @if($readonly) readonly @endif
        @if($hasError) aria-invalid="true" @endif
        @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
        class="{{ $inputClasses }}"
    />
    
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
        <p id="{{ $hintId }}" class="mt-1.5 text-sm text-neutral-500 dark:text-neutral-400">
            {{ $hint }}
        </p>
    @endif
</div>
