@extends('layouts.app')

@section('title', 'Character Report - ' . ($character?->name ?? 'Unknown'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
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
                        <span class="text-gray-700 dark:text-gray-300">Character Report</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $character?->name ?? 'Unknown' }} - Character Report</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Comprehensive analysis across {{ $report['character_info']['total_careers'] }} career(s)
            </p>
        </div>

        {{-- Character Overview --}}
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    @if ($character?->avatar_url)
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}"
                            loading="lazy" decoding="async"
                            class="w-20 h-20 rounded-full object-cover">
                    @else
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                            {{ substr($character?->name ?? 'U', 0, 1) }}
                        </span>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report['character_info']['name'] ?? 'Unknown' }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ ucfirst(str_replace('_', ' ', $report['character_info']['scenario_type'] ?? 'unknown')) }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $report['character_info']['total_careers'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Careers</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ $report['character_info']['completed_careers'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Trends --}}
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Performance Trends</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Trend Direction</p>
                    <p
                        class="text-2xl font-bold {{ $report['performance_trends']['trend_direction'] === 'improving' ? 'text-green-600 dark:text-green-400' : ($report['performance_trends']['trend_direction'] === 'declining' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }}">
                        @if ($report['performance_trends']['trend_direction'] === 'improving')
                            <svg class="w-6 h-6 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        @elseif($report['performance_trends']['trend_direction'] === 'declining')
                            <svg class="w-6 h-6 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                            </svg>
                        @endif
                        {{ ucfirst($report['performance_trends']['trend_direction']) }}
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Improvement Rate</p>
                    <p
                        class="text-2xl font-bold {{ $report['performance_trends']['improvement_rate'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $report['performance_trends']['improvement_rate'] >= 0 ? '+' : '' }}{{ $report['performance_trends']['improvement_rate'] }}%
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Average Efficiency</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $report['aggregate_statistics']['avg_efficiency'] }}%
                    </p>
                </div>
            </div>
        </div>

        {{-- Aggregate Statistics --}}
        <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Overall Stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Aggregate Statistics</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($report['aggregate_statistics']['total_training_sessions']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Training Sessions</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $report['aggregate_statistics']['total_races'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Races</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $report['aggregate_statistics']['total_wins'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Race Wins</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $report['aggregate_statistics']['overall_win_rate'] }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Win Rate</p>
                    </div>
                </div>
            </div>

            {{-- Average Stat Gains --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Average Stat Gains Per Career</h2>
                <div class="space-y-3">
                    @php
                        $maxAvgStat = max($report['aggregate_statistics']['avg_stat_gains_per_career']);
                        $statColors = [
                            'speed' => 'bg-blue-500',
                            'stamina' => 'bg-orange-500',
                            'power' => 'bg-red-500',
                            'guts' => 'bg-pink-500',
                            'wit' => 'bg-green-500',
                        ];
                    @endphp
                    @foreach ($report['aggregate_statistics']['avg_stat_gains_per_career'] as $stat => $value)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($stat) }}</span>
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400">+{{ number_format($value, 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="{{ $statColors[$stat] ?? 'bg-gray-500' }} h-2 rounded-full"
                                    style="width: {{ $maxAvgStat > 0 ? ($value / $maxAvgStat) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Career History --}}
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Career History</h2>
            @if (!empty($report['career_history']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Career</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Grade</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Efficiency</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Win Rate</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($report['career_history'] as $career)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $career['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Started
                                            {{ $career['started_at'] ?? 'Unknown' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $career['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                            {{ ucfirst($career['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $career['grade'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                        {{ $career['efficiency'] }}%</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                        {{ $career['win_rate'] }}%</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('reports.career', $career['career_id'] ?? $career['id'] ?? 0) }}"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                                            View Report
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No career history available.</p>
            @endif
        </div>

        {{-- Strengths & Improvement Areas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Strengths --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    <svg class="w-5 h-5 inline mr-2 text-green-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Strengths
                </h2>
                <ul class="space-y-3">
                    @forelse($report['strengths'] as $strength)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $strength }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">Complete more careers to identify strengths.
                        </li>
                    @endforelse
                </ul>
            </div>

            {{-- Improvement Areas --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    <svg class="w-5 h-5 inline mr-2 text-yellow-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Areas for Improvement
                </h2>
                <ul class="space-y-3">
                    @forelse($report['improvement_areas'] as $area)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $area }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">No improvement areas identified yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
