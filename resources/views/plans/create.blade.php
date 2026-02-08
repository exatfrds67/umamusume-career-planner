@extends('layouts.app')

@section('title', 'Create Plan')

@section('content')
    <div class="relative z-10 container mx-auto px-4 py-8">
        {{-- Wizard Container --}}
        <div x-data="planWizard({ mode: 'create' })" x-init="init()" class="max-w-5xl mx-auto">

            {{-- Header with Title and Draft Actions --}}
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Career Plan</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Plan your character's training journey step by step
                        </p>
                    </div>

                    {{-- Critical Alert Badge for Training Screen --}}
                    <x-ai.critical-alert-badge :alert-count="0" size="lg" />
                </div>

                {{-- Draft Actions --}}
                <div class="flex items-center gap-2">
                    <button type="button" @click="loadDraft()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-colors duration-200">
                        <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Load Draft
                    </button>

                    <button type="button" @click="saveDraft()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-colors duration-200">
                        <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save Draft
                    </button>
                </div>
            </div>

            {{-- Stepper Component --}}
            <x-stepper :steps="['Character', 'Goals', 'Skills', 'Races', 'Review']" x-bind:current="currentStep" x-bind:completed="completedSteps" class="mb-8" />

            {{-- Error Display --}}
            <div x-show="currentStepErrors.length > 0" x-transition class="mb-6">
                <x-alert-banner variant="error" x-bind:message="currentStepErrors.join(', ')" />
            </div>

            {{-- Wizard Content Card --}}
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-lg p-6 mb-6">

                {{-- Step 1: Character Selection --}}
                <div x-show="currentStep === 0" x-transition class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        Select Character
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Choose the character you want to create a training plan for. This will determine available aptitudes
                        and growth rates.
                    </p>

                    {{-- Character List Component --}}
                    <x-character-list :characters="$characters" :columns="3" show-search show-filters
                        @character-selected="selectCharacter($event.detail.character)" />

                    {{-- Selected Character Preview --}}
                    <div x-show="plan.character_id" x-transition
                        class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center gap-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Selected:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="plan.character_name"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Goal Setting --}}
                <div x-show="currentStep === 1" x-transition class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        Set Training Goals
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Define your target stats for this training plan. You must set at least one stat goal.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Speed Goal --}}
                        <div>
                            <label for="goal-speed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Speed
                            </label>
                            <input type="number" id="goal-speed" x-model.number="plan.goals.target_speed"
                                @input="updateGoal('target_speed', $event.target.value)" min="0" max="1200"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 800" />
                        </div>

                        {{-- Stamina Goal --}}
                        <div>
                            <label for="goal-stamina"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Stamina
                            </label>
                            <input type="number" id="goal-stamina" x-model.number="plan.goals.target_stamina"
                                @input="updateGoal('target_stamina', $event.target.value)" min="0" max="1200"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 600" />
                        </div>

                        {{-- Power Goal --}}
                        <div>
                            <label for="goal-power" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Power
                            </label>
                            <input type="number" id="goal-power" x-model.number="plan.goals.target_power"
                                @input="updateGoal('target_power', $event.target.value)" min="0" max="1200"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 700" />
                        </div>

                        {{-- Guts Goal --}}
                        <div>
                            <label for="goal-guts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Guts
                            </label>
                            <input type="number" id="goal-guts" x-model.number="plan.goals.target_guts"
                                @input="updateGoal('target_guts', $event.target.value)" min="0" max="1200"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 500" />
                        </div>

                        {{-- Wit Goal --}}
                        <div>
                            <label for="goal-wit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Wit
                            </label>
                            <input type="number" id="goal-wit" x-model.number="plan.goals.target_wit"
                                @input="updateGoal('target_wit', $event.target.value)" min="0" max="1200"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 600" />
                        </div>

                        {{-- SP Goal --}}
                        <div>
                            <label for="goal-sp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-tooltip content="Total SP available for skill acquisition" position="top">
                                    <span class="inline-flex items-center gap-1">
                                        Target Total SP
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                </x-tooltip>
                            </label>
                            <input type="number" id="goal-sp" x-model.number="plan.goals.target_total_sp"
                                @input="updateGoal('target_total_sp', $event.target.value)" min="0" max="500"
                                class="w-full px-4 py-2 text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                placeholder="e.g., 300" />
                        </div>
                    </div>
                </div>

                {{-- Step 3: Skill Selection --}}
                <div x-show="currentStep === 2" x-transition class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        Select Skills (Optional)
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Choose the skills you want to acquire during training. This step is optional.
                    </p>

                    {{-- Skill Shop List Component --}}
                    <x-skill-shop-list :skills="$skills" show-search show-filters selectable />

                    {{-- Selected Skills Summary --}}
                    <div x-show="plan.skills.length > 0" x-transition
                        class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center gap-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    <span x-text="plan.skills.length"></span> skill<span
                                        x-show="plan.skills.length !== 1">s</span> selected
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Race Planning --}}
                <div x-show="currentStep === 3" x-transition class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        Plan Races (Optional)
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Select which races you plan to participate in during training. This step is optional.
                    </p>

                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <p>Race planning interface coming soon</p>
                        <p class="text-sm mt-2">For now, you can skip this step</p>
                    </div>
                </div>

                {{-- Step 5: Review & Submit --}}
                <div x-show="currentStep === 4" x-transition class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        Review Your Plan
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Review your training plan before submitting. You can go back to edit any step.
                    </p>

                    {{-- Summary Card --}}
                    <div class="space-y-4">
                        {{-- Character Summary --}}
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Character</h3>
                            <p class="text-gray-600 dark:text-gray-400" x-text="plan.character_name || 'Not selected'">
                            </p>
                        </div>

                        {{-- Goals Summary --}}
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Training Goals</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <div x-show="plan.goals.target_speed">
                                    <span class="text-gray-600 dark:text-gray-400">Speed:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_speed"></span>
                                </div>
                                <div x-show="plan.goals.target_stamina">
                                    <span class="text-gray-600 dark:text-gray-400">Stamina:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_stamina"></span>
                                </div>
                                <div x-show="plan.goals.target_power">
                                    <span class="text-gray-600 dark:text-gray-400">Power:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_power"></span>
                                </div>
                                <div x-show="plan.goals.target_guts">
                                    <span class="text-gray-600 dark:text-gray-400">Guts:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_guts"></span>
                                </div>
                                <div x-show="plan.goals.target_wit">
                                    <span class="text-gray-600 dark:text-gray-400">Wit:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_wit"></span>
                                </div>
                                <div x-show="plan.goals.target_total_sp">
                                    <span class="text-gray-600 dark:text-gray-400">Total SP:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1"
                                        x-text="plan.goals.target_total_sp"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Skills Summary --}}
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Selected Skills</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                <span x-text="plan.skills.length || 0"></span> skill<span
                                    x-show="plan.skills.length !== 1">s</span>
                            </p>
                        </div>

                        {{-- Races Summary --}}
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Planned Races</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                <span x-text="plan.races.length || 0"></span> race<span
                                    x-show="plan.races.length !== 1">s</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="flex items-center justify-between">
                <button type="button" @click="prevStep()" x-show="!isFirstStep" :disabled="isFirstStep"
                    class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-all duration-200">
                    <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>

                <div class="flex items-center gap-3">
                    @if (!Auth::guest())
                        <a href="/plans"
                            class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-colors duration-200">
                            Cancel
                        </a>
                    @endif

                    <button type="button" @click="isLastStep ? submit() : nextStep()"
                        :disabled="!canProceed || isSubmitting"
                        class="px-6 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-all duration-200">
                        <span x-show="!isSubmitting" x-text="isLastStep ? 'Create Plan' : 'Next'"></span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <x-spinner class="w-4 h-4" />
                            Creating...
                        </span>
                        <svg x-show="!isLastStep && !isSubmitting" class="w-4 h-4 inline-block ml-1.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
