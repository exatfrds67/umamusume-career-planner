{{--
Component: ClassPyramid
Purpose: Fan count hierarchy visualization showing fanbase distribution

Props:
  - title (string): Component title
  - grades (array): Grade structure with fan counts
  - variant (string): Display variant - 'pyramid'/'bars'/'cards' (default: pyramid)
  - height (string): Container height (default: h-96)
  - colors (array): Color scheme for grades

Grades structure:
  [
      { grade: 'G1', fans: 5000, color: 'bg-red-500' },
      { grade: 'G2', fans: 3500, color: 'bg-orange-500' },
      { grade: 'G3', fans: 2000, color: 'bg-yellow-500' },
      { grade: 'Listed', fans: 1200, color: 'bg-green-500' },
      { grade: 'Open', fans: 800, color: 'bg-blue-500' }
  ]

Usage:
  <x-class-pyramid 
      title="Fan Hierarchy" 
      :grades="$fanGrades"
      variant="pyramid"
  />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'title' => 'Fan Distribution',
    'grades' => [],
    'variant' => 'pyramid',
    'height' => 'h-96',
])

<div class="glass-card rounded-xl p-6 space-y-4"
    x-data="classPyramid({{ json_encode($grades) }})">
    {{-- Header --}}
    <div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $title }}</h3>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Race tier distribution and fanbase growth</p>
    </div>

    {{-- Total Fans Summary --}}
    <div
        class="bg-linear-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700/50">
        <div class="flex items-center justify-between">
            <div>
                <span class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Total Fanbase</span>
                <span class="block text-2xl font-bold text-neutral-900 dark:text-white" x-text="totalFans.toLocaleString()">
                </span>
            </div>
            <div class="text-3xl opacity-20">👥</div>
        </div>
    </div>

    {{-- Pyramid Variant --}}
    @if ($variant === 'pyramid')
        <div class="{{ $height }} flex flex-col justify-center items-center space-y-2">

            {{-- Pyramid Layers --}}
            <template x-for="(layer, index) in sortedGrades" :key="index">
                <div class="w-full">
                    {{-- Layer Container --}}
                    <div class="flex items-center justify-center gap-2 mb-2">
                        {{-- Left padding for pyramid effect --}}
                        <div :style="`width: ${(index) * 15}px`"></div>

                        {{-- Layer bar --}}
                        <div class="flex-1 relative">
                            <div :class="`${layer.color} rounded-lg transition-all duration-300 hover:shadow-lg hover:scale-105 cursor-pointer p-3`"
                                :style="`opacity: 1 - (index * 0.1)`" @mouseover="hoveredLayer = index"
                                @mouseout="hoveredLayer = null" role="button"
                                :aria-label="`${layer.grade} tier with ${layer.fans.toLocaleString()} fans`"
                                tabindex="0">

                                {{-- Content --}}
                                <div class="flex items-center justify-between text-white">
                                    <span class="font-bold text-sm" x-text="layer.grade"></span>
                                    <span class="text-xs opacity-90"
                                        x-text="`${layer.fans.toLocaleString()} fans`"></span>
                                    <span class="text-xs font-semibold"
                                        x-text="`${Math.round((layer.fans / totalFans) * 100)}%`">
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right padding for pyramid effect --}}
                        <div :style="`width: ${(index) * 15}px`"></div>
                    </div>

                    {{-- Tooltip on hover --}}
                    <div x-show="hoveredLayer === index"
                        class="text-center text-xs text-neutral-600 dark:text-neutral-400 mb-2" x-transition>
                        <span x-text="`Growth potential: +${(layer.fans * 0.3).toLocaleString()} fans`"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Bars Variant --}}
    @elseif ($variant === 'bars')
        <div class="space-y-3">
            <template x-for="(grade, index) in sortedGrades" :key="index">
                <div class="space-y-1">
                    {{-- Grade Label --}}
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300" x-text="grade.grade">
                        </label>
                        <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400"
                            x-text="`${grade.fans.toLocaleString()} (${Math.round((grade.fans / totalFans) * 100)}%)`">
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                        <div :class="`${grade.color} h-full rounded-full transition-all duration-500`"
                            :style="`width: ${(grade.fans / maxFans) * 100}%`">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Cards Variant --}}
    @elseif ($variant === 'cards')
        <div class="grid grid-cols-2 gap-3 @lg:grid-cols-3">
            <template x-for="(grade, index) in sortedGrades" :key="index">
                <div :class="`${grade.color} rounded-lg p-4 text-white space-y-2 hover:shadow-lg transition-shadow`"
                    role="article" :aria-label="`${grade.grade} tier`">

                    {{-- Tier Name --}}
                    <h4 class="font-bold text-lg" x-text="grade.grade"></h4>

                    {{-- Fans Count --}}
                    <div>
                        <span class="text-xs opacity-90 block">Fanbase</span>
                        <span class="text-xl font-bold" x-text="grade.fans.toLocaleString()"></span>
                    </div>

                    {{-- Percentage --}}
                    <div>
                        <span class="text-xs opacity-90 block">Share</span>
                        <span class="text-sm font-semibold" x-text="`${Math.round((grade.fans / totalFans) * 100)}%`">
                        </span>
                    </div>
                </div>
            </template>
        </div>
    @endif

    {{-- Legend --}}
    <div class="mt-6 pt-4 border-t border-neutral-200 dark:border-neutral-700">
        <h4 class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-3">Grade Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="font-medium text-red-600 dark:text-red-400">G1</span>
                <span class="text-neutral-600 dark:text-neutral-400"> - Highest tier races</span>
            </div>
            <div>
                <span class="font-medium text-blue-600 dark:text-blue-400">Open</span>
                <span class="text-neutral-600 dark:text-neutral-400"> - General races</span>
            </div>
        </div>
    </div>
</div>

@once
    @vite(['resources/js/components/class-pyramid.js'])
@endonce
