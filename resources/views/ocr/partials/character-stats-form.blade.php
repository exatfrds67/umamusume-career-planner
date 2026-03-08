{{-- Character Stats Extraction Form --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Stats Section -->
    <div class="space-y-4">
        <h3 class="text-md font-semibold text-neutral-900 dark:text-white mb-3">Character Stats</h3>

        @foreach (['speed' => 'Speed', 'stamina' => 'Stamina', 'power' => 'Power', 'guts' => 'Guts', 'wit' => 'Wit'] as $stat => $label)
            <div>
                <label for="stat_{{ $stat }}"
                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    {{ $label }}
                    @if (isset($data['stats'][$stat]) && $data['stats'][$stat] !== null)
                        <span class="text-xs text-neutral-500">(Confidence:
                            {{ number_format(($data['confidence'] ?? 0.5) * 100, 0) }}%)</span>
                    @endif
                </label>
                <input type="number" id="stat_{{ $stat }}" name="stats[{{ $stat }}]"
                    value="{{ $data['stats'][$stat] ?? '' }}" min="0" max="1200"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white dark:border-neutral-600 {{ isset($data['stats'][$stat]) && $data['stats'][$stat] === null ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-neutral-300' }}"
                    placeholder="0-1200" aria-label="{{ $label }} stat value">
                @if (isset($data['stats'][$stat]) && $data['stats'][$stat] === null)
                    <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400"><span aria-hidden="true">⚠️</span> Not extracted - please enter
                        manually</p>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Additional Info Section -->
    <div class="space-y-4">
        <h3 class="text-md font-semibold text-neutral-900 dark:text-white mb-3">Additional Information</h3>

        <!-- Energy Level -->
        <div>
            <label for="energy_level" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Energy Level (%)
            </label>
            <input type="number" id="energy_level" name="energy_level" value="{{ $data['energy_level'] ?? '' }}"
                min="0" max="100"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="0-100" aria-label="Energy level percentage">
        </div>

        <!-- Mood Status -->
        <div>
            <label for="mood_status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Mood Status
            </label>
            <select id="mood_status" name="mood_status"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                aria-label="Character mood status">
                <option value="">-- Select Mood --</option>
                @foreach (['great' => 'Great', 'good' => 'Good', 'normal' => 'Normal', 'bad' => 'Bad', 'awful' => 'Awful'] as $value => $label)
                    <option value="{{ $value }}"
                        {{ ($data['mood_status'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Current Turn -->
        <div>
            <label for="current_turn" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Current Turn
            </label>
            <input type="number" id="current_turn" name="current_turn" value="{{ $data['current_turn'] ?? '' }}"
                min="1" max="78"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="1-78" aria-label="Current turn number">
        </div>

        <!-- Total Turns -->
        <div>
            <label for="total_turns" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Total Turns
            </label>
            <input type="number" id="total_turns" name="total_turns" value="{{ $data['total_turns'] ?? 78 }}"
                min="1" max="78"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="78" aria-label="Total turns in career">
        </div>
    </div>
</div>

@if (isset($data['errors']) && !empty($data['errors']))
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2"><span aria-hidden="true">⚠️</span> Validation Warnings</h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
            @foreach ($data['errors'] as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
