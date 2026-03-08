@extends('layouts.app')

@section('title', 'Print Report - ' . ($career->character?->name ?? 'Unknown'))

@section('content')
    <div class="container mx-auto px-4 py-8 print:p-0">
        {{-- Print Controls (hidden when printing) --}}
        <div class="mb-6 flex items-center justify-between print:hidden">
            <a href="{{ route('reports.career', $career) }}"
                class="inline-flex items-center text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Report
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Save as PDF
            </button>
        </div>

        {{-- Report Content --}}
        <div
            class="bg-white dark:bg-neutral-800 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700 p-8 print:shadow-none print:border-0 print:p-0">
            {{-- Header --}}
            <div class="text-center mb-8 pb-6 border-b border-neutral-200 dark:border-neutral-700">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $pdfData['title'] }}</h1>
                <p class="text-xl text-neutral-600 dark:text-neutral-400 mt-2">{{ $pdfData['subtitle'] }}</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-500 mt-2">
                    Generated: {{ \Carbon\Carbon::parse($pdfData['generated_at'])->format('F j, Y \a\t g:i A') }}
                </p>
            </div>

            {{-- Sections --}}
            @foreach ($pdfData['sections'] as $section)
                <div class="mb-8 print:break-inside-avoid">
                    <h2
                        class="text-xl font-semibold text-neutral-900 dark:text-white mb-4 pb-2 border-b border-neutral-200 dark:border-neutral-700">
                        {{ $section['title'] }}
                    </h2>

                    @if (is_array($section['content']))
                        @if (isset($section['content'][0]) && is_string($section['content'][0]))
                            {{-- List of strings (insights, recommendations) --}}
                            <ul class="space-y-2">
                                @foreach ($section['content'] as $item)
                                    <li class="flex items-start gap-2">
                                        <span class="text-primary-600 dark:text-primary-400">•</span>
                                        <span class="text-neutral-700 dark:text-neutral-300">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @elseif(isset($section['content'][0]) && is_array($section['content'][0]))
                            {{-- Array of arrays (stat distribution) --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($section['content'] as $item)
                                    @foreach ($item as $key => $value)
                                        <div class="p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $key }}</p>
                                            <p class="text-lg font-semibold text-neutral-900 dark:text-white">
                                                {{ $value }}</p>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            {{-- Key-value pairs --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($section['content'] as $key => $value)
                                    <div
                                        class="flex justify-between items-center p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                        <span class="text-neutral-600 dark:text-neutral-400">{{ $key }}</span>
                                        @if (is_array($value))
                                            <div class="text-right">
                                                @foreach ($value as $item)
                                                    <span
                                                        class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full mr-1 mb-1">
                                                        {{ $item }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span
                                                class="font-semibold text-neutral-900 dark:text-white">{{ $value }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="text-neutral-700 dark:text-neutral-300">{{ $section['content'] }}</p>
                    @endif
                </div>
            @endforeach

            {{-- Footer --}}
            <div
                class="mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-700 text-center text-sm text-neutral-500 dark:text-neutral-500">
                <p>Umamusume Career Planner - Career Report</p>
                <p>This report was automatically generated based on career performance data.</p>
            </div>
        </div>
    </div>

@endsection
