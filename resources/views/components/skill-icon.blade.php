{{--
Component: SkillIcon
Purpose: Skill type indicator with emoji/icon display

Features:
  - Type-based icon/emoji display
  - Game-aligned colors per skill type
  - Optional label with name
  - Size variants (xs, sm, md, lg)
  - Tooltip on hover
  - Accessibility labels

Props:
  - type (string): Skill type (speed/stamina/power/guts/wit/unique)
  - name (string, optional): Skill name for label
  - size (string): 'xs' (16px), 'sm' (20px), 'md' (24px), 'lg' (32px) - default: md
  - showLabel (bool): Display skill name (default: false)
  - interactive (bool): Add hover effects (default: false)

Skill Types & Colors:
  - speed: Red (#EF4444) - 🔴
  - stamina: Green (#10B981) - 💚
  - power: Yellow (#FBBF24) - 💛
  - guts: Purple (#A855F7) - 💜
  - wit: Blue (#3B82F6) - 💙
  - unique: Pink (#EC4899) - 💗

Usage:
  <x-skill-icon type="speed" name="Sprint" size="md" :showLabel="true" />
  <x-skill-icon type="stamina" size="lg" interactive />

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'type' => 'unique',
    'name' => null,
    'size' => 'md',
    'showLabel' => false,
    'interactive' => false,
])

@php
    $sizeMap = [
        'xs' => 'w-4 h-4 text-xs',
        'sm' => 'w-5 h-5 text-sm',
        'md' => 'w-6 h-6 text-base',
        'lg' => 'w-8 h-8 text-lg',
    ];

    $typeConfig = [
        'speed' => [
            'icon' => '🔴',
            'label' => 'Speed',
            'color' => 'text-red-500 dark:text-red-400',
            'bgColor' => 'bg-red-100 dark:bg-red-900/30',
        ],
        'stamina' => [
            'icon' => '💚',
            'label' => 'Stamina',
            'color' => 'text-green-500 dark:text-green-400',
            'bgColor' => 'bg-green-100 dark:bg-green-900/30',
        ],
        'power' => [
            'icon' => '💛',
            'label' => 'Power',
            'color' => 'text-yellow-500 dark:text-yellow-400',
            'bgColor' => 'bg-yellow-100 dark:bg-yellow-900/30',
        ],
        'guts' => [
            'icon' => '💜',
            'label' => 'Guts',
            'color' => 'text-purple-500 dark:text-purple-400',
            'bgColor' => 'bg-purple-100 dark:bg-purple-900/30',
        ],
        'wit' => [
            'icon' => '💙',
            'label' => 'Wit',
            'color' => 'text-blue-500 dark:text-blue-400',
            'bgColor' => 'bg-blue-100 dark:bg-blue-900/30',
        ],
        'unique' => [
            'icon' => '💗',
            'label' => 'Unique',
            'color' => 'text-pink-500 dark:text-pink-400',
            'bgColor' => 'bg-pink-100 dark:bg-pink-900/30',
        ],
    ];

    $config = $typeConfig[$type] ?? $typeConfig['unique'];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['md'];
    $interactiveClass = $interactive ? 'cursor-pointer hover:scale-110 transition-transform duration-200' : '';
@endphp

<div class="inline-flex items-center gap-1 group" :title="'{{ $name ?? ($config['label'] ?? 'Skill') }}'">

    {{-- Icon Container --}}
    <div class="flex items-center justify-center {{ $sizeClass }} {{ $config['bgColor'] }} rounded-full {{ $interactiveClass }}"
        :aria-label="'{{ $name ?? ($config['label'] ?? 'Skill') }} skill icon'">
        <span class="{{ $config['color'] }} font-semibold">
            {{ $config['icon'] }}
        </span>
    </div>

    {{-- Label (optional) --}}
    @if ($showLabel && $name)
        <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100 ml-1">
            {{ $name }}
        </span>
    @elseif ($showLabel)
        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400 ml-1">
            {{ $config['label'] }}
        </span>
    @endif

    {{-- Tooltip on Interactive --}}
    @if ($interactive && $name)
        <div
            class="absolute hidden group-hover:block bg-neutral-900 dark:bg-neutral-700 text-white text-xs rounded px-2 py-1 whitespace-nowrap pointer-events-none z-10 bottom-full mb-2 left-1/2 transform -translate-x-1/2">
            {{ $name }}
            <div
                class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-neutral-900 dark:border-t-neutral-700">
            </div>
        </div>
    @endif
</div>

@once
    @push('styles')
        @vite(['resources/css/components/animations.css'])
    @endpush
@endonce
