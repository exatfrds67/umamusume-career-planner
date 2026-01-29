<div class="border-l-4 rounded-r-lg {{ $bgColor() }} p-4 flex items-start justify-between gap-4" role="alert">
    <!-- Content -->
    <div class="flex gap-4 flex-1">
        <!-- Icon -->
        <div class="text-2xl shrink-0">
            {{ $getIcon() }}
        </div>

        <!-- Text Content -->
        <div class="flex-1 {{ $textColor() }}">
            @if($title)
                <h3 class="font-bold {{ $accentColor() }} mb-1">
                    {{ $title }}
                </h3>
            @endif
            @if($message)
                <p class="text-sm opacity-90">
                    {{ $message }}
                </p>
            @endif
        </div>
    </div>

    <!-- Close Button -->
    @if($dismissible)
        <button type="button"
            class="shrink-0 p-1 hover:opacity-75 transition-opacity"
            @click="$el.closest('[role=\"alert\"]').remove()"
            aria-label="Close notification">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>