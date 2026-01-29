{{--
Component: Pagination
Purpose: Pagination controls for multi-page lists
Props:
  - currentPage (int, required): Current page number
  - totalPages (int, required): Total number of pages
  - baseUrl (string, required): URL pattern with {page} placeholder
  - onPageChange (callback, optional): Alpine event for page changes
Usage:
  <x-pagination :current-page="1" :total-pages="5" base-url="/characters?page={page}" />
Accessibility: WCAG 2.2 AA compliant, aria-current="page"
--}}

@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'baseUrl' => '#',
    'onPageChange' => null,
])

@php
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    $pagesToShow = [];
    
    if ($startPage > 1) {
        $pagesToShow[] = 1;
        if ($startPage > 2) {
            $pagesToShow[] = '...';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pagesToShow[] = $i;
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $pagesToShow[] = '...';
        }
        $pagesToShow[] = $totalPages;
    }
@endphp

@if($totalPages > 1)
    <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-0" aria-label="Pagination">
        {{-- Previous Button --}}
        <div class="w-0 flex-1">
            @if($currentPage > 1)
                <a
                    href="{{ str_replace('{page}', $currentPage - 1, $baseUrl) }}"
                    @if($onPageChange) @click="{{ str_replace('{page}', $currentPage - 1, $onPageChange) }}" @endif
                    class="relative inline-flex items-center gap-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Previous</span>
                </a>
            @endif
        </div>

        {{-- Page Numbers --}}
        <div class="hidden sm:flex items-center gap-1">
            @foreach($pagesToShow as $page)
                @if($page === '...')
                    <span class="text-gray-700 dark:text-gray-400 px-2">•••</span>
                @else
                    @php
                        $isCurrent = (int)$page === (int)$currentPage;
                        $pageUrl = str_replace('{page}', $page, $baseUrl);
                    @endphp
                    <a
                        href="{{ $pageUrl }}"
                        @if($onPageChange && !$isCurrent) @click="{{ str_replace('{page}', $page, $onPageChange) }}" @endif
                        @if($isCurrent) aria-current="page" @endif
                        class="@if($isCurrent)
                            relative z-10 inline-flex items-center border border-primary-500 bg-primary-50 dark:bg-primary-900/20 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400
                        @else
                            relative inline-flex items-center border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700
                        @endif rounded-lg transition-colors"
                    >
                        {{ $page }}
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Next Button --}}
        <div class="flex w-0 flex-1 justify-end">
            @if($currentPage < $totalPages)
                <a
                    href="{{ str_replace('{page}', $currentPage + 1, $baseUrl) }}"
                    @if($onPageChange) @click="{{ str_replace('{page}', $currentPage + 1, $onPageChange) }}" @endif
                    class="relative inline-flex items-center gap-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    <span>Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif
        </div>
    </nav>

    {{-- Mobile Page Indicator --}}
    <div class="sm:hidden px-4 py-2 text-center text-sm text-gray-700 dark:text-gray-300">
        Page {{ $currentPage }} of {{ $totalPages }}
    </div>
@endif
