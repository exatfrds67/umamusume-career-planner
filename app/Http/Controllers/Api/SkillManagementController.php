<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\MCPToolUsage;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillBuild;
use App\Services\MCP\SkillOptimizationOrchestrationService;
use App\Services\SkillEvolutionService;
use App\Services\SkillHintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillManagementController extends Controller
{
    public function __construct(
        private SkillEvolutionService $evolutionService,
        private SkillHintService $hintService,
        private SkillOptimizationOrchestrationService $orchestrationService
    ) {}

    /**
     * Get all skills with acquisition status for a character.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
        ]);

        /** @var Character $character */
        $character = Character::query()->findOrFail($validated['character_id']);

        // Get all skills with acquisition status
        $skills = Skill::with(['acquisitions' => function ($query) use ($character) {
            $query->where('character_id', $character->id)
                ->where('is_active', true);
        }])
            ->get()
            ->map(function ($skill) use ($character) {
                /** @var Skill $skill */
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
                    'is_evolution' => $acquisition !== null ? $acquisition->is_evolution : false,
                    'final_sp_cost' => $acquisition !== null ? $acquisition->final_sp_cost : null,
                    'sp_saved' => $acquisition !== null ? $acquisition->sp_saved : 0,
                    'hint_count' => $acquisition !== null ? $acquisition->hints_used : 0,
                    'races_used' => $acquisition !== null ? $acquisition->races_used : 0,
                    'effectiveness_rating' => $acquisition !== null ? $acquisition->effectiveness_rating : null,
                    'can_evolve' => $skill->can_evolve,
                    'evolution_target_id' => $skill->evolution_target_id,
                    'available_hints' => $this->hintService->getUnusedHintsForSkill($character, $skill)->count(),
                    'discounted_cost' => $this->hintService->calculateFinalCost(
                        $skill,
                        $this->hintService->getUnusedHintsForSkill($character, $skill)->count()
                    ),
                    'sp_savings' => $skill->base_sp_cost - $this->hintService->calculateFinalCost(
                        $skill,
                        $this->hintService->getUnusedHintsForSkill($character, $skill)->count()
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
        $validated = $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'skill_id' => 'required|integer|exists:ucp_skills,id',
            'turn_acquired' => 'nullable|integer|min:1|max:72',
            'career_phase' => 'nullable|string|in:junior,classic,senior',
        ]);

        try {
            /** @var Character $character */
            $character = Character::query()->findOrFail($validated['character_id']);

            // Use policy authorization (allows admins and owners)
            $this->authorize('update', $character);

            /** @var Skill $skill */
            $skill = Skill::query()->findOrFail($validated['skill_id']);

            // Get unused hints
            $hints = $this->hintService->getUnusedHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            // Calculate final cost
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);

            $isAdmin = auth()->user()?->isAdmin() ?? false;

            // Check if character has enough SP (admins bypass this check)
            if (! $isAdmin && $character->available_sp < $finalCost) {
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
                'turn_acquired' => $validated['turn_acquired'] ?? $character->current_turn,
                'career_phase' => $validated['career_phase'] ?? $character->career_stage,
                'acquisition_method' => 'purchase',
                'base_sp_cost' => $skill->base_sp_cost,
                'hints_used' => $hintCount,
                'total_discount_percentage' => $skill->getDiscountPercentage($hintCount),
                'final_sp_cost' => $finalCost,
                'sp_saved' => $skill->getSpSaved($hintCount),
                'is_active' => true,
            ]);

            // Deduct SP from character (admins bypass this)
            $isAdmin = auth()->user()?->isAdmin() ?? false;
            if (! $isAdmin) {
                $character->decrement('available_sp', $finalCost);
            }

            // Mark hints as used
            $this->hintService->markHintsAsUsed($character, $skill);

            /** @var Character $freshCharacter */
            $freshCharacter = $character->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Skill acquired successfully',
                'data' => [
                    'acquisition' => $acquisition,
                    'remaining_sp' => $freshCharacter->available_sp,
                ],
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to acquire skill', [
                'error' => $e->getMessage(),
                'character_id' => $validated['character_id'],
                'skill_id' => $validated['skill_id'],
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
        /** @var Character $character */
        $character = Character::query()->findOrFail($characterId);
        $opportunities = $this->evolutionService->getEvolutionOpportunities($character);

        // Transform the response to match expected structure
        $transformedOpportunities = array_map(function ($opportunity) {
            $skill = $opportunity['skill'] ?? null;
            if (! ($skill instanceof Skill)) {
                return null;
            }

            $evolutionTarget = property_exists($skill, 'evolutionTarget') ? $skill->evolutionTarget : null;
            if (! ($evolutionTarget instanceof Skill)) {
                return null;
            }

            $efficiency = $opportunity['efficiency'] ?? [];
            $finalCost = 0;

            if (is_array($efficiency) && isset($efficiency['rare_skill']) && is_array($efficiency['rare_skill'])) {
                $finalCost = $efficiency['rare_skill']['final_cost'] ?? 0;
            }

            return [
                'normal_skill' => $skill,
                'rare_skill' => $evolutionTarget,
                'can_evolve' => $opportunity['can_evolve_now'] ?? false,
                'evolution_cost' => $finalCost,
                'block_reason' => $opportunity['block_reason'] ?? null,
                'efficiency' => $efficiency,
                'priority' => $opportunity['priority'] ?? 0,
            ];
        }, $opportunities);

        // Filter out null values
        $transformedOpportunities = array_filter($transformedOpportunities);

        return response()->json([
            'success' => true,
            'data' => $transformedOpportunities,
        ]);
    }

    /**
     * Evolve a skill.
     */
    public function evolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'skill_id' => 'required|integer|exists:ucp_skills,id',
        ]);

        try {
            /** @var Character $character */
            $character = Character::query()->findOrFail($validated['character_id']);

            // Use policy authorization (allows admins and owners)
            $this->authorize('update', $character);

            /** @var Skill $skill */
            $skill = Skill::query()->findOrFail($validated['skill_id']);

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
                'character_id' => $validated['character_id'],
                'skill_id' => $validated['skill_id'],
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
        $validated = $request->validate([
            'character_id' => 'required|integer|exists:ucp_characters,id',
            'target_skills' => 'nullable|array',
            'target_skills.*' => 'integer|exists:ucp_skills,id',
        ]);

        try {
            /** @var Character $character */
            $character = Character::query()->with(['supportCards.supportCard'])->findOrFail($validated['character_id']);

            $targetSkills = isset($validated['target_skills'])
                ? Skill::whereIn('id', $validated['target_skills'])->get()
                : Skill::where('is_active', true)->get();

            // Get AI recommendations through MCP orchestration
            $recommendations = $this->orchestrationService->optimizeSkillAcquisition(
                character: $character,
                targetSkills: $targetSkills,
                context: [
                    'available_sp' => $character->available_sp,
                    'current_turn' => $character->current_turn,
                    'career_phase' => $character->career_stage,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get skill recommendations', [
                'error' => $e->getMessage(),
                'character_id' => $validated['character_id'],
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
        /** @var Character $character */
        $character = Character::query()->findOrFail($characterId);

        $acquisitions = SkillAcquisition::query()->where('character_id', $characterId)->get();

        $mcpUsages = MCPToolUsage::query()
            ->where('tool_category', 'skill')
            ->orderBy('executed_at', 'desc')
            ->limit(500)
            ->get();

        $avgResponseTime = $mcpUsages->isNotEmpty()
            ? round((float) ($mcpUsages->avg('execution_time') ?? 0.0), 3)
            : 0.0;

        $successfulMcp = $mcpUsages->where('execution_status', 'success')->count();
        $mcpAccuracy = $mcpUsages->isNotEmpty()
            ? round(($successfulMcp / $mcpUsages->count()) * 100, 1)
            : 0.0;

        $evolutionAcquisitions = $acquisitions->where('is_evolution', true);
        $evolutionSuccessRate = $evolutionAcquisitions->isNotEmpty()
            ? round(($evolutionAcquisitions->where('is_active', true)->count() / $evolutionAcquisitions->count()) * 100, 1)
            : 0.0;

        $builds = SkillBuild::query()->where('character_id', $characterId)->get();
        $avgSynergy = $builds->isNotEmpty() && $builds->avg('optimized_cost') > 0
            ? round(($builds->avg('total_sp_cost') - $builds->avg('optimized_cost')) / max($builds->avg('total_sp_cost'), 1) * 100, 1)
            : 0.0;

        $aiRecommended = $acquisitions->where('acquisition_method', 'ai_recommended');
        $manualAcquisitions = $acquisitions->where('acquisition_method', '!=', 'ai_recommended');
        $pendingRecommendations = $mcpUsages->where('execution_status', 'success')
            ->where('tool_name', 'recommend_skills')
            ->count() - $aiRecommended->count();

        /** @var int|float $baseSpCostRaw */
        $baseSpCostRaw = $manualAcquisitions->sum('base_sp_cost');
        /** @var int|float $finalSpCostRaw */
        $finalSpCostRaw = $manualAcquisitions->sum('final_sp_cost');
        $potentialSavings = $baseSpCostRaw - $finalSpCostRaw;
        /** @var int|float $filteredSpCostRaw */
        $filteredSpCostRaw = $manualAcquisitions->filter(fn ($a) => ($a->hints_used ?? 0) === 0)->sum('base_sp_cost');
        $missedSavings = $filteredSpCostRaw * 0.2;

        $performance = [
            'total_sp_saved' => $acquisitions->sum('sp_saved'),
            'total_recommendations' => $aiRecommended->count(),
            'success_rate' => $this->calculateSuccessRate($acquisitions),
            'avg_response_time' => $avgResponseTime,
            'activities' => $this->getRecentActivities($character),
            'agents' => [
                'skill_analysis' => [
                    'total' => $acquisitions->count(),
                    'accuracy' => $mcpAccuracy,
                    'sp_optimized' => $acquisitions->sum('sp_saved'),
                ],
                'hint_optimization' => [
                    'total' => $acquisitions->where('hints_used', '>', 0)->count(),
                    'avg_discount' => round((float) ($acquisitions->where('hints_used', '>', 0)->avg('total_discount_percentage') ?? 0), 1),
                    'sp_saved' => $acquisitions->sum('sp_saved'),
                ],
                'evolution_planning' => [
                    'total' => $evolutionAcquisitions->count(),
                    'success_rate' => $evolutionSuccessRate,
                    'efficiency_gain' => $evolutionAcquisitions->isNotEmpty()
                        ? (function () use ($evolutionAcquisitions): float {
                            /** @var int|float $spSaved */
                            $spSaved = $evolutionAcquisitions->sum('sp_saved');
                            /** @var int|float $baseSpCost */
                            $baseSpCost = $evolutionAcquisitions->sum('base_sp_cost');

                            return round(($spSaved / max($baseSpCost, 1)) * 100, 1);
                        })()
                        : 0.0,
                ],
                'build_planning' => [
                    'total' => $builds->count(),
                    'avg_synergy' => $avgSynergy,
                    'meta_alignment' => $builds->isNotEmpty()
                        ? round($builds->whereNotNull('meta_tier')->count() / max($builds->count(), 1) * 100, 1)
                        : 0.0,
                ],
            ],
            'recommendations' => [
                'followed' => $aiRecommended->count(),
                'pending' => max(0, (int) $pendingRecommendations),
                'ignored' => $manualAcquisitions->count(),
                'sp_saved' => $aiRecommended->sum('sp_saved'),
                'potential_savings' => (int) $potentialSavings,
                'missed_savings' => (int) round($missedSavings),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }

    /**
     * Calculate success rate of AI recommendations.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, SkillAcquisition>  $acquisitions
     */
    private function calculateSuccessRate(\Illuminate\Database\Eloquent\Collection $acquisitions): float
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
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentActivities(Character $character): array
    {
        $acquisitions = SkillAcquisition::where('character_id', $character->id)
            ->with('skill')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        /** @var array<int, array<string, mixed>> $activities */
        $activities = $acquisitions->map(function ($acquisition) {
            $skill = $acquisition->skill;
            $skillName = $skill !== null ? $skill->name : 'Unknown Skill';
            $createdAt = $acquisition->created_at;

            return [
                'id' => $acquisition->id,
                'type' => $acquisition->is_evolution ? 'optimization' : 'success',
                'title' => $acquisition->is_evolution
                    ? "Skill Evolved: {$skillName}"
                    : "Skill Acquired: {$skillName}",
                'description' => $acquisition->is_evolution
                    ? "Successfully evolved skill with {$acquisition->hints_used} hints applied"
                    : "Acquired skill for {$acquisition->final_sp_cost} SP (saved {$acquisition->sp_saved} SP)",
                'timestamp' => $createdAt !== null ? $createdAt->diffForHumans() : 'Unknown',
                'agent_name' => $acquisition->is_evolution ? 'Evolution Agent' : 'Acquisition Agent',
                'metrics' => [
                    'sp_saved' => $acquisition->sp_saved,
                    'efficiency' => round(($acquisition->sp_saved / max($acquisition->base_sp_cost, 1)) * 100, 1),
                    'processing_time' => $this->getAcquisitionProcessingTime($acquisition),
                ],
            ];
        })->toArray();

        return $activities;
    }

    /**
     * Get the processing time for a skill acquisition from MCP tool usage logs.
     */
    private function getAcquisitionProcessingTime(SkillAcquisition $acquisition): float
    {
        $usage = MCPToolUsage::query()
            ->where('tool_category', 'skill')
            ->where('executed_at', '>=', $acquisition->created_at?->subMinutes(5))
            ->where('executed_at', '<=', $acquisition->created_at?->addMinutes(5))
            ->orderBy('executed_at', 'desc')
            ->first();

        return $usage ? round($usage->execution_time, 3) : 0.0;
    }
}
