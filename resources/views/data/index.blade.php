@extends('layouts.app')

@section('title', 'Data Management')

@section('content')
    <div style="max-width:900px; margin:0 auto; padding:24px 20px; font-family:'Nunito',sans-serif;">

        {{-- Page Header --}}
        <div style="margin-bottom:24px;">
            <h1 style="font-size:22px; font-weight:900; color:#1E1033; margin:0 0 4px;">🗄️ Data Management</h1>
            <p style="font-size:13px; color:#7C6FAB; margin:0;">Import, export, migrate, and backup your career data.</p>
        </div>

        @livewire('data-management.migration-wizard')

    </div>
@endsection
