{{-- Skill List Extraction Form --}}
<div class="space-y-6">
    <!-- Total SP -->
    <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <label for="total_sp" class="block text-sm font-medium text-primary-900 dark:text-primary-100 mb-1">
                    Total Skill Points (SP)
                </label>
                <input type="number" id="total_sp" name="total_sp" value="{{ $data['total_sp'] ?? '' }}" min="0"
                    max="99999"
                    class="w-48 px-4 py-2 border border-primary-300 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-primary-900/50 dark:text-white"
                    placeholder="0" aria-label="Total skill points">
            </div>
            <div class="text-right">
                <p class="text-sm text-primary-700 dark:text-primary-300">Skills Extracted</p>
                <p class="text-2xl font-bold text-primary-900 dark:text-primary-100">
                    {{ count($data['skills'] ?? []) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Skills List -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-md font-semibold text-gray-900 dark:text-white">Extracted Skills</h3>
            <button type="button" id="add-skill-button"
                class="px-3 py-1 text-sm bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                + Add Skill
            </button>
        </div>

        <div id="skills-container" class="space-y-3">
            @forelse($data['skills'] ?? [] as $index => $skill)
                <div
                    class="skill-item bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <!-- Skill Name -->
                        <div class="md:col-span-5">
                            <label for="skill_name_{{ $index }}"
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Skill Name
                            </label>
                            <input type="text" id="skill_name_{{ $index }}"
                                name="skills[{{ $index }}][name]" value="{{ $skill['name'] ?? '' }}"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                                placeholder="Skill name" aria-label="Skill name">
                        </div>

                        <!-- SP Cost -->
                        <div class="md:col-span-2">
                            <label for="skill_sp_{{ $index }}"
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SP Cost
                            </label>
                            <input type="number" id="skill_sp_{{ $index }}"
                                name="skills[{{ $index }}][sp_cost]" value="{{ $skill['sp_cost'] ?? '' }}"
                                min="0" max="500"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                                placeholder="0" aria-label="Skill SP cost">
                        </div>

                        <!-- Hint Level -->
                        <div class="md:col-span-2">
                            <label for="skill_hint_{{ $index }}"
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Hint Level
                            </label>
                            <input type="number" id="skill_hint_{{ $index }}"
                                name="skills[{{ $index }}][hint_level]" value="{{ $skill['hint_level'] ?? 0 }}"
                                min="0" max="5"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                                placeholder="0" aria-label="Skill hint level">
                        </div>

                        <!-- Acquired -->
                        <div class="md:col-span-2 flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" name="skills[{{ $index }}][is_acquired]" value="1"
                                    {{ $skill['is_acquired'] ?? false ? 'checked' : '' }}
                                    class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Acquired</span>
                            </label>
                        </div>

                        <!-- Remove Button -->
                        <div class="md:col-span-1 flex items-end">
                            <button type="button"
                                class="remove-skill-button w-full px-2 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg focus:outline-none transition-colors"
                                aria-label="Remove skill">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>No skills extracted. Click "Add Skill" to add manually.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Data injection for skill list form --}}
<script id="skill-list-form-data" type="application/json">
    {!! json_encode(['initialSkillCount' => count($data['skills'] ?? [])]) !!}
</script>
@vite(['resources/js/pages/ocr/partials/skill-list-form.js'])

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
