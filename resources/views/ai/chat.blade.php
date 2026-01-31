@extends('layouts.app')

@section('title', 'AI Career Assistant')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'AI & Tools', 'url' => route('ai.dashboard')], ['label' => 'AI Chat']]" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page Header --}}
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">AI Career Assistant</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Get personalized advice and strategic guidance for your Umamusume career planning
                </p>
            </div>

            {{-- Character Context (if available) --}}
            @if (isset($character))
                <div
                    class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $character->name ?? 'Character' }}</h3>
                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-600 dark:text-gray-400">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
                style="height: calc(100vh - 300px); min-height: 600px;">
                <x-ai.chat-interface :character-id="$character->id ?? null" :career-id="isset($character->currentCareer) && $character->currentCareer
                    ? $character->currentCareer->id
                    : null" />
            </div>

            {{-- Quick Actions --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <button @click="sendQuickMessage('What training should I focus on next?')"
                    class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Training Advice</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Get training recommendations</div>
                        </div>
                    </div>
                </button>

                <button @click="sendQuickMessage('How should I prepare for my next race?')"
                    class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Race Strategy</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Get race preparation tips</div>
                        </div>
                    </div>
                </button>

                <button @click="sendQuickMessage('What skills should I prioritize?')"
                    class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Skill Building</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Get skill optimization advice</div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/ai/chat.js'])
@endsection
