<div {{ $attributes }}>
    @if($title)
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $title }}
        </h3>
    @endif

    <div class="grid {{ $gridClasses() }} gap-4">
        @forelse($items as $item)
            @php
                $locked = $isLocked($item);
                $new = $isNew($item);
                $url = $itemUrl($item);
                $hasUrl = !empty($url);
            @endphp

            @if($hasUrl)
                <a href="{{ $url }}" 
                   class="relative group block aspect-square rounded-lg overflow-hidden {{ $locked ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg transition-all duration-300' }}">
                    @else
                    <div class="relative group block aspect-square rounded-lg overflow-hidden {{ $locked ? 'opacity-50 bg-gray-200 dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @endif

                    <!-- Background -->
                    <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                        @if(!$locked && isset($item['icon']))
                            <span class="text-4xl">{{ $item['icon'] }}</span>
                        @elseif($locked && $showLockIcons)
                            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4.5-8 3 6 2.5-4 3.5 6z" />
                            </svg>
                        @endif
                    </div>

                    <!-- Title overlay -->
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-200 flex items-center justify-center p-2">
                        <p class="text-white text-center text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            {{ $item['title'] }}
                        </p>
                    </div>

                    <!-- New badge -->
                    @if($new)
                        <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            NEW
                        </div>
                    @endif

                    <!-- Lock badge -->
                    @if($locked && $showLockIcons)
                        <div class="absolute top-2 left-2 bg-gray-500 text-white p-1 rounded-full">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif

                    @if($hasUrl)
                </a>
            @else
                </div>
            @endif
        @empty
            <div class="col-span-full py-12 text-center text-gray-500 dark:text-gray-400">
                <p class="text-sm">No items to display</p>
            </div>
        @endforelse
    </div>
</div>