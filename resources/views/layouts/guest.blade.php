<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Umamusume Career Planner'))</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('images/app_logo/uma_musume_race_planner_logo_16.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32"
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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900">
    <!-- Fixed Background with Theme-Aware Images -->
    <div id="app-background" class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500"
        role="presentation" data-bg-light-desktop="/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png"
        data-bg-light-mobile="/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png"
        data-bg-dark-desktop="/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png"
        data-bg-dark-mobile="/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png" aria-hidden="true">
    </div>

    <!-- Content -->
    <div class="relative z-10">
        @yield('content')
    </div>

    @stack('scripts')
</body>

</html>
