<?php

declare(strict_types=1);

use App\Livewire\Dashboard\TrainingSuggestionPanel;
use Livewire\Livewire;

it('renders training suggestions when provided', function () {
    Livewire::test(TrainingSuggestionPanel::class, [
        'suggestions' => [
            ['action' => 'Speed Training', 'gains' => '+18 Speed, +5 Power', 'risk' => 'low'],
            ['action' => 'Stamina Training', 'gains' => '+16 Stamina, +4 Guts', 'risk' => 'medium'],
        ],
    ])
        ->assertStatus(200)
        ->assertSee('Training Suggestions')
        ->assertSee('Speed Training')
        ->assertSee('Stamina Training');
});

it('renders the empty state when no suggestions exist', function () {
    Livewire::test(TrainingSuggestionPanel::class, ['suggestions' => []])
        ->assertStatus(200)
        ->assertSee('No suggestions yet. Run a prediction to get AI-powered advice.');
});

it('selects and deselects a suggestion', function () {
    Livewire::test(TrainingSuggestionPanel::class, [
        'suggestions' => [
            ['action' => 'Speed Training', 'gains' => '+18 Speed, +5 Power', 'risk' => 'low'],
        ],
    ])
        ->assertSet('selectedIndex', null)
        ->call('selectSuggestion', 0)
        ->assertSet('selectedIndex', 0)
        ->call('selectSuggestion', 0)
        ->assertSet('selectedIndex', null);
});

it('clears the current suggestion selection', function () {
    Livewire::test(TrainingSuggestionPanel::class, [
        'suggestions' => [
            ['action' => 'Speed Training', 'gains' => '+18 Speed, +5 Power', 'risk' => 'low'],
        ],
    ])
        ->call('selectSuggestion', 0)
        ->assertSet('selectedIndex', 0)
        ->call('clearSelection')
        ->assertSet('selectedIndex', null);
});
