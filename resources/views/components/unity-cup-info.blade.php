@props(['data'])

<div
    class="mb-4 p-3 glass-card-inner rounded-lg border-2 border-purple-200 dark:border-purple-800 transition-colors duration-300">
    <h4 class="text-sm font-semibold text-purple-700 dark:text-purple-300 mb-2 transition-colors duration-300">
        🏆 Unity Cup Mechanics
    </h4>
    <div class="space-y-2 text-xs">
        @if (isset($data['spirit_burst_progress']))
            <div class="flex justify-between items-center">
                <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Spirit Burst
                    Progress</span>
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 4; $i++)
                        <span
                            class="{{ $i <= ($data['spirit_burst_progress'] ?? 0) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }}"
                            aria-hidden="true">
                            {{ $i <= ($data['spirit_burst_progress'] ?? 0) ? '🔥' : '○' }}
                        </span>
                    @endfor
                </div>
            </div>
        @endif

        @if (isset($data['team_synergy_bonus']))
            <div class="flex justify-between">
                <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Team Synergy
                    Bonus</span>
                <span class="font-medium text-purple-900 dark:text-purple-100 transition-colors duration-300">
                    +{{ $data['team_synergy_bonus'] }}%
                </span>
            </div>
        @endif

        @if (isset($data['teammates_present']))
            <div class="flex justify-between">
                <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Teammates
                    Present</span>
                <span class="font-medium text-purple-900 dark:text-purple-100 transition-colors duration-300">
                    {{ $data['teammates_present'] }}
                </span>
            </div>
        @endif

        @if (isset($data['facility_level']))
            <div class="flex justify-between">
                <span class="text-purple-700 dark:text-purple-300 transition-colors duration-300">Facility Level</span>
                <span class="font-medium text-purple-900 dark:text-purple-100 transition-colors duration-300">
                    Lv. {{ $data['facility_level'] }}
                </span>
            </div>
        @endif
    </div>
</div>
