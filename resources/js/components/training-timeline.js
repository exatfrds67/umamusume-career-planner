import axios from "axios";

/**
 * Training Timeline Component
 *
 * API-backed career plan visualizer for generation, locking, and turn tracking.
 */
export function trainingTimeline(config = {}) {
    return {
        currentTurn: Number(config.currentTurn ?? 1),
        totalTurns: Number(config.totalTurns ?? 1),
        characterId: Number(config.characterId ?? 0),
        turns: [],
        planId: config.initialPlanMeta?.id ?? config.initialPlan?.plan_id ?? null,
        plan: config.initialPlan ?? null,
        summary: config.initialPlan?.summary ?? {},
        generationGoal: config.initialPlanMeta?.goal ?? config.initialPlan?.goal ?? "",
        focusStats: Array.isArray(config.initialPlan?.metadata?.options?.focus_stats)
            ? [...config.initialPlan.metadata.options.focus_stats]
            : [],
        notificationPreferences: {
            email: Boolean(config.initialPlan?.metadata?.notification_preferences?.email ?? true),
            push: Boolean(config.initialPlan?.metadata?.notification_preferences?.push ?? false),
        },
        isLocked: Boolean(config.initialPlanMeta?.is_locked ?? false),
        isGenerating: false,
        isPolling: false,
        isLocking: false,
        isAdvancing: false,
        statusMessage: "",
        errorMessage: "",
        successMessage: "",
        jobId: null,
        nextAction: null,
        pollHandle: null,
        touchStartX: 0,
        touchEndX: 0,

        selectableStats: ["speed", "stamina", "power", "guts", "wit"],

        get hasPlan() {
            return Boolean(this.plan && Array.isArray(this.plan.timeline) && this.plan.timeline.length > 0);
        },

        get canGoForward() {
            return this.currentTurn < this.totalTurns;
        },

        get canGoBackward() {
            return this.currentTurn > 1;
        },

        get canLockPlan() {
            return this.hasPlan && !this.isLocked && !this.isGenerating;
        },

        get canAdvancePlan() {
            return this.hasPlan && this.isLocked && !this.isAdvancing && this.currentTurn <= this.totalTurns;
        },

        get progressPercentage() {
            return Math.round((this.currentTurn / this.totalTurns) * 100);
        },

        get currentTurnData() {
            return this.turns.find((turn) => turn.turn === this.currentTurn) || null;
        },

        get planGoalText() {
            return this.plan?.goal || this.generationGoal || "Complete the career efficiently.";
        },

        get confidenceLabel() {
            const raw = Number(this.summary?.confidence ?? 0);

            if (!Number.isFinite(raw) || raw <= 0) {
                return "N/A";
            }

            return `${Math.round(raw * 100)}%`;
        },

        get statusLabel() {
            if (this.isPolling || this.isGenerating) {
                return "Generating";
            }

            if (this.isLocked) {
                return "Locked";
            }

            if (this.hasPlan) {
                return "Ready";
            }

            return "Idle";
        },

        get statusBadgeClass() {
            return {
                "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300": this.isPolling || this.isGenerating,
                "bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300": this.isLocked,
                "bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-300": this.hasPlan && !this.isLocked,
                "bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300": !this.hasPlan && !this.isGenerating,
            };
        },

        get currentActionTitle() {
            if (!this.currentTurnData) {
                return "No turn selected";
            }

            return this.describeAction(this.currentTurnData.action);
        },

        get nextActionTitle() {
            if (!this.nextAction?.action) {
                return "Plan complete";
            }

            return this.describeAction(this.nextAction.action);
        },

        init() {
            this.setupGestureListeners();
            this.hydrateInitialPlan();
        },

        hydrateInitialPlan() {
            if (this.plan && Array.isArray(this.plan.timeline)) {
                this.hydratePlan(this.plan, {
                    id: this.planId,
                    is_locked: this.isLocked,
                    current_turn: this.currentTurn,
                    goal: this.generationGoal,
                });

                if (this.isLocked) {
                    this.fetchNextAction();
                }
            }
        },

        nextTurn() {
            if (this.canGoForward) {
                this.currentTurn++;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },

        prevTurn() {
            if (this.canGoBackward) {
                this.currentTurn--;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },

        goToTurn(turnNumber) {
            if (turnNumber >= 1 && turnNumber <= this.totalTurns) {
                this.currentTurn = turnNumber;
                this.$dispatch('turn-changed', { turn: this.currentTurn });
                this.scrollToCurrentTurn();
            }
        },

        async generatePlan() {
            this.clearMessages();
            this.isGenerating = true;
            this.statusMessage = 'Submitting plan generation request…';

            try {
                const response = await axios.post('/api/ai/career/plan', {
                    character_id: this.characterId,
                    goal: this.generationGoal || null,
                    options: {
                        depth: 'full',
                        focus_stats: this.focusStats,
                    },
                }, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                this.planId = response.data.plan_id;
                this.jobId = response.data.job_id;
                this.plan = null;
                this.turns = [];
                this.summary = {};
                this.isLocked = false;
                this.nextAction = null;
                this.statusMessage = 'Plan queued. Polling for completion…';
                this.beginPolling();
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Failed to generate the plan. Please try again.';
            } finally {
                this.isGenerating = false;
            }
        },

        beginPolling() {
            if (!this.jobId) {
                return;
            }

            this.isPolling = true;
            this.stopPolling();
            this.pollHandle = window.setInterval(() => {
                this.pollJobStatus();
            }, 3000);

            this.pollJobStatus();
        },

        stopPolling() {
            if (this.pollHandle) {
                window.clearInterval(this.pollHandle);
                this.pollHandle = null;
            }
        },

        async pollJobStatus() {
            if (!this.jobId) {
                return;
            }

            try {
                const response = await axios.get(`/api/ai/career/plan/jobs/${this.jobId}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const status = response.data?.data?.status ?? 'unknown';
                this.statusMessage = `Current job status: ${status}.`;

                if (status === 'completed') {
                    this.stopPolling();
                    this.isPolling = false;
                    await this.fetchPlan(response.data?.data?.plan_id || this.planId);
                    this.successMessage = 'AI plan generated successfully.';
                }

                if (status === 'failed') {
                    this.stopPolling();
                    this.isPolling = false;
                    this.errorMessage = response.data?.data?.error || 'Plan generation failed.';
                }
            } catch (error) {
                this.stopPolling();
                this.isPolling = false;
                this.errorMessage = error.response?.data?.message || 'Unable to retrieve plan generation status.';
            }
        },

        async fetchPlan(explicitPlanId = null) {
            const targetPlanId = explicitPlanId || this.planId;
            if (!targetPlanId) {
                return;
            }

            try {
                const response = await axios.get(`/api/ai/plan/${targetPlanId}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                    validateStatus: (status) => status === 200 || status === 202,
                });

                if (response.status === 202) {
                    this.statusMessage = 'Plan is still being prepared.';
                    return;
                }

                this.hydratePlan(response.data.plan, {
                    id: targetPlanId,
                    is_locked: this.isLocked,
                    current_turn: this.currentTurn,
                    goal: this.generationGoal,
                });
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Unable to load the generated plan.';
            }
        },

        hydratePlan(plan, meta = {}) {
            this.plan = plan;
            this.planId = meta.id ?? plan.plan_id ?? this.planId;
            this.totalTurns = Number(plan.total_turns ?? this.totalTurns);
            this.currentTurn = Number(meta.current_turn ?? this.currentTurn ?? 1);
            this.isLocked = Boolean(meta.is_locked ?? this.isLocked);
            this.summary = plan.summary ?? {};
            this.generationGoal = meta.goal ?? plan.goal ?? this.generationGoal;
            this.focusStats = Array.isArray(plan.metadata?.options?.focus_stats) ? [...plan.metadata.options.focus_stats] : this.focusStats;
            this.turns = Array.isArray(plan.timeline)
                ? plan.timeline.map((entry) => this.normalizeTurnEntry(entry))
                : [];

            if (this.currentTurn > this.totalTurns) {
                this.currentTurn = this.totalTurns;
            }
        },

        normalizeTurnEntry(entry) {
            const action = entry.action ?? {};
            const stateAfter = entry.state_after ?? {};
            const gains = action.expected_gains ?? {
                speed: 0,
                stamina: 0,
                power: 0,
                guts: 0,
                wit: 0,
            };

            return {
                turn: Number(entry.turn ?? 1),
                action,
                stats: {
                    speed: Number(gains.speed ?? 0),
                    stamina: Number(gains.stamina ?? 0),
                    power: Number(gains.power ?? 0),
                    guts: Number(gains.guts ?? 0),
                    wit: Number(gains.wit ?? 0),
                },
                energy: Number(stateAfter.energy ?? action.energy_after ?? 100),
                condition: stateAfter.mood ?? 'normal',
                sp: Number(stateAfter.sp ?? 0),
                stateAfter,
                completed: Number(entry.turn ?? 1) < this.currentTurn,
            };
        },

        async refreshPlan() {
            this.clearMessages();
            await this.fetchPlan(this.planId);

            if (this.isLocked) {
                await this.fetchNextAction();
            }
        },

        async lockPlan() {
            if (!this.planId) {
                return;
            }

            this.clearMessages();
            this.isLocking = true;

            try {
                await axios.post(`/api/ai/plan/${this.planId}/lock`, {
                    start_turn: this.currentTurn,
                    notification_preferences: this.notificationPreferences,
                }, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                this.isLocked = true;
                this.successMessage = 'Plan locked. Daily reminders and guided turn tracking are active.';
                await this.fetchNextAction();
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Unable to lock the generated plan.';
            } finally {
                this.isLocking = false;
            }
        },

        async fetchNextAction() {
            if (!this.planId || !this.isLocked) {
                return;
            }

            try {
                const response = await axios.get(`/api/ai/plan/${this.planId}/next`, {
                    headers: {
                        Accept: 'application/json',
                    },
                    validateStatus: (status) => status === 200 || status === 404,
                });

                this.nextAction = response.status === 200
                    ? {
                        turn: response.data.turn,
                        action: response.data.action,
                        remainingTurns: response.data.remaining_turns,
                    }
                    : null;
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Unable to retrieve the next action.';
            }
        },

        async advanceTurn() {
            if (!this.planId || !this.isLocked) {
                return;
            }

            this.clearMessages();
            this.isAdvancing = true;

            try {
                const response = await axios.post(`/api/ai/plan/${this.planId}/advance`, {}, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                this.currentTurn = Number(response.data.new_turn ?? this.currentTurn);
                this.turns = this.turns.map((turn) => ({
                    ...turn,
                    completed: turn.turn < this.currentTurn,
                }));
                this.successMessage = `Advanced to turn ${this.currentTurn}.`;
                await this.fetchNextAction();
            } catch (error) {
                this.errorMessage = error.response?.data?.message || 'Unable to advance the locked plan.';
            } finally {
                this.isAdvancing = false;
            }
        },

        toggleFocusStat(stat) {
            if (this.focusStats.includes(stat)) {
                this.focusStats = this.focusStats.filter((item) => item !== stat);

                return;
            }

            this.focusStats = [...this.focusStats, stat].slice(0, 3);
        },

        clearMessages() {
            this.errorMessage = '';
            this.successMessage = '';
        },

        scrollToCurrentTurn() {
            this.$nextTick(() => {
                const element = this.$el?.querySelector(`[data-turn="${this.currentTurn}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            });
        },

        setupGestureListeners() {
            this.$el?.addEventListener('touchstart', (e) => {
                this.touchStartX = e.changedTouches[0].screenX;
            }, false);

            this.$el?.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            }, false);
        },

        handleSwipe() {
            const threshold = 50;
            const diff = this.touchStartX - this.touchEndX;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.nextTurn();
                } else {
                    this.prevTurn();
                }
            }
        },

        formatStatLabel(stat) {
            return stat.charAt(0).toUpperCase() + stat.slice(1);
        },

        describeAction(action = {}) {
            switch (action.type) {
                case 'training':
                    return `${this.formatStatLabel(action.facility || 'training')} training`;
                case 'race':
                    return 'Scheduled race checkpoint';
                case 'skill':
                    return action.skill_name ? `Acquire ${action.skill_name}` : 'Skill acquisition';
                case 'rest':
                    return 'Recovery turn';
                default:
                    return 'Career action';
            }
        },

        trainingDetailLabel(action = {}) {
            const facility = action.facility ? this.formatStatLabel(action.facility) : 'Training';
            const risk = action.risk_percentage ? ` • ${action.risk_percentage}% risk` : '';

            return `${facility}${risk}`;
        },

        hasStatGains(turn) {
            return Object.values(turn?.stats ?? {}).some((value) => Number(value) !== 0);
        },

        getTurnStatusColor(turn) {
            if (turn.completed) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
            if (turn.turn === this.currentTurn) return 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300';

            return 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700/30 dark:text-neutral-300';
        },

        getTurnStatusText(turn) {
            if (turn.completed) return 'Completed';
            if (turn.turn === this.currentTurn) return this.isLocked ? 'Current recommendation' : 'Selected turn';

            return 'Upcoming';
        },

        getConditionChipClass(condition) {
            const colors = {
                great: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                good: 'bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300',
                normal: 'bg-neutral-100 text-neutral-700 dark:bg-neutral-700/60 dark:text-neutral-200',
                bad: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                awful: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            };

            return colors[condition] || colors.normal;
        },

        getStatChangeColor(stat) {
            if (stat > 0) return 'text-green-600 dark:text-green-400';
            if (stat < 0) return 'text-red-600 dark:text-red-400';

            return 'text-neutral-500 dark:text-neutral-400';
        },
    };
}
