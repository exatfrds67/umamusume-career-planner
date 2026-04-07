@extends('layouts.app')

@section('title', 'Data Management')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management']]" />

    <div class="container mx-auto px-4 py-8 page-stack" x-data="dataManagementHub()">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Data Management</h1>
            <p class="text-neutral-500 dark:text-neutral-400 mt-2 max-w-2xl text-lg">
                Import, export, migrate, and backup your career data in one unified dashboard. Choose an operation below to get started.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Column: Actions Layout --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Quick Actions Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Import Data Card --}}
                    <a href="{{ route('import.index') }}" class="group relative bg-white dark:bg-neutral-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <div class="mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Import Data</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">Bring your outside data into the planner. Supports CSV, JSON, and structured text formats.</p>
                    </a>

                    {{-- Export Data Card --}}
                    <a href="{{ route('export.index') }}" class="group relative bg-white dark:bg-neutral-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <div class="mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/></svg>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Export Data</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">Download your career runs and character data for external analysis or sharing.</p>
                    </a>

                    {{-- Migrate Legacy Card --}}
                    <a href="{{ route('migration.index') }}" class="group relative bg-white dark:bg-neutral-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:border-orange-500 dark:hover:border-orange-500 hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <div class="mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Migrate Legacy Data</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">Convert older umamusume data formats to support our current schema.</p>
                    </a>

                    {{-- Backup Card --}}
                    <a href="{{ route('backup.index') }}" class="group relative bg-white dark:bg-neutral-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
                        <div class="mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Create Backup</h3>
                        <p class="text-neutral-500 dark:text-neutral-400 text-sm">Secure your progress with manual or automated database snapshots to prevent data loss.</p>
                    </a>
                </div>

                {{-- Render History in the Main Column --}}
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                    <div class="p-6 pb-2 border-b border-neutral-200 dark:border-neutral-800">
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Recent Activity History</h2>
                    </div>
                    <div class="p-6">
                        @include('data-management.partials.history')
                    </div>
                </div>

            </div>

            {{-- Sidebar Column: Overview Stats --}}
            <div class="space-y-6">
                <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-xl p-6 border border-neutral-200 dark:border-neutral-800">
                    <h2 class="font-bold text-neutral-900 dark:text-white mb-4">Storage Overview</h2>
                    
                    <ul class="space-y-4">
                        <li class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Total Characters</span>
                            </div>
                            <span class="text-lg font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_characters">0</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Total Careers</span>
                            </div>
                            <span class="text-lg font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_careers">0</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Total Backups</span>
                            </div>
                            <span class="text-lg font-bold text-neutral-900 dark:text-white" x-text="quickStats.total_backups">0</span>
                        </li>
                    </ul>
                </div>

                {{-- Record Operations Breakdown --}}
                <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-xl p-6 border border-neutral-200 dark:border-neutral-800">
                    <h2 class="font-bold text-neutral-900 dark:text-white mb-4">Operations Health</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Success Rate</span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400" x-text="statistics.success_rate + '%'">0%</span>
                            </div>
                            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" :style="'width: ' + statistics.success_rate + '%'"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Records</p>
                                <p class="text-xl font-bold text-neutral-900 dark:text-white mt-1" x-text="statistics.records_processed">0</p>
                            </div>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Failed Ops</p>
                                <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1" x-text="statistics.failed_operations">0</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Ongoing Operations Panel (Unmodified) --}}
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
