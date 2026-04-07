@extends('layouts.app')

@section('title', 'Data Export')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Export Data']]" />

    <div class="container mx-auto px-4 py-8 page-stack">
        {{-- Page Header --}}
        <div class="page-hero">
            <div class="page-hero__content">
            <div>
            <h1 class="page-hero__title">Export career run data</h1>
            <p class="page-hero__body text-sm sm:text-base">
                Export your career data in multiple formats including JSON, CSV, and PDF.
            </p>
        </div>
            <div class="page-hero__meta">
                <span class="hero-chip">Shareable formats</span>
                <span class="hero-chip">Filter before export</span>
            </div>
            </div>
        </div>

        {{-- Export Type Selection --}}
        <div class="filter-surface p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export Type
                </span>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4" role="group" aria-label="Export type selection">
                @foreach ($exportTypes as $type => $label)
                    <button type="button"
                        class="export-type-btn p-4 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        data-type="{{ $type }}" aria-pressed="false">
                        <div class="flex justify-center mb-2" aria-hidden="true">
                            @switch($type)
                                @case('character')
                                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                @break

                                @case('career')
                                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                @break

                                @case('training_session')
                                    <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                @break

                                @case('skill')
                                    <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                @break

                                @case('support_card')
                                    <svg class="w-7 h-7 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                @break

                                @case('full_backup')
                                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                @break
                            @endswitch
                        </div>
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" id="export-type" name="export_type" value="">
            <p id="export-type-hint" class="mt-3 text-sm text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Select an export type above to enable Preview and Export.
            </p>
        </div>

        {{-- Format Selection --}}
        <div class="filter-surface p-6 mt-4">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    Export Format
                </span>
            </h2>

            <div class="grid grid-cols-3 gap-4" role="group" aria-label="Export format selection">
                @foreach ($supportedFormats as $format)
                    <button type="button"
                        class="format-btn p-4 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        data-format="{{ $format }}" aria-pressed="false">
                        <div class="flex justify-center mb-2" aria-hidden="true">
                            @switch($format)
                                @case('json')
                                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @break

                                @case('csv')
                                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                @break

                                @case('pdf')
                                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                @break
                            @endswitch
                        </div>
                        <span
                            class="text-sm font-medium text-neutral-700 dark:text-neutral-300 uppercase">{{ $format }}</span>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            @switch($format)
                                @case('json')
                                    Structured data format
                                @break

                                @case('csv')
                                    Spreadsheet compatible
                                @break

                                @case('pdf')
                                    Printable report
                                @break
                            @endswitch
                        </p>
                    </button>
                @endforeach
            </div>
            <input type="hidden" id="export-format" name="format" value="json">
        </div>

        {{-- Filters Section --}}
        <div x-data="{ showFilters: false }" class="filter-surface mt-4">
            <button type="button" @click="showFilters = !showFilters" class="w-full flex items-center justify-between p-6 focus:outline-none">
                <span class="text-lg font-semibold text-neutral-900 dark:text-white inline-flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Customize Export
                </span>
                <span class="flex items-center text-sm text-neutral-500">
                    Optional
                    <svg :class="{'rotate-180': showFilters}" class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>

            <div x-show="showFilters" x-collapse x-cloak class="px-6 pb-6 pt-2 border-t border-neutral-100 dark:border-neutral-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Scenario Type Filter --}}
                <div>
                    <label for="filter-scenario" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Scenario Type
                    </label>
                    <select id="filter-scenario" name="filters[scenario_type]"
                        class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Scenarios</option>
                        <option value="ura_finale">URA Finale</option>
                        <option value="unity_cup">Unity Cup</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="filter-status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Status
                    </label>
                    <select id="filter-status" name="filters[status]"
                        class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="abandoned">Abandoned</option>
                    </select>
                </div>

                {{-- Date Range Filter --}}
                <div>
                    <label for="filter-date-from" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Date Range
                    </label>
                    <div class="flex gap-2">
                        <input type="date" id="filter-date-from" name="filters[date_from]"
                            aria-label="From date"
                            class="flex-1 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="From">
                        <input type="date" id="filter-date-to" name="filters[date_to]"
                            aria-label="To date"
                            class="flex-1 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="To">
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview Section --}}
        <div id="preview-section" class="hidden mt-4">
            <div class="filter-surface p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        Export Preview
                    </span>
                </h2>

                {{-- Preview Summary --}}
                <div id="preview-summary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-4">
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Total Records</div>
                        <div id="total-records" class="text-2xl font-bold text-neutral-900 dark:text-white">0</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Export Type</div>
                        <div id="preview-type" class="text-2xl font-bold text-blue-700 dark:text-blue-300">-</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-sm text-green-600 dark:text-green-400">Format</div>
                        <div id="preview-format" class="text-2xl font-bold text-green-700 dark:text-green-300 uppercase">-
                        </div>
                    </div>
                </div>

                {{-- Preview Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-700">
                            <tr id="preview-headers"></tr>
                        </thead>
                        <tbody id="preview-body"
                            class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700"></tbody>
                    </table>
                </div>

                <div id="preview-more" class="hidden mt-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                    Showing first 10 records. Full export will include all <span id="preview-total">0</span> records.
                </div>
            </div>
        </div>

        {{-- Export History & Scheduled Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            {{-- Export History Section (Minimal Card) --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-5">
                <h2 class="text-md font-semibold text-neutral-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Recent Exports
                </h2>
                <div id="export-history" class="text-sm">
                    <div class="flex flex-col items-center justify-center p-4 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg border border-dashed border-neutral-200 dark:border-neutral-700">
                        <svg class="w-6 h-6 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-neutral-500 dark:text-neutral-400">No export history yet.</p>
                    </div>
                </div>
            </div>

            {{-- Scheduled Exports Section (Minimal Card) --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-5">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-md font-semibold text-neutral-900 dark:text-white flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Scheduled Exports
                    </h2>
                    <button type="button" id="schedule-btn-secondary" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                        Schedule New
                    </button>
                </div>
                <div id="scheduled-exports" class="text-sm">
                    <div class="flex flex-col items-center justify-center p-4 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg border border-dashed border-neutral-200 dark:border-neutral-700">
                        <svg class="w-6 h-6 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-neutral-500 dark:text-neutral-400">No automated exports scheduled.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Templates Section --}}
        <div x-data="{ open: false }" class="mt-4 bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700">
            <button @click="open = !open" type="button" class="w-full flex items-center justify-between p-6 focus:outline-none">
                <span class="text-lg font-semibold text-neutral-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Export Templates
                </span>
                <svg :class="{'rotate-180': open}" class="w-5 h-5 text-neutral-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-collapse x-cloak class="px-6 pb-6 border-t border-neutral-100 dark:border-neutral-700 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($templates as $key => $template)
                        <button type="button"
                            class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-left template-card focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                            data-template="{{ $key }}">
                            <h3 class="font-medium text-neutral-900 dark:text-white mb-1">{{ $template['name'] }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $template['description'] }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($template['types'] as $type)
                                    <span
                                        class="px-2 py-0.5 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded">
                                        {{ $type }}
                                    </span>
                                @endforeach
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky Action Footer --}}
    <div class="sticky bottom-0 z-40 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-t border-neutral-200 dark:border-neutral-800 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <button type="button" id="schedule-btn"
                class="inline-flex items-center px-6 py-2.5 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium rounded-lg transition-colors focus:ring-2 focus:ring-neutral-500 focus:outline-none">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Schedule Export
            </button>
            <div class="flex gap-4 ml-auto">
                <button type="button" id="preview-btn"
                    class="inline-flex items-center px-6 py-2.5 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <svg class="w-5 h-5 mr-2 -ml-1 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    Preview
                </button>
                <button type="button" id="export-btn"
                    class="inline-flex items-center px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export Data
                </button>
            </div>
        </div>
    </div>

    {{-- Schedule Modal --}}
    <div id="schedule-modal" class="hidden fixed inset-0 bg-black/50 z-50" role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 id="schedule-modal-title" class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Schedule Automated Export</h3>

                <form id="schedule-form" class="space-y-4">
                    <div>
                        <label for="schedule-type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Export Type</label>
                        <select id="schedule-type" name="export_type" required
                            class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white">
                            @foreach ($exportTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="schedule-format" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Format</label>
                        <select id="schedule-format" name="format"
                            class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white">
                            @foreach ($supportedFormats as $format)
                                <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="schedule-frequency" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Frequency</label>
                        <select id="schedule-frequency" name="frequency" required
                            class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div id="day-of-week-container" class="hidden">
                        <label for="schedule-day-of-week" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Day of Week</label>
                        <select id="schedule-day-of-week" name="day_of_week"
                            class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white">
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </select>
                    </div>

                    <div>
                        <label for="schedule-time" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Time</label>
                        <input type="time" id="schedule-time" name="time" value="00:00"
                            class="w-full px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="schedule-email" name="email_notification"
                            class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                        <label for="schedule-email" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                            Send email notification when export is ready
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" id="cancel-schedule-btn"
                            class="px-4 py-2 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-600 dark:hover:bg-neutral-500 text-neutral-700 dark:text-neutral-200 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Schedule Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/export/index.js'])
@endsection
