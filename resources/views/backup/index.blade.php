@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Data Management', 'url' => route('data-management.index')], ['label' => 'Backup & Restore']]" />

    <div id="backup-page" class="container mx-auto px-4 py-8 mb-24 max-w-5xl">
        <!-- Page Header (Flat Apple-style, No Eyebrow) -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-semibold text-neutral-900 dark:text-white tracking-tight">Backup & Restore</h1>
            <p class="mt-3 text-neutral-500 dark:text-neutral-400">
                Manage your data backups, schedule automated backups, and restore from previous backups.
            </p>
        </div>

        <div id="backup-page-status" class="sr-only" aria-live="polite"></div>

        <!-- 3 Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white dark:bg-neutral-800/80 rounded-2xl p-6 border border-neutral-100 dark:border-neutral-700/50 shadow-sm flex items-center">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Backups</p>
                    <p class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ $statistics['total_backups'] ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800/80 rounded-2xl p-6 border border-neutral-100 dark:border-neutral-700/50 shadow-sm flex items-center">
                <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Storage Used</p>
                    <p class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ $statistics['total_size_formatted'] ?? '0 B' }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800/80 rounded-2xl p-6 border border-neutral-100 dark:border-neutral-700/50 shadow-sm flex items-center">
                <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl text-purple-600 dark:text-purple-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Active Schedules</p>
                    <p class="text-2xl font-semibold text-neutral-900 dark:text-white">{{ $statistics['schedules'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- 3 Task Cards -->
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Operations</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Create Task -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs flex flex-col overflow-hidden">
                <div class="p-6 flex-1">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Create backup</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-6">
                        Immediately secure your current application state, saving all settings and runs to a discrete backup point.
                    </p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-700 mt-auto">
                    <button type="button" id="create-backup-btn" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Now
                    </button>
                </div>
            </div>

            <!-- Restore Task -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs flex flex-col overflow-hidden">
                <div class="p-6 flex-1">
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Restore</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-6">
                        Roll back to a previous backup snapshot or clear out old expired backups to save space.
                    </p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-700 mt-auto flex gap-2">
                    <a href="#restore" class="flex-1 justify-center inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 font-medium rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500">
                        View List
                    </a>
                    <button type="button" id="cleanup-btn" class="flex-1 justify-center inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 font-medium rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500">
                        Cleanup
                    </button>
                </div>
            </div>

            <!-- Schedule Task -->
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-xs flex flex-col overflow-hidden">
                <div class="p-6 flex-1">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Schedule automated</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-6">
                        Set up automated routine backups to protect data continually without manual intervention.
                    </p>
                </div>
                <div class="p-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-100 dark:border-neutral-700 mt-auto">
                    <button type="button" id="schedule-backup-btn" class="w-full justify-center inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Setup Schedule
                    </button>
                </div>
            </div>
        </div>

        <!-- Scheduled Backups Tiny Section -->
        <div class="mb-10">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Scheduled Backups</h2>
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xs border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                @forelse($schedules['schedules'] ?? [] as $schedule)
                    <div class="flex items-center justify-between p-4 {{ !$loop->last ? 'border-b border-neutral-200 dark:border-neutral-700' : '' }} hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full {{ $schedule['enabled'] ? 'bg-green-100 dark:bg-green-900/30' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <svg class="w-4 h-4 {{ $schedule['enabled'] ? 'text-green-600 dark:text-green-400' : 'text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ucfirst($schedule['frequency']) }} at {{ $schedule['time'] }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ ucfirst($schedule['backup_type']) }} backup • Retention: {{ $schedule['retention_days'] }} days</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $schedule['enabled'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300' }}">
                                {{ $schedule['enabled'] ? 'Active' : 'Disabled' }}
                            </span>
                            <button type="button" class="text-red-500 hover:text-red-700 delete-schedule-btn transition-colors p-1" data-schedule-id="{{ $schedule['schedule_id'] }}" aria-label="Delete schedule">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col sm:flex-row items-center justify-between p-6 text-neutral-500 dark:text-neutral-400 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-neutral-100 dark:bg-neutral-700 rounded-lg text-neutral-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">No schedules configured</p>
                                <p class="text-sm">Never miss a backup by scheduling daily or weekly intervals.</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('schedule-backup-btn').click()" class="text-sm flex-shrink-0 text-purple-600 hover:text-purple-700 dark:text-purple-400 font-medium">
                            Setup basic schedule &rarr;
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Backup List Tiny Section -->
        <div id="restore" class="scroll-mt-24">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Backup History</h2>
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xs border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                @if(empty($backups['backups'] ?? []))
                    <div class="flex flex-col sm:flex-row items-center justify-between p-6 text-neutral-500 dark:text-neutral-400 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-neutral-100 dark:bg-neutral-700 rounded-lg text-neutral-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">Empty backup archive</p>
                                <p class="text-sm">Store complete snapshots of your current application state.</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('create-backup-btn').click()" class="text-sm flex-shrink-0 text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                            Create first backup &rarr;
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50/50 dark:bg-neutral-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Size</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700" id="backup-list">
                                @foreach($backups['backups'] as $backup)
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-neutral-900 dark:text-white">
                                            {{ Str::limit($backup['backup_id'], 8) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300">
                                                {{ ucfirst($backup['type']) }}
                                            </span>
                                            @if ($backup['encrypted'] ?? false)
                                                <span class="ml-2 inline-flex items-center" title="Encrypted Backup">
                                                    <svg class="w-3.5 h-3.5 text-yellow-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    <span class="sr-only">Encrypted</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400 text-right">
                                            {{ $backup['file_size'] ? number_format($backup['file_size'] / 1024, 2) . ' KB' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                {{ $backup['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                                {{ $backup['status'] === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}
                                                {{ $backup['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}">
                                                {{ ucfirst($backup['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ \Carbon\Carbon::parse($backup['created_at'])->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right flex justify-end gap-3">
                                            <button type="button" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors restore-btn" data-backup-id="{{ $backup['backup_id'] }}" data-encrypted="{{ $backup['encrypted'] ? 'true' : 'false' }}">Restore</button>
                                            <button type="button" class="text-red-500 hover:text-red-700 transition-colors delete-btn" data-backup-id="{{ $backup['backup_id'] }}" aria-label="Delete">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/backup/index.js'])
@endsection
