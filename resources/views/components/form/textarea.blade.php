@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
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
    $textareaId = $id ?? $name;
    $errorId = $error ? "{$textareaId}-error" : null;
    $helpId = $helpText ? "{$textareaId}-help" : null;

    $describedBy = collect([$ariaDescribedby, $errorId, $helpId])
        ->filter()
        ->implode(' ');

    $textareaClasses = 'form-input';
    if ($error) {
        $textareaClasses .= ' border-error-500';
    }

    $ariaInvalid = $error ? 'true' : null;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $textareaId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-error-600" aria-label="required">*</span>
            @endif
        </label>
    @endif

    <textarea name="{{ $name }}" id="{{ $textareaId }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        class="{{ $textareaClasses }}" @if ($required) required @endif
        @if ($disabled) disabled @endif @if ($readonly) readonly @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($ariaInvalid) aria-invalid="{{ $ariaInvalid }}" @endif
        {{ $attributes->except(['class', 'name', 'id', 'value', 'placeholder', 'rows', 'required', 'disabled', 'readonly', 'error', 'helpText', 'label', 'ariaLabel', 'ariaDescribedby']) }}>{{ old($name, $value) }}</textarea>

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
