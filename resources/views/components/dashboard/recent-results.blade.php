@props([
    'results' => [],
])

@php
    $iconConfig = [
        'training' => [
            'icon' =>
                '<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />',
            'bgColor' => 'bg-success-500',
        ],
        'race' => [
            'icon' =>
                '<path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.061l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z" />',
            'bgColor' => 'bg-primary-500',
        ],
        'skill' => [
            'icon' =>
                '<path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" />',
            'bgColor' => 'bg-secondary-500',
        ],
        'info' => [
            'icon' =>
                '<path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />',
            'bgColor' => 'bg-gray-400',
        ],
    ];

    $colorConfig = [
        'success' => 'bg-success-500',
        'primary' => 'bg-primary-500',
        'secondary' => 'bg-secondary-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
        'gray' => 'bg-gray-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow']) }}>
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
            Recent Results
        </h3>

        @if (empty($results))
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                No recent activity. Start training to see your progress here.
            </p>
        @else
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach ($results as $index => $result)
                        @php
                            $type = $result['type'] ?? 'info';
                            $icon = $iconConfig[$type] ?? $iconConfig['info'];
                            $color = $colorConfig[$result['color'] ?? 'gray'] ?? $colorConfig['gray'];
                            $isLast = $index === count($results) - 1;
                        @endphp
                        <li>
                            <div class="relative {{ !$isLast ? 'pb-8' : '' }}">
                                @if (!$isLast)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                                        aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span
                                            class="h-8 w-8 rounded-full {{ $color }} flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                {!! $icon['icon'] !!}
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-900 dark:text-white">
                                                {{ $result['description'] }}
                                                @if (!empty($result['highlight']))
                                                    <span
                                                        class="font-medium text-{{ $result['color'] ?? 'primary' }}-600 dark:text-{{ $result['color'] ?? 'primary' }}-400">
                                                        {{ $result['highlight'] }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div
                                            class="text-right text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            @if (isset($result['timestamp']))
                                                {{ $result['timestamp']->diffForHumans() }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
