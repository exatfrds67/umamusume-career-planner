<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\TrainingPredictionService;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Training Controller
 *
 * Handles training predictions and execution.
 */
class TrainingController extends Controller
{
    public function __construct(
        protected TrainingPredictionService $predictionService,
        protected TrainingService $trainingService
    ) {}

    /**
     * Get training predictions for a character.
     */
    public function predictions(Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        $predictions = $this->predictionService->getPredictions($character);
        $recommended = $this->predictionService->getRecommendedTraining($character);

        return response()->json([
            'success' => true,
            'character_id' => $character->id,
            'character_name' => $character->name,
            'current_turn' => $character->current_turn,
            'predictions' => $predictions,
            'recommended' => $recommended,
        ]);
    }

    /**
     * Get prediction for a specific facility.
     */
    public function facilityPrediction(Character $character, string $facility): JsonResponse
    {
        $this->authorize('view', $character);

        $validFacilities = ['speed', 'stamina', 'power', 'guts', 'wit'];
        if (! in_array($facility, $validFacilities, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid facility type',
            ], 400);
        }

        $prediction = $this->predictionService->getPredictionForFacility($character, $facility);

        return response()->json([
            'success' => true,
            'character_id' => $character->id,
            'prediction' => $prediction,
        ]);
    }

    /**
     * Execute training.
     */
    public function execute(Request $request, Character $character): JsonResponse
    {
        $this->authorize('update', $character);

        $validated = $request->validate([
            'training_type' => 'required|string|in:speed,stamina,power,guts,wit',
            'actual_gains' => 'required|array',
            'actual_gains.speed' => 'integer|min:0',
            'actual_gains.stamina' => 'integer|min:0',
            'actual_gains.power' => 'integer|min:0',
            'actual_gains.guts' => 'integer|min:0',
            'actual_gains.wit' => 'integer|min:0',
            'actual_gains.sp' => 'integer|min:0',
        ]);

        $result = $this->trainingService->executeTraining(
            $character,
            $validated['training_type'],
            $validated['actual_gains']
        );

        return response()->json([
            'success' => true,
            'message' => 'Training completed successfully',
            'result' => $result,
        ]);
    }

    /**
     * Get character's active support deck.
     */
    public function deck(Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        $activeDeck = $character->activeSupportDeck()->with('supportCards')->first();

        if (! $activeDeck) {
            return response()->json([
                'success' => true,
                'has_deck' => false,
                'message' => 'No active support deck',
            ]);
        }

        return response()->json([
            'success' => true,
            'has_deck' => true,
            'deck' => [
                'id' => $activeDeck->id,
                'name' => $activeDeck->name,
                'cards' => $activeDeck->supportCards->map(function ($card) {
                    $pivot = $card->pivot;

                    return [
                        'id' => $card->id,
                        'name' => $card->name_en ?? $card->title_en ?? 'Unknown',
                        'rarity' => $card->rarity,
                        'position' => $pivot?->getAttribute('position') ?? 0,
                        'bond_level' => $pivot?->getAttribute('bond_level') ?? 0,
                        'is_borrowed' => $pivot?->getAttribute('is_borrowed') ?? false,
                    ];
                }),
            ],
        ]);
    }
}
