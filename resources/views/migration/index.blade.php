@extends('layouts.app')

@section('title', 'Data Migration')

@section('content')
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

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            let currentBatchId = null;

            // Tab switching
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;

                    // Update button styles
                    document.querySelectorAll('.tab-btn').forEach(b => {
                        b.classList.remove('active', 'border-blue-500', 'text-blue-600',
                            'dark:text-blue-400');
                        b.classList.add('border-transparent', 'text-gray-500');
                    });
                    this.classList.add('active', 'border-blue-500', 'text-blue-600',
                        'dark:text-blue-400');
                    this.classList.remove('border-transparent', 'text-gray-500');

                    // Show/hide content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    document.getElementById('tab-' + tabId).classList.remove('hidden');
                });
            });

            // Detect Format
            document.getElementById('detect-format-btn')?.addEventListener('click', async function() {
                const content = document.getElementById('convert-content').value;
                if (!content.trim()) {
                    alert('Please enter content to detect format');
                    return;
                }

                try {
                    const response = await fetch('/api/migration/detect-format', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('source-format').value = data.data.format;
                        alert(
                            `Detected format: ${data.data.format} (${Math.round(data.data.confidence * 100)}% confidence)`
                        );
                    } else {
                        alert('Could not detect format: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error detecting format');
                }
            });

            // Convert Form
            document.getElementById('convert-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = {
                    content: document.getElementById('convert-content').value,
                    source_format: document.getElementById('source-format').value,
                    target_type: document.getElementById('target-type').value
                };

                try {
                    const response = await fetch('/api/migration/convert', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    const resultsDiv = document.getElementById('convert-results');
                    const contentDiv = document.getElementById('convert-results-content');

                    resultsDiv.classList.remove('hidden');

                    if (data.success) {
                        contentDiv.innerHTML = `
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                                <p class="text-green-800 dark:text-green-200">Successfully converted ${data.data.statistics?.converted_records || 0} records</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="font-medium mb-2">Converted Data:</h4>
                                <pre class="text-sm overflow-auto max-h-96">${JSON.stringify(data.data.converted_data, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        contentDiv.innerHTML = `
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <p class="text-red-800 dark:text-red-200">Conversion failed: ${data.errors?.join(', ') || data.message}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error converting data');
                }
            });

            // Batch Import Form
            document.getElementById('batch-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                let data;
                try {
                    data = JSON.parse(document.getElementById('batch-data').value);
                } catch (err) {
                    alert('Invalid JSON data. Please check your input.');
                    return;
                }

                const formData = {
                    data: data,
                    import_type: document.getElementById('batch-import-type').value,
                    conflict_strategy: document.getElementById('conflict-strategy').value,
                    batch_size: parseInt(document.getElementById('batch-size').value)
                };

                try {
                    const response = await fetch('/api/migration/batch', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        currentBatchId = result.data.batch_id;
                        document.getElementById('batch-progress').classList.remove('hidden');
                        document.getElementById('batch-id').textContent = currentBatchId;
                        document.getElementById('total-records').textContent = result.data
                            .total_records;
                        updateBatchStatus();
                    } else {
                        alert('Failed to start batch: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error starting batch import');
                }
            });

            // Process Batch
            document.getElementById('process-batch-btn')?.addEventListener('click', async function() {
                if (!currentBatchId) return;

                try {
                    const response = await fetch(`/api/migration/batch/${currentBatchId}/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    updateProgressUI(result.data);
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error processing batch');
                }
            });

            // Cancel Batch
            document.getElementById('cancel-batch-btn')?.addEventListener('click', async function() {
                if (!currentBatchId) return;
                if (!confirm('Are you sure you want to cancel this import?')) return;

                try {
                    const response = await fetch(`/api/migration/batch/${currentBatchId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        document.getElementById('batch-status').textContent = 'Cancelled';
                        alert('Batch import cancelled');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error cancelling batch');
                }
            });

            async function updateBatchStatus() {
                if (!currentBatchId) return;

                try {
                    const response = await fetch(`/api/migration/batch/${currentBatchId}/status`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        updateProgressUI(result.data);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function updateProgressUI(data) {
                if (!data) return;

                document.getElementById('batch-status').textContent = data.status || '-';

                if (data.progress) {
                    const p = data.progress;
                    document.getElementById('total-records').textContent = p.total_records || 0;
                    document.getElementById('successful-records').textContent = p.successful_records || 0;
                    document.getElementById('failed-records').textContent = p.failed_records || 0;
                    document.getElementById('skipped-records').textContent = p.skipped_records || 0;
                    document.getElementById('progress-bar').style.width = (p.percentage || 0) + '%';
                }
            }

            // Validate Form
            document.getElementById('validate-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                let data;
                try {
                    data = JSON.parse(document.getElementById('validate-data').value);
                } catch (err) {
                    alert('Invalid JSON data. Please check your input.');
                    return;
                }

                const formData = {
                    data: data,
                    import_type: document.getElementById('validate-import-type').value
                };

                try {
                    const response = await fetch('/api/migration/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    const resultsDiv = document.getElementById('validate-results');
                    const contentDiv = document.getElementById('validate-results-content');

                    resultsDiv.classList.remove('hidden');

                    if (result.success) {
                        const summary = result.data.summary;
                        const isValid = result.data.valid;

                        let html = `
                    <div class="${isValid ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800'} border rounded-lg p-4 mb-4">
                        <p class="${isValid ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200'}">
                            ${isValid ? 'All records are valid!' : 'Some records have validation errors.'}
                        </p>
                        <div class="mt-2 text-sm">
                            <span class="mr-4">Total: ${summary.total}</span>
                            <span class="mr-4 text-green-600">Valid: ${summary.valid_count}</span>
                            <span class="text-red-600">Invalid: ${summary.invalid_count}</span>
                        </div>
                    </div>
                `;

                        if (result.data.invalid_records?.length > 0) {
                            html += `<div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 mb-4">
                        <h4 class="font-medium text-red-800 dark:text-red-200 mb-2">Invalid Records:</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">`;
                            result.data.invalid_records.forEach(r => {
                                html += `<li>Record ${r.index}: ${r.errors.join(', ')}</li>`;
                            });
                            html += `</ul></div>`;
                        }

                        if (result.data.warnings?.length > 0) {
                            html += `<div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                        <h4 class="font-medium text-yellow-800 dark:text-yellow-200 mb-2">Warnings:</h4>
                        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300">`;
                            result.data.warnings.forEach(w => {
                                html += `<li>${w}</li>`;
                            });
                            html += `</ul></div>`;
                        }

                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-red-800 dark:text-red-200">Validation failed: ${result.message || 'Unknown error'}</p>
                    </div>
                `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error validating data');
                }
            });

            // Load Conflicts
            document.getElementById('load-conflicts-btn')?.addEventListener('click', async function() {
                const batchId = document.getElementById('conflict-batch-id').value.trim();
                if (!batchId) {
                    alert('Please enter a batch ID');
                    return;
                }

                try {
                    const response = await fetch(`/api/migration/batch/${batchId}/conflicts`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    const listDiv = document.getElementById('conflicts-list');
                    const contentDiv = document.getElementById('conflicts-content');

                    listDiv.classList.remove('hidden');
                    document.getElementById('conflicts-count').textContent = result.data?.count || 0;

                    if (result.success && result.data?.conflicts?.length > 0) {
                        let html = '';
                        result.data.conflicts.forEach((conflict, index) => {
                            html += `
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-medium">Conflict #${index + 1}</h4>
                                <span class="text-sm text-gray-500">Index: ${conflict.index}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">New Record:</p>
                                    <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded text-xs overflow-auto">${JSON.stringify(conflict.record, null, 2)}</pre>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Existing Record:</p>
                                    <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded text-xs overflow-auto">${JSON.stringify(conflict.existing, null, 2)}</pre>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="resolveConflict('${batchId}', ${index}, 'skip')" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded text-sm hover:bg-gray-300">Skip</button>
                                <button onclick="resolveConflict('${batchId}', ${index}, 'overwrite')" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Overwrite</button>
                                <button onclick="resolveConflict('${batchId}', ${index}, 'merge')" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">Merge</button>
                                <button onclick="resolveConflict('${batchId}', ${index}, 'rename')" class="px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-yellow-700">Rename</button>
                            </div>
                        </div>
                    `;
                        });
                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML =
                            '<p class="text-gray-500 dark:text-gray-400">No pending conflicts found.</p>';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading conflicts');
                }
            });

            // Resolve Conflict function (global)
            window.resolveConflict = async function(batchId, conflictIndex, resolution) {
                try {
                    const response = await fetch('/api/migration/resolve-conflicts', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            batch_id: batchId,
                            conflict_index: conflictIndex,
                            resolution: resolution
                        })
                    });

                    const result = await response.json();
                    alert(result.message);

                    // Reload conflicts
                    document.getElementById('load-conflicts-btn').click();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error resolving conflict');
                }
            };
        });
    </script>
@endpush
