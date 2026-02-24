<?php

declare(strict_types=1);

use App\Livewire\Simulation\SimulationWizard;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders the simulation wizard component', function () {
    Livewire::test(SimulationWizard::class)
        ->assertStatus(200)
        ->assertSee('Configure Scenarios');
});

it('initializes with 2 default scenarios', function () {
    Livewire::test(SimulationWizard::class)
        ->assertSet('scenarioCount', 2)
        ->assertSet('step', 1)
        ->assertCount('scenarios', 2);
});

it('updates scenario count within valid range', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarioCount', 5)
        ->assertCount('scenarios', 5);
});

it('clamps scenario count to minimum of 2', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarioCount', 1)
        ->assertSet('scenarioCount', 2);
});

it('clamps scenario count to maximum of 10', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarioCount', 15)
        ->assertSet('scenarioCount', 10);
});

it('runs simulation and shows results', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarios.0.training_focus', 'speed')
        ->set('scenarios.0.scenario_type', 'ura_finale')
        ->set('scenarios.0.support_deck_bonus', 1.2)
        ->set('scenarios.0.speed', 800)
        ->set('scenarios.0.stamina', 600)
        ->set('scenarios.0.power', 700)
        ->set('scenarios.0.guts', 500)
        ->set('scenarios.0.wit', 600)
        ->set('scenarios.1.training_focus', 'stamina')
        ->set('scenarios.1.scenario_type', 'ura_finale')
        ->set('scenarios.1.support_deck_bonus', 1.0)
        ->set('scenarios.1.speed', 600)
        ->set('scenarios.1.stamina', 800)
        ->set('scenarios.1.power', 600)
        ->set('scenarios.1.guts', 600)
        ->set('scenarios.1.wit', 600)
        ->call('runSimulation')
        ->assertSet('step', 3)
        ->assertSee('Comparison Results');
});

it('validates training focus values', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarios.0.training_focus', 'invalid')
        ->call('runSimulation')
        ->assertHasErrors(['scenarios.0.training_focus']);
});

it('validates stat ranges', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarios.0.speed', 0)
        ->call('runSimulation')
        ->assertHasErrors(['scenarios.0.speed']);
});

it('validates support deck bonus range', function () {
    Livewire::test(SimulationWizard::class)
        ->set('scenarios.0.support_deck_bonus', 5.0)
        ->call('runSimulation')
        ->assertHasErrors(['scenarios.0.support_deck_bonus']);
});

it('resets wizard to initial state', function () {
    Livewire::test(SimulationWizard::class)
        ->call('runSimulation')
        ->call('resetWizard')
        ->assertSet('step', 1)
        ->assertSet('results', [])
        ->assertSet('report', []);
});

it('shows results table with best scenario highlighted', function () {
    Livewire::test(SimulationWizard::class)
        ->call('runSimulation')
        ->assertSet('step', 3)
        ->assertSee('Best Scenario');
});

it('requires authenticated access to simulation page', function () {
    $this->get(route('simulation.index'))
        ->assertRedirect('/');
});

it('allows authenticated user to access simulation page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('simulation.index'))
        ->assertSuccessful()
        ->assertSee('Batch Simulation');
});
