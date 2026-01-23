<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SupportCardResource;
use App\Models\SupportCardDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupportCardController extends Controller
{
    /**
     * Display a listing of support card definitions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SupportCardDefinition::query()->where('is_active', true);

        // Filter by card type
        if ($request->has('card_type')) {
            $query->where('card_type', $request->input('card_type'));
        }

        // Filter by rarity
        if ($request->has('rarity')) {
            $query->where('rarity', $request->input('rarity'));
        }

        // Filter by meta tier (handle URL encoding)
        if ($request->has('meta_tier')) {
            $metaTier = $request->input('meta_tier');
            // Handle both encoded and decoded versions
            $query->where('meta_tier', $metaTier);
        }

        // Search by name
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $search = is_string($searchTerm) ? $searchTerm : '';
            if ($search !== '') {
                $query->where('name', 'like', '%'.(is_string($search) ? $search : '').'%');
            }
        }

        $cards = $query->get();

        return SupportCardResource::collection($cards);
    }

    /**
     * Display the specified support card.
     */
    public function show(int $id): SupportCardResource|JsonResponse
    {
        $card = SupportCardDefinition::find($id);

        if (! $card) {
            return response()->json(['message' => 'Support card not found'], 404);
        }

        return new SupportCardResource($card);
    }

    /**
     * Get cards ranked by meta tier.
     */
    public function metaRanking(): JsonResponse
    {
        $cards = SupportCardDefinition::query()
            ->where('is_active', true)
            ->get()
            ->groupBy('meta_tier')
            ->map(fn ($group) => SupportCardResource::collection($group));

        return response()->json(['data' => $cards]);
    }

    /**
     * Get synergy data for a specific card.
     */
    public function synergies(int $id): JsonResponse
    {
        $card = SupportCardDefinition::find($id);

        if (! $card) {
            return response()->json(['message' => 'Support card not found'], 404);
        }

        // Return synergy data (simplified for now)
        return response()->json([
            'data' => [
                'synergy_cards' => $card->deck_synergies ?? [],
                'synergy_score' => 0, // Placeholder
            ],
        ]);
    }
}
