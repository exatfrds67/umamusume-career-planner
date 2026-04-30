@props(['character', 'size' => 'md', 'lazy' => true])

@php
$sizes = [
    'sm' => 'w-8 h-10',
    'md' => 'w-24 h-32',
    'lg' => 'w-48 h-64',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="{{ $sizeClass }} relative overflow-hidden rounded-lg bg-gradient-to-br from-pink-400 to-purple-600">
  @if($character->avatar_url)
    <img 
      src="{{ $character->avatar_url }}" 
      alt="{{ $character->name }}"
      class="w-full h-full object-cover transition-opacity duration-200"
      {{ $lazy ? 'loading="lazy"' : '' }}
      onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden');"
    />
    <!-- Icon fallback (hidden until image error) -->
    <div class="hidden absolute inset-0 flex items-center justify-center bg-gradient-to-br from-pink-400 to-purple-600">
      <x-icon name="user-character" class="w-12 h-12 text-white/50" />
    </div>
  @else
    <!-- No image URL: show icon immediately -->
    <div class="w-full h-full flex items-center justify-center">
      <x-icon name="user-character" class="w-12 h-12 text-white/50" />
    </div>
  @endif
</div>
