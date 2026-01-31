@extends('layouts.app')

@section('title', 'Data Migration')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Data Migration']]" />

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Migration</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Convert legacy data formats, batch import records, and resolve conflicts.
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-4" aria-label="Migration tabs">
                <button type="button"
                    class="tab-btn active px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600 dark:text-blue-400"
                    data-tab="convert">
                    Format Conversion
                </button>
                <button type="button"
                    class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    data-tab="batch">
                    Batch Import
                </button>
                <button type="button"
                    class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    data-tab="validate">
                    Validate Data
                </button>
                <button type="button"
                    class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    data-tab="conflicts">
                    Resolve Conflicts
                </button>
            </nav>
        </div>

        <!-- Format Conversion Tab -->
        <div id="tab-convert" class="tab-content">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Convert Legacy Data Format</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Paste your legacy data below. The system will automatically detect the format and convert it to the
                    current schema.
                </p>

                <form id="convert-form" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="source-format"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Source Format
                            </label>
                            <select id="source-format" name="source_format"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="auto">Auto-detect</option>
                                @foreach ($legacyFormats as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="target-type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Target Data Type
                            </label>
                            <select id="target-type" name="target_type" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($importTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="convert-content"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Legacy Data Content
                        </label>
                        <textarea id="convert-content" name="content" rows="12" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                            placeholder="Paste your legacy JSON, CSV, or text data here..."></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="button" id="detect-format-btn"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Detect Format
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Convert Data
                        </button>
                    </div>
                </form>

                <!-- Conversion Results -->
                <div id="convert-results" class="mt-6 hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Conversion Results</h3>
                    <div id="convert-results-content"></div>
                </div>
            </div>
        </div>

        <!-- Batch Import Tab -->
        <div id="tab-batch" class="tab-content hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Batch Import Processing</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Import large datasets with progress tracking and automatic conflict handling.
                </p>

                <form id="batch-form" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="batch-import-type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Import Type
                            </label>
                            <select id="batch-import-type" name="import_type" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($importTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="conflict-strategy"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Conflict Strategy
                            </label>
                            <select id="conflict-strategy" name="conflict_strategy"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($conflictStrategies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="batch-size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Batch Size
                            </label>
                            <input type="number" id="batch-size" name="batch_size" value="50" min="1"
                                max="500"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="batch-data" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Import Data (JSON Array)
                        </label>
                        <textarea id="batch-data" name="data" rows="10" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                            placeholder='[{"name": "Character 1", "speed": 800}, {"name": "Character 2", "speed": 750}]'></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" id="start-batch-btn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Start Batch Import
                        </button>
                    </div>
                </form>

                <!-- Batch Progress -->
                <div id="batch-progress" class="mt-6 hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Import Progress</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Status: <span id="batch-status"
                                    class="font-medium">-</span></span>
                            <span class="text-gray-600 dark:text-gray-400">Batch ID: <span id="batch-id"
                                    class="font-mono">-</span></span>
                        </div>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                            <div id="progress-bar" class="bg-blue-600 h-4 rounded-full transition-all duration-300"
                                style="width: 0%"></div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white" id="total-records">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400"
                                    id="successful-records">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Successful</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="failed-records">0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Failed</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="skipped-records">
                                    0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Skipped</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="button" id="process-batch-btn"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Process Next Batch
                            </button>
                            <button type="button" id="cancel-batch-btn"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Cancel Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validate Data Tab -->
        <div id="tab-validate" class="tab-content hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Validate Data Before Import</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Check your data for errors and get detailed validation feedback before importing.
                </p>

                <form id="validate-form" class="space-y-6">
                    @csrf
                    <div>
                        <label for="validate-import-type"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data Type
                        </label>
                        <select id="validate-import-type" name="import_type" required
                            class="w-full md:w-1/3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($importTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="validate-data"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data to Validate (JSON Array)
                        </label>
                        <textarea id="validate-data" name="data" rows="10" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                            placeholder='[{"name": "Test Character", "speed": 800, "stamina": 750}]'></textarea>
                    </div>

                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Validate Data
                    </button>
                </form>

                <!-- Validation Results -->
                <div id="validate-results" class="mt-6 hidden">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Validation Results</h3>
                    <div id="validate-results-content"></div>
                </div>
            </div>
        </div>

        <!-- Resolve Conflicts Tab -->
        <div id="tab-conflicts" class="tab-content hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Resolve Import Conflicts</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Review and resolve conflicts from batch imports. Enter a batch ID to view pending conflicts.
                </p>

                <div class="space-y-6">
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label for="conflict-batch-id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Batch ID
                            </label>
                            <input type="text" id="conflict-batch-id"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 font-mono"
                                placeholder="Enter batch UUID...">
                        </div>
                        <button type="button" id="load-conflicts-btn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Load Conflicts
                        </button>
                    </div>

                    <!-- Conflicts List -->
                    <div id="conflicts-list" class="hidden">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Pending Conflicts (<span id="conflicts-count">0</span>)
                        </h3>
                        <div id="conflicts-content" class="space-y-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/migration/index.js'])
@endsection
