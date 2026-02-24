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
                $query->where('name', 'like', '%'.$search.'%');
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

        $synergyCards = $this->findSynergyCards($card);
        $synergyScore = $this->calculateCardSynergyScore($card, $synergyCards);

        return response()->json([
            'data' => [
                'card_id' => $card->id,
                'card_name' => $card->name,
                'synergy_cards' => $synergyCards,
                'synergy_score' => $synergyScore,
                'deck_synergies' => $card->deck_synergies ?? [],
                'recommended_scenarios' => $card->recommended_scenarios ?? [],
            ],
        ]);
    }

    /**
     * Find cards that synergize well with the given card.
     *
     * @return array<int, array<string, mixed>>
     */
    private function findSynergyCards(SupportCardDefinition $card): array
    {
        $compatibleCards = SupportCardDefinition::query()
            ->where('is_active', true)
            ->where('id', '!=', $card->id)
            ->get();

        $synergyCards = [];

        foreach ($compatibleCards as $candidate) {
            $score = $this->pairSynergyScore($card, $candidate);

            if ($score > 0) {
                $synergyCards[] = [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'card_type' => $candidate->card_type,
                    'meta_tier' => $candidate->meta_tier,
                    'synergy_score' => $score,
                    'reason' => $this->synergyReason($card, $candidate),
                ];
            }
        }

        usort($synergyCards, fn (array $a, array $b) => $b['synergy_score'] <=> $a['synergy_score']);

        return array_slice($synergyCards, 0, 10);
    }

    /**
     * Calculate pairwise synergy score between two cards.
     */
    private function pairSynergyScore(SupportCardDefinition $card, SupportCardDefinition $candidate): float
    {
        $score = 0.0;

        // Same type cards complement each other for focused training
        if ($card->card_type === $candidate->card_type) {
            $score += 15.0;
        }

        // Different types provide diversity bonus
        if ($card->card_type !== $candidate->card_type) {
            $score += 10.0;
        }

        // High meta tier cards synergize better
        $tierValues = ['S+' => 5, 'S' => 4, 'A' => 3, 'B' => 2, 'C' => 1];
        $candidateTierValue = $tierValues[$candidate->meta_tier] ?? 0;
        $score += $candidateTierValue * 3.0;

        // Check deck_synergies field for explicit synergy references
        $deckSynergies = $card->deck_synergies ?? [];
        if (is_array($deckSynergies)) {
            foreach ($deckSynergies as $synergy) {
                if (is_array($synergy) && ($synergy['card_id'] ?? null) === $candidate->id) {
                    $score += 20.0;
                }
            }
        }

        // Complementary stat bonuses
        $cardStats = $this->getCardStatProfile($card);
        $candidateStats = $this->getCardStatProfile($candidate);
        $complementScore = $this->calculateComplementarity($cardStats, $candidateStats);
        $score += $complementScore;

        return round($score, 1);
    }

    /**
     * Get stat bonus profile for a card.
     *
     * @return array<string, int>
     */
    private function getCardStatProfile(SupportCardDefinition $card): array
    {
        return [
            'speed' => $card->speed_bonus ?? 0,
            'stamina' => $card->stamina_bonus ?? 0,
            'power' => $card->power_bonus ?? 0,
            'guts' => $card->guts_bonus ?? 0,
            'wit' => $card->wit_bonus ?? 0,
        ];
    }

    /**
     * Calculate how well two stat profiles complement each other.
     *
     * @param  array<string, int>  $statsA
     * @param  array<string, int>  $statsB
     */
    private function calculateComplementarity(array $statsA, array $statsB): float
    {
        $score = 0.0;

        foreach ($statsA as $stat => $valueA) {
            $valueB = $statsB[$stat] ?? 0;
            if ($valueA > 0 && $valueB > 0) {
                $score += min($valueA, $valueB) * 0.5;
            }
        }

        return min($score, 15.0);
    }

    /**
     * Generate a human-readable synergy reason.
     */
    private function synergyReason(SupportCardDefinition $card, SupportCardDefinition $candidate): string
    {
        if ($card->card_type === $candidate->card_type) {
            return "Same training type ({$card->card_type}) for focused stat growth";
        }

        $tierValues = ['S+' => 5, 'S' => 4, 'A' => 3, 'B' => 2, 'C' => 1];
        $candidateTier = $candidate->meta_tier;
        if (($tierValues[$candidateTier] ?? 0) >= 4) {
            return "High meta tier ({$candidateTier}) provides strong overall bonuses";
        }

        return 'Complementary stat bonuses for balanced training';
    }

    /**
     * Calculate overall synergy score for a card based on its best partners.
     *
     * @param  array<int, array<string, mixed>>  $synergyCards
     */
    private function calculateCardSynergyScore(SupportCardDefinition $card, array $synergyCards): float
    {
        if (empty($synergyCards)) {
            return 0.0;
        }

        $topScores = array_slice(array_column($synergyCards, 'synergy_score'), 0, 5);
        $avgScore = count($topScores) > 0 ? array_sum($topScores) / count($topScores) : 0;

        // Factor in the card's own meta tier
        $tierMultipliers = ['S+' => 1.2, 'S' => 1.1, 'A' => 1.0, 'B' => 0.9, 'C' => 0.8];
        $multiplier = $tierMultipliers[$card->meta_tier] ?? 1.0;

        return round($avgScore * $multiplier, 1);
    }
}
