@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseClasses = 'badge';

    $variantClasses = match ($variant) {
        // CSS-class-based variants (defined in app.css)
        'primary'      => 'badge-primary',
        'secondary'    => 'badge-secondary',
        'success'      => 'badge-success',
        'warning'      => 'badge-warning',
        'error'        => 'badge-error',
        'grade-g1'     => 'badge-grade-g1',
        'grade-g2'     => 'badge-grade-g2',
        'grade-g3'     => 'badge-grade-g3',
        'grade-op'     => 'badge-grade-op',
        'grade-preop'  => 'badge-grade-preop',
        'grade-debut'  => 'badge-grade-debut',
        // Inline Tailwind variants for unified badge system
        'neutral'      => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300',
        'rarity-ssr'   => 'bg-linear-to-r from-yellow-100 to-amber-100 text-amber-800 dark:from-yellow-900 dark:to-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700',
        'rarity-sr'    => 'bg-linear-to-r from-purple-100 to-pink-100 text-purple-800 dark:from-purple-900 dark:to-pink-900 dark:text-purple-200 border border-purple-300 dark:border-purple-700',
        'rarity-r'     => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-600',
        'tier-s-plus'  => 'bg-linear-to-r from-red-100 to-pink-100 text-red-800 dark:from-red-900 dark:to-pink-900 dark:text-red-200 border border-red-300 dark:border-red-700',
        'tier-s'       => 'bg-linear-to-r from-orange-100 to-yellow-100 text-orange-800 dark:from-orange-900 dark:to-yellow-900 dark:text-orange-200 border border-orange-300 dark:border-orange-700',
        'tier-a'       => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border border-green-300 dark:border-green-700',
        'tier-b'       => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700',
        'tier-c'       => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-600',
        'type-speed'   => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'type-stamina' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'type-power'   => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'type-guts'    => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'type-wit'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'type-friend'  => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        default        => 'badge-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'text-[10px] px-1.5 py-0',
        'lg' => 'text-sm px-3 py-1',
        default => '',
    };

    $classes = trim("$baseClasses $variantClasses $sizeClasses");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
