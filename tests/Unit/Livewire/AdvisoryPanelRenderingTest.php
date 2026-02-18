<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire;

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Livewire\AdvisoryPanel;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Advisory Panel Rendering Performance Test
 *
 * Tests that the advisory panel component renders efficiently
 * and handles state changes without unnecessary re-renders.
 */
class AdvisoryPanelRenderingTest extends TestCase
{
    /**
     * Test panel does not fetch data when closed
     */
    public function test_panel_does_not_fetch_data_when_closed(): void
    {
        // Mock the advisory service to track calls
        $serviceCalled = false;
        $this->mock(\App\Services\TrainingAdvisoryService::class, function ($mock) use (&$serviceCalled) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturnUsing(function () use (&$serviceCalled) {
                    $serviceCalled = true;

                    return new CriticalAlertCollection([]);
                });
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturnUsing(function () use (&$serviceCalled) {
                    $serviceCalled = true;

                    return new RecommendationCollection([]);
                });
        });

        // Render component with panel closed
        Livewire::test(AdvisoryPanel::class, [
            'isOpen' => false,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->assertSet('isOpen', false);

        // Service should not be called when panel is closed
        $this->assertFalse($serviceCalled, 'Advisory service should not be called when panel is closed');
    }

    /**
     * Test toggle operations are fast
     */
    public function test_toggle_operations_are_fast(): void
    {
        $this->mock(\App\Services\TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([]));
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturn(new RecommendationCollection([]));
        });

        $component = Livewire::test(AdvisoryPanel::class, [
            'isOpen' => false,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        // Measure toggle time
        $startTime = microtime(true);

        $component->call('togglePanel');

        $duration = (microtime(true) - $startTime) * 1000;

        // Toggle should be instant (< 50ms)
        $this->assertLessThan(50, $duration, 'Toggle should complete within 50ms');

        $component->assertSet('isOpen', true);
    }

    /**
     * Test section toggle is efficient
     */
    public function test_section_toggle_is_efficient(): void
    {
        $this->mock(\App\Services\TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([]));
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturn(new RecommendationCollection([]));
        });

        $component = Livewire::test(AdvisoryPanel::class, [
            'isOpen' => true,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        // Measure section toggle time
        $startTime = microtime(true);

        $component->call('toggleSection', 'criticalAlerts');

        $duration = (microtime(true) - $startTime) * 1000;

        // Section toggle should be fast (< 500ms including Livewire overhead)
        $this->assertLessThan(500, $duration, 'Section toggle should complete within 500ms');
    }

    /**
     * Test dismissal updates are efficient
     */
    public function test_dismissal_updates_are_efficient(): void
    {
        $this->mock(\App\Services\TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([]));
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturn(new RecommendationCollection([]));
        });

        // Measure dismissal time
        $startTime = microtime(true);

        Livewire::test(AdvisoryPanel::class, [
            'isOpen' => true,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->call('dismissAlert', 'test-alert-id');

        $duration = (microtime(true) - $startTime) * 1000;

        // Dismissal should complete quickly (< 100ms)
        $this->assertLessThan(100, $duration, 'Dismissal should complete within 100ms');
    }

    /**
     * Test component renders without errors
     */
    public function test_component_renders_without_errors(): void
    {
        $this->mock(\App\Services\TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([]));
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturn(new RecommendationCollection([]));
        });

        Livewire::test(AdvisoryPanel::class, [
            'isOpen' => true,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ])
            ->assertStatus(200)
            ->assertSee('AI Advisory');
    }
}
