<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\SupportCardDefinition;
use App\Services\DeckManagementService;
use App\Services\DeckOptimizationService;
use App\Services\FriendshipBondService;
use App\Services\SupportCardMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportCardController extends Controller
{
    public function __construct(
        protected DeckManagementService $deckService,
        protected DeckOptimizationService $optimizationService,
        protected FriendshipBondService $friendshipService,
        protected SupportCardMetaService $metaService
    ) {}

    /**
     * Display support cards collection with filters
     */
    public function index(Request $request): View
    {
        $query = SupportCardDefinition::query()->where('is_active', true);

        // Filter by card type
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (is_string($type)) {
                $query->where('card_type', $type);
            }
        }

        // Filter by rarity
        if ($request->filled('rarity')) {
            $rarity = $request->input('rarity');
            if (is_string($rarity)) {
                $query->where('rarity', $rarity);
            }
        }

        // Filter by meta tier
        if ($request->filled('tier')) {
            $tier = $request->input('tier');
            if (is_string($tier)) {
                $query->where('meta_tier', $tier);
            }
        }

        // Search by name or character
        if ($request->filled('search')) {
            $search = $request->input('search');
            if (is_string($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('character_name', 'like', '%'.$search.'%');
                });
            }
        }

        // Sorting
        $sortField = $request->input('sort', 'meta_tier');
        $sortDirection = $request->input('direction', 'asc');

        $allowedSorts = ['name', 'rarity', 'meta_tier', 'usage_rate', 'card_type'];
        if (is_string($sortField) && in_array($sortField, $allowedSorts) && is_string($sortDirection)) {
            // Custom sort for meta tier to maintain S+, S, A, B, C order
            if ($sortField === 'meta_tier') {
                $query->orderByRaw("CASE meta_tier WHEN 'S+' THEN 1 WHEN 'S' THEN 2 WHEN 'A' THEN 3 WHEN 'B' THEN 4 WHEN 'C' THEN 5 ELSE 6 END");
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $cards = $query->paginate(24)->withQueryString();

        // Get filter options
        $cardTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'];
        $rarities = ['SSR', 'SR', 'R'];
        $tiers = ['S+', 'S', 'A', 'B', 'C'];

        return view('support-cards.index', compact('cards', 'cardTypes', 'rarities', 'tiers'));
    }

    /**
     * Display detailed card information
     */
    public function show(SupportCardDefinition $supportCard): View
    {
        return view('support-cards.show', compact('supportCard'));
    }

    /**
     * Display deck builder for a character
     */
    public function deckBuilder(Character $character): View
    {
        $character->load('supportCards.supportCard');

        $availableCards = SupportCardDefinition::where('is_active', true)
            ->orderByRaw("CASE meta_tier WHEN 'S+' THEN 1 WHEN 'S' THEN 2 WHEN 'A' THEN 3 WHEN 'B' THEN 4 WHEN 'C' THEN 5 ELSE 6 END")
            ->orderBy('rarity', 'desc')
            ->get();

        $currentDeck = $this->deckService->getDeck($character->id);

        // Get comprehensive deck analysis
        $deckAnalysis = null;
        $friendshipOverview = null;
        $deckStatistics = null;

        if ($currentDeck->count() > 0) {
            $deckAnalysis = $this->optimizationService->getComprehensiveAnalysis($character->id);
            $friendshipOverview = $this->friendshipService->getDeckFriendshipOverview($character->id);
            $deckStatistics = $this->deckService->getDeckStatistics($character->id);
        }

        // Get cards by meta tier for recommendations
        $cardsByTier = $this->metaService->getCardsByTier();

        return view('support-cards.deck-builder', compact(
            'character',
            'availableCards',
            'currentDeck',
            'deckAnalysis',
            'friendshipOverview',
            'deckStatistics',
            'cardsByTier'
        ));
    }

    /**
     * Add card to deck (API endpoint)
     */
    public function addCardToDeck(Request $request, Character $character): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'support_card_id' => 'required|integer|exists:ucp_support_cards,id',
            'position_slot' => 'required|integer|min:1|max:6',
            'is_friend_card' => 'boolean',
            'limit_break_level' => 'integer|min:0|max:4',
        ]);

        try {
            $card = $this->deckService->addCardToDeck(
                $character->id,
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
                    'deck' => $this->deckService->getDeck($character->id),
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove card from deck (API endpoint)
     */
    public function removeCardFromDeck(Character $character, int $position): \Illuminate\Http\JsonResponse
    {
        try {
            $success = $this->deckService->removeCardFromDeck($character->id, $position);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card removed from deck successfully',
                    'data' => [
                        'deck' => $this->deckService->getDeck($character->id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Card not found at specified position',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update card in deck (API endpoint)
     */
    public function updateCardInDeck(Request $request, Character $character, int $position): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'support_card_id' => 'required|integer|exists:ucp_support_cards,id',
            'limit_break_level' => 'integer|min:0|max:4',
        ]);

        try {
            $card = $this->deckService->replaceCard(
                $character->id,
                $position,
                $validated['support_card_id'],
                $validated['limit_break_level'] ?? 0
            );

            if ($card) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card updated successfully',
                    'data' => [
                        'card' => $card->load('supportCard'),
                        'deck' => $this->deckService->getDeck($character->id),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update card',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear entire deck (API endpoint)
     */
    public function clearDeck(Character $character): \Illuminate\Http\JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Swap two cards in the deck (API endpoint)
     */
    public function swapCards(Request $request, Character $character): \Illuminate\Http\JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize deck (API endpoint)
     */
    public function optimizeDeck(Request $request, Character $character): \Illuminate\Http\JsonResponse
    {
        try {
            $options = $request->input('options', []);
            $options = is_array($options) ? $options : [];
            $recommendations = $this->optimizationService->recommendDeck($character->id, $options);

            return response()->json([
                'success' => true,
                'message' => 'Deck optimization recommendations generated',
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save entire deck (API endpoint)
     */
    public function saveDeck(Request $request, Character $character): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'cards' => 'required|array|min:1|max:6',
            'cards.*.support_card_id' => 'required|integer|exists:ucp_support_cards,id',
            'cards.*.position_slot' => 'required|integer|min:1|max:6',
            'cards.*.is_friend_card' => 'boolean',
            'cards.*.limit_break_level' => 'integer|min:0|max:4',
        ]);

        try {
            // Clear existing deck first
            $this->deckService->clearDeck($character->id);

            // Add each card
            foreach ($validated['cards'] as $cardData) {
                $this->deckService->addCardToDeck(
                    $character->id,
                    $cardData['support_card_id'],
                    $cardData['position_slot'],
                    $cardData['is_friend_card'] ?? false,
                    $cardData['limit_break_level'] ?? 0
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Deck saved successfully',
                'data' => [
                    'deck' => $this->deckService->getDeck($character->id),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
