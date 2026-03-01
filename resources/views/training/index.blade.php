@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Training</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select a training facility to improve {{ $character->name }}'s stats</p>
            </div>
            <div class="flex items-center gap-4">
                 <!-- Vital Stats -->
                <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-lg px-4 py-2 shadow-xs">
                    <div class="flex items-center gap-2">
                         <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Energy:</span>
                         <span class="font-bold {{ $character->energy_level < 30 ? 'text-red-600' : 'text-green-600' }}">{{ $character->energy_level }}/100</span>
                    </div>
                    <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                     <div class="flex items-center gap-2">
                         <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Mood:</span>
                         <span class="font-bold text-primary-600">{{ ucfirst($character->mood_status) }}</span>
                    </div>
                </div>

                <a href="{{ route('characters.show', $character) }}" class="btn btn-secondary">
                    Back to Character
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
         @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <!-- Training Options Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach ($trainingData as $type => $data)
                <div class="card bg-white dark:bg-gray-800 hover:ring-2 hover:ring-primary-500 transition-all cursor-pointer relative overflow-hidden group">
                    <form action="{{ route('training.store', $character) }}" method="POST" class="h-full">
                        @csrf
                        <input type="hidden" name="training_type" value="{{ $type }}">
                        
                        <button type="submit" class="w-full h-full text-left p-0">
                            <!-- Header / Failure Rate -->
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100 capitalize">{{ $type }}</h3>
                                <span class="text-xs font-bold px-2 py-0.5 rounded {{ $data['failure_rate'] > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    Risk: {{ $data['failure_rate'] }}%
                                </span>
                            </div>

                            <!-- Body / Gains -->
                            <div class="p-4 space-y-3">
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Gains</p>
                                    @foreach ($data['gains'] as $stat => $gain)
                                        @if($gain > 0)
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="capitalize text-gray-700 dark:text-gray-300">{{ $stat }}</span>
                                                <span class="font-bold text-green-600">+{{ $gain }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Energy</span>
                                        <span class="font-bold {{ $data['energy_cost'] < 0 ? 'text-red-500' : 'text-green-500' }}">
                                            {{ $data['energy_cost'] > 0 ? '+' : '' }}{{ $data['energy_cost'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hover Overlay -->
                             <div class="absolute inset-0 bg-primary-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
        
        <!-- Current Stats Snapshot -->
        <div class="card bg-white dark:bg-gray-800 mt-6">
            <div class="card-header">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Current Status</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-5 gap-4">
                    @php 
                        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
                        $currentStats = $character->current_stats;
                    @endphp
                    @foreach($stats as $stat)
                        <div class="text-center">
                            <span class="block text-xs uppercase text-gray-500 mb-1">{{ $stat }}</span>
                            <span class="block text-xl font-bold">{{ $currentStats[$stat] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
