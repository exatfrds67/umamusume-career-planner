{{--
Component: Synergy Build Planner (Livewire)
Purpose: Displays multi-layer synergy analysis for a character build

Features:
  - Overall score ring with animated fill
  - Tier badge (S+, S, A, B, C)
  - Per-layer breakdown bars (7 layers)
  - Critical issues list
  - Actionable recommendations
  - Refresh button to force recalculation
  - Compact mode for embedding in other panels

Props (from Livewire component):
  - reportData (array|null): Serialized SynergyReport
  - isCompact (bool): Whether to show compact/mini view
  - characterId (int): Character ID

Accessibility: WCAG 2.2 AA compliant
--}}

<div x-data="synergyPlanner()" class="synergy-build-planner">
    @if ($reportData === null)
        <div class="flex items-center justify-center py-8 text-neutral-500 dark:text-neutral-400">
            <svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>Analyzing build synergy...</span>
        </div>
    @else
        @php
            $overallScore = $reportData['overall_score'] ?? 0;
            $tier = $reportData['tier'] ?? 'C';
            $layers = $reportData['layers'] ?? [];
            $criticalIssues = $reportData['critical_issues'] ?? [];
        @endphp

        @if ($isCompact)
            {{-- Compact Mode: mini badge + score --}}
            <div class="flex items-center gap-3 p-3 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                <div class="relative flex items-center justify-center w-12 h-12">
                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <path class="text-neutral-200 dark:text-neutral-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                        <path class="{{ $this->tierColorClass($tier) }}" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ $overallScore }}, 100" stroke-linecap="round" />
                    </svg>
                    <span class="absolute text-xs font-bold text-neutral-900 dark:text-white">{{ round($overallScore) }}</span>
                </div>
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $this->tierBadgeClass($tier) }}">
                        {{ $tier }}
                    </span>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Synergy Score</p>
                </div>
                @if (count($criticalIssues) > 0)
                    <span class="ml-auto inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-bold" title="{{ count($criticalIssues) }} critical issue(s)">
                        {{ count($criticalIssues) }}
                    </span>
                @endif
            </div>
        @else
            {{-- Full Mode --}}
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Synergy Analysis</h3>
                    </div>
                    <button wire:click="refresh" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg transition-colors focus:outline-hidden focus:ring-2 focus:ring-primary-500/50"
                        aria-label="Refresh synergy analysis">
                        <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span wire:loading.remove>Refresh</span>
                        <span wire:loading>Analyzing...</span>
                    </button>
                </div>

                {{-- Score Ring + Tier --}}
                <div class="flex items-center gap-6 px-6 py-5">
                    <div class="relative flex items-center justify-center w-24 h-24 shrink-0">
                        <svg class="w-24 h-24 -rotate-90" viewBox="0 0 36 36" aria-label="Synergy score: {{ round($overallScore) }} out of 100">
                            <path class="text-neutral-200 dark:text-neutral-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="2.5" />
                            <path class="{{ $this->tierColorClass($tier) }}" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="2.5" stroke-dasharray="{{ $overallScore }}, 100" stroke-linecap="round" />
                        </svg>
                        <span class="absolute text-2xl font-bold text-neutral-900 dark:text-white">{{ round($overallScore) }}</span>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $this->tierBadgeClass($tier) }}">
                            Tier {{ $tier }}
                        </span>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                            @if ($overallScore >= 90)
                                Exceptional synergy — your build is highly optimized.
                            @elseif ($overallScore >= 80)
                                Strong synergy with minor improvement areas.
                            @elseif ($overallScore >= 65)
                                Good foundation with room for optimization.
                            @elseif ($overallScore >= 50)
                                Average synergy — several areas need attention.
                            @else
                                Weak synergy — significant improvements recommended.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Layer Breakdown --}}
                <div class="px-6 pb-4">
                    <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Layer Breakdown</h4>
                    <div class="space-y-3">
                        @foreach ($layers as $layer)
                            @php
                                $layerScore = $layer['score'] ?? 0;
                                $layerName = $layer['breakdown']['display_name']
                                    ?? str_replace('_', ' ', ucfirst($layer['layer'] ?? 'unknown'));
                                $layerWeight = ($layer['weight'] ?? 0) * 100;
                                $uniqueSkillName = $layer['breakdown']['unique_skill_name'] ?? null;
                                $avgHintLevel = $layer['breakdown']['avg_hint_level'] ?? null;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $layerName }}</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">
                                        {{ round($layerScore) }}/100
                                        <span class="text-xs text-neutral-400 dark:text-neutral-500">({{ round($layerWeight) }}%)</span>
                                    </span>
                                </div>
                                <div class="w-full h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $this->barColorClass($layerScore) }}"
                                         style="width: {{ $layerScore }}%"
                                         role="progressbar"
                                         aria-valuenow="{{ round($layerScore) }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-label="{{ $layerName }} synergy score">
                                    </div>
                                </div>
                                @if ($uniqueSkillName !== null || $avgHintLevel !== null)
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                                        @if ($uniqueSkillName !== null)
                                            <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-0.5 dark:bg-neutral-700">
                                                Unique: {{ $uniqueSkillName }}
                                            </span>
                                        @endif
                                        @if ($avgHintLevel !== null)
                                            <span>Avg. hint level: {{ number_format((float) $avgHintLevel, 1) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Critical Issues --}}
                @if (count($criticalIssues) > 0)
                    <div class="px-6 pb-4">
                        <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            Critical Issues
                        </h4>
                        <ul class="space-y-1.5" role="list">
                            @foreach ($criticalIssues as $issue)
                                <li class="flex items-start gap-2 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-lg px-3 py-2">
                                    <span class="shrink-0 mt-0.5 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    {{ $issue }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Recommendations (collapsible) --}}
                @php
                    $allRecommendations = [];
                    foreach ($layers as $layer) {
                        foreach ($layer['recommendations'] ?? [] as $rec) {
                            $allRecommendations[] = $rec;
                        }
                    }
                @endphp
                @if (count($allRecommendations) > 0)
                    <div class="px-6 pb-5" x-data="{ showRecs: false }">
                        <button @click="showRecs = !showRecs"
                            class="flex items-center gap-2 text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                            :aria-expanded="showRecs">
                            <svg class="w-4 h-4 transition-transform" :class="showRecs && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Recommendations ({{ count($allRecommendations) }})
                        </button>
                        <div x-show="showRecs" x-transition class="mt-2">
                            <ul class="space-y-1.5" role="list">
                                @foreach ($allRecommendations as $rec)
                                    <li class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300 bg-primary-50 dark:bg-primary-900/10 rounded-lg px-3 py-2">
                                        <span class="shrink-0 mt-0.5 w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                        {{ $rec }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>

@script
<script>
    Alpine.data('synergyPlanner', () => ({
        // Placeholder for future Alpine interactivity
    }));
</script>
@endscript
