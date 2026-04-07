{{--
Component: TrainingTimeline
Purpose: API-backed AI career plan visualisation and turn navigation

Props:
  - characterId (int): Character identifier used for generation requests
  - totalTurns (int): Total scenario turns available
  - currentTurn (int): Current active turn in the character state
  - initialPlan (array|null): Latest preloaded plan payload
  - initialPlanMeta (array|null): Latest preloaded plan model metadata

Accessibility: WCAG 2.2 AA compliant
--}}
@props([
    'characterId',
    'totalTurns' => 1,
    'currentTurn' => 1,
    'initialPlan' => null,
    'initialPlanMeta' => null,
])

<div
    x-data="trainingTimeline({
        characterId: {{ $characterId }},
        totalTurns: {{ $totalTurns }},
        currentTurn: {{ $currentTurn }},
        initialPlan: @js($initialPlan),
        initialPlanMeta: @js($initialPlanMeta),
    })"
    x-init="init()"
    class="space-y-5"
>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">AI Training Plan Visualisation</h3>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Generate a turn-by-turn plan, lock it when ready, and step through the run from this page.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                :class="statusBadgeClass"
                x-text="statusLabel"
            ></span>
            <template x-if="planId">
                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                    Plan <span x-text="planId"></span>
                </span>
            </template>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300" role="alert" x-text="errorMessage"></div>
    </template>

    <template x-if="successMessage">
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300" role="status" x-text="successMessage"></div>
    </template>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="space-y-5">
            <section class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex flex-col gap-4">
                    <div>
                        <label for="career-plan-goal" class="mb-2 block text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                            Planning goal
                        </label>
                        <textarea
                            id="career-plan-goal"
                            x-model="generationGoal"
                            rows="3"
                            maxlength="500"
                            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 shadow-xs transition-colors focus:outline-hidden focus:ring-2 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                            placeholder="Example: Build enough stamina and race readiness to win the final URA race."
                        ></textarea>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-semibold text-neutral-800 dark:text-neutral-200">Focus stats</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="stat in selectableStats" :key="stat">
                                <button
                                    type="button"
                                    @click="toggleFocusStat(stat)"
                                    class="rounded-full border px-3 py-1.5 text-sm font-medium transition-colors focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                                    :class="focusStats.includes(stat)
                                        ? 'border-primary-500 bg-primary-500 text-white dark:border-primary-400 dark:bg-primary-500 dark:text-white'
                                        : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                    :aria-pressed="focusStats.includes(stat).toString()"
                                    x-text="formatStatLabel(stat)"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                            Plans are generated asynchronously and resumed from the latest saved state on reload.
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="generatePlan()"
                                :disabled="isGenerating"
                                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                            >
                                <svg x-show="!isGenerating" class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <svg x-show="isGenerating" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span x-text="isGenerating ? 'Generating plan…' : (hasPlan ? 'Regenerate plan' : 'Generate plan')"></span>
                            </button>

                            <button
                                type="button"
                                @click="refreshPlan()"
                                :disabled="!planId || isGenerating"
                                class="inline-flex items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-2.5 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-hidden focus:ring-2 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                            >
                                Refresh plan
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <template x-if="isPolling">
                <section class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-xs dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 dark:text-blue-200">Plan generation in progress</h4>
                            <p class="mt-1 text-sm text-blue-800 dark:text-blue-300" x-text="statusMessage"></p>
                        </div>
                    </div>
                </section>
            </template>

            <template x-if="hasPlan">
                <section class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 class="text-base font-semibold text-neutral-900 dark:text-white">Plan summary</h4>
                                    <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300" x-text="`Turn ${currentTurn} / ${totalTurns}`"></span>
                                </div>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-text="planGoalText"></p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="lockPlan()"
                                    x-show="canLockPlan"
                                    :disabled="isLocking"
                                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                                >
                                    <span x-text="isLocking ? 'Locking…' : 'Lock plan'"></span>
                                </button>

                                <button
                                    type="button"
                                    @click="advanceTurn()"
                                    x-show="canAdvancePlan"
                                    :disabled="isAdvancing"
                                    class="inline-flex items-center justify-center rounded-xl bg-secondary-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-secondary-700 disabled:cursor-not-allowed disabled:opacity-60 focus:outline-hidden focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                                >
                                    <span x-text="isAdvancing ? 'Advancing…' : 'Advance recommended turn'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Confidence</p>
                                <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white" x-text="confidenceLabel"></p>
                            </div>
                            <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">SP earned</p>
                                <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white" x-text="summary.total_sp_earned ?? 0"></p>
                            </div>
                            <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Predicted wins</p>
                                <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white" x-text="summary.races_won ?? 0"></p>
                            </div>
                            <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Timeline length</p>
                                <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white" x-text="totalTurns"></p>
                            </div>
                        </div>

                        <template x-if="nextAction">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Next recommended action</p>
                                        <h5 class="mt-1 text-sm font-semibold text-amber-900 dark:text-amber-100" x-text="nextActionTitle"></h5>
                                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-200" x-text="nextAction.action.reasoning || 'Time to take the next recommended action.'"></p>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200" x-text="`Turn ${nextAction.turn ?? currentTurn}`"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="summary.final_predicted_stats">
                            <div class="grid gap-3 sm:grid-cols-5">
                                <template x-for="(value, stat) in summary.final_predicted_stats" :key="stat">
                                    <div class="rounded-xl border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900/60">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400" x-text="formatStatLabel(stat)"></p>
                                        <p class="mt-2 text-xl font-bold text-neutral-900 dark:text-white" x-text="value"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </section>
            </template>

            <template x-if="hasPlan">
                <section class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-neutral-900 dark:text-white">Turn navigation</h4>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Browse the full plan timeline or follow the current locked recommendation.</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="prevTurn()"
                                :disabled="!canGoBackward"
                                class="rounded-xl border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-hidden focus:ring-2 focus:ring-primary-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                            >Previous</button>
                            <button
                                type="button"
                                @click="nextTurn()"
                                :disabled="!canGoForward"
                                class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                            >Next</button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="career-plan-turn-slider" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Jump to turn</label>
                        <input
                            id="career-plan-turn-slider"
                            type="range"
                            min="1"
                            :max="totalTurns"
                            x-model="currentTurn"
                            @input="goToTurn(Number($event.target.value))"
                            class="w-full accent-primary-600"
                        >
                    </div>

                    <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                        <template x-for="turn in turns" :key="turn.turn">
                            <button
                                type="button"
                                @click="goToTurn(turn.turn)"
                                :data-turn="turn.turn"
                                class="min-w-[4rem] rounded-xl border px-3 py-2 text-left text-xs font-semibold transition-colors"
                                :class="turn.turn === currentTurn
                                    ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-900/30 dark:text-primary-300'
                                    : turn.completed
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300'
                                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                            >
                                <div x-text="`T${turn.turn}`"></div>
                                <div class="mt-1 text-[10px] uppercase tracking-wider" x-text="turn.action.type"></div>
                            </button>
                        </template>
                    </div>
                </section>
            </template>
        </div>

        <div class="space-y-5">
            <template x-if="!hasPlan && !isGenerating">
                <section class="rounded-xl border border-dashed border-neutral-300 bg-white p-6 text-center shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-700/50">
                        <svg class="h-6 w-6 text-neutral-500 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m9 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-base font-semibold text-neutral-900 dark:text-white">No generated plan yet</h4>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Start by entering a goal, then generate a plan to see turn-by-turn recommendations here.</p>
                </section>
            </template>

            <template x-if="currentTurnData">
                <section class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-base font-semibold text-neutral-900 dark:text-white" x-text="currentActionTitle"></h4>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-text="currentTurnData.action.reasoning || 'No reasoning available.'"></p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="getTurnStatusColor(currentTurnData)" x-text="getTurnStatusText(currentTurnData)"></span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Condition</p>
                            <div class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold capitalize" :class="getConditionChipClass(currentTurnData.condition)">
                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                <span x-text="currentTurnData.condition"></span>
                            </div>
                        </div>
                        <div class="rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900/70">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Energy after turn</p>
                            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white" x-text="`${currentTurnData.energy}/100`"></p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3" x-show="hasStatGains(currentTurnData)">
                        <h5 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Projected stat gains</h5>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="(value, stat) in currentTurnData.stats" :key="stat">
                                <div class="rounded-xl bg-neutral-50 p-2 text-center dark:bg-neutral-900/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400" x-text="formatStatLabel(stat)"></div>
                                    <div class="mt-1 text-lg font-bold" :class="getStatChangeColor(value)" x-text="value > 0 ? `+${value}` : value"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2" x-show="currentTurnData.action.type === 'skill' || currentTurnData.action.type === 'race' || currentTurnData.action.type === 'training'">
                        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700" x-show="currentTurnData.action.type === 'skill'">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Skill purchase</p>
                            <p class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white" x-text="currentTurnData.action.skill_name || 'Skill acquisition'"></p>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-text="`SP cost: ${currentTurnData.action.discounted_cost || currentTurnData.action.spent_sp || 0}`"></p>
                        </div>

                        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700" x-show="currentTurnData.action.type === 'race'">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Race projection</p>
                            <p class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white" x-text="currentTurnData.action.expected_result || 'Race planned'"></p>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-text="`SP reward: ${currentTurnData.action.sp_reward || 0}`"></p>
                        </div>

                        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700" x-show="currentTurnData.action.type === 'training'">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Training details</p>
                            <p class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white" x-text="trainingDetailLabel(currentTurnData.action)"></p>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400" x-text="`Projected SP gain: ${currentTurnData.action.expected_sp_gain || 0}`"></p>
                        </div>
                    </div>
                </section>
            </template>

            <template x-if="hasPlan && !isLocked">
                <section class="rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-neutral-800">
                    <h4 class="text-base font-semibold text-neutral-900 dark:text-white">Reminder preferences</h4>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">These settings apply when you lock a freshly generated plan.</p>
                    <div class="mt-4 space-y-3">
                        <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3 text-sm dark:border-neutral-700">
                            <span class="font-medium text-neutral-800 dark:text-neutral-200">Email reminders</span>
                            <input type="checkbox" x-model="notificationPreferences.email" class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                        </label>
                        <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3 text-sm dark:border-neutral-700">
                            <span class="font-medium text-neutral-800 dark:text-neutral-200">Push reminders</span>
                            <input type="checkbox" x-model="notificationPreferences.push" class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                        </label>
                    </div>
                </section>
            </template>
        </div>
    </div>
</div>
