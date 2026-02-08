<?php

declare(strict_types=1);

use App\Enums\AlertType;
use App\Enums\Mood;
use App\Enums\Priority;
use App\Models\Career;
use App\Models\CriticalAlert;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\CriticalAlert as CriticalAlertVO;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the dependencies to avoid AWS credential issues
    $this->neuronAIService = Mockery::mock(\App\Services\Neuron\NeuronAIService::class);
    $this->ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
    $this->mechanicsEngine = app(GameMechanicsEngine::class);
    $this->accuracyTracker = Mockery::mock(PredictionAccuracyTracker::class);
    $this->criticalDetector = app(CriticalSituationDetector::class);
    $this->recommendationCache = Mockery::mock(\App\Services\RecommendationCacheService::class);
    $this->performanceMonitor = Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class);

    $this->service = new TrainingAdvisoryService(
        $this->neuronAIService,
        $this->ruleBasedAdvisor,
        $this->mechanicsEngine,
        $this->accuracyTracker,
        $this->criticalDetector,
        $this->recommendationCache,
        $this->performanceMonitor
    );

    $this->career = Career::factory()->create();
});

describe('persistCriticalAlerts', function () {
    it('persists account mode alerts to database', function () {
        $alerts = new \App\Collections\CriticalAlertCollection([
            new CriticalAlertVO(
                type: AlertType::STAMINA_CRISIS,
                message: 'Stamina critically low',
                actionItems: ['Focus on stamina training', 'Purchase recovery skills'],
                turnsUntilCritical: 3,
                detailedAnalysis: 'Detailed analysis here',
                priority: Priority::CRITICAL,
                storageMode: 'account',
            ),
            new CriticalAlertVO(
                type: AlertType::ENERGY_CRITICAL,
                message: 'Energy at 35',
                actionItems: ['Rest immediately'],
                turnsUntilCritical: 0,
                detailedAnalysis: null,
                priority: Priority::HIGH,
                storageMode: 'account',
            ),
        ]);

        $count = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );

        expect($count)->toBe(2);

        $persisted = CriticalAlert::where('career_id', $this->career->id)->get();
        expect($persisted)->toHaveCount(2);

        $staminaAlert = $persisted->firstWhere('alert_type', AlertType::STAMINA_CRISIS);
        expect($staminaAlert)->not->toBeNull();
        expect($staminaAlert->message)->toBe('Stamina critically low');
        expect($staminaAlert->action_items)->toBe(['Focus on stamina training', 'Purchase recovery skills']);
        expect($staminaAlert->turns_until_critical)->toBe(3);
        expect($staminaAlert->was_dismissed)->toBeFalse();
        expect($staminaAlert->dismissed_at)->toBeNull();

        $energyAlert = $persisted->firstWhere('alert_type', AlertType::ENERGY_CRITICAL);
        expect($energyAlert)->not->toBeNull();
        expect($energyAlert->message)->toBe('Energy at 35');
        expect($energyAlert->turns_until_critical)->toBe(0);
    });

    it('skips local mode alerts', function () {
        $alerts = new \App\Collections\CriticalAlertCollection([
            new CriticalAlertVO(
                type: AlertType::STAMINA_CRISIS,
                message: 'Stamina critically low',
                actionItems: ['Focus on stamina training'],
                turnsUntilCritical: 3,
                storageMode: 'local', // Local mode
            ),
            new CriticalAlertVO(
                type: AlertType::ENERGY_CRITICAL,
                message: 'Energy at 35',
                actionItems: ['Rest immediately'],
                turnsUntilCritical: 0,
                storageMode: 'account', // Account mode
            ),
        ]);

        $count = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );

        // Only account mode alert should be persisted
        expect($count)->toBe(1);

        $persisted = CriticalAlert::where('career_id', $this->career->id)->get();
        expect($persisted)->toHaveCount(1);
        expect($persisted->first()->alert_type)->toBe(AlertType::ENERGY_CRITICAL);
    });

    it('avoids duplicate alerts for same career/turn/type', function () {
        $alerts = new \App\Collections\CriticalAlertCollection([
            new CriticalAlertVO(
                type: AlertType::STAMINA_CRISIS,
                message: 'Stamina critically low',
                actionItems: ['Focus on stamina training'],
                turnsUntilCritical: 3,
                storageMode: 'account',
            ),
        ]);

        // Persist first time
        $count1 = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );
        expect($count1)->toBe(1);

        // Try to persist again - should skip duplicate
        $count2 = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );
        expect($count2)->toBe(0);

        // Should still only have one alert
        $persisted = CriticalAlert::where('career_id', $this->career->id)->get();
        expect($persisted)->toHaveCount(1);
    });

    it('allows same alert type on different turns', function () {
        $alerts = new \App\Collections\CriticalAlertCollection([
            new CriticalAlertVO(
                type: AlertType::STAMINA_CRISIS,
                message: 'Stamina critically low',
                actionItems: ['Focus on stamina training'],
                turnsUntilCritical: 3,
                storageMode: 'account',
            ),
        ]);

        // Persist for turn 15
        $count1 = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );
        expect($count1)->toBe(1);

        // Persist for turn 16 - should succeed
        $count2 = $this->service->persistCriticalAlerts(
            $this->career->id,
            16,
            $alerts
        );
        expect($count2)->toBe(1);

        // Should have two alerts
        $persisted = CriticalAlert::where('career_id', $this->career->id)->get();
        expect($persisted)->toHaveCount(2);
    });

    it('handles empty alert collection', function () {
        $alerts = new \App\Collections\CriticalAlertCollection([]);

        $count = $this->service->persistCriticalAlerts(
            $this->career->id,
            15,
            $alerts
        );

        expect($count)->toBe(0);

        $persisted = CriticalAlert::where('career_id', $this->career->id)->get();
        expect($persisted)->toHaveCount(0);
    });

    it('handles persistence errors gracefully', function () {
        // Create an invalid career ID to trigger error
        $alerts = new \App\Collections\CriticalAlertCollection([
            new CriticalAlertVO(
                type: AlertType::STAMINA_CRISIS,
                message: 'Test',
                actionItems: ['Test'],
                turnsUntilCritical: 0,
                storageMode: 'account',
            ),
        ]);

        // Use non-existent career ID
        $count = $this->service->persistCriticalAlerts(
            999999,
            15,
            $alerts
        );

        // Should return 0 on error
        expect($count)->toBe(0);
    });
});

describe('dismissCriticalAlert', function () {
    it('dismisses an alert successfully', function () {
        $alert = CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'dismissed_at' => null,
        ]);

        $result = $this->service->dismissCriticalAlert($alert->id);

        expect($result)->toBeTrue();

        $alert->refresh();
        expect($alert->was_dismissed)->toBeTrue();
        expect($alert->dismissed_at)->not->toBeNull();
    });

    it('returns false for non-existent alert', function () {
        $result = $this->service->dismissCriticalAlert(999999);

        expect($result)->toBeFalse();
    });

    it('can dismiss already dismissed alert', function () {
        $alert = CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => true,
            'dismissed_at' => now()->subHour(),
        ]);

        $result = $this->service->dismissCriticalAlert($alert->id);

        expect($result)->toBeTrue();
        $alert->refresh();
        expect($alert->was_dismissed)->toBeTrue();
    });
});

describe('reactivateCriticalAlert', function () {
    it('reactivates a dismissed alert successfully', function () {
        $alert = CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => true,
            'dismissed_at' => now()->subHour(),
        ]);

        $result = $this->service->reactivateCriticalAlert($alert->id);

        expect($result)->toBeTrue();

        $alert->refresh();
        expect($alert->was_dismissed)->toBeFalse();
        expect($alert->dismissed_at)->toBeNull();
    });

    it('returns false for non-existent alert', function () {
        $result = $this->service->reactivateCriticalAlert(999999);

        expect($result)->toBeFalse();
    });

    it('can reactivate already active alert', function () {
        $alert = CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'dismissed_at' => null,
        ]);

        $result = $this->service->reactivateCriticalAlert($alert->id);

        expect($result)->toBeTrue();
        $alert->refresh();
        expect($alert->was_dismissed)->toBeFalse();
    });
});

describe('getActiveCriticalAlerts', function () {
    it('returns only active alerts', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'turns_until_critical' => 0,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => true,
            'dismissed_at' => now(),
            'turns_until_critical' => 3,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'turns_until_critical' => 5,
        ]);

        $alerts = $this->service->getActiveCriticalAlerts($this->career->id);

        expect($alerts)->toHaveCount(2);
        expect($alerts->every(fn ($alert) => ! $alert->was_dismissed))->toBeTrue();
    });

    it('orders alerts by urgency', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'turns_until_critical' => 5,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'turns_until_critical' => 0,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
            'turns_until_critical' => 2,
        ]);

        $alerts = $this->service->getActiveCriticalAlerts($this->career->id);

        expect($alerts)->toHaveCount(3);
        // Most urgent (0 turns) should be first
        expect($alerts->first()->turns_until_critical)->toBe(0);
        // Least urgent (5 turns) should be last
        expect($alerts->last()->turns_until_critical)->toBe(5);
    });

    it('returns empty collection when no active alerts', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => true,
            'dismissed_at' => now(),
        ]);

        $alerts = $this->service->getActiveCriticalAlerts($this->career->id);

        expect($alerts)->toHaveCount(0);
    });

    it('filters by career ID', function () {
        $otherCareer = Career::factory()->create();

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'was_dismissed' => false,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $otherCareer->id,
            'was_dismissed' => false,
        ]);

        $alerts = $this->service->getActiveCriticalAlerts($this->career->id);

        expect($alerts)->toHaveCount(1);
        expect($alerts->first()->career_id)->toBe($this->career->id);
    });
});

describe('getCriticalAlertsForTurn', function () {
    it('returns all alerts for specific turn', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
            'was_dismissed' => false,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
            'was_dismissed' => true,
            'dismissed_at' => now(),
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 16,
            'was_dismissed' => false,
        ]);

        $alerts = $this->service->getCriticalAlertsForTurn($this->career->id, 15);

        expect($alerts)->toHaveCount(2);
        expect($alerts->every(fn ($alert) => $alert->turn_number === 15))->toBeTrue();
    });

    it('includes both active and dismissed alerts', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
            'was_dismissed' => false,
        ]);

        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
            'was_dismissed' => true,
            'dismissed_at' => now(),
        ]);

        $alerts = $this->service->getCriticalAlertsForTurn($this->career->id, 15);

        expect($alerts)->toHaveCount(2);
        expect($alerts->where('was_dismissed', false))->toHaveCount(1);
        expect($alerts->where('was_dismissed', true))->toHaveCount(1);
    });

    it('returns empty collection when no alerts for turn', function () {
        CriticalAlert::factory()->create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
        ]);

        $alerts = $this->service->getCriticalAlertsForTurn($this->career->id, 20);

        expect($alerts)->toHaveCount(0);
    });
});

describe('integration with detectCriticalSituations', function () {
    it('can detect and persist alerts in one workflow', function () {
        $context = new TrainingContext(
            turnNumber: 35,
            phase: \App\Enums\CareerPhase::CLASSIC,
            stats: new CharacterStats(
                speed: 450,
                stamina: 320, // Low stamina
                power: 420,
                guts: 350,
                wisdom: 400
            ),
            spAvailable: 180,
            energy: 35, // Low energy
            mood: Mood::NORMAL,
            acquiredSkills: [],
            skillHints: [],
            deck: new SupportCardDeck([]),
            facilityLevels: [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            upcomingRaces: [
                ['distance' => 'medium', 'turn' => 38],
            ],
            scenario: null,
            storageMode: 'account',
            careerRunId: $this->career->id,
        );

        // Detect critical situations
        $alerts = $this->service->detectCriticalSituations($context);

        expect($alerts)->not->toBeEmpty();

        // Persist the alerts
        $count = $this->service->persistCriticalAlerts(
            $this->career->id,
            $context->turnNumber,
            $alerts
        );

        expect($count)->toBeGreaterThan(0);

        // Verify alerts are in database
        $persisted = CriticalAlert::where('career_id', $this->career->id)
            ->where('turn_number', 35)
            ->get();

        expect($persisted)->toHaveCount($count);
    });
});
