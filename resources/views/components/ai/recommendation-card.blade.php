@props(['recommendation', 'expanded' => false, 'showActions' => true])

@php
    $priorityColors = [
        'critical' => 'red',
        'high' => 'orange',
        'medium' => 'blue',
        'low' => 'neutral',
    ];

    $priorityIcons = [
        'critical' => '🚨',
        'high' => '⭐',
        'medium' => '💡',
        'low' => 'ℹ️',
    ];

    $color = $priorityColors[$recommendation->priority] ?? 'neutral';
    $icon = $priorityIcons[$recommendation->priority] ?? '•';

    $cardId = 'recommendation-' . uniqid();
@endphp

<div x-data="{
    expanded: @js($expanded),
    dismissed: false
}" x-show="!dismissed" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95"
    {{ $attributes->merge(['class' => 'recommendation-card rounded-lg border-2 transition-all duration-300 hover:shadow-lg bg-white dark:bg-neutral-800 border-' . $color . '-200 dark:border-' . $color . '-700']) }}
    role="article" aria-labelledby="{{ $cardId }}-title" aria-expanded="false"
    x-bind:aria-expanded="expanded.toString()">
    {{-- Header (Always Visible) --}}
    <button @click="expanded = !expanded"
        class="w-full text-left p-4 focus:outline-none focus:ring-2 focus:ring-{{ $color }}-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 rounded-t-lg"
        aria-controls="{{ $cardId }}-content" aria-label="Toggle recommendation details">
        <div class="flex items-start justify-between gap-3">
            {{-- Icon & Title --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xl" role="img"
                        aria-label="{{ ucfirst($recommendation->priority) }} priority">{{ $icon }}</span>
                    <h3 id="{{ $cardId }}-title"
                        class="text-lg font-bold text-neutral-900 dark:text-neutral-100 truncate">
                        {{ $recommendation->action }}
                    </h3>
                </div>

                {{-- Priority & Confidence --}}
                <div class="flex items-center gap-3 text-sm">
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                        Priority: {{ ucfirst($recommendation->priority) }}
                    </span>

                    @if (isset($recommendation->confidence_score) && $recommendation->confidence_score)
                        <span class="text-neutral-600 dark:text-neutral-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>Confidence: {{ number_format($recommendation->confidence_score * 100) }}%</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Expand/Collapse Icon --}}
            <div class="shrink-0 mt-1">
                <svg class="w-5 h-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </button>

    {{-- Expanded Content --}}
    <div id="{{ $cardId }}-content" x-show="expanded" x-collapse
        class="border-t border-{{ $color }}-200 dark:border-{{ $color }}-700">
        <div class="p-4 space-y-4">
            {{-- Reasoning Section --}}
            @if (isset($recommendation->reasoning) && $recommendation->reasoning)
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Reasoning
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        @foreach (explode("\n", $recommendation->reasoning) as $line)
                            @if (trim($line))
                                <div class="flex items-start gap-2">
                                    <span class="text-{{ $color }}-500 mt-0.5 shrink-0"
                                        aria-hidden="true">•</span>
                                    <span class="flex-1">{{ trim($line, '• ') }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Expected Outcomes Section --}}
            @if (isset($recommendation->expected_outcomes) && !empty($recommendation->expected_outcomes))
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Expected Outcomes
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        @foreach ($recommendation->expected_outcomes as $key => $outcome)
                            <div class="flex items-start gap-2">
                                <span class="text-success-500 mt-0.5 shrink-0" aria-hidden="true">✓</span>
                                <span class="flex-1">
                                    @if (is_string($key) && !is_numeric($key))
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                    @endif
                                    {{ is_array($outcome) ? implode(', ', $outcome) : $outcome }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Risks Section --}}
            @if (isset($recommendation->risks) && !empty($recommendation->risks))
                <div>
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-neutral-100 mb-2 uppercase tracking-wide">
                        Risks
                    </h4>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                        @foreach ($recommendation->risks as $risk)
                            <div class="flex items-start gap-2">
                                <span class="text-warning-500 mt-0.5 shrink-0" aria-hidden="true">⚠</span>
                                <span class="flex-1">{{ $risk }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            @if ($showActions)
                <div class="flex items-center gap-2 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                    <button type="button" wire:click="applyRecommendation('{{ $recommendation->id ?? '' }}')"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-{{ $color }}-600 text-white hover:bg-{{ $color }}-700 focus:outline-none focus:ring-2 focus:ring-{{ $color }}-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Apply this recommendation">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Apply Recommendation
                    </button>

                    <button type="button" @click="dismissed = true"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Dismiss this recommendation">
                        Dismiss
                    </button>

                    <button type="button" wire:click="provideFeedback('{{ $recommendation->id ?? '' }}')"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors duration-200"
                        aria-label="Provide feedback on this recommendation">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        Feedback
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@once
    @push('styles')
        @vite(['resources/css/components/ai/recommendation-card.css'])
    @endpush
@endonce
