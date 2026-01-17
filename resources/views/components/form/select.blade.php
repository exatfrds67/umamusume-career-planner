@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helpText' => null,
    'label' => null,
    'ariaLabel' => null,
    'ariaDescribedby' => null,
])

@php
    $selectId = $id ?? $name;
    $errorId = $error ? "{$selectId}-error" : null;
    $helpId = $helpText ? "{$selectId}-help" : null;

    $describedBy = collect([$ariaDescribedby, $errorId, $helpId])
        ->filter()
        ->implode(' ');

    $selectClasses = 'form-select';
    if ($error) {
        $selectClasses .= ' border-error-500';
    }

    $ariaInvalid = $error ? 'true' : null;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $selectId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-error-600" aria-label="required">*</span>
            @endif
        </label>
    @endif

    <select name="{{ $name }}" id="{{ $selectId }}" class="{{ $selectClasses }}"
        @if ($required) required @endif @if ($disabled) disabled @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($ariaInvalid) aria-invalid="{{ $ariaInvalid }}" @endif
        {{ $attributes->except(['class', 'name', 'id', 'value', 'options', 'placeholder', 'required', 'disabled', 'error', 'helpText', 'label', 'ariaLabel', 'ariaDescribedby']) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @if (old($name, $value) == $optionValue) selected @endif>
                {{ $optionLabel }}
            </option>
        @endforeach

        {{ $slot }}
    </select>

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
