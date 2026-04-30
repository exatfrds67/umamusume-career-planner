@extends('layouts.app')

@section('title', 'Race Strategy')

@section('content')
    @livewire('races.race-index')
    @livewire('races.race-entry-modal')
@endsection
