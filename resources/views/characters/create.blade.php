@extends('layouts.app')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Characters', 'url' => route('characters.index')], ['label' => 'Create Character']]" />

    <div class="max-w-5xl mx-auto space-y-6" x-data="characterWizard()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-neutral-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Create New Character
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Set up your Umamusume for career training
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0">
                <a href="{{ route('characters.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Characters
                </a>
            </div>
        </div>

        <!-- Progress Steps (Desktop) -->
        <div class="glass-card rounded-xl p-6 hidden lg:block">
            <nav aria-label="Progress">
                <ol class="flex items-center justify-between">
                    @php
                        $steps = [
                            [
                                'id' => 1,
                                'name' => 'Basic Info',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                            ],
                            [
                                'id' => 2,
                                'name' => 'Stats',
                                'icon' =>
                                    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            ],
                            [
                                'id' => 3,
                                'name' => 'Aptitudes',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            [
                                'id' => 4,
                                'name' => 'Review',
                                'icon' =>
                                    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                            ],
                        ];
                    @endphp
                    @foreach ($steps as $index => $step)
                        <li class="relative {{ $index < count($steps) - 1 ? 'pr-8 sm:pr-20' : '' }} flex-1">
                            @if ($index < count($steps) - 1)
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full"
                                        :class="currentStep > {{ $step['id'] }} ? 'bg-primary-600' :
                                            'bg-neutral-200 dark:bg-neutral-700'">
                                    </div>
                                </div>
                            @endif
                            <button type="button" @click="goToStep({{ $step['id'] }})"
                                class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                                :class="currentStep === {{ $step['id'] }} ?
                                    'bg-primary-600 ring-4 ring-primary-100 dark:ring-primary-900/30' :
                                    currentStep > {{ $step['id'] }} ? 'bg-primary-600' :
                                    'bg-neutral-200 dark:bg-neutral-700'"
                                :aria-current="currentStep === {{ $step['id'] }} ? 'step' : null">
                                <svg class="w-5 h-5"
                                    :class="currentStep >= {{ $step['id'] }} ? 'text-white' :
                                        'text-neutral-500 dark:text-neutral-400'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $step['icon'] }}" />
                                </svg>
                                <span class="sr-only">{{ $step['name'] }}</span>
                            </button>
                            <span class="absolute top-12 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs font-medium"
                                :class="currentStep === {{ $step['id'] }} ? 'text-primary-600 dark:text-primary-400' :
                                    'text-neutral-500 dark:text-neutral-400'">
                                {{ $step['name'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <!-- Mobile Progress Bar (WF-002 Spec) -->
        <div class="lg:hidden glass-card rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-neutral-900 dark:text-white"
                    x-text="'Step ' + currentStep + ' of 4: ' + getStepName(currentStep)"></span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400"
                    x-text="Math.round((currentStep / 4) * 100) + '% complete'"></span>
            </div>
            <div class="h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden" role="progressbar"
                :aria-valuenow="currentStep" aria-valuemin="1" aria-valuemax="4"
                :aria-label="'Step ' + currentStep + ' of 4'">
                <div class="h-full bg-primary-600 transition-all duration-300 rounded-full"
                    :style="'width: ' + ((currentStep / 4) * 100) + '%'"></div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if ($errors->any())
            <div class="alert alert-error">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- External Data Prefill Notice -->
        <div x-show="showExternalPrefillNotice" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        Character data loaded from External Database
                    </p>
                    <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                        Name and image have been pre-filled. You can modify them as needed.
                    </p>
                </div>
                <button @click="showExternalPrefillNotice = false" class="text-green-500 hover:text-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 rounded"
                    aria-label="Dismiss pre-filled data notice">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('characters.store') }}" id="character-form">
            @csrf

            <!-- Step 1: Basic Information -->
            <section x-show="currentStep === 1" x-transition class="card rounded-xl" role="region"
                aria-labelledby="step-1-heading" aria-live="polite">
                <header class="card-header">
                    <h2 id="step-1-heading" class="text-lg font-medium text-neutral-900 dark:text-white">Basic Information
                    </h2>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Enter your character's name and select a
                        scenario</p>
                </header>
                <div class="card-body space-y-6">

                    <!-- Validation Error Display (WCAG 2.2 AA) -->
                    <template x-if="Object.keys(validationErrors).length > 0 && currentStep === 1">
                        <div class="bg-error-50 dark:bg-error-900/20 border border-error-200 dark:border-error-800 rounded-lg p-4"
                            role="alert" aria-live="polite" aria-atomic="true">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-error-500 mt-0.5 shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-medium text-error-900 dark:text-error-100 mb-2">Please fix the
                                        following errors:</h3>
                                    <ul class="space-y-1 text-sm text-error-800 dark:text-error-200">
                                        <template x-for="(error, field) in validationErrors" :key="field">
                                            <li>• <span x-text="error"></span></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Toggle Button for Database -->
                    <div
                        class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/10 rounded-lg border border-primary-100 dark:border-primary-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white dark:bg-neutral-800 rounded-lg shadow-xs">
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white">Character Database</h4>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    <span x-show="!showDatabase">Find and autofill from 60+ characters</span>
                                    <span x-show="showDatabase">Select a character to autofill details</span>
                                </p>
                            </div>
                        </div>
                        <button type="button"
                            @click="showDatabase = !showDatabase; if(showDatabase) showExternalApi = false"
                            class="btn btn-primary btn-sm">
                            <span x-show="!showDatabase" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                                Open Database
                            </span>
                            <span x-show="showDatabase" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7" />
                                </svg>
                                Close Database
                            </span>
                        </button>
                    </div>

                    <!-- Database Search Panel - Hidden by Default -->
                    <div x-show="showDatabase" x-transition
                        class="space-y-4 p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg bg-neutral-50/50 dark:bg-neutral-800/50">

                        {{-- Selected Character Banner (shown when a trainee is selected) --}}
                        <div x-show="formData.trainee" x-transition
                            class="flex items-center gap-3 p-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800">
                            <div class="shrink-0 w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center overflow-hidden border border-primary-200 dark:border-primary-700">
                                <img :src="formData.trainee?.image" :alt="formData.trainee?.name"
                                    loading="lazy" decoding="async"
                                    x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex'"
                                    class="w-10 h-10 object-cover">
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-300 hidden items-center justify-center w-full h-full"
                                    x-text="formData.trainee?.name ? formData.trainee.name.substring(0, 2).toUpperCase() : '??'"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-primary-900 dark:text-primary-100 truncate"
                                    x-text="'Selected: ' + (formData.trainee?.name || '')"></p>
                                <p class="text-xs text-primary-700 dark:text-primary-300"
                                    x-text="(formData.trainee?.distance || '') + ' · ' + (formData.trainee?.surface || '') + ' · ' + (formData.trainee?.style || '')"></p>
                            </div>
                            <svg class="w-5 h-5 text-primary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        {{-- Search & Grid --}}
                        <div class="space-y-3">
                            @include('characters.partials.database-search')
                        </div>

                        <input type="hidden" name="trainee_id" :value="formData.trainee?.id || ''">
                    </div>

                    <!-- Manual Input Fields -->
                    <div class="pt-2">
                        @include('characters.partials.basic-info-fields')
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" disabled class="btn btn-secondary opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" :disabled="!formData.name || !formData.scenario_type"
                        class="btn btn-primary"
                        :class="!formData.name || !formData.scenario_type ? 'opacity-50 cursor-not-allowed' : ''">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Step 2: Stats -->
            <section x-show="currentStep === 2" x-transition class="card rounded-xl" role="region"
                aria-labelledby="step-2-heading" aria-live="polite">
                <header class="card-header">
                    <h2 id="step-2-heading" class="text-lg font-medium text-neutral-900 dark:text-white">Current Stats</h2>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Set initial stat values (soft cap at 1,200 — stats can exceed with diminishing returns)</p>
                </header>
                <div class="card-body">
                    @php
                        $stats = [
                            'speed' => [
                                'label' => 'Speed',
                                'priority' => '★★★★★',
                                'color' => 'blue',
                                'desc' => 'Acceleration & top speed',
                            ],
                            'stamina' => [
                                'label' => 'Stamina',
                                'priority' => '★★★★',
                                'color' => 'orange',
                                'desc' => 'Endurance for long races',
                            ],
                            'power' => [
                                'label' => 'Power',
                                'priority' => '★★★',
                                'color' => 'red',
                                'desc' => 'Uphill & final sprint',
                            ],
                            'guts' => [
                                'label' => 'Guts',
                                'priority' => '★',
                                'color' => 'pink',
                                'desc' => 'Maintain position',
                            ],
                            'wit' => [
                                'label' => 'Wit',
                                'priority' => '★★',
                                'color' => 'green',
                                'desc' => 'Skill activation rate',
                            ],
                        ];
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($stats as $stat => $info)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="stat_{{ $stat }}" class="form-label flex items-center gap-2">
                                        <span class="font-semibold">{{ $info['label'] }}</span>
                                        <span class="text-xs text-primary-500"
                                            title="Priority">{{ $info['priority'] }}</span>
                                    </label>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $info['desc'] }}</span>
                                </div>
                                <div class="relative">
                                    <input type="number" id="stat_{{ $stat }}"
                                        name="stats[{{ $stat }}]"
                                        x-model.number="formData.stats.{{ $stat }}" min="0"
                                        max="2000" step="10" class="form-input pr-16 text-lg font-semibold"
                                        @input="validateStat('{{ $stat }}')">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm font-medium px-2 py-1 rounded"
                                            :class="getGradeColor(formData.stats.{{ $stat }})"
                                            x-text="getGrade(formData.stats.{{ $stat }})"></span>
                                    </div>
                                </div>
                                <div class="h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-{{ $info['color'] }}-500 transition-all duration-300"
                                        :style="`width: ${Math.min((formData.stats.{{ $stat }} / 1200) * 100, 100)}%`"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div
                        class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-semibold mb-1">Stat Information:</p>
                                <p class="text-xs">Set your character's initial stats. Stats above 1,200 gain at 50% effectiveness (soft cap).</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" @click="previousStep()" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" class="btn btn-primary">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Step 3: Aptitudes -->
            <section x-show="currentStep === 3" x-transition class="card rounded-xl" role="region"
                aria-labelledby="step-3-heading" aria-live="polite">
                <header class="card-header">
                    <h2 id="step-3-heading" class="text-lg font-medium text-neutral-900 dark:text-white">Aptitudes</h2>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Select aptitude grades for distance, surface,
                        and running style</p>
                </header>
                <div class="card-body">
                    {{-- VERIFIED (Jan 2026): S is the maximum aptitude grade. SS does NOT exist. --}}
                    @php $grades = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S']; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Distance -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-primary-500">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <h4 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
                                    Distance</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['sprint' => 'Sprint (1000-1400m)', 'mile' => 'Mile (1401-1800m)', 'medium' => 'Medium (1801-2400m)', 'long' => 'Long (2401m+)'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[distance][{{ $key }}]"
                                            x-model="formData.aptitudes.distance.{{ $key }}"
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Surface -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-green-500">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
                                    Surface</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['turf' => 'Turf', 'dirt' => 'Dirt'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[surface][{{ $key }}]"
                                            x-model="formData.aptitudes.surface.{{ $key }}"
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Running Style -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-purple-500">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h4 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
                                    Running Style</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['front_runner' => 'Front Runner', 'pace_chaser' => 'Pace Chaser', 'late_surger' => 'Late Surger', 'end_closer' => 'End Closer'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[style][{{ $key }}]"
                                            x-model="formData.aptitudes.style.{{ $key }}"
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" @click="previousStep()" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" :disabled="!isStep3Valid()" class="btn btn-primary"
                        :class="!isStep3Valid() ? 'opacity-50 cursor-not-allowed' : ''">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Step 4: Review -->
            <section x-show="currentStep === 4" x-transition class="card rounded-xl" role="region"
                aria-labelledby="step-4-heading" aria-live="polite">
                <header class="card-header">
                    <h2 id="step-4-heading" class="text-lg font-medium text-neutral-900 dark:text-white">Review & Confirm
                    </h2>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Review your character details before creating
                    </p>
                </header>
                <div class="card-body space-y-6">
                    <!-- Basic Info Summary -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Basic Information</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <!-- Avatar Preview -->
                            <div x-show="formData.avatar_preview" class="flex items-center gap-4">
                                <dt class="text-xs text-neutral-500 dark:text-neutral-400">Avatar</dt>
                                <dd>
                                    <div
                                        class="w-24 h-24 rounded-full border-2 border-primary-400 dark:border-primary-500 overflow-hidden relative bg-neutral-200 dark:bg-neutral-700">
                                        <!-- Replicate the editor's 256px container scaled down to 96px (96/256 = 0.375) -->
                                        <div class="absolute"
                                            style="width: 256px; height: 256px; left: 50%; top: 50%; transform: translate(-50%, -50%) scale(0.375); transform-origin: center center;">
                                            <div class="absolute inset-0"
                                                :style="`transform: translate(${formData.imageX}px, ${formData.imageY}px);`">
                                                <img :src="formData.avatar_preview" alt="Character avatar" loading="lazy"
                                                    decoding="async" width="256" height="256" class="w-full h-full object-cover"
                                                    x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(formData.name || 'User') + '&background=random'"
                                                    :style="`transform: scale(${formData.imageZoom}) rotate(${formData.imageRotation}deg) scaleX(${formData.imageFlipH ? -1 : 1}); transform-origin: center center;`">
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-neutral-500 dark:text-neutral-400">Full Character Name</dt>
                                <dd class="text-base font-bold text-neutral-900 dark:text-white">
                                    <span x-show="formData.title" class="text-primary-600 dark:text-primary-400"
                                        x-text="'[' + formData.title + '] '"></span>
                                    <span x-text="formData.name || 'Not set'"></span>
                                </dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-neutral-500 dark:text-neutral-400">Scenario</dt>
                                    <dd class="text-sm font-medium text-neutral-900 dark:text-white"
                                        x-text="formData.scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'"></dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <!-- Stats Summary -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Stats</h4>
                        <div class="grid grid-cols-5 gap-3">
                            <template x-for="(value, stat) in formData.stats" :key="stat">
                                <div class="text-center">
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase mb-1" x-text="stat">
                                    </div>
                                    <div class="text-lg font-bold text-neutral-900 dark:text-white" x-text="value"></div>
                                    <div class="text-xs font-medium px-2 py-0.5 rounded inline-block mt-1"
                                        :class="getGradeColor(value)" x-text="getGrade(value)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Aptitudes Summary -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Aptitudes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            <div>
                                <div class="font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Distance</div>
                                <template x-for="(value, key) in formData.aptitudes.distance" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-neutral-600 dark:text-neutral-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-neutral-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Surface</div>
                                <template x-for="(value, key) in formData.aptitudes.surface" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-neutral-600 dark:text-neutral-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-neutral-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Running Style</div>
                                <template x-for="(value, key) in formData.aptitudes.style" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-neutral-600 dark:text-neutral-400 capitalize"
                                            x-text="key.replace('_', ' ')"></span>
                                        <span class="font-medium text-neutral-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <div class="flex gap-2">
                        <button type="button" @click="previousStep()" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        <button type="button"
                            @click="if(confirm('Clear all draft data? This cannot be undone.')) { clearStorage(); location.reload(); }"
                            class="btn btn-outline text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Draft
                        </button>
                    </div>
                    <button type="submit" @click="clearStorage()" class="btn btn-primary px-4 py-2">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Character
                    </button>
                </div>
            </section>
        </form>
    </div>

    <script id="page-data" type="application/json">
        {!! json_encode([
            'trainees' => $trainees ?? [],
            'routes' => [
                'store' => route('characters.store')
            ],
            'externalPrefill' => $externalPrefill ?? null
        ]) !!}
    </script>

    @vite(['resources/js/pages/characters/create.js'])
@endsection
