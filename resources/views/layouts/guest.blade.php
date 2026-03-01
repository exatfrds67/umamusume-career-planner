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
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" media="print"
        onload="this.media='all'" />
    <noscript>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    </noscript>

    <!-- Synchronous Theme Initialization (prevents flash/mismatch on page load) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

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

<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:px-4 focus:py-2 focus:bg-white focus:dark:bg-gray-800 focus:text-primary-600 focus:dark:text-primary-400 focus:rounded-md focus:shadow-lg focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
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

    <!-- Content -->
    <div id="main-content" class="relative z-10">
        @yield('content')
    </div>

    @stack('scripts')
    @livewireScriptConfig
</body>

</html>
