{{-- Export Tab Content --}}
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Export Data</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Export your career data in JSON, CSV, or PDF formats.</p>
        </div>
        <a href="{{ route('export.index') }}"
            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            Open Full Export Page
        </a>
    </div>

    {{-- Export Types Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        @foreach ($exportTypes as $type => $label)
            <a href="{{ route('export.index') }}?type={{ $type }}"
                class="p-4 rounded-lg border-2 border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors text-center">
                <div class="flex justify-center mb-2" aria-hidden="true">
                    @switch($type)
                        @case('character')
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @break
                        @case('career')
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        @break
                        @case('training_session')
                            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @break
                        @case('skill')
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        @break
                        @case('support_card')
                            <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        @break
                        @case('full_backup')
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        @break
                    @endswitch
                </div>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</span>
            </a>
        @endforeach
    </div>

    {{-- Export Formats --}}
    <div class="bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
        <h4 class="font-medium text-neutral-900 dark:text-white mb-3">Export Formats</h4>
        <div class="grid grid-cols-3 gap-4">
            @foreach ($exportFormats as $format)
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 flex items-center justify-center bg-neutral-100 dark:bg-neutral-600 rounded" aria-hidden="true">
                        @switch($format)
                            @case('json')
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @break
                            @case('csv')
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @break
                            @case('pdf')
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @break
                        @endswitch
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900 dark:text-white uppercase">{{ $format }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            @switch($format)
                                @case('json')
                                    Structured data
                                @break
                                @case('csv')
                                    Spreadsheet compatible
                                @break
                                @case('pdf')
                                    Printable report
                                @break
                            @endswitch
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Export Templates --}}
    <div>
        <h4 class="font-medium text-neutral-900 dark:text-white mb-3">Quick Export Templates</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach (array_slice($exportTemplates, 0, 3) as $key => $template)
                <a href="{{ route('export.index') }}?template={{ $key }}"
                    class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-primary-500 dark:hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors block">
                    <h5 class="font-medium text-neutral-900 dark:text-white mb-1">{{ $template['name'] }}</h5>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $template['description'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</div>
