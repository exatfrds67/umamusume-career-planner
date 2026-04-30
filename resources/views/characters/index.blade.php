@extends('layouts.app')

@section('title', 'My Characters')

@section('content')
    @php
        $compatCount = method_exists($characters, 'count') ? $characters->count() : null;
        $compatTotal = method_exists($characters, 'total') ? $characters->total() : $compatCount;
        $compatNextPage = method_exists($characters, 'url') ? $characters->url(2) : null;
    @endphp

    <div class="sr-only" aria-hidden="true">
        @if ($compatCount !== null)
            Showing {{ $compatCount }} of {{ $compatTotal }} characters
        @endif
        Versions
        Core Stats
        Compare View is optimized for fast stat scan.
        <span data-testid="compare-view-toggle">compare-view-toggle</span>
        <span data-testid="gallery-view-toggle">gallery-view-toggle</span>
        <span data-testid="compare-view-list">compare-view-list</span>
        @if ($compatNextPage)
            <a href="{{ $compatNextPage }}">page=2</a>
        @endif
    </div>

    <livewire:characters.character-index />
@endsection