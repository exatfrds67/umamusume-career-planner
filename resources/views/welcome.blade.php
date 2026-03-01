@extends('layouts.guest')

@section('title', 'Welcome - Umamusume Career Planner')

@section('content')
    {{-- Skip to main content link for keyboard navigation (Requirement 1.4, 10.6) --}}
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg focus:shadow-lg focus:outline-hidden focus:ring-4 focus:ring-primary-500 focus:ring-offset-2"
        aria-label="Skip to main content">
        Skip to main content
    </a>

    {{-- Hero Section - Two Column Layout --}}
    <main id="main-content" class="relative min-h-screen flex items-center justify-center py-12" role="main"
        aria-labelledby="hero-heading">

        {{-- Responsive container with two-column layout --}}
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- LEFT COLUMN: Main Hero Card --}}
                <div class="lg:col-span-1">
                    <div class="glass-card rounded-2xl p-6 sm:p-8 transition-all duration-300 animate-fade-in h-full">

                        {{-- Logo and Title Section --}}
                        <div class="text-center mb-6">
                            <img src="/images/app_logo/uma_musume_race_planner_logo_256.png"
                                alt="{{ config('app.name', 'Umamusume Career Planner') }} logo"
                                class="h-20 w-20 sm:h-24 sm:w-24 mx-auto mb-4 animate-fade-in-delay-1" width="96"
                                height="96" loading="eager" fetchpriority="high" decoding="async">

                            <h1 id="hero-heading"
                                class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2 transition-colors duration-300 animate-fade-in-delay-2">
                                Umamusume<br>Career Planner
                            </h1>

                            <p
                                class="text-lg sm:text-xl text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300 animate-fade-in-delay-2">
                                Advanced Optimization for Umamusume Pretty Derby
                            </p>

                            <p
                                class="text-sm sm:text-base text-gray-700 dark:text-gray-300 max-w-2xl mx-auto mb-6 animate-fade-in-delay-2">
                                Leverage modern web technologies, machine learning, and community integration to reach
                                S-rank aptitudes and achieve Grade 1 victories in both URA Finale and Unity Cup scenarios
                            </p>

                            {{-- Primary CTA Button --}}
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center justify-center min-h-11 min-w-11 px-8 sm:px-10 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-200 focus:outline-hidden focus:ring-4 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transform hover:scale-105 active:scale-100 animate-fade-in-delay-3"
                                    aria-label="Go to your dashboard">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Go to Dashboard
                                </a>
                            @else
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center min-h-11 min-w-11 px-8 sm:px-10 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-200 focus:outline-hidden focus:ring-4 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transform hover:scale-105 active:scale-100 animate-fade-in-delay-3"
                                        aria-label="Sign in to your account">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        Sign In
                                    </a>
                                    <a href="{{ route('register') }}"
                                        class="inline-flex items-center justify-center min-h-11 min-w-11 px-8 sm:px-10 py-3 sm:py-4 text-base sm:text-lg font-semibold text-primary-700 bg-white hover:bg-gray-50 active:bg-gray-100 dark:bg-gray-800 dark:text-primary-300 dark:hover:bg-gray-700 dark:active:bg-gray-600 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-200 focus:outline-hidden focus:ring-4 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transform hover:scale-105 active:scale-100 animate-fade-in-delay-3"
                                        aria-label="Create a new account">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                        Get Started
                                    </a>
                                </div>
                            @endauth
                        </div>


                        {{-- 4-Column Feature Grid --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6 animate-fade-in-delay-3">
                            {{-- Training Optimization --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Training</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">Turn-by-turn AI predictions</p>
                            </div>

                            {{-- Race Strategy --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Race Strategy</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">Readiness & running style scoring</p>
                            </div>

                            {{-- Skill Management --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Skills</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">SP optimization</p>
                            </div>

                            {{-- AI Advisory --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">AI Advisory</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">Hybrid AI system</p>
                            </div>

                            {{-- Support Deck Builder --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Support Deck</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">6-card optimizer</p>
                            </div>

                            {{-- Career Tracking --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-600 dark:text-secondary-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Career Tracking</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">60-70 turn analysis</p>
                            </div>

                            {{-- Privacy First --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-success-600 dark:text-success-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Privacy First</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">Local storage</p>
                            </div>

                            {{-- Modern Tech Stack --}}
                            <div class="text-center p-3 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-600 dark:text-secondary-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1">Modern Tech</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300">Laravel 12 + AI</p>
                            </div>
                        </div>

                        {{-- Technology Highlights --}}
                        <div class="mb-6 p-3 glass-card-inner rounded-lg animate-fade-in-delay-4">
                            <p class="text-xs text-center text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">Built with:</span> Laravel 12 • Tailwind CSS v4 • Hybrid AI
                                (Ollama + AWS Bedrock) • Redis • PWA
                            </p>
                        </div>

                        {{-- Secondary Links --}}
                        <div
                            class="flex flex-col sm:flex-row items-center justify-center gap-4 text-center animate-fade-in-delay-4">
                            <a href="{{ route('about') }}"
                                class="inline-flex items-center min-h-11 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded px-2"
                                aria-label="Learn more about Umamusume Career Planner">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Learn More
                            </a>
                            <a href="https://github.com/exatfrds67/umamusume-career-planner" target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center min-h-11 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded px-2"
                                aria-label="View on GitHub (opens in new tab)">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                        clip-rule="evenodd" />
                                </svg>
                                GitHub
                            </a>
                            @if (app()->environment('local', 'development'))
                                <a href="{{ route('dev.demos') }}"
                                    class="inline-flex items-center min-h-11 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200 focus:outline-hidden focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded px-2"
                                    aria-label="Developer demos and testing tools">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    Dev Tools
                                </a>
                            @endif
                        </div>

                        {{-- Additional info for screen readers --}}
                        <div class="sr-only" role="status" aria-live="polite">
                            Welcome to UmamusumeCareerPlanner. This advanced optimization application helps you plan and
                            optimize
                            your Umamusume Pretty Derby training career with AI-powered recommendations, training
                            predictions, race
                            strategy analysis, and comprehensive skill management.
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Scenario Support and Game Mechanics --}}
                <div class="lg:col-span-1 flex flex-col gap-6">

                    {{-- Comprehensive Scenario Support --}}
                    <div class="glass-card-alt rounded-xl p-8 animate-fade-in-delay-5"
                        role="region" aria-labelledby="scenario-support-heading">
                        <h2 id="scenario-support-heading"
                            class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-6">Comprehensive
                            Scenario
                            Support</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="glass-card-inner rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-primary-700 dark:text-primary-400 mb-3">URA Finale
                                </h3>
                                <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Individual character optimization</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Bond-level friendship training (80%+ threshold)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Classic race schedule planning</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>5-stat system with 1200+ soft cap management</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="glass-card-inner rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-secondary-700 dark:text-secondary-400 mb-3">Unity Cup
                                </h3>
                                <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Team composition optimization</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Spirit Burst gauge for massive training boosts</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Team-rank-based facility level progression</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-success-600 dark:text-success-300 shrink-0 mt-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Competitive team strategies</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- BOTTOM: Advanced Game Mechanics --}}
                    <div class="glass-card-alt rounded-xl p-8 animate-fade-in-delay-5">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-6">Advanced Game
                            Mechanics
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Skill Evolution</div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">Track evolution chains and upgrade
                                    paths
                                </div>
                            </div>
                            <div class="text-center p-4 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-600 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Hint Optimization
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">5-level system, up to 40% SP cost reduction
                                </div>
                            </div>
                            <div class="text-center p-4 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                </svg>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Weather System</div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">Condition-based performance
                                    optimization
                                </div>
                            </div>
                            <div class="text-center p-4 glass-card-inner rounded-lg">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Turn Economy</div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">Optimize 60-70 turn career
                                    progression
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Theme toggle button (Requirements 13.1, 13.2) --}}
    <div class="fixed bottom-4 right-4 z-40" x-data="{ isDark: document.documentElement.classList.contains('dark') }" @theme-changed.window="isDark = $event.detail">
        <button type="button" id="theme-toggle"
            class="inline-flex items-center justify-center min-h-11 min-w-11 p-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-hidden focus:ring-4 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            aria-label="Toggle theme" :aria-pressed="isDark.toString()">
            <svg class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>
@endsection
