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
                $query->orderByRaw("FIELD(meta_tier, 'S+', 'S', 'A', 'B', 'C')");
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $cards = $query->paginate(24);

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
            ->orderByRaw("FIELD(meta_tier, 'S+', 'S', 'A', 'B', 'C')")
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
}
