@extends('layouts.app')

@section('title', 'Data Management Hub')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management']]" />

    <div class="container mx-auto px-4 py-8 page-stack" x-data="dataManagementHub()">
        {{-- Page Header --}}
        <div class="page-hero">
            <div class="page-hero__content">
            <div>
                <div class="page-hero__eyebrow">
                    <span>Operations Center</span>
                </div>
            <h1 class="page-hero__title">Data Management Hub</h1>
            <p class="page-hero__body text-sm sm:text-base">
                Unified interface for importing, exporting, migrating, and backing up your career data.
            </p>
        </div>
            <div class="page-hero__meta">
                <span class="hero-chip">Import</span>
                <span class="hero-chip">Export</span>
                <span class="hero-chip">Backup</span>
            </div>
            </div>
        </div>

        {{-- Quick Stats Dashboard --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8" role="region" aria-label="Data summary statistics">
            <div class="metric-card metric-card--primary">
                <div class="metric-card__body">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Characters</p>
                        <p class="text-xl font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_characters" aria-live="polite">0</p>
                    </div>
                </div>
                </div>
            </div>
            <div class="metric-card metric-card--secondary">
                <div class="metric-card__body">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Careers</p>
                        <p class="text-xl font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_careers" aria-live="polite">0</p>
                    </div>
                </div>
                </div>
            </div>
            <div class="metric-card metric-card--warning">
                <div class="metric-card__body">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Backups</p>
                        <p class="text-xl font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_backups" aria-live="polite">0</p>
                    </div>
                </div>
                </div>
            </div>
            <div class="metric-card metric-card--success">
                <div class="metric-card__body">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30 shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Success Rate</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400" x-text="statistics.success_rate + '%'" aria-live="polite">0%</p>
                    </div>
                </div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="tab-surface">
            <div class="border-b border-neutral-200 dark:border-neutral-700">
                <nav class="flex -mb-px overflow-x-auto" role="tablist" aria-label="Data management tabs">
                    <button type="button" role="tab" id="tab-btn-overview" aria-controls="tabpanel-overview"
                        :aria-selected="activeTab === 'overview'" :tabindex="activeTab === 'overview' ? 0 : -1"
                        @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Overview
                    </button>
                    <button type="button" role="tab" id="tab-btn-import" aria-controls="tabpanel-import"
                        :aria-selected="activeTab === 'import'" :tabindex="activeTab === 'import' ? 0 : -1"
                        @click="activeTab = 'import'"
                        :class="activeTab === 'import' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Import
                    </button>
                    <button type="button" role="tab" id="tab-btn-export" aria-controls="tabpanel-export"
                        :aria-selected="activeTab === 'export'" :tabindex="activeTab === 'export' ? 0 : -1"
                        @click="activeTab = 'export'"
                        :class="activeTab === 'export' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/></svg>
                        Export
                    </button>
                    <button type="button" role="tab" id="tab-btn-migration" aria-controls="tabpanel-migration"
                        :aria-selected="activeTab === 'migration'" :tabindex="activeTab === 'migration' ? 0 : -1"
                        @click="activeTab = 'migration'"
                        :class="activeTab === 'migration' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Migration
                    </button>
                    <button type="button" role="tab" id="tab-btn-backup" aria-controls="tabpanel-backup"
                        :aria-selected="activeTab === 'backup'" :tabindex="activeTab === 'backup' ? 0 : -1"
                        @click="activeTab = 'backup'"
                        :class="activeTab === 'backup' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Backup
                    </button>
                    <button type="button" role="tab" id="tab-btn-history" aria-controls="tabpanel-history"
                        :aria-selected="activeTab === 'history'" :tabindex="activeTab === 'history' ? 0 : -1"
                        @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        History
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Overview Tab --}}
                <div role="tabpanel" id="tabpanel-overview" aria-labelledby="tab-btn-overview"
                    x-show="activeTab === 'overview'" x-transition>
                    @include('data-management.partials.overview')
                </div>

                {{-- Import Tab --}}
                <div role="tabpanel" id="tabpanel-import" aria-labelledby="tab-btn-import"
                    x-show="activeTab === 'import'" x-cloak x-transition>
                    @include('data-management.partials.import')
                </div>

                {{-- Export Tab --}}
                <div role="tabpanel" id="tabpanel-export" aria-labelledby="tab-btn-export"
                    x-show="activeTab === 'export'" x-cloak x-transition>
                    @include('data-management.partials.export')
                </div>

                {{-- Migration Tab --}}
                <div role="tabpanel" id="tabpanel-migration" aria-labelledby="tab-btn-migration"
                    x-show="activeTab === 'migration'" x-cloak x-transition>
                    @include('data-management.partials.migration')
                </div>

                {{-- Backup Tab --}}
                <div role="tabpanel" id="tabpanel-backup" aria-labelledby="tab-btn-backup"
                    x-show="activeTab === 'backup'" x-cloak x-transition>
                    @include('data-management.partials.backup')
                </div>

                {{-- History Tab --}}
                <div role="tabpanel" id="tabpanel-history" aria-labelledby="tab-btn-history"
                    x-show="activeTab === 'history'" x-cloak x-transition>
                    @include('data-management.partials.history')
                </div>
            </div>
        </div>

        {{-- Ongoing Operations Panel --}}
        <div x-show="ongoingOperations.length > 0" x-transition
            class="fixed bottom-4 right-4 w-96 bg-white dark:bg-neutral-800 rounded-lg shadow-xl border border-neutral-200 dark:border-neutral-700 z-50">
            <div class="p-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-neutral-900 dark:text-white">Ongoing Operations</h3>
                    <span
                        class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full"
                        x-text="ongoingOperations.length"></span>
                </div>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto space-y-3">
                <template x-for="op in ongoingOperations" :key="op.operation_id">
                    <x-data-management.progress-tracker x-bind:operation-id="op.operation_id"
                        x-bind:operation-type="op.operation_type" x-bind:status="op.status" x-bind:message="op.message"
                        :show-details="false" />
                </template>
            </div>
        </div>
    </div>
    @vite(['resources/js/pages/data-management/index.js'])
@endsection
