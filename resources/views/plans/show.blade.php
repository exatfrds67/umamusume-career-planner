@extends('layouts.app')

@section('title', 'Plan: ' . $plan->name ?? 'Untitled')

@section('content')
<div class="relative z-10 container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header with Title and Actions --}}
        <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $plan->name ?? 'Untitled Plan' }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Created {{ $plan->created_at->diffForHumans() }} • 
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-xs font-medium">
                        {{ $plan->status ?? 'Draft' }}
                    </span>
                </p>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 flex-wrap">
                <button 
                    type="button"
                    x-data
                    @click="$dispatch('menu-toggle')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-colors duration-200"
                >
                    <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                    More
                </button>
                
                <a 
                    href="/plans/{{ $plan->id }}/edit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-md focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-colors duration-200"
                >
                    <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>
        
        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            {{-- Left Sidebar: Character Info --}}
            <div class="lg:col-span-1">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xs rounded-lg shadow-lg p-6">
                    {{-- Character Portrait Section --}}
                    <div class="mb-6">
                        <div class="aspect-square bg-linear-to-br from-blue-400 to-purple-600 rounded-lg mb-4 flex items-center justify-center text-white text-center">
                            <div>
                                <p class="text-4xl font-bold">{{ substr($plan->character->name ?? 'N/A', 0, 1) }}</p>
                                <p class="text-sm mt-1">{{ $plan->character->name ?? 'Character' }}</p>
                            </div>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 dark:text-white text-center mb-1">
                            {{ $plan->character->name ?? 'Untitled Character' }}
                        </h3>
                        
                        @if($plan->character && $plan->character->title)
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                {{ $plan->character->title }}
                            </p>
                        @endif

                        {{-- Star Level Display --}}
                        <div class="mt-3 text-center">
                            <span class="text-xl tracking-wider text-yellow-400 drop-shadow-sm" aria-label="{{ $plan->star_level ?? 3 }} stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= ($plan->star_level ?? 3) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                                @endfor
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ ($plan->star_level ?? 3) }}★ — {{ ($plan->star_level ?? 3) >= 3 ? 'Unique skill upgraded' : 'Base unique skill' }}
                            </p>
                        </div>
                    </div>
                    
                    <hr class="my-4 border-gray-200 dark:border-gray-700">
                    
                    {{-- Character Stats Summary --}}
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Base Stats</h4>
                        
                        {{-- Speed --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Speed</span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $plan->character->base_speed ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500" style="width: {{ min(($plan->character->base_speed ?? 0) / 12 * 100, 100) }}%"></div>
                            </div>
                        </div>
                        
                        {{-- Stamina --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Stamina</span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $plan->character->base_stamina ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" style="width: {{ min(($plan->character->base_stamina ?? 0) / 12 * 100, 100) }}%"></div>
                            </div>
                        </div>
                        
                        {{-- Power --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Power</span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $plan->character->base_power ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500" style="width: {{ min(($plan->character->base_power ?? 0) / 12 * 100, 100) }}%"></div>
                            </div>
                        </div>
                        
                        {{-- Guts --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Guts</span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $plan->character->base_guts ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500" style="width: {{ min(($plan->character->base_guts ?? 0) / 12 * 100, 100) }}%"></div>
                            </div>
                        </div>
                        
                        {{-- Wit --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Wit</span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $plan->character->base_wit ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500" style="width: {{ min(($plan->character->base_wit ?? 0) / 12 * 100, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Right Content: Tabbed View --}}
            <div class="lg:col-span-3">
                <div x-data="{ activeTab: 'overview' }" class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xs rounded-lg shadow-lg overflow-hidden">
                    {{-- Tab Navigation --}}
                    <x-tab-bar 
                        :tabs="[
                            ['key' => 'overview', 'label' => 'Overview'],
                            ['key' => 'stats', 'label' => 'Stats', 'count' => 5],
                            ['key' => 'skills', 'label' => 'Skills', 'count' => count($plan->skills ?? [])],
                            ['key' => 'races', 'label' => 'Races', 'count' => count($plan->races ?? [])],
                        ]"
                        x-bind:active="activeTab"
                        variant="default"
                        class="border-b border-gray-200 dark:border-gray-700"
                    />
                    
                    {{-- Tab Content --}}
                    <div class="p-6">
                        
                        {{-- Overview Tab --}}
                        <div x-show="activeTab === 'overview'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                    Plan Details
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                            {{ $plan->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Updated</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                            {{ $plan->updated_at->format('M d, Y') }}
                                        </p>
                                    </div>

                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Star Level</p>
                                        <p class="text-sm font-semibold text-yellow-500 dark:text-yellow-400 mt-1">
                                            @for($i = 1; $i <= ($plan->star_level ?? 3); $i++)★@endfor
                                            <span class="text-gray-400 dark:text-gray-500">
                                                @for($i = ($plan->star_level ?? 3) + 1; $i <= 5; $i++)☆@endfor
                                            </span>
                                        </p>
                                    </div>

                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unique Skill</p>
                                        <p class="text-sm font-semibold mt-1 {{ ($plan->star_level ?? 3) >= 3 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ ($plan->star_level ?? 3) >= 3 ? 'Upgraded' : 'Base version' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($plan->notes)
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notes</h4>
                                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">
                                        {{ $plan->notes }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Stats Tab --}}
                        <div x-show="activeTab === 'stats'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Target Stats
                                </h3>
                                
                                <div class="space-y-4">
                                    {{-- Speed Goal --}}
                                    @if($plan->goals->target_speed)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Speed</span>
                                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                                    {{ $plan->goals->target_speed }}
                                                </span>
                                            </div>
                                            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-red-500" style="width: {{ min(($plan->goals->target_speed ?? 0) / 12 * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Stamina Goal --}}
                                    @if($plan->goals->target_stamina)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Stamina</span>
                                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                    {{ $plan->goals->target_stamina }}
                                                </span>
                                            </div>
                                            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-green-500" style="width: {{ min(($plan->goals->target_stamina ?? 0) / 12 * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Power Goal --}}
                                    @if($plan->goals->target_power)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Power</span>
                                                <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">
                                                    {{ $plan->goals->target_power }}
                                                </span>
                                            </div>
                                            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-yellow-500" style="width: {{ min(($plan->goals->target_power ?? 0) / 12 * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Guts Goal --}}
                                    @if($plan->goals->target_guts)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Guts</span>
                                                <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                                                    {{ $plan->goals->target_guts }}
                                                </span>
                                            </div>
                                            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-purple-500" style="width: {{ min(($plan->goals->target_guts ?? 0) / 12 * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Wit Goal --}}
                                    @if($plan->goals->target_wit)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Wit</span>
                                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                    {{ $plan->goals->target_wit }}
                                                </span>
                                            </div>
                                            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-500" style="width: {{ min(($plan->goals->target_wit ?? 0) / 12 * 100, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Total SP Goal --}}
                                    @if($plan->goals->target_total_sp)
                                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Total SP Budget</p>
                                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                                                {{ $plan->goals->target_total_sp }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- Skills Tab --}}
                        <div x-show="activeTab === 'skills'" x-transition class="space-y-6">
                            @if($plan->skills && count($plan->skills) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($plan->skills as $skill)
                                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                            <div class="flex items-start justify-between mb-2">
                                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $skill->name ?? 'Skill' }}
                                                </h4>
                                                @if($skill->tier)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded {{ match($skill->tier) {
                                                        'S' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                                                        'A' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                                        'B' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                                        'C' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
                                                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
                                                    } }}">
                                                        {{ $skill->tier }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($skill->type)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 capitalize">
                                                    {{ $skill->type }}
                                                </p>
                                            @endif
                                            
                                            @if($skill->sp_cost)
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-2">
                                                    SP Cost: <span class="text-blue-600 dark:text-blue-400">{{ $skill->sp_cost }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p>No skills selected yet</p>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Races Tab --}}
                        <div x-show="activeTab === 'races'" x-transition class="space-y-6">
                            @if($plan->races && count($plan->races) > 0)
                                <div class="space-y-3">
                                    @foreach($plan->races as $race)
                                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-between">
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $race->name ?? 'Race' }}
                                                </h4>
                                                @if($race->grade)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Grade: <span class="font-semibold">{{ $race->grade }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            @if($race->distance)
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $race->distance }}m
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p>No races planned yet</p>
                                </div>
                            @endif
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
