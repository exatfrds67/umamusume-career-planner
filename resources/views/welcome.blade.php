<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- PWA Manifest and Theme -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UmaCareer">

    <!-- PWA Icons for iOS -->
    <link rel="apple-touch-icon" href="/images/app_logo/uma_musume_race_planner_logo_256.png">
    <link rel="apple-touch-icon" sizes="128x128" href="/images/app_logo/uma_musume_race_planner_logo_128.png">
    <link rel="apple-touch-icon" sizes="256x256" href="/images/app_logo/uma_musume_race_planner_logo_256.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/images/app_logo/uma_musume_race_planner_logo_512.png">

    <title>{{ config('app.name', 'UmaCareer') }} - Umamusume Pretty Derby Career Planner</title>
    <meta name="description"
        content="Optimize your Umamusume Pretty Derby training with AI-powered recommendations, comprehensive stat tracking, and intelligent career planning for both URA Finale and Unity Cup scenarios.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|instrument-sans:400,500,600"
        rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /* Tailwind CSS v4 Base Styles */
            @layer theme {
                :root {
                    --font-sans: 'Inter', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                    --color-primary-50: #eff6ff;
                    --color-primary-100: #dbeafe;
                    --color-primary-500: #3b82f6;
                    --color-primary-600: #2563eb;
                    --color-primary-700: #1d4ed8;
                    --color-primary-900: #1e3a8a;
                    --color-gray-50: #f9fafb;
                    --color-gray-100: #f3f4f6;
                    --color-gray-200: #e5e7eb;
                    --color-gray-300: #d1d5db;
                    --color-gray-400: #9ca3af;
                    --color-gray-500: #6b7280;
                    --color-gray-600: #4b5563;
                    --color-gray-700: #374151;
                    --color-gray-800: #1f2937;
                    --color-gray-900: #111827;
                    --color-green-500: #10b981;
                    --color-green-600: #059669;
                    --color-red-500: #ef4444;
                    --color-red-600: #dc2626;
                }
            }

            @layer base {
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }

                html {
                    font-family: var(--font-sans);
                    line-height: 1.5;
                    -webkit-text-size-adjust: 100%;
                    -moz-tab-size: 4;
                    tab-size: 4;
                }

                body {
                    font-family: inherit;
                    line-height: inherit;
                    color: var(--color-gray-900);
                    background-color: var(--color-gray-50);
                }

                h1,
                h2,
                h3,
                h4,
                h5,
                h6 {
                    font-size: inherit;
                    font-weight: inherit;
                }

                a {
                    color: inherit;
                    text-decoration: inherit;
                }

                button,
                input,
                select,
                textarea {
                    font: inherit;
                    color: inherit;
                }

                img,
                svg,
                video,
                canvas,
                audio,
                iframe,
                embed,
                object {
                    display: block;
                    vertical-align: middle;
                }

                img,
                video {
                    max-width: 100%;
                    height: auto;
                }
            }

            /* Utility Classes */
            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            .focus\:not-sr-only:focus {
                position: static;
                width: auto;
                height: auto;
                padding: 0;
                margin: 0;
                overflow: visible;
                clip: auto;
                white-space: normal;
            }

            .container {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .flex {
                display: flex;
            }

            .flex-col {
                flex-direction: column;
            }

            .items-center {
                align-items: center;
            }

            .justify-center {
                justify-content: center;
            }

            .justify-between {
                justify-content: space-between;
            }

            .min-h-screen {
                min-height: 100vh;
            }

            .w-full {
                width: 100%;
            }

            .h-full {
                height: 100%;
            }

            .text-center {
                text-align: center;
            }

            .text-left {
                text-align: left;
            }

            .text-sm {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .text-base {
                font-size: 1rem;
                line-height: 1.5rem;
            }

            .text-lg {
                font-size: 1.125rem;
                line-height: 1.75rem;
            }

            .text-xl {
                font-size: 1.25rem;
                line-height: 1.75rem;
            }

            .text-2xl {
                font-size: 1.5rem;
                line-height: 2rem;
            }

            .text-3xl {
                font-size: 1.875rem;
                line-height: 2.25rem;
            }

            .text-4xl {
                font-size: 2.25rem;
                line-height: 2.5rem;
            }

            .font-medium {
                font-weight: 500;
            }

            .font-semibold {
                font-weight: 600;
            }

            .font-bold {
                font-weight: 700;
            }

            .text-gray-600 {
                color: var(--color-gray-600);
            }

            .text-gray-700 {
                color: var(--color-gray-700);
            }

            .text-gray-800 {
                color: var(--color-gray-800);
            }

            .text-primary-600 {
                color: var(--color-primary-600);
            }

            .bg-white {
                background-color: white;
            }

            .bg-gray-50 {
                background-color: var(--color-gray-50);
            }

            .bg-primary-600 {
                background-color: var(--color-primary-600);
            }

            .bg-primary-700 {
                background-color: var(--color-primary-700);
            }

            .hover\:bg-primary-700:hover {
                background-color: var(--color-primary-700);
            }

            .hover\:bg-gray-50:hover {
                background-color: var(--color-gray-50);
            }

            .border {
                border-width: 1px;
            }

            .border-gray-200 {
                border-color: var(--color-gray-200);
            }

            .border-gray-300 {
                border-color: var(--color-gray-300);
            }

            .rounded {
                border-radius: 0.25rem;
            }

            .rounded-md {
                border-radius: 0.375rem;
            }

            .rounded-lg {
                border-radius: 0.5rem;
            }

            .shadow {
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }

            .shadow-lg {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            .p-4 {
                padding: 1rem;
            }

            .p-6 {
                padding: 1.5rem;
            }

            .p-8 {
                padding: 2rem;
            }

            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .py-3 {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .py-4 {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .py-12 {
                padding-top: 3rem;
                padding-bottom: 3rem;
            }

            .py-16 {
                padding-top: 4rem;
                padding-bottom: 4rem;
            }

            .mb-4 {
                margin-bottom: 1rem;
            }

            .mb-6 {
                margin-bottom: 1.5rem;
            }

            .mb-8 {
                margin-bottom: 2rem;
            }

            .mt-8 {
                margin-top: 2rem;
            }

            .mt-12 {
                margin-top: 3rem;
            }

            .grid {
                display: grid;
            }

            .grid-cols-1 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .gap-4 {
                gap: 1rem;
            }

            .gap-6 {
                gap: 1.5rem;
            }

            .gap-8 {
                gap: 2rem;
            }

            .focus\:outline-none:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
            }

            .focus\:ring-2:focus {
                box-shadow: 0 0 0 2px var(--color-primary-500);
            }

            .focus\:ring-offset-2:focus {
                box-shadow: 0 0 0 2px white, 0 0 0 4px var(--color-primary-500);
            }

            .transition {
                transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 150ms;
            }

            /* Button Styles */
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.5rem;
                font-size: 0.875rem;
                font-weight: 500;
                border-radius: 0.375rem;
                border: 1px solid transparent;
                text-decoration: none;
                transition: all 150ms ease-in-out;
                cursor: pointer;
            }

            .btn:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
                box-shadow: 0 0 0 2px white, 0 0 0 4px var(--color-primary-500);
            }

            .btn-primary {
                background-color: var(--color-primary-600);
                color: white;
            }

            .btn-primary:hover {
                background-color: var(--color-primary-700);
            }

            .btn-secondary {
                background-color: white;
                color: var(--color-gray-700);
                border-color: var(--color-gray-300);
            }

            .btn-secondary:hover {
                background-color: var(--color-gray-50);
            }

            /* Card Styles */
            .card {
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                padding: 1.5rem;
            }

            /* Responsive Design */
            @media (min-width: 640px) {
                .sm\:grid-cols-2 {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .sm\:text-lg {
                    font-size: 1.125rem;
                    line-height: 1.75rem;
                }

                .sm\:text-xl {
                    font-size: 1.25rem;
                    line-height: 1.75rem;
                }
            }

            @media (min-width: 768px) {
                .md\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .md\:text-2xl {
                    font-size: 1.5rem;
                    line-height: 2rem;
                }

                .md\:text-4xl {
                    font-size: 2.25rem;
                    line-height: 2.5rem;
                }

                .md\:py-20 {
                    padding-top: 5rem;
                    padding-bottom: 5rem;
                }
            }

            @media (min-width: 1024px) {
                .lg\:grid-cols-3 {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .lg\:text-5xl {
                    font-size: 3rem;
                    line-height: 1;
                }
            }

            /* Background Image */
            .hero-bg {
                background-image: url('/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

            @media (max-width: 767px) {
                .hero-bg {
                    background-image: url('/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png');
                }
            }

            @media (prefers-color-scheme: dark) {
                .hero-bg {
                    background-image: url('/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png');
                }

                @media (max-width: 767px) {
                    .hero-bg {
                        background-image: url('/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png');
                    }
                }
            }

            /* Dark mode styles */
            @media (prefers-color-scheme: dark) {
                :root {
                    --color-gray-50: #1f2937;
                    --color-gray-100: #374151;
                    --color-gray-200: #4b5563;
                    --color-gray-300: #6b7280;
                    --color-gray-400: #9ca3af;
                    --color-gray-500: #d1d5db;
                    --color-gray-600: #e5e7eb;
                    --color-gray-700: #f3f4f6;
                    --color-gray-800: #f9fafb;
                    --color-gray-900: #ffffff;
                }

                body {
                    background-color: var(--color-gray-50);
                    color: var(--color-gray-900);
                }

                .bg-white {
                    background-color: var(--color-gray-100);
                }

                .card {
                    background-color: var(--color-gray-100);
                }
            }
        </style>
    @endif
</head>

<body class="min-h-screen bg-gray-50">
    {{-- Accessibility: Skip Links for keyboard navigation --}}
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
        tabindex="0">
        Skip to main content
    </a>

    {{-- Accessibility: ARIA Live Region for dynamic announcements --}}
    <div id="aria-live-region" aria-live="polite" aria-atomic="true" class="sr-only" role="status">
        Welcome to Umamusume Career Planner
    </div>

    {{-- Header Navigation --}}
    <header class="bg-white shadow" role="banner">
        <nav class="container flex items-center justify-between py-4" role="navigation" aria-label="Main navigation">
            <div class="flex items-center">
                <img src="/images/app_logo/uma_musume_race_planner_logo_128.png" alt="UmaCareer Logo"
                    class="h-8 w-8 mr-3" width="32" height="32">
                <h1 class="text-xl font-bold text-gray-800">{{ config('app.name', 'UmaCareer') }}</h1>
            </div>

            @if (Route::has('login'))
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary" aria-label="Go to Dashboard">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary" aria-label="Sign in to your account">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary" aria-label="Create a new account">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    {{-- Main Content --}}
    <main id="main-content" role="main">
        {{-- Hero Section --}}
        <section class="hero-bg py-16 md:py-20" aria-labelledby="hero-heading">
            <div class="container text-center">
                <div class="bg-white bg-opacity-90 rounded-lg p-8 shadow-lg max-w-4xl mx-auto">
                    <h2 id="hero-heading" class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-800 mb-6">
                        Master Your Umamusume Training
                    </h2>
                    <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Optimize your character development with AI-powered training recommendations,
                        comprehensive stat tracking, and intelligent career planning for both URA Finale and Unity Cup
                        scenarios.
                    </p>

                    @guest
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('register') }}" class="btn btn-primary text-lg px-8 py-3"
                                aria-label="Start your training journey">
                                Get Started
                            </a>
                            <a href="#features" class="btn btn-secondary text-lg px-8 py-3"
                                aria-label="Learn more about features">
                                Learn More
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary text-lg px-8 py-3"
                                aria-label="Continue to your dashboard">
                                Continue Training
                            </a>
                            <a href="#features" class="btn btn-secondary text-lg px-8 py-3"
                                aria-label="Explore all features">
                                Explore Features
                            </a>
                        </div>
                    @endguest
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="features" class="py-16 bg-white" aria-labelledby="features-heading">
            <div class="container">
                <div class="text-center mb-12">
                    <h2 id="features-heading" class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                        Powerful Training Tools
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Everything you need to achieve A-grade rankings and optimize your character development
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- AI Training Recommendations --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div
                                class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">AI Training Recommendations</h3>
                        <p class="text-gray-600">
                            Get intelligent training suggestions powered by hybrid AI processing,
                            optimized for your character's goals and current stats.
                        </p>
                    </article>

                    {{-- Comprehensive Stat Tracking --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Comprehensive Stat Tracking</h3>
                        <p class="text-gray-600">
                            Monitor Speed, Stamina, Power, Guts, and Wisdom with detailed progression analytics
                            and performance visualization.
                        </p>
                    </article>

                    {{-- Skill Management --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div
                                class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Advanced Skill Management</h3>
                        <p class="text-gray-600">
                            Optimize skill acquisition with hint tracking, SP cost reduction calculations,
                            and evolution path planning.
                        </p>
                    </article>

                    {{-- Support Card Optimization --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Support Card Optimization</h3>
                        <p class="text-gray-600">
                            Build optimal 6-card decks with meta tier analysis, friendship tracking,
                            and synergy recommendations.
                        </p>
                    </article>

                    {{-- Scenario Support --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">URA & Unity Cup Support</h3>
                        <p class="text-gray-600">
                            Specialized strategies for both URA Finale and Unity Cup scenarios,
                            including Spirit Burst mechanics and team optimization.
                        </p>
                    </article>

                    {{-- Privacy & Accessibility --}}
                    <article class="card text-center">
                        <div class="mb-4">
                            <div
                                class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Privacy & Accessibility</h3>
                        <p class="text-gray-600">
                            Local-first data architecture with WCAG 2.2 AA compliance,
                            ensuring your data stays private and accessible to everyone.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- Call to Action Section --}}
        <section class="py-16 bg-primary-600" aria-labelledby="cta-heading">
            <div class="container text-center">
                <h2 id="cta-heading" class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Ready to Optimize Your Training?
                </h2>
                <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
                    Join trainers who are already achieving A-grade rankings with intelligent career planning
                </p>

                @guest
                    <a href="{{ route('register') }}"
                        class="btn bg-white text-primary-600 hover:bg-gray-50 text-lg px-8 py-3"
                        aria-label="Start your training journey now">
                        Start Training Now
                    </a>
                @else
                    <a href="{{ url('/dashboard') }}"
                        class="btn bg-white text-primary-600 hover:bg-gray-50 text-lg px-8 py-3"
                        aria-label="Continue to your dashboard">
                        Continue to Dashboard
                    </a>
                @endguest
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-8" role="contentinfo">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="/images/app_logo/uma_musume_race_planner_logo_128.png" alt="UmaCareer Logo"
                            class="h-8 w-8 mr-3" width="32" height="32">
                        <h3 class="text-lg font-semibold">{{ config('app.name', 'UmaCareer') }}</h3>
                    </div>
                    <p class="text-gray-300">
                        Advanced optimization application for Umamusume Pretty Derby mobile game training.
                    </p>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Features</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#features" class="hover:text-white transition">AI Training Recommendations</a>
                        </li>
                        <li><a href="#features" class="hover:text-white transition">Stat Tracking</a></li>
                        <li><a href="#features" class="hover:text-white transition">Skill Management</a></li>
                        <li><a href="#features" class="hover:text-white transition">Support Card Optimization</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                        <li><a href="#" class="hover:text-white transition">Accessibility</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'UmaCareer') }}. All rights reserved.</p>
                <p class="mt-2 text-sm">
                    This application is not affiliated with Cygames or Umamusume Pretty Derby.
                </p>
            </div>
        </div>
    </footer>

    {{-- Accessibility: Focus management script --}}
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Set focus to target for screen readers
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                }
            });
        });

        // Announce page load completion
        window.addEventListener('load', function() {
            const liveRegion = document.getElementById('aria-live-region');
            if (liveRegion) {
                setTimeout(() => {
                    liveRegion.textContent =
                        'Page loaded successfully. Welcome to Umamusume Career Planner.';
                }, 1000);
            }
        });

        // Keyboard navigation enhancement
        document.addEventListener('keydown', function(e) {
            // Skip to main content with Alt+M
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    mainContent.setAttribute('tabindex', '-1');
                    mainContent.focus();
                }
            }
        });
    </script>
</body>

</html>
