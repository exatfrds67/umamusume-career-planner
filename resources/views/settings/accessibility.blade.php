@extends('layouts.app')

@section('title', 'Accessibility Settings')

@section('content')
    <x-breadcrumb :items="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Accessibility']]" />

    <div class="page-stack animate-fade-in">
        <div class="page-hero">
            <div class="page-hero__content">
            <div class="min-w-0 flex-1">
                <div class="page-hero__eyebrow">
                    <span>Accessibility</span>
                </div>
                <h1 class="page-hero__title sm:truncate">
                    Accessibility Settings
                </h1>
                <p class="page-hero__body text-sm sm:text-base">
                    Configure accessibility preferences for a comfortable experience. WCAG 2.2 AA compliant.
                </p>
            </div>
            </div>
        </div>

        <div class="filter-surface">
            <div class="px-4 py-5 sm:p-6">
                @livewire('settings.accessibility-settings')
            </div>
        </div>
    </div>
@endsection
