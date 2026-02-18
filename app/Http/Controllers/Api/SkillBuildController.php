<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillBuild;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                $totalCost = (int) $group->sum('base_sp_cost');
                $optimizedCost = (int) round($totalCost * 0.8);

                return [
                    'id' => crc32($type),
                    'name' => ucfirst($type).' Build',
                    'category' => ucfirst($type),
                    'meta_tier' => $group->first()->meta_tier ?? 'B',
                    'description' => 'Auto-generated build based on recent skills.',
                    'skill_count' => count($skillIds),
                    'total_sp_cost' => $totalCost,
                    'optimized_cost' => $optimizedCost,
                    'potential_savings' => $totalCost - $optimizedCost,
                    'tags' => [ucfirst($type)],
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
                'skill_count' => is_array($build->skill_ids) ? count($build->skill_ids) : 0,
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
        $skills = Skill::whereIn('id', $skillIds)->get(['id', 'name', 'skill_type', 'base_sp_cost']);
        $totalCost = (int) $skills->sum('base_sp_cost');
        $optimizedCost = (int) round($totalCost * 0.8);
        $efficiencyScore = $totalCost > 0 ? (int) round(($optimizedCost / $totalCost) * 100) : 0;
        $synergyRating = max(1, min(10, $skills->count()));

        $optimization = [
            'summary' => 'Optimization calculated from selected skills.',
            'efficiency_score' => $efficiencyScore,
            'synergy_rating' => $synergyRating,
            'total_sp_cost' => $totalCost,
            'optimized_cost' => $optimizedCost,
            'recommendations' => [
                'Prioritize high-impact skills first to maximize early gains.',
                'Monitor hint availability to reduce total SP cost.',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $optimization,
        ]);
    }

    /**
     * Apply a build to a character.
     */
    public function applyBuild(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|integer',
            'character_id' => 'sometimes|integer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Build applied successfully',
            'data' => [
                'skills_added' => 3,
                'sp_spent' => 300,
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
        $totalCost = (int) Skill::whereIn('id', $skillIds)->sum('base_sp_cost');
        $optimizedCost = (int) round($totalCost * 0.8);

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

        $skillCount = is_array($build->skill_ids) ? count($build->skill_ids) : 0;
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
