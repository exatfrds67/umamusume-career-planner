{{-- Phase 6: Import/Export Handler Component --}}
<div x-data="importExportHandler()">
    <!-- Import Section -->
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-neutral-900 dark:text-white mb-2">
                Import Plans
            </label>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg cursor-pointer transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>Choose File</span>
                    <input
                        type="file"
                        accept=".json,.csv"
                        @change="handleFileUpload"
                        class="hidden"
                    />
                </label>
                <span class="text-sm text-neutral-500 dark:text-neutral-400" x-show="currentFormat">
                    Format: <span x-text="currentFormat" class="font-mono"></span>
                </span>
            </div>
        </div>

        <!-- Import Progress -->
        <div x-show="isImporting" class="space-y-2">
            <div class="h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-blue-600 transition-all duration-300"
                    :style="`width: ${(importProgress / importTotal) * 100}%`"
                ></div>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Importing... <span x-text="`${importProgress}/${importTotal}`"></span>
            </p>
        </div>

        <!-- Import Preview -->
        <div x-show="previewData" class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-4 bg-neutral-50 dark:bg-neutral-800">
            <h3 class="text-sm font-medium text-neutral-900 dark:text-white mb-2">Preview</h3>
            <div class="max-h-40 overflow-y-auto">
                <template x-if="Array.isArray(previewData)">
                    <ul class="space-y-1 text-sm">
                        <template x-for="(item, idx) in previewData.slice(0, 5)" :key="idx">
                            <li class="text-neutral-600 dark:text-neutral-400">
                                <span x-text="item.characterName || item.name || `Item ${idx + 1}`"></span>
                            </li>
                        </template>
                        <template x-if="previewData.length > 5">
                            <li class="text-neutral-500 italic text-xs">
                                <span x-text="`+${previewData.length - 5} more items`"></span>
                            </li>
                        </template>
                    </ul>
                </template>
                <template x-if="!Array.isArray(previewData)">
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">
                        <p><span x-text="`Character: ${previewData.characterName}`"></span></p>
                        <p><span x-text="`Turns: ${previewData.turns?.length || 0}`"></span></p>
                    </div>
                </template>
            </div>
            <button
                @click="processPlanImport(previewData)"
                class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition"
            >
                Confirm Import
            </button>
        </div>

        <!-- Error Messages -->
        <template x-if="importErrors.length > 0">
            <div class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
                <h4 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Errors</h4>
                <ul class="text-xs text-red-700 dark:text-red-300 space-y-0.5">
                    <template x-for="error in importErrors" :key="error">
                        <li>• <span x-text="error"></span></li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Warning Messages -->
        <template x-if="importWarnings.length > 0">
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-1">Warnings</h4>
                <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-0.5">
                    <template x-for="warning in importWarnings" :key="warning">
                        <li>⚠ <span x-text="warning"></span></li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Success Messages -->
        <template x-if="importSuccesses.length > 0">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg">
                <h4 class="text-sm font-medium text-green-800 dark:text-green-200 mb-1">
                    <span x-text="`Successfully imported ${importSuccesses.length} plan(s)`"></span>
                </h4>
                <ul class="text-xs text-green-700 dark:text-green-300 space-y-0.5">
                    <template x-for="success in importSuccesses.slice(0, 3)" :key="success">
                        <li>✓ <span x-text="success"></span></li>
                    </template>
                    <template x-if="importSuccesses.length > 3">
                        <li class="italic text-xs">
                            <span x-text="`+${importSuccesses.length - 3} more`"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
</div>
