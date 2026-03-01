@props(['items' => []])

@php
    // Ensure items is an array
    $items = is_array($items) ? $items : [];

    // Add home as first item if not present
    if (empty($items) || !isset($items[0]['label']) || $items[0]['label'] !== 'Home') {
        array_unshift($items, [
            'label' => 'Home',
            'url' => route('dashboard'),
            'icon' => 'home',
        ]);
    }
@endphp

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex items-center space-x-2 text-sm">
        @foreach ($items as $index => $item)
            @php
                $isLast = $index === count($items) - 1;
                $label = $item['label'] ?? '';
                $url = $item['url'] ?? null;
                $icon = $item['icon'] ?? null;
            @endphp

            <li class="flex items-center">
                @if (!$isLast)
                    {{-- Clickable breadcrumb item --}}
                    <a href="{{ $url }}"
                        class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded px-1"
                        @if ($index === 0) aria-label="Home" @endif>
                        @if ($icon === 'home')
                            {{-- Home icon --}}
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                    </a>

                    {{-- Separator --}}
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-600 mx-1 shrink-0" fill="currentColor"
                        viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                @else
                    {{-- Current page (not clickable) --}}
                    <span class="font-semibold text-gray-900 dark:text-gray-100 px-1" aria-current="page">
                        {{ $label }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
        {!! $jsonLd() !!}
    </script>
</nav>
