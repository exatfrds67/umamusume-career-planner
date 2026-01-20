{{-- Export Tab Content --}}
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Export Data</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Export your career data in JSON, CSV, or PDF formats.</p>
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
                class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-center">
                <div class="text-2xl mb-2">
                    @switch($type)
                        @case('character')
                            👤
                        @break

                        @case('career')
                            📊
                        @break

                        @case('training_session')
                            🏃
                        @break

                        @case('skill')
                            ⚡
                        @break

                        @case('support_card')
                            🃏
                        @break

                        @case('full_backup')
                            💾
                        @break
                    @endswitch
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
            </a>
        @endforeach
    </div>

    {{-- Export Formats --}}
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Export Formats</h4>
        <div class="grid grid-cols-3 gap-4">
            @foreach ($exportFormats as $format)
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-600 rounded">
                        @switch($format)
                            @case('json')
                                📄
                            @break

                            @case('csv')
                                📊
                            @break

                            @case('pdf')
                                📑
                            @break
                        @endswitch
                    </span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white uppercase">{{ $format }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
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
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Quick Export Templates</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach (array_slice($exportTemplates, 0, 3) as $key => $template)
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors cursor-pointer"
                    onclick="window.location.href='{{ route('export.index') }}?template={{ $key }}'">
                    <h5 class="font-medium text-gray-900 dark:text-white mb-1">{{ $template['name'] }}</h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $template['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
