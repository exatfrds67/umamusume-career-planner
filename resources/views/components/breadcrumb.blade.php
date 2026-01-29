@php
    $allItems = $allItems();
    $itemCount = count($allItems);
@endphp

@if($itemCount > 0)
    <nav aria-label="Breadcrumb" class="flex items-center space-x-2 text-sm" {{ $attributes }}>
        <ol class="flex items-center space-x-2">
            @foreach($allItems as $index => $item)
                @php
                    $isLast = $index === $itemCount - 1;
                    $hasUrl = isset($item['url']);
                @endphp
                
                <li class="flex items-center">
                    @if($index > 0)
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 mx-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                    
                    @if($isLast)
                        <span class="font-medium text-gray-700 dark:text-gray-200" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @elseif($hasUrl)
                        <a href="{{ $item['url'] }}" 
                           class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                           @if($index === 0) aria-label="Home" @endif>
                            @if($index === 0 && $showHome)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                            @else
                                {{ $item['label'] }}
                            @endif
                        </a>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
    
    {{-- JSON-LD Structured Data for SEO --}}
    <script type="application/ld+json">
        {!! $jsonLd() !!}
    </script>
@endif