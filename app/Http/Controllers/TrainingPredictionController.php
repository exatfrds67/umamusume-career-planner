<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Training Prediction UI Controller
 *
 * Handles the frontend interface for training predictions with:
 * - Agent visualization and workflow display
 * - Stat gains and energy cost predictions
 * - Spirit Burst indicators for Unity Cup
 * - Recommendation rankings with reasoning
 */
class TrainingPredictionController extends Controller
{
    /**
     * Display training prediction interface for a character
     */
    public function index(Request $request): View
    {
        $characters = Character::with(['aptitudes', 'supportCards'])
            ->orderBy('name')
            ->get();

        $selectedCharacter = null;
        if ($request->has('character_id')) {
            $selectedCharacter = Character::with([
                'aptitudes',
                'supportCards.supportCard',
                'factors',
            ])->find($request->input('character_id'));
        }

        return view('training.predictions', [
            'characters' => $characters,
            'selectedCharacter' => $selectedCharacter,
            'trainingTypes' => $this->getTrainingTypes(),
        ]);
    }

    /**
     * Show detailed prediction for a specific character
     */
    public function show(Character $character): View
    {
        $character->load([
            'aptitudes',
            'supportCards.supportCard',
            'factors',
        ]);

        return view('training.show', [
            'character' => $character,
            'trainingTypes' => $this->getTrainingTypes(),
        ]);
    }

    /**
     * Get available training types
     *
     * @return array<string, array<string, mixed>>
     */
    private function getTrainingTypes(): array
    {
        return [
            'speed' => [
                'name' => 'Speed Training',
                'icon' => '⚡',
                'color' => 'blue',
                'description' => 'Increases top speed capability',
                'priority' => 5,
            ],
            'stamina' => [
                'name' => 'Stamina Training',
                'icon' => '💪',
                'color' => 'green',
                'description' => 'Extends duration at top speed',
                'priority' => 4,
            ],
            'power' => [
                'name' => 'Power Training',
                'icon' => '🔥',
                'color' => 'red',
                'description' => 'Improves acceleration rate',
                'priority' => 3,
            ],
            'guts' => [
                'name' => 'Guts Training',
                'icon' => '💎',
                'color' => 'purple',
                'description' => 'Enhances final phase performance',
                'priority' => 1,
            ],
            'wit' => [
                'name' => 'Wit Training',
                'icon' => '🧠',
                'color' => 'yellow',
                'description' => 'Boosts skill activation and positioning',
                'priority' => 2,
            ],
            'rest' => [
                'name' => 'Rest',
                'icon' => '😴',
                'color' => 'gray',
                'description' => 'Recovers energy and reduces failure risk',
                'priority' => 0,
            ],
        ];
    }
}
