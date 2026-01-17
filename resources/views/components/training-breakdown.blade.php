@props(['breakdown'])

<div class="mb-4 glass-card-inner rounded-lg p-3">
    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-300">Calculation
        Breakdown</h4>
    <div class="space-y-1 text-xs">
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Support Card Bonus</span>
            <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">
                +{{ number_format($breakdown['support_card_bonus'] ?? 0, 1) }}%
            </span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Friendship Multiplier</span>
            <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">
                ×{{ number_format($breakdown['friendship_multiplier'] ?? 1, 2) }}
            </span>
        </div>
        @if (isset($breakdown['facility_bonus']))
            <div class="flex justify-between">
                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Facility Bonus</span>
                <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">
                    +{{ number_format($breakdown['facility_bonus'], 1) }}%
                </span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Growth Rate Bonus</span>
            <span class="font-medium text-gray-900 dark:text-white transition-colors duration-300">
                +{{ number_format($breakdown['growth_rate_bonus'] ?? 0, 1) }}%
            </span>
        </div>
        <div class="flex justify-between pt-1 border-t border-gray-300 dark:border-gray-600">
            <span class="text-gray-700 dark:text-gray-300 font-semibold transition-colors duration-300">Total
                Multiplier</span>
            <span class="font-bold text-gray-900 dark:text-white transition-colors duration-300">
                ×{{ number_format($breakdown['total_multiplier'] ?? 1, 2) }}
            </span>
        </div>
    </div>
</div>
