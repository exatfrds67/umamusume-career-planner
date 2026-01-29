{{--
Component: FilterBadge
Purpose: Removable filter tag for active filters
Props:
  - label (string, required): Filter label text
  - onRemove (callback, optional): Alpine event to dispatch on remove
Usage:
  <x-filter-badge label="Grade: S" @remove="removeFilter('grade')" />
Accessibility: WCAG 2.2 AA compliant
--}}

@props([
    'label',
    'onRemove' => null,
])

<div class="inline-flex items-center gap-2 bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 rounded-full px-3 py-1 text-sm border border-primary-200 dark:border-primary-800">
    <span>{{ $label }}</span>
    <button
        type="button"
        @if($onRemove) @click="{{ $onRemove }}" @endif
        class="inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
        aria-label="Remove {{ $label }} filter"
    >
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>
</div>
