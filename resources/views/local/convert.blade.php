@extends('layouts.app')

@section('title', 'Convert Local Data to Account')

@section('content')
    <x-breadcrumb :items="[['label' => 'Local Mode'], ['label' => 'Convert to Account']]" />

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Convert to Account Storage</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Migrate your locally stored data to your account for secure cloud-based storage,
                cross-device sync, and backup protection.
            </p>
        </div>

        <div x-data="localStorageConvert()" class="space-y-6">
            <!-- Current Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Local Data</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.characters">0</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Characters</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.careers">0</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Career Plans</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.builds">0</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Skill Builds</p>
                    </div>
                </div>
            </div>

            <!-- Validation -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Data Validation</h2>
                    <button type="button" @click="validateData()"
                        class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conversion Options</h2>

                <div class="space-y-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="options.keepLocal"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Keep local copy</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Retain local data after conversion as a backup. Recommended for first-time conversion.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="options.mergeDuplicates"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Merge duplicates</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Automatically merge local data with existing account data if duplicates are found.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Convert Button -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('local.dashboard') }}"
                    class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="button" @click="convertToAccount()"
                    class="px-6 py-3 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50"
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
        <script>
            function localStorageConvert() {
                return {
                    stats: { characters: 0, careers: 0, builds: 0 },
                    validating: false,
                    validationResult: null,
                    converting: false,
                    conversionComplete: false,
                    conversionMessage: '',
                    options: {
                        keepLocal: true,
                        mergeDuplicates: false,
                    },

                    init() {
                        this.loadStats();
                    },

                    loadStats() {
                        try {
                            const characters = JSON.parse(localStorage.getItem('ucp_characters') || '[]');
                            const careers = JSON.parse(localStorage.getItem('ucp_careers') || '[]');
                            const builds = JSON.parse(localStorage.getItem('ucp_skill_builds') || '[]');
                            this.stats.characters = characters.length;
                            this.stats.careers = careers.length;
                            this.stats.builds = builds.length;
                        } catch (e) {
                            console.warn('Failed to load local stats:', e);
                        }
                    },

                    async validateData() {
                        this.validating = true;
                        this.validationResult = null;

                        try {
                            const localData = {
                                characters: JSON.parse(localStorage.getItem('ucp_characters') || '[]'),
                                careers: JSON.parse(localStorage.getItem('ucp_careers') || '[]'),
                                builds: JSON.parse(localStorage.getItem('ucp_skill_builds') || '[]'),
                            };

                            const response = await fetch('/api/storage/validate', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                body: JSON.stringify({ data: localData }),
                            });

                            this.validationResult = await response.json();
                        } catch (e) {
                            this.validationResult = { valid: false, errors: ['Failed to validate: ' + e.message] };
                        } finally {
                            this.validating = false;
                        }
                    },

                    async convertToAccount() {
                        if (!this.validationResult?.valid) return;
                        this.converting = true;

                        try {
                            const localData = {
                                characters: JSON.parse(localStorage.getItem('ucp_characters') || '[]'),
                                careers: JSON.parse(localStorage.getItem('ucp_careers') || '[]'),
                                builds: JSON.parse(localStorage.getItem('ucp_skill_builds') || '[]'),
                            };

                            const response = await fetch('/api/storage/convert', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                },
                                body: JSON.stringify({
                                    data: localData,
                                    options: this.options,
                                }),
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.conversionComplete = true;
                                this.conversionMessage = result.message || 'All data has been migrated to your account.';

                                if (!this.options.keepLocal) {
                                    localStorage.removeItem('ucp_characters');
                                    localStorage.removeItem('ucp_careers');
                                    localStorage.removeItem('ucp_skill_builds');
                                }
                            } else {
                                this.validationResult = { valid: false, errors: [result.message || 'Conversion failed.'] };
                            }
                        } catch (e) {
                            this.validationResult = { valid: false, errors: ['Conversion error: ' + e.message] };
                        } finally {
                            this.converting = false;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
