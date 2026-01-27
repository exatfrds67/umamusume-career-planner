@extends('layouts.guest')

@section('title', 'About')

@section('content')
    {{-- Hero Section --}}
    <section class="py-16 md:py-24 bg-linear-to-br from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900"
        aria-labelledby="about-heading">
        <div class="container mx-auto px-4 text-center">
            <h1 id="about-heading" class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                Master Your Umamusume Training
            </h1>
            <p class="text-lg md:text-xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                Optimize your character development with AI-powered training recommendations,
                comprehensive stat tracking, and intelligent career planning for both URA Finale and Unity Cup
                scenarios.
            </p>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-16 md:py-24 bg-white dark:bg-gray-900" aria-labelledby="features-heading">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 id="features-heading"
                    class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Powerful Training Tools
                </h2>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    Everything you need to achieve A-grade rankings and optimize your character development
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- AI Training Recommendations --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div
                        class="flex items-center justify-center w-14 h-14 bg-primary-100 dark:bg-primary-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">AI Training Recommendations</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Get intelligent training suggestions powered by hybrid AI processing,
                        optimized for your character's goals and current stats.
                    </p>
                </article>

                {{-- Comprehensive Stat Tracking --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div
                        class="flex items-center justify-center w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Comprehensive Stat Tracking</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Monitor Speed, Stamina, Power, Guts, and Wisdom with detailed progression analytics
                        and performance visualization.
                    </p>
                </article>

                {{-- Skill Management --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div
                        class="flex items-center justify-center w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Advanced Skill Management</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Optimize skill acquisition with hint tracking, SP cost reduction calculations,
                        and evolution path planning.
                    </p>
                </article>

                {{-- Support Card Optimization --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Support Card Optimization</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Build optimal 6-card decks with meta tier analysis, friendship tracking,
                        and synergy recommendations.
                    </p>
                </article>

                {{-- Scenario Support --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">URA & Unity Cup Support</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Specialized strategies for both URA Finale and Unity Cup scenarios,
                        including Spirit Burst mechanics and team optimization.
                    </p>
                </article>

                {{-- Privacy & Accessibility --}}
                <article
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-200 border border-gray-200 dark:border-gray-700">
                    <div
                        class="flex items-center justify-center w-14 h-14 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl mb-4">
                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Privacy & Accessibility</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Local-first data architecture with WCAG 2.2 AA compliance,
                        ensuring your data stays private and accessible to everyone.
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- Call to Action Section --}}
    <section class="py-16 md:py-24 bg-primary-600 dark:bg-primary-700" aria-labelledby="cta-heading">
        <div class="container mx-auto px-4 text-center">
            <h2 id="cta-heading" class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to Optimize Your Training?
            </h2>
            <p class="text-xl text-primary-100 mb-10 max-w-3xl mx-auto leading-relaxed">
                Join trainers who are already achieving A-grade rankings with intelligent career planning
            </p>

            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center px-10 py-5 text-lg font-semibold text-primary-600 bg-white hover:bg-gray-50 rounded-lg shadow-xl hover:shadow-2xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary-600"
                aria-label="Start your training journey now">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Start Training Now
            </a>
        </div>
    </section>
@endsection
