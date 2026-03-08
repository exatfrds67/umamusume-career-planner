{{-- Race Result Extraction Form --}}
<div class="space-y-6">
    <!-- Race Basic Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="race_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Race Name
            </label>
            <input type="text" id="race_name" name="race_name" value="{{ $data['race_name'] ?? '' }}"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="e.g., Japan Cup" aria-label="Race name">
        </div>

        <div>
            <label for="race_grade" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Race Grade
            </label>
            <select id="race_grade" name="race_grade"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                aria-label="Race grade">
                <option value="">-- Select Grade --</option>
                @foreach (['G1', 'G2', 'G3', 'OP', 'Pre-OP'] as $grade)
                    <option value="{{ $grade }}" {{ ($data['race_grade'] ?? '') === $grade ? 'selected' : '' }}>
                        {{ $grade }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Race Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="distance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Distance (m)
            </label>
            <input type="number" id="distance" name="distance" value="{{ $data['distance'] ?? '' }}" min="1000"
                max="3600"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="1000-3600" aria-label="Race distance in meters">
        </div>

        <div>
            <label for="surface" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Surface
            </label>
            <select id="surface" name="surface"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                aria-label="Race surface">
                <option value="">-- Select Surface --</option>
                @foreach (['turf' => 'Turf', 'dirt' => 'Dirt'] as $value => $label)
                    <option value="{{ $value }}" {{ ($data['surface'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="distance_category" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Distance Category
            </label>
            <select id="distance_category" name="distance_category"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                aria-label="Distance category">
                <option value="">-- Select Category --</option>
                @foreach (['sprint' => 'Sprint', 'mile' => 'Mile', 'medium' => 'Medium', 'long' => 'Long'] as $value => $label)
                    <option value="{{ $value }}"
                        {{ ($data['distance_category'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Race Result -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="position" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Final Position
            </label>
            <input type="number" id="position" name="position" value="{{ $data['position'] ?? '' }}" min="1"
                max="18"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="1-18" aria-label="Final race position">
        </div>

        <div>
            <label for="outcome" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Outcome
            </label>
            <select id="outcome" name="outcome"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                aria-label="Race outcome">
                <option value="">-- Select Outcome --</option>
                @foreach (['victory' => 'Victory (1st)', 'podium' => 'Podium (2nd-3rd)', 'top_5' => 'Top 5', 'defeat' => 'Defeat'] as $value => $label)
                    <option value="{{ $value }}" {{ ($data['outcome'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Rewards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="fans_gained" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Fans Gained
            </label>
            <input type="number" id="fans_gained" name="fans_gained" value="{{ $data['fans_gained'] ?? '' }}"
                min="0" max="999999"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="0" aria-label="Fans gained from race">
        </div>

        <div>
            <label for="skill_points_gained" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Skill Points Gained
            </label>
            <input type="number" id="skill_points_gained" name="skill_points_gained"
                value="{{ $data['skill_points_gained'] ?? '' }}" min="0" max="9999"
                class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
                placeholder="0" aria-label="Skill points gained from race">
        </div>
    </div>

    <!-- Race Date -->
    <div>
        <label for="race_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Race Date (Optional)
        </label>
        <input type="date" id="race_date" name="race_date" value="{{ $data['race_date'] ?? '' }}"
            class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-white"
            aria-label="Race date">
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
