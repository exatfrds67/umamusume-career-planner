<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\TrainingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Advisory Panel Component
 *
 * Displays AI-powered recommendations and critical alerts during training.
 * Features collapsible panel with priority-based organization and session-based dismissal tracking.
 *
 * Features:
 * - Collapsible panel with expand/collapse state
 * - Priority-based organization (CRITICAL > HIGH > MEDIUM > LOW)
 * - Session-based dismissal tracking
 * - Keyboard navigation support (Alt+A to toggle)
 * - WCAG 2.2 AA compliant
 * - Works in both Local and Account storage modes
 *
 * @property bool $isOpen Panel open/closed state
 * @property bool $showCriticalAlerts Show critical alerts section
 * @property bool $showTrainingRecommendations Show training recommendations section
 * @property bool $showSkillRecommendations Show skill recommendations section
 * @property bool $showRaceStrategy Show race strategy section
 * @property array<int, int|string> $dismissedAlerts Array of dismissed alert IDs (session-based)
 * @property array<int, int|string> $dismissedRecommendations Array of dismissed recommendation IDs (session-based)
 */
class AdvisoryPanel extends Component
{
    /**
     * Panel open/closed state
     */
    public bool $isOpen = false;

    /**
     * Section visibility toggles
     */
    public bool $showCriticalAlerts = true;

    public bool $showTrainingRecommendations = true;

    public bool $showSkillRecommendations = true;

    public bool $showRaceStrategy = true;

    /**
     * Dismissed items tracking (session-based)
     *
     * @var array<int, int|string>
     */
    public array $dismissedAlerts = [];

    /**
     * @var array<int, int|string>
     */
    public array $dismissedRecommendations = [];

    /**
     * Training context for generating recommendations
     *
     * @var array<string, mixed>|null
     */
    public ?array $trainingContext = null;

    /**
     * Career run ID (for account mode)
     */
    public ?int $careerRunId = null;

    /**
     * Storage mode (local or account)
     */
    public string $storageMode = 'local';

    /**
     * Current turn number
     */
    public int $turnNumber = 1;

    /**
     * Cached recommendations and alerts
     */
    protected ?RecommendationCollection $recommendations = null;

    protected ?CriticalAlertCollection $alerts = null;

    /**
     * Mount the component
     *
     * @param  array<string, mixed>|null  $trainingContext  Training context data
     * @param  int|null  $careerRunId  Career run ID (account mode)
     * @param  string  $storageMode  Storage mode (local or account)
     * @param  int  $turnNumber  Current turn number
     */
    public function mount(
        ?array $trainingContext = null,
        ?int $careerRunId = null,
        string $storageMode = 'local',
        int $turnNumber = 1
    ): void {
        $this->trainingContext = $trainingContext;
        $this->careerRunId = $careerRunId;
        $this->storageMode = $storageMode;
        $this->turnNumber = $turnNumber;

        // Load dismissed items from session
        $this->loadDismissedFromSession();
    }

    /**
     * Toggle panel open/closed state
     */
    public function togglePanel(): void
    {
        $this->isOpen = ! $this->isOpen;

        // Dispatch event for analytics/tracking
        $this->dispatch('advisory-panel-toggled', isOpen: $this->isOpen);
    }

    /**
     * Open the panel
     */
    #[On('open-advisory-panel')]
    public function openPanel(): void
    {
        $this->isOpen = true;
        $this->dispatch('advisory-panel-opened');
    }

    /**
     * Close the panel
     */
    #[On('close-advisory-panel')]
    public function closePanel(): void
    {
        $this->isOpen = false;
        $this->dispatch('advisory-panel-closed');
    }

    /**
     * Toggle section visibility
     *
     * @param  string  $section  Section name (criticalAlerts, trainingRecommendations, skillRecommendations, raceStrategy)
     */
    public function toggleSection(string $section): void
    {
        match ($section) {
            'criticalAlerts' => $this->showCriticalAlerts = ! $this->showCriticalAlerts,
            'trainingRecommendations' => $this->showTrainingRecommendations = ! $this->showTrainingRecommendations,
            'skillRecommendations' => $this->showSkillRecommendations = ! $this->showSkillRecommendations,
            'raceStrategy' => $this->showRaceStrategy = ! $this->showRaceStrategy,
            default => null,
        };
    }

    /**
     * Dismiss a critical alert
     *
     * @param  int|string  $alertId  Alert ID to dismiss
     */
    public function dismissAlert(int|string $alertId): void
    {
        // Add to dismissed list
        if (! \in_array($alertId, $this->dismissedAlerts, true)) {
            $this->dismissedAlerts[] = $alertId;
        }

        // Save to session
        $this->saveDismissedToSession();

        // If account mode, persist dismissal to database
        if ($this->storageMode === 'account' && \is_int($alertId)) {
            try {
                $advisoryService = app(TrainingAdvisoryService::class);
                $advisoryService->dismissCriticalAlert($alertId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[AdvisoryPanel] Failed to dismiss alert in database', [
                    'alert_id' => $alertId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Dispatch event for UI feedback
        $this->dispatch('alert-dismissed', alertId: $alertId);
    }

    /**
     * Dismiss a recommendation
     *
     * @param  int|string  $recommendationId  Recommendation ID to dismiss
     */
    public function dismissRecommendation(int|string $recommendationId): void
    {
        // Add to dismissed list
        if (! \in_array($recommendationId, $this->dismissedRecommendations, true)) {
            $this->dismissedRecommendations[] = $recommendationId;
        }

        // Save to session
        $this->saveDismissedToSession();

        // Dispatch event for UI feedback
        $this->dispatch('recommendation-dismissed', recommendationId: $recommendationId);
    }

    /**
     * Reactivate a dismissed alert
     *
     * @param  int|string  $alertId  Alert ID to reactivate
     */
    public function reactivateAlert(int|string $alertId): void
    {
        // Remove from dismissed list and re-index
        $this->dismissedAlerts = array_values(array_filter(
            $this->dismissedAlerts,
            fn ($id) => $id !== $alertId
        ));

        // Save to session
        $this->saveDismissedToSession();

        // If account mode, reactivate in database
        if ($this->storageMode === 'account' && is_int($alertId)) {
            try {
                $advisoryService = app(TrainingAdvisoryService::class);
                $advisoryService->reactivateCriticalAlert($alertId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[AdvisoryPanel] Failed to reactivate alert in database', [
                    'alert_id' => $alertId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Dispatch event for UI feedback
        $this->dispatch('alert-reactivated', alertId: $alertId);
    }

    /**
     * Clear all dismissed items
     */
    public function clearDismissed(): void
    {
        $this->dismissedAlerts = [];
        $this->dismissedRecommendations = [];
        $this->saveDismissedToSession();

        $this->dispatch('dismissed-cleared');
    }

    /**
     * Refresh recommendations and alerts
     */
    #[On('refresh-advisory')]
    public function refresh(): void
    {
        // Clear cached data
        $this->recommendations = null;
        $this->alerts = null;

        // Dispatch event for UI feedback
        $this->dispatch('advisory-refreshed');
    }

    /**
     * Get critical alerts
     */
    public function getCriticalAlerts(): CriticalAlertCollection
    {
        // Return cached alerts if available
        if ($this->alerts !== null) {
            return $this->filterDismissedAlerts($this->alerts);
        }

        // Generate alerts if training context is available
        if ($this->trainingContext !== null) {
            try {
                $advisoryService = app(TrainingAdvisoryService::class);
                $context = $this->buildTrainingContext();
                $this->alerts = $advisoryService->detectCriticalSituations($context);

                return $this->filterDismissedAlerts($this->alerts);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[AdvisoryPanel] Failed to get critical alerts', [
                    'error' => $e->getMessage(),
                    'turn' => $this->turnNumber,
                ]);

                return new CriticalAlertCollection([]);
            }
        }

        // If account mode and career run ID is available, load from database
        if ($this->storageMode === 'account' && $this->careerRunId !== null) {
            try {
                $advisoryService = app(TrainingAdvisoryService::class);
                $dbAlerts = $advisoryService->getActiveCriticalAlerts($this->careerRunId);

                // Convert Eloquent models to CriticalAlert ValueObjects
                $alertValueObjects = $dbAlerts->map(function ($model) {
                    // Assuming the model has a toCriticalAlert() method or similar
                    // If not, we need to manually construct CriticalAlert from model data
                    if (method_exists($model, 'toCriticalAlert')) {
                        return $model->toCriticalAlert();
                    }

                    // Fallback: construct from model attributes
                    return \App\ValueObjects\CriticalAlert::fromArray([
                        'type' => $model->type ?? 'stamina_crisis',
                        'message' => $model->message ?? '',
                        'action_items' => $model->action_items ?? [],
                        'turns_until_critical' => $model->turns_until_critical ?? 0,
                        'detailed_analysis' => $model->detailed_analysis ?? null,
                        'priority' => $model->priority ?? 'critical',
                        'storage_mode' => $this->storageMode,
                    ]);
                })->all();

                // Convert to CriticalAlertCollection with proper type
                $this->alerts = new CriticalAlertCollection($alertValueObjects);

                return $this->filterDismissedAlerts($this->alerts);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[AdvisoryPanel] Failed to load alerts from database', [
                    'error' => $e->getMessage(),
                    'career_run_id' => $this->careerRunId,
                ]);

                return new CriticalAlertCollection([]);
            }
        }

        return new CriticalAlertCollection([]);
    }

    /**
     * Get training recommendations
     */
    public function getTrainingRecommendations(): RecommendationCollection
    {
        // Return cached recommendations if available
        if ($this->recommendations !== null) {
            return $this->filterDismissedRecommendations($this->recommendations);
        }

        // Generate recommendations if training context is available
        if ($this->trainingContext !== null) {
            try {
                $advisoryService = app(TrainingAdvisoryService::class);
                $context = $this->buildTrainingContext();
                $this->recommendations = $advisoryService->getTrainingRecommendations($context);

                return $this->filterDismissedRecommendations($this->recommendations);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[AdvisoryPanel] Failed to get training recommendations', [
                    'error' => $e->getMessage(),
                    'turn' => $this->turnNumber,
                ]);

                return new RecommendationCollection([]);
            }
        }

        return new RecommendationCollection([]);
    }

    /**
     * Build TrainingContext from component data
     *
     * @throws \ValueError If enum values are invalid
     */
    protected function buildTrainingContext(): TrainingContext
    {
        // This is a simplified version - in production, you'd build a complete TrainingContext
        // from the trainingContext array data

        // Safely extract and cast values from trainingContext array
        $turnNumber = $this->trainingContext['turn_number'] ?? $this->turnNumber;
        $phase = $this->trainingContext['phase'] ?? 'junior_year';
        $stats = $this->trainingContext['stats'] ?? [];
        $spAvailable = $this->trainingContext['sp_available'] ?? 0;
        $energy = $this->trainingContext['energy'] ?? 100;
        $mood = $this->trainingContext['mood'] ?? 'normal';
        $acquiredSkills = $this->trainingContext['acquired_skills'] ?? [];
        $skillHints = $this->trainingContext['skill_hints'] ?? [];
        $supportDeck = $this->trainingContext['support_deck'] ?? [];
        $facilityLevels = $this->trainingContext['facility_levels'] ?? [];
        $upcomingRaces = $this->trainingContext['upcoming_races'] ?? [];
        $scenario = $this->trainingContext['scenario'] ?? null;

        // Cast stats array values to int with proper type checking
        $speed = 0;
        $stamina = 0;
        $power = 0;
        $guts = 0;
        $wisdom = 0;

        if (is_array($stats)) {
            $speed = isset($stats['speed']) && is_numeric($stats['speed']) ? (int) $stats['speed'] : 0;
            $stamina = isset($stats['stamina']) && is_numeric($stats['stamina']) ? (int) $stats['stamina'] : 0;
            $power = isset($stats['power']) && is_numeric($stats['power']) ? (int) $stats['power'] : 0;
            $guts = isset($stats['guts']) && is_numeric($stats['guts']) ? (int) $stats['guts'] : 0;
            $wisdom = isset($stats['wisdom']) && is_numeric($stats['wisdom']) ? (int) $stats['wisdom'] : 0;
        }

        // Cast acquiredSkills to array<int>
        $acquiredSkillsTyped = [];
        if (is_array($acquiredSkills)) {
            foreach ($acquiredSkills as $skillId) {
                if (is_int($skillId) || is_numeric($skillId)) {
                    $acquiredSkillsTyped[] = (int) $skillId;
                }
            }
        }

        // Cast skillHints to array<array{skill_id: int, level: int}>
        $skillHintsTyped = [];
        if (is_array($skillHints)) {
            foreach ($skillHints as $hint) {
                if (is_array($hint)) {
                    $skillId = $hint['skill_id'] ?? 0;
                    $level = $hint['level'] ?? 0;
                    $skillHintsTyped[] = [
                        'skill_id' => is_numeric($skillId) ? (int) $skillId : 0,
                        'level' => is_numeric($level) ? (int) $level : 0,
                    ];
                }
            }
        }

        // Cast facilityLevels to array<string, int>
        $facilityLevelsTyped = [];
        if (is_array($facilityLevels)) {
            foreach ($facilityLevels as $facility => $level) {
                $facilityKey = is_string($facility) ? $facility : (string) $facility;
                $facilityLevelsTyped[$facilityKey] = is_numeric($level) ? (int) $level : 1;
            }
        }

        // Cast upcomingRaces to array<array{id: int, distance: string, turn: int}>
        $upcomingRacesTyped = [];
        if (is_array($upcomingRaces)) {
            foreach ($upcomingRaces as $race) {
                if (is_array($race)) {
                    $raceId = $race['id'] ?? 0;
                    $distance = $race['distance'] ?? 'short';
                    $turn = $race['turn'] ?? 0;

                    $upcomingRacesTyped[] = [
                        'id' => is_numeric($raceId) ? (int) $raceId : 0,
                        'distance' => is_string($distance) ? $distance : 'short',
                        'turn' => is_numeric($turn) ? (int) $turn : 0,
                    ];
                }
            }
        }

        // Cast support deck to array<SupportCard>
        $supportCards = [];
        if (is_array($supportDeck)) {
            // If supportDeck is already an array of SupportCard objects, use it directly
            // Otherwise, we need to construct SupportCard objects from array data
            foreach ($supportDeck as $card) {
                if ($card instanceof \App\ValueObjects\SupportCard) {
                    $supportCards[] = $card;
                } elseif (is_array($card)) {
                    // Ensure the array has string keys for fromArray method
                    /** @var array<string, mixed> $cardData */
                    $cardData = [];
                    foreach ($card as $key => $value) {
                        $cardData[(string) $key] = $value;
                    }
                    // Construct SupportCard from array if needed
                    $supportCards[] = \App\ValueObjects\SupportCard::fromArray($cardData);
                }
            }
        }

        return new TrainingContext(
            turnNumber: is_numeric($turnNumber) ? (int) $turnNumber : 1,
            phase: \App\Enums\CareerPhase::from(is_string($phase) ? $phase : 'junior_year'),
            stats: new \App\ValueObjects\CharacterStats(
                speed: $speed,
                stamina: $stamina,
                power: $power,
                guts: $guts,
                wisdom: $wisdom
            ),
            spAvailable: is_numeric($spAvailable) ? (int) $spAvailable : 0,
            energy: is_numeric($energy) ? (int) $energy : 100,
            mood: \App\Enums\Mood::from(is_string($mood) ? $mood : 'normal'),
            acquiredSkills: $acquiredSkillsTyped,
            skillHints: $skillHintsTyped,
            deck: new \App\ValueObjects\SupportCardDeck($supportCards),
            facilityLevels: $facilityLevelsTyped,
            upcomingRaces: $upcomingRacesTyped,
            scenario: is_string($scenario) ? $scenario : null
        );
    }

    /**
     * Filter dismissed alerts from collection
     */
    protected function filterDismissedAlerts(CriticalAlertCollection $alerts): CriticalAlertCollection
    {
        return new CriticalAlertCollection(
            $alerts->filter(function ($alert) {
                // Use type value as identifier since CriticalAlert doesn't have an id property
                $alertId = $alert->type->value;

                return ! \in_array($alertId, $this->dismissedAlerts, true);
            })->all()
        );
    }

    /**
     * Filter dismissed recommendations from collection
     */
    protected function filterDismissedRecommendations(RecommendationCollection $recommendations): RecommendationCollection
    {
        return new RecommendationCollection(
            $recommendations->filter(function ($recommendation) {
                // Use action as identifier since Recommendation doesn't have an id property
                $recId = $recommendation->action;

                return ! \in_array($recId, $this->dismissedRecommendations, true);
            })->all()
        );
    }

    /**
     * Load dismissed items from session
     */
    protected function loadDismissedFromSession(): void
    {
        $sessionKey = $this->getSessionKey();

        // Load and cast to proper types
        $alerts = session()->get("{$sessionKey}.alerts", []);
        $recommendations = session()->get("{$sessionKey}.recommendations", []);

        // Ensure we have array<int, int|string> by filtering and casting
        $this->dismissedAlerts = [];
        if (is_array($alerts)) {
            foreach ($alerts as $alert) {
                if (is_int($alert) || is_string($alert)) {
                    $this->dismissedAlerts[] = $alert;
                }
            }
        }

        $this->dismissedRecommendations = [];
        if (is_array($recommendations)) {
            foreach ($recommendations as $recommendation) {
                if (is_int($recommendation) || is_string($recommendation)) {
                    $this->dismissedRecommendations[] = $recommendation;
                }
            }
        }
    }

    /**
     * Save dismissed items to session
     */
    protected function saveDismissedToSession(): void
    {
        $sessionKey = $this->getSessionKey();

        session()->put("{$sessionKey}.alerts", $this->dismissedAlerts);
        session()->put("{$sessionKey}.recommendations", $this->dismissedRecommendations);
    }

    /**
     * Get session key for dismissed items
     */
    protected function getSessionKey(): string
    {
        if ($this->storageMode === 'account' && $this->careerRunId !== null) {
            return "advisory_dismissed_account_{$this->careerRunId}";
        }

        return 'advisory_dismissed_local';
    }

    /**
     * Get count of active critical alerts
     */
    public function getCriticalAlertCount(): int
    {
        return $this->getCriticalAlerts()->count();
    }

    /**
     * Get count of active training recommendations
     */
    public function getTrainingRecommendationCount(): int
    {
        return $this->getTrainingRecommendations()->count();
    }

    /**
     * Check if panel has any content to display
     */
    public function hasContent(): bool
    {
        return $this->getCriticalAlertCount() > 0 || $this->getTrainingRecommendationCount() > 0;
    }

    /**
     * Render the component
     */
    public function render(): View
    {
        // Only fetch data if panel is open or has content to show
        // This prevents unnecessary data fetching when panel is closed
        $criticalAlerts = $this->isOpen ? $this->getCriticalAlerts() : new CriticalAlertCollection([]);
        $trainingRecommendations = $this->isOpen ? $this->getTrainingRecommendations() : new RecommendationCollection([]);

        $criticalAlertCount = $criticalAlerts->count();
        $trainingRecommendationCount = $trainingRecommendations->count();
        $hasContent = $criticalAlertCount > 0 || $trainingRecommendationCount > 0;

        return view('livewire.advisory-panel', [
            'criticalAlerts' => $criticalAlerts,
            'trainingRecommendations' => $trainingRecommendations,
            'criticalAlertCount' => $criticalAlertCount,
            'trainingRecommendationCount' => $trainingRecommendationCount,
            'hasContent' => $hasContent,
        ])->layout('layouts.app', [
            // Disable layout caching for this component to ensure fresh data
            'cache' => false,
        ]);
    }
}
