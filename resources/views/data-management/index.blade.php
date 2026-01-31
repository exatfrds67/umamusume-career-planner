@extends('layouts.app')

@section('title', 'Data Management Hub')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management']]" />

    <div class="container mx-auto px-4 py-8" x-data="dataManagementHub()">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Management Hub</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Unified interface for importing, exporting, migrating, and backing up your career data.
            </p>
        </div>

        {{-- Quick Stats Dashboard --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Characters</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_characters">0
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Careers</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_careers">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💾</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Backups</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="quickStats.total_backups">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Success Rate</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400"
                            x-text="statistics.success_rate + '%'">0%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Data management tabs">
                    <button type="button" @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📋</span> Overview
                    </button>
                    <button type="button" @click="activeTab = 'import'"
                        :class="activeTab === 'import' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📥</span> Import
                    </button>
                    <button type="button" @click="activeTab = 'export'"
                        :class="activeTab === 'export' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📤</span> Export
                    </button>
                    <button type="button" @click="activeTab = 'migration'"
                        :class="activeTab === 'migration' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>🔄</span> Migration
                    </button>
                    <button type="button" @click="activeTab = 'backup'"
                        :class="activeTab === 'backup' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>💾</span> Backup
                    </button>
                    <button type="button" @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <span>📜</span> History
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Overview Tab --}}
                <div x-show="activeTab === 'overview'" x-transition>
                    @include('data-management.partials.overview')
                </div>

                {{-- Import Tab --}}
                <div x-show="activeTab === 'import'" x-cloak x-transition>
                    @include('data-management.partials.import')
                </div>

                {{-- Export Tab --}}
                <div x-show="activeTab === 'export'" x-cloak x-transition>
                    @include('data-management.partials.export')
                </div>

                {{-- Migration Tab --}}
                <div x-show="activeTab === 'migration'" x-cloak x-transition>
                    @include('data-management.partials.migration')
                </div>

                {{-- Backup Tab --}}
                <div x-show="activeTab === 'backup'" x-cloak x-transition>
                    @include('data-management.partials.backup')
                </div>

                {{-- History Tab --}}
                <div x-show="activeTab === 'history'" x-cloak x-transition>
                    @include('data-management.partials.history')
                </div>
            </div>
        </div>

        {{-- Ongoing Operations Panel --}}
        <div x-show="ongoingOperations.length > 0" x-transition
            class="fixed bottom-4 right-4 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900 dark:text-white">Ongoing Operations</h3>
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
