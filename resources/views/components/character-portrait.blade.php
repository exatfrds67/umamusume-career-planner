<div class="relative inline-block {{ $sizeClasses() }}" {{ $attributes }}>
    <div class="relative w-full h-full {{ $roundedClasses() }} {{ $borderClasses() }} bg-gray-100 dark:bg-gray-800">
        @if($image)
            <img 
                src="{{ $image }}" 
                alt="{{ $alt }}"
                class="w-full h-full object-cover"
                loading="lazy"
                decoding="async"
            />
        @else
            {{-- Default placeholder for missing images --}}
            <div class="w-full h-full flex items-center justify-center text-gray-500 dark:text-gray-400">
                <svg class="w-1/2 h-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        @endif
        
        @if($badge)
            <div class="absolute {{ $badgePositionClasses() }} m-1 z-10">
                {!! $badge !!}
            </div>
        @endif
        
        {{-- Optional slot for additional overlays --}}
        {{ $slot }}
    </div>
</div>