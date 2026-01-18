<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Services\MCP\SkillOptimizationOrchestrationService;
use App\Services\SkillAnalysisService;
use App\Services\SkillEvolutionService;
use App\Services\SkillHintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SkillManagementController extends Controller
{
    public function __construct(
        private SkillAnalysisService $analysisService,
        private SkillEvolutionService $evolutionService,
        private SkillHintService $hintService,
        private SkillOptimizationOrchestrationService $orchestrationService
    ) {}

    /**
     * Get all skills with acquisition status for a character.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
        ]);

        $character = Character::findOrFail($request->character_id);

        // Get all skills with acquisition status
        $skills = Skill::with(['acquisitions' => function ($query) use ($character) {
            $query->where('character_id', $character->id)
                ->where('is_active', true);
        }])
            ->get()
            ->map(function ($skill) use ($character) {
                $acquisition = $skill->acquisitions->first();

                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'internal_id' => $skill->internal_id,
                    'skill_type' => $skill->skill_type,
                    'rarity' => $skill->rarity,
                    'base_sp_cost' => $skill->base_sp_cost,
                    'description' => $skill->description,
                    'effects' => $skill->effects,
                    'meta_tier' => $skill->meta_tier,
                    'is_acquired' => $acquisition !== null,
                    'is_evolution' => $acquisition?->is_evolution ?? false,
                    'final_sp_cost' => $acquisition?->final_sp_cost,
                    'sp_saved' => $acquisition?->sp_saved ?? 0,
                    'hint_count' => $acquisition?->hints_used ?? 0,
                    'races_used' => $acquisition?->races_used ?? 0,
                    'effectiveness_rating' => $acquisition?->effectiveness_rating,
                    'can_evolve' => $skill->can_evolve,
                    'evolution_target_id' => $skill->evolution_target_id,
                    'available_hints' => $this->hintService->getUnusedHints($character, $skill)->count(),
                    'discounted_cost' => $this->hintService->calculateFinalCost(
                        $skill,
                        $this->hintService->getUnusedHints($character, $skill)->count()
                    ),
                    'sp_savings' => $skill->base_sp_cost - $this->hintService->calculateFinalCost(
                        $skill,
                        $this->hintService->getUnusedHints($character, $skill)->count()
                    ),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Acquire a skill for a character.
     */
    public function acquire(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'skill_id' => 'required|integer|exists:ucp_skills,id',
            'turn_acquired' => 'nullable|integer|min:1|max:72',
            'career_phase' => 'nullable|string|in:junior,classic,senior',
        ]);

        try {
            DB::beginTransaction();

            $character = Character::findOrFail($request->character_id);
            $skill = Skill::findOrFail($request->skill_id);

            // Get unused hints
            $hints = $this->hintService->getUnusedHints($character, $skill);
            $hintCount = $hints->count();

            // Calculate final cost
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);

            // Check if character has enough SP
            if ($character->available_sp < $finalCost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient SP',
                    'required' => $finalCost,
                    'available' => $character->available_sp,
                ], 400);
            }

            // Create skill acquisition
            $acquisition = SkillAcquisition::create([
                'character_id' => $character->id,
                'skill_id' => $skill->id,
                'turn_acquired' => $request->turn_acquired ?? $character->current_turn,
                'career_phase' => $request->career_phase ?? $character->career_phase,
                'acquisition_method' => 'manual',
                'base_sp_cost' => $skill->base_sp_cost,
                'hints_used' => $hintCount,
                'total_discount_percentage' => $skill->getDiscountPercentage($hintCount),
                'final_sp_cost' => $finalCost,
                'sp_saved' => $skill->getSpSaved($hintCount),
                'is_active' => true,
            ]);

            // Deduct SP from character
            $character->decrement('available_sp', $finalCost);

            // Mark hints as used
            $this->hintService->markHintsAsUsed($character, $skill);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Skill acquired successfully',
                'data' => [
                    'acquisition' => $acquisition,
                    'remaining_sp' => $character->fresh()->available_sp,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to acquire skill', [
                'error' => $e->getMessage(),
                'character_id' => $request->character_id,
                'skill_id' => $request->skill_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to acquire skill',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get evolution opportunities for a character.
     */
    public function evolutionOpportunities(int $characterId): JsonResponse
    {
        $character = Character::findOrFail($characterId);
        $opportunities = $this->evolutionService->getEvolutionOpportunities($character);

        return response()->json([
            'success' => true,
            'data' => $opportunities,
        ]);
    }

    /**
     * Evolve a skill.
     */
    public function evolve(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'skill_id' => 'required|integer|exists:ucp_skills,id',
        ]);

        try {
            $character = Character::findOrFail($request->character_id);
            $skill = Skill::findOrFail($request->skill_id);

            $result = $this->evolutionService->evolveSkill($character, $skill);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Skill evolved successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to evolve skill', [
                'error' => $e->getMessage(),
                'character_id' => $request->character_id,
                'skill_id' => $request->skill_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to evolve skill',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get AI-powered skill recommendations.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'target_skills' => 'nullable|array',
            'target_skills.*' => 'integer|exists:ucp_skills,id',
        ]);

        try {
            $character = Character::with(['supportCards.supportCard'])->findOrFail($request->character_id);

            $targetSkills = $request->has('target_skills')
                ? Skill::whereIn('id', $request->target_skills)->get()
                : Skill::where('is_active', true)->get();

            // Get AI recommendations through MCP orchestration
            $recommendations = $this->orchestrationService->optimizeSkillAcquisition(
                character: $character,
                targetSkills: $targetSkills,
                context: [
                    'available_sp' => $character->available_sp,
                    'current_turn' => $character->current_turn,
                    'career_phase' => $character->career_phase,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get skill recommendations', [
                'error' => $e->getMessage(),
                'character_id' => $request->character_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get agent performance metrics.
     */
    public function agentPerformance(int $characterId): JsonResponse
    {
        $character = Character::findOrFail($characterId);

        // Get performance metrics from acquisitions
        $acquisitions = SkillAcquisition::where('character_id', $characterId)->get();

        $performance = [
            'total_sp_saved' => $acquisitions->sum('sp_saved'),
            'total_recommendations' => $acquisitions->where('acquisition_method', 'ai_recommended')->count(),
            'success_rate' => $this->calculateSuccessRate($acquisitions),
            'avg_response_time' => 1.2, // Mock data - would come from MCP logs
            'activities' => $this->getRecentActivities($character),
            'agents' => [
                'skill_analysis' => [
                    'total' => $acquisitions->count(),
                    'accuracy' => 94,
                    'sp_optimized' => $acquisitions->sum('sp_saved'),
                ],
                'hint_optimization' => [
                    'total' => $acquisitions->where('hints_used', '>', 0)->count(),
                    'avg_discount' => $acquisitions->where('hints_used', '>', 0)->avg('total_discount_percentage') ?? 0,
                    'sp_saved' => $acquisitions->sum('sp_saved'),
                ],
                'evolution_planning' => [
                    'total' => $acquisitions->where('is_evolution', true)->count(),
                    'success_rate' => 100,
                    'efficiency_gain' => 35,
                ],
                'build_planning' => [
                    'total' => 0, // Would track saved builds
                    'avg_synergy' => 8.5,
                    'meta_alignment' => 92,
                ],
            ],
            'recommendations' => [
                'followed' => $acquisitions->where('acquisition_method', 'ai_recommended')->count(),
                'pending' => 3, // Mock data
                'ignored' => 1, // Mock data
                'sp_saved' => $acquisitions->where('acquisition_method', 'ai_recommended')->sum('sp_saved'),
                'potential_savings' => 120, // Mock data
                'missed_savings' => 40, // Mock data
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }

    /**
     * Calculate success rate of AI recommendations.
     */
    private function calculateSuccessRate($acquisitions): float
    {
        $total = $acquisitions->where('acquisition_method', 'ai_recommended')->count();
        if ($total === 0) {
            return 0;
        }

        $successful = $acquisitions->where('acquisition_method', 'ai_recommended')
            ->where('effectiveness_rating', '>=', 7)
            ->count();

        return round(($successful / $total) * 100, 1);
    }

    /**
     * Get recent agent activities.
     */
    private function getRecentActivities(Character $character): array
    {
        $acquisitions = SkillAcquisition::where('character_id', $character->id)
            ->with('skill')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $acquisitions->map(function ($acquisition) {
            return [
                'id' => $acquisition->id,
                'type' => $acquisition->is_evolution ? 'optimization' : 'success',
                'title' => $acquisition->is_evolution
                    ? "Skill Evolved: {$acquisition->skill->name}"
                    : "Skill Acquired: {$acquisition->skill->name}",
                'description' => $acquisition->is_evolution
                    ? "Successfully evolved skill with {$acquisition->hints_used} hints applied"
                    : "Acquired skill for {$acquisition->final_sp_cost} SP (saved {$acquisition->sp_saved} SP)",
                'timestamp' => $acquisition->created_at->diffForHumans(),
                'agent_name' => $acquisition->is_evolution ? 'Evolution Agent' : 'Acquisition Agent',
                'metrics' => [
                    'sp_saved' => $acquisition->sp_saved,
                    'efficiency' => round(($acquisition->sp_saved / $acquisition->base_sp_cost) * 100, 1),
                    'processing_time' => 0.8, // Mock data
                ],
            ];
        })->toArray();
    }
}
