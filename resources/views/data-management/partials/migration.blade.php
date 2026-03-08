{{-- Migration Tab Content --}}
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Data Migration</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Convert legacy data formats and migrate from external
                sources.</p>
        </div>
        <a href="{{ route('migration.index') }}"
            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Open Migration Tool
        </a>
    </div>

    {{-- Legacy Formats --}}
    <div>
        <h4 class="font-medium text-neutral-900 dark:text-white mb-3">Supported Legacy Formats</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach ($legacyFormats as $format => $label)
                <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 text-center">
                    <div class="flex justify-center mb-2" aria-hidden="true">
                        @switch($format)
                            @case('v1_json')
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @break
                            @case('v1_csv')
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @break
                            @case('google_sheets')
                                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @break
                            @case('excel')
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @break
                            @case('custom')
                                <svg class="w-8 h-8 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @break
                        @endswitch
                    </div>
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Migration Features --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-neutral-200 dark:bg-neutral-600 shrink-0 mt-0.5" aria-hidden="true">
                    <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div>
                    <h5 class="font-medium text-neutral-900 dark:text-white">Auto Format Detection</h5>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Automatically detects the format of your legacy
                        data and suggests the best conversion approach.</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30 shrink-0 mt-0.5" aria-hidden="true">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h5 class="font-medium text-neutral-900 dark:text-white">Data Validation</h5>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Validates all data during migration to ensure
                        integrity and catch errors early.</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 shrink-0 mt-0.5" aria-hidden="true">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                <div>
                    <h5 class="font-medium text-neutral-900 dark:text-white">Conflict Resolution</h5>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Smart conflict detection with options to skip,
                        overwrite, merge, or rename duplicate records.</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/30 shrink-0 mt-0.5" aria-hidden="true">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div>
                    <h5 class="font-medium text-neutral-900 dark:text-white">Batch Processing</h5>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Process large datasets in batches with progress
                        tracking and pause/resume support.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Migration Tips --}}
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <div>
                <h4 class="font-medium text-yellow-800 dark:text-yellow-200">Before You Migrate</h4>
                <ul class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                    <li>• Create a backup of your current data first</li>
                    <li>• Review the format detection results before proceeding</li>
                    <li>• Use preview mode to check data transformation</li>
                    <li>• Large migrations may take several minutes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
