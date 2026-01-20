@props([
    'errors' => [],
    'title' => 'Errors Occurred',
    'type' => 'error', // error, warning, info
    'showResolution' => true,
    'dismissible' => true,
])

@php
    $typeStyles = [
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'icon_bg' => 'bg-red-100 dark:bg-red-900/30',
            'icon_color' => 'text-red-600 dark:text-red-400',
            'title_color' => 'text-red-800 dark:text-red-200',
            'text_color' => 'text-red-700 dark:text-red-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'icon_bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'icon_color' => 'text-yellow-600 dark:text-yellow-400',
            'title_color' => 'text-yellow-800 dark:text-yellow-200',
            'text_color' => 'text-yellow-700 dark:text-yellow-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'icon_bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'icon_color' => 'text-blue-600 dark:text-blue-400',
            'title_color' => 'text-blue-800 dark:text-blue-200',
            'text_color' => 'text-blue-700 dark:text-blue-300',
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
    ];

    $style = $typeStyles[$type] ?? $typeStyles['error'];

    // Common error resolutions
    $resolutions = [
        'Invalid JSON' => 'Check your JSON syntax. Use a JSON validator to find formatting errors.',
        'Column count mismatch' => 'Ensure all rows have the same number of columns as the header row.',
        'Invalid numeric value' => 'Make sure numeric fields contain only numbers (0-1200 for stats).',
        'Invalid scenario type' => 'Use "ura_finale" or "unity_cup" for scenario type.',
        'File not found' => 'The file may have been moved or deleted. Try uploading again.',
        'Permission denied' => 'Check file permissions or try a different file.',
        'Checksum mismatch' => 'The file may be corrupted. Try downloading or creating a new backup.',
        'Version incompatible' =>
            'This backup was created with a different version. Contact support for migration assistance.',
        'Duplicate entry' => 'A record with this name already exists. Use a different name or enable overwrite mode.',
        'Required field missing' => 'Make sure all required fields are filled in.',
        'Rate limit exceeded' => 'Too many requests. Please wait a moment and try again.',
        'Connection timeout' => 'The server took too long to respond. Check your connection and try again.',
    ];
@endphp

@if (!empty($errors))
    <div {{ $attributes->merge(['class' => "error-display rounded-lg border p-4 {$style['bg']} {$style['border']}"]) }}
        x-data="{ expanded: false, dismissed: false }" x-show="!dismissed" x-transition>

        <div class="flex items-start gap-3">
            {{-- Icon --}}
            <div class="shrink-0">
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $style['icon_bg'] }} {{ $style['icon_color'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $style['icon'] !!}
                    </svg>
                </span>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium {{ $style['title_color'] }}">
                    {{ $title }}
                    @if (count($errors) > 1)
                        <span class="font-normal">({{ count($errors) }} issues)</span>
                    @endif
                </h3>

                {{-- Error List --}}
                <div class="mt-2">
                    @if (count($errors) <= 3)
                        <ul class="list-disc list-inside space-y-1 text-sm {{ $style['text_color'] }}">
                            @foreach ($errors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @else
                        {{-- Show first 2 errors, then expandable --}}
                        <ul class="list-disc list-inside space-y-1 text-sm {{ $style['text_color'] }}">
                            @foreach (array_slice($errors, 0, 2) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <div x-show="expanded" x-collapse>
                            <ul class="list-disc list-inside space-y-1 text-sm {{ $style['text_color'] }} mt-1">
                                @foreach (array_slice($errors, 2) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" @click="expanded = !expanded"
                            class="mt-2 text-sm font-medium {{ $style['icon_color'] }} hover:underline">
                            <span x-text="expanded ? 'Show less' : 'Show {{ count($errors) - 2 }} more'"></span>
                        </button>
                    @endif
                </div>

                {{-- Resolution Suggestions --}}
                @if ($showResolution)
                    @php
                        $matchedResolutions = [];
                        foreach ($errors as $error) {
                            foreach ($resolutions as $pattern => $resolution) {
                                if (stripos($error, $pattern) !== false) {
                                    $matchedResolutions[$pattern] = $resolution;
                                }
                            }
                        }
                    @endphp

                    @if (!empty($matchedResolutions))
                        <div class="mt-3 pt-3 border-t {{ $style['border'] }}">
                            <h4 class="text-sm font-medium {{ $style['title_color'] }} mb-2">
                                💡 Suggested Resolutions
                            </h4>
                            <ul class="space-y-2 text-sm {{ $style['text_color'] }}">
                                @foreach ($matchedResolutions as $pattern => $resolution)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span><strong>{{ $pattern }}:</strong> {{ $resolution }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif

                {{-- Slot for additional content --}}
                @if ($slot->isNotEmpty())
                    <div class="mt-3 pt-3 border-t {{ $style['border'] }}">
                        {{ $slot }}
                    </div>
                @endif
            </div>

            {{-- Dismiss Button --}}
            @if ($dismissible)
                <button type="button" @click="dismissed = true"
                    class="shrink-0 {{ $style['icon_color'] }} hover:opacity-75 transition-opacity">
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
@endif
