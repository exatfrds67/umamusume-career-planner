@extends('layouts.app')

@section('title', 'Historical Tracking & Benchmarking')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[
        ['label' => 'Analytics & Reports', 'url' => route('reports.index')],
        ['label' => 'Historical Tracking'],
    ]" />

    <div class="container mx-auto px-4 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Historical Tracking & Benchmarking</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Analyze your long-term performance trends and compare against community benchmarks.
            </p>
        </div>

        {{-- Error/Insufficient Data Alert --}}
        @if (isset($longTermTrends['error']))
            <div
                class="mb-6 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Insufficient Data</h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            {{ $longTermTrends['message'] ?? 'Complete more careers to unlock historical tracking features.' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Stats Cards --}}
        @if (!isset($longTermTrends['error']))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Total Careers --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Careers</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $longTermTrends['trend_summary']['total_careers'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Overall Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div
                            class="p-3 rounded-full {{ ($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'improving' ? 'bg-green-100 dark:bg-green-900' : (($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'declining' ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-700') }}">
                            @if (($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'improving')
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            @elseif(($longTermTrends['trend_summary']['overall_trend'] ?? '') === 'declining')
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overall Trend</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white capitalize">
                                {{ $longTermTrends['trend_summary']['overall_trend'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Success Rate --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $successRates['overall_success_rate']['success_rate'] ?? 0 }}%
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Percentile Ranking --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Percentile Rank</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $benchmarkComparison['percentile_rankings']['overall_percentile'] ?? 'N/A' }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Content Grid --}}
        @if (!isset($longTermTrends['error']))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Performance Evolution Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Evolution</h2>
                    <div class="space-y-4">
                        @foreach ($longTermTrends['performance_evolution'] ?? [] as $evolution)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Career
                                        #{{ $evolution['career_id'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $evolution['completed_at'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Score:
                                        {{ number_format($evolution['score'], 1) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Efficiency:
                                        {{ $evolution['efficiency'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Success Rate with Confidence Intervals --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Success Rate Analysis</h2>
                    @if (isset($successRates['overall_success_rate']))
                        <div class="space-y-4">
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $successRates['overall_success_rate']['success_rate'] }}%
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overall Success Rate</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    95% CI: {{ $successRates['overall_success_rate']['confidence_interval_95']['lower'] }}%
                                    - {{ $successRates['overall_success_rate']['confidence_interval_95']['upper'] }}%
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($successRates['success_by_scenario'] ?? [] as $scenario => $data)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                            {{ str_replace('_', ' ', $scenario) }}</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $data['success_rate'] }}%</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['sample_size'] }}
                                            careers</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Benchmark Comparison --}}
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Benchmark Comparison</h2>
                    @if (isset($benchmarkComparison['benchmark_comparison']))
                        <div class="space-y-4">
                            @foreach (['efficiency', 'win_rate', 'total_stats'] as $metric)
                                @if (isset($benchmarkComparison['benchmark_comparison'][$metric]))
                                    @php $data = $benchmarkComparison['benchmark_comparison'][$metric]; @endphp
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex justify-between items-center mb-2">
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $metric) }}</span>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full {{ $data['status'] === 'above_average' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($data['status'] === 'below_average' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200') }}">
                                                {{ ucfirst(str_replace('_', ' ', $data['status'])) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">You: {{ $data['user'] }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">Avg:
                                                {{ $data['benchmark'] }}</span>
                                            <span
                                                class="{{ $data['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $data['difference'] >= 0 ? '+' : '' }}{{ $data['difference'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Improvement Velocity --}}
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Improvement Velocity</h2>
                    @if (isset($longTermTrends['improvement_velocity']))
                        @php $velocity = $longTermTrends['improvement_velocity']; @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                                    <p
                                        class="text-2xl font-bold {{ $velocity['velocity'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $velocity['velocity'] >= 0 ? '+' : '' }}{{ $velocity['velocity'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Points/Career</p>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ $velocity['projected_next_score'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Projected Next</p>
                                </div>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <span class="font-medium">Trend:</span>
                                    {{ ucfirst(str_replace('_', ' ', $velocity['velocity_trend'])) }}
                                </p>
                                @if ($velocity['time_to_mastery'])
                                    <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                        <span class="font-medium">Est. careers to mastery:</span>
                                        {{ $velocity['time_to_mastery'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Improvement Areas & Recommendations --}}
        @if (!isset($longTermTrends['error']) && isset($benchmarkComparison['improvement_areas']))
            <div
                class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Areas for Improvement</h2>
                @if (!empty($benchmarkComparison['improvement_areas']))
                    <ul class="space-y-2">
                        @foreach ($benchmarkComparison['improvement_areas'] as $area)
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-yellow-500 mr-2 mt-0.5 shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">{{ $area }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 dark:text-gray-400">Great job! No significant improvement areas identified.</p>
                @endif
            </div>
        @endif

        {{-- Performance Summary --}}
        @if (!isset($longTermTrends['error']) && isset($benchmarkComparison['performance_summary']))
            <div
                class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Summary</h2>
                @php $summary = $benchmarkComparison['performance_summary']; @endphp

                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-blue-800 dark:text-blue-200">{{ $summary['overall_assessment'] ?? '' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Strengths --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Strengths</h3>
                        @if (!empty($summary['strengths']))
                            <ul class="space-y-1">
                                @foreach ($summary['strengths'] as $strength)
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $strength }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Keep working to develop your strengths!</p>
                        @endif
                    </div>

                    {{-- Notable Achievements --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Notable Achievements</h3>
                        @if (!empty($summary['notable_achievements']))
                            <ul class="space-y-1">
                                @foreach ($summary['notable_achievements'] as $achievement)
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4 text-yellow-500 mr-2" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        {{ $achievement }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Complete more careers to unlock
                                achievements!</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Refresh Cache Button --}}
        <div class="mt-8 flex justify-end">
            <button onclick="clearHistoricalCache()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
        </div>
    </div>

    @vite(['resources/js/pages/historical/index.js'])
@endsection
