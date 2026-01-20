@extends('layouts.app')

@section('title', 'Data Management Hub')

@section('content')
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
@endsection

@push('scripts')
    <script>
        function dataManagementHub() {
            return {
                activeTab: 'overview',
                loading: false,
                quickStats: {
                    total_characters: 0,
                    total_careers: 0,
                    total_backups: 0,
                    last_backup: null
                },
                statistics: {
                    total_operations: 0,
                    successful_operations: 0,
                    failed_operations: 0,
                    success_rate: 0,
                    by_type: {},
                    records_processed: 0
                },
                recentOperations: [],
                ongoingOperations: [],
                history: [],
                historyPagination: null,
                historyFilters: {
                    operation_type: '',
                    status: '',
                    date_from: '',
                    date_to: ''
                },

                init() {
                    this.loadDashboard();
                    // Poll for ongoing operations every 5 seconds
                    setInterval(() => this.checkOngoingOperations(), 5000);
                },

                async loadDashboard() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/data-management/dashboard', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.quickStats = result.data.quick_stats;
                            this.statistics = result.data.statistics;
                            this.recentOperations = result.data.recent_operations;
                            this.ongoingOperations = result.data.ongoing_operations;
                        }
                    } catch (error) {
                        console.error('Failed to load dashboard:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async checkOngoingOperations() {
                    try {
                        const response = await fetch('/api/data-management/status', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.ongoingOperations = result.data.ongoing_operations;
                        }
                    } catch (error) {
                        console.error('Failed to check ongoing operations:', error);
                    }
                },

                async loadHistory(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: page,
                            ...Object.fromEntries(Object.entries(this.historyFilters).filter(([_, v]) => v))
                        });
                        const response = await fetch(`/api/data-management/history?${params}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.history = result.data;
                            this.historyPagination = result.pagination;
                        }
                    } catch (error) {
                        console.error('Failed to load history:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                showToast(message, type = 'info') {
                    // Simple toast notification
                    const toast = document.createElement('div');
                    toast.className = `fixed bottom-4 left-4 px-4 py-2 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                'bg-blue-500'
            } text-white`;
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            };
        }
    </script>
@endpush
