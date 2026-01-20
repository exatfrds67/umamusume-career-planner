{{-- Training Session Extraction Form --}}
<div class="space-y-6">
    <!-- Training Type -->
    <div>
        <label for="training_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Training Type
        </label>
        <select id="training_type" name="training_type"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            aria-label="Training type">
            <option value="">-- Select Training Type --</option>
            @foreach (['speed' => 'Speed', 'stamina' => 'Stamina', 'power' => 'Power', 'guts' => 'Guts', 'wit' => 'Wit', 'rest' => 'Rest', 'infirmary' => 'Infirmary', 'outing' => 'Outing'] as $value => $label)
                <option value="{{ $value }}" {{ ($data['training_type'] ?? '') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Stat Gains -->
    <div>
        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Stat Gains</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach (['speed' => 'Speed', 'stamina' => 'Stamina', 'power' => 'Power', 'guts' => 'Guts', 'wit' => 'Wit'] as $stat => $label)
                <div>
                    <label for="stat_gain_{{ $stat }}"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $label }}
                    </label>
                    <input type="number" id="stat_gain_{{ $stat }}" name="stat_gains[{{ $stat }}]"
                        value="{{ $data['stat_gains'][$stat] ?? 0 }}" min="0" max="200"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        placeholder="0" aria-label="{{ $label }} stat gain">
                </div>
            @endforeach
        </div>
    </div>

    <!-- Energy Cost -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="energy_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Energy Cost (%)
            </label>
            <input type="number" id="energy_cost" name="energy_cost" value="{{ $data['energy_cost'] ?? '' }}"
                min="0" max="100"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="0-100" aria-label="Energy cost percentage">
        </div>

        <div>
            <label for="turn_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Turn Number
            </label>
            <input type="number" id="turn_number" name="turn_number" value="{{ $data['turn_number'] ?? '' }}"
                min="1" max="78"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="1-78" aria-label="Turn number">
        </div>
    </div>

    <!-- Training Flags -->
    <div>
        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Training Features</h3>
        <div class="space-y-2">
            <label class="flex items-center">
                <input type="checkbox" name="has_skill_hint" value="1"
                    {{ $data['has_skill_hint'] ?? false ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has Skill Hint (Red !)</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="has_friendship" value="1"
                    {{ $data['has_friendship'] ?? false ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Friendship Training (Rainbow)</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="has_spirit_burst" value="1"
                    {{ $data['has_spirit_burst'] ?? false ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Spirit Burst Available (Unity Cup)</span>
            </label>
        </div>
    </div>

    <!-- Participants -->
    <div>
        <label for="participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Participants (comma-separated support card names)
        </label>
        <input type="text" id="participants" name="participants"
            value="{{ is_array($data['participants'] ?? null) ? implode(', ', $data['participants']) : $data['participants'] ?? '' }}"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="e.g., Kitasan Black, Narita Brian" aria-label="Training participants">
    </div>
</div>

@if (isset($data['errors']) && !empty($data['errors']))
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Validation Warnings</h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
            @foreach ($data['errors'] as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
