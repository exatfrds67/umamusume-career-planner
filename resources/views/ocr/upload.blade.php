@extends('layouts.app')

@section('title', 'OCR Screenshot Upload')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'AI & Tools', 'url' => route('ai.dashboard')], ['label' => 'OCR Upload']]" />

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                    Screenshot OCR Processing
                </h1>
                <p class="text-neutral-600 dark:text-neutral-400">
                    Upload game screenshots to automatically extract character stats, training data, race results, and
                    skills.
                </p>
            </div>

            <!-- OCR Status Card -->
            <div id="ocr-status-card"
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1">OCR System Status</h2>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Checking OCR availability...</p>
                    </div>
                    <div id="ocr-status-indicator" class="flex items-center" role="status" aria-label="Checking OCR system status">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500" aria-hidden="true"></div>
                        <span class="sr-only">Loading OCR status...</span>
                    </div>
                </div>
            </div>

            <!-- Character Selection -->
            <div
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="character-select"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Select Character
                        </label>
                        <select id="character-select"
                            class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                            aria-label="Select character for OCR data import">
                            <option value="">-- Select a character --</option>
                            @foreach ($characters as $character)
                                <option value="{{ $character->id }}">
                                    {{ $character->name }} ({{ ucfirst($character->scenario_type) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Select the character to import extracted data into.
                        </p>
                    </div>

                    <!-- Data Type Selector (per OCR spec requirement) -->
                    <div>
                        <label for="data-type-select"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Data Type
                        </label>
                        <select id="data-type-select"
                            class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                            aria-label="Select type of data to extract from screenshot">
                            <option value="auto">Auto-detect</option>
                            <option value="character_stats">Character Stats</option>
                            <option value="training_session">Training Session</option>
                            <option value="race_result">Race Result</option>
                            <option value="skill_list">Skill List</option>
                            <option value="support_card">Support Card Info</option>
                        </select>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Choose the type of data to extract, or use auto-detect.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                <div id="drop-zone"
                    class="border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg p-12 text-center transition-colors hover:border-primary-500 dark:hover:border-primary-400 cursor-pointer"
                    role="button" tabindex="0" aria-label="Drag and drop screenshots or click to select files"
                    aria-describedby="upload-format-info">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-neutral-500 dark:text-neutral-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <p class="text-lg font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Drag and drop screenshots here
                        </p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                            or click to browse files
                        </p>
                        <button type="button" id="browse-button"
                            class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                            Browse Files
                        </button>
                        <input type="file" id="file-input" class="hidden"
                            accept="image/jpeg,image/jpg,image/png,image/webp" multiple
                            aria-label="Select screenshot files">
                    </div>
                    <div id="upload-format-info" class="mt-4 text-xs text-neutral-500 dark:text-neutral-400">
                        Supported formats: JPG, PNG, WEBP • Max size: <span id="max-file-size">10 MB</span> per file
                    </div>
                </div>
            </div>

            <!-- Upload Queue -->
            <div id="upload-queue" class="hidden mb-6">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Upload Queue</h2>
                        <div class="flex gap-2">
                            <button id="process-all-button"
                                class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-hidden focus:ring-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                Process All
                            </button>
                            <button id="clear-queue-button"
                                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 focus:outline-hidden focus:ring-2 focus:ring-neutral-500 transition-colors">
                                Clear Queue
                            </button>
                        </div>
                    </div>
                    <div id="queue-items" class="space-y-3">
                        <!-- Queue items will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Processing Results -->
            <div id="processing-results" class="hidden">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Processing Results</h2>
                    <div id="results-container" class="space-y-4">
                        <!-- Results will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Extracted: JS logic + routes moved to resources/js/pages/ocr/upload.js --}}
    @vite(['resources/js/pages/ocr/upload.js'])
@endsection
