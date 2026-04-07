<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Umamusume Career Planner'))</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" sizes="16x16"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_16.ico') }}">
    <link rel="icon" type="image/x-icon" sizes="32x32"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_32.ico') }}">
    <link rel="icon" type="image/png" sizes="128x128"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_128.png') }}">
    <link rel="icon" type="image/png" sizes="256x256"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_256.png') }}">
    <link rel="icon" type="image/png" sizes="512x512"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_512.png') }}">
    <link rel="apple-touch-icon" sizes="128x128"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_128.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet"
        media="print" onload="this.media='all'" />
    <noscript>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
    </noscript>

    <!-- Synchronous Theme Initialization (prevents flash/mismatch on page load) -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            let theme;

            if (stored) {
                theme = stored;
            } else {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                // Store the system preference so it's consistent across pages
                localStorage.setItem('theme', theme);
            }

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full font-sans antialiased text-neutral-900 dark:text-neutral-100 bg-neutral-50 dark:bg-neutral-900" x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false">

    <!-- Skip to Content (Accessibility) -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 z-50 px-4 py-2 bg-blue-600 text-white rounded-md shadow-lg">
        Skip to content
    </a>

<!-- Fixed Background with Theme-Aware Images (class-based dark mode) -->
<div class="fixed inset-0 z-0" aria-hidden="true">
    {{-- Light mode backgrounds --}}
    <picture class="block dark:hidden w-full h-full">
        <source media="(min-width: 1024px)" srcset="/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png">
        <img src="/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png" alt="" class="w-full h-full object-cover object-center" loading="lazy" decoding="async">
    </picture>
    {{-- Dark mode backgrounds --}}
    <picture class="hidden dark:block w-full h-full">
        <source media="(min-width: 1024px)" srcset="/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png">
        <img src="/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png" alt="" class="w-full h-full object-cover object-center" loading="lazy" decoding="async">
    </picture>
</div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-neutral-900/80 z-40 lg:hidden" aria-hidden="true"
        @click="sidebarOpen = false"></div>

    <!-- Mobile Sidebar (shown when sidebarOpen is true) -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        x-trap.noscroll="sidebarOpen"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-neutral-800 shadow-xl lg:hidden">
        <x-app.sidebar />
    </div>

    <!-- Desktop Sidebar (always visible on lg screens) -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'"
        class="hidden sm:hidden md:hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:flex-col lg:border-r lg:border-neutral-200 dark:lg:border-neutral-700 lg:bg-white dark:lg:bg-neutral-800 transition-all duration-300 ease-in-out">
        <x-app.sidebar />
    </div>

    <!-- Main Column of Content -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:pl-20' : 'lg:pl-72'"
        class="flex flex-col min-h-screen transition-all duration-300 ease-in-out relative z-10">
        @php
            $topStatus = $topStatus ?? [];
        @endphp

        <!-- Sticky Header -->
        <header
            class="sticky top-0 z-40 border-b border-neutral-200 dark:border-neutral-700 bg-white/80 dark:bg-neutral-800/80 backdrop-blur-md shadow-xs">
            <div class="flex flex-col">
                {{-- TODO: Provide storage mode and SP data from a shared context or controller-specific view data. --}}
                <x-top-status-bar :current-turn="$topStatus['currentTurn'] ?? null" :max-turns="$topStatus['maxTurns'] ?? null" :sp-available="$topStatus['spAvailable'] ?? null" :storage-mode="$topStatus['storageMode'] ?? null" />

                <div class="flex h-16 shrink-0 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
                    <button type="button" id="sidebar-toggle-btn" class="-m-2.5 p-2.5 text-neutral-700 dark:text-neutral-200 lg:hidden"
                        @click="sidebarOpen = true" :aria-expanded="sidebarOpen.toString()">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                        <x-app.header />
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Header (if provided via slot) -->
        @isset($header)
            <header class="bg-white dark:bg-neutral-800 shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Main Content -->
        <main class="flex flex-col flex-1 min-h-0 py-10 pb-24 lg:pb-10" id="main-content">
            <div class="px-4 sm:px-6 lg:px-8 flex-1 flex flex-col min-h-0">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>

    <x-bottom-nav-bar />

    <!-- Offline Indicator (Task 2.2.1) -->
    <x-offline-indicator />

    <!-- Accessibility Settings Panel (WCAG 2.2 AA) -->
    <x-accessibility-settings-panel />

    <!-- Screen Reader Announcer for Sidebar State -->
    <div id="sidebar-announcer" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>

    <!-- Global Flash Notifications (server-side) -->
    @if (session('success') || session('error') || session('warning') || session('info'))
        <div class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-sm w-full" aria-live="polite">
            @if (session('success'))
                <x-toast variant="success">{{ session('success') }}</x-toast>
            @endif
            @if (session('error'))
                <x-toast variant="error">{{ session('error') }}</x-toast>
            @endif
            @if (session('warning'))
                <x-toast variant="warning">{{ session('warning') }}</x-toast>
            @endif
            @if (session('info'))
                <x-toast variant="info">{{ session('info') }}</x-toast>
            @endif
        </div>
    @endif

    <!-- Dynamic Toast Container (client-side, for JS-dispatched toasts) -->
    <div id="dynamic-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"
         aria-live="polite"
         x-data="toastContainer"
         @toast.window="addFromEvent($event)">
        <template x-for="toast in items" :key="toast.id">
            <div class="pointer-events-auto alert shadow-lg"
                 :class="{
                     'alert-success': toast.type === 'success',
                     'alert-error': toast.type === 'error',
                     'alert-warning': toast.type === 'warning',
                     'alert-info': toast.type === 'info' || !['success','error','warning'].includes(toast.type)
                 }"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2">
                <div class="flex items-start gap-3">
                    <div class="flex-1" x-text="toast.message"></div>
                    <button type="button" @click="remove(toast.id)"
                            class="shrink-0 opacity-70 hover:opacity-100 transition-opacity"
                            aria-label="Dismiss notification">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    @stack('scripts')
    @livewireScriptConfig
</body>

</html>
