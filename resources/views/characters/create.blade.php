@extends('layouts.app')

@section('content')
    <!-- Desktop Sidebar Stepper (lg+ screens, WF-002 Spec) -->
    <aside
        class="hidden lg:block fixed left-0 top-16 h-[calc(100%-4rem)] w-56 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 shadow-sm z-40 overflow-y-auto"
        x-data="{ steps: ['Basic Info', 'Stats', 'Aptitudes', 'Review'], descriptions: ['Name & scenario', 'Initial stats setup', 'Distance, surface, style', 'Review & confirm'] }">
        <nav class="space-y-2 p-4" role="navigation" aria-label="Wizard steps">
            <template x-for="(step, index) in steps" :key="index">
                <button type="button" @click="$root.goToStep && $root.goToStep(index + 1)"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-left group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                    :class="$root.currentStep === index + 1 ?
                        'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 font-semibold' :
                        'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    :aria-current="$root.currentStep === index + 1 ? 'step' : false"
                    :aria-label="`Step ${index + 1}: ${step} ${$root.currentStep === index + 1 ? '(current)' : ($root.currentStep > index + 1 ? '(completed)' : '')}`">

                    <!-- Step number circle -->
                    <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-semibold transition-colors"
                        :class="$root.currentStep === index + 1 ?
                            'bg-primary-500 text-white' :
                            $root.currentStep > index + 1 ?
                            'bg-success-500 text-white' :
                            'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">

                        <!-- Checkmark for completed steps -->
                        <svg x-show="$root.currentStep > index + 1" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>

                        <!-- Step number for current/future steps -->
                        <span x-show="$root.currentStep <= index + 1" x-text="index + 1"></span>
                    </div>

                    <!-- Step label -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" x-text="step"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="descriptions[index]"></p>
                    </div>
                </button>
            </template>
        </nav>
    </aside>

    <div class="lg:ml-60 max-w-5xl mx-auto space-y-6" x-data="characterWizard()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Create New Character
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
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
                                            'bg-gray-200 dark:bg-gray-700'">
                                    </div>
                                </div>
                            @endif
                            <button type="button" @click="goToStep({{ $step['id'] }})"
                                class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                                :class="currentStep === {{ $step['id'] }} ?
                                    'bg-primary-600 ring-4 ring-primary-100 dark:ring-primary-900/30' :
                                    currentStep > {{ $step['id'] }} ? 'bg-primary-600' :
                                    'bg-gray-200 dark:bg-gray-700'"
                                :aria-current="currentStep === {{ $step['id'] }} ? 'step' : null">
                                <svg class="w-5 h-5"
                                    :class="currentStep >= {{ $step['id'] }} ? 'text-white' :
                                        'text-gray-500 dark:text-gray-400'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $step['icon'] }}" />
                                </svg>
                                <span class="sr-only">{{ $step['name'] }}</span>
                            </button>
                            <span class="absolute top-12 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs font-medium"
                                :class="currentStep === {{ $step['id'] }} ? 'text-primary-600 dark:text-primary-400' :
                                    'text-gray-500 dark:text-gray-400'">
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
                <span class="text-sm font-medium text-gray-900 dark:text-white"
                    x-text="'Step ' + currentStep + ' of 4: ' + getStepName(currentStep)"></span>
                <span class="text-xs text-gray-500 dark:text-gray-400"
                    x-text="Math.round((currentStep / 4) * 100) + '% complete'"></span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden" role="progressbar"
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
                <button @click="showExternalPrefillNotice = false" class="text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <h2 id="step-1-heading" class="text-lg font-medium text-gray-900 dark:text-white">Basic Information
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your character's name and select a
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
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Character Database</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
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

                    <!-- Database Search Grid - Hidden by Default -->
                    <div x-show="showDatabase" x-transition
                        class="grid grid-cols-1 xl:grid-cols-3 gap-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="xl:col-span-2 space-y-4">
                            @include('characters.partials.database-search')
                        </div>

                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Selected Preview</h4>
                                <div x-show="formData.trainee" class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <img :src="formData.trainee?.image" :alt="formData.trainee?.name" loading="lazy"
                                            decoding="async"
                                            x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(formData.trainee?.name || 'Chk') + '&background=random&color=fff'"
                                            class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white"
                                                x-text="formData.trainee?.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"
                                                x-text="formData.trainee?.rarity + ' • ' + formData.trainee?.distance"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-5 gap-2">
                                        <template x-for="(value, stat) in formData.trainee?.baseStats || {}"
                                            :key="stat">
                                            <div class="text-center">
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase"
                                                    x-text="stat"></div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white"
                                                    x-text="value"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <template x-for="aptitude in formData.trainee?.aptitudes || []"
                                            :key="aptitude">
                                            <span
                                                class="px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-200"
                                                x-text="aptitude"></span>
                                        </template>
                                    </div>
                                </div>
                                <div x-show="!formData.trainee" class="text-sm text-gray-500 dark:text-gray-400">
                                    Select a trainee to see the preview.
                                </div>
                                <input type="hidden" name="trainee_id" :value="formData.trainee?.id || ''">
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Button for External API -->
                    <div
                        class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">External API (umapyoi.net)
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-show="!showExternalApi">Search and import from official game data</span>
                                    <span x-show="showExternalApi">Select a character to autofill with real game
                                        data</span>
                                </p>
                            </div>
                        </div>
                        <button type="button"
                            @click="showExternalApi = !showExternalApi; if(showExternalApi) showDatabase = false"
                            class="btn btn-success btn-sm">
                            <span x-show="!showExternalApi" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                                Open External API
                            </span>
                            <span x-show="showExternalApi" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7" />
                                </svg>
                                Close External API
                            </span>
                        </button>
                    </div>

                    <!-- External API Search Grid - Hidden by Default -->
                    <div x-show="showExternalApi" x-transition
                        class="grid grid-cols-1 xl:grid-cols-3 gap-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="xl:col-span-2 space-y-4">
                            @include('characters.partials.external-api-search')
                        </div>

                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">External Character
                                    Preview</h4>
                                <div x-show="selectedExternalCharacter" class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <img :src="selectedExternalCharacter?.image"
                                            :alt="selectedExternalCharacter?.name" loading="lazy" decoding="async"
                                            x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedExternalCharacter?.name || 'Char') + '&background=random&color=fff'"
                                            class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white"
                                                x-text="selectedExternalCharacter?.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"
                                                x-text="selectedExternalCharacter?.name_jp"></p>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        <p><strong>Source:</strong> umapyoi.net</p>
                                        <p><strong>ID:</strong> <span x-text="selectedExternalCharacter?.id"></span></p>
                                        <p x-show="selectedExternalCharacter?.category"><strong>Category:</strong> <span
                                                x-text="selectedExternalCharacter?.category"></span></p>
                                    </div>
                                    <button type="button" @click="loadExternalCharacterData()"
                                        :disabled="externalDataLoading" class="btn btn-primary btn-sm w-full">
                                        <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': externalDataLoading }"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span x-show="!externalDataLoading">Load Full Data</span>
                                        <span x-show="externalDataLoading">Loading...</span>
                                    </button>
                                </div>
                                <div x-show="!selectedExternalCharacter" class="text-sm text-gray-500 dark:text-gray-400">
                                    Search and select a character to see the preview.
                                </div>
                            </div>
                        </div>
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
                    <h2 id="step-2-heading" class="text-lg font-medium text-gray-900 dark:text-white">Current Stats</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set initial stat values (0-1200)</p>
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
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $info['desc'] }}</span>
                                </div>
                                <div class="relative">
                                    <input type="number" id="stat_{{ $stat }}"
                                        name="stats[{{ $stat }}]"
                                        x-model.number="formData.stats.{{ $stat }}" min="0"
                                        max="1200" step="10" class="form-input pr-16 text-lg font-semibold"
                                        @input="validateStat('{{ $stat }}')">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm font-medium px-2 py-1 rounded"
                                            :class="getGradeColor(formData.stats.{{ $stat }})"
                                            x-text="getGrade(formData.stats.{{ $stat }})"></span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-{{ $info['color'] }}-500 transition-all duration-300"
                                        :style="`width: ${(formData.stats.{{ $stat }} / 1200) * 100}%`"></div>
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
                                <p class="text-xs">Set your character's initial stats. These will grow through training and
                                    races.</p>
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
                    <h2 id="step-3-heading" class="text-lg font-medium text-gray-900 dark:text-white">Aptitudes</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select aptitude grades for distance, surface,
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
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
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
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
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
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
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
                    <h2 id="step-4-heading" class="text-lg font-medium text-gray-900 dark:text-white">Review & Confirm
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review your character details before creating
                    </p>
                </header>
                <div class="card-body space-y-6">
                    <!-- Basic Info Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Basic Information</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <!-- Avatar Preview -->
                            <div x-show="formData.avatar_preview" class="flex items-center gap-4">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Avatar</dt>
                                <dd>
                                    <div
                                        class="w-24 h-24 rounded-full border-2 border-primary-400 dark:border-primary-500 overflow-hidden relative bg-gray-200 dark:bg-gray-700">
                                        <!-- Replicate the editor's 256px container scaled down to 96px (96/256 = 0.375) -->
                                        <div class="absolute"
                                            style="width: 256px; height: 256px; left: 50%; top: 50%; transform: translate(-50%, -50%) scale(0.375); transform-origin: center center;">
                                            <div class="absolute inset-0"
                                                :style="`transform: translate(${formData.imageX}px, ${formData.imageY}px);`">
                                                <img :src="formData.avatar_preview" alt="Character avatar" loading="lazy"
                                                    decoding="async" class="w-full h-full object-cover"
                                                    x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(formData.name || 'User') + '&background=random'"
                                                    :style="`transform: scale(${formData.imageZoom}) rotate(${formData.imageRotation}deg) scaleX(${formData.imageFlipH ? -1 : 1}); transform-origin: center center;`">
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Full Character Name</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">
                                    <span x-show="formData.title" class="text-primary-600 dark:text-primary-400"
                                        x-text="'[' + formData.title + '] '"></span>
                                    <span x-text="formData.name || 'Not set'"></span>
                                </dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Scenario</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white"
                                        x-text="formData.scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'"></dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <!-- Stats Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Stats</h4>
                        <div class="grid grid-cols-5 gap-3">
                            <template x-for="(value, stat) in formData.stats" :key="stat">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1" x-text="stat">
                                    </div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="value"></div>
                                    <div class="text-xs font-medium px-2 py-0.5 rounded inline-block mt-1"
                                        :class="getGradeColor(value)" x-text="getGrade(value)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Aptitudes Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Aptitudes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Distance</div>
                                <template x-for="(value, key) in formData.aptitudes.distance" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Surface</div>
                                <template x-for="(value, key) in formData.aptitudes.surface" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Running Style</div>
                                <template x-for="(value, key) in formData.aptitudes.style" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize"
                                            x-text="key.replace('_', ' ')"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
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

    <script>
        function characterWizard() {
            return {
                currentStep: 1,
                showGallery: false,
                showDatabase: false,
                showExternalApi: false,
                showExternalPrefillNotice: false,

                // Validation state (WCAG 2.2 AA compliance)
                validationErrors: {},
                isValidating: false,

                // Step names for mobile progress bar
                getStepName(step) {
                    const names = {
                        1: 'Basic Info',
                        2: 'Stats',
                        3: 'Aptitudes',
                        4: 'Review'
                    };
                    return names[step] || '';
                },

                // Validate current step before proceeding
                validateStep(step) {
                    this.validationErrors = {};

                    switch (step) {
                        case 1:
                            if (!this.formData.name || !this.formData.name.trim()) {
                                this.validationErrors.name = 'Character name is required';
                            }
                            if (!this.formData.scenario_type) {
                                this.validationErrors.scenario_type = 'Scenario type is required';
                            }
                            break;
                        case 2:
                            // Stats validation (optional - could add range checks)
                            break;
                        case 3:
                            // Aptitudes validation - check if at least some are selected
                            break;
                    }

                    return Object.keys(this.validationErrors).length === 0;
                },

                // Clear validation errors when moving to a step
                clearValidationErrors() {
                    this.validationErrors = {};
                },

                // External API state
                externalCharacters: [],
                selectedExternalCharacter: null,
                externalLoading: false,
                externalDataLoading: false,
                externalError: null,
                externalSearched: false,
                externalFilters: {
                    query: '',
                    category: ''
                },

                formData: {
                    title: '',
                    name: '',
                    avatar_url: '',
                    avatar_preview: '',
                    imageZoom: 1,
                    imageRotation: 0,
                    imageFlipH: false,
                    imageX: 0,
                    imageY: 0,
                    scenario_type: '',
                    external_source: null,
                    external_id: null,
                    stats: {
                        speed: 0,
                        stamina: 0,
                        power: 0,
                        guts: 0,
                        wit: 0
                    },
                    aptitudes: {
                        distance: {
                            sprint: '',
                            mile: '',
                            medium: '',
                            long: ''
                        },
                        surface: {
                            turf: '',
                            dirt: ''
                        },
                        style: {
                            front_runner: '',
                            pace_chaser: '',
                            late_surger: '',
                            end_closer: ''
                        }
                    }
                },
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,

                // New Search Filters
                filters: {
                    query: '',
                    rarity: '',
                    distance: '',
                    surface: '',
                    strategy: ''
                },

                // Trainee Database (Simulated)
                trainees: [{
                        id: 1,
                        name: "Special Week",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener", "Leader"],
                        image: "/images/trainee_images/__special_week_umamusume_drawn_by_mikawa_ayumu__c5289136bde8f5e1cb096090308a8496.jpg",
                        stats: {
                            speed: 98,
                            stamina: 93,
                            power: 94,
                            guts: 88,
                            wisdom: 91
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 2,
                        name: "Silence Suzuka",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Runner",
                        aptitudes: ["Turf", "Mile", "Medium", "Runner"],
                        image: "/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg",
                        stats: {
                            speed: 105,
                            stamina: 85,
                            power: 88,
                            guts: 80,
                            wisdom: 95
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 0,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 3,
                        name: "Tokai Teio",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader"],
                        image: "/images/trainee_images/__tokai_teio_umamusume_drawn_by_so_on__305c01834a0c0cf3fe3593c281a0b05b.jpg",
                        stats: {
                            speed: 100,
                            stamina: 90,
                            power: 90,
                            guts: 85,
                            wisdom: 92
                        },
                        growth: {
                            speed: 20,
                            stamina: 10,
                            power: 0,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 4,
                        name: "Maruzensky",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Runner",
                        aptitudes: ["Turf", "Mile", "Medium", "Runner"],
                        image: "/images/trainee_images/__maruzensky_umamusume_drawn_by_kamishima_kanon__sample-297ecca0da3990374954a514f06bea2b.jpg",
                        stats: {
                            speed: 102,
                            stamina: 88,
                            power: 92,
                            guts: 85,
                            wisdom: 95
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 5,
                        name: "Fuji Kiseki",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Mile", "Medium", "Leader", "Betweener"],
                        image: "/images/trainee_images/__fuji_kiseki_umamusume_drawn_by_snowater__79fb8292647cf289359469f2fec0ed4a.jpg",
                        stats: {
                            speed: 94,
                            stamina: 85,
                            power: 100,
                            guts: 88,
                            wisdom: 90
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 6,
                        name: "Oguri Cap",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Dirt", "Mile", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__oguri_cap_and_jacques_villeneuve_umamusume_and_1_more_drawn_by_holeecrab__sample-9628095fc1e0ee5bcc8c96c47d5722a1.jpg",
                        stats: {
                            speed: 100,
                            stamina: 92,
                            power: 105,
                            guts: 90,
                            wisdom: 85
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 7,
                        name: "Gold Ship",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Long",
                        style: "Chaser",
                        aptitudes: ["Turf", "Medium", "Long", "Chaser"],
                        image: "/images/trainee_images/__gold_ship_umamusume_drawn_by_advarcher__sample-2713426899554240b99dc00440e97745.jpg",
                        stats: {
                            speed: 90,
                            stamina: 110,
                            power: 100,
                            guts: 95,
                            wisdom: 80
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 8,
                        name: "Vodka",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Betweener",
                        aptitudes: ["Turf", "Mile", "Medium", "Betweener", "Leader"],
                        image: "/images/trainee_images/__vodka_umamusume_drawn_by_mayata__41166bfaeb2670ae37c8785af4566d58.jpg",
                        stats: {
                            speed: 95,
                            stamina: 80,
                            power: 108,
                            guts: 85,
                            wisdom: 82
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 9,
                        name: "Daiwa Scarlet",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Runner",
                        aptitudes: ["Turf", "Mile", "Medium", "Runner", "Leader"],
                        image: "/images/trainee_images/__daiwa_scarlet_umamusume_drawn_by_kurokawa_heuy__sample-9576ae268cdddfe167c2300d5453f2cf.jpg",
                        stats: {
                            speed: 98,
                            stamina: 90,
                            power: 92,
                            guts: 95,
                            wisdom: 88
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 0,
                            guts: 20,
                            wisdom: 10
                        }
                    },
                    {
                        id: 10,
                        name: "Taiki Shuttle",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Dirt", "Sprint", "Mile", "Leader", "Runner"],
                        image: "/images/trainee_images/__taiki_shuttle_umamusume_drawn_by_kitsutsuki_dzgu4744__2923a6d9bcc13a323eda812483bd7569.jpg",
                        stats: {
                            speed: 105,
                            stamina: 80,
                            power: 95,
                            guts: 85,
                            wisdom: 92
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 11,
                        name: "Grass Wonder",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Mile", "Medium", "Long", "Betweener", "Leader"],
                        image: "/images/trainee_images/__special_week_and_grass_wonder_umamusume_drawn_by_murasaki_himuro__c5cd811241a372d775e9ba2e2d09c65f.jpg",
                        stats: {
                            speed: 96,
                            stamina: 92,
                            power: 98,
                            guts: 90,
                            wisdom: 90
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 12,
                        name: "Hishi Amazon",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Chaser",
                        aptitudes: ["Turf", "Mile", "Medium", "Chaser", "Betweener"],
                        image: "/images/trainee_images/__hishi_amazon_umamusume_drawn_by_eve_on_k__a46dc4de4b4fb109f54b72bc3ae44a2c.png",
                        stats: {
                            speed: 92,
                            stamina: 88,
                            power: 102,
                            guts: 94,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 20,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 13,
                        name: "Mejiro McQueen",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader"],
                        image: "/images/trainee_images/__mejiro_mcqueen_umamusume_drawn_by_miwerjooggetser__52537bea1d60aaa40c1532613769ce26.png",
                        stats: {
                            speed: 90,
                            stamina: 105,
                            power: 92,
                            guts: 90,
                            wisdom: 95
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 14,
                        name: "El Condor Pasa",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Dirt", "Mile", "Medium", "Leader", "Betweener"],
                        image: "/images/trainee_images/__el_condor_pasa_umamusume_drawn_by_nekogusa_kinako__85cfefb3697093d2c40c9db031ab46a5.jpg",
                        stats: {
                            speed: 98,
                            stamina: 90,
                            power: 95,
                            guts: 90,
                            wisdom: 88
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 15,
                        name: "T.M. Opera O",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__t_m_opera_o_umamusume_drawn_by_eriario__7e3d265d1e3e405cf79bdce81ba87adf.jpg",
                        stats: {
                            speed: 95,
                            stamina: 100,
                            power: 94,
                            guts: 92,
                            wisdom: 90
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 16,
                        name: "Narita Brian",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener", "Leader"],
                        image: "/images/trainee_images/__narita_brian_and_biwa_hayahide_umamusume_drawn_by_hitoto__sample-e1edfe57e7e12f49d5a724698f738783.jpg",
                        stats: {
                            speed: 102,
                            stamina: 98,
                            power: 100,
                            guts: 95,
                            wisdom: 90
                        },
                        growth: {
                            speed: 10,
                            stamina: 20,
                            power: 0,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 17,
                        name: "Symboli Rudolf",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener", "Leader"],
                        image: "/images/trainee_images/__symboli_rudolf_umamusume_drawn_by_kusanagi_kaoru__da889d95df3f77d7d1b3f7105f463d36.png",
                        stats: {
                            speed: 98,
                            stamina: 100,
                            power: 95,
                            guts: 90,
                            wisdom: 98
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 18,
                        name: "Air Groove",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Mile", "Medium", "Betweener", "Leader"],
                        image: "/images/trainee_images/__air_groove_umamusume_drawn_by_nil__9c09dc404769151d63e3ddfa37513b20.jpg",
                        stats: {
                            speed: 96,
                            stamina: 88,
                            power: 98,
                            guts: 85,
                            wisdom: 90
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 19,
                        name: "Agnes Digital",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Betweener",
                        aptitudes: ["Turf", "Dirt", "Mile", "Medium", "Betweener", "Leader"],
                        image: "/images/trainee_images/__agnes_digital_umamusume_drawn_by_shinmai_kyata__65f32dbc74a3429009c967bb2cf5f7b7.png",
                        stats: {
                            speed: 95,
                            stamina: 85,
                            power: 95,
                            guts: 90,
                            wisdom: 95
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 10,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 20,
                        name: "Seiun Sky",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Runner",
                        aptitudes: ["Turf", "Medium", "Long", "Runner"],
                        image: "/images/trainee_images/__seiun_sky_umamusume_drawn_by_masaki_shino__65730ce77e97e0b5a2c44211de420fa3.jpg",
                        stats: {
                            speed: 94,
                            stamina: 98,
                            power: 85,
                            guts: 90,
                            wisdom: 105
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 21,
                        name: "Tamamo Cross",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__tamamo_cross_umamusume_drawn_by_ayrw7t1__d15ea703c90277149049cb36a6d0fa62.jpg",
                        stats: {
                            speed: 98,
                            stamina: 96,
                            power: 95,
                            guts: 92,
                            wisdom: 88
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 22,
                        name: "Fine Motion",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Mile", "Medium", "Leader"],
                        image: "/images/trainee_images/__fine_motion_umamusume_drawn_by_fuku_f2uk9u__85b37e5fc89efc667ae0f67aa20e5d73.png",
                        stats: {
                            speed: 92,
                            stamina: 85,
                            power: 90,
                            guts: 80,
                            wisdom: 100
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 15,
                            guts: 0,
                            wisdom: 15
                        }
                    },
                    {
                        id: 23,
                        name: "Biwa Hayahide",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader"],
                        image: "/images/trainee_images/__narita_brian_and_biwa_hayahide_umamusume_drawn_by_hitoto__sample-e1edfe57e7e12f49d5a724698f738783.jpg",
                        stats: {
                            speed: 95,
                            stamina: 94,
                            power: 90,
                            guts: 90,
                            wisdom: 98
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 0,
                            guts: 10,
                            wisdom: 20
                        }
                    },
                    {
                        id: 24,
                        name: "Mayano Top Gun",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Long",
                        style: "Runner",
                        aptitudes: ["Turf", "Medium", "Long", "Runner", "Leader", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__mayano_top_gun_umamusume_drawn_by_shibuki_kamone__24fecd157b3aaa34baee37d155c51862.jpg",
                        stats: {
                            speed: 90,
                            stamina: 100,
                            power: 90,
                            guts: 95,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 25,
                        name: "Manhattan Cafe",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Betweener",
                        aptitudes: ["Turf", "Long", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__manhattan_cafe_umamusume_drawn_by_omotil__bcb209e6d3735c1ea79aa9b3cc4a1aae.jpg",
                        stats: {
                            speed: 92,
                            stamina: 108,
                            power: 88,
                            guts: 95,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 30,
                            power: 0,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 26,
                        name: "Mihono Bourbon",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Runner",
                        aptitudes: ["Turf", "Mile", "Medium", "Runner"],
                        image: "/images/trainee_images/__mihono_bourbon_umamusume_drawn_by_yukke_jan__1e0736fa3d9611b5316f1a142ed55b93.jpg",
                        stats: {
                            speed: 100,
                            stamina: 95,
                            power: 95,
                            guts: 85,
                            wisdom: 88
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 27,
                        name: "Mejiro Ryan",
                        rarity: 1,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener"],
                        image: "/images/trainee_images/__mejiro_ryan_umamusume_drawn_by_otono_bkt4b__9befe399d00d71c6b9870eb0c6096245.png",
                        stats: {
                            speed: 85,
                            stamina: 88,
                            power: 95,
                            guts: 85,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 28,
                        name: "Hishi Akebono",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Sprint",
                        style: "Front",
                        aptitudes: ["Turf", "Sprint", "Mile", "Leader"],
                        image: "/images/trainee_images/__hishi_akebono_umamusume_drawn_by_buta_don__09316b18d340a98b9c72115f259eefef.png",
                        stats: {
                            speed: 95,
                            stamina: 80,
                            power: 110,
                            guts: 90,
                            wisdom: 80
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 20,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 29,
                        name: "Yukino Bijin",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Dirt", "Mile", "Medium", "Leader", "Betweener"],
                        image: "/images/trainee_images/__yukino_bijin_umamusume_drawn_by_migolu__e054e36adacdaab18766d5dcf3d662a4.jpg",
                        stats: {
                            speed: 94,
                            stamina: 85,
                            power: 90,
                            guts: 92,
                            wisdom: 95
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 0,
                            guts: 20,
                            wisdom: 10
                        }
                    },
                    {
                        id: 30,
                        name: "Rice Shower",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__rice_shower_umamusume_drawn_by_jjjsss__ab0944b4a248893cddca61ce1ee4a1e9.jpg",
                        stats: {
                            speed: 88,
                            stamina: 105,
                            power: 85,
                            guts: 100,
                            wisdom: 90
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 31,
                        name: "Ines Fujin",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Runner",
                        aptitudes: ["Turf", "Dirt", "Medium", "Runner"],
                        image: "/images/trainee_images/__ines_fujin_umamusume_drawn_by_codename47__3b98b45424e1cdbf84c1f69c9cd8baca.jpg",
                        stats: {
                            speed: 96,
                            stamina: 88,
                            power: 85,
                            guts: 94,
                            wisdom: 85
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 32,
                        name: "Agnes Tachyon",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader"],
                        image: "/images/trainee_images/__agnes_tachyon_umamusume_drawn_by_welchino__sample-1db2ca428e2545fcae81fe526d7a8e96.jpg",
                        stats: {
                            speed: 94,
                            stamina: 85,
                            power: 88,
                            guts: 85,
                            wisdom: 98
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 33,
                        name: "Admire Vega",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Chaser",
                        aptitudes: ["Turf", "Medium", "Long", "Chaser", "Betweener"],
                        image: "/images/trainee_images/__admire_vega_umamusume_drawn_by_starheart__73f0bb397f0b402e876d32e31699e7f8.png",
                        stats: {
                            speed: 95,
                            stamina: 90,
                            power: 96,
                            guts: 88,
                            wisdom: 90
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 34,
                        name: "Curren Chan",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Sprint",
                        style: "Leader",
                        aptitudes: ["Turf", "Sprint", "Mile", "Leader", "Runner"],
                        image: "/images/trainee_images/__curren_chan_umamusume_drawn_by_motsutoko__214a9ed5e49207f8f6c3daaab732e40f.jpg",
                        stats: {
                            speed: 100,
                            stamina: 80,
                            power: 95,
                            guts: 88,
                            wisdom: 90
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 35,
                        name: "Kawakami Princess",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__kawakami_princess_umamusume_drawn_by_kurokawa_heuy__f291f768b02437eb7e1db94e5560f720.jpg",
                        stats: {
                            speed: 92,
                            stamina: 85,
                            power: 105,
                            guts: 94,
                            wisdom: 82
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 10,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 36,
                        name: "Gold City",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Mile", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__gold_city_umamusume_drawn_by_chahiro__67945e12fb1202e37bfb10604e906125.jpg",
                        stats: {
                            speed: 96,
                            stamina: 88,
                            power: 88,
                            guts: 94,
                            wisdom: 88
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 37,
                        name: "Sakura Bakushin O",
                        rarity: 1,
                        surface: "Turf",
                        distance: "Sprint",
                        style: "Runner",
                        aptitudes: ["Turf", "Sprint", "Mile", "Runner", "Leader"],
                        image: "/images/trainee_images/__sakura_bakushin_o_umamusume_drawn_by_itou_onsoku_tassha__8b4727be2ac678c0c1cad199ce9c18e3.jpg",
                        stats: {
                            speed: 105,
                            stamina: 75,
                            power: 85,
                            guts: 90,
                            wisdom: 90
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 38,
                        name: "Shinko Windy",
                        rarity: 2,
                        surface: "Dirt",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Dirt", "Mile", "Medium", "Leader"],
                        image: "/images/trainee_images/__shinko_windy_umamusume_drawn_by_toriga_naku__95b4fbdeb3df9122e8131289abd13cb1.jpg",
                        stats: {
                            speed: 90,
                            stamina: 85,
                            power: 94,
                            guts: 90,
                            wisdom: 88
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 39,
                        name: "Smart Falcon",
                        rarity: 3,
                        surface: "Dirt",
                        distance: "Medium",
                        style: "Runner",
                        aptitudes: ["Dirt", "Mile", "Medium", "Runner"],
                        image: "/images/trainee_images/__smart_falcon_umamusume_drawn_by_motsutoko__695387267327663d2f7a9eb898107126.jpg",
                        stats: {
                            speed: 100,
                            stamina: 90,
                            power: 92,
                            guts: 88,
                            wisdom: 88
                        },
                        growth: {
                            speed: 20,
                            stamina: 0,
                            power: 10,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 40,
                        name: "Zenno Rob Roy",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__zenno_rob_roy_umamusume_drawn_by_factory314__2ea22f996f5a613b62bb354cf31c0b64.png",
                        stats: {
                            speed: 92,
                            stamina: 94,
                            power: 90,
                            guts: 85,
                            wisdom: 95
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 41,
                        name: "Tosen Jordan",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__tosen_jordan_umamusume_drawn_by_hiyo_uma__a352b60dedc127b826458915c0a8b94d.jpg",
                        stats: {
                            speed: 94,
                            stamina: 90,
                            power: 92,
                            guts: 88,
                            wisdom: 90
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 10,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 42,
                        name: "Nakayama Festa",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener"],
                        image: "/images/trainee_images/__nakayama_festa_and_alex_umamusume_and_2_more_drawn_by_hakuki__c80d09aab832bf5d94c91dfbe030df9f.jpg",
                        stats: {
                            speed: 92,
                            stamina: 96,
                            power: 94,
                            guts: 92,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 43,
                        name: "Narita Taishin",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Chaser",
                        aptitudes: ["Turf", "Medium", "Long", "Chaser"],
                        image: "/images/trainee_images/__narita_taishin_umamusume_drawn_by_izumi_mahiru__60b068372d0a2398231aea82bfd89de7.jpg",
                        stats: {
                            speed: 95,
                            stamina: 92,
                            power: 88,
                            guts: 96,
                            wisdom: 82
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 44,
                        name: "Nishino Flower",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Leader",
                        aptitudes: ["Turf", "Sprint", "Mile", "Leader", "Betweener"],
                        image: "/images/trainee_images/__nishino_flower_umamusume_drawn_by_otono_bkt4b__c4ec03e283e403171cb8dc47175b920f.png",
                        stats: {
                            speed: 98,
                            stamina: 82,
                            power: 94,
                            guts: 85,
                            wisdom: 95
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 20,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 45,
                        name: "Haru Urara",
                        rarity: 1,
                        surface: "Dirt",
                        distance: "Sprint",
                        style: "Betweener",
                        aptitudes: ["Dirt", "Sprint", "Mile", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__haru_urara_umamusume_drawn_by_advarcher__sample-7d1c3c431ef193e5e061bdda73f97fd5.jpg",
                        stats: {
                            speed: 85,
                            stamina: 80,
                            power: 90,
                            guts: 100,
                            wisdom: 75
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 10,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 46,
                        name: "Matikanefukukitaru",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Long",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener"],
                        image: "/images/trainee_images/__matikanefukukitaru_umamusume_drawn_by_kawashina_momen_silicon__00780ddf644b4efef3e7744b2b946a28.png",
                        stats: {
                            speed: 88,
                            stamina: 95,
                            power: 88,
                            guts: 90,
                            wisdom: 92
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 0,
                            wisdom: 10
                        }
                    },
                    {
                        id: 47,
                        name: "Meisho Doto",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader"],
                        image: "/images/trainee_images/__meisho_doto_umamusume_drawn_by_tukune__d3e176e31aa10898a48e01654298cc07.jpg",
                        stats: {
                            speed: 92,
                            stamina: 98,
                            power: 94,
                            guts: 90,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 48,
                        name: "Nice Nature",
                        rarity: 1,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener"],
                        image: "/images/trainee_images/__nice_nature_umamusume_drawn_by_sekiyu_inu__d1d0d3773cf6b911f6ac6b1f753acff0.jpg",
                        stats: {
                            speed: 85,
                            stamina: 85,
                            power: 90,
                            guts: 88,
                            wisdom: 92
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 10,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 49,
                        name: "King Halo",
                        rarity: 1,
                        surface: "Turf",
                        distance: "Sprint",
                        style: "Betweener",
                        aptitudes: ["Turf", "Sprint", "Mile", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__king_halo_umamusume_drawn_by_jjjsss__1d5e6f12cd1a62d961c95a210cb275b2.jpg",
                        stats: {
                            speed: 90,
                            stamina: 82,
                            power: 95,
                            guts: 90,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 20,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 50,
                        name: "Machikane Tannhauser",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Long",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Long", "Leader", "Betweener"],
                        image: "/images/trainee_images/__matikanetannhauser_umamusume_drawn_by_funamori__d3c68aae9e0247b1c79ad830d655fe27.jpg",
                        stats: {
                            speed: 88,
                            stamina: 96,
                            power: 90,
                            guts: 94,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 20,
                            power: 0,
                            guts: 10,
                            wisdom: 0
                        }
                    },
                    {
                        id: 51,
                        name: "Ikuno Dictus",
                        rarity: 2,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Mile", "Medium", "Betweener", "Chaser"],
                        image: "/images/trainee_images/__ikuno_dictus_umamusume_drawn_by_zukki_bijitaru__b5bd688fe98c5379acac9ed07537db83.png",
                        stats: {
                            speed: 85,
                            stamina: 94,
                            power: 85,
                            guts: 92,
                            wisdom: 95
                        },
                        growth: {
                            speed: 0,
                            stamina: 15,
                            power: 0,
                            guts: 0,
                            wisdom: 15
                        }
                    },
                    {
                        id: 52,
                        name: "Twin Turbo",
                        rarity: 1,
                        surface: "Turf",
                        distance: "Mile",
                        style: "Runner",
                        aptitudes: ["Turf", "Mile", "Medium", "Runner"],
                        image: "/images/trainee_images/__twin_turbo_umamusume_drawn_by_urujika__718f48540797d2872f7a1d578edae44a.jpg",
                        stats: {
                            speed: 100,
                            stamina: 70,
                            power: 80,
                            guts: 85,
                            wisdom: 75
                        },
                        growth: {
                            speed: 30,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 53,
                        name: "Satono Diamond",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Long", "Betweener"],
                        image: "/images/trainee_images/__satono_diamond_umamusume_drawn_by_freely2327__80fbdecd4f5c28f8501c032031d978d0.png",
                        stats: {
                            speed: 94,
                            stamina: 102,
                            power: 90,
                            guts: 95,
                            wisdom: 92
                        },
                        growth: {
                            speed: 0,
                            stamina: 15,
                            power: 0,
                            guts: 0,
                            wisdom: 15
                        }
                    },
                    {
                        id: 54,
                        name: "Kitasan Black",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Runner",
                        aptitudes: ["Turf", "Medium", "Long", "Runner", "Leader"],
                        image: "/images/trainee_images/__kitasan_black_umamusume_drawn_by_mattya122__47e067dbb97ad9a28758789d05c28f90.jpg",
                        stats: {
                            speed: 100,
                            stamina: 98,
                            power: 92,
                            guts: 88,
                            wisdom: 90
                        },
                        growth: {
                            speed: 20,
                            stamina: 10,
                            power: 0,
                            guts: 0,
                            wisdom: 0
                        }
                    },
                    {
                        id: 55,
                        name: "Mejiro Ardan",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Leader"],
                        image: "/images/trainee_images/__mejiro_ardan_umamusume_drawn_by_kentan_kingtaiki__3e95bc0615337fe4e260aaf5c73b1595.jpg",
                        stats: {
                            speed: 96,
                            stamina: 88,
                            power: 90,
                            guts: 85,
                            wisdom: 100
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 56,
                        name: "Mejiro Dober",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Betweener",
                        aptitudes: ["Turf", "Medium", "Mile", "Betweener"],
                        image: "/images/trainee_images/__mejiro_dober_umamusume_drawn_by_puddinghomhom__fa2912aaa50460a0804ff3253ad00be1.png",
                        stats: {
                            speed: 92,
                            stamina: 90,
                            power: 85,
                            guts: 90,
                            wisdom: 102
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 0,
                            guts: 10,
                            wisdom: 20
                        }
                    },
                    {
                        id: 57,
                        name: "Mejiro Palmer",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Runner",
                        aptitudes: ["Turf", "Medium", "Long", "Runner"],
                        image: "/images/trainee_images/__mejiro_palmer_umamusume_drawn_by_fuchina__9867288e81382c6cacc9bdd33d1eb582.png",
                        stats: {
                            speed: 95,
                            stamina: 94,
                            power: 92,
                            guts: 98,
                            wisdom: 80
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 10,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 58,
                        name: "Mr. C.B.",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Long",
                        style: "Chaser",
                        aptitudes: ["Turf", "Medium", "Long", "Chaser"],
                        image: "/images/trainee_images/__mr_c_b_umamusume_drawn_by_taromarun__f18d9b885cd85cacc316047a32b2af8f.jpg",
                        stats: {
                            speed: 92,
                            stamina: 100,
                            power: 90,
                            guts: 88,
                            wisdom: 105
                        },
                        growth: {
                            speed: 0,
                            stamina: 10,
                            power: 0,
                            guts: 0,
                            wisdom: 20
                        }
                    },
                    {
                        id: 59,
                        name: "Yaeno Muteki",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Mile", "Leader"],
                        image: "/images/trainee_images/__yaeno_muteki_umamusume_drawn_by_rinka_tonariuta__4b68983d5d9e3e3243a2a2c8901047d8.png",
                        stats: {
                            speed: 90,
                            stamina: 90,
                            power: 105,
                            guts: 95,
                            wisdom: 85
                        },
                        growth: {
                            speed: 0,
                            stamina: 0,
                            power: 10,
                            guts: 20,
                            wisdom: 0
                        }
                    },
                    {
                        id: 60,
                        name: "Sakura Chiyono O",
                        rarity: 3,
                        surface: "Turf",
                        distance: "Medium",
                        style: "Leader",
                        aptitudes: ["Turf", "Medium", "Mile", "Leader"],
                        image: "/images/trainee_images/__sakura_chiyono_o_umamusume_drawn_by_gamyuu_gamyu__cdf9c12c540c3c6222ddca538485f180.png",
                        stats: {
                            speed: 94,
                            stamina: 92,
                            power: 88,
                            guts: 96,
                            wisdom: 90
                        },
                        growth: {
                            speed: 10,
                            stamina: 0,
                            power: 0,
                            guts: 20,
                            wisdom: 0
                        }
                    }
                ],

                filteredTrainees() {
                    return this.trainees.filter(trainee => {
                        const matchesQuery = this.filters.query === '' ||
                            trainee.name.toLowerCase().includes(this.filters.query.toLowerCase());

                        const matchesRarity = this.filters.rarity === '' ||
                            trainee.rarity.toString() === this.filters.rarity;

                        const matchesDistance = this.filters.distance === '' ||
                            trainee.distance === this.filters.distance;

                        const matchesSurface = this.filters.surface === '' ||
                            trainee.surface === this.filters.surface;

                        return matchesQuery && matchesRarity && matchesDistance && matchesSurface;
                    });
                },

                selectTrainee(trainee) {
                    // Update Selected Trainee
                    this.formData.trainee = trainee;

                    // Populate Form Data
                    this.formData.name = trainee.name;
                    this.formData.avatar_url = trainee.image;
                    this.formData.avatar_preview = trainee.image;

                    // Populate Stats
                    this.formData.stats.speed = trainee.stats.speed;
                    this.formData.stats.stamina = trainee.stats.stamina;
                    this.formData.stats.power = trainee.stats.power;
                    this.formData.stats.guts = trainee.stats.guts;
                    this.formData.stats.wisdom = trainee.stats.wisdom;

                    // Reset Filters
                    this.showGallery = false;

                    // Visual feedback could be added here
                    // Move to next step if desired
                    // this.nextStep();
                },

                init() {
                    // Check for external character prefill data
                    this.loadExternalPrefill();

                    // Load saved data from localStorage
                    this.loadFromStorage();

                    // Save to localStorage whenever formData changes
                    this.$watch('formData', () => {
                        this.saveToStorage();
                    }, {
                        deep: true
                    });

                    // Add global mouse/touch event listeners for dragging
                    document.addEventListener('mousemove', (e) => this.onDrag(e));
                    document.addEventListener('mouseup', () => this.stopDrag());
                    document.addEventListener('touchmove', (e) => this.onDrag(e));
                    document.addEventListener('touchend', () => this.stopDrag());
                },

                loadExternalPrefill() {
                    // Check if we're coming from external data browser
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('from_external') !== '1') return;

                    const prefillData = sessionStorage.getItem('external_character_prefill');
                    if (!prefillData) return;

                    try {
                        const data = JSON.parse(prefillData);

                        // Prefill form data
                        if (data.name) {
                            this.formData.name = data.name;
                        }
                        if (data.image) {
                            this.formData.avatar_url = data.image;
                            this.formData.avatar_preview = data.image;
                        }

                        // Store external reference
                        this.formData.external_source = data.source || 'umapyoi.net';
                        this.formData.external_id = data.external_id;

                        // Clear the prefill data after use
                        sessionStorage.removeItem('external_character_prefill');

                        // Show a notification
                        this.showExternalPrefillNotice = true;
                        setTimeout(() => {
                            this.showExternalPrefillNotice = false;
                        }, 5000);

                        console.log('Loaded external character data:', data);
                    } catch (e) {
                        console.error('Failed to load external prefill data:', e);
                    }
                },


                loadFromStorage() {
                    const saved = localStorage.getItem('characterWizardData');
                    if (saved) {
                        try {
                            const data = JSON.parse(saved);
                            // Merge saved data with default formData
                            Object.assign(this.formData, data);
                        } catch (e) {
                            console.error('Failed to load saved data:', e);
                        }
                    }
                },

                saveToStorage() {
                    try {
                        localStorage.setItem('characterWizardData', JSON.stringify(this.formData));
                    } catch (e) {
                        console.error('Failed to save data:', e);
                    }
                },

                clearStorage() {
                    localStorage.removeItem('characterWizardData');
                },

                nextStep() {
                    if (this.currentStep < 4) {
                        this.currentStep++;
                    }
                },

                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },

                goToStep(step) {
                    // Allow navigation to previous steps or current step
                    if (step <= this.currentStep) {
                        this.currentStep = step;
                    }
                },

                handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please upload a valid image file (JPG, PNG, or GIF)');
                        return;
                    }

                    // Validate file size (max 2MB)
                    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                    if (file.size > maxSize) {
                        alert('File size must be less than 2MB');
                        return;
                    }

                    // Create preview URL
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.avatar_preview = e.target.result;
                        this.formData.avatar_url = 'custom_upload'; // Mark as custom upload
                        this.showGallery = false;
                    };
                    reader.readAsDataURL(file);
                },

                selectGalleryImage(imagePath) {
                    this.formData.avatar_url = imagePath;
                    this.formData.avatar_preview = imagePath;
                    this.showGallery = false;
                },

                // External API Methods
                async searchExternalCharacters() {
                    this.externalLoading = true;
                    this.externalError = null;
                    this.externalSearched = true;

                    try {
                        const params = new URLSearchParams();
                        if (this.externalFilters.query) {
                            params.append('q', this.externalFilters.query);
                        }
                        if (this.externalFilters.category) {
                            params.append('category', this.externalFilters.category);
                        }

                        const response = await fetch(`/api/characters/prefill/search?${params.toString()}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.externalCharacters = data.data || [];
                        } else {
                            throw new Error(data.message || 'Failed to fetch characters');
                        }
                    } catch (error) {
                        console.error('External API search error:', error);
                        this.externalError = error.message || 'Failed to search external database';
                        this.externalCharacters = [];
                    } finally {
                        this.externalLoading = false;
                    }
                },

                selectExternalCharacter(character) {
                    this.selectedExternalCharacter = character;
                    this.formData.external_id = character.id;
                    this.formData.external_source = 'umapyoi.net';
                },

                async loadExternalCharacterData() {
                    if (!this.selectedExternalCharacter) return;

                    this.externalDataLoading = true;
                    this.externalError = null;

                    try {
                        const response = await fetch(`/api/characters/prefill/${this.selectedExternalCharacter.id}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success && result.data) {
                            const data = result.data;

                            // Prefill form data
                            this.formData.name = data.name || data.name_en || '';
                            this.formData.avatar_url = data.image_url || '';
                            this.formData.avatar_preview = data.image_url || '';
                            this.formData.external_id = data.external_id;
                            this.formData.external_source = data.metadata?.source || 'umapyoi.net';

                            // Prefill stats
                            if (data.stats) {
                                this.formData.stats.speed = data.stats.speed || 0;
                                this.formData.stats.stamina = data.stats.stamina || 0;
                                this.formData.stats.power = data.stats.power || 0;
                                this.formData.stats.guts = data.stats.guts || 0;
                                this.formData.stats.wit = data.stats.wit || 0;
                            }

                            // Prefill aptitudes
                            if (data.aptitudes) {
                                // Distance aptitudes
                                if (data.aptitudes.distance) {
                                    this.formData.aptitudes.distance.sprint = data.aptitudes.distance.sprint || '';
                                    this.formData.aptitudes.distance.mile = data.aptitudes.distance.mile || '';
                                    this.formData.aptitudes.distance.medium = data.aptitudes.distance.medium || '';
                                    this.formData.aptitudes.distance.long = data.aptitudes.distance.long || '';
                                }

                                // Surface aptitudes
                                if (data.aptitudes.surface) {
                                    this.formData.aptitudes.surface.turf = data.aptitudes.surface.turf || '';
                                    this.formData.aptitudes.surface.dirt = data.aptitudes.surface.dirt || '';
                                }

                                // Running style aptitudes
                                if (data.aptitudes.style) {
                                    this.formData.aptitudes.style.front_runner = data.aptitudes.style.front_runner ||
                                        '';
                                    this.formData.aptitudes.style.pace_chaser = data.aptitudes.style.pace_chaser || '';
                                    this.formData.aptitudes.style.late_surger = data.aptitudes.style.late_surger || '';
                                    this.formData.aptitudes.style.end_closer = data.aptitudes.style.end_closer || '';
                                }
                            }

                            // Close the external API panel and show success message
                            this.showExternalApi = false;
                            this.showExternalPrefillNotice = true;
                            setTimeout(() => {
                                this.showExternalPrefillNotice = false;
                            }, 5000);

                            console.log('Loaded external character data:', data);
                        } else {
                            throw new Error(result.message || 'Failed to load character data');
                        }
                    } catch (error) {
                        console.error('External API load error:', error);
                        this.externalError = error.message || 'Failed to load character data';
                    } finally {
                        this.externalDataLoading = false;
                    }
                },

                useDefaultAvatar() {
                    this.formData.avatar_url = '';
                    this.formData.avatar_preview = '';
                    this.showGallery = false;
                    this.resetImageEdits();
                },

                adjustZoom(delta) {
                    const newZoom = this.formData.imageZoom + delta;
                    if (newZoom >= 0.5 && newZoom <= 2) {
                        this.formData.imageZoom = Math.round(newZoom * 10) / 10;
                    }
                },

                rotateImage(degrees) {
                    this.formData.imageRotation = (this.formData.imageRotation + degrees) % 360;
                    if (this.formData.imageRotation < 0) {
                        this.formData.imageRotation += 360;
                    }
                },

                flipImageHorizontal() {
                    this.formData.imageFlipH = !this.formData.imageFlipH;
                },

                resetImageEdits() {
                    this.formData.imageZoom = 1;
                    this.formData.imageRotation = 0;
                    this.formData.imageFlipH = false;
                    this.formData.imageX = 0;
                    this.formData.imageY = 0;
                },

                moveImage(deltaX, deltaY) {
                    this.formData.imageX += deltaX;
                    this.formData.imageY += deltaY;
                },

                startDrag(event) {
                    this.isDragging = true;
                    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                    const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                    this.dragStartX = clientX - this.formData.imageX;
                    this.dragStartY = clientY - this.formData.imageY;
                },

                onDrag(event) {
                    if (!this.isDragging) return;
                    event.preventDefault();
                    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                    const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                    this.formData.imageX = clientX - this.dragStartX;
                    this.formData.imageY = clientY - this.dragStartY;
                },

                stopDrag() {
                    this.isDragging = false;
                },

                validateStat(stat) {
                    const value = this.formData.stats[stat];
                    if (value < 0) {
                        this.formData.stats[stat] = 0;
                    } else if (value > 1200) {
                        this.formData.stats[stat] = 1200;
                    }
                },

                getGrade(value) {
                    if (value >= 1200) return 'SS+';
                    if (value >= 1100) return 'SS';
                    if (value >= 1050) return 'S+';
                    if (value >= 1000) return 'S';
                    if (value >= 900) return 'A+';
                    if (value >= 800) return 'A';
                    if (value >= 700) return 'B+';
                    if (value >= 600) return 'B';
                    if (value >= 500) return 'C+';
                    if (value >= 400) return 'C';
                    if (value >= 350) return 'D+';
                    if (value >= 300) return 'D';
                    if (value >= 250) return 'E+';
                    if (value >= 200) return 'E';
                    if (value >= 150) return 'F+';
                    if (value >= 100) return 'F';
                    if (value >= 50) return 'G+';
                    return 'G';
                },

                getGradeColor(value) {
                    if (value >= 1200) return 'bg-yellow-500 text-white';
                    if (value >= 1100) return 'bg-yellow-500 text-white';
                    if (value >= 1000) return 'bg-yellow-500 text-white';
                    if (value >= 900) return 'bg-orange-500 text-white';
                    if (value >= 800) return 'bg-orange-500 text-white';
                    if (value >= 700) return 'bg-red-500 text-white';
                    if (value >= 600) return 'bg-red-500 text-white';
                    if (value >= 500) return 'bg-green-500 text-white';
                    if (value >= 400) return 'bg-green-500 text-white';
                    if (value >= 350) return 'bg-blue-500 text-white';
                    if (value >= 300) return 'bg-blue-500 text-white';
                    if (value >= 250) return 'bg-purple-500 text-white';
                    if (value >= 200) return 'bg-purple-500 text-white';
                    if (value >= 150) return 'bg-purple-800 text-white';
                    if (value >= 100) return 'bg-purple-800 text-white';
                    if (value >= 50) return 'bg-gray-500 text-white';
                    return 'bg-gray-500 text-white';
                },

                isStep3Valid() {
                    // Check if all aptitudes are selected
                    const distance = Object.values(this.formData.aptitudes.distance).every(v => v !== '');
                    const surface = Object.values(this.formData.aptitudes.surface).every(v => v !== '');
                    const style = Object.values(this.formData.aptitudes.style).every(v => v !== '');
                    return distance && surface && style;
                }
            };
        }
    </script>
@endsection
