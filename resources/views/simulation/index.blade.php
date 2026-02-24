@extends('layouts.app')

@section('content')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Simulation']]" />

    <div class="space-y-6 animate-fade-in">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Batch Simulation
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Compare training strategies by simulating multiple scenarios side-by-side.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                @livewire('simulation.simulation-wizard')
            </div>
        </div>
    </div>
@endsection
