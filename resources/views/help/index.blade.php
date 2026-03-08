@extends('layouts.app')

@section('title', 'Help & Support')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Help & Support']]" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="border-b border-neutral-200 dark:border-neutral-700 pb-5">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white sm:text-3xl">Help & Support</h1>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                Find guidance on getting started, common workflows, and accessibility resources.
            </p>
        </div>

        <!-- Quick Start Guide -->
        <section aria-labelledby="quick-start-heading">
            <h2 id="quick-start-heading" class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Quick Start Guide</h2>
            <div class="glass-card rounded-xl p-6">
                <ol class="space-y-4" role="list">
                    <li class="flex items-start gap-3">
                        <span class="shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-bold" aria-hidden="true">1</span>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Create a Character</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Pick a trainee from the database, set stats, and configure aptitudes.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-bold" aria-hidden="true">2</span>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Plan Training</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Use the training planner to schedule stat growth across turns.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-bold" aria-hidden="true">3</span>
                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">Review Race Readiness</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Check stats, skills, and aptitudes to make sure your trainee is race-ready.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Resource Cards -->
        <section aria-labelledby="resources-heading">
            <h2 id="resources-heading" class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Resources</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="glass-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30" aria-hidden="true">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Getting Started</h3>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        Learn the core workflow: create a character, plan training, and review race readiness.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('about') }}" variant="primary" size="sm">Read Overview</x-button>
                        <x-button href="{{ route('demo') }}" variant="outline" size="sm">View Demo</x-button>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30" aria-hidden="true">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Accessibility</h3>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        Review our WCAG 2.2 AA statement and available keyboard shortcuts.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('accessibility.statement') }}" variant="secondary" size="sm">Accessibility Statement</x-button>
                        <x-button href="{{ route('keyboard.shortcuts') }}" variant="outline" size="sm">Keyboard Shortcuts</x-button>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30" aria-hidden="true">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Privacy & Data</h3>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        Understand how your data is stored locally and how export options work.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('privacy.policy') }}" variant="secondary" size="sm">Privacy Policy</x-button>
                        <x-button href="{{ route('terms.service') }}" variant="outline" size="sm">Terms of Service</x-button>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30" aria-hidden="true">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Feedback</h3>
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        Share ideas or report issues to help us improve the planner.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-button href="{{ route('feedback.create') }}" variant="primary" size="sm">Send Feedback</x-button>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Frequently Asked Questions</h2>
            <div class="glass-card rounded-xl divide-y divide-neutral-200 dark:divide-neutral-700" x-data="{ openFaq: null }">
                @php
                    $faqs = [
                        ['q' => 'How do I create my first character?', 'a' => 'Go to Characters → Create Character. Use the Character Database to pick a trainee, then configure stats and aptitudes through the wizard steps.'],
                        ['q' => 'What is the difference between Local and Account storage?', 'a' => 'Local storage saves data in your browser and works offline. Account storage saves to the database and syncs across devices when logged in.'],
                        ['q' => 'How do I plan a training schedule?', 'a' => 'Navigate to Training → Predictions to view AI-powered training recommendations based on your character\'s current stats and goals.'],
                        ['q' => 'Can I export my character data?', 'a' => 'Yes! Go to Data Management → Export to download your character data as JSON. You can import it back later or share with others.'],
                        ['q' => 'How do keyboard shortcuts work?', 'a' => 'Press Ctrl+/ (or Cmd+/ on Mac) to view all available keyboard shortcuts. You can also find them under Accessibility → Keyboard Shortcuts.'],
                    ];
                @endphp
                @foreach ($faqs as $index => $faq)
                    <div class="px-5">
                        <button type="button"
                            class="w-full flex items-center justify-between py-4 text-left text-sm font-medium text-neutral-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                            @click="openFaq === {{ $index }} ? openFaq = null : openFaq = {{ $index }}"
                            :aria-expanded="openFaq === {{ $index }}"
                            aria-controls="faq-answer-{{ $index }}">
                            <span>{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-neutral-400 transition-transform duration-200" :class="openFaq === {{ $index }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openFaq === {{ $index }}" x-collapse id="faq-answer-{{ $index }}" role="region" :aria-labelledby="'faq-q-{{ $index }}'">
                            <p class="pb-4 text-sm text-neutral-500 dark:text-neutral-400">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
