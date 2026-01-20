<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportDeckRequest;
use App\Http\Resources\SupportCardResource;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Services\SupportDeckService;
use App\Services\SynergyScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportDeckController extends Controller
{
    public function __construct(
        protected SupportDeckService $deckService,
        protected SynergyScorer $synergyScorer
    ) {}

    /**
     * Save deck configuration for a character
     */
    public function save(StoreSupportDeckRequest $request, Character $character): JsonResponse
    {
        $cards = $request->validated()['cards'];

        $success = $this->deckService->saveDeck($character, $cards);

        if ($success) {
            $character->load('supportCards.supportCard');

            return response()->json([
                'message' => 'Deck saved successfully',
                'deck' => SupportCardResource::collection($character->supportCards),
                'synergy_score' => $this->synergyScorer->calculateDeckScore($character),
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to save deck',
            'errors' => ['deck' => ['Deck validation failed']],
        ], 422);
    }

    /**
     * Get current deck configuration
     */
    public function show(Character $character): JsonResponse
    {
        $character->load('supportCards.supportCard');

        return response()->json([
            'deck' => SupportCardResource::collection($character->supportCards),
            'synergy_score' => $this->synergyScorer->calculateDeckScore($character),
            'tier_rating' => $this->deckService->calculateDeckTier($character),
        ]);
    }

    /**
     * Clear all cards from deck
     */
    public function clear(Character $character): JsonResponse
    {
        $character->supportCards()->delete();

        return response()->json([
            'message' => 'Deck cleared successfully',
        ]);
    }

    /**
     * Validate deck composition without saving
     */
    public function validateDeck(Request $request, Character $character): JsonResponse
    {
        $cards = $request->input('cards', []);

        $validation = $this->deckService->validateDeck($cards);

        return response()->json([
            'valid' => $validation['valid'],
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
        ]);
    }

    /**
     * Calculate synergy score for current deck
     */
    public function synergy(Character $character): JsonResponse
    {
        $character->load('supportCards.supportCard');

        $score = $this->synergyScorer->calculateDeckScore($character);

        return response()->json([
            'synergy_score' => $score,
            'tier_rating' => $this->deckService->calculateDeckTier($character),
        ]);
    }

    /**
     * Get deck recommendations based on character goals
     */
    public function recommendations(Request $request, Character $character): JsonResponse
    {
        $focusStat = $request->input('focus_stat');

        $recommendations = $this->deckService->getRecommendations($character, $focusStat);

        return response()->json([
            'recommendations' => SupportCardResource::collection($recommendations),
            'focus_stat' => $focusStat,
        ]);
    }

    /**
     * Update individual card in deck (limit break, friendship level)
     */
    public function updateCard(Request $request, Character $character, CharacterSupportCard $card): JsonResponse
    {
        $validated = $request->validate([
            'limit_break_level' => 'sometimes|integer|min:0|max:4',
            'friendship_level' => 'sometimes|integer|min:0|max:100',
        ]);

        if ($card->character_id !== $character->id) {
            return response()->json([
                'message' => 'Card does not belong to this character',
            ], 403);
        }

        $card->update($validated);

        return response()->json([
            'message' => 'Card updated successfully',
            'card' => new SupportCardResource($card),
        ]);
    }
}
