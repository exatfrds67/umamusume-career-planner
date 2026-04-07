@extends('layouts.app')

@section('title', 'AI Career Assistant')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'AI & Tools', 'url' => route('ai.dashboard')], ['label' => 'AI Chat']]" />

    <div class="page-stack flex-1 min-h-0 animate-fade-in">
        <div class="page-hero shrink-0">
            <div class="page-hero__content">
                <div class="min-w-0 flex-1">
                    <div class="page-hero__eyebrow">
                        <span>AI Guidance</span>
                    </div>
                    <h1 class="page-hero__title sm:truncate">AI Career Assistant</h1>
                    <p class="page-hero__body text-sm sm:text-base">
                        Get personalized advice and strategic guidance for your Umamusume career planning.
                    </p>
                </div>
            </div>
        </div>

            {{-- Character Context (if available) --}}
            @if (isset($character))
                <div
                    class="card rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 sm:p-5 shrink-0">
                    <div class="flex items-center gap-4">
                        <x-character-portrait :image="$character->avatar_url" :alt="$character->name ?? 'Character portrait'" size="lg" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                                {{ $character->name ?? 'Character' }}</h3>
                            <div class="flex items-center gap-4 mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                <span>{{ ucfirst($character->scenario_type ?? 'unknown') }}</span>
                                <span>•</span>
                                <span>{{ $character->career_stage ?? 'N/A' }}</span>
                                @if (isset($character->currentCareer) && $character->currentCareer)
                                    <span>•</span>
                                    <span>Turn {{ $character->currentCareer->current_turn ?? 0 }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('characters.show', $character) }}"
                            class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                            View Character
                        </a>
                    </div>
                </div>
            @endif

            {{-- Main Chat Interface --}}
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg overflow-hidden border border-neutral-200 dark:border-neutral-700 flex flex-col flex-1 min-h-0"
                role="region" aria-label="Chat conversation">
                <x-ai.chat-interface :character-id="$character->id ?? null" :career-id="isset($character->currentCareer) && $character->currentCareer
                    ? $character->currentCareer->id
                    : null" class="flex-1 min-h-0" />
            </div>

            {{-- Quick Actions --}}
            <section x-data class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 shrink-0" aria-label="Quick Actions">
                <button @click="window.sendQuickMessage('What training should I focus on next?')"
                    class="p-3 bg-white dark:bg-neutral-800 rounded-xl shadow-xs border border-neutral-200 dark:border-neutral-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-neutral-900 dark:text-white truncate">Training Advice</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">Get training recommendations</div>
                        </div>
                    </div>
                </button>

                <button @click="window.sendQuickMessage('How should I prepare for my next race?')"
                    class="p-3 bg-white dark:bg-neutral-800 rounded-xl shadow-xs border border-neutral-200 dark:border-neutral-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:bg-purple-100 dark:group-hover:bg-purple-900/50 transition-colors shrink-0">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-neutral-900 dark:text-white truncate">Race Strategy</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">Get race preparation tips</div>
                        </div>
                    </div>
                </button>

                <button @click="window.sendQuickMessage('What skills should I prioritize?')"
                    class="p-3 bg-white dark:bg-neutral-800 rounded-xl shadow-xs border border-neutral-200 dark:border-neutral-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-50 dark:bg-orange-900/30 rounded-lg flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-900/50 transition-colors shrink-0">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Skill Building</div>
                            <div class="text-xs text-neutral-600 dark:text-neutral-400">Get skill optimization advice</div>
                        </div>
                    </div>
                </button>
            </section>
    </div>

    @vite(['resources/js/pages/ai/chat.js'])
@endsection
