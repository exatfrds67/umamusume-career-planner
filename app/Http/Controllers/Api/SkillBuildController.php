<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillBuild;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Skill Build Controller
 *
 * Handles skill build templates, saved builds, and optimization
 * for the Skills Management page Build Planner tab.
 */
class SkillBuildController extends Controller
{
    /**
     * Get available build templates.
     */
    public function templates(): JsonResponse
    {
        $templateModels = SkillBuild::query()
            ->where('is_template', true)
            ->orderByDesc('updated_at')
            ->get();

        if ($templateModels->isNotEmpty()) {
            $templates = $templateModels->map(fn (SkillBuild $build): array => $this->formatBuildTemplate($build))->all();

            return response()->json([
                'success' => true,
                'data' => $templates,
            ]);
        }

        $skills = Skill::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->limit(24)
            ->get(['id', 'name', 'skill_type', 'rarity', 'base_sp_cost', 'meta_tier', 'description']);

        $templates = $skills
            ->groupBy('skill_type')
            ->take(5)
            ->map(function ($group, string $type): array {
                $skillIds = $group->pluck('id')->all();
                $totalCost = (int) $group->sum(fn (Skill $skill): int => $skill->base_sp_cost);
                $optimizedCost = $this->calculateOptimizedCostForSkills($group);

                return [
                    'id' => \crc32($type),
                    'name' => \ucfirst($type).' Build',
                    'category' => \ucfirst($type),
                    'meta_tier' => $group->first()->meta_tier ?? 'B',
                    'description' => 'Auto-generated build based on recent skills.',
                    'skill_count' => \count($skillIds),
                    'total_sp_cost' => $totalCost,
                    'optimized_cost' => $optimizedCost,
                    'potential_savings' => $totalCost - $optimizedCost,
                    'tags' => [\ucfirst($type)],
                    'skills' => $group->map(fn (Skill $skill): array => [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'rarity' => $skill->rarity,
                        'skill_type' => $skill->skill_type,
                        'final_cost' => $skill->base_sp_cost,
                        'hints_available' => 0,
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get user's saved builds.
     */
    public function savedBuilds(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $savedBuilds = SkillBuild::query()
            ->where('user_id', $user->id)
            ->where('is_template', false)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (SkillBuild $build): array => [
                'id' => $build->id,
                'name' => $build->name,
                'skill_count' => \is_array($build->skill_ids) ? \count($build->skill_ids) : 0,
                'total_sp' => $build->total_sp_cost,
                'created_at' => $build->created_at?->toDateTimeString(),
            ])
            ->all();

        return response()->json([
            'success' => true,
            'data' => $savedBuilds,
        ]);
    }

    /**
     * Get optimization for a build based on selected skills.
     */
    public function optimize(Request $request): JsonResponse
    {
        $request->validate([
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
        ]);

        /** @var array<int, int> $skillIds */
        $skillIds = $request->input('skill_ids', []);
        $skills = Skill::whereIn('id', $skillIds)->get();

        $totalBaseCost = 0;
        $totalOptimizedCost = 0;
        $skillDetails = [];

        foreach ($skills as $skill) {
            $baseCost = $skill->base_sp_cost;
            $totalBaseCost += $baseCost;

            $hintLevel = $skill->hints()->count();
            $discountedCost = $skill->calculateFinalCost($hintLevel);
            $totalOptimizedCost += $discountedCost;

            $skillDetails[] = [
                'id' => $skill->id,
                'name' => $skill->name,
                'skill_type' => $skill->skill_type,
                'base_cost' => $baseCost,
                'hint_level' => $hintLevel,
                'discounted_cost' => $discountedCost,
                'savings' => $baseCost - $discountedCost,
                'can_evolve' => $skill->canEvolve(),
            ];
        }

        $synergyCount = 0;
        /** @var array<int, Skill> $skillArray */
        $skillArray = $skills->values()->all();
        $skillCount = \count($skillArray);
        for ($i = 0; $i < $skillCount; $i++) {
            for ($j = $i + 1; $j < $skillCount; $j++) {
                if ($skillArray[$i]->synergizesWith($skillArray[$j])) {
                    $synergyCount++;
                }
            }
        }

        $maxPossibleSynergies = max(1, (int) (($skills->count() * ($skills->count() - 1)) / 2));
        $synergyRating = min(10, max(1, (int) round(($synergyCount / $maxPossibleSynergies) * 10)));

        $efficiencyScore = $totalBaseCost > 0
            ? (int) round((1 - ($totalOptimizedCost / $totalBaseCost)) * 100)
            : 0;

        $recommendations = $this->generateOptimizationRecommendations($skills, $skillDetails);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => \sprintf(
                    'Analyzed %d skills. Potential savings: %d SP (%d%% reduction).',
                    $skills->count(),
                    $totalBaseCost - $totalOptimizedCost,
                    $efficiencyScore,
                ),
                'efficiency_score' => $efficiencyScore,
                'synergy_rating' => $synergyRating,
                'total_sp_cost' => $totalBaseCost,
                'optimized_cost' => $totalOptimizedCost,
                'recommendations' => $recommendations,
            ],
        ]);
    }

    /**
     * Apply a build to a character.
     */
    public function applyBuild(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|integer',
            'character_id' => 'sometimes|integer|exists:ucp_characters,id',
        ]);

        $build = SkillBuild::find($request->integer('template_id'));

        if (! $build) {
            return response()->json([
                'success' => false,
                'message' => 'Build not found',
            ], 404);
        }

        $skillIds = $build->skill_ids ?? [];
        $skills = Skill::whereIn('id', $skillIds)->get();

        if ($skills->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Build contains no valid skills',
            ], 422);
        }

        $character = $request->has('character_id') ? Character::find($request->integer('character_id')) : null;

        if ($request->has('character_id') && ! $character) {
            return response()->json([
                'success' => false,
                'message' => 'Character not found',
            ], 404);
        }

        $totalSpSpent = 0;
        $skillsAdded = [];
        $skippedSkills = [];

        if ($character) {
            $existingSkillIds = $character->skills()->pluck('ucp_skills.id')->toArray();

            DB::transaction(function () use ($skills, $character, $existingSkillIds, &$totalSpSpent, &$skillsAdded, &$skippedSkills): void {
                foreach ($skills as $skill) {
                    if (\in_array($skill->id, $existingSkillIds)) {
                        $skippedSkills[] = [
                            'id' => $skill->id,
                            'name' => $skill->name,
                            'reason' => 'already_acquired',
                        ];

                        continue;
                    }

                    $hintLevel = $skill->hints()->where('character_id', $character->id)->count();
                    $finalCost = $skill->calculateFinalCost($hintLevel);
                    $totalSpSpent += $finalCost;

                    SkillAcquisition::create([
                        'character_id' => $character->id,
                        'skill_id' => $skill->id,
                        'career_id' => $character->currentCareer?->id,
                        'turn_acquired' => $character->current_turn ?? 1,
                        'career_phase' => $character->career_stage ?? 'junior',
                        'acquisition_method' => 'purchase',
                        'base_sp_cost' => $skill->base_sp_cost,
                        'hints_used' => $hintLevel,
                        'final_sp_cost' => $finalCost,
                        'sp_saved' => $skill->base_sp_cost - $finalCost,
                        'is_evolution' => false,
                        'is_active' => true,
                    ]);

                    $skillsAdded[] = [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'sp_cost' => $finalCost,
                    ];
                }
            });
        } else {
            foreach ($skills as $skill) {
                $finalCost = $skill->calculateFinalCost(0);
                $totalSpSpent += $finalCost;

                $skillsAdded[] = [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'sp_cost' => $finalCost,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => $character
                ? \sprintf('Applied %d skills to %s', \count($skillsAdded), $character->name)
                : \sprintf('Build preview: %d skills', \count($skillsAdded)),
            'data' => [
                'skills_added' => \count($skillsAdded),
                'sp_spent' => $totalSpSpent,
                'skills' => $skillsAdded,
                'skipped' => $skippedSkills,
                'build_name' => $build->name,
            ],
        ]);
    }

    /**
     * Save a custom build.
     */
    public function saveBuild(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'skill_ids' => 'required|array|min:1',
            'skill_ids.*' => 'integer|exists:ucp_skills,id',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        /** @var array<int, int> $skillIds */
        $skillIds = $request->input('skill_ids', []);
        $skills = Skill::whereIn('id', $skillIds)->get();
        $totalCost = (int) $skills->sum(fn (Skill $skill): int => $skill->base_sp_cost);
        $optimizedCost = $this->calculateOptimizedCostForSkills($skills);

        $build = SkillBuild::create([
            'user_id' => $user->id,
            'character_id' => null,
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'skill_ids' => $skillIds,
            'total_sp_cost' => $totalCost,
            'optimized_cost' => $optimizedCost,
            'tags' => $request->input('tags', []),
            'is_template' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Build saved successfully',
            'data' => [
                'id' => $build->id,
                'name' => $build->name,
                'created_at' => $build->created_at?->toDateTimeString(),
            ],
        ], 201);
    }

    /**
     * Delete a saved build.
     */
    public function deleteBuild(int $id): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $build = SkillBuild::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $build || $build->is_template) {
            return response()->json([
                'success' => false,
                'message' => 'Build not found',
            ], 404);
        }

        $build->delete();

        return response()->json([
            'success' => true,
            'message' => 'Build deleted successfully',
        ]);
    }

    /**
     * Calculate optimized cost for a collection of skills using hint discounts.
     *
     * @param  Collection<int, Skill>  $skills
     */
    private function calculateOptimizedCostForSkills(Collection $skills): int
    {
        $total = 0;
        foreach ($skills as $skill) {
            $hintLevel = $skill->hints()->count();
            $total += $skill->calculateFinalCost($hintLevel);
        }

        return $total;
    }

    /**
     * Generate optimization recommendations based on skill analysis.
     *
     * @param  Collection<int, Skill>  $skills
     * @param  array<int, array<string, mixed>>  $skillDetails
     * @return array<int, string>
     */
    private function generateOptimizationRecommendations(Collection $skills, array $skillDetails): array
    {
        $recommendations = [];

        $noHintSkills = \array_filter($skillDetails, fn (array $detail): bool => $detail['hint_level'] === 0);
        if (\count($noHintSkills) > 0) {
            $recommendations[] = \sprintf(
                'Gather hints for %d skills without discounts to reduce total cost.',
                \count($noHintSkills),
            );
        }

        $evolvableSkills = \array_filter($skillDetails, fn (array $detail): bool => $detail['can_evolve'] === true);
        if (\count($evolvableSkills) > 0) {
            $recommendations[] = \sprintf(
                '%d skills have evolution paths — consider evolving for stronger effects.',
                \count($evolvableSkills),
            );
        }

        $typeGroups = [];
        foreach ($skillDetails as $detail) {
            $type = is_string($detail['skill_type'] ?? null) ? $detail['skill_type'] : 'unknown';
            $typeGroups[$type] = ($typeGroups[$type] ?? 0) + 1;
        }
        $dominantType = ! empty($typeGroups) ? (\array_keys($typeGroups, max($typeGroups))[0] ?? null) : null;
        if ($dominantType && ($typeGroups[$dominantType] ?? 0) >= 3) {
            $recommendations[] = \sprintf(
                'Build is %s-focused (%d skills). Look for %s synergy bonuses.',
                \ucfirst((string) $dominantType),
                $typeGroups[$dominantType],
                $dominantType,
            );
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Build looks well-balanced. Prioritize high-impact skills first.';
        }

        return $recommendations;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBuildTemplate(SkillBuild $build): array
    {
        $skills = $build->skills()->map(fn (Skill $skill): array => [
            'id' => $skill->id,
            'name' => $skill->name,
            'rarity' => $skill->rarity,
            'skill_type' => $skill->skill_type,
            'final_cost' => $skill->base_sp_cost,
            'hints_available' => 0,
        ])->all();

        $skillCount = \is_array($build->skill_ids) ? \count($build->skill_ids) : 0;
        $totalCost = $build->total_sp_cost ?? 0;
        $optimizedCost = $build->optimized_cost ?? 0;

        return [
            'id' => $build->id,
            'name' => $build->name,
            'category' => $build->category,
            'meta_tier' => $build->meta_tier,
            'description' => $build->description,
            'skill_count' => $skillCount,
            'total_sp_cost' => $totalCost,
            'optimized_cost' => $optimizedCost,
            'potential_savings' => $totalCost - $optimizedCost,
            'tags' => $build->tags ?? [],
            'skills' => $skills,
        ];
    }
}
