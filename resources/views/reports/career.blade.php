@extends('layouts.app')

@section('title', 'Career Report - ' . ($career->character?->name ?? 'Unknown'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Page Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li><a href="{{ route('reports.index') }}"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Reports</a>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Career Report</span>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $career->character?->name ?? 'Unknown' }} - Career Report
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ ucfirst(str_replace('_', ' ', $report['report_metadata']['scenario_type'])) }} Career
                    • Report ID: {{ $report['report_metadata']['report_id'] }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Report
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10">
                        <a href="{{ route('reports.export.json', $career) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t-lg">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            Export as JSON
                        </a>
                        <a href="{{ route('reports.export.csv', $career) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export as CSV
                        </a>
                        <a href="{{ route('reports.export.pdf', $career) }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-b-lg">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export as PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Executive Summary --}}
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Executive Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p
                        class="text-2xl font-bold {{ $report['executive_summary']['career_status'] === 'completed' ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                        {{ ucfirst($report['executive_summary']['career_status']) }}
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Overall Grade</p>
                    <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $report['executive_summary']['overall_grade'] }}
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Progress</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $report['executive_summary']['total_turns'] }} turns
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $report['executive_summary']['completion_percentage'] }}% complete
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Best Stat</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ ucfirst($report['executive_summary']['highlight_stats']['best_stat']) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        +{{ $report['executive_summary']['highlight_stats']['best_value'] }} total
                    </p>
                </div>
            </div>

            {{-- Key Achievements --}}
            @if (!empty($report['executive_summary']['key_achievements']))
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Key Achievements</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($report['executive_summary']['key_achievements'] as $achievement)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $achievement }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Performance Overview --}}
        <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Efficiency & Rates --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Performance Metrics</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Training Efficiency</span>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['performance_overview']['efficiency_rating'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full"
                                style="width: {{ min(100, $report['performance_overview']['efficiency_rating']) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Training Success Rate</span>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['performance_overview']['training_success_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full"
                                style="width: {{ $report['performance_overview']['training_success_rate'] }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Race Win Rate</span>
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['performance_overview']['race_win_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full"
                                style="width: {{ $report['performance_overview']['race_win_rate'] }}%"></div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total SP Earned</span>
                            <span
                                class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ number_format($report['performance_overview']['sp_earned']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stat Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Stat Distribution</h2>
                <div class="space-y-3">
                    @php
                        $maxStat = max($report['performance_overview']['stat_distribution']);
                        $statColors = [
                            'speed' => 'bg-blue-500',
                            'stamina' => 'bg-orange-500',
                            'power' => 'bg-red-500',
                            'guts' => 'bg-pink-500',
                            'wit' => 'bg-green-500',
                        ];
                    @endphp
                    @foreach ($report['performance_overview']['stat_distribution'] as $stat => $value)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($stat) }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">+{{ $value }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div class="{{ $statColors[$stat] ?? 'bg-gray-500' }} h-3 rounded-full transition-all duration-300"
                                    style="width: {{ $maxStat > 0 ? ($value / $maxStat) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Training & Race Analysis --}}
        <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Training Analysis --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Training Analysis</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $report['training_analysis']['total_sessions'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Sessions</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ ucfirst($report['training_analysis']['best_training_type']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Best Training</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $report['training_analysis']['friendship_training_stats']['count'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Friendship Training</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p
                            class="text-2xl font-bold {{ $report['training_analysis']['failure_analysis']['total_failures'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $report['training_analysis']['failure_analysis']['total_failures'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Failures</p>
                    </div>
                </div>
            </div>

            {{-- Race Analysis --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Race Analysis</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $report['race_analysis']['total_races'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Races</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $report['race_analysis']['wins'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Wins</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $report['race_analysis']['win_rate'] }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Win Rate</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $report['race_analysis']['avg_position'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg Position</p>
                    </div>
                </div>

                @if ($report['race_analysis']['best_race'])
                    <div
                        class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Best Race</p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            {{ $report['race_analysis']['best_race']['race_name'] }}
                            ({{ $report['race_analysis']['best_race']['grade'] }})
                            - Position: {{ $report['race_analysis']['best_race']['position'] }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Key Insights & Recommendations --}}
        <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Key Insights --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    <svg class="w-5 h-5 inline mr-2 text-blue-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Key Insights
                </h2>
                <ul class="space-y-3">
                    @forelse($report['key_insights'] as $insight)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $insight }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">No insights available yet.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Recommendations --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    <svg class="w-5 h-5 inline mr-2 text-green-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recommendations
                </h2>
                <ul class="space-y-3">
                    @forelse($report['recommendations'] as $recommendation)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $recommendation }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">No recommendations available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Statistical Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Statistical Summary</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Stat</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mean</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Median</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Std Dev</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Min</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Max</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($report['statistical_summary']['stat_statistics'] as $stat => $stats)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ ucfirst($stat) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $stats['mean'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $stats['median'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $stats['std_dev'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $stats['min'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $stats['max'] }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                                    {{ $stats['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
