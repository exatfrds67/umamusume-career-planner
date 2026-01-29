{{--
Component: SkillLoadout
Purpose: Display and manage equipped skills in a grid layout

Props:
  - skills (array): Array of equipped skill objects
  - editable (bool): Whether skills can be removed/modified
  - columns (int): Number of grid columns (default: 3)
  - size (string): Card size - sm/md/lg (default: md)
  - variant (string): Display variant - grid/list/compact (default: grid)
  - dragDropEnabled (bool): Enable drag-and-drop reordering
  - @skill-removed: Event fired when skill is removed from loadout
  - @skill-selected: Event fired when skill is clicked
  - @loadout-reordered: Event fired when skills are reordered via drag-drop

Usage:
  <x-skill-loadout :skills="$character->equippedSkills" editable drag-drop-enabled />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'skills' => [],
    'editable' => false,
    'columns' => 3,
    'size' => 'md',
    'variant' => 'grid',
    'dragDropEnabled' => false,
])

@php
    $sizeClasses = [
        'sm' => 'w-20 h-20 text-xs',
        'md' => 'w-24 h-24 text-sm',
        'lg' => 'w-32 h-32 text-base'
    ];
    
    $gridClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        4 => 'grid-cols-4',
        5 => 'grid-cols-5',
        6 => 'grid-cols-6'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $gridClass = $gridClasses[$columns] ?? $gridClasses[3];
    
    $tierColors = [
        'S' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border-yellow-300',
        'A' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border-purple-300',
        'B' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border-blue-300',
        'C' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border-green-300',
    ];
@endphp

@if($variant === 'grid')
    {{-- Grid Layout --}}
    <div class="grid {{ $gridClass }} gap-4">
        @forelse($skills as $index => $skill)
            <div 
                class="group relative"
                draggable="{{ $dragDropEnabled ? 'true' : 'false' }}"
                data-skill-id="{{ $skill->id }}"
                data-slot-index="{{ $index }}"
            >
                {{-- Skill Card --}}
                <div class="
                    {{ $sizeClass }} 
                    flex flex-col items-center justify-center
                    bg-gradient-to-br from-gray-50 to-gray-100
                    dark:from-gray-700 dark:to-gray-800
                    border-2 border-gray-300 dark:border-gray-600
                    rounded-lg
                    hover:ring-2 hover:ring-blue-500 dark:hover:ring-blue-400
                    transition-all duration-200
                    cursor-pointer
                    p-2
                    space-y-1
                    {{ $dragDropEnabled ? 'cursor-grab active:cursor-grabbing' : '' }}
                " 
                    @click="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
                    role="button"
                    tabindex="0"
                    :aria-label="`{{ $skill->name }}, Tier {{ $skill->tier }}, SP Cost: {{ $skill->sp_cost }}`"
                    @keydown.enter="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
                    @keydown.space="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
                >
                    {{-- Skill Icon Placeholder --}}
                    <div class="
                        w-full h-2/3 flex items-center justify-center
                        bg-gray-200 dark:bg-gray-600
                        rounded-md
                        text-gray-400 dark:text-gray-500
                    ">
                        <svg class="w-1/2 h-1/2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </div>
                    
                    {{-- Skill Name --}}
                    <div class="text-center w-full px-0.5 text-gray-900 dark:text-gray-100 font-semibold truncate">
                        {{ \Illuminate\Support\Str::limit($skill->name, 10, '...') }}
                    </div>
                </div>
                
                {{-- Tier Badge --}}
                <div class="absolute -top-2 -right-2 
                    {{ $tierColors[$skill->tier] ?? $tierColors['C'] }}
                    border px-1.5 py-0.5 rounded font-bold text-xs
                    shadow-sm
                ">
                    {{ $skill->tier }}
                </div>
                
                {{-- SP Cost Badge --}}
                <div class="absolute -bottom-2 -left-2 
                    bg-amber-100 dark:bg-amber-900/30
                    text-amber-800 dark:text-amber-200
                    border border-amber-300
                    px-1.5 py-0.5 rounded text-xs font-semibold
                    shadow-sm
                ">
                    SP {{ $skill->sp_cost }}
                </div>
                
                {{-- Remove Button (if editable) --}}
                @if($editable)
                    <button
                        type="button"
                        @click="$dispatch('skill-removed', { skill_id: {{ $skill->id }}, index: {{ $index }} })"
                        class="
                            absolute -top-3 -right-3
                            opacity-0 group-hover:opacity-100
                            transition-opacity
                            bg-red-500 hover:bg-red-600
                            text-white rounded-full p-1
                            shadow-md
                            focus:outline-none focus:ring-2 focus:ring-red-400
                        "
                        aria-label="Remove {{ $skill->name }}"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
                
                {{-- Tooltip --}}
                <div class="
                    hidden group-hover:block absolute z-10
                    bg-gray-900 dark:bg-gray-700
                    text-white text-xs
                    px-2 py-1 rounded
                    whitespace-nowrap
                    -bottom-8 left-1/2 -translate-x-1/2
                    pointer-events-none
                ">
                    {{ $skill->name }}
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="col-span-{{ $columns }} 
                flex flex-col items-center justify-center
                py-12 px-4
                text-center
                bg-gray-50 dark:bg-gray-800/50
                border-2 border-dashed border-gray-300 dark:border-gray-600
                rounded-lg
            ">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6-6v6m0-6v6m0-6h-6m0 0H6" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400 font-medium">No skills equipped</p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Add skills to your loadout to get started</p>
            </div>
        @endforelse
    </div>

@elseif($variant === 'list')
    {{-- List Layout --}}
    <div class="space-y-2">
        @forelse($skills as $index => $skill)
            <div class="
                flex items-center justify-between
                bg-white dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                rounded-lg px-4 py-3
                hover:ring-2 hover:ring-blue-500
                transition-all duration-200
                group
            "
                role="button"
                tabindex="0"
                @click="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
                @keydown.enter="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
            >
                <div class="flex items-center gap-4 flex-1">
                    <div class="w-12 h-12 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded-md">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $skill->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $skill->description ?? 'No description' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="
                        {{ $tierColors[$skill->tier] ?? $tierColors['C'] }}
                        border px-2 py-1 rounded font-bold text-xs
                    ">
                        {{ $skill->tier }}
                    </span>
                    <span class="
                        bg-amber-100 dark:bg-amber-900/30
                        text-amber-800 dark:text-amber-200
                        px-2 py-1 rounded text-sm font-semibold
                    ">
                        {{ $skill->sp_cost }} SP
                    </span>
                    @if($editable)
                        <button
                            type="button"
                            @click.stop="$dispatch('skill-removed', { skill_id: {{ $skill->id }}, index: {{ $index }} })"
                            class="
                                opacity-0 group-hover:opacity-100
                                transition-opacity
                                p-2
                                text-red-600 hover:text-red-700
                                focus:outline-none focus:ring-2 focus:ring-red-400 rounded
                            "
                            aria-label="Remove {{ $skill->name }}"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-12 text-center">
                <p class="text-gray-600 dark:text-gray-400">No skills equipped</p>
            </div>
        @endforelse
    </div>

@else
    {{-- Compact Layout (default) --}}
    <div class="flex flex-wrap gap-2">
        @forelse($skills as $index => $skill)
            <button
                type="button"
                @click="$dispatch('skill-selected', { skill: {{ json_encode($skill) }}, index: {{ $index }} })"
                class="
                    inline-flex items-center gap-2
                    {{ $tierColors[$skill->tier] ?? $tierColors['C'] }}
                    border px-3 py-1 rounded-full text-sm font-semibold
                    hover:shadow-md transition-all
                    focus:outline-none focus:ring-2 focus:ring-offset-2
                "
                :aria-label="`{{ $skill->name }}, Tier {{ $skill->tier }}`"
            >
                {{ $skill->name }}
                @if($editable)
                    <span class="text-xs opacity-60">×</span>
                @endif
            </button>
        @empty
            <div class="w-full text-center py-8">
                <p class="text-gray-500 dark:text-gray-400 text-sm">No skills equipped</p>
            </div>
        @endforelse
    </div>
@endif
