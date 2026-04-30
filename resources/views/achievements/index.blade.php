@extends('layouts.app')

@section('title', 'Achievements')

@section('content')
    <x-breadcrumb :items="[['label' => 'Achievements']]" />

    <div class="container mx-auto px-4 py-6 max-w-6xl">
        @livewire('achievements.achievement-browser')
    </div>
@endsection
