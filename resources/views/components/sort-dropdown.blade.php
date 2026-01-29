{{--
Component: SortDropdown
Purpose: Dropdown for sort criteria selection
Props:
  - name (string, required): Input name attribute
  - options (array, required): Sort options [{value: '', label: ''}]
  - value (mixed, optional): Currently selected sort
  - label (string, optional): Label text
  - icon (bool, optional): Show sort icon, default true
Usage:
  <x-sort-dropdown name="sort" :options="['name' => 'Name (A-Z)', 'date' => 'Newest First']" />
Accessibility: WCAG 2.2 AA compliant
--}}

@props([
    'name',
    'options' => [],
    'value' => null,
    'label' => 'Sort by',
    'icon' => true,
])

@php
    $sortId = $name . '-sort-' . uniqid();
    $selectedValue = old($name, $value);
    
    $normalizedOptions = collect($options)->map(function ($item, $key) {
        if (is_array($item) && isset($item['value'])) {
            return $item;
        }
        return ['value' => $key, 'label' => $item];
    })->values()->all();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $sortId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
            {{ $label }}
        </label>
    @endif
    
    {{-- Sort Select --}}
    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $sortId }}"
            value="{{ $selectedValue }}"
            class="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-150 cursor-pointer"
        >
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
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</div>
