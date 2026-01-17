@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'helpText' => null,
    'label' => null,
    'ariaLabel' => null,
    'ariaDescribedby' => null,
])

@php
    $inputId = $id ?? $name;
    $errorId = $error ? "{$inputId}-error" : null;
    $helpId = $helpText ? "{$inputId}-help" : null;

    $describedBy = collect([$ariaDescribedby, $errorId, $helpId])
        ->filter()
        ->implode(' ');

    $inputClasses = 'form-input';
    if ($error) {
        $inputClasses .= ' border-error-500';
    }

    $ariaInvalid = $error ? 'true' : null;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-error-600" aria-label="required">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $inputId }}" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" class="{{ $inputClasses }}" @if ($required) required @endif
        @if ($disabled) disabled @endif @if ($readonly) readonly @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($ariaInvalid) aria-invalid="{{ $ariaInvalid }}" @endif
        {{ $attributes->except(['class', 'type', 'name', 'id', 'value', 'placeholder', 'required', 'disabled', 'readonly', 'error', 'helpText', 'label', 'ariaLabel', 'ariaDescribedby']) }}>

    @if ($error)
        <p id="{{ $errorId }}" class="form-error" role="alert">
            {{ $error }}
        </p>
    @endif

    @if ($helpText)
        <p id="{{ $helpId }}" class="form-help">
            {{ $helpText }}
        </p>
    @endif
</div>
