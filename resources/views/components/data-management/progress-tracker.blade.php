@props([
    'operationId' => null,
    'operationType' => 'operation',
    'status' => 'pending',
    'progress' => 0,
    'message' => '',
    'showDetails' => true,
])

@php
    $statusColors = [
        'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'cancelled' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    ];

    $statusIcons = [
        'pending' =>
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'in_progress' =>
            '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>',
        'completed' =>
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        'failed' =>
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        'cancelled' =>
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
    ];

    $progressBarColor = match ($status) {
        'completed' => 'bg-green-500',
        'failed' => 'bg-red-500',
        'cancelled' => 'bg-yellow-500',
        default => 'bg-blue-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'progress-tracker rounded-lg border border-gray-200 dark:border-gray-700 p-4']) }}
    data-operation-id="{{ $operationId }}" data-status="{{ $status }}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-full {{ $statusColors[$status] ?? $statusColors['pending'] }}">
                {!! $statusIcons[$status] ?? $statusIcons['pending'] !!}
            </span>
            <div>
                <h4 class="font-medium text-gray-900 dark:text-white capitalize">
                    {{ str_replace('_', ' ', $operationType) }}
                </h4>
                @if ($message)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
                @endif
            </div>
        </div>
        <span
            class="px-2.5 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$status] ?? $statusColors['pending'] }}">
            {{ ucfirst(str_replace('_', ' ', $status)) }}
        </span>
    </div>

    {{-- Progress Bar --}}
    @if ($status === 'in_progress' || $progress > 0)
        <div class="mb-3">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                <span>Progress</span>
                <span class="progress-percentage">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div class="progress-bar {{ $progressBarColor }} h-2.5 rounded-full transition-all duration-300"
                    style="width: {{ $progress }}%"></div>
            </div>
        </div>
    @endif

    {{-- Details Slot --}}
    @if ($showDetails && $slot->isNotEmpty())
        <div class="progress-details mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
            {{ $slot }}
        </div>
    @endif
</div>
