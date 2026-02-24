@extends('layouts.app')

@section('content')
    <x-breadcrumb :items="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Notifications']]" />

    <div class="space-y-6 animate-fade-in">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Notification Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure push notification preferences and quiet hours.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                @livewire('settings.notification-settings')
            </div>
        </div>
    </div>
@endsection
