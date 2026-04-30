<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\TrainingPredictionService;
use App\Services\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * Display the training page for a character.
     */
    public function index(Character $character): View
    {
        $this->authorize('view', $character);

        $predictions = $this->predictionService->getPredictions($character);
        /** @var array<string, array{base_gains?: array<string, int>, final_gains?: array<string, int>}> $facilitiesData */
        $facilitiesData = $predictions['predictions'] ?? [];

        $trainingData = [];
        foreach ($facilitiesData as $type => $prediction) {
            $trainingData[$type] = [
                'gains' => $prediction['final_gains'] ?? $prediction['base_gains'] ?? [],
                'failure_rate' => $this->calculateFailureRate($character, (string) $type),
                'energy_cost' => $this->getEnergyCost((string) $type),
            ];
        }

        $recommended = $this->predictionService->getRecommendedTraining($character);
        $recommendedFacility = $recommended['recommended_facility'] ?? null;
        $recommendedReason = $recommended['reason'] ?? null;

        return view('training.index', [
            'character' => $character,
            'trainingData' => $trainingData,
            'recommendedFacility' => $recommendedFacility,
            'recommendedReason' => $recommendedReason,
        ]);
    }

    /**
     * Store a training execution from the web form.
     */
    public function store(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        // Validate turn limit
        if ($character->current_turn >= 78) {
            return redirect()
                ->route('characters.show', $character)
                ->with('error', 'Career has reached maximum turns. The career run is complete.');
        }

        $validated = $request->validate([
            'training_type' => 'required|string|in:speed,stamina,power,guts,wit',
        ]);

        $predictions = $this->predictionService->getPredictions($character);
        /** @var array<string, array{base_gains?: array<string, int>, final_gains?: array<string, int>}> $facilitiesData */
        $facilitiesData = $predictions['predictions'] ?? [];
        /** @var array{base_gains?: array<string, int>, final_gains?: array<string, int>}|null $prediction */
        $prediction = $facilitiesData[$validated['training_type']] ?? null;

        /** @var array<string, int> $gains */
        $gains = $prediction['final_gains'] ?? $prediction['base_gains'] ?? [];

        $result = $this->trainingService->executeTraining(
            $character,
            $validated['training_type'],
            $gains
        );

        if (! ($result['success'] ?? false)) {
            $message = $result['message'] ?? 'Training failed. Please try again.';

            return redirect()
                ->route('training.index', $character)
                ->with('error', $message);
        }

        $message = ucfirst($validated['training_type']).' training completed successfully!';

        if ($result['training_failed'] ?? false) {
            $message = ucfirst($validated['training_type']).' training failed! Stats were reduced due to low energy.';
        }

        if ($result['career_completed'] ?? false) {
            return redirect()
                ->route('characters.show', $character)
                ->with('success', 'Career run completed! Final stats have been recorded.');
        }

        return redirect()
            ->route('training.index', $character)
            ->with($result['training_failed'] ? 'warning' : 'success', $message);
    }

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

    /**
     * Calculate failure rate based on character energy level.
     */
    private function calculateFailureRate(Character $character, string $trainingType): int
    {
        $energyLevel = $character->energy_level ?? 100;

        if ($energyLevel >= 50) {
            return 0;
        }

        if ($energyLevel >= 30) {
            return 20;
        }

        return 40;
    }

    /**
     * Get the energy cost for a training type.
     */
    private function getEnergyCost(string $trainingType): int
    {
        $costs = [
            'speed' => -20,
            'stamina' => -20,
            'power' => -20,
            'guts' => -20,
            'wit' => -10,
        ];

        return $costs[$trainingType] ?? -20;
    }
}
