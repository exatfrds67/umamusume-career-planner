@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Races</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View upcoming races and results</p>
            </div>
        </div>

        <div class="card bg-white dark:bg-gray-800">
            <div class="card-body text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8M3 21h18M3 21l9-9 9 9M12 6.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" />
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Coming Soon</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Race functionality is currently under development.</p>
                <div class="mt-6">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
