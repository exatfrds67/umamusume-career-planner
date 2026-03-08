@props([
    'id' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => '••••••••',
    'autocomplete' => 'current-password',
    'required' => true,
    'value' => '',
    'showToggle' => true,
])

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
        {{ $label }}
    </label>
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="password" autocomplete="{{ $autocomplete }}"
            @if ($required) required @endif value="{{ $value }}"
            class="appearance-none relative block w-full px-3 py-2 {{ $showToggle ? 'pr-10' : '' }} border border-neutral-300 dark:border-neutral-600 placeholder-neutral-500 dark:placeholder-neutral-400 text-neutral-900 dark:text-white rounded-md focus:outline-hidden focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm bg-white dark:bg-neutral-800"
            placeholder="{{ $placeholder }}" {{ $attributes }}>

        @if ($showToggle)
            <button type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-md"
                onclick="togglePasswordVisibility('{{ $id }}')" aria-label="Toggle password visibility"
                title="Show/hide password">
                <svg id="{{ $id }}-eye-open"
                    class="h-5 w-5 text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300 transition-colors"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="{{ $id }}-eye-closed"
                    class="h-5 w-5 text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300 transition-colors hidden"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
            </button>
        @endif
    </div>
</div>

@once
    @vite(['resources/js/components/password-input.js'])
@endonce
