<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Display a listing of skills.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Skill::query()->where('is_active', true);

        // Filter by skill type
        if ($request->has('skill_type')) {
            $query->where('skill_type', $request->input('skill_type'));
        }

        // Search by name
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            if (is_string($searchTerm)) {
                $query->where('name', 'like', '%'.(is_string($searchTerm) ? $searchTerm : '').'%');
            }
        }

        // Filter by SP cost range
        if ($request->has('min_sp_cost')) {
            $query->where('base_sp_cost', '>=', $request->input('min_sp_cost'));
        }

        if ($request->has('max_sp_cost')) {
            $query->where('base_sp_cost', '<=', $request->input('max_sp_cost'));
        }

        // Filter by rarity
        if ($request->has('rarity')) {
            $query->where('rarity', $request->input('rarity'));
        }

        // Filter by meta tier
        if ($request->has('meta_tier')) {
            $query->where('meta_tier', $request->input('meta_tier'));
        }

        $skills = $query->get();

        return response()->json([
            'data' => $skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'skill_type' => $skill->skill_type,
                    'base_sp_cost' => $skill->base_sp_cost,
                    'description' => $skill->description,
                    'rarity' => $skill->rarity,
                ];
            }),
        ]);
    }

    /**
     * Display the specified skill.
     */
    public function show(int $id): JsonResponse
    {
        $skill = Skill::find($id);
        if ($skill instanceof \Illuminate\Database\Eloquent\Collection) {
            $skill = $skill->first();
        }
        if (! $skill instanceof \App\Models\Skill) {
            return response()->json(['error' => 'Skill not found'], 404);
        }

        if (! $skill) {
            return response()->json([
                'message' => 'Skill not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'description' => $skill->description,
                'skill_type' => $skill->skill_type,
                'base_sp_cost' => $skill->base_sp_cost,
                'rarity' => $skill->rarity,
                'effects' => $skill->effects,
                'activation_conditions' => $skill->activation_conditions,
            ],
        ]);
    }

    /**
     * Get hints for a specific skill.
     */
    public function hints(int $id): JsonResponse
    {
        $skill = Skill::find($id);
        if ($skill instanceof \Illuminate\Database\Eloquent\Collection) {
            $skill = $skill->first();
        }
        if (! $skill instanceof \App\Models\Skill) {
            return response()->json(['error' => 'Skill not found'], 404);
        }

        if (! $skill) {
            return response()->json([
                'message' => 'Skill not found',
            ], 404);
        }

        $hints = SkillHint::where('skill_id', $id)->get();

        return response()->json([
            'data' => $hints->map(function ($hint) {
                return [
                    'id' => $hint->id,
                    'skill_id' => $hint->skill_id,
                    'character_id' => $hint->character_id,
                    'source_type' => $hint->source_type,
                    'source_name' => $hint->source_name,
                    'turn_obtained' => $hint->turn_obtained,
                    'is_used' => $hint->is_used,
                ];
            }),
        ]);
    }

    /**
     * Get skill recommendations for a character.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
        ]);

        $character = Character::where('user_id', Auth::id())
            ->findOrFail($request->input('character_id'));

        if (! ($character instanceof Character)) {
            return response()->json([
                'error' => 'Character not found',
            ], 404);
        }

        // Get skills the character doesn't have yet
        $acquiredSkillIds = $character->skillAcquisitions()->pluck('skill_id');

        $recommendations = Skill::where('is_active', true)
            ->whereNotIn('id', $acquiredSkillIds)
            ->where('meta_tier', 'S')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'recommended_skills' => $recommendations->map(function ($skill) {
                    if (! ($skill instanceof Skill)) {
                        return null;
                    }

                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'skill_type' => $skill->skill_type,
                        'base_sp_cost' => $skill->base_sp_cost,
                        'meta_tier' => $skill->meta_tier,
                    ];
                })->filter(),
                'reasoning' => 'Recommended based on meta tier S skills not yet acquired',
            ],
        ]);
    }

    /**
     * Get skill evolution paths.
     */
    public function evolution(Request $request): JsonResponse
    {
        $request->validate([
            'skill_id' => ['required', 'integer', 'exists:ucp_skills,id'],
        ]);

        /** @var Skill $skill */
        $skill = Skill::findOrFail($request->input('skill_id'));

        $evolutionPaths = [];
        $prerequisites = [];

        $evolutionTarget = $skill->evolutionTarget;
        if ($evolutionTarget instanceof Skill) {
            $evolutionPaths[] = [
                'from' => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ],
                'to' => [
                    'id' => $evolutionTarget->id,
                    'name' => $evolutionTarget->name,
                ],
            ];
        }

        $evolutionSource = $skill->evolutionSource;
        if ($evolutionSource instanceof Skill) {
            $prerequisites[] = [
                'id' => $evolutionSource->id,
                'name' => $evolutionSource->name,
            ];
        }

        return response()->json([
            'data' => [
                'evolution_paths' => $evolutionPaths,
                'prerequisites' => $prerequisites,
            ],
        ]);
    }
}
