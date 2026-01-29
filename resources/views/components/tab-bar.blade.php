@props([
    'tabs' => [],           // Array of tab objects: [['key' => 'overview', 'label' => 'Overview', 'count' => 5], ...]
    'active' => null,       // Currently active tab key
    'variant' => 'default', // 'default' | 'pills' | 'underline'
    'size' => 'md',         // 'sm' | 'md' | 'lg'
])

@php
    $isPills = $variant === 'pills';
    $isUnderline = $variant === 'underline';
    
    $sizeClasses = match($size) {
        'sm' => 'text-sm px-3 py-1.5',
        'lg' => 'text-lg px-6 py-3',
        default => 'text-base px-4 py-2',
    };
    
    $containerClasses = "flex border-b border-gray-200 dark:border-gray-700 ";
    if ($isPills) {
        $containerClasses = "flex gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg ";
    }
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <nav class="{{ $containerClasses }}" role="tablist" aria-label="Tabs">
        @foreach($tabs as $tab)
            @php
                $tabKey = is_array($tab) ? $tab['key'] : $tab;
                $tabLabel = is_array($tab) ? $tab['label'] : $tab;
                $tabCount = is_array($tab) && isset($tab['count']) ? $tab['count'] : null;
                $tabIcon = is_array($tab) && isset($tab['icon']) ? $tab['icon'] : null;
                $isActive = $tabKey === $active;
                
                $baseClasses = "inline-flex items-center gap-2 font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 ";
                $baseClasses .= $sizeClasses . ' ';
                
                if ($isPills) {
                    if ($isActive) {
                        $baseClasses .= 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm rounded-md';
                    } else {
                        $baseClasses .= 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 rounded-md';
                    }
                } elseif ($isUnderline) {
                    $baseClasses .= 'border-b-2 ';
                    if ($isActive) {
                        $baseClasses .= 'border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400';
                    } else {
                        $baseClasses .= 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600';
                    }
                } else {
                    // Default variant
                    $baseClasses .= 'border-b-2 ';
                    if ($isActive) {
                        $baseClasses .= 'border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400';
                    } else {
                        $baseClasses .= 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200';
                    }
                }
            @endphp
            
            <button 
                type="button"
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                aria-controls="tabpanel-{{ $tabKey }}"
                id="tab-{{ $tabKey }}"
                x-data
                @click="$dispatch('tab-changed', { tab: '{{ $tabKey }}' })"
                class="{{ $baseClasses }}"
            >
                {{-- Optional Icon --}}
                @if($tabIcon)
                    <span class="w-5 h-5" aria-hidden="true">
                        {!! $tabIcon !!}
                    </span>
                @endif
                
                {{-- Tab Label --}}
                <span>{{ $tabLabel }}</span>
                
                {{-- Optional Count Badge --}}
                @if($tabCount !== null)
                    <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-semibold rounded-full {{ $isActive ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                          aria-label="{{ $tabCount }} items">
                        {{ $tabCount }}
                    </span>
                @endif
            </button>
        @endforeach
    </nav>
    
    {{-- Tab Panels (Content) --}}
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
