{{--
Component: DashboardGrid
Purpose: Multi-column responsive grid layout for dashboard widgets
Props:
  - columns (int, optional): Base number of columns (1-4), default 3
  - gap (string, optional): Gap size (sm|md|lg), default md
Usage:
  <x-dashboard-grid columns="3" gap="md">
      <x-card>Widget 1</x-card>
      <x-card>Widget 2</x-card>
  </x-dashboard-grid>
Accessibility: WCAG 2.2 AA compliant, semantic HTML
--}}

@props([
    'columns' => 3,
    'gap' => 'md',
])

@php
    $gapClasses = [
        'sm' => 'gap-2 sm:gap-3',
        'md' => 'gap-4 sm:gap-6',
        'lg' => 'gap-6 sm:gap-8',
    ];
    
    $columnClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    ];
    
    $gapClass = $gapClasses[$gap] ?? $gapClasses['md'];
    $colClass = $columnClasses[$columns] ?? $columnClasses[3];
@endphp

<div 
    {{ $attributes->merge([
        'class' => "grid {$colClass} {$gapClass}",
        'role' => 'region',
    ]) }}
>
    {{ $slot }}
</div>
