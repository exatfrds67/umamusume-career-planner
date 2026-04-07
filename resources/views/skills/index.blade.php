@extends('layouts.app')

@section('title', 'Skill Management')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Skills']]" />

    <div class="page-stack" x-data="skillManagement({{ $isAdmin ? 'true' : 'false' }}, '{{ $selectedCharacterId ?? '' }}')">
        {{-- Header Section --}}
        <header class="page-hero">
            <div class="page-hero__content">
            <div>
                <div class="page-hero__eyebrow">
                    <span>Skill Loadout</span>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="page-hero__title">Skill Management</h1>
                    <span x-show="isAdmin" class="hero-chip">
                        <span aria-hidden="true">🔓</span>
                        <span>Admin Mode</span>
                    </span>
                </div>
                <p class="page-hero__body text-sm sm:text-base">
                    Manage skills, track hints, and optimize SP allocation with AI-powered recommendations
                </p>
            </div>

            <div class="page-hero__actions">
                {{-- Character Selector --}}
                <select x-model="selectedCharacterId" @change="loadCharacterData()" id="character-selector"
                    name="character_id"
                    class="form-select min-w-64 rounded-xl border-neutral-300/80 bg-white/90 dark:border-neutral-600 dark:bg-neutral-800/90 dark:text-white"
                    aria-label="Select character">
                    <option value="">Select Character</option>
                    @foreach ($characters as $character)
                        <option value="{{ $character->id }}">{{ $character->name }}</option>
                    @endforeach
                </select>

                {{-- Refresh Button --}}
                <button @click="refreshData()" :disabled="loading" class="btn btn-secondary" aria-label="Refresh data">
                    <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="ml-2">Refresh</span>
                </button>
            </div>
            </div>
        </header>

        {{-- Loading State --}}
        <div x-show="loading && !character" class="filter-surface" role="status">
            <div class="card-body text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500 mx-auto" aria-hidden="true"></div>
                <p class="mt-4 text-neutral-600 dark:text-neutral-400">Loading skill data...</p>
            </div>
        </div>

        {{-- No Character Selected --}}
        <div x-show="!loading && !selectedCharacterId" class="filter-surface">
            <div class="card-body text-center py-12">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No Character Selected</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Select a character to manage their skills</p>
            </div>
        </div>

        {{-- Main Content --}}
        <div x-show="character && !loading" class="space-y-6">
            {{-- SP Overview Card --}}
            <section class="card bg-linear-to-br from-primary-500 to-primary-600 text-white"
                aria-labelledby="sp-stats-heading">
                <h2 id="sp-stats-heading" class="sr-only">SP Statistics Overview</h2>
                <div class="card-body">
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                        {{-- 1. Available SP (primary KPI — largest) --}}
                        <div class="flex flex-col gap-1">
                            <p class="text-primary-100 text-xs font-semibold uppercase tracking-wide">Available SP</p>
                            <p class="text-3xl font-extrabold tabular-nums leading-none"
                                x-text="formatNumber(character?.available_sp || 0)"
                                aria-label="Available SP"></p>
                            <p class="text-xs text-primary-200">Ready to spend</p>
                        </div>

                        {{-- 2. SP Spent --}}
                        <div class="flex flex-col gap-1">
                            <p class="text-primary-100 text-xs font-semibold uppercase tracking-wide">SP Spent</p>
                            <p class="text-2xl font-bold tabular-nums leading-none"
                                x-text="formatNumber(spStats?.total_spent || 0)"
                                aria-label="Total SP spent"></p>
                            <p class="text-xs text-primary-200">Total invested</p>
                        </div>

                        {{-- 3. SP Saved via Hints --}}
                        <div class="flex flex-col gap-1">
                            <p class="text-primary-100 text-xs font-semibold uppercase tracking-wide">SP Saved</p>
                            <p class="text-2xl font-bold tabular-nums leading-none text-green-300"
                                x-text="formatNumber(spStats?.total_saved || 0)"
                                aria-label="SP saved through hint discounts"></p>
                            <p class="text-xs text-primary-200">Via hint discounts</p>
                        </div>

                        {{-- 4. SP Potential (with info tooltip) --}}
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-1.5">
                                <p class="text-primary-100 text-xs font-semibold uppercase tracking-wide">SP Potential</p>
                                {{-- Info button with inline tooltip --}}
                                <div
                                    x-data="{ showSpTooltip: false }"
                                    class="relative inline-flex"
                                    @mouseenter="showSpTooltip = true"
                                    @mouseleave="showSpTooltip = false"
                                    @focus="showSpTooltip = true"
                                    @blur="showSpTooltip = false">
                                    <button
                                        type="button"
                                        class="w-4 h-4 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
                                        aria-label="What is SP Potential?"
                                        tabindex="0">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div
                                        x-show="showSpTooltip"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 w-56 rounded-lg bg-neutral-900 text-white text-xs p-3 shadow-xl pointer-events-none"
                                        role="tooltip"
                                        aria-live="polite">
                                        <p class="font-semibold mb-1">SP Potential</p>
                                        <p class="text-neutral-300">Maximum additional SP you could save by applying your current hints. Calculated as the sum of each hinted skill&apos;s base cost &times; its hint discount level.</p>
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 w-2 h-2 bg-neutral-900 rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-2xl font-bold tabular-nums leading-none text-yellow-300"
                                x-text="formatNumber(Math.round(potentialSavings))"
                                aria-label="SP savings potential from unused hints"></p>
                            <p class="text-xs text-primary-200">
                                <span x-text="skillsWithHints"></span> skill<span x-show="skillsWithHints !== 1">s</span> with hints
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tab Navigation --}}
            <div class="tab-surface">
                <nav class="tab-surface__nav" aria-label="Skill Management Sections" role="tablist"
                    @keydown.arrow-right.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; const idx = tabs.indexOf(document.activeElement); if (idx >= 0) { const next = tabs[(idx + 1) % tabs.length]; next.focus(); next.click(); }"
                    @keydown.arrow-left.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; const idx = tabs.indexOf(document.activeElement); if (idx >= 0) { const prev = tabs[(idx - 1 + tabs.length) % tabs.length]; prev.focus(); prev.click(); }"
                    @keydown.home.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; tabs[0]?.focus(); tabs[0]?.click();"
                    @keydown.end.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; tabs[tabs.length - 1]?.focus(); tabs[tabs.length - 1]?.click();">
                    <button @click="activeTab = 'inventory'" id="tab-inventory" aria-controls="panel-inventory"
                        :class="activeTab === 'inventory' ? 'soft-pill soft-pill--active' : 'soft-pill'"
                        class="font-medium text-sm focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'inventory' ? 'true' : 'false'"
                        :tabindex="activeTab === 'inventory' ? '0' : '-1'">
                        Skill Inventory
                    </button>
                    <button @click="activeTab = 'acquisition'" id="tab-acquisition" aria-controls="panel-acquisition"
                        :class="activeTab === 'acquisition' ? 'soft-pill soft-pill--active' : 'soft-pill'"
                        class="font-medium text-sm focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'acquisition' ? 'true' : 'false'"
                        :tabindex="activeTab === 'acquisition' ? '0' : '-1'">
                        Skill Acquisition
                    </button>
                    <button @click="activeTab = 'evolution'" id="tab-evolution" aria-controls="panel-evolution"
                        :class="activeTab === 'evolution' ? 'soft-pill soft-pill--active' : 'soft-pill'"
                        class="font-medium text-sm focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'evolution' ? 'true' : 'false'"
                        :tabindex="activeTab === 'evolution' ? '0' : '-1'">
                        Skill Evolution
                    </button>
                    <button @click="activeTab = 'planner'" id="tab-planner" aria-controls="panel-planner"
                        :class="activeTab === 'planner' ? 'soft-pill soft-pill--active' : 'soft-pill'"
                        class="font-medium text-sm focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'planner' ? 'true' : 'false'"
                        :tabindex="activeTab === 'planner' ? '0' : '-1'">
                        Build Planner
                    </button>
                    <button @click="activeTab = 'performance'" id="tab-performance" aria-controls="panel-performance"
                        :class="activeTab === 'performance' ? 'soft-pill soft-pill--active' : 'soft-pill'"
                        class="font-medium text-sm focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'performance' ? 'true' : 'false'"
                        :tabindex="activeTab === 'performance' ? '0' : '-1'">
                        Agent Performance
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="mt-6">
                {{-- Skill Inventory Tab --}}
                <div x-show="activeTab === 'inventory'" role="tabpanel" id="panel-inventory"
                    aria-labelledby="tab-inventory" tabindex="0">
                    @include('skills.partials.inventory')
                </div>

                {{-- Skill Acquisition Tab --}}
                <div x-show="activeTab === 'acquisition'" role="tabpanel" id="panel-acquisition"
                    aria-labelledby="tab-acquisition" tabindex="0">
                    @include('skills.partials.acquisition')
                </div>

                {{-- Skill Evolution Tab --}}
                <div x-show="activeTab === 'evolution'" role="tabpanel" id="panel-evolution"
                    aria-labelledby="tab-evolution" tabindex="0">
                    @include('skills.partials.evolution')
                </div>

                {{-- Build Planner Tab --}}
                <div x-show="activeTab === 'planner'" role="tabpanel" id="panel-planner" aria-labelledby="tab-planner"
                    tabindex="0">
                    @include('skills.partials.planner')
                </div>

                {{-- Agent Performance Tab --}}
                <div x-show="activeTab === 'performance'" role="tabpanel" id="panel-performance"
                    aria-labelledby="tab-performance" tabindex="0">
                    @include('skills.partials.performance')
                </div>
            </div>
        </div>

        {{-- Skill Details Modal --}}
        <div x-show="showSkillModal" x-cloak @keydown.escape.window="closeSkillModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog"
            :aria-modal="showSkillModal ? 'true' : 'false'">
            <div class="fixed inset-0 bg-neutral-500/75 dark:bg-neutral-900/75" @click="closeSkillModal()" aria-hidden="true"></div>
                {{-- Modal panel --}}
                <div x-show="showSkillModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    class="relative bg-white dark:bg-neutral-800 rounded-xl text-left shadow-2xl transform transition-all w-full max-w-2xl max-h-[90vh] overflow-y-auto z-50">

                    <template x-if="selectedSkill">
                        <div>
                            {{-- Modal Header --}}
                            <div
                                class="bg-linear-to-r from-primary-500 to-primary-600 px-6 py-4 flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-white" id="modal-title" x-text="selectedSkill.name"></h3>
                                    <div class="flex items-center gap-2 mt-2">
                                        {{-- Rarity Badge --}}
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                            :class="{
                                                'bg-white/20 text-white': selectedSkill.rarity === 'normal',
                                                'bg-blue-200 text-blue-900': selectedSkill.rarity === 'rare',
                                                'bg-purple-200 text-purple-900': selectedSkill.rarity === 'unique'
                                            }"
                                            x-text="selectedSkill.rarity.charAt(0).toUpperCase() + selectedSkill.rarity.slice(1)">
                                        </span>

                                        {{-- Meta Tier Badge --}}
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                            :class="{
                                                'bg-yellow-200 text-yellow-900': selectedSkill
                                                    .meta_tier === 'S+' || selectedSkill
                                                    .meta_tier === 'S',
                                                'bg-green-200 text-green-900': selectedSkill.meta_tier === 'A',
                                                'bg-blue-200 text-blue-900': selectedSkill.meta_tier === 'B',
                                                'bg-neutral-200 text-neutral-900': selectedSkill.meta_tier === 'C'
                                            }"
                                            x-text="selectedSkill.meta_tier"></span>

                                        {{-- Skill Type Badge --}}
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-white/20 text-white"
                                            x-text="selectedSkill.skill_type ? selectedSkill.skill_type.charAt(0).toUpperCase() + selectedSkill.skill_type.slice(1) : 'N/A'">
                                        </span>
                                    </div>
                                </div>
                                <button @click="closeSkillModal()" type="button"
                                    class="text-white hover:text-neutral-200 transition-colors"
                                    aria-label="Close skill details">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-4 space-y-4">
                                {{-- Description --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Description
                                    </h4>
                                    <p class="text-neutral-900 dark:text-white" x-text="selectedSkill.description"></p>
                                </div>

                                {{-- Effects --}}
                                <div x-show="selectedSkill.effects">
                                    <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Effects</h4>
                                    <div class="text-neutral-900 dark:text-white">
                                        <template
                                            x-if="typeof selectedSkill.effects === 'object' && selectedSkill.effects !== null">
                                            <div class="space-y-1">
                                                <p x-show="selectedSkill.effects.activation">
                                                    <span class="font-medium">Activation:</span>
                                                    <span
                                                        x-text="selectedSkill.effects.activation?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                                </p>
                                                <p x-show="selectedSkill.effects.effect">
                                                    <span class="font-medium">Effect:</span>
                                                    <span
                                                        x-text="selectedSkill.effects.effect?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                                </p>
                                                <p x-show="selectedSkill.effects.power">
                                                    <span class="font-medium">Power:</span>
                                                    <span class="capitalize" x-text="selectedSkill.effects.power"></span>
                                                </p>
                                            </div>
                                        </template>
                                        <template x-if="typeof selectedSkill.effects === 'string'">
                                            <p x-text="selectedSkill.effects"></p>
                                        </template>
                                    </div>
                                </div>

                                {{-- SP Cost Information --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-4">
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Base SP Cost</p>
                                        <p class="text-2xl font-bold text-neutral-900 dark:text-white"
                                            x-text="selectedSkill.base_sp_cost"></p>
                                    </div>
                                    <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-4"
                                        x-show="selectedSkill.available_hints > 0">
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Discounted Cost</p>
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"
                                            x-text="selectedSkill.discounted_cost || selectedSkill.base_sp_cost"></p>
                                        <p class="text-xs text-green-600 dark:text-green-400 mt-1"
                                            x-text="`Save ${selectedSkill.sp_savings || 0} SP!`"></p>
                                    </div>
                                </div>

                                {{-- Hint Information --}}
                                <div x-show="selectedSkill.available_hints > 0"
                                    class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-semibold text-green-700 dark:text-green-400">Hints
                                            Available</h4>
                                        <span class="text-lg font-bold text-green-900 dark:text-green-300"
                                            x-text="`${selectedSkill.available_hints}/2`"></span>
                                    </div>
                                    <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                        <div class="bg-green-600 dark:bg-green-400 h-2 rounded-full transition-all"
                                            :style="`width: ${(selectedSkill.available_hints / 2) * 100}%`"></div>
                                    </div>
                                    <p class="text-xs text-green-700 dark:text-green-400 mt-2">Each hint reduces SP cost
                                        by 20%</p>
                                </div>

                                {{-- Evolution Information --}}
                                <div x-show="selectedSkill.evolves_from_id || selectedSkill.evolves_to_id">
                                    <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Evolution
                                        Path</h4>
                                    <div class="flex items-center gap-2">
                                        <span x-show="selectedSkill.evolves_from_id"
                                            class="text-sm text-neutral-600 dark:text-neutral-400">
                                            Evolves from: <span class="font-medium"
                                                x-text="selectedSkill.evolves_from_name || 'Unknown'"></span>
                                        </span>
                                        <span x-show="selectedSkill.evolves_to_id"
                                            class="text-sm text-neutral-600 dark:text-neutral-400">
                                            Evolves to: <span class="font-medium"
                                                x-text="selectedSkill.evolves_to_name || 'Unknown'"></span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Acquisition Status --}}
                                <div x-show="selectedSkill.is_acquired"
                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Already
                                            Acquired</span>
                                    </div>
                                    <div class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                        <span x-show="selectedSkill.turn_acquired">Acquired on Turn: <span
                                                class="font-medium" x-text="selectedSkill.turn_acquired"></span></span>
                                        <span x-show="selectedSkill.final_sp_cost"> | SP Spent: <span class="font-medium"
                                                x-text="selectedSkill.final_sp_cost"></span></span>
                                    </div>
                                </div>

                                {{-- Planned Status --}}
                                <div x-show="!selectedSkill.is_acquired && selectedSkill.is_planned"
                                    class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Planned / Targeted</span>
                                    </div>
                                    <div x-show="selectedSkill.metadata_notes" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                                        <span x-text="selectedSkill.metadata_notes"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-neutral-50 dark:bg-neutral-700 px-6 py-4 flex items-center justify-between gap-3">
                                {{-- Remove button: only for acquired or planned skills --}}
                                <button x-show="selectedSkill.is_acquired || selectedSkill.is_planned"
                                    @click="openRemoveModal(selectedSkill); closeSkillModal()"
                                    :disabled="loading" type="button"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-300 dark:border-red-700 bg-white dark:bg-neutral-800 text-red-600 dark:text-red-400 text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-400 dark:hover:border-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove
                                </button>

                                {{-- Right-side buttons --}}
                                <div class="flex items-center gap-3">
                                    <button @click="closeSkillModal()" type="button"
                                        class="btn btn-secondary">Close</button>
                                    <button x-show="!selectedSkill.is_acquired && !selectedSkill.is_metadata_only" @click="acquireSkill(selectedSkill)"
                                        :disabled="!isAdmin && (!character || (character.available_sp || 0) < (selectedSkill
                                            .discounted_cost ||
                                            selectedSkill.base_sp_cost))"
                                        class="btn btn-primary"
                                        :class="{
                                            'opacity-50 cursor-not-allowed': !isAdmin && (!character || (character
                                                .available_sp || 0) < (
                                                selectedSkill.discounted_cost || selectedSkill.base_sp_cost))
                                        }">
                                        <span x-show="isAdmin" class="mr-1" aria-hidden="true">🔓</span>
                                        Acquire Skill
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
        </div>

    {{-- Action Confirmation Modal --}}
    <div x-show="showActionModal" x-cloak
        class="fixed inset-0 z-60 overflow-y-auto"
        role="dialog" aria-modal="true" :aria-label="actionModalSkill ? 'Skill action for ' + actionModalSkill.name : 'Skill action'"
        @keydown.escape.window="closeActionModal()">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/60" @click="closeActionModal()" aria-hidden="true"></div>

        {{-- Centering wrapper --}}
        <div class="flex min-h-full items-center justify-center p-4">

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-neutral-800 rounded-xl shadow-2xl w-full max-w-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white truncate"
                        x-text="actionModalSkill?.name"></h3>
                    <p class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">Choose an action for this skill</p>
                </div>
                <button @click="closeActionModal()" type="button"
                    class="ml-4 shrink-0 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                    aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Cost summary --}}
            <div class="px-6 py-4">
                <div class="flex items-center justify-between bg-neutral-50 dark:bg-neutral-700 rounded-lg px-4 py-3 mb-5">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">SP Cost</span>
                    <div class="flex items-center gap-2">
                        <template x-if="actionModalSkill && actionModalSkill.available_hints > 0">
                            <span class="text-sm text-neutral-400 line-through"
                                x-text="actionModalSkill.base_sp_cost + ' SP'"></span>
                        </template>
                        <span class="text-xl font-bold text-neutral-900 dark:text-white"
                            x-text="actionModalSkill ? (actionModalSkill.available_hints > 0 ? actionModalSkill.discounted_cost : actionModalSkill.base_sp_cost) + ' SP' : ''"></span>
                        <template x-if="actionModalSkill && actionModalSkill.available_hints > 0">
                            <span class="text-xs font-semibold text-success-600 dark:text-success-400"
                                x-text="'-' + Math.round((actionModalSkill.sp_savings / actionModalSkill.base_sp_cost) * 100) + '%'"></span>
                        </template>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="grid grid-cols-2 gap-3">
                    {{-- Acquire --}}
                    <button @click="confirmAcquire()"
                        :disabled="loading || (!isAdmin && (!character || (character.available_sp || 0) < (actionModalSkill?.discounted_cost || actionModalSkill?.base_sp_cost || 0)))"
                        class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-success-500 bg-success-50 dark:bg-success-900/20 hover:bg-success-100 dark:hover:bg-success-900/40 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50 cursor-not-allowed': loading }">
                        <svg class="w-7 h-7 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-semibold text-success-700 dark:text-success-300">Acquire Now</span>
                        <span class="text-xs text-success-600 dark:text-success-400 text-center">Deduct SP &amp; record acquisition</span>
                    </button>

                    {{-- Plan --}}
                    <button @click="confirmPlan()"
                        :disabled="loading"
                        class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-amber-400 bg-amber-50 dark:bg-neutral-700 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="text-sm font-semibold text-amber-700 dark:text-amber-300">Mark as Planned</span>
                        <span class="text-xs text-amber-600 dark:text-amber-400 text-center">Add to career plan, no SP deducted</span>
                    </button>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-5 flex justify-end">
                <button @click="closeActionModal()" type="button"
                    class="btn btn-secondary">Cancel</button>
            </div>
        </div>
        </div>
    </div>

    {{-- Remove Confirmation Modal --}}
    <div x-show="showRemoveModal" x-cloak
        class="fixed inset-0 z-70 overflow-y-auto"
        role="dialog" aria-modal="true" :aria-label="removeModalSkill ? 'Remove ' + removeModalSkill.name : 'Remove skill'"
        @keydown.escape.window="closeRemoveModal()">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/60" @click="closeRemoveModal()" aria-hidden="true"></div>

        {{-- Centering wrapper --}}
        <div class="flex min-h-full items-center justify-center p-4">

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-neutral-800 rounded-xl shadow-2xl w-full max-w-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white truncate"
                        x-text="removeModalSkill?.name"></h3>
                    <p class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">Remove this skill from your career plan?</p>
                </div>
                <button @click="closeRemoveModal()" type="button"
                    class="ml-4 shrink-0 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                    aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5">
                <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    This will remove
                    <span class="font-semibold text-neutral-900 dark:text-white" x-text="removeModalSkill?.name"></span>
                    from your career plan.
                </p>
                <p x-show="removeModalSkill?.is_acquired"
                    class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">
                    Note: SP spent on this skill will not be refunded.
                </p>
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-5 flex items-center justify-end gap-3">
                <button @click="closeRemoveModal()" type="button"
                    class="btn btn-secondary">Cancel</button>
                <button @click="confirmRemove()" :disabled="loading" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Yes, Remove</span>
                </button>
            </div>
        </div>
        </div>
    </div>
    </div>

@endsection

{{-- JS extracted to resources/js/pages/skills/index.js --}}
@push('scripts')
    {{-- Inject data for JavaScript --}}
    <script id="skills-data" type="application/json">
        {!! json_encode([
            'isAdmin' => $isAdmin ?? false,
            'preSelectedCharacterId' => $preSelectedCharacterId ?? ''
        ]) !!}
    </script>
    @vite('resources/js/pages/skills/index.js')
@endpush
