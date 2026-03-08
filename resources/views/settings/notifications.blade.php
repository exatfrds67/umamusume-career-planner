@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
    <x-breadcrumb :items="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Notifications']]" />

    <div class="page-stack animate-fade-in">
        <div class="page-hero">
            <div class="page-hero__content">
            <div class="min-w-0 flex-1">
                <div class="page-hero__eyebrow">
                    <span>Notifications</span>
                </div>
                <h1 class="page-hero__title sm:truncate">
                    Notification Settings
                </h1>
                <p class="page-hero__body text-sm sm:text-base">
                    Configure push notification preferences and quiet hours.
                </p>
            </div>
            </div>
        </div>

        <div class="filter-surface">
            <div class="px-4 py-5 sm:p-6">
                @livewire('settings.notification-settings')
            </div>
        </div>
    </div>
@endsection
