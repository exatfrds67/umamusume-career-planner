@props(['analysis', 'character'])

<div class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deck Analysis Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Composition Score -->
        <div
            class="p-4 rounded-lg {{ $analysis['composition']['coverage_score'] >= 80 ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($analysis['composition']['coverage_score'] >= 60 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800') }}">
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium {{ $analysis['composition']['coverage_score'] >= 80 ? 'text-green-800 dark:text-green-200' : ($analysis['composition']['coverage_score'] >= 60 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                        Coverage Score
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold {{ $analysis['composition']['coverage_score'] >= 80 ? 'text-green-600 dark:text-green-400' : ($analysis['composition']['coverage_score'] >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ number_format($analysis['composition']['coverage_score'], 1) }}%
                    </p>
                </div>
                <svg class="w-8 h-8 {{ $analysis['composition']['coverage_score'] >= 80 ? 'text-green-500' : ($analysis['composition']['coverage_score'] >= 60 ? 'text-yellow-500' : 'text-red-500') }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>

        <!-- Synergy Score -->
        <div
            class="p-4 rounded-lg {{ $analysis['synergy']['synergy_score'] >= 70 ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($analysis['synergy']['synergy_score'] >= 50 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800') }}">
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium {{ $analysis['synergy']['synergy_score'] >= 70 ? 'text-green-800 dark:text-green-200' : ($analysis['synergy']['synergy_score'] >= 50 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                        Synergy Score
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold {{ $analysis['synergy']['synergy_score'] >= 70 ? 'text-green-600 dark:text-green-400' : ($analysis['synergy']['synergy_score'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ number_format($analysis['synergy']['synergy_score'], 1) }}%
                    </p>
                </div>
                <svg class="w-8 h-8 {{ $analysis['synergy']['synergy_score'] >= 70 ? 'text-green-500' : ($analysis['synergy']['synergy_score'] >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <!-- Meta Score -->
        <div
            class="p-4 rounded-lg {{ $analysis['meta_optimization']['meta_score']['total_score'] >= 70 ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($analysis['meta_optimization']['meta_score']['total_score'] >= 50 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800') }}">
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium {{ $analysis['meta_optimization']['meta_score']['total_score'] >= 70 ? 'text-green-800 dark:text-green-200' : ($analysis['meta_optimization']['meta_score']['total_score'] >= 50 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                        Meta Score
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold {{ $analysis['meta_optimization']['meta_score']['total_score'] >= 70 ? 'text-green-600 dark:text-green-400' : ($analysis['meta_optimization']['meta_score']['total_score'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ number_format($analysis['meta_optimization']['meta_score']['total_score'], 1) }}%
                    </p>
                </div>
                <svg class="w-8 h-8 {{ $analysis['meta_optimization']['meta_score']['total_score'] >= 70 ? 'text-green-500' : ($analysis['meta_optimization']['meta_score']['total_score'] >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    @if (!empty($analysis['composition']['recommendations']))
        <div class="mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Optimization Recommendations</h3>
            <ul class="space-y-1">
                @foreach (array_slice($analysis['composition']['recommendations'], 0, 3) as $recommendation)
                    <li class="text-sm text-blue-800 dark:text-blue-200 flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $recommendation }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
