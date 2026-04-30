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

    <!-- Fonts: Nunito (all weights 400-900) + JetBrains Mono for code -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900|jetbrains-mono:400,500"
        rel="stylesheet" media="print" onload="this.media='all'" />
    <noscript>
        <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900|jetbrains-mono:400,500"
            rel="stylesheet" />
    </noscript>

    <!-- Synchronous Theme Initialization (prevents flash/mismatch on page load) -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            // Default to light theme per design system spec; only use stored preference if explicitly set
            const theme = stored || 'light';
            if (!stored) {
                localStorage.setItem('theme', 'light');
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

<body class="h-full font-sans antialiased text-neutral-900 dark:text-neutral-100"
    style="font-family: 'Nunito', sans-serif; background: #F9F5FF;" x-data="{ sidebarOpen: false }"
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
            <img src="/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png" alt=""
                class="w-full h-full object-cover object-center" loading="lazy" decoding="async">
        </picture>
        {{-- Dark mode backgrounds --}}
        <picture class="hidden dark:block w-full h-full">
            <source media="(min-width: 1024px)" srcset="/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png">
            <img src="/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png" alt=""
                class="w-full h-full object-cover object-center" loading="lazy" decoding="async">
        </picture>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-neutral-900/80 z-40 lg:hidden" aria-hidden="true"
        @click="sidebarOpen = false" data-testid="sidebar-backdrop"></div>

    <!-- Mobile Sidebar (shown when sidebarOpen is true) -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" x-trap.noscroll="sidebarOpen"
        class="fixed inset-y-0 left-0 z-50 w-72 shadow-xl lg:hidden"
        style="background: linear-gradient(180deg, #150D35 0%, #0A0620 100%);" data-testid="mobile-sidebar">
        <x-app.sidebar />
    </div>

    <!-- Desktop Sidebar (always visible on lg screens) -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'"
        class="hidden sm:hidden md:hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:flex-col lg:border-r transition-all duration-300 ease-in-out"
        style="background: linear-gradient(180deg, #150D35 0%, #0A0620 100%); border-color: #1E1033;">
        <x-app.sidebar />
    </div>

    <!-- Main Column of Content -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:pl-20' : 'lg:pl-72'"
        class="flex flex-col min-h-screen transition-all duration-300 ease-in-out relative z-10">
        @php
            $topStatus = $topStatus ?? [];
        @endphp

        <!-- Sticky Header — Design Spec: 64px, rgba(255,255,255,0.95), blur(12px), border #EDE9FE -->
        <header class="sticky top-0 z-40"
            style="height: 64px; background: rgba(255,255,255,0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid #EDE9FE; box-shadow: 0 1px 8px rgba(124,58,237,0.06);"
            role="banner">
            <div class="flex h-full items-center gap-x-4 px-4 sm:px-6 lg:px-8">
                {{-- Mobile sidebar toggle --}}
                <button type="button" id="sidebar-toggle-btn"
                    class="lg:hidden shrink-0 rounded-lg p-2 transition-colors duration-150"
                    style="color: #7C6FAB; background: transparent;" @click="sidebarOpen = true"
                    :aria-expanded="sidebarOpen.toString()" data-testid="sidebar-toggle">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex flex-1 min-w-0">
                    <x-app.header />
                </div>
            </div>
        </header>

        <!-- Page Header (if provided via slot) -->
        @isset($header)
            <header class="shadow" style="background-color: #F9F5FF;">
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
        class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none" aria-live="polite"
        x-data="toastContainer" @toast.window="addFromEvent($event)">
        <template x-for="toast in items" :key="toast.id">
            <div class="pointer-events-auto alert shadow-lg"
                :class="{
                    'alert-success': toast.type === 'success',
                    'alert-error': toast.type === 'error',
                    'alert-warning': toast.type === 'warning',
                    'alert-info': toast.type === 'info' || !['success', 'error', 'warning'].includes(toast.type)
                }"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2">
                <div class="flex items-start gap-3">
                    <div class="flex-1" x-text="toast.message"></div>
                    <button type="button" @click="remove(toast.id)"
                        class="shrink-0 opacity-70 hover:opacity-100 transition-opacity"
                        aria-label="Dismiss notification">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
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
