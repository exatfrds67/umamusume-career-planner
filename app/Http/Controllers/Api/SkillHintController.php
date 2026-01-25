<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkillHintRequest;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use App\Services\Agents\HintOptimizationAgent;
use App\Services\SkillHintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillHintController extends Controller
{
    public function __construct(
        private SkillHintService $hintService,
        private HintOptimizationAgent $hintAgent
    ) {}

    /**
     * Display a listing of hints for a character.
     */
    public function index(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'skill_id' => 'nullable|integer|exists:ucp_skills,id',
            'unused_only' => 'nullable|boolean',
            'source_type' => 'nullable|string',
        ]);

        $character = Character::findOrFail($characterId);

        $query = SkillHint::where('character_id', $characterId);

        if ($request->filled('skill_id')) {
            $skillId = $request->input('skill_id');
            if (is_numeric($skillId)) {
                $query->where('skill_id', (int) $skillId);
            }
        }

        if ($request->boolean('unused_only')) {
            $query->unused();
        }

        if ($request->filled('source_type')) {
            $sourceType = $request->input('source_type');
            if (is_string($sourceType)) {
                $query->fromSource($sourceType);
            }
        }

        $hints = $query->with(['skill', 'character'])
            ->orderBy('turn_obtained')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hints,
            'meta' => [
                'total' => $hints->count(),
                'unused' => $hints->where('is_used', false)->count(),
                'used' => $hints->where('is_used', true)->count(),
            ],
        ]);
    }

    /**
     * Store a newly created hint.
     */
    public function store(StoreSkillHintRequest $request): JsonResponse
    {
        try {
            $characterId = $request->input('character_id');
            $skillId = $request->input('skill_id');

            if (! is_numeric($characterId) || ! is_numeric($skillId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid character_id or skill_id',
                ], 422);
            }

            $character = Character::findOrFail((int) $characterId);
            $skill = Skill::findOrFail((int) $skillId);

            $sourceType = $request->input('source_type');
            $sourceName = $request->input('source_name');
            $sourceId = $request->input('source_id');
            $turnObtained = $request->input('turn_obtained');
            $careerPhase = $request->input('career_phase');
            $trainingType = $request->input('training_type');
            $trainingParticipants = $request->input('training_participants');
            $hintMetadata = $request->input('hint_metadata');

            $hint = $this->hintService->createHint(
                character: $character,
                skill: $skill,
                sourceType: is_string($sourceType) ? $sourceType : '',
                sourceName: is_string($sourceName) ? $sourceName : '',
                sourceId: is_numeric($sourceId) ? (int) $sourceId : null,
                additionalData: [
                    'turn_obtained' => is_numeric($turnObtained) ? (int) $turnObtained : null,
                    'career_phase' => is_string($careerPhase) ? $careerPhase : null,
                    'guaranteed_hint' => $request->boolean('guaranteed_hint'),
                    'training_type' => is_string($trainingType) ? $trainingType : null,
                    'training_participants' => is_array($trainingParticipants) ? $trainingParticipants : null,
                    'friendship_training' => $request->boolean('friendship_training'),
                    'hint_metadata' => is_array($hintMetadata) ? $hintMetadata : null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Skill hint created successfully',
                'data' => $hint->load(['skill', 'character']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create skill hint', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create skill hint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified hint.
     */
    public function show(int $id): JsonResponse
    {
        $hint = SkillHint::with(['skill', 'character'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $hint,
        ]);
    }

    /**
     * Remove the specified hint.
     */
    public function destroy(int $id): JsonResponse
    {
        $hint = SkillHint::findOrFail($id);
        $hint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill hint deleted successfully',
        ]);
    }

    /**
     * Get cost breakdown for a skill with current hints.
     */
    public function costBreakdown(int $characterId, int $skillId): JsonResponse
    {
        $character = Character::findOrFail($characterId);
        $skill = Skill::findOrFail($skillId);

        $breakdown = $this->hintService->getCostBreakdown($character, $skill);

        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Get hint statistics for a character.
     */
    public function statistics(int $characterId): JsonResponse
    {
        $character = Character::findOrFail($characterId);
        $statistics = $this->hintService->getHintStatistics($character);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Predict hint opportunities for training.
     */
    public function predictOpportunities(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'training_type' => 'required|string|in:speed,stamina,power,guts,wit',
            'support_card_ids' => 'required|array',
            'support_card_ids.*' => 'integer|exists:character_support_cards,id',
        ]);

        $character = Character::with('supportCards.supportCard')->findOrFail($characterId);

        // Get character support cards (pivot instances)
        $characterSupportCards = $character->supportCards()
            ->whereIn('id', $request->support_card_ids)
            ->with('supportCard')
            ->get();

        // Transform to support card instances with friendship/limit break data
        $supportCards = $characterSupportCards->map(function ($csc) {
            $card = $csc->supportCard;
            if ($card === null) {
                return null;
            }

            $card->friendship_level = $csc->friendship_level;
            $card->limit_break_level = $csc->limit_break_level;
            $card->specialization = $card->card_type;

            // Map skill_hints_provided JSON to skill_provision format
            // skill_hints_provided contains skill IDs that this card can provide hints for
            $hintsProvided = $card->skill_hints_provided ?? [];

            /** @var array<int, array<string, mixed>> $skillProvision */
            $skillProvision = collect($hintsProvided)->map(fn ($skillId) => [
                'skill_id' => $skillId,
                'probability' => $this->calculateHintProbability($card, $csc->friendship_level ?? 0),
            ])->toArray();

            $card->skill_provision = $skillProvision;

            return $card;
        })->filter();

        $opportunities = $this->hintService->predictHintOpportunities(
            character: $character,
            trainingType: is_string($request->input('training_type')) ? $request->input('training_type') : 'speed',
            supportCards: $supportCards
        );

        return response()->json([
            'success' => true,
            'data' => [
                'training_type' => $request->training_type,
                'opportunities' => $opportunities,
                'guaranteed_count' => count(array_filter($opportunities, fn ($o) => $o['guaranteed'])),
                'total_opportunities' => count($opportunities),
            ],
        ]);
    }

    /**
     * Get hint collection strategy recommendations.
     */
    public function collectionStrategy(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
        ]);

        $character = Character::findOrFail($characterId);
        $skillIds = $request->input('skill_ids');
        if (! is_array($skillIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid skill_ids format.',
            ], 422);
        }

        $targetSkills = Skill::whereIn('id', $skillIds)->get();

        $strategies = $this->hintService->getHintCollectionStrategy($character, $targetSkills);

        return response()->json([
            'success' => true,
            'data' => $strategies,
        ]);
    }

    /**
     * Get MCP-powered hint optimization analysis.
     */
    public function optimizationAnalysis(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
            'support_card_ids' => 'required|array',
            'support_card_ids.*' => 'integer|exists:character_support_cards,id',
            'context' => 'nullable|array',
        ]);

        $character = Character::findOrFail($characterId);
        $skillIds = $request->input('skill_ids');
        $supportCardIds = $request->input('support_card_ids');
        $context = $request->input('context');

        if (! is_array($skillIds) || ! is_array($supportCardIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid skill_ids or support_card_ids format.',
            ], 422);
        }

        $targetSkills = Skill::whereIn('id', $skillIds)->get();

        // Get character support cards with their definitions
        $characterSupportCards = $character->supportCards()
            ->whereIn('id', $supportCardIds)
            ->with('supportCard')
            ->get();

        // Transform to support card instances with friendship/limit break data
        /** @var \Illuminate\Support\Collection<int, \App\Models\SupportCard> $supportCards */
        $supportCards = $characterSupportCards->map(function ($csc): ?\App\Models\SupportCard {
            $card = $csc->supportCard;
            if (! $card instanceof \App\Models\SupportCard) {
                return null;
            }

            $friendshipLevel = $csc->friendship_level;
            $card->friendship_level = $friendshipLevel;
            $card->limit_break_level = $csc->limit_break_level;

            return $card;
        })->filter()->values();

        $analysis = $this->hintAgent->analyzeHintOpportunities(
            character: $character,
            targetSkills: $targetSkills,
            supportCards: $supportCards,
            context: is_array($context) ? $context : []
        );

        return response()->json([
            'success' => $analysis['success'],
            'data' => $analysis,
        ]);
    }

    /**
     * Calculate optimal hint collection sequence.
     */
    public function optimalSequence(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
            'available_turns' => 'required|integer|min:1|max:72',
        ]);

        $character = Character::findOrFail($characterId);
        $skillIds = $request->input('skill_ids');
        $availableTurns = $request->input('available_turns');

        if (! is_array($skillIds) || ! is_numeric($availableTurns)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid skill_ids or available_turns format.',
            ], 422);
        }

        $targetSkills = Skill::whereIn('id', $skillIds)->get();

        $sequence = $this->hintAgent->calculateOptimalSequence(
            character: $character,
            targetSkills: $targetSkills,
            availableTurns: (int) $availableTurns
        );

        return response()->json([
            'success' => true,
            'data' => $sequence,
        ]);
    }

    /**
     * Evaluate hint collection efficiency.
     */
    public function evaluateEfficiency(Request $request, int $characterId): JsonResponse
    {
        $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
        ]);

        $character = Character::findOrFail($characterId);
        $skillIds = $request->input('skill_ids');

        if (! is_array($skillIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid skill_ids format.',
            ], 422);
        }

        $acquiredSkills = Skill::whereIn('id', $skillIds)->get();

        $efficiency = $this->hintAgent->evaluateEfficiency($character, $acquiredSkills);

        return response()->json([
            'success' => true,
            'data' => $efficiency,
        ]);
    }

    /**
     * Mark hints as used when a skill is acquired.
     */
    public function markAsUsed(Request $request, int $characterId, int $skillId): JsonResponse
    {
        $request->validate([
            'confirm' => 'required|boolean|accepted',
        ]);

        $character = Character::findOrFail($characterId);
        $skill = Skill::findOrFail($skillId);

        $hintsUsed = $this->hintService->markHintsAsUsed($character, $skill);

        return response()->json([
            'success' => true,
            'message' => "Marked {$hintsUsed} hints as used",
            'data' => [
                'hints_used' => $hintsUsed,
                'skill_id' => $skillId,
                'character_id' => $characterId,
            ],
        ]);
    }

    /**
     * Calculate hint probability based on support card and friendship level.
     *
     * Higher friendship levels increase the probability of receiving skill hints.
     * Base probability is 10%, with up to 40% bonus at max friendship (100).
     *
     * @param  object  $card  The support card definition
     * @param  int  $friendshipLevel  Current friendship level (0-100)
     * @return float Probability as decimal (0.0 - 1.0)
     */
    private function calculateHintProbability(object $card, int $friendshipLevel): float
    {
        $baseProbability = 0.10; // 10% base chance
        $friendshipBonus = ($friendshipLevel / 100) * 0.40; // Up to 40% bonus at max friendship

        // Rare cards have slightly higher hint rates
        $rarityMultiplier = match ($card->rarity ?? 'R') {
            'SSR' => 1.3,
            'SR' => 1.15,
            default => 1.0,
        };

        return min(1.0, ($baseProbability + $friendshipBonus) * $rarityMultiplier);
    }
}
