@props(['mood' => 'normal'])

@php
    $mood = strtolower((string) $mood);

    $config = match ($mood) {
        'great' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'icon' => '✨', 'label' => 'Great'],
        'good' => ['bg' => '#EDE9FE', 'color' => '#5B21B6', 'icon' => '😊', 'label' => 'Good'],
        'bad' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'icon' => '😟', 'label' => 'Bad'],
        'awful' => ['bg' => '#FEE2E2', 'color' => '#7F1D1D', 'icon' => '😢', 'label' => 'Awful'],
        default => ['bg' => '#F3F4F6', 'color' => '#374151', 'icon' => '😐', 'label' => 'Normal'],
    };
@endphp

<span {{ $attributes }}
    style="display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 700; font-family: 'Nunito', sans-serif; background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
    <span aria-hidden="true">{{ $config['icon'] }}</span>
    <span>{{ $config['label'] }}</span>
</span>
