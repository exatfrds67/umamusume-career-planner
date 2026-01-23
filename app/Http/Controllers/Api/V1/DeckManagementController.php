<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Services\DeckManagementService;
use App\Services\DeckOptimizationService;
use App\Services\FriendshipBondService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeckManagementController extends Controller
{
    public function __construct(
        private DeckManagementService $deckService,
        private DeckOptimizationService $optimizationService,
        private FriendshipBondService $friendshipService
    ) {}

    /**
     * Get deck for a character
     */
    public function getDeck(int $id): JsonResponse
    {
        try {
            $deck = $this->deckService->getDeck($id);

            return response()->json([
                'success' => true,
                'data' => $deck,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get deck', [
                'character_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deck',
            ], 500);
        }
    }

    /**
     * Add card to deck
     */
    public function addCard(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'support_card_id' => 'required|integer|exists:ucp_support_cards,id',
            'position_slot' => 'required|integer|min:1|max:6',
            'is_friend_card' => 'boolean',
            'limit_break_level' => 'integer|min:0|max:4',
        ]);

        try {
            $card = $this->deckService->addCardToDeck(
                $id,
                $validated['support_card_id'],
                $validated['position_slot'],
                $validated['is_friend_card'] ?? false,
                $validated['limit_break_level'] ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Card added to deck successfully',
                'data' => [
                    'card' => $card->load('supportCard'),
                    'deck' => $this->deckService->getDeck($id),
                    'statistics' => $this->deckService->getDeckStatistics($id),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add card to deck', [
                'character_id' => $id,
                'support_card_id' => $validated['support_card_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add card to deck',
            ], 500);
        }
    }

    /**
     * Remove card from deck
     */
    public function removeCard(Request $request, Character $character): JsonResponse
    {
        $validated = $request->validate([
            'position_slot' => 'required|integer|min:1|max:6',
        ]);

        try {
            $success = $this->deckService->removeCardFromDeck(
                $character->id,
                $validated['position_slot']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card removed from deck successfully',
                    'data' => [
                        'deck' => $this->deckService->getDeck($character->id),
                        'statistics' => $this->deckService->getDeckStatistics($character->id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Card not found at specified position',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to remove card from deck', [
                'character_id' => $character->id,
                'position_slot' => $validated['position_slot'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove card from deck',
            ], 500);
        }
    }

    /**
     * Remove card from deck by slot (URL parameter)
     */
    public function removeCardBySlot(int $id, int $slot): JsonResponse
    {
        try {
            $success = $this->deckService->removeCardFromDeck($id, $slot);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card removed from deck successfully',
                    'data' => [
                        'deck' => $this->deckService->getDeck($id),
                        'statistics' => $this->deckService->getDeckStatistics($id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Card not found at specified position',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to remove card from deck', [
                'character_id' => $id,
                'position_slot' => $slot,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove card from deck',
            ], 500);
        }
    }

    /**
     * Swap two cards in the deck
     */
    public function swapCards(Request $request, Character $character): JsonResponse
    {
        $validated = $request->validate([
            'position1' => 'required|integer|min:1|max:6',
            'position2' => 'required|integer|min:1|max:6|different:position1',
        ]);

        try {
            $success = $this->deckService->swapCards(
                $character->id,
                $validated['position1'],
                $validated['position2']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cards swapped successfully',
                    'data' => [
                        'deck' => $this->deckService->getDeck($character->id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to swap cards',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Failed to swap cards', [
                'character_id' => $character->id,
                'position1' => $validated['position1'],
                'position2' => $validated['position2'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to swap cards',
            ], 500);
        }
    }

    /**
     * Replace a card in the deck
     */
    public function replaceCard(Request $request, Character $character): JsonResponse
    {
        $validated = $request->validate([
            'position_slot' => 'required|integer|min:1|max:6',
            'new_support_card_id' => 'required|integer|exists:ucp_support_cards,id',
            'limit_break_level' => 'integer|min:0|max:4',
        ]);

        try {
            $card = $this->deckService->replaceCard(
                $character->id,
                $validated['position_slot'],
                $validated['new_support_card_id'],
                $validated['limit_break_level'] ?? 0
            );

            if ($card) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card replaced successfully',
                    'data' => [
                        'card' => $card->load('supportCard'),
                        'deck' => $this->deckService->getDeck($character->id),
                        'statistics' => $this->deckService->getDeckStatistics($character->id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to replace card',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Failed to replace card', [
                'character_id' => $character->id,
                'position_slot' => $validated['position_slot'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to replace card',
            ], 500);
        }
    }

    /**
     * Clear entire deck
     */
    public function clearDeck(Character $character): JsonResponse
    {
        try {
            $success = $this->deckService->clearDeck($character->id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deck cleared successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear deck',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Failed to clear deck', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear deck',
            ], 500);
        }
    }

    /**
     * Get deck analysis
     */
    public function getAnalysis(Character $character): JsonResponse
    {
        try {
            $analysis = $this->optimizationService->getComprehensiveAnalysis($character->id);

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get deck analysis', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deck analysis',
            ], 500);
        }
    }

    /**
     * Get deck recommendations
     */
    public function getRecommendations(Request $request, Character $character): JsonResponse
    {
        try {
            $options = $request->input('options', []);
            $options = is_array($options) ? $options : [];
            if (! is_array($options)) {
                $options = [];
            }
            $recommendations = $this->optimizationService->recommendDeck($character->id, $options);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get deck recommendations', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deck recommendations',
            ], 500);
        }
    }

    /**
     * Update friendship level
     */
    public function updateFriendship(Request $request, Character $character): JsonResponse
    {
        $validated = $request->validate([
            'character_support_card_id' => 'required|integer|exists:character_support_cards,id',
            'points_gained' => 'integer|min:1|max:100',
        ]);

        try {
            $result = $this->friendshipService->updateFriendshipLevel(
                $validated['character_support_card_id'],
                $validated['points_gained'] ?? 5
            );

            return response()->json([
                'success' => true,
                'message' => 'Friendship level updated successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update friendship level', [
                'character_support_card_id' => $validated['character_support_card_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update friendship level',
            ], 500);
        }
    }

    /**
     * Get friendship overview
     */
    public function getFriendshipOverview(Character $character): JsonResponse
    {
        try {
            $overview = $this->friendshipService->getDeckFriendshipOverview($character->id);

            return response()->json([
                'success' => true,
                'data' => $overview,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get friendship overview', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve friendship overview',
            ], 500);
        }
    }
}
