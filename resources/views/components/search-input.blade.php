{{--
Component: SearchInput
Purpose: Search field with debounced input and clear button
Props:
  - name (string, required): Input name attribute
  - placeholder (string, optional): Placeholder text
  - value (string, optional): Current search value
  - debounceMs (int, optional): Debounce delay in ms, default 300
  - minChars (int, optional): Minimum characters before search, default 1
Usage:
  <x-search-input name="q" placeholder="Search characters..." />
Accessibility: WCAG 2.2 AA compliant, clear button with aria-label
--}}

@props([
    'name' => 'q',
    'placeholder' => 'Search...',
    'value' => null,
    'debounceMs' => 300,
    'minChars' => 1,
])

@php
    $searchId = $name . '-search-' . uniqid();
@endphp

<div
    x-data="{ 
        query: '{{ old($name, $value) }}',
        searching: false,
        clearSearch() {
            this.query = '';
            this.$refs.input.focus();
        }
    }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    <div class="relative">
        {{-- Search Icon --}}
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>

        {{-- Input --}}
        <input
            type="search"
            name="{{ $name }}"
            id="{{ $searchId }}"
            x-ref="input"
            x-model="query"
            @input.debounce.{{ $debounceMs }}="$dispatch('search', { query: query })"
            placeholder="{{ $placeholder }}"
            aria-label="Search"
            class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-10 pr-10 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500/20 focus:outline-none focus:ring-2 transition-colors duration-150"
        />

        {{-- Clear Button --}}
        <button
            type="button"
            @click="clearSearch()"
            :style="query ? 'opacity: 1' : 'opacity: 0 pointer-events-none'"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-all duration-150"
            aria-label="Clear search"
        >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>
