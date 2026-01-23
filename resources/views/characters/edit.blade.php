@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\Character $character */
    @endphp
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Edit {{ $character->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update character stats, goals, and tracking information
                </p>
            </div>
            <a href="{{ route('characters.show', $character) }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('characters.update', $character) }}" class="space-y-6"
            id="character-form">
            @csrf
            @method('PUT')

            <!-- Row 1: Basic Info (Left) + Stats (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information & Status -->
                <div class="glass-card rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Basic Information &
                            Status</h3>
                    </div>
                    <div class="card-body grid grid-cols-1 gap-4">
                        <div>
                            <label for="name" class="form-label">Character Name</label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', $character->name) }}" required maxlength="100" class="form-input">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="career_stage" class="form-label">Career Stage</label>
                                <select id="career_stage" name="career_stage" class="form-select">
                                    <option value="junior"
                                        {{ old('career_stage', $character->career_stage) === 'junior' ? 'selected' : '' }}>
                                        Junior</option>
                                    <option value="classic"
                                        {{ old('career_stage', $character->career_stage) === 'classic' ? 'selected' : '' }}>
                                        Classic</option>
                                    <option value="senior"
                                        {{ old('career_stage', $character->career_stage) === 'senior' ? 'selected' : '' }}>
                                        Senior</option>
                                </select>
                            </div>

                            <div>
                                <label for="current_turn" class="form-label">Turn (1-78)</label>
                                <input type="number" id="current_turn" name="current_turn"
                                    value="{{ old('current_turn', $character->current_turn) }}" min="1"
                                    max="78" class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="energy_level" class="form-label">Energy (%)</label>
                                <input type="number" id="energy_level" name="energy_level"
                                    value="{{ old('energy_level', $character->energy_level) }}" min="0"
                                    max="100" class="form-input">
                            </div>

                            <div>
                                <label for="mood_status" class="form-label">Mood</label>
                                <select id="mood_status" name="mood_status" class="form-select">
                                    <option value="awful"
                                        {{ old('mood_status', $character->mood_status) === 'awful' ? 'selected' : '' }}>
                                        Awful (-20%)</option>
                                    <option value="bad"
                                        {{ old('mood_status', $character->mood_status) === 'bad' ? 'selected' : '' }}>
                                        Bad (-10%)</option>
                                    <option value="normal"
                                        {{ old('mood_status', $character->mood_status) === 'normal' ? 'selected' : '' }}>
                                        Normal</option>
                                    <option value="good"
                                        {{ old('mood_status', $character->mood_status) === 'good' ? 'selected' : '' }}>
                                        Good (+10%)</option>
                                    <option value="great"
                                        {{ old('mood_status', $character->mood_status) === 'great' ? 'selected' : '' }}>
                                        Great (+20%)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Stats -->
                <div class="glass-card rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Current Stats</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Values between 0-1200</p>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                <div>
                                    <label for="stat_{{ $stat }}" class="form-label capitalize text-xs">
                                        {{ $stat }}
                                        <span class="text-xs text-gray-500 ml-1 font-bold">
                                            {{ $character->getStatGrade($character->current_stats[$stat] ?? 0) }}
                                        </span>
                                    </label>
                                    <input type="number" id="stat_{{ $stat }}"
                                        name="stats[{{ $stat }}]"
                                        value="{{ old('stats.' . $stat, $character->current_stats[$stat] ?? 0) }}"
                                        min="0" max="1200" step="1" class="form-input stat-input"
                                        data-stat="{{ $stat }}" oninput="enforceStatMax(this)">
                                    <p id="stat_{{ $stat }}_error" class="text-xs text-red-500 hidden mt-1">Max
                                        1200</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Goals (Full Width) -->
            <div class="glass-card-alt rounded-lg">
                <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Training Goals</h3>
                </div>
                <div class="card-body space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
                        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                            <div>
                                <label for="goal_{{ $stat }}" class="form-label capitalize text-xs">Target
                                    {{ $stat }}</label>
                                <input type="number" id="goal_{{ $stat }}"
                                    name="goals[target_stats][{{ $stat }}]"
                                    value="{{ old('goals.target_stats.' . $stat, $character->goals['target_stats'][$stat] ?? '') }}"
                                    min="0" max="1200" step="1" placeholder="Optional"
                                    class="form-input stat-input" oninput="enforceStatMax(this)">
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="goals[notes]" rows="4" class="form-input"
                            placeholder="Training strategy notes...">{{ old('goals.notes', $character->goals['notes'] ?? '') }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-t border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                    <button type="button"
                        onclick="if(confirm('Delete character? This cannot be undone.')) document.getElementById('delete-form').submit()"
                        class="text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none">
                        Delete Character
                    </button>
                    <div class="flex gap-4">
                        <a href="{{ route('characters.show', $character) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <form id="delete-form" method="POST" action="{{ route('characters.destroy', $character) }}"
            class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script>
        function enforceStatMax(input) {
            const max = 1200;
            const errorId = input.id.includes('goal_') ? null : input.id + '_error';
            const errorEl = errorId ? document.getElementById(errorId) : null;

            if (parseInt(input.value) > max) {
                input.value = max;
                if (errorEl) {
                    errorEl.classList.remove('hidden');
                    setTimeout(() => errorEl.classList.add('hidden'), 2000);
                }
            }

            if (parseInt(input.value) < 0) {
                input.value = 0;
            }
        }

        document.getElementById('character-form').addEventListener('submit', function(e) {
            const statInputs = document.querySelectorAll('.stat-input');
            let hasError = false;

            statInputs.forEach(input => {
                if (parseInt(input.value) > 1200) {
                    input.value = 1200;
                    hasError = true;
                }
            });

            if (hasError) {
                // Just notify, form still submits with corrected values
                // alert('Stats capped to 1200');
            }
        });
    </script>
@endsection
