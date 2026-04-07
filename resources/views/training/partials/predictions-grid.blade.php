{{-- WF-004: Training Predictions Grid - Six Facilities --}}
@php
    $facilityConfig = [
        'speed' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
            'color' => 'stat-speed',
            'bgLight' => 'bg-stat-speed-100 dark:bg-stat-speed-900/30',
            'textColor' => 'text-stat-speed-600 dark:text-stat-speed-400',
        ],
        'stamina' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />',
            'color' => 'stat-stamina',
            'bgLight' => 'bg-stat-stamina-100 dark:bg-stat-stamina-900/30',
            'textColor' => 'text-stat-stamina-600 dark:text-stat-stamina-400',
        ],
        'power' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
            'color' => 'stat-power',
            'bgLight' => 'bg-stat-power-100 dark:bg-stat-power-900/30',
            'textColor' => 'text-stat-power-600 dark:text-stat-power-400',
        ],
        'guts' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />',
            'color' => 'stat-guts',
            'bgLight' => 'bg-stat-guts-100 dark:bg-stat-guts-900/30',
            'textColor' => 'text-stat-guts-600 dark:text-stat-guts-400',
        ],
        'wit' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />',
            'color' => 'stat-wit',
            'bgLight' => 'bg-stat-wit-100 dark:bg-stat-wit-900/30',
            'textColor' => 'text-stat-wit-600 dark:text-stat-wit-400',
        ],
        'rest' => [
            'icon' =>
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />',
            'color' => 'gray',
            'bgLight' => 'bg-neutral-100 dark:bg-neutral-700',
            'textColor' => 'text-neutral-600 dark:text-neutral-400',
        ],
    ];
@endphp

<div id="training-predictions-app" data-character-id="{{ $character->id }}"
    data-scenario-type="{{ $character->scenario_type }}"
    data-api-url="{{ route('api.training-predictions.batch') }}"
    data-simulate-url="{{ route('api.training-predictions.simulate') }}"
    data-train-url="{{ route('training.store', $character) }}"
    data-rest-url="{{ route('characters.rest', $character) }}"
    class="animate-fade-in-delay-3">

    {{-- Loading State --}}
    <div id="predictions-loading" class="card rounded-xl p-12 text-center" role="status" aria-busy="true"
        aria-live="polite">
        <div class="text-primary-500 dark:text-primary-400 mb-4">
            <svg class="mx-auto h-16 w-16 animate-spin" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Loading Training Predictions...</h3>
        <p class="text-neutral-700 dark:text-neutral-300">Analyzing training facilities and generating AI recommendations</p>
    </div>

    {{-- Error State --}}
    <div id="predictions-error" class="card rounded-xl p-12 text-center hidden" role="alert" aria-live="assertive">
        <div class="text-red-500 dark:text-red-400 mb-4">
            <svg class="mx-auto h-16 w-16" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">Unable to Load Predictions</h3>
        <p id="predictions-error-message" class="text-neutral-700 dark:text-neutral-300 mb-4">An error occurred while fetching
            training predictions.</p>
        <button onclick="refreshPredictions()"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
            aria-label="Retry loading training predictions">
            Try Again
        </button>
    </div>

    {{-- Predictions Grid --}}
    <div id="predictions-grid" class="hidden">
        {{-- Decision-First Hero --}}
        <section id="recommended-action-hero"
            class="mb-6 rounded-xl border border-primary-200/70 dark:border-primary-700 bg-linear-to-r from-primary-50 to-blue-50 dark:from-primary-950/30 dark:to-blue-950/20 p-5 sm:p-6"
            role="region" aria-labelledby="recommended-action-heading" aria-live="polite">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary-700 dark:text-primary-300">Recommended Action</p>
                    <h3 id="recommended-action-heading" class="mt-1 text-xl font-bold text-neutral-900 dark:text-white">Analyzing best move...</h3>
                    <p id="recommended-action-summary" class="mt-2 text-sm sm:text-base text-neutral-700 dark:text-neutral-200">
                        We are evaluating gains, risk, and support-card synergy for this turn.
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                        <span id="recommended-action-risk"
                            class="inline-flex items-center rounded-md px-2 py-1 font-semibold bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200">
                            Failure Risk: --% (--)
                        </span>
                        <span id="recommended-action-primary-gain"
                            class="inline-flex items-center rounded-md px-2 py-1 font-medium bg-white/80 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700">
                            Expected Gain: --
                        </span>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                    <button id="recommended-action-cta" type="button" onclick="window.openTrainingConfirmation(window.__recommendedTraining || 'speed')"
                        class="btn btn-primary btn-md" aria-label="Confirm recommended training" disabled>
                        Confirm Recommended Action
                    </button>
                    <button type="button" onclick="window.showAIDetails()"
                        class="btn btn-secondary btn-md" aria-label="View recommendation rationale">
                        Why this choice?
                    </button>
                </div>
            </div>
        </section>

        {{-- Section Header with Legend --}}
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h2 id="facilities-heading" class="text-lg font-semibold text-neutral-900 dark:text-white">
                Ranked Alternatives
                <span class="text-sm font-normal text-neutral-500 dark:text-neutral-400 ml-2">(Levels 1-5)</span>
            </h2>
            <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400" aria-label="Risk level legend">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500" aria-hidden="true"></span> Low Risk
                    (&lt;15%)</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500" aria-hidden="true"></span> Medium
                    (15-40%)</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500" aria-hidden="true"></span> High
                    (&gt;40%)</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" aria-labelledby="facilities-heading">
            @foreach (['speed', 'stamina', 'power', 'guts', 'wit', 'rest'] as $facility)
                @php $config = $facilityConfig[$facility]; @endphp
                <div class="card p-5 training-facility hover:shadow-lg transition-shadow cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    data-facility="{{ $facility }}" data-testid="prediction-card-{{ $facility }}"
                    role="article" tabindex="0"
                    aria-label="Select {{ $facility }} training facility"
                    onclick="selectFacility('{{ $facility }}')"
                    onkeydown="if(event.key==='Enter'||event.key===' ')selectFacility('{{ $facility }}')">

                    {{-- Header with Icon, Name, and Badges --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg {{ $config['bgLight'] }} flex items-center justify-center" aria-hidden="true">
                                <svg class="w-6 h-6 {{ $config['textColor'] }}" aria-hidden="true" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    {!! $config['icon'] !!}
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 dark:text-white capitalize">{{ $facility }}
                                </h3>
                                @if ($facility !== 'rest')
                                    <span class="facility-level text-xs text-neutral-500 dark:text-neutral-400">Lv --
                                        (--×)</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="rank-badge hidden items-center gap-1 px-2 py-1 rounded text-xs font-semibold bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200"
                                data-testid="rank-badge-{{ $facility }}">
                                <span class="rank-badge-value">#--</span>
                                <span class="sr-only">rank</span>
                            </span>
                            {{-- AI Recommendation Badge --}}
                            <span
                                class="ai-badge hidden items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300"
                                data-testid="ai-recommended-badge">
                                <span aria-hidden="true">AI <svg class="w-3 h-3 inline" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></span>
                                <span class="sr-only">AI Recommended</span>
                            </span>
                            {{-- Risk Badge --}}
                            <span
                                class="risk-badge px-2 py-1 rounded text-xs font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300"
                                data-testid="risk-badge-{{ $facility }}">
                                Fail: --%
                            </span>
                        </div>
                    </div>

                    @if ($facility !== 'rest')
                        {{-- Stat Gains --}}
                        <div class="facility-stats space-y-2 text-sm mb-4" data-testid="stat-gains">
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Primary Gain:</span>
                                <span class="stat-gain font-semibold {{ $config['textColor'] }}">+--</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Secondary:</span>
                                <span class="secondary-gain font-medium text-neutral-700 dark:text-neutral-300">+--</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Skill Points:</span>
                                <span class="skill-points font-medium text-neutral-700 dark:text-neutral-300">+--</span>
                            </div>
                        </div>

                        {{-- Support Cards at Facility --}}
                        <div class="support-cards-section border-t border-neutral-100 dark:border-neutral-700 pt-3 mb-3">
                            <div
                                class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                                <span>Support Cards:</span>
                                <span class="support-card-count">0 cards (+0%)</span>
                            </div>
                            <div class="support-card-list flex flex-wrap gap-1">
                                {{-- Populated by JS --}}
                            </div>
                        </div>

                        {{-- Skill Hints --}}
                        <div class="skill-hints-section border-t border-neutral-100 dark:border-neutral-700 pt-3 mb-3 hidden">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Skill Hints:</div>
                            <div class="skill-hints-list space-y-1">
                                {{-- Populated by JS --}}
                            </div>
                        </div>

                        {{-- Efficiency Rating --}}
                        <div
                            class="flex items-center justify-between border-t border-neutral-100 dark:border-neutral-700 pt-3">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Efficiency:</span>
                            <div class="efficiency-rating flex items-center gap-1">
                                <span class="efficiency-stars flex items-center text-yellow-500" aria-hidden="true">
                                    @for($i=0; $i<5; $i++)
                                        <svg class="w-4 h-4 text-neutral-300 dark:text-neutral-600" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </span>
                                <span class="efficiency-score text-xs text-neutral-600 dark:text-neutral-400" aria-label="Efficiency rating">(--)</span>
                            </div>
                        </div>
                        <p class="efficiency-hint mt-1 text-xs text-neutral-500 dark:text-neutral-400" aria-live="polite">
                            Score = gains + bonuses - risk penalty
                        </p>
                    @else
                        {{-- Rest Option --}}
                        <div class="facility-stats space-y-2 text-sm mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Energy Recovery:</span>
                                <span
                                    class="energy-recovery font-semibold text-green-600 dark:text-green-400">+50</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Mood Effect:</span>
                                <span class="mood-effect font-medium text-neutral-700 dark:text-neutral-300">Possible
                                    improvement</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-400">Bad Status Cure:</span>
                                <span class="cure-chance font-medium text-neutral-700 dark:text-neutral-300">Chance to
                                    cure</span>
                            </div>
                        </div>
                    @endif

                    {{-- Action Button --}}
                    <button
                        class="w-full mt-2 btn {{ $facility === 'rest' ? 'btn-secondary' : 'btn-primary' }} btn-sm"
                        onclick="event.stopPropagation(); openTrainingConfirmation('{{ $facility }}')"
                        aria-label="{{ $facility === 'rest' ? 'Choose rest this turn' : 'Choose ' . $facility . ' training this turn' }}">
                        {{ $facility === 'rest' ? 'Rest' : 'Train' }}
                    </button>
                </div>
            @endforeach
        </div>

        {{-- AI Summary Panel --}}
        <div id="predictions-summary" class="mt-6 card rounded-xl p-6" role="complementary"
            aria-labelledby="ai-recommendation-heading">
            <h3 id="ai-recommendation-heading"
                class="text-lg font-semibold text-neutral-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" aria-hidden="true" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Evidence & Calculation
            </h3>
            <p id="ai-recommendation-text" class="text-neutral-700 dark:text-neutral-300 mb-4">
                Detailed rationale for the recommended action appears here.
            </p>

            {{-- Calculation Breakdown (Collapsible) --}}
            <details class="mt-4">
                <summary
                    class="text-sm font-medium text-neutral-600 dark:text-neutral-400 cursor-pointer hover:text-neutral-900 dark:hover:text-white"
                    aria-label="Toggle calculation breakdown">
                    View Calculation Breakdown
                </summary>
                <div id="calculation-breakdown" class="mt-3 p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg text-sm">
                    <div class="grid grid-cols-2 gap-2 text-neutral-600 dark:text-neutral-400">
                        <span>Base Gain:</span><span class="breakdown-base font-mono">--</span>
                        <span>Growth Rate:</span><span class="breakdown-growth font-mono">×--</span>
                        <span>Mood Modifier:</span><span class="breakdown-mood font-mono">×--</span>
                        <span>Support Cards:</span><span class="breakdown-support font-mono">×--</span>
                        <span>Friendship:</span><span class="breakdown-friendship font-mono">×--</span>
                        <span>Facility Level:</span><span class="breakdown-facility font-mono">×--</span>
                        <span class="font-semibold text-neutral-900 dark:text-white">Total Multiplier:</span>
                        <span
                            class="breakdown-total font-mono font-semibold text-primary-600 dark:text-primary-400">×--</span>
                    </div>
                </div>
            </details>
        </div>
    </div>
</div>

