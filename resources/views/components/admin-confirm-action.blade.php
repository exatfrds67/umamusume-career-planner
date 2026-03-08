@props([
    'action' => '',
    'method' => 'POST',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'warning',
    'buttonClass' => '',
    'buttonLabel' => 'Submit',
    'requireTypedConfirmation' => false,
    'typedConfirmationWord' => 'CONFIRM',
    'hiddenInputs' => [],
])

@php
    $uniqueId = 'confirm-' . md5($action . $buttonLabel . uniqid('', true));

    $confirmButtonClasses = match($variant) {
        'danger' => 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white',
        'warning' => 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white',
        'info' => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white',
        default => 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white',
    };

    $iconClasses = match($variant) {
        'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
        'success' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        'info' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        default => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
    };

    $iconSvg = match($variant) {
        'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
    };
@endphp

<div x-data="{
    showConfirm: false,
    loading: false,
    @if($requireTypedConfirmation) typedWord: '', @endif
    get canConfirm() {
        @if($requireTypedConfirmation)
            return this.typedWord === '{{ $typedConfirmationWord }}';
        @else
            return true;
        @endif
    },
    submitForm() {
        if (!this.canConfirm) return;
        this.loading = true;
        this.$refs.form.submit();
    }
}" {{ $attributes->only('class') }}>
    {{-- Trigger Button --}}
    <button type="button"
        @click="showConfirm = true"
        :disabled="loading"
        class="{{ $buttonClass }}"
        x-bind:class="{ 'opacity-50 cursor-not-allowed': loading }">
        <template x-if="loading">
            <svg class="mr-1.5 inline-block h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </template>
        {{ $buttonLabel }}
    </button>

    {{-- Hidden Form --}}
    <form x-ref="form" method="{{ in_array(strtoupper($method), ['GET', 'POST']) ? strtoupper($method) : 'POST' }}" action="{{ $action }}" class="hidden">
        @csrf
        @if(!in_array(strtoupper($method), ['GET', 'POST']))
            @method($method)
        @endif
        @foreach($hiddenInputs as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>

    {{-- Modal Overlay --}}
    <template x-teleport="body">
        <div x-show="showConfirm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            @keydown.escape.window="showConfirm = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $uniqueId }}-title">

            {{-- Dialog --}}
            <div x-show="showConfirm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.away="showConfirm = false"
                class="relative mx-4 w-full max-w-md overflow-hidden rounded-lg bg-white shadow-xl dark:bg-neutral-800">

                <div class="p-6">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $iconSvg !!}
                            </svg>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1">
                            <h3 id="{{ $uniqueId }}-title" class="text-lg font-semibold text-neutral-900 dark:text-white">
                                {{ $title }}
                            </h3>
                            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $message }}
                            </p>

                            @if($requireTypedConfirmation)
                                <div class="mt-3">
                                    <label for="{{ $uniqueId }}-typed" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                        Type <span class="font-bold text-red-600 dark:text-red-400">{{ $typedConfirmationWord }}</span> to confirm:
                                    </label>
                                    <input type="text" id="{{ $uniqueId }}-typed"
                                        x-model="typedWord"
                                        autocomplete="off"
                                        class="mt-1 block w-full rounded-md border-neutral-300 text-sm shadow-xs focus:border-red-500 focus:ring-red-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                        placeholder="{{ $typedConfirmationWord }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 bg-neutral-50 px-6 py-4 dark:bg-neutral-900/50">
                    <button type="button"
                        @click="showConfirm = false"
                        class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:focus-visible:ring-offset-neutral-900">
                        {{ $cancelText }}
                    </button>
                    <button type="button"
                        @click="submitForm()"
                        :disabled="!canConfirm"
                        :class="!canConfirm ? 'opacity-50 cursor-not-allowed' : ''"
                        class="rounded-md px-4 py-2 text-sm font-medium focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 {{ $confirmButtonClasses }}">
                        {{ $confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
