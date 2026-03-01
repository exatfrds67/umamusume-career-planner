@props([
    'suggestions' => [],
])

@php
    $riskColors = [
        'none' => 'text-gray-600 dark:text-gray-300',
        'low' => 'text-success-600 dark:text-success-400',
        'medium' => 'text-warning-600 dark:text-warning-400',
        'high' => 'text-error-600 dark:text-error-400',
    ];

    $riskLabels = [
        'none' => 'No risk',
        'low' => 'Low risk',
        'medium' => 'Medium risk',
        'high' => 'High risk',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'glass-card rounded-xl overflow-hidden']) }}>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Training Suggestions
            </h3>
            <a href="{{ route('training.predictions') }}"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                aria-label="View all training suggestions">
                View All
            </a>
        </div>

        @if (empty($suggestions))
            <div class="text-center py-6">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Create a character to get AI-powered training suggestions.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($suggestions as $suggestion)
                    <div
                        class="flex items-center justify-between p-3 rounded-lg {{ $suggestion['recommended'] ?? false ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 hover:bg-primary-100/70 dark:hover:bg-primary-900/40' : 'bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $suggestion['action'] ?? 'Unknown' }}
                                </span>
                                @if ($suggestion['recommended'] ?? false)
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300">
                                        Recommended
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                {{ $suggestion['gains'] ?? '' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 ml-4">
                            <span class="text-xs font-medium {{ $riskColors[$suggestion['risk'] ?? 'low'] }}">
                                {{ $riskLabels[$suggestion['risk'] ?? 'low'] }}
                            </span>
                            <a href="{{ route('training.predictions') }}"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                                aria-label="Select {{ $suggestion['action'] ?? 'training' }}">
                                Select
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
