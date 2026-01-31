
<div class="space-y-6" x-data="buildPlanner()">
    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Build Templates</h3>
                <button @click="createCustomBuild()" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Custom Build
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="template in buildTemplates" :key="template.id">
                    <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                        :class="selectedTemplate?.id === template.id ?
                            'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                            'border-gray-200 dark:border-gray-700'"
                        @click="selectTemplate(template)">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="template.name"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="template.category"></p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                :class="{
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': template
                                        .meta_tier === 'S+' || template.meta_tier === 'S',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': template
                                        .meta_tier === 'A',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': template
                                        .meta_tier === 'B'
                                }"
                                x-text="template.meta_tier"></span>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="template.description"></p>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Total Skills</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="template.skill_count"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Total SP Cost</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="`${template.total_sp_cost} SP`"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Potential Savings</span>
                                <span class="font-medium text-green-600 dark:text-green-400"
                                    x-text="`${template.potential_savings} SP`"></span>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-1">
                                <template x-for="tag in template.tags" :key="tag">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                        x-text="tag"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div x-show="selectedTemplate" class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="selectedTemplate?.name">
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="selectedTemplate?.description"></p>
                </div>
                <button @click="getAIOptimization()" :disabled="loading" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Get AI Optimization
                </button>
            </div>
        </div>
        <div class="card-body">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Skills</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="selectedTemplate?.skill_count">
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Base SP Cost</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"
                        x-text="selectedTemplate?.total_sp_cost"></p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <p class="text-sm text-green-600 dark:text-green-400 mb-1">With Hints</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-300"
                        x-text="selectedTemplate?.optimized_cost"></p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                    <p class="text-sm text-purple-600 dark:text-purple-400 mb-1">Total Savings</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-300"
                        x-text="selectedTemplate?.potential_savings"></p>
                </div>
            </div>

            
            <div class="mb-6">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Skills in This Build</h4>
                <div class="space-y-2">
                    <template x-for="(skill, index) in selectedTemplate?.skills" :key="skill.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span
                                    class="shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-xs font-bold"
                                    x-text="index + 1"></span>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="skill.name"></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            :class="{
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': skill
                                                    .rarity === 'normal',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': skill
                                                    .rarity === 'rare',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': skill
                                                    .rarity === 'unique'
                                            }"
                                            x-text="skill.rarity"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400"
                                            x-text="skill.skill_type"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900 dark:text-white" x-text="`${skill.final_cost} SP`">
                                </p>
                                <p x-show="skill.hints_available > 0"
                                    class="text-xs text-green-600 dark:text-green-400"
                                    x-text="`${skill.hints_available} hints`"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            
                <div class="bg-linear-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg p-6">
                <div class="flex items-start gap-4 mb-4">
                    <div class="shrink-0">
                        <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI Optimization Analysis
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4" x-text="aiOptimization?.summary"></p>

                        
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Efficiency Score</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                                        x-text="aiOptimization?.efficiency_score"></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 100</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Synergy Rating</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                        x-text="aiOptimization?.synergy_rating"></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 10</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Meta Alignment</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400"
                                        x-text="aiOptimization?.meta_alignment"></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                        </div>

                        
                        <div x-show="aiOptimization?.recommendations && aiOptimization.recommendations.length > 0">
                            <h5 class="font-medium text-gray-900 dark:text-white mb-2">Recommendations</h5>
                            <ul class="space-y-2">
                                <template x-for="rec in (aiOptimization?.recommendations || [])"
                                    :key="rec">
                                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-purple-500 shrink-0 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span x-text="rec"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        
                        <div x-show="aiOptimization?.acquisition_order && aiOptimization.acquisition_order.length > 0"
                            class="mt-4">
                            <h5 class="font-medium text-gray-900 dark:text-white mb-2">Optimal Acquisition Order</h5>
                            <div class="space-y-2">
                                <template x-for="(step, index) in (aiOptimization?.acquisition_order || [])"
                                    :key="index">
                                    <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded">
                                        <span
                                            class="shrink-0 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold"
                                            x-text="index + 1"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="step"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="flex gap-2 mt-6">
                <button @click="applyBuild()" class="btn btn-primary">
                    Apply This Build
                </button>
                <button @click="saveBuild()" class="btn btn-secondary">
                    Save Build
                </button>
                <button @click="exportBuild()" class="btn btn-secondary">
                    Export Build
                </button>
            </div>
        </div>
    </div>

    
    <div class="card bg-white dark:bg-gray-800">
        <div class="card-header border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Saved Builds</h3>
        </div>
        <div class="card-body">
            <div x-show="savedBuilds.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No saved builds yet</p>
            </div>

            <div x-show="savedBuilds.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="build in savedBuilds" :key="build.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white" x-text="build.name"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="build.created_at">
                                </p>
                            </div>
                            <button @click="deleteBuild(build.id)"
                                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-2 mb-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Skills</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="build.skill_count"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Total SP</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="`${build.total_sp} SP`"></span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button @click="loadBuild(build)" class="flex-1 btn btn-sm btn-primary">
                                Load
                            </button>
                            <button @click="exportBuild(build)" class="btn btn-sm btn-secondary">
                                Export
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        function buildPlanner() {
            return {
                buildTemplates: [],
                selectedTemplate: null,
                aiOptimization: null,
                savedBuilds: [],
                loading: false,

                init() {
                    this.loadBuildTemplates();
                    this.loadSavedBuilds();
                },

                async loadBuildTemplates() {
                    // Load predefined build templates
                    this.buildTemplates = [{
                            id: 1,
                            name: 'Speed Specialist',
                            category: 'Sprint/Mile',
                            description: 'Optimized for short-distance races with maximum speed focus',
                            meta_tier: 'S+',
                            skill_count: 8,
                            total_sp_cost: 1200,
                            optimized_cost: 840,
                            potential_savings: 360,
                            tags: ['Speed', 'Sprint', 'Meta'],
                            skills: []
                        },
                        // Add more templates...
                    ];
                },

                async loadSavedBuilds() {
                    // Load user's saved builds
                    this.savedBuilds = [];
                },

                selectTemplate(template) {
                    this.selectedTemplate = template;
                    this.aiOptimization = null;
                },

                async getAIOptimization() {
                    this.loading = true;
                    try {
                        // Call AI optimization API
                        this.aiOptimization = {
                            summary: 'This build is highly optimized for sprint races...',
                            efficiency_score: 92,
                            synergy_rating: 8.5,
                            meta_alignment: 95,
                            recommendations: [
                                'Consider adding "Corner Master" for better positioning',
                                'Collect hints for "Lane Legerdemain" before acquisition'
                            ],
                            acquisition_order: [
                                'Acquire "Go with the Flow" first (120 SP)',
                                'Collect hints for evolution',
                                'Evolve to "Lane Legerdemain" (108 SP with hints)'
                            ]
                        };
                    } finally {
                        this.loading = false;
                    }
                },

                async applyBuild() {
                    // Apply the selected build
                    console.log('Applying build:', this.selectedTemplate);
                },

                async saveBuild() {
                    // Save the current build
                    console.log('Saving build:', this.selectedTemplate);
                },

                async exportBuild(build = null) {
                    // Export build as JSON
                    const buildToExport = build || this.selectedTemplate;
                    console.log('Exporting build:', buildToExport);
                },

                async loadBuild(build) {
                    this.selectedTemplate = build;
                },

                async deleteBuild(buildId) {
                    if (confirm('Are you sure you want to delete this build?')) {
                        this.savedBuilds = this.savedBuilds.filter(b => b.id !== buildId);
                    }
                },

                createCustomBuild() {
                    // Open custom build creator
                    console.log('Creating custom build');
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/skills/partials/planner.blade.php ENDPATH**/ ?>