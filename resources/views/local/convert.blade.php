@extends('layouts.app')

@section('title', 'Convert Local Data to Account')

@section('content')
    <x-breadcrumb :items="[['label' => 'Local Mode'], ['label' => 'Convert to Account']]" />

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Convert to Account Storage</h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                Migrate your locally stored data to your account for secure cloud-based storage,
                cross-device sync, and backup protection.
            </p>
        </div>

        <div x-data="localStorageConvert()" class="space-y-6">
            <!-- Current Status Card -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Current Local Data</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white" x-text="stats.characters">0</p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Characters</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white" x-text="stats.careers">0</p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Career Plans</p>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white" x-text="stats.builds">0</p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Skill Builds</p>
                    </div>
                </div>
            </div>

            <!-- Validation -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Data Validation</h2>
                    <button type="button" @click="validateData()"
                        class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800"
                        :disabled="validating">
                        <span x-show="!validating">Validate Data</span>
                        <span x-show="validating" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Validating...
                        </span>
                    </button>
                </div>

                <template x-if="validationResult">
                    <div>
                        <div x-show="validationResult.valid"
                            class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <p class="text-green-800 dark:text-green-300 font-medium">
                                ✓ All data is valid and ready for conversion.
                            </p>
                        </div>
                        <div x-show="!validationResult.valid"
                            class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-red-800 dark:text-red-300 font-medium mb-2">
                                ✗ Some data has issues that need to be resolved:
                            </p>
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                                <template x-for="error in validationResult.errors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Conversion Options -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Conversion Options</h2>

                <div class="space-y-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="options.keepLocal"
                            class="mt-1 h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700">
                        <div>
                            <span class="font-medium text-neutral-900 dark:text-white">Keep local copy</span>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Retain local data after conversion as a backup. Recommended for first-time conversion.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="options.mergeDuplicates"
                            class="mt-1 h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700">
                        <div>
                            <span class="font-medium text-neutral-900 dark:text-white">Merge duplicates</span>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Automatically merge local data with existing account data if duplicates are found.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Convert Button -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('local.dashboard') }}"
                    class="px-6 py-3 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                    Cancel
                </a>
                <button type="button" @click="convertToAccount()"
                    class="px-6 py-3 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 disabled:opacity-50"
                    :disabled="converting || !validationResult?.valid">
                    <span x-show="!converting">Convert to Account</span>
                    <span x-show="converting" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Converting...
                    </span>
                </button>
            </div>

            <!-- Success Message -->
            <template x-if="conversionComplete">
                <div class="p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-center">
                    <p class="text-xl font-semibold text-green-800 dark:text-green-300 mb-2">
                        ✓ Conversion Complete!
                    </p>
                    <p class="text-green-700 dark:text-green-400 mb-4" x-text="conversionMessage"></p>
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Go to Dashboard
                    </a>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
        {{-- Extracted: JS logic moved to resources/js/pages/local/convert.js --}}
        @vite('resources/js/pages/local/convert.js')
    @endpush
@endsection
