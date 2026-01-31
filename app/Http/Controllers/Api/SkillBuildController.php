<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Skill Build Controller
 *
 * Handles skill build templates, saved builds, and AI optimization
 * for the Skills Management page Build Planner tab.
 */
class SkillBuildController extends Controller
{
    /**
     * Get available build templates.
     */
    public function templates(): JsonResponse
    {
        $templates = [
            [
                'id' => 1,
                'name' => 'Speed Specialist',
                'category' => 'Speed',
                'meta_tier' => 'S',
                'description' => 'Optimized for short-distance speed races with acceleration focus',
                'skill_count' => 8,
                'total_sp_cost' => 800,
                'optimized_cost' => 640,
                'potential_savings' => 160,
                'tags' => ['Speed', 'Acceleration', 'Short Distance'],
                'skills' => [
                    ['id' => 1, 'name' => 'Speed Star', 'rarity' => 'rare', 'skill_type' => 'speed', 'final_cost' => 120, 'hints_available' => 2],
                    ['id' => 2, 'name' => 'Quick Start', 'rarity' => 'normal', 'skill_type' => 'speed', 'final_cost' => 80, 'hints_available' => 1],
                    ['id' => 3, 'name' => 'Acceleration Burst', 'rarity' => 'rare', 'skill_type' => 'speed', 'final_cost' => 100, 'hints_available' => 0],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Stamina Endurance',
                'category' => 'Stamina',
                'meta_tier' => 'A',
                'description' => 'Built for long-distance races with stamina recovery',
                'skill_count' => 10,
                'total_sp_cost' => 1000,
                'optimized_cost' => 800,
                'potential_savings' => 200,
                'tags' => ['Stamina', 'Recovery', 'Long Distance'],
                'skills' => [
                    ['id' => 4, 'name' => 'Endurance Master', 'rarity' => 'rare', 'skill_type' => 'stamina', 'final_cost' => 150, 'hints_available' => 2],
                    ['id' => 5, 'name' => 'Recovery Boost', 'rarity' => 'normal', 'skill_type' => 'stamina', 'final_cost' => 90, 'hints_available' => 1],
                ],
            ],
            [
                'id' => 3,
                'name' => 'Balanced All-Rounder',
                'category' => 'Balanced',
                'meta_tier' => 'A',
                'description' => 'Versatile build suitable for various race types',
                'skill_count' => 12,
                'total_sp_cost' => 1200,
                'optimized_cost' => 960,
                'potential_savings' => 240,
                'tags' => ['Balanced', 'Versatile', 'All Distance'],
                'skills' => [
                    ['id' => 6, 'name' => 'Versatile Runner', 'rarity' => 'rare', 'skill_type' => 'balanced', 'final_cost' => 130, 'hints_available' => 1],
                    ['id' => 7, 'name' => 'Adaptive Pace', 'rarity' => 'normal', 'skill_type' => 'balanced', 'final_cost' => 85, 'hints_available' => 2],
                ],
            ],
            [
                'id' => 4,
                'name' => 'Power Runner',
                'category' => 'Power',
                'meta_tier' => 'S+',
                'description' => 'Maximum power output for competitive racing',
                'skill_count' => 9,
                'total_sp_cost' => 950,
                'optimized_cost' => 760,
                'potential_savings' => 190,
                'tags' => ['Power', 'Competitive', 'Mid Distance'],
                'skills' => [
                    ['id' => 8, 'name' => 'Power Surge', 'rarity' => 'unique', 'skill_type' => 'power', 'final_cost' => 200, 'hints_available' => 0],
                    ['id' => 9, 'name' => 'Muscle Memory', 'rarity' => 'rare', 'skill_type' => 'power', 'final_cost' => 110, 'hints_available' => 1],
                ],
            ],
            [
                'id' => 5,
                'name' => 'Guts Fighter',
                'category' => 'Guts',
                'meta_tier' => 'B',
                'description' => 'Never give up mentality for close finishes',
                'skill_count' => 7,
                'total_sp_cost' => 700,
                'optimized_cost' => 560,
                'potential_savings' => 140,
                'tags' => ['Guts', 'Determination', 'Close Finish'],
                'skills' => [
                    ['id' => 10, 'name' => 'Fighting Spirit', 'rarity' => 'rare', 'skill_type' => 'guts', 'final_cost' => 100, 'hints_available' => 2],
                    ['id' => 11, 'name' => 'Last Spurt', 'rarity' => 'normal', 'skill_type' => 'guts', 'final_cost' => 70, 'hints_available' => 1],
                ],
            ],
        ];

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
        // In a real implementation, this would fetch from database
        // For now, return empty array or mock data based on auth status
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        // Mock saved builds for authenticated users
        $savedBuilds = [
            [
                'id' => 1,
                'name' => 'My Speed Build',
                'skill_count' => 6,
                'total_sp' => 650,
                'created_at' => now()->subDays(3)->toDateTimeString(),
            ],
            [
                'id' => 2,
                'name' => 'Championship Prep',
                'skill_count' => 8,
                'total_sp' => 920,
                'created_at' => now()->subDays(7)->toDateTimeString(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $savedBuilds,
        ]);
    }

    /**
     * Get AI optimization for a build.
     */
    public function optimize(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|integer',
        ]);

        // Mock AI optimization response
        $optimization = [
            'summary' => 'This build has excellent synergy for speed-focused racing. Consider adding more stamina skills for longer races.',
            'efficiency_score' => 85,
            'synergy_rating' => 8,
            'meta_alignment' => 92,
            'recommendations' => [
                'Prioritize acquiring Speed Star first for maximum early-game impact',
                'Wait for hint level 2 on Acceleration Burst before purchasing',
                'Consider swapping Quick Start for Dash Master if available',
            ],
            'acquisition_order' => [
                'Speed Star (120 SP) - High priority, good synergy',
                'Quick Start (80 SP) - Early game essential',
                'Acceleration Burst (100 SP) - Wait for hints',
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

        // In a real implementation, this would apply the build to the character
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
            'template_id' => 'sometimes|integer',
            'skills' => 'sometimes|array',
        ]);

        // In a real implementation, this would save to database
        return response()->json([
            'success' => true,
            'message' => 'Build saved successfully',
            'data' => [
                'id' => rand(100, 999),
                'name' => $request->input('name'),
                'created_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Delete a saved build.
     */
    public function deleteBuild(int $id): JsonResponse
    {
        // In a real implementation, this would delete from database
        return response()->json([
            'success' => true,
            'message' => 'Build deleted successfully',
        ]);
    }
}
