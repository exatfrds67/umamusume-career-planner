@php
    $storageMode = auth()->check() ? 'Account' : 'Local';
    $nextRace = $upcomingRaces[0] ?? null;
    $nextRaceName = is_array($nextRace) ? ($nextRace['name'] ?? null) : null;
    $lowestStatKey = collect($stats)->sort()->keys()->first() ?? 'speed';
    $lowestStatLabel = ucfirst($lowestStatKey);
    $lowestStatValue = (int) ($stats[$lowestStatKey] ?? 0);
    $selectedMood = strtolower(str_replace(' ', '_', (string) ($selectedCharacter?->mood_status ?? 'normal')));
    $lastTip = $selectedCharacter
        ? 'Focus on '.$lowestStatLabel.' training next to keep the run on pace for '.($nextRaceName ?? 'the next objective').'.'
        : 'Select a character to reveal the next best training action.';
    $tipReasoning = $selectedCharacter
        ? 'The weakest stat is '.$lowestStatLabel.' at '.$lowestStatValue.'. Prioritizing it now gives the best immediate return.'
        : null;
    $shortTermGoal = $goals[0]['label'] ?? 'No short-term goal set';
    $shortTermProgress = (int) ($goals[0]['progress'] ?? 0);
    $longTermGoal = $goals[1]['label'] ?? ($goals[0]['label'] ?? 'No long-term goal set');
    $longTermProgress = (int) ($goals[1]['progress'] ?? ($goals[0]['progress'] ?? 0));

    $dashboardActions = [
        [
            'title' => 'Training',
            'eyebrow' => 'Next turn',
            'symbol' => '⚡',
            'description' => ($trainingSuggestions[0]['action'] ?? 'Train the lowest stat').' · '.($trainingSuggestions[0]['gains'] ?? 'Review the best value action'),
            'hint' => $trainingSuggestions[0]['risk'] ?? 'Low risk',
            'href' => route('training.predictions'),
            'variant' => 'primary',
        ],
        [
            'title' => 'Skills',
            'eyebrow' => 'SP spend',
            'symbol' => '✨',
            'description' => 'Review new skills and spend your current SP budget wisely.',
            'hint' => number_format((int) ($selectedCharacter?->available_sp ?? 0)).' SP available',
            'href' => route('skills.index'),
            'variant' => 'secondary',
        ],
        [
            'title' => 'Races',
            'eyebrow' => 'Race plan',
            'symbol' => '🏆',
            'description' => $nextRaceName ? 'Preview '.$nextRaceName.' and readiness details.' : 'Open the race calendar and plan the next entry.',
            'hint' => $nextRace ? ($nextRace['grade'] ?? 'Race ready') : 'Calendar view',
            'href' => route('races.index'),
            'variant' => 'warning',
        ],
        [
            'title' => 'Support Deck',
            'eyebrow' => 'Setup',
            'symbol' => '🎒',
            'description' => 'Check the active support deck and make the next adjustment.',
            'hint' => $selectedCharacter ? 'Character deck' : 'Deck builder',
            'href' => $selectedCharacter ? route('characters.deck-builder', $selectedCharacter) : route('support-cards.index'),
            'variant' => 'neutral',
        ],
        [
            'title' => 'AI Advisor',
            'eyebrow' => 'Guidance',
            'symbol' => '🤖',
            'description' => 'Open guided chat for training, race, and skill decisions.',
            'hint' => 'Bedrock-ready',
            'href' => route('ai.chat'),
            'variant' => 'success',
        ],
        [
            'title' => 'Reports',
            'eyebrow' => 'Review',
            'symbol' => '📊',
            'description' => 'Open reports and track how the run is trending over time.',
            'hint' => 'Career history',
            'href' => route('reports.index'),
            'variant' => 'primary',
        ],
    ];
@endphp

<div class="space-y-8 pb-8">
    {{-- Accessibility / test hooks for dashboard zones --}}
    <div class="sr-only" aria-hidden="false">
        Primary Decision Zone
        Secondary Context Zone
        Tertiary Insights Zone
    </div>
    <section class="relative overflow-hidden rounded-[24px] bg-linear-to-br from-[#150D35] via-[#24124D] to-[#0A0620] p-6 text-white shadow-[0_28px_90px_rgba(20,12,48,0.28)] sm:p-8">
        <div class="absolute -right-12 -top-12 h-52 w-52 rounded-full bg-[#E879A0]/15 blur-2xl"></div>
        <div class="absolute bottom-0 right-16 h-36 w-36 rounded-full bg-[#7C3AED]/15 blur-2xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-4 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-white/60">
                    <span>Career Command Center</span>
                    <x-badge variant="neutral">{{ $storageMode }} Mode</x-badge>
                    @if ($selectedCharacter)
                        <x-badge variant="rarity-ssr">{{ strtoupper((string) ($selectedCharacter->status ?? 'active')) }}</x-badge>
                        <x-badge variant="neutral">{{ ucwords(str_replace('_', ' ', (string) $selectedCharacter->scenario_type)) }}</x-badge>
                    @endif
                </div>

                <div>
                    <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                        {{ $selectedCharacter?->name ?? 'Dashboard' }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">
                        Track the active career run, inspect your current state, and jump into training, races, skills, and AI guidance without losing context.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($selectedCharacter)
                        <x-badge variant="secondary">{{ ucfirst((string) $selectedCharacter->mood_status) }}</x-badge>
                        <x-badge variant="neutral">Turn {{ (int) ($selectedCharacter->current_turn ?? 0) }}</x-badge>
                        <x-badge variant="neutral">{{ number_format((int) ($selectedCharacter->available_sp ?? 0)) }} SP</x-badge>
                    @else
                        <x-badge variant="neutral">No character selected</x-badge>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-3 lg:gap-6">
                @if ($selectedCharacter)
                    <div class="hidden lg:flex lg:justify-end">
                        <x-character-avatar :character="$selectedCharacter" size="md" />
                    </div>
                @endif

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-white/60">Turn</div>
                        <div class="mt-2 text-3xl font-black text-[#F9A8D4]">{{ (int) ($selectedCharacter?->current_turn ?? 0) }}</div>
                        <div class="mt-1 text-xs text-white/60">of {{ (int) ($selectedCharacter?->currentCareer?->max_turns ?? 78) }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-white/60">Energy</div>
                        <div class="mt-2 text-3xl font-black text-white">{{ (int) ($selectedCharacter?->energy_level ?? 0) }}%</div>
                        <div class="mt-1 text-xs text-white/60">{{ $selectedCharacter?->mood_status ?? 'Normal' }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-white/60">SP</div>
                        <div class="mt-2 text-3xl font-black text-white">{{ number_format((int) ($selectedCharacter?->available_sp ?? 0)) }}</div>
                        <div class="mt-1 text-xs text-white/60">Available</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($characters->isNotEmpty())
            <div class="relative mt-6 flex flex-wrap items-center gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                <label for="dashboard-character-selector" class="text-xs font-semibold uppercase tracking-[0.12em] text-white/60">Active character</label>
                <select id="dashboard-character-selector"
                    class="min-w-[18rem] rounded-xl border-white/15 bg-white/95 px-4 py-3 text-sm font-semibold text-[#1E1033] shadow-sm focus:border-[#E879A0] focus:ring-[#E879A0]"
                    wire:change="selectCharacter($event.target.value)">
                    @foreach ($characters as $character)
                        <option value="{{ $character->id }}" @selected($selectedCharacter?->id === $character->id)>
                            {{ $character->name }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('characters.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-4 py-3 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,0.35)] transition hover:brightness-105">
                    <span>+</span>
                    New Character
                </a>
            </div>
        @endif
    </section>

    @if (! $selectedCharacter)
        <x-dashboard.empty-state />
    @else
        <x-dashboard.command-grid :actions="$dashboardActions" />

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <x-dashboard.stats-snapshot
                :stats="$stats"
                :character="$selectedCharacter"
                :race-requirements="[]"
                :next-race-name="$nextRaceName"
            />

            <div class="space-y-6">
                <x-dashboard.goals-widget
                    :short-term-goal="$shortTermGoal"
                    :short-term-progress="$shortTermProgress"
                    :long-term-goal="$longTermGoal"
                    :long-term-progress="$longTermProgress"
                    :character-id="$selectedCharacter->id"
                />

                <x-dashboard.mood-energy-widget
                    :mood="$selectedMood"
                    :energy="(int) ($selectedCharacter->energy_level ?? 0)"
                    :max-energy="100"
                />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <x-dashboard.training-suggestions :suggestions="$trainingSuggestions" />
            <x-dashboard.upcoming-races :races="$upcomingRaces" />
            <x-dashboard.ai-advisor-card
                :last-tip="$lastTip"
                :tip-timestamp="$selectedCharacter->updated_at"
                :tip-reasoning="$tipReasoning"
            />
        </section>
    @endif
</div>
