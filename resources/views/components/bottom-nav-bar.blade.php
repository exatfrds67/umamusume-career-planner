@props(['items' => null])

@php
    $defaultItems = [
        [
            'route' => 'characters.index',
            'label' => 'Characters',
            'icon' => 'star',
            'active' => request()->routeIs('characters.*'),
        ],
        [
            'route' => 'training.predictions',
            'label' => 'Training',
            'icon' => 'trainer',
            'active' => request()->routeIs('training.*'),
        ],
        [
            'route' => 'skills.index',
            'label' => 'Skills',
            'icon' => 'skills',
            'active' => request()->routeIs('skills.*'),
        ],
        [
            'route' => 'races.index',
            'label' => 'Races',
            'icon' => 'races',
            'active' => request()->routeIs('races.*'),
        ],
        [
            'route' => 'support-cards.index',
            'label' => 'Support',
            'icon' => 'support',
            'active' => request()->routeIs('support-cards.*'),
        ],
    ];

    $navItems = is_array($items) ? $items : $defaultItems;
@endphp

<nav {{ $attributes->merge(['class' => 'lg:hidden fixed bottom-0 inset-x-0 z-40 border-t border-neutral-200 dark:border-neutral-700 bg-white/90 dark:bg-neutral-800/90 backdrop-blur-md']) }} aria-label="Mobile navigation">
    <ul class="grid grid-cols-5">
        @foreach ($navItems as $item)
            <li>
                <a href="{{ route($item['route']) }}"
                    class="flex flex-col items-center justify-center gap-1 px-2 py-2 text-[11px] font-medium transition-colors {{ $item['active'] ? 'text-primary-600 dark:text-primary-400' : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200' }}"
                    {!! $item['active'] ? 'aria-current="page"' : '' !!}>
                    @switch($item['icon'])
                        @case('trainer')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                            @break
                        @case('skills')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 13.5 10.5 6.75m0 0L7.5 3.75m3 3L13.5 3.75M3.75 19.5l6.75-6.75m0 0 3 3m-3-3 3-3m6.75 6.75-6.75-6.75m0 0 3-3m-3 3-3-3" />
                            </svg>
                            @break
                        @case('races')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            @break
                        @case('support')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                            @break
                        @case('star')
                        @default
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                    @endswitch
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
