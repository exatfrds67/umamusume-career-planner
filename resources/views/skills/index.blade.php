@extends('layouts.app')

@section('title', 'Skill Management')

@section('content')
    <div class="space-y-6" x-data="skillManagement()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Skill Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage skills, track hints, and optimize SP allocation with AI-powered recommendations
                </p>
            </div>

            <div class="flex items-center gap-3">
                {{-- Character Selector --}}
                <select x-model="selectedCharacterId" @change="loadCharacterData()"
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
        </div>

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
            <div class="card bg-linear-to-br from-primary-500 to-primary-600 text-white">
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Available SP</p>
                            <p class="text-3xl font-bold mt-1" x-text="character?.available_sp || 0"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Total SP Earned</p>
                            <p class="text-3xl font-bold mt-1" x-text="spStats?.total_earned || 0"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">SP Spent</p>
                            <p class="text-3xl font-bold mt-1" x-text="spStats?.total_spent || 0"></p>
                        </div>
                        <div>
                            <p class="text-primary-100 text-sm font-medium">SP Saved (Hints)</p>
                            <p class="text-3xl font-bold mt-1 text-green-300" x-text="spStats?.total_saved || 0"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'inventory'"
                        :class="activeTab === 'inventory' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" role="tab"
                        :aria-selected="activeTab === 'inventory'">
                        Skill Inventory
                    </button>
                    <button @click="activeTab = 'acquisition'"
                        :class="activeTab === 'acquisition' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" role="tab"
                        :aria-selected="activeTab === 'acquisition'">
                        Skill Acquisition
                    </button>
                    <button @click="activeTab = 'evolution'"
                        :class="activeTab === 'evolution' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" role="tab"
                        :aria-selected="activeTab === 'evolution'">
                        Skill Evolution
                    </button>
                    <button @click="activeTab = 'planner'"
                        :class="activeTab === 'planner' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" role="tab"
                        :aria-selected="activeTab === 'planner'">
                        Build Planner
                    </button>
                    <button @click="activeTab = 'performance'"
                        :class="activeTab === 'performance' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" role="tab"
                        :aria-selected="activeTab === 'performance'">
                        Agent Performance
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="mt-6">
                {{-- Skill Inventory Tab --}}
                <div x-show="activeTab === 'inventory'" role="tabpanel">
                    @include('skills.partials.inventory')
                </div>

                {{-- Skill Acquisition Tab --}}
                <div x-show="activeTab === 'acquisition'" role="tabpanel">
                    @include('skills.partials.acquisition')
                </div>

                {{-- Skill Evolution Tab --}}
                <div x-show="activeTab === 'evolution'" role="tabpanel">
                    @include('skills.partials.evolution')
                </div>

                {{-- Build Planner Tab --}}
                <div x-show="activeTab === 'planner'" role="tabpanel">
                    @include('skills.partials.planner')
                </div>

                {{-- Agent Performance Tab --}}
                <div x-show="activeTab === 'performance'" role="tabpanel">
                    @include('skills.partials.performance')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function skillManagement() {
                return {
                    // State
                    selectedCharacterId: '',
                    character: null,
                    skills: [],
                    acquiredSkills: [],
                    hints: [],
                    evolutionOpportunities: [],
                    spStats: {},
                    agentPerformance: {},
                    loading: false,
                    activeTab: 'inventory',

                    // Filters
                    filters: {
                        skillType: 'all',
                        rarity: 'all',
                        metaTier: 'all',
                        searchQuery: ''
                    },

                    // Initialize
                    init() {
                        // Load from URL params if present
                        const urlParams = new URLSearchParams(window.location.search);
                        const characterId = urlParams.get('character');
                        if (characterId) {
                            this.selectedCharacterId = characterId;
                            this.loadCharacterData();
                        }
                    },

                    // Load character data
                    async loadCharacterData() {
                        if (!this.selectedCharacterId) return;

                        this.loading = true;
                        try {
                            // Update URL
                            const url = new URL(window.location);
                            url.searchParams.set('character', this.selectedCharacterId);
                            window.history.pushState({}, '', url);

                            // Load all data in parallel
                            await Promise.all([
                                this.loadCharacter(),
                                this.loadSkills(),
                                this.loadHints(),
                                this.loadEvolutionOpportunities(),
                                this.loadSPStats(),
                                this.loadAgentPerformance()
                            ]);
                        } catch (error) {
                            console.error('Error loading character data:', error);
                            this.showError('Failed to load character data');
                        } finally {
                            this.loading = false;
                        }
                    },

                    // Load character
                    async loadCharacter() {
                        const response = await fetch(`/api/characters/${this.selectedCharacterId}`);
                        if (!response.ok) throw new Error('Failed to load character');
                        const data = await response.json();
                        this.character = data.data;
                    },

                    // Load skills
                    async loadSkills() {
                        const response = await fetch('/api/skills');
                        if (!response.ok) throw new Error('Failed to load skills');
                        const data = await response.json();
                        this.skills = data.data;

                        // Separate acquired skills
                        this.acquiredSkills = this.skills.filter(s => s.is_acquired);
                    },

                    // Load hints
                    async loadHints() {
                        const response = await fetch(`/api/characters/${this.selectedCharacterId}/skill-hints`);
                        if (!response.ok) throw new Error('Failed to load hints');
                        const data = await response.json();
                        this.hints = data.data;
                    },

                    // Load evolution opportunities
                    async loadEvolutionOpportunities() {
                        const response = await fetch(
                            `/api/characters/${this.selectedCharacterId}/skill-evolution/opportunities`);
                        if (!response.ok) throw new Error('Failed to load evolution opportunities');
                        const data = await response.json();
                        this.evolutionOpportunities = data.data;
                    },

                    // Load SP statistics
                    async loadSPStats() {
                        const response = await fetch(`/api/characters/${this.selectedCharacterId}/skill-hints/statistics`);
                        if (!response.ok) throw new Error('Failed to load SP statistics');
                        const data = await response.json();
                        this.spStats = data.data;
                    },

                    // Load agent performance
                    async loadAgentPerformance() {
                        const response = await fetch(`/api/characters/${this.selectedCharacterId}/agent-performance`);
                        if (!response.ok) throw new Error('Failed to load agent performance');
                        const data = await response.json();
                        this.agentPerformance = data.data;
                    },

                    // Refresh all data
                    async refreshData() {
                        if (this.selectedCharacterId) {
                            await this.loadCharacterData();
                            this.showSuccess('Data refreshed successfully');
                        }
                    },

                    // Filter skills
                    get filteredSkills() {
                        let filtered = this.skills;

                        if (this.filters.skillType !== 'all') {
                            filtered = filtered.filter(s => s.skill_type === this.filters.skillType);
                        }

                        if (this.filters.rarity !== 'all') {
                            filtered = filtered.filter(s => s.rarity === this.filters.rarity);
                        }

                        if (this.filters.metaTier !== 'all') {
                            filtered = filtered.filter(s => s.meta_tier === this.filters.metaTier);
                        }

                        if (this.filters.searchQuery) {
                            const query = this.filters.searchQuery.toLowerCase();
                            filtered = filtered.filter(s =>
                                s.name.toLowerCase().includes(query) ||
                                s.description?.toLowerCase().includes(query)
                            );
                        }

                        return filtered;
                    },

                    // Utility functions
                    showSuccess(message) {
                        // Implement toast notification
                        console.log('Success:', message);
                    },

                    showError(message) {
                        // Implement toast notification
                        console.error('Error:', message);
                    },

                    formatNumber(num) {
                        return new Intl.NumberFormat().format(num);
                    }
                }
            }
        </script>
    @endpush
@endsection
