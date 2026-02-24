@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <nav class="flex text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-gray-100">Pattern Analytics</span>
        </nav>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Pattern Analytics</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">K-means clustering, association rule mining, and career comparison analysis.</p>
    </div>

    @livewire('analytics.pattern-dashboard')
</div>
@endsection
