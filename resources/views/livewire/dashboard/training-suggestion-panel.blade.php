<div class="glass-card rounded-xl overflow-hidden" x-data="{}">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-medium leading-6 text-neutral-900 dark:text-white" id="training-suggestions-heading">
                Training Suggestions
            </h3>
            <a href="{{ route('training.predictions') }}"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                aria-label="View all training suggestions">
                View All
            </a>
        </div>
        <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-300">Recommended now: compare projected gains and risk, then take the highest-value action.</p>

        @if (empty($suggestions))
            <div class="text-center py-6">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="1"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </svg>
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    No suggestions yet. Run a prediction to get AI-powered advice.
                </p>
            </div>
        @else
            @php
                $riskClasses = [
                    'none' => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-100',
                    'low' => 'bg-success-100 text-success-800 dark:bg-success-900/35 dark:text-success-100 dark:ring-1 dark:ring-success-500/30',
                    'medium' => 'bg-warning-100 text-warning-800 dark:bg-warning-700/35 dark:text-warning-100 dark:ring-1 dark:ring-warning-500/40',
                    'high' => 'bg-error-100 text-error-800 dark:bg-error-900/35 dark:text-error-100 dark:ring-1 dark:ring-error-500/35',
                ];

                $riskLabels = [
                    'none' => 'No Risk',
                    'low' => 'Low Risk',
                    'medium' => 'Medium Risk',
                    'high' => 'High Risk',
                ];
            @endphp

            <div class="space-y-3" role="list" aria-labelledby="training-suggestions-heading">
                @foreach ($suggestions as $index => $suggestion)
                    @php
                        $isSelected = $selectedIndex === $index;
                        $riskKey = $suggestion['risk'] ?? 'none';
                        $isBest = ($suggestion['recommended'] ?? false) || $index === 0;
                    @endphp

                    <button type="button"
                        wire:click="selectSuggestion({{ $index }})"
                        class="w-full text-left rounded-lg border transition-colors p-3 {{ $isSelected ? 'border-primary-400 bg-primary-50/80 dark:bg-primary-900/20 dark:border-primary-600' : 'border-neutral-200 bg-white/70 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800/60 dark:hover:bg-neutral-700/70' }}"
                        aria-expanded="{{ $isSelected ? 'true' : 'false' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-neutral-900 dark:text-white truncate">
                                        {{ $suggestion['action'] ?? 'Unknown Training' }}
                                    </span>
                                    @if ($isBest)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                                            Recommended
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-secondary-100 text-secondary-800 dark:bg-secondary-900/50 dark:text-secondary-300">
                                        Gains: {{ $suggestion['gains'] ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded {{ $riskClasses[$riskKey] ?? $riskClasses['none'] }}">
                                        {{ $riskLabels[$riskKey] ?? 'No Risk' }}
                                    </span>
                                </div>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-neutral-400 transition-transform {{ $isSelected ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </button>

                    <div x-show="{{ $isSelected ? 'true' : 'false' }}" x-collapse>
                        <div class="rounded-lg border border-primary-200 bg-primary-50/70 dark:border-primary-800 dark:bg-primary-900/20 p-4 -mt-1">
                            <p class="text-sm text-neutral-700 dark:text-neutral-200">
                                Prioritize <span class="font-semibold">{{ $suggestion['action'] ?? 'this training' }}</span>
                                to capitalize on current momentum and improve race readiness.
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <a href="{{ route('training.predictions') }}"
                                    class="btn btn-primary btn-sm">
                                    Start Training
                                </a>
                                <button type="button" wire:click="clearSelection" class="btn btn-secondary btn-sm">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
