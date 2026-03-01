{{--
Component: SPAllocatorInterface
Purpose: Budget allocation interface with skill skill distribution

Props:
  - character (object): Character with skill data
  - totalBudget (int): Total SP available for allocation
  - allocations (array): Current SP allocations by skill ID
  - skills (array): Available skills for allocation

Usage:
  <x-sp-allocator-interface :character="$character" :totalBudget="500" :skills="$skills" />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'character' => null,
    'totalBudget' => 0,
    'allocations' => [],
    'skills' => [],
])

<div x-data="spAllocator()" 
     x-init="totalBudget = {{ $totalBudget }}; skills = {{ json_encode($skills) }}; allocations = {{ json_encode($allocations) }}; loadCharacterSkills()"
     class="space-y-6">

    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SP Allocation</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Distribute skill points across your character's skills</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="undo()"
                :disabled="historyIndex <= 0"
                class="
                    p-2 rounded-lg
                    bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    disabled:opacity-50 disabled:cursor-not-allowed
                    transition-colors
                    focus:outline-hidden focus:ring-2 focus:ring-blue-500
                "
                aria-label="Undo allocation"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6-6m0 0l-6 6" />
                </svg>
            </button>

            <button
                type="button"
                @click="redo()"
                :disabled="historyIndex >= allocationHistory.length - 1"
                class="
                    p-2 rounded-lg
                    bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    text-gray-700 dark:text-gray-300
                    hover:bg-gray-50 dark:hover:bg-gray-700
                    disabled:opacity-50 disabled:cursor-not-allowed
                    transition-colors
                    focus:outline-hidden focus:ring-2 focus:ring-blue-500
                "
                aria-label="Redo allocation"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m21-10l-6-6m0 0l6 6" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Budget Status Card --}}
    <div :class="`
        rounded-lg border p-4 space-y-3
        ${budgetColor.includes('critical') ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : ''}
        ${budgetColor.includes('warning') ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' : ''}
        ${budgetColor.includes('healthy') ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : ''}
    `">
        
        {{-- Budget Bars --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium" :class="budgetColor">Total Budget</span>
                <span class="text-sm font-semibold" :class="budgetColor" x-text="`${totalBudget} SP`"></span>
            </div>
            
            <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="absolute h-full bg-linear-to-r from-green-500 to-emerald-500"
                     :style="`width: ${(totalAllocated / totalBudget) * 100}%`">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-600 dark:text-gray-400">Allocated</span>
                <span class="font-semibold text-gray-900 dark:text-white" x-text="`${totalAllocated} / ${totalBudget}`"></span>
            </div>
        </div>

        {{-- Remaining SP Highlight --}}
        <div class="flex items-center justify-between pt-2 border-t" :class="budgetColor">
            <span class="font-medium">Remaining</span>
            <span class="text-xl font-bold" 
                :class="isOverBudget ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                x-text="`${remainingSP} SP`"
            ></span>
        </div>

        {{-- Over Budget Warning --}}
        <template x-if="showBudgetWarning && isOverBudget">
            <div class="flex items-start gap-2 bg-red-100 dark:bg-red-900/50 border border-red-300 dark:border-red-700 rounded p-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">Budget Exceeded</p>
                    <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                        You've allocated more SP than available. Reduce allocations to proceed.
                    </p>
                </div>
            </div>
        </template>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-2">
        <button
            type="button"
            @click="distributeEvenly()"
            class="
                px-4 py-2 rounded-lg text-sm font-medium
                bg-blue-100 dark:bg-blue-900/30
                text-blue-700 dark:text-blue-300
                hover:bg-blue-200 dark:hover:bg-blue-900/50
                transition-colors
                focus:outline-hidden focus:ring-2 focus:ring-blue-500
            "
        >
            Distribute Evenly
        </button>
        
        <button
            type="button"
            @click="clearAllAllocations()"
            class="
                px-4 py-2 rounded-lg text-sm font-medium
                bg-gray-100 dark:bg-gray-700
                text-gray-700 dark:text-gray-300
                hover:bg-gray-200 dark:hover:bg-gray-600
                transition-colors
                focus:outline-hidden focus:ring-2 focus:ring-gray-500
            "
        >
            Clear All
        </button>

        {{-- Auto-Save Indicator --}}
        <div class="ml-auto flex items-center gap-2 px-3 py-2">
            <template x-if="isSaving">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-xs font-medium">Saving...</span>
                </div>
            </template>
            <template x-if="!isSaving && isDirty">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm4-2a1 1 0 11-2 0 1 1 0 012 0zm3 1a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-medium">Unsaved changes</span>
                </div>
            </template>
            <template x-if="!isSaving && !isDirty">
                <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs font-medium">All saved</span>
                </div>
            </template>
        </div>
    </div>

    {{-- Allocated Skills Section --}}
    <template x-if="allocatedSkills.length > 0">
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Allocated Skills</h4>
            <div class="space-y-2">
                <template x-for="skill in allocatedSkills" :key="skill.id">
                    <div class="
                        flex items-center justify-between gap-4
                        bg-white dark:bg-gray-800
                        border border-gray-200 dark:border-gray-700
                        rounded-lg p-4
                        hover:ring-2 hover:ring-blue-500
                        transition-all
                    ">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h5 class="font-medium text-gray-900 dark:text-white truncate" x-text="skill.name"></h5>
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold"
                                    :class="getSkillColor(skill.tier)"
                                    x-text="`Tier ${skill.tier}`"
                                ></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="`Max: ${skill.max_sp}`"></span>
                                <span>•</span>
                                <span x-text="`Type: ${skill.type || 'Unknown'}`"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="decrementAllocation(skill.id, 5)"
                                :disabled="(allocations[skill.id] || 0) <= 0"
                                class="
                                    p-1.5 rounded
                                    bg-gray-100 dark:bg-gray-700
                                    text-gray-600 dark:text-gray-400
                                    hover:bg-gray-200 dark:hover:bg-gray-600
                                    disabled:opacity-50 disabled:cursor-not-allowed
                                    transition-colors
                                    focus:outline-hidden focus:ring-2 focus:ring-blue-500
                                "
                                aria-label="Decrease SP"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <input
                                type="number"
                                x-model.number="allocations[skill.id]"
                                @change="allocateSP(skill.id, allocations[skill.id])"
                                :max="skill.max_sp"
                                min="0"
                                class="
                                    w-16 px-2 py-1 rounded text-center font-semibold
                                    bg-gray-100 dark:bg-gray-700
                                    border border-gray-200 dark:border-gray-600
                                    text-gray-900 dark:text-white
                                    focus:outline-hidden focus:ring-2 focus:ring-blue-500
                                "
                                aria-label="SP amount"
                            />

                            <button
                                type="button"
                                @click="incrementAllocation(skill.id, 5)"
                                :disabled="(allocations[skill.id] || 0) >= skill.max_sp"
                                class="
                                    p-1.5 rounded
                                    bg-blue-100 dark:bg-blue-900/30
                                    text-blue-600 dark:text-blue-400
                                    hover:bg-blue-200 dark:hover:bg-blue-900/50
                                    disabled:opacity-50 disabled:cursor-not-allowed
                                    transition-colors
                                    focus:outline-hidden focus:ring-2 focus:ring-blue-500
                                "
                                aria-label="Increase SP"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                @click="clearAllocation(skill.id)"
                                class="
                                    p-1.5 rounded
                                    bg-red-100 dark:bg-red-900/30
                                    text-red-600 dark:text-red-400
                                    hover:bg-red-200 dark:hover:bg-red-900/50
                                    transition-colors
                                    focus:outline-hidden focus:ring-2 focus:ring-red-500
                                "
                                aria-label="Remove allocation"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Unallocated Skills Grid --}}
    <template x-if="unallocatedSkills.length > 0">
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Available Skills</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <template x-for="skill in unallocatedSkills" :key="skill.id">
                    <button
                        type="button"
                        @click="allocateSP(skill.id, Math.min(10, skill.max_sp))"
                        class="
                            text-left p-4 rounded-lg
                            bg-gray-50 dark:bg-gray-800
                            border border-gray-200 dark:border-gray-700
                            hover:border-blue-400 dark:hover:border-blue-600
                            hover:bg-blue-50 dark:hover:bg-blue-900/20
                            transition-colors
                            focus:outline-hidden focus:ring-2 focus:ring-blue-500
                            group
                        "
                    >
                        <div class="flex items-start justify-between mb-2">
                            <h5 class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400" x-text="skill.name"></h5>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold"
                                :class="getSkillColor(skill.tier)"
                                x-text="`Tier ${skill.tier}`"
                            ></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="`Max SP: ${skill.max_sp}`"></p>
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- Empty State --}}
    <template x-if="skills.length === 0">
        <div class="py-12 text-center">
            <svg class="w-12 h-12 text-gray-500 dark:text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400 font-medium">No skills available</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Equip skills from your character's skill list to allocate SP</p>
        </div>
    </template>
</div>
