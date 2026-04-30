@extends('layouts.app')

@section('title', 'Create Character')

@section('content')
    <x-breadcrumb :items="[['label' => 'Characters', 'url' => route('characters.index')], ['label' => 'Create Character']]" />

    <livewire:characters.character-wizard />
@endsection