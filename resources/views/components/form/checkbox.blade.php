@props([
    'name' => '',
    'id' => null,
    'value' => '1',
    'checked' => false,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helpText' => null,
    'label' => null,
    'ariaLabel' => null,
    'ariaDescribedby' => null,
])

@php
    $checkboxId = $id ?? $name;
    $errorId = $error ? "{$checkboxId}-error" : null;
    $helpId = $helpText ? "{$checkboxId}-help" : null;

    $describedBy = collect([$ariaDescribedby, $errorId, $helpId])
        ->filter()
        ->implode(' ');

    $ariaInvalid = $error ? 'true' : null;
    $isChecked = old($name, $checked);
@endphp

<div class="form-group">
    <div class="flex items-start gap-2">
        <input type="checkbox" name="{{ $name }}" id="{{ $checkboxId }}" value="{{ $value }}"
            class="form-checkbox mt-1" @if ($isChecked) checked @endif
            @if ($required) required @endif @if ($disabled) disabled @endif
            @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($ariaInvalid) aria-invalid="{{ $ariaInvalid }}" @endif
            {{ $attributes->except(['class', 'name', 'id', 'value', 'checked', 'required', 'disabled', 'error', 'helpText', 'label', 'ariaLabel', 'ariaDescribedby']) }}>

        @if ($label || $slot->isNotEmpty())
            <label for="{{ $checkboxId }}" class="form-label mb-0 cursor-pointer">
                {{ $label ?? $slot }}
                @if ($required)
                    <span class="text-error-600" aria-label="required">*</span>
                @endif
            </label>
        @endif
    </div>

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
