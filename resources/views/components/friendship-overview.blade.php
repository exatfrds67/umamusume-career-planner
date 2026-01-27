@props(['overview'])

<div class="card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Friendship Levels</h2>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Avg: <span
                class="font-medium text-gray-900 dark:text-white">{{ number_format($overview['average_friendship'], 1) }}%</span>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($overview['cards'] as $card)
            <div
                class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 {{ $card['is_rainbow_available'] ? 'bg-linear-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-200 dark:border-purple-700' : 'bg-gray-50 dark:bg-gray-900/20' }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $card['card_name'] }}
                        </span>
                        @if ($card['is_rainbow_available'])
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-linear-to-r from-purple-100 to-pink-100 text-purple-800 dark:from-purple-900 dark:to-pink-900 dark:text-purple-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                Rainbow
                            </span>
                        @endif
                    </div>
                    <span
                        class="text-sm font-semibold {{ $card['is_rainbow_available'] ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400' }}">
                        {{ $card['friendship_level'] }}%
                    </span>
                </div>

                <!-- Progress Bar -->
                <div class="relative w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="absolute inset-y-0 left-0 {{ $card['is_rainbow_available'] ? 'bg-linear-to-r from-purple-500 to-pink-500' : 'bg-blue-500' }} rounded-full transition-all duration-300"
                        style="width: {{ $card['friendship_level'] }}%"></div>

                    <!-- Rainbow threshold marker -->
                    <div class="absolute inset-y-0 left-[80%] w-0.5 bg-purple-600 dark:bg-purple-400"></div>
                </div>

                <div class="mt-1 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $card['card_type'] }}</span>
                    @if (!$card['is_rainbow_available'])
                        <span>{{ 80 - $card['friendship_level'] }}% to Rainbow</span>
                    @else
                        <span class="text-purple-600 dark:text-purple-400 font-medium">Rainbow Training
                            Available!</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Rainbow Training Info -->
    @if ($overview['rainbow_available_count'] > 0)
        <div
            class="mt-4 p-3 rounded-lg bg-linear-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-700">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-purple-900 dark:text-purple-100">
                        {{ $overview['rainbow_available_count'] }}
                        card{{ $overview['rainbow_available_count'] > 1 ? 's' : '' }} ready for Rainbow Training!
                    </p>
                    <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">
                        Rainbow Training provides enhanced stat bonuses when 2+ cards with 80%+ friendship participate
                        in training.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
