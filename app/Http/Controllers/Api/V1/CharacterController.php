<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller
{
    /**
     * Display a listing of characters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPageInput = $request->input('per_page', 20);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 20;

        $query = Character::where('user_id', Auth::id())
            ->with(['careers', 'supportCards']);

        // Support pagination
        if ($request->has('per_page')) {
            $characters = $query->paginate($perPage);
        } else {
            $characters = $query->get();
            $characters = [
                'data' => $characters,
            ];
        }

        return response()->json($characters);
    }

    /**
     * Store a newly created character.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scenario_type' => ['required', 'string', 'in:ura_finale,unity_cup'],
            'speed_stat' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'stamina_stat' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'power_stat' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'guts_stat' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'wit_stat' => ['nullable', 'integer', 'min:0', 'max:1200'],
        ]);

        // Sanitize name to prevent XSS
        $sanitizedName = strip_tags($validated['name']);

        $character = Character::create([
            'user_id' => Auth::id(),
            'name' => $sanitizedName,
            'scenario_type' => $validated['scenario_type'],
            'career_stage' => 'junior',
            'current_turn' => 1,
            'current_stats' => [
                'speed' => $validated['speed_stat'] ?? 100,
                'stamina' => $validated['stamina_stat'] ?? 100,
                'power' => $validated['power_stat'] ?? 100,
                'guts' => $validated['guts_stat'] ?? 100,
                'wit' => $validated['wit_stat'] ?? 100,
            ],
            'stat_priorities' => [
                'speed' => 1,
                'stamina' => 2,
                'power' => 3,
                'guts' => 4,
                'wit' => 5,
            ],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'conditions' => [],
            'status' => 'active',
        ]);

        return response()->json([
            'data' => $character,
        ], 201);
    }

    /**
     * Display the specified character.
     */
    public function show(string $id): JsonResponse
    {
        $character = Character::findOrFail($id);

        // Use policy authorization (allows admins and owners)
        $this->authorize('view', $character);

        return response()->json([
            'data' => $character,
        ]);
    }

    /**
     * Update the specified character.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $character = Character::findOrFail($id);

        // Use policy authorization (allows admins and owners)
        $this->authorize('update', $character);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'scenario_type' => ['sometimes', 'string', 'in:ura_finale,unity_cup'],
            'speed_stat' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'stamina_stat' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'power_stat' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'guts_stat' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'wit_stat' => ['sometimes', 'integer', 'min:0', 'max:1200'],
        ]);

        if (isset($validated['name'])) {
            // Sanitize name to prevent XSS
            $character->name = strip_tags($validated['name']);
        }

        if (isset($validated['scenario_type'])) {
            $character->scenario_type = $validated['scenario_type'];
        }

        $currentStats = $character->current_stats ?? [];
        if (isset($validated['speed_stat'])) {
            $currentStats['speed'] = $validated['speed_stat'];
        }
        if (isset($validated['stamina_stat'])) {
            $currentStats['stamina'] = $validated['stamina_stat'];
        }
        if (isset($validated['power_stat'])) {
            $currentStats['power'] = $validated['power_stat'];
        }
        if (isset($validated['guts_stat'])) {
            $currentStats['guts'] = $validated['guts_stat'];
        }
        if (isset($validated['wit_stat'])) {
            $currentStats['wit'] = $validated['wit_stat'];
        }

        if (count($currentStats) > 0) {
            $character->current_stats = $currentStats;
        }

        $character->save();

        return response()->json([
            'data' => $character,
        ]);
    }

    /**
     * Remove the specified character.
     */
    public function destroy(string $id): JsonResponse
    {
        $character = Character::findOrFail($id);

        // Use policy authorization (allows admins and owners)
        $this->authorize('delete', $character);

        $character->delete();

        return response()->json([
            'message' => 'Character deleted successfully',
        ]);
    }

    /**
     * Acquire a skill for the character.
     */
    public function acquireSkill(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'skill_id' => ['required', 'integer', 'exists:ucp_skills,id'],
        ]);

        $isAdmin = Auth::user()?->isAdmin() ?? false;

        // Admins can access any character, regular users only their own
        if ($isAdmin) {
            $character = Character::findOrFail($id);
        } else {
            $character = Character::where('user_id', Auth::id())
                ->findOrFail($id);
        }

        $skill = Skill::findOrFail($validated['skill_id']);

        if (! ($skill instanceof Skill)) {
            return response()->json([
                'message' => 'Skill not found',
                'errors' => [
                    'skill_id' => ['Invalid skill'],
                ],
            ], 422);
        }

        // Check if skill is already acquired
        $existing = SkillAcquisition::where('character_id', $character->id)
            ->where('skill_id', $skill->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Skill already acquired',
                'errors' => [
                    'skill_id' => ['This skill has already been acquired'],
                ],
            ], 422);
        }

        // Check if character has sufficient SP (bypass for admins)
        $availableSp = $character->available_sp ?? 0;

        $skillCost = $skill->base_sp_cost;
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        if (! $isAdmin && $availableSp < $skillCost) {
            return response()->json([
                'message' => 'Insufficient SP',
                'errors' => [
                    'skill_id' => ['Character does not have sufficient SP to acquire this skill'],
                ],
            ], 422);
        }

        // Deduct SP from character (only if not admin)
        if (! $isAdmin) {
            $character->update([
                'available_sp' => $availableSp - $skillCost,
            ]);
        }

        // Create skill acquisition
        $acquisition = SkillAcquisition::create([
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'career_id' => $character->currentCareer?->id,
            'turn_acquired' => $character->current_turn,
            'career_phase' => $character->career_stage,
            'acquisition_method' => 'manual',
            'base_sp_cost' => $skillCost,
            'final_sp_cost' => $skillCost,
            'sp_saved' => 0,
            'hints_used' => 0,
            'is_evolution' => false,
            'is_active' => true,
        ]);

        return response()->json([
            'data' => $acquisition,
        ], 201);
    }

    /**
     * Get all skills for the character.
     */
    public function skills(string $id): JsonResponse
    {
        $character = Character::where('user_id', Auth::id())
            ->findOrFail($id);

        $skills = SkillAcquisition::where('character_id', $character->id)
            ->with('skill')
            ->get();

        return response()->json([
            'data' => $skills,
        ]);
    }

    /**
     * Remove a skill from the character.
     */
    public function removeSkill(string $id, string $skillId): JsonResponse
    {
        $character = Character::where('user_id', Auth::id())
            ->findOrFail($id);

        $acquisition = SkillAcquisition::where('character_id', $character->id)
            ->where('skill_id', $skillId)
            ->first();

        if (! $acquisition) {
            return response()->json([
                'message' => 'Skill not found',
            ], 404);
        }

        // Refund SP to character
        $character->update([
            'available_sp' => ($character->available_sp ?? 0) + $acquisition->final_sp_cost,
        ]);

        $acquisition->delete();

        return response()->json([
            'message' => 'Skill removed successfully',
        ]);
    }
}
