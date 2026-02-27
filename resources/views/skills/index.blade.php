@extends('layouts.app')

@section('title', 'Skill Management')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Skills']]" />

    <div class="space-y-6" x-data="skillManagement({{ $isAdmin ? 'true' : 'false' }}, '{{ $selectedCharacterId ?? '' }}')">
        {{-- Header Section --}}
        <header class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Skill Management</h1>
                    <span x-show="isAdmin"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        🔓 Admin Mode - No SP Required
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage skills, track hints, and optimize SP allocation with AI-powered recommendations
                </p>
            </div>

            <div class="flex items-center gap-3">
                {{-- Character Selector --}}
                <select x-model="selectedCharacterId" @change="loadCharacterData()" id="character-selector"
                    name="character_id"
                    class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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
        </header>

        {{-- Loading State --}}
        <div x-show="loading && !character" class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500 mx-auto"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading skill data...</p>
            </div>
        </div>

        {{-- No Character Selected --}}
        <div x-show="!loading && !selectedCharacterId" class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Character Selected</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a character to manage their skills</p>
            </div>
        </div>

        {{-- Main Content --}}
        <div x-show="character && !loading" class="space-y-6">
            {{-- SP Overview Card --}}
            <section class="card bg-linear-to-br from-primary-500 to-primary-600 text-white"
                aria-labelledby="sp-stats-heading">
                <h2 id="sp-stats-heading" class="sr-only">SP Statistics</h2>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Available SP</p>
                            <p class="text-3xl font-bold mt-1" x-text="formatNumber(character?.available_sp || 0)"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Total Earned</p>
                            <p class="text-2xl font-bold mt-1" x-text="formatNumber(spStats?.total_earned || 0)"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">SP Spent</p>
                            <p class="text-2xl font-bold mt-1" x-text="formatNumber(spStats?.total_spent || 0)"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">SP Saved (Hints)</p>
                            <p class="text-2xl font-bold mt-1 text-green-300"
                                x-text="formatNumber(spStats?.total_saved || 0)"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Skills with Hints</p>
                            <p class="text-2xl font-bold mt-1" x-text="skillsWithHints"></p>
                            <p class="text-xs text-primary-100 mt-1"
                                x-text="`${formatNumber(potentialSavings)} SP potential`"></p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Skill Management Sections" role="tablist">
                    <button @click="activeTab = 'inventory'" id="tab-inventory" aria-controls="panel-inventory"
                        :class="activeTab === 'inventory' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'inventory'">
                        Skill Inventory
                    </button>
                    <button @click="activeTab = 'acquisition'" id="tab-acquisition" aria-controls="panel-acquisition"
                        :class="activeTab === 'acquisition' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'acquisition'">
                        Skill Acquisition
                    </button>
                    <button @click="activeTab = 'evolution'" id="tab-evolution" aria-controls="panel-evolution"
                        :class="activeTab === 'evolution' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'evolution'">
                        Skill Evolution
                    </button>
                    <button @click="activeTab = 'planner'" id="tab-planner" aria-controls="panel-planner"
                        :class="activeTab === 'planner' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'planner'">
                        Build Planner
                    </button>
                    <button @click="activeTab = 'performance'" id="tab-performance" aria-controls="panel-performance"
                        :class="activeTab === 'performance' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        role="tab" :aria-selected="activeTab === 'performance'">
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
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div x-show="showSkillModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="closeSkillModal()"
                    class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity z-40"
                    aria-hidden="true"></div>

                {{-- Modal panel --}}
                <div x-show="showSkillModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50">

                    <template x-if="selectedSkill">
                        <div>
                            {{-- Modal Header --}}
                            <div
                                class="bg-linear-to-r from-primary-500 to-primary-600 px-6 py-4 flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-white" x-text="selectedSkill.name"></h3>
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
                                                'bg-gray-200 text-gray-900': selectedSkill.meta_tier === 'C'
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
                                    class="text-white hover:text-gray-200 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-4 space-y-4">
                                {{-- Description --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description
                                    </h4>
                                    <p class="text-gray-900 dark:text-white" x-text="selectedSkill.description"></p>
                                </div>

                                {{-- Effects --}}
                                <div x-show="selectedSkill.effects">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Effects</h4>
                                    <div class="text-gray-900 dark:text-white">
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
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Base SP Cost</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white"
                                            x-text="selectedSkill.base_sp_cost"></p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4"
                                        x-show="selectedSkill.available_hints > 0">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Discounted Cost</p>
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
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Evolution
                                        Path</h4>
                                    <div class="flex items-center gap-2">
                                        <span x-show="selectedSkill.evolves_from_id"
                                            class="text-sm text-gray-600 dark:text-gray-400">
                                            Evolves from: <span class="font-medium"
                                                x-text="selectedSkill.evolves_from_name || 'Unknown'"></span>
                                        </span>
                                        <span x-show="selectedSkill.evolves_to_id"
                                            class="text-sm text-gray-600 dark:text-gray-400">
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
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex items-center justify-end gap-3">
                                <button @click="closeSkillModal()" type="button"
                                    class="btn btn-secondary">Close</button>
                                <button x-show="!selectedSkill.is_acquired" @click="acquireSkill(selectedSkill)"
                                    :disabled="!isAdmin && (!character || (character.available_sp || 0) < (selectedSkill
                                        .discounted_cost ||
                                        selectedSkill.base_sp_cost))"
                                    class="btn btn-primary"
                                    :class="{
                                        'opacity-50 cursor-not-allowed': !isAdmin && (!character || (character
                                            .available_sp || 0) < (
                                            selectedSkill.discounted_cost || selectedSkill.base_sp_cost))
                                    }">
                                    <span x-show="isAdmin" class="mr-1">🔓</span>
                                    Acquire Skill
                                </button>
                            </div>
                        </div>
                    </template>
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
