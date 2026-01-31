@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\Character $character */
        /** @var array<string, \Illuminate\Support\Collection<int, \App\Models\Factor>> $factorsByType */
        /** @var array<string, int> $factorCounts */
    @endphp

    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[
        ['label' => 'Characters', 'url' => route('characters.index')],
        ['label' => $character->name, 'url' => route('characters.show', $character)],
        ['label' => 'Manage Factors'],
    ]" />

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header / Back Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('characters.show', $character) }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to {{ $character->name }}
            </a>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Factors</h1>
            </div>
        </div>

        <!-- Factor Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $factorTypeLabels = [
                    '1_star' => 'One Star',
                    '2_star' => 'Two Star',
                    '3_star' => 'Three Star',
                ];
                $factorTypeColors = [
                    '1_star' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                    '2_star' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
                    '3_star' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
                ];
            @endphp
            @foreach ($factorCounts as $starLevel => $count)
                <div class="glass-card-alt rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ $factorTypeLabels[$starLevel] ?? ucfirst(str_replace('_', ' ', $starLevel)) }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $count }}</p>
                        </div>
                        <div class="flex items-center">
                            @for ($i = 1; $i <= 3; $i++)
                                <svg class="w-4 h-4 {{ $i <= (int) str_replace('_star', '', $starLevel) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="glass-card-alt rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Factors</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ array_sum($factorCounts) }}</p>
                    </div>
                    <div class="text-primary-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add New Factor Form -->
            <div class="lg:col-span-1">
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Factor</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('characters.factors.store', $character) }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Factor Type -->
                            <div>
                                <label for="factor_type"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Factor Type
                                </label>
                                <select name="factor_type" id="factor_type" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Type</option>
                                    <option value="blue_stats">Blue (Stat Bonuses)</option>
                                    <option value="red_aptitudes">Red (Aptitude Upgrades)</option>
                                    <option value="green_unique_skills">Green (Unique Skills)</option>
                                    <option value="white_normal_skills">White (Normal Skills)</option>
                                </select>
                                @error('factor_type')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Factor Name -->
                            <div>
                                <label for="factor_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Factor Name
                                </label>
                                <input type="text" name="factor_name" id="factor_name" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter factor name">
                                @error('factor_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Star Level -->
                            <div>
                                <label for="star_level"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Star Level
                                </label>
                                <select name="star_level" id="star_level" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Star Level</option>
                                    <option value="1_star">⭐ One Star</option>
                                    <option value="2_star">⭐⭐ Two Star</option>
                                    <option value="3_star">⭐⭐⭐ Three Star</option>
                                </select>
                                @error('star_level')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Conditional Fields -->
                            <div id="stat_type_field" class="hidden">
                                <label for="stat_type"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Stat Type
                                </label>
                                <select name="stat_type" id="stat_type"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Stat</option>
                                    <option value="speed">Speed</option>
                                    <option value="stamina">Stamina</option>
                                    <option value="power">Power</option>
                                    <option value="guts">Guts</option>
                                    <option value="wit">Wit</option>
                                </select>
                            </div>

                            <div id="aptitude_type_field" class="hidden">
                                <label for="aptitude_type"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Aptitude Type
                                </label>
                                <select name="aptitude_type" id="aptitude_type"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Aptitude</option>
                                    <option value="sprint">Sprint</option>
                                    <option value="mile">Mile</option>
                                    <option value="medium">Medium</option>
                                    <option value="long">Long</option>
                                    <option value="turf">Turf</option>
                                    <option value="dirt">Dirt</option>
                                    <option value="front_runner">Front Runner</option>
                                    <option value="pace_chaser">Pace Chaser</option>
                                    <option value="late_surger">Late Surger</option>
                                    <option value="end_closer">End Closer</option>
                                </select>
                            </div>

                            <div id="unique_skill_field" class="hidden">
                                <label for="unique_skill_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Unique Skill Name
                                </label>
                                <input type="text" name="unique_skill_name" id="unique_skill_name"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter unique skill name">
                            </div>

                            <div id="normal_skill_field" class="hidden">
                                <label for="normal_skill_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Normal Skill Name
                                </label>
                                <input type="text" name="normal_skill_name" id="normal_skill_name"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter normal skill name">
                            </div>

                            <!-- Source Parent -->
                            <div>
                                <label for="source_parent"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Source Parent
                                </label>
                                <select name="source_parent" id="source_parent" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Parent</option>
                                    <option value="main_parent_1">Main Parent 1</option>
                                    <option value="main_parent_2">Main Parent 2</option>
                                    <option value="grandparent_1">Grandparent 1</option>
                                    <option value="grandparent_2">Grandparent 2</option>
                                    <option value="grandparent_3">Grandparent 3</option>
                                    <option value="grandparent_4">Grandparent 4</option>
                                </select>
                                @error('source_parent')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Source Character Name -->
                            <div>
                                <label for="source_character_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Source Character Name (Optional)
                                </label>
                                <input type="text" name="source_character_name" id="source_character_name"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter character name">
                                @error('source_character_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add Factor
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Factors -->
            <div class="lg:col-span-2">
                <div class="glass-card-alt rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Current Factors</h3>
                    </div>
                    <div class="card-body">
                        @if (array_sum($factorCounts) > 0)
                            @php
                                $factorTypeLabels = [
                                    'blue_stats' => 'Stat Bonuses',
                                    'red_aptitudes' => 'Aptitude Upgrades',
                                    'green_unique_skills' => 'Unique Skills',
                                    'white_normal_skills' => 'Normal Skills',
                                ];
                                $factorTypeColors = [
                                    'blue_stats' => 'text-blue-600 dark:text-blue-400',
                                    'red_aptitudes' => 'text-red-600 dark:text-red-400',
                                    'green_unique_skills' => 'text-green-600 dark:text-green-400',
                                    'white_normal_skills' => 'text-gray-600 dark:text-gray-400',
                                ];
                            @endphp

                            <div class="space-y-6">
                                @foreach ($factorsByType as $type => $factors)
                                    @if ($factors->count() > 0)
                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                                <div
                                                    class="w-2 h-2 rounded-full bg-{{ str_replace('_', '-', str_replace('_stats', '', str_replace('_aptitudes', '', str_replace('_unique_skills', '', str_replace('_normal_skills', '', $type))))) }}-500">
                                                </div>
                                                {{ $factorTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                                <span class="text-gray-400">({{ $factors->count() }})</span>
                                            </h4>
                                            <div class="space-y-2">
                                                @foreach ($factors as $factor)
                                                    <div
                                                        class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 {{ !$factor->is_active ? 'opacity-50' : '' }}">
                                                        <div class="flex items-center gap-3">
                                                            <!-- Star Level -->
                                                            <div class="flex items-center">
                                                                @for ($i = 1; $i <= 3; $i++)
                                                                    <svg class="w-3 h-3 {{ $i <= (int) str_replace('_star', '', $factor->star_level) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path
                                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                    </svg>
                                                                @endfor
                                                            </div>

                                                            <!-- Factor Details -->
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <span
                                                                        class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ $factor->factor_name }}
                                                                    </span>
                                                                    @if (!$factor->is_active)
                                                                        <span
                                                                            class="text-xs text-gray-400 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                                                            Inactive
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                @if ($factor->source_character_name)
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                        From {{ ucfirst($factor->source_parent) }}:
                                                                        {{ $factor->source_character_name }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-2">
                                                            <!-- Factor Value -->
                                                            @if ($factor->factor_type === 'blue_stats')
                                                                <span
                                                                    class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                                                    +{{ $factor->getStatBonus() }}
                                                                </span>
                                                            @elseif ($factor->factor_type === 'red_aptitudes')
                                                                <span
                                                                    class="text-xs font-bold text-red-600 dark:text-red-400">
                                                                    +{{ (int) str_replace('_star', '', $factor->star_level) }}
                                                                    grade{{ (int) str_replace('_star', '', $factor->star_level) > 1 ? 's' : '' }}
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="text-xs font-bold {{ $factorTypeColors[$factor->factor_type] ?? 'text-gray-600 dark:text-gray-400' }}">
                                                                    {{ (int) str_replace('_star', '', $factor->star_level) }}★
                                                                </span>
                                                            @endif

                                                            <!-- Actions -->
                                                            <div class="flex items-center gap-1">
                                                                <!-- Toggle Active -->
                                                                <form
                                                                    action="{{ route('characters.factors.toggle', [$character, $factor]) }}"
                                                                    method="POST" class="inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit"
                                                                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                                                        title="{{ $factor->is_active ? 'Deactivate' : 'Activate' }}">
                                                                        @if ($factor->is_active)
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                            </svg>
                                                                        @else
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                                                            </svg>
                                                                        @endif
                                                                    </button>
                                                                </form>

                                                                <!-- Delete -->
                                                                <form
                                                                    action="{{ route('characters.factors.destroy', [$character, $factor]) }}"
                                                                    method="POST" class="inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this factor?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="p-1 text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                                                        title="Delete Factor">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-gray-500 py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No factors yet</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Add your first inherited factor using the
                                    form on the left.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/characters/factors-manage.js'])
@endsection
