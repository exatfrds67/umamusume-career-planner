@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Backup & Restore']]" />

    <div id="backup-page" class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Backup & Restore</h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                Manage your data backups, schedule automated backups, and restore from previous backups.
            </p>
        </div>

        <div id="backup-page-status" class="sr-only" aria-live="polite"></div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs p-6 border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Backups</p>
                        <p class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            {{ $statistics['total_backups'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs p-6 border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Storage Used</p>
                        <p class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            {{ $statistics['total_size_formatted'] ?? '0 B' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs p-6 border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Active Schedules</p>
                        <p class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ $statistics['schedules'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs p-6 border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Encrypted</p>
                        <p class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            {{ $statistics['by_status']['encrypted'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div id="create" class="flex scroll-mt-24 flex-wrap gap-4 mb-8">
            <button type="button" id="create-backup-btn"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Backup
            </button>
            <button type="button" id="schedule-backup-btn"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Schedule Backup
            </button>
            <button type="button" id="cleanup-btn"
                class="inline-flex items-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
                Cleanup Old Backups
            </button>
        </div>

        <!-- Backup List -->
        <div id="restore" class="scroll-mt-24 bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 mb-8">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Backup History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Backup ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Size</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Created</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700"
                        id="backup-list">
                        @forelse($backups['backups'] ?? [] as $backup)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ Str::limit($backup['backup_id'], 8) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ ucfirst($backup['type']) }}
                                    </span>
                                    @if ($backup['encrypted'] ?? false)
                                        <span class="ml-1 inline-flex items-center" title="Encrypted">
                                            <svg class="w-4 h-4 text-yellow-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span class="sr-only">Encrypted</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $backup['file_size'] ? number_format($backup['file_size'] / 1024, 2) . ' KB' : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $backup['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $backup['status'] === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                {{ $backup['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                                        {{ ucfirst($backup['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ \Carbon\Carbon::parse($backup['created_at'])->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3 restore-btn focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                                        data-backup-id="{{ $backup['backup_id'] }}"
                                        data-encrypted="{{ $backup['encrypted'] ? 'true' : 'false' }}"
                                        aria-label="Restore backup {{ Str::limit($backup['backup_id'], 8) }}">Restore</button>
                                    <button type="button"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 delete-btn focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                                        data-backup-id="{{ $backup['backup_id'] }}"
                                        aria-label="Delete backup {{ Str::limit($backup['backup_id'], 8) }}">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                    <p class="text-lg font-medium">No backups yet</p>
                                    <p class="mt-2">Create your first backup to protect your data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Scheduled Backups -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Scheduled Backups</h2>
            </div>
            <div class="p-6">
                @forelse($schedules['schedules'] ?? [] as $schedule)
                    <div
                        class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-900 rounded-lg mb-4 last:mb-0">
                        <div class="flex items-center">
                            <div
                                class="p-2 rounded-full {{ $schedule['enabled'] ? 'bg-green-100 dark:bg-green-900' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <svg class="w-5 h-5 {{ $schedule['enabled'] ? 'text-green-600 dark:text-green-400' : 'text-neutral-500' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ ucfirst($schedule['frequency']) }} at {{ $schedule['time'] }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ ucfirst($schedule['backup_type']) }} backup • Retention:
                                    {{ $schedule['retention_days'] }} days</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $schedule['enabled'] ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-800' }}">
                                {{ $schedule['enabled'] ? 'Active' : 'Disabled' }}
                            </span>
                            <button type="button" class="text-red-600 hover:text-red-900 delete-schedule-btn"
                                data-schedule-id="{{ $schedule['schedule_id'] }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                        <p class="text-lg font-medium">No scheduled backups</p>
                        <p class="mt-2">Set up automated backups to protect your data regularly.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/backup/index.js'])
@endsection
