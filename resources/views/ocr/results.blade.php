@extends('layouts.app')

@section('title', 'OCR Extraction Results')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'AI & Tools', 'url' => route('ai.dashboard')], ['label' => 'OCR Upload', 'url' => route('ocr.upload')], ['label' => 'OCR Results']]" />

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                            OCR Extraction Results
                        </h1>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Review and correct extracted data before importing.
                        </p>
                    </div>
                    <a href="{{ route('ocr.upload') }}"
                        class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 focus:outline-hidden focus:ring-2 focus:ring-neutral-500 transition-colors"
                        aria-label="Back to OCR Upload page">
                        Back to Upload
                    </a>
                </div>
            </div>

            @if ($extraction)
                <!-- Extraction Info Card -->
                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Screen Type</p>
                            <p class="text-lg font-semibold text-neutral-900 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $extraction->data_type)) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Confidence Score</p>
                            <div class="flex items-center">
                                <p
                                    class="text-lg font-semibold {{ $extraction->confidence_score >= 0.7 ? 'text-green-600 dark:text-green-400' : ($extraction->confidence_score >= 0.5 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ number_format($extraction->confidence_score * 100, 1) }}%
                                </p>
                                @if ($extraction->confidence_score < 0.7)
                                    <span class="ml-2 text-xs text-yellow-600 dark:text-yellow-400" role="alert">
                                        <span aria-hidden="true">⚠️</span> Review recommended
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Processed</p>
                            <p class="text-lg font-semibold text-neutral-900 dark:text-white">
                                {{ $extraction->processed_at?->diffForHumans() ?? 'Processing...' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Screenshot Preview -->
                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Screenshot Preview</h2>
                    <div class="flex justify-center">
                        <img src="{{ Storage::url($extraction->image_path) }}" alt="Uploaded screenshot" loading="lazy"
                            decoding="async"
                            class="max-w-full h-auto rounded-lg border border-neutral-300 dark:border-neutral-600"
                            style="max-height: 500px;">
                    </div>
                </div>

                <!-- Extracted Data Form -->
                <form id="correction-form" method="POST" action="{{ route('ocr.import', $extraction->id) }}">
                    @csrf

                    <div
                        class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            Extracted Data
                            <span class="text-sm font-normal text-neutral-600 dark:text-neutral-400">(Editable)</span>
                        </h2>

                        @if ($extraction->data_type === 'character_stats')
                            @include('ocr.partials.character-stats-form', [
                                'data' => $extraction->parsed_data,
                            ])
                        @elseif($extraction->data_type === 'training_session')
                            @include('ocr.partials.training-session-form', [
                                'data' => $extraction->parsed_data,
                            ])
                        @elseif($extraction->data_type === 'race_result')
                            @include('ocr.partials.race-result-form', ['data' => $extraction->parsed_data])
                        @elseif($extraction->data_type === 'skill_list')
                            @include('ocr.partials.skill-list-form', ['data' => $extraction->parsed_data])
                        @else
                            <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                                <p>Unknown screen type: {{ $extraction->data_type }}</p>
                                <p class="text-sm mt-2">Cannot display extraction form.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Raw Text (Collapsible) -->
                    <div
                        class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-6 mb-6">
                        <button type="button" id="toggle-raw-text"
                            class="w-full flex items-center justify-between text-left focus:outline-hidden"
                            aria-expanded="false" aria-controls="raw-text-content">
                            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Raw OCR Text</h2>
                            <svg class="w-5 h-5 text-neutral-500 transform transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div id="raw-text-content" class="hidden mt-4">
                            <pre
                                class="bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg text-sm text-neutral-700 dark:text-neutral-300 overflow-x-auto whitespace-pre-wrap">{{ $extraction->extracted_text }}</pre>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <button type="submit" name="action" value="import"
                                class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors font-medium">
                                Import Data
                            </button>
                            <button type="submit" name="action" value="save_draft"
                                class="px-6 py-3 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 focus:outline-hidden focus:ring-2 focus:ring-neutral-500 transition-colors font-medium">
                                Save as Draft
                            </button>
                        </div>
                        <button type="button"
                            onclick="if(confirm('Are you sure you want to discard this extraction?')) { window.location.href='{{ route('ocr.upload') }}'; }"
                            class="px-6 py-3 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 focus:outline-hidden transition-colors font-medium">
                            Discard
                        </button>
                    </div>
                </form>
            @else
                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-12 text-center">
                    <p class="text-neutral-500 dark:text-neutral-400 mb-4">No extraction found.</p>
                    <a href="{{ route('ocr.upload') }}"
                        class="inline-block px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-hidden focus:ring-2 focus:ring-primary-500 transition-colors">
                        Upload Screenshot
                    </a>
                </div>
            @endif
        </div>
    </div>

    @vite(['resources/js/pages/ocr/results.js'])
@endsection
