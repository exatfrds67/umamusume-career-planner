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
            $query->where('skill_id', $request->skill_id);
        }

        if ($request->boolean('unused_only')) {
            $query->unused();
        }

        if ($request->filled('source_type')) {
            $query->fromSource($request->source_type);
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
            $character = Character::findOrFail($request->character_id);
            $skill = Skill::findOrFail($request->skill_id);

            $hint = $this->hintService->createHint(
                character: $character,
                skill: $skill,
                sourceType: $request->source_type,
                sourceName: $request->source_name,
                sourceId: $request->source_id,
                additionalData: [
                    'turn_obtained' => $request->turn_obtained,
                    'career_phase' => $request->career_phase,
                    'guaranteed_hint' => $request->boolean('guaranteed_hint'),
                    'training_type' => $request->training_type,
                    'training_participants' => $request->training_participants,
                    'friendship_training' => $request->boolean('friendship_training'),
                    'hint_metadata' => $request->hint_metadata,
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
            $card->friendship_level = $csc->friendship_level;
            $card->limit_break_level = $csc->limit_break_level;
            $card->specialization = $card->card_type; // Map card_type to specialization
            $card->skill_provision = []; // TODO: Map skill_hints_provided to skill_provision format

            return $card;
        });

        $opportunities = $this->hintService->predictHintOpportunities(
            character: $character,
            trainingType: $request->training_type,
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
        $targetSkills = Skill::whereIn('id', $request->skill_ids)->get();

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
        $targetSkills = Skill::whereIn('id', $request->skill_ids)->get();

        // Get character support cards with their definitions
        $characterSupportCards = $character->supportCards()
            ->whereIn('id', $request->support_card_ids)
            ->with('supportCard')
            ->get();

        // Transform to support card instances with friendship/limit break data
        $supportCards = $characterSupportCards->map(function ($csc) {
            $card = $csc->supportCard;
            $card->friendship_level = $csc->friendship_level;
            $card->limit_break_level = $csc->limit_break_level;

            return $card;
        });

        $analysis = $this->hintAgent->analyzeHintOpportunities(
            character: $character,
            targetSkills: $targetSkills,
            supportCards: $supportCards,
            context: $request->context ?? []
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
        $targetSkills = Skill::whereIn('id', $request->skill_ids)->get();

        $sequence = $this->hintAgent->calculateOptimalSequence(
            character: $character,
            targetSkills: $targetSkills,
            availableTurns: $request->available_turns
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
        $acquiredSkills = Skill::whereIn('id', $request->skill_ids)->get();

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
}
