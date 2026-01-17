@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Skills Library</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage and browse available skills</p>
            </div>
        </div>

        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Coming Soon</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Skills functionality is currently under development.</p>
                <div class="mt-6">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
