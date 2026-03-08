@props([
    'operations' => [],
    'showPagination' => true,
    'pagination' => null,
    'emptyMessage' => 'No operations found.',
])

@php
    $statusColors = [
        'pending' => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'cancelled' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    ];

    $typeIcons = [
        'import' => '📥',
        'export' => '📤',
        'migration' => '🔄',
        'backup' => '💾',
        'restore' => '♻️',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'operation-history']) }}>
    @if (empty($operations))
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($operations as $operation)
                <div
                    class="operation-item flex items-center justify-between p-4 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600 transition-colors">
                    <div class="flex items-center gap-4">
                        {{-- Operation Type Icon --}}
                        <span class="text-2xl" role="img"
                            aria-label="{{ $operation['operation_type'] ?? 'operation' }}">
                            {{ $typeIcons[$operation['operation_type'] ?? 'import'] ?? '📋' }}
                        </span>

                        {{-- Operation Details --}}
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-neutral-900 dark:text-white capitalize">
                                    {{ str_replace('_', ' ', $operation['operation_type'] ?? 'Operation') }}
                                </h4>
                                <span
                                    class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$operation['status'] ?? 'pending'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $operation['status'] ?? 'pending')) }}
                                </span>
                            </div>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $operation['message'] ?? 'No details available' }}
                            </p>
                            @if (!empty($operation['results']))
                                <div class="mt-1 flex gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                                    @if (isset($operation['results']['imported']))
                                        <span>{{ $operation['results']['imported'] }} imported</span>
                                    @endif
                                    @if (isset($operation['results']['exported']))
                                        <span>{{ $operation['results']['exported'] }} exported</span>
                                    @endif
                                    @if (isset($operation['results']['failed']))
                                        <span class="text-red-500">{{ $operation['results']['failed'] }} failed</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timestamp --}}
                    <div class="text-right">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            @if (isset($operation['completed_at']))
                                {{ \Carbon\Carbon::parse($operation['completed_at'])->diffForHumans() }}
                            @elseif(isset($operation['created_at']))
                                {{ \Carbon\Carbon::parse($operation['created_at'])->diffForHumans() }}
                            @endif
                        </p>
                        @if (isset($operation['operation_id']))
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">
                                {{ substr($operation['operation_id'], 0, 8) }}...
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($showPagination && $pagination && $pagination['total_pages'] > 1)
            <div class="mt-4 flex items-center justify-between">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Showing {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}
                    to
                    {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
                    of {{ $pagination['total'] }} results
                </p>
                <div class="flex gap-2">
                    <button type="button"
                        class="pagination-btn px-3 py-1 text-sm rounded border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        data-page="{{ $pagination['current_page'] - 1 }}"
                        {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}>
                        Previous
                    </button>
                    <button type="button"
                        class="pagination-btn px-3 py-1 text-sm rounded border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        data-page="{{ $pagination['current_page'] + 1 }}"
                        {{ !$pagination['has_more'] ? 'disabled' : '' }}>
                        Next
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
