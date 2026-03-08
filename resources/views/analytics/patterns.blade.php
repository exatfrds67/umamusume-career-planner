@extends('layouts.app')
@section('title', 'Pattern Analytics')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <x-breadcrumb :items="[['label' => 'Analytics & Reports', 'url' => route('reports.index')], ['label' => 'Pattern Analytics']]" />
    <div class="mb-6">
        <h1 class="mt-2 text-2xl font-bold text-neutral-900 dark:text-neutral-100">Pattern Analytics</h1>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">K-means clustering, association rule mining, and career comparison analysis.</p>
    </div>

    @livewire('analytics.pattern-dashboard')
</div>
@endsection
