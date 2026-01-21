{{--
    Offline Indicator Component

    Displays a banner when the application is in offline mode.
    Shows connectivity status, offline duration, and cache information.

    Requirements: 14.2 (Intelligent Caching and Offline Functionality)
    Task: 2.2.1
--}}

<div x-data="connectivityMonitor()" x-init="init()" class="connectivity-monitor">
    {{-- Offline Banner --}}
    <div x-show="showBanner && !isOnline" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-full"
        class="fixed top-0 left-0 right-0 z-50 bg-yellow-500 dark:bg-yellow-600 text-white shadow-lg" role="alert"
        aria-live="assertive">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                {{-- Icon and Message --}}
                <div class="flex items-center gap-3 flex-1">
                    {{-- Offline Icon --}}
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
                    </svg>

                    {{-- Message --}}
                    <div class="flex-1">
                        <p class="font-semibold text-sm md:text-base">
                            You are currently offline
                        </p>
                        <p class="text-xs md:text-sm opacity-90 mt-0.5">
                            <span x-show="offlineSince">
                                Offline since <span x-text="getOfflineDuration()"></span>
                            </span>
                            <span class="mx-2">•</span>
                            Using cached data
                            <span x-show="cacheStats" class="mx-2">•</span>
                            <span x-show="cacheStats">
                                Cache hit rate: <span x-text="getCacheHitRate().toFixed(1)"></span>%
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Retry Button --}}
                    <button @click="forceCheck()" :disabled="isChecking"
                        class="px-3 py-1.5 text-sm font-medium bg-white/20 hover:bg-white/30 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Check connectivity">
                        <span x-show="!isChecking">Retry</span>
                        <span x-show="isChecking" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Checking...
                        </span>
                    </button>

                    {{-- Close Button --}}
                    <button @click="closeBanner()" class="p-1.5 hover:bg-white/20 rounded-md transition-colors"
                        title="Close banner" aria-label="Close offline banner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Warning for low cache hit rate --}}
            <div x-show="isLowCacheHitRate()" class="mt-2 pt-2 border-t border-white/20">
                <p class="text-xs opacity-90">
                    ⚠️ Limited cached data available. Some features may not work properly while offline.
                </p>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-4 right-4 z-50 max-w-sm"
        role="alert" aria-live="polite">
        <div class="rounded-lg shadow-lg p-4 flex items-start gap-3"
            :class="{
                'bg-blue-500 text-white': toastType === 'info',
                'bg-yellow-500 text-white': toastType === 'warning',
                'bg-red-500 text-white': toastType === 'error',
                'bg-green-500 text-white': toastType === 'success'
            }">
            {{-- Icon --}}
            <div class="flex-shrink-0">
                {{-- Info Icon --}}
                <svg x-show="toastType === 'info'" class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Warning Icon --}}
                <svg x-show="toastType === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                {{-- Error Icon --}}
                <svg x-show="toastType === 'error'" class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Success Icon --}}
                <svg x-show="toastType === 'success'" class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            {{-- Message --}}
            <div class="flex-1">
                <p class="text-sm font-medium" x-text="toastMessage"></p>
            </div>

            {{-- Close Button --}}
            <button @click="closeToast()" class="flex-shrink-0 p-1 hover:bg-white/20 rounded transition-colors"
                aria-label="Close notification">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Online Status Indicator (small badge in corner) --}}
    <div class="fixed bottom-4 left-4 z-40">
        <div class="flex items-center gap-2 px-3 py-2 rounded-full shadow-lg text-xs font-medium transition-colors"
            :class="{
                'bg-green-500 text-white': isOnline,
                'bg-red-500 text-white': !isOnline
            }"
            :title="isOnline ? 'Online' : 'Offline'">
            {{-- Status Dot --}}
            <span class="w-2 h-2 rounded-full"
                :class="{
                    'bg-white animate-pulse': isOnline,
                    'bg-white': !isOnline
                }"></span>

            {{-- Status Text --}}
            <span x-text="isOnline ? 'Online' : 'Offline'"></span>
        </div>
    </div>
</div>

@push('scripts')
    <script type="module">
        import connectivityMonitor from '/resources/js/core/connectivity-monitor.js';

        // Register Alpine component
        if (window.Alpine) {
            window.Alpine.data('connectivityMonitor', connectivityMonitor);
        }
    </script>
@endpush
