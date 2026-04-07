@extends('layouts.app')

@section('title', 'Data Migration')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Data Migration']]" />

    <div class="container mx-auto px-4 py-8 max-w-4xl mb-24">
        <!-- Page Header -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-semibold text-neutral-900 dark:text-white tracking-tight">Data Migration</h1>
            <p class="mt-3 text-neutral-500 dark:text-neutral-400">
                Convert legacy data, validate structure, batched imports, and resolve conflicts sequentially.
            </p>
        </div>

        <div x-data="{ activeAccordion: 'convert' }" class="space-y-6">
            
            <!-- Format Conversion Section -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs overflow-hidden transition-all duration-200" :class="{'ring-2 ring-blue-500/20 border-blue-200 dark:border-blue-800': activeAccordion === 'convert'}">
                <button type="button" @click="activeAccordion = activeAccordion === 'convert' ? null : 'convert'" class="w-full flex items-center justify-between p-6 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <div class="flex items-center gap-4 text-left">
                        <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-neutral-900 dark:text-white">Format Conversion</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 font-normal">Convert your legacy data chunks to compatible schema</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="{'rotate-180': activeAccordion === 'convert'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="activeAccordion === 'convert'" x-collapse x-cloak>
                    <div class="p-6 pt-0 border-t border-neutral-100 dark:border-neutral-700/50 mt-2">
                        <form id="convert-form" class="space-y-6 pt-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="source-format" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Source Format <span class="text-neutral-400 font-normal ml-1">(Optional)</span>
                                    </label>
                                    <select id="source-format" name="source_format" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        <option value="auto">Auto-detect</option>
                                        @foreach ($legacyFormats as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="target-type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Target Data Type
                                    </label>
                                    <select id="target-type" name="target_type" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        @foreach ($importTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="convert-content" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Legacy Data Content
                                </label>
                                <textarea id="convert-content" name="content" rows="6" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm font-mono text-sm resize-y" placeholder="Paste your legacy JSON, CSV, or text data here..."></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-700/50">
                                <button type="button" id="detect-format-btn" class="px-4 py-2 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-600 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm font-medium">
                                    Detect Format
                                </button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm font-medium">
                                    Convert Data
                                </button>
                            </div>
                        </form>

                        <div id="convert-results" class="mt-6 hidden bg-neutral-50 dark:bg-neutral-900 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700">
                            <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-3">Conversion Results</h3>
                            <div id="convert-results-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validate Data Section -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs overflow-hidden transition-all duration-200" :class="{'ring-2 ring-blue-500/20 border-blue-200 dark:border-blue-800': activeAccordion === 'validate'}">
                <button type="button" @click="activeAccordion = activeAccordion === 'validate' ? null : 'validate'" class="w-full flex items-center justify-between p-6 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <div class="flex items-center gap-4 text-left">
                        <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-neutral-900 dark:text-white">Validate Data</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 font-normal">Check integrity and schema validity before importing</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="{'rotate-180': activeAccordion === 'validate'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="activeAccordion === 'validate'" x-collapse x-cloak>
                    <div class="p-6 pt-0 border-t border-neutral-100 dark:border-neutral-700/50 mt-2">
                        <form id="validate-form" class="space-y-6 pt-4">
                            @csrf
                            <div>
                                <label for="validate-import-type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Data Type
                                </label>
                                <select id="validate-import-type" name="import_type" required class="w-full md:w-1/2 rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    @foreach ($importTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="validate-data" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Data to Validate (JSON Array)
                                </label>
                                <textarea id="validate-data" name="data" rows="5" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm font-mono text-sm resize-y" placeholder='[{"name": "Test Character", "speed": 800}]'></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-neutral-700/50">
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm font-medium">
                                    Run Validation
                                </button>
                            </div>
                        </form>

                        <div id="validate-results" class="mt-6 hidden bg-neutral-50 dark:bg-neutral-900 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700">
                            <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-3">Validation Results</h3>
                            <div id="validate-results-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Batch Import Section -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs overflow-hidden transition-all duration-200" :class="{'ring-2 ring-blue-500/20 border-blue-200 dark:border-blue-800': activeAccordion === 'batch'}">
                <button type="button" @click="activeAccordion = activeAccordion === 'batch' ? null : 'batch'" class="w-full flex items-center justify-between p-6 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <div class="flex items-center gap-4 text-left">
                        <div class="p-2 rounded-xl bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-neutral-900 dark:text-white">Batch Import</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 font-normal">Safely insert large datasets with automated conflict handling</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="{'rotate-180': activeAccordion === 'batch'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="activeAccordion === 'batch'" x-collapse x-cloak>
                    <div class="p-6 pt-0 border-t border-neutral-100 dark:border-neutral-700/50 mt-2">
                        <form id="batch-form" class="space-y-6 pt-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="batch-import-type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Import Type
                                    </label>
                                    <select id="batch-import-type" name="import_type" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        @foreach ($importTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="conflict-strategy" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Conflict Strategy <span class="text-neutral-400 font-normal ml-1">(Optional)</span>
                                    </label>
                                    <select id="conflict-strategy" name="conflict_strategy" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        @foreach ($conflictStrategies as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="batch-size" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Batch Size <span class="text-neutral-400 font-normal ml-1">(Optional)</span>
                                    </label>
                                    <input type="number" id="batch-size" name="batch_size" value="50" min="1" max="500" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                            </div>

                            <div>
                                <label for="batch-data" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Import Data (JSON Array)
                                </label>
                                <textarea id="batch-data" name="data" rows="5" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm font-mono text-sm resize-y" placeholder='[{"name": "...", "speed": 800}]'></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-neutral-700/50">
                                <button type="submit" id="start-batch-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm font-medium">
                                    Execute Import
                                </button>
                            </div>
                        </form>

                        <div id="batch-progress" class="mt-8 hidden border border-neutral-200 dark:border-neutral-700 rounded-xl p-6 bg-white dark:bg-neutral-800">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Import Progress</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-neutral-600 dark:text-neutral-400">Status: <span id="batch-status" class="font-medium text-neutral-900 dark:text-white">-</span></span>
                                    <span class="text-neutral-600 dark:text-neutral-400 text-xs">ID: <span id="batch-id" class="font-mono text-neutral-500">-</span></span>
                                </div>

                                <div class="w-full bg-neutral-100 dark:bg-neutral-700 rounded-full h-2">
                                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center my-4">
                                    <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg py-2 border border-neutral-100 dark:border-neutral-800">
                                        <div class="text-xl font-semibold text-neutral-800 dark:text-white" id="total-records">0</div>
                                        <div class="text-[10px] uppercase tracking-wider text-neutral-500">Total</div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/10 rounded-lg py-2 border border-green-100 dark:border-green-900/30">
                                        <div class="text-xl font-semibold text-green-600 dark:text-green-400" id="successful-records">0</div>
                                        <div class="text-[10px] uppercase tracking-wider text-green-600 dark:text-green-500">Success</div>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900/10 rounded-lg py-2 border border-red-100 dark:border-red-900/30">
                                        <div class="text-xl font-semibold text-red-600 dark:text-red-400" id="failed-records">0</div>
                                        <div class="text-[10px] uppercase tracking-wider text-red-600 dark:text-red-500">Failed</div>
                                    </div>
                                    <div class="bg-yellow-50 dark:bg-yellow-900/10 rounded-lg py-2 border border-yellow-100 dark:border-yellow-900/30">
                                        <div class="text-xl font-semibold text-yellow-600 dark:text-yellow-400" id="skipped-records">0</div>
                                        <div class="text-[10px] uppercase tracking-wider text-yellow-600 dark:text-yellow-500">Skipped</div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 border-t border-neutral-100 dark:border-neutral-700/50 pt-4">
                                    <button type="button" id="cancel-batch-btn" class="px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-neutral-700 rounded-lg transition-colors font-medium text-sm">
                                        Cancel
                                    </button>
                                    <button type="button" id="process-batch-btn" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 hover:bg-black dark:hover:bg-white rounded-lg transition-colors font-medium text-sm">
                                        Next Batch
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resolve Conflicts Section -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs overflow-hidden transition-all duration-200" :class="{'ring-2 ring-blue-500/20 border-blue-200 dark:border-blue-800': activeAccordion === 'conflicts'}">
                <button type="button" @click="activeAccordion = activeAccordion === 'conflicts' ? null : 'conflicts'" class="w-full flex items-center justify-between p-6 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <div class="flex items-center gap-4 text-left">
                        <div class="p-2 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-neutral-900 dark:text-white">Resolve Conflicts</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 font-normal">Manually review items flagged during import</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="{'rotate-180': activeAccordion === 'conflicts'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="activeAccordion === 'conflicts'" x-collapse x-cloak>
                    <div class="p-6 pt-0 border-t border-neutral-100 dark:border-neutral-700/50 mt-2">
                        <div class="space-y-6 pt-4">
                            <div class="flex items-end gap-3">
                                <div class="flex-1">
                                    <label for="conflict-batch-id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Batch ID
                                    </label>
                                    <input type="text" id="conflict-batch-id" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm font-mono text-sm" placeholder="Ex: 550e8400-e29b-41d4-a716-446655440000">
                                </div>
                                <button type="button" id="load-conflicts-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm font-medium">
                                    Find
                                </button>
                            </div>

                            <div id="conflicts-list" class="hidden mt-6">
                                <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-4">Pending Conflicts (<span id="conflicts-count">0</span>)</h3>
                                <div id="conflicts-content" class="space-y-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @vite(['resources/js/pages/migration/index.js'])
@endsection
