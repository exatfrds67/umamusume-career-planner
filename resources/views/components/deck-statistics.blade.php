@props(['statistics'])

<div class="card bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-xs border border-neutral-200 dark:border-neutral-700">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Deck Statistics</h3>

    <dl class="space-y-3">
        <!-- Total Cards -->
        <div class="flex justify-between items-center text-sm">
            <dt class="text-neutral-500 dark:text-neutral-400">Total Cards</dt>
            <dd class="font-medium text-neutral-900 dark:text-white">
                {{ $statistics['total_cards'] }}/6
            </dd>
        </div>

        <!-- Owned vs Friend -->
        <div class="flex justify-between items-center text-sm">
            <dt class="text-neutral-500 dark:text-neutral-400">Owned Cards</dt>
            <dd class="font-medium text-neutral-900 dark:text-white">
                {{ $statistics['owned_cards'] }}/5
            </dd>
        </div>

        <div class="flex justify-between items-center text-sm">
            <dt class="text-neutral-500 dark:text-neutral-400">Friend Cards</dt>
            <dd class="font-medium text-neutral-900 dark:text-white">
                {{ $statistics['friend_cards'] }}/1
            </dd>
        </div>

        <!-- Average Limit Break -->
        <div class="flex justify-between items-center text-sm">
            <dt class="text-neutral-500 dark:text-neutral-400">Avg Limit Break</dt>
            <dd class="font-medium text-neutral-900 dark:text-white">
                {{ number_format($statistics['average_limit_break'], 1) }}/4
            </dd>
        </div>

        <!-- Average Friendship -->
        <div class="flex justify-between items-center text-sm">
            <dt class="text-neutral-500 dark:text-neutral-400">Avg Friendship</dt>
            <dd class="font-medium text-neutral-900 dark:text-white">
                {{ number_format($statistics['average_friendship'], 1) }}%
            </dd>
        </div>
    </dl>

    <!-- Card Type Distribution -->
    @if (!empty($statistics['card_types']))
        <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
            <h4 class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Card Type Distribution</h4>
            <div class="space-y-2">
                @foreach ($statistics['card_types'] as $type => $count)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-neutral-600 dark:text-neutral-400 capitalize">{{ $type }}</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ $count }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $type === 'speed' ? 'bg-red-500' : ($type === 'stamina' ? 'bg-green-500' : ($type === 'power' ? 'bg-yellow-500' : ($type === 'guts' ? 'bg-orange-500' : ($type === 'wit' ? 'bg-blue-500' : 'bg-purple-500')))) }} rounded-full transition-all duration-300"
                                style="width: {{ ($count / $statistics['total_cards']) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Rarity Distribution -->
    @if (!empty($statistics['rarity_distribution']))
        <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
            <h4 class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Rarity Distribution</h4>
            <div class="flex items-center gap-2">
                @foreach ($statistics['rarity_distribution'] as $rarity => $count)
                    <div class="flex items-center gap-1">
                        <x-support-card-rarity-badge :rarity="$rarity" size="xs" />
                        <span class="text-xs font-medium text-neutral-900 dark:text-white">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
