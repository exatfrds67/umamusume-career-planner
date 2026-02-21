<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Enums\AlertType;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Livewire\AdvisoryPanel;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CriticalAlert;
use App\ValueObjects\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire as LivewireFacade;
use Tests\TestCase;

class AdvisoryPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test component can be mounted
     */
    public function test_component_can_be_mounted(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.advisory-panel');
    }

    /**
     * Test component starts with panel closed
     */
    public function test_component_starts_with_panel_closed(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->assertSet('isOpen', false);
    }

    /**
     * Test toggle panel opens and closes
     */
    public function test_toggle_panel_opens_and_closes(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->assertSet('isOpen', false)
            ->call('togglePanel')
            ->assertSet('isOpen', true)
            ->call('togglePanel')
            ->assertSet('isOpen', false);
    }

    /**
     * Test open panel event
     */
    public function test_open_panel_event(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->assertSet('isOpen', false)
            ->dispatch('open-advisory-panel')
            ->assertSet('isOpen', true)
            ->assertDispatched('advisory-panel-opened');
    }

    /**
     * Test close panel event
     */
    public function test_close_panel_event(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->set('isOpen', true)
            ->dispatch('close-advisory-panel')
            ->assertSet('isOpen', false)
            ->assertDispatched('advisory-panel-closed');
    }

    /**
     * Test toggle section visibility
     */
    public function test_toggle_section_visibility(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->assertSet('showCriticalAlerts', true)
            ->call('toggleSection', 'criticalAlerts')
            ->assertSet('showCriticalAlerts', false)
            ->call('toggleSection', 'criticalAlerts')
            ->assertSet('showCriticalAlerts', true);
    }

    /**
     * Test dismiss alert adds to dismissed list
     */
    public function test_dismiss_alert_adds_to_dismissed_list(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->call('dismissAlert', 'stamina_crisis')
            ->assertSet('dismissedAlerts', ['stamina_crisis'])
            ->assertDispatched('alert-dismissed');
    }

    /**
     * Test dismiss recommendation adds to dismissed list
     */
    public function test_dismiss_recommendation_adds_to_dismissed_list(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->call('dismissRecommendation', 'speed_training')
            ->assertSet('dismissedRecommendations', ['speed_training'])
            ->assertDispatched('recommendation-dismissed');
    }

    /**
     * Test reactivate alert removes from dismissed list
     */
    public function test_reactivate_alert_removes_from_dismissed_list(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->set('dismissedAlerts', ['stamina_crisis', 'energy_critical'])
            ->call('reactivateAlert', 'stamina_crisis')
            ->assertSet('dismissedAlerts', ['energy_critical'])
            ->assertDispatched('alert-reactivated');
    }

    /**
     * Test clear dismissed clears all dismissed items
     */
    public function test_clear_dismissed_clears_all_dismissed_items(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->set('dismissedAlerts', ['stamina_crisis'])
            ->set('dismissedRecommendations', ['speed_training'])
            ->call('clearDismissed')
            ->assertSet('dismissedAlerts', [])
            ->assertSet('dismissedRecommendations', [])
            ->assertDispatched('dismissed-cleared');
    }

    /**
     * Test refresh clears cached data
     */
    public function test_refresh_clears_cached_data(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->dispatch('refresh-advisory')
            ->assertDispatched('advisory-refreshed');
    }

    /**
     * Test component displays empty state when no content
     */
    public function test_component_displays_empty_state_when_no_content(): void
    {
        LivewireFacade::test(AdvisoryPanel::class)
            ->set('isOpen', true)
            ->assertSee('No critical alerts or recommendations at this time');
    }

    /**
     * Test component displays critical alerts
     */
    public function test_component_displays_critical_alerts(): void
    {
        // Mock the TrainingAdvisoryService
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $alert = new CriticalAlert(
            type: AlertType::STAMINA_CRISIS,
            priority: Priority::CRITICAL,
            message: 'Stamina critically low',
            actionItems: ['Focus on Stamina training'],
            turnsUntilCritical: 3
        );

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([$alert]));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 320,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->set('isOpen', true)
            ->assertSee('Critical Alerts')
            ->assertSee('Stamina critically low');
    }

    /**
     * Test component displays training recommendations
     */
    public function test_component_displays_training_recommendations(): void
    {
        // Mock the TrainingAdvisoryService
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $recommendation = new Recommendation(
            type: RecommendationType::TRAINING_FACILITY,
            priority: Priority::HIGH,
            action: 'Speed Training',
            reasoning: '3 support cards present',
            expectedOutcomes: ['+45-55 Speed'],
            risks: ['5% failure rate'],
            confidenceScore: 0.92
        );

        $mockService->shouldReceive('getTrainingRecommendations')
            ->andReturn(new RecommendationCollection([$recommendation]));

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([]));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->set('isOpen', true)
            ->assertSee('Training Recommendations')
            ->assertSee('Speed Training')
            ->assertSee('3 support cards present');
    }

    /**
     * Test dismissed alerts are filtered from display
     */
    public function test_dismissed_alerts_are_filtered_from_display(): void
    {
        // Mock the TrainingAdvisoryService
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $alert1 = new CriticalAlert(
            type: AlertType::STAMINA_CRISIS,
            priority: Priority::CRITICAL,
            message: 'Stamina critically low',
            actionItems: ['Focus on Stamina training'],
            turnsUntilCritical: 3
        );

        $alert2 = new CriticalAlert(
            type: AlertType::ENERGY_CRITICAL,
            priority: Priority::HIGH,
            message: 'Energy at 35',
            actionItems: ['Rest immediately'],
            turnsUntilCritical: 0
        );

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([$alert1, $alert2]));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 320,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 35,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->set('isOpen', true)
            ->assertSee('Stamina critically low')
            ->assertSee('Energy at 35')
            ->call('dismissAlert', 'stamina_crisis')
            ->assertDontSee('Stamina critically low')
            ->assertSee('Energy at 35');
    }

    /**
     * Test session key is different for local and account modes
     */
    public function test_session_key_is_different_for_local_and_account_modes(): void
    {
        $localComponent = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
        ]);

        $accountComponent = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'account',
            'careerRunId' => 123,
        ]);

        // Dismiss an alert in local mode
        $localComponent->call('dismissAlert', 'test_alert');

        // Verify it doesn't affect account mode
        $accountComponent->assertSet('dismissedAlerts', []);
    }

    /**
     * Test component works in both local and account storage modes
     */
    public function test_component_works_in_both_storage_modes(): void
    {
        // Test local mode
        LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
        ])
            ->assertSet('storageMode', 'local')
            ->assertStatus(200);

        // Test account mode
        LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'account',
            'careerRunId' => 123,
        ])
            ->assertSet('storageMode', 'account')
            ->assertSet('careerRunId', 123)
            ->assertStatus(200);
    }

    /**
     * Test critical alert count is accurate
     */
    public function test_critical_alert_count_is_accurate(): void
    {
        // Mock the TrainingAdvisoryService
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $alerts = [
            new CriticalAlert(
                type: AlertType::STAMINA_CRISIS,
                priority: Priority::CRITICAL,
                message: 'Alert 1',
                actionItems: [],
                turnsUntilCritical: 3
            ),
            new CriticalAlert(
                type: AlertType::ENERGY_CRITICAL,
                priority: Priority::HIGH,
                message: 'Alert 2',
                actionItems: [],
                turnsUntilCritical: 0
            ),
        ];

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection($alerts));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 320,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 35,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        // getCriticalAlertCount() is a method, not a Livewire property.
        // Call it directly on the component instance.
        expect($component->instance()->getCriticalAlertCount())->toBe(2); // @phpstan-ignore method.notFound
    }

    /**
     * Test has content returns true when alerts or recommendations exist
     */
    public function test_has_content_returns_true_when_content_exists(): void
    {
        // Mock the TrainingAdvisoryService
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $alert = new CriticalAlert(
            type: AlertType::STAMINA_CRISIS,
            priority: Priority::CRITICAL,
            message: 'Stamina critically low',
            actionItems: [],
            turnsUntilCritical: 3
        );

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([$alert]));

        $mockService->shouldReceive('getTrainingRecommendations')
            ->andReturn(new RecommendationCollection([]));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 320,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        // hasContent() is a method, not a Livewire property.
        // Call it directly on the component instance.
        expect($component->instance()->hasContent())->toBeTrue(); // @phpstan-ignore method.notFound
    }
}
